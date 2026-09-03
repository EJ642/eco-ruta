<?php
require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['administrador']);
require_once __DIR__ . '/../servicios/conexion.php';

$response = ['status' => false, 'msg' => ''];

$id = (int) ($_POST['id_usuario'] ?? 0);

if (!$id) {
    echo json_encode(['status' => false, 'msg' => 'Identificador no válido.']);
    exit;
}

// Protección: no eliminar el propio usuario de la sesión activa
if ((int) ($_SESSION['usuario_id'] ?? 0) === $id) {
    echo json_encode(['status' => false, 'msg' => 'No podés eliminar tu propio usuario mientras estás conectado.']);
    exit;
}

$conexion = conectar_bd();

$stmtUsuario = $conexion->prepare(
    'SELECT u.id_usuario, r.nombre_rol, c.id_comercio, rp.id_repartidor
     FROM usuarios u
     INNER JOIN roles r ON r.id_rol = u.id_rol
     LEFT JOIN comercios c ON c.id_usuario = u.id_usuario
     LEFT JOIN repartidores rp ON rp.id_usuario = u.id_usuario
     WHERE u.id_usuario = ? LIMIT 1'
);
$stmtUsuario->bind_param('i', $id);
$stmtUsuario->execute();
$usuario = $stmtUsuario->get_result()->fetch_assoc();
$stmtUsuario->close();

if (!$usuario) {
    $conexion->close();
    echo json_encode(['status' => false, 'msg' => 'El usuario no fue encontrado.']);
    exit;
}

// Bloquear si el comercio o repartidor tiene pedidos asociados (histórico a preservar)
if ($usuario['nombre_rol'] === 'comerciante' && $usuario['id_comercio']) {
    $stmtPedidos = $conexion->prepare('SELECT COUNT(*) AS total FROM pedidos WHERE id_comercio = ?');
    $stmtPedidos->bind_param('i', $usuario['id_comercio']);
    $stmtPedidos->execute();
    $total = (int) $stmtPedidos->get_result()->fetch_assoc()['total'];
    $stmtPedidos->close();

    if ($total > 0) {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'Este comercio tiene pedidos registrados. Desactivalo en vez de eliminarlo.']);
        exit;
    }
}

if ($usuario['nombre_rol'] === 'repartidor' && $usuario['id_repartidor']) {
    $stmtPedidos = $conexion->prepare('SELECT COUNT(*) AS total FROM pedidos WHERE id_repartidor = ?');
    $stmtPedidos->bind_param('i', $usuario['id_repartidor']);
    $stmtPedidos->execute();
    $total = (int) $stmtPedidos->get_result()->fetch_assoc()['total'];
    $stmtPedidos->close();

    if ($total > 0) {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'Este repartidor tiene pedidos registrados. Desactivalo en vez de eliminarlo.']);
        exit;
    }
}

$conexion->begin_transaction();

try {
    if ($usuario['nombre_rol'] === 'comerciante' && $usuario['id_comercio']) {
        $stmtDelPerfil = $conexion->prepare('DELETE FROM comercios WHERE id_comercio = ?');
        $stmtDelPerfil->bind_param('i', $usuario['id_comercio']);
        $stmtDelPerfil->execute();
        $stmtDelPerfil->close();
    } elseif ($usuario['nombre_rol'] === 'repartidor' && $usuario['id_repartidor']) {
        $stmtDelPerfil = $conexion->prepare('DELETE FROM repartidores WHERE id_repartidor = ?');
        $stmtDelPerfil->bind_param('i', $usuario['id_repartidor']);
        $stmtDelPerfil->execute();
        $stmtDelPerfil->close();
    }

    $stmtDelUsuario = $conexion->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
    $stmtDelUsuario->bind_param('i', $id);
    if (!$stmtDelUsuario->execute()) {
        throw new Exception('No se pudo eliminar el usuario: ' . $stmtDelUsuario->error);
    }
    $stmtDelUsuario->close();

    $conexion->commit();
    $response['status'] = true;
    $response['msg'] = 'Usuario eliminado correctamente.';

} catch (Exception $e) {
    $conexion->rollback();
    error_log('Error al eliminar usuario: ' . $e->getMessage());
    $response['msg'] = 'Error al eliminar el registro.';
}

$conexion->close();
echo json_encode($response);
