<?php
require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['administrador']);
require_once __DIR__ . '/../servicios/conexion.php';

$response = ['status' => false, 'msg' => ''];

if (empty($_POST)) {
    echo json_encode(['status' => false, 'msg' => 'No se recibieron datos.']);
    exit;
}

$tipoRegistro = limpiar_cadena($_POST['tipo_registro'] ?? '');
$nombreCompleto = trim($_POST['nombre_completo'] ?? '');
$email = trim($_POST['email'] ?? '');
$clave = $_POST['clave'] ?? '';

if (!in_array($tipoRegistro, ['administrador', 'comerciante', 'repartidor'], true)) {
    echo json_encode(['status' => false, 'msg' => 'Tipo de registro no válido.']);
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

// Validación de contraseña también del lado del servidor (la del cliente se puede saltear)
if (strlen($clave) < 8 || !preg_match('/[A-Z]/', $clave) || !preg_match('/[0-9]/', $clave) || !preg_match('/[^A-Za-z0-9]/', $clave)) {
    echo json_encode(['status' => false, 'msg' => 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un carácter especial.']);
    exit;
}

$conexion = conectar_bd();

// Verificar unicidad de email antes de abrir la transacción
$stmtExiste = $conexion->prepare('SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1');
$stmtExiste->bind_param('s', $email);
$stmtExiste->execute();
if ($stmtExiste->get_result()->fetch_assoc()) {
    $stmtExiste->close();
    $conexion->close();
    echo json_encode(['status' => false, 'msg' => 'El correo electrónico ya está registrado.']);
    exit;
}
$stmtExiste->close();

// Datos específicos según el tipo de registro
if ($tipoRegistro === 'comerciante') {
    $razonSocial = trim($_POST['razon_social'] ?? '');
    $ruc = trim($_POST['ruc'] ?? '');
    $rubro = trim($_POST['rubro'] ?? '');
    $direccionFiscal = trim($_POST['direccion_fiscal'] ?? '');

    if ($razonSocial === '' || $ruc === '') {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'Razón social y RUC son obligatorios.']);
        exit;
    }
} elseif ($tipoRegistro === 'repartidor') {
    $tipoVehiculo = $_POST['tipo_vehiculo'] ?? '';
    $placa = trim($_POST['placa_identificacion'] ?? '');

    if (!in_array($tipoVehiculo, ['bicicleta', 'vehiculo_electrico'], true)) {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'Seleccione un tipo de vehículo válido.']);
        exit;
    }
}

$claveHash = password_hash($clave, PASSWORD_DEFAULT);

$conexion->begin_transaction();

try {
    // 1. Insertar usuario (id_rol se busca por nombre, no hardcodeado)
    $stmtUsuario = $conexion->prepare(
        'INSERT INTO usuarios (id_rol, nombre_completo, email, password_hash, activo)
         VALUES ((SELECT id_rol FROM roles WHERE nombre_rol = ?), ?, ?, ?, 1)'
    );
    $stmtUsuario->bind_param('ssss', $tipoRegistro, $nombreCompleto, $email, $claveHash);
    if (!$stmtUsuario->execute()) {
        throw new Exception('No se pudo crear el usuario: ' . $stmtUsuario->error);
    }
    $idUsuario = $conexion->insert_id;
    $stmtUsuario->close();

    // 2. Insertar el perfil específico, si corresponde
    if ($tipoRegistro === 'comerciante') {
        $stmtPerfil = $conexion->prepare(
            'INSERT INTO comercios (id_usuario, razon_social, ruc, direccion_fiscal, rubro)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmtPerfil->bind_param('issss', $idUsuario, $razonSocial, $ruc, $direccionFiscal, $rubro);
        if (!$stmtPerfil->execute()) {
            // errno 1062 = entrada duplicada (ej. RUC ya registrado, columna UNIQUE)
            $msg = $stmtPerfil->errno === 1062 ? 'El RUC ya está registrado.' : 'No se pudo crear el comercio: ' . $stmtPerfil->error;
            throw new Exception($msg);
        }
        $stmtPerfil->close();
    } elseif ($tipoRegistro === 'repartidor') {
        $stmtPerfil = $conexion->prepare(
            'INSERT INTO repartidores (id_usuario, tipo_vehiculo, placa_identificacion, disponible)
             VALUES (?, ?, ?, 1)'
        );
        $stmtPerfil->bind_param('iss', $idUsuario, $tipoVehiculo, $placa);
        if (!$stmtPerfil->execute()) {
            throw new Exception('No se pudo crear el repartidor: ' . $stmtPerfil->error);
        }
        $stmtPerfil->close();
    }

    $conexion->commit();
    $response['status'] = true;
    $response['msg'] = ucfirst($tipoRegistro) . ' creado correctamente.';

} catch (Exception $e) {
    $conexion->rollback();
    error_log('Error al crear usuario/perfil: ' . $e->getMessage());
    $response['msg'] = $e->getMessage();
}

$conexion->close();
echo json_encode($response);
