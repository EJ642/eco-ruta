<?php
header('Content-Type: application/json');

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$usuarioId = (int) ($input['usuario_id'] ?? $_SESSION['usuario_id'] ?? 0);
$tokenRecibido = (string) ($input['token'] ?? '');
$rol = (string) ($input['rol'] ?? $_SESSION['rol'] ?? '');

if (!isset($_SESSION['usuario_id'], $_SESSION['token'], $_SESSION['rol'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión inválida.']);
    exit;
}

if ($usuarioId !== (int) $_SESSION['usuario_id']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no válido.']);
    exit;
}

if ($tokenRecibido !== $_SESSION['token']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token inválido.']);
    exit;
}

if (!in_array($rol, ['administrador', 'comerciante', 'repartidor'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Rol no permitido.']);
    exit;
}

$_SESSION['active'] = true;
$_SESSION['token'] = bin2hex(random_bytes(32));
$_SESSION['ultimo_acceso'] = time();

echo json_encode([
    'success' => true,
    'new_token' => $_SESSION['token'],
    'usuario_id' => $_SESSION['usuario_id'],
    'rol' => $_SESSION['rol']
]);
