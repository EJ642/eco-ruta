<?php
session_name('DOCENTE_SESSION');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../servicios/conexion.php';


function validarRutaSegura($ruta) {
    if (empty($ruta)) {
        return false;
    }

    $ruta = filter_var($ruta, FILTER_SANITIZE_URL);
    $ruta = strtok($ruta, '#');

    if (strpos($ruta, '..') !== false) {
        return false;
    }

    if (!preg_match('#^/[A-Za-z0-9_\-./?=&%]*$#', $ruta)) {
        return false;
    }

    $basename = basename(parse_url($ruta, PHP_URL_PATH));
    if (in_array($basename, ['index.php', 'login.php', 'salir.php'], true)) {
        return false;
    }

    return $ruta;
}



function obtenerRutaDeCookie() {
    if (empty($_COOKIE['ultima_ruta'])) {
        return false;
    }
    return validarRutaSegura($_COOKIE['ultima_ruta']);
}


// Determine if request is AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$correo = isset($_POST['correo']) ? limpiar_cadena($_POST['correo']) : '';
$password = isset($_POST['password']) ? limpiar_cadena($_POST['password']) : '';

if ($correo === '' || $password === '') {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Debe completar correo y contraseña.']);
        exit;
    }
    header('Location: index.php?error=1');
    exit;
}

// Buscar usuario por correo y rol Docente (idRol = 3)
$datos = buscar_datos("SELECT u.*, r.rol, d.idDocente, d.nombres as nombre_docente, d.apellidos as apellido_docente 
FROM usuarios u 
JOIN rol r ON u.idRol = r.idRol
LEFT JOIN docente d ON u.idUsuario = d.idUsuario
WHERE u.correo = '" . $correo . "' AND u.idRol = 3 AND u.estado = 'Activo'");

if ($datos !== false && count($datos) > 0) {
    $user = $datos[0];
    $passwordStored = $user['password'];
    
    // Verificar contraseña
    if (password_verify($password, $passwordStored) || $passwordStored === $password) {
        // Generar token único de sesión
        $token = bin2hex(random_bytes(32));
        
        $_SESSION['active'] = true;
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['correo'] = $user['correo'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['usuario_id'] = $user['idUsuario'];
        $_SESSION['docente_id'] = $user['idDocente'] ?? null;
        $_SESSION['nombre_docente'] = $user['nombre_docente'] ?? '';
        $_SESSION['apellido_docente'] = $user['apellido_docente'] ?? '';
        $_SESSION['token'] = $token;
        $_SESSION['login_time'] = time();
        $_SESSION['ultimo_acceso'] = time();

        // Docente siempre va a su menú, sin importar cookies previas
        $destino = obtenerRutaDeCookie() ?: 'menu.php';

        if ($isAjax) {
            echo json_encode(['success' => true, 'redirect' => $destino]);
            exit;
        }
        header('Location: ' . $destino);
        exit;
    }
}

if ($isAjax) {
    echo json_encode(['success' => false, 'message' => '!Credenciales incorrectas!']);
    exit;
}

header('Location: index.php?error=1');
exit;