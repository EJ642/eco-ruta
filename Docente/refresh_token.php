<?php
session_name('DOCENTE_SESSION');
session_start();
//11:05
define('SESSION_TIMEOUT', 30 * 60); 

// Verificar que sea una petición POST y que tenga token válido
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$input = json_decode(file_get_contents('php://input'), true);
$token_recibido = $input['token'] ?? '';

if (empty($token_recibido) || !isset($_SESSION['token']) || $_SESSION['token'] !== $token_recibido) {
    http_response_code(401);
    exit('Token inválido');
}

//11:05
if (!isset($_SESSION['ultimo_acceso']) || (time() - $_SESSION['ultimo_acceso']) > SESSION_TIMEOUT) {
    // La sesión ya expiró por inactividad
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    http_response_code(401);
    exit('Sesión expirada');
}

// Regenerar token y actualizar acceso
$nuevo_token = bin2hex(random_bytes(32));
$_SESSION['token'] = $nuevo_token;
$_SESSION['ultimo_acceso'] = time();

// Responder con el nuevo token
header('Content-Type: application/json');
echo json_encode(['success' => true, 'new_token' => $nuevo_token]);
?>