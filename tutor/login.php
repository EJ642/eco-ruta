<?php
    session_name('TUTOR_SESSION');
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
        if (empty($_COOKIE['ultima_ruta_tutor'])) {
            return false;
        }
        return validarRutaSegura($_COOKIE['ultima_ruta_tutor']);
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
        header('Location: indexTutor.php?error=1');
        exit;
    }

    $datos = buscar_datos("SELECT u.*, r.rol, t.idTutor
    FROM usuarios u 
    JOIN rol r ON u.idRol = r.idRol
    LEFT JOIN tutor t ON u.idUsuario = t.idUsuario
    WHERE u.correo = '" . $correo . "' AND u.idRol = 4 AND u.estado = 'Activo'");

    if ($datos !== false && count($datos) > 0) {
        $user = $datos[0];
        $passwordStored = $user['password'];
        if (password_verify($password, $passwordStored) || $passwordStored === $password) {
            // Generar token único de sesión
            $token = bin2hex(random_bytes(32));
            
            $_SESSION['active'] = true;
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['usuario_id'] = $user['idUsuario'] ?? $user['usuario'];
            $_SESSION['idTutor'] = $user['idTutor'] ?? 0;
            $_SESSION['token'] = $token;
            $_SESSION['login_time'] = time(); 

            //Validar que la ruta guardada sea de Tutores
            $destino = obtenerRutaDeCookie() ?: 'menuTutor.php';
            
            $rutaCookieTutor = obtenerRutaDeCookie();
            if ($rutaCookieTutor && strpos($rutaCookieTutor, '/tutor/') !== false) {
                $destino = $rutaCookieTutor;
            }

            if ($isAjax) {
                echo json_encode(['success' => true, 'redirect' => $destino]);
                exit;
            }
            header('Location: ' . $destino);
            exit;
        }
    }
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas!']);
        exit;
    }
    header('Location: indexTutor.php?error=1');
    exit;
?>