<?php
require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['administrador']);
require_once __DIR__ . '/../servicios/conexion.php';

$response = ['status' => false, 'msg' => ''];

if (empty($_POST)) {
    echo json_encode(['status' => false, 'msg' => 'No se recibieron datos.']);
    exit;
}

$idUsuario = (int) ($_POST['id_usuario'] ?? 0);
$rol = $_POST['rol'] ?? '';
$nombreCompleto = trim($_POST['nombre_completo'] ?? '');
$email = trim($_POST['email'] ?? '');
$clave = $_POST['clave'] ?? '';
$activo = isset($_POST['activo']) ? (int) $_POST['activo'] : 1;

if ($idUsuario <= 0 || !in_array($rol, ['administrador', 'comerciante', 'repartidor'], true)) {
    echo json_encode(['status' => false, 'msg' => 'Datos no válidos.']);
    exit;
}

if (mb_strlen($nombreCompleto) < 5) {
    echo json_encode(['status' => false, 'msg' => 'El nombre debe tener al menos 5 caracteres.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => false, 'msg' => 'El correo electrónico no es válido.']);
    exit;
}

if ($clave !== '' && (strlen($clave) < 8 || !preg_match('/[A-Z]/', $clave) || !preg_match('/[0-9]/', $clave) || !preg_match('/[^A-Za-z0-9]/', $clave))) {
    echo json_encode(['status' => false, 'msg' => 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un carácter especial.']);
    exit;
}

$conexion = conectar_bd();

// Email único, excluyendo al propio usuario que se está editando
$stmtExiste = $conexion->prepare('SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ? LIMIT 1');
$stmtExiste->bind_param('si', $email, $idUsuario);
$stmtExiste->execute();
if ($stmtExiste->get_result()->fetch_assoc()) {
    $stmtExiste->close();
    $conexion->close();
    echo json_encode(['status' => false, 'msg' => 'El correo electrónico ya está en uso por otro usuario.']);
    exit;
}
$stmtExiste->close();

$conexion->begin_transaction();

try {
    if ($clave !== '') {
        $claveHash = password_hash($clave, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare('UPDATE usuarios SET nombre_completo = ?, email = ?, password_hash = ?, activo = ? WHERE id_usuario = ?');
        $stmt->bind_param('sssii', $nombreCompleto, $email, $claveHash, $activo, $idUsuario);
    } else {
        $stmt = $conexion->prepare('UPDATE usuarios SET nombre_completo = ?, email = ?, activo = ? WHERE id_usuario = ?');
        $stmt->bind_param('ssii', $nombreCompleto, $email, $activo, $idUsuario);
    }
    if (!$stmt->execute()) {
        throw new Exception('No se pudo actualizar el usuario: ' . $stmt->error);
    }
    $stmt->close();

    if ($rol === 'comerciante') {
        $razonSocial = trim($_POST['razon_social'] ?? '');
        $ruc = trim($_POST['ruc'] ?? '');
        $rubro = trim($_POST['rubro'] ?? '');
        $direccionFiscal = trim($_POST['direccion_fiscal'] ?? '');

        $stmtPerfil = $conexion->prepare(
            'UPDATE comercios SET razon_social = ?, ruc = ?, rubro = ?, direccion_fiscal = ? WHERE id_usuario = ?'
        );
        $stmtPerfil->bind_param('ssssi', $razonSocial, $ruc, $rubro, $direccionFiscal, $idUsuario);
        if (!$stmtPerfil->execute()) {
            $msg = $stmtPerfil->errno === 1062 ? 'El RUC ya está registrado en otro comercio.' : 'No se pudo actualizar el comercio: ' . $stmtPerfil->error;
            throw new Exception($msg);
        }
        $stmtPerfil->close();
    } elseif ($rol === 'repartidor') {
        $tipoVehiculo = $_POST['tipo_vehiculo'] ?? '';
        $placa = trim($_POST['placa_identificacion'] ?? '');

        $stmtPerfil = $conexion->prepare(
            'UPDATE repartidores SET tipo_vehiculo = ?, placa_identificacion = ? WHERE id_usuario = ?'
        );
        $stmtPerfil->bind_param('ssi', $tipoVehiculo, $placa, $idUsuario);
        if (!$stmtPerfil->execute()) {
            throw new Exception('No se pudo actualizar el repartidor: ' . $stmtPerfil->error);
        }
        $stmtPerfil->close();
    }

    $conexion->commit();
    $response['status'] = true;
    $response['msg'] = 'Usuario actualizado correctamente.';

} catch (Exception $e) {
    $conexion->rollback();
    error_log('Error al actualizar usuario/perfil: ' . $e->getMessage());
    $response['msg'] = $e->getMessage();
}

$conexion->close();
echo json_encode($response);
