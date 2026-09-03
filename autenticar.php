<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    header('Location: index.php?error=credenciales');
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';

if (!$email || $password === '') {
    header('Location: index.php?error=credenciales');
    exit;
}

require_once __DIR__ . '/servicios/conexion.php';
$conexion = conectar_bd();
$consulta = $conexion->prepare(
    'SELECT u.id_usuario, u.nombre_completo, u.email, u.password_hash, u.activo, r.nombre_rol
     FROM usuarios u
     INNER JOIN roles r ON r.id_rol = u.id_rol
     WHERE u.email = ? LIMIT 1'
);
$consulta->bind_param('s', $email);
$consulta->execute();
$usuario = $consulta->get_result()->fetch_assoc();
$consulta->close();

// Permite datos antiguos en texto plano y los convierte al iniciar sesión.
$passwordValida = $usuario && password_verify($password, $usuario['password_hash']);
$passwordPlana = $usuario && hash_equals((string) $usuario['password_hash'], $password);

if (!$passwordValida && !$passwordPlana) {
    $conexion->close();
    header('Location: index.php?error=credenciales');
    exit;
}

if (!(int) $usuario['activo']) {
    $conexion->close();
    header('Location: index.php?error=inactiva');
    exit;
}

if ($passwordPlana) {
    $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
    $actualizacion = $conexion->prepare('UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?');
    $actualizacion->bind_param('si', $nuevoHash, $usuario['id_usuario']);
    $actualizacion->execute();
    $actualizacion->close();
}

$conexion->close();

session_regenerate_id(true);
$_SESSION['usuario_id'] = (int) $usuario['id_usuario'];
$_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['rol'] = $usuario['nombre_rol'];
$_SESSION['active'] = true;
$_SESSION['token'] = bin2hex(random_bytes(32));
$_SESSION['ultimo_acceso'] = time();
$_SESSION['inicio_sesion'] = time();
unset($_SESSION['csrf_token']);

header('Location: router.php');
exit;
