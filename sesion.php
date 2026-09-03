<?php
session_start();

// Configuración
define('SESSION_TIMEOUT', 1800); // 30 minutos en segundos (ajusta según necesites)
define('TOKEN_LENGTH', 32);

// Generar token seguro
function generarToken() {
    return bin2hex(random_bytes(TOKEN_LENGTH));
}

// Verificar si la sesión es válida
function verificarSesion() {
    // Verificar si existe sesión activa
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }

    // Verificar si el token existe
    if (!isset($_SESSION['token'])) {
        return false;
    }

    // Verificar si ha expirado por inactividad
    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempoInactivo = time() - $_SESSION['ultimo_acceso'];
        
        if ($tiempoInactivo > SESSION_TIMEOUT) {
            // Session expiró por inactividad
            destruirSesion();
            return false;
        }
    }
//11:05
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
        // Sesión expiró por tiempo total de vida
        destruirSesion();
        return false;
    }

    // Actualizar último acceso
    $_SESSION['ultimo_acceso'] = time();

    if (!function_exists('validarRutaSegura')) {
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

            return $ruta;
        }
    }

    if (!function_exists('guardarUltimaRuta')) {
        function guardarUltimaRuta() {
            $rutaActual = $_SERVER['REQUEST_URI'] ?? '';
            $rutaSegura = validarRutaSegura($rutaActual);
            if ($rutaSegura) {
                // Guardar en cookie separada según el rol
                $rol = strtolower($_SESSION['rol'] ?? '');
                if ($rol === 'docente') {
                    setcookie('ultima_ruta_docente', $rutaSegura, time() + 30 * 24 * 60 * 60, '/', '', false, true);
                } else {
                    setcookie('ultima_ruta_admin', $rutaSegura, time() + 30 * 24 * 60 * 60, '/', '', false, true);
                }
            }
        }
    }

    guardarUltimaRuta();
    
    return true;
}

// Destruir la sesión completamente
function destruirSesion() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

// Crear sesión de usuario (llamar al hacer login)
function crearSesion($usuario_id, $usuario, $rol) {
    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['usuario'] = $usuario;
    $_SESSION['rol'] = $rol;
    $_SESSION['token'] = generarToken();
    $_SESSION['ultimo_acceso'] = time();
    $_SESSION['hora_inicio'] = time();
    $_SESSION['active'] = true;
}

// Para verificar en cada página (excepto login, index y salir)
if (basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'index.php' && basename($_SERVER['PHP_SELF']) !== 'salir.php') {
    if (!verificarSesion()) {
        destruirSesion();
        header("Location: Administracion/index.php?error=sesion_expirada");
        exit();
    }
}

$ruta = "./";
?>
