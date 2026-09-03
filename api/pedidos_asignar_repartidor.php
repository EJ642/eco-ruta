<?php
/**
 * API: Asignar Repartidor a un Pedido
 * =====================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['administrador']);

require_once __DIR__ . '/../servicios/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'msg' => 'Método no permitido.']);
    exit;
}

$idPedido = (int) ($_POST['id_pedido'] ?? 0);
$idRepartidor = (int) ($_POST['id_repartidor'] ?? 0);

if ($idPedido <= 0 || $idRepartidor <= 0) {
    echo json_encode(['status' => false, 'msg' => 'Debe seleccionar un pedido y un repartidor válido.']);
    exit;
}

$conexion = conectar_bd();

try {
    $stmt = $conexion->prepare('UPDATE pedidos SET id_repartidor = ? WHERE id_pedido = ?');
    $stmt->bind_param('ii', $idRepartidor, $idPedido);
    $res = $stmt->execute();
    $stmt->close();
    $conexion->close();

    if ($res) {
        echo json_encode([
            'status' => true,
            'msg' => 'Repartidor asignado con éxito al pedido #' . $idPedido
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'msg' => 'No se pudo asignar el repartidor.'
        ]);
    }

} catch (Exception $e) {
    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->close();
    }
    error_log("Error en pedidos_asignar_repartidor: " . $e->getMessage());
    echo json_encode([
        'status' => false,
        'msg' => 'Error en el servidor al asignar repartidor.'
    ]);
}

