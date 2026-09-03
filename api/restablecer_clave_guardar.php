<?php
/**
 * API: Guardar Nueva Contraseña Restablecida
 * ===========================================
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'msg' => 'Método no permitido.']);
    exit;
}

$token = trim($_POST['token'] ?? '');
$nuevaClave = $_POST['nueva_clave'] ?? '';
$confirmarClave = $_POST['confirmar_clave'] ?? '';

if ($token === '' || $nuevaClave === '') {
    echo json_encode(['status' => false, 'msg' => 'Todos los campos son requeridos.']);
    exit;
}

if ($nuevaClave !== $confirmarClave) {
    echo json_encode(['status' => false, 'msg' => 'Las contraseñas no coinciden.']);
    exit;
}

// Validación de complejidad de contraseña
if (strlen($nuevaClave) < 8 || !preg_match('/[A-Z]/', $nuevaClave) || !preg_match('/[0-9]/', $nuevaClave) || !preg_match('/[^A-Za-z0-9]/', $nuevaClave)) {
    echo json_encode(['status' => false, 'msg' => 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un carácter especial.']);
    exit;
}

require_once __DIR__ . '/../servicios/conexion.php';

$conexion = conectar_bd();

try {
    // 1. Verificar validez del token
    $stmtToken = $conexion->prepare(
        'SELECT id, email, expiracion, usado
         FROM recuperacion_claves
         WHERE token = ? LIMIT 1'
    );
    $stmtToken->bind_param('s', $token);
    $stmtToken->execute();
    $registro = $stmtToken->get_result()->fetch_assoc();
    $stmtToken->close();

    if (!$registro) {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'El enlace de recuperación es inválido.']);
        exit;
    }

    if ((int) $registro['usado'] === 1) {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'Este enlace de recuperación ya fue utilizado previamente.']);
        exit;
    }

    if (strtotime($registro['expiracion']) < time()) {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'El enlace de recuperación ha expirado. Por favor, solicita uno nuevo.']);
        exit;
    }

    $email = $registro['email'];
    $nuevoHash = password_hash($nuevaClave, PASSWORD_DEFAULT);

    // 2. Transacción para actualizar clave e invalidar token
    $conexion->begin_transaction();

    $stmtUsuario = $conexion->prepare('UPDATE usuarios SET password_hash = ? WHERE email = ?');
    $stmtUsuario->bind_param('ss', $nuevoHash, $email);
    $stmtUsuario->execute();
    $stmtUsuario->close();

    $stmtUsado = $conexion->prepare('UPDATE recuperacion_claves SET usado = 1 WHERE id = ?');
    $stmtUsado->bind_param('i', $registro['id']);
    $stmtUsado->execute();
    $stmtUsado->close();

    $conexion->commit();
    $conexion->close();

    echo json_encode([
        'status' => true,
        'msg' => '¡Tu contraseña ha sido actualizada con éxito! Redirigiendo al inicio de sesión...'
    ]);

} catch (Exception $e) {
    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->rollback();
        $conexion->close();
    }
    error_log("Error en restablecer_clave_guardar: " . $e->getMessage());
    echo json_encode([
        'status' => false,
        'msg' => 'Ocurrió un error al actualizar la contraseña. Intente nuevamente.'
    ]);
}

