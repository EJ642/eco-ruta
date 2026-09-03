<?php
/**
 * API: Docente/api/actualizar_usuario.php
 * Actualiza el nombre de usuario y/o contraseña del docente logueado
 */
session_start();
require_once "../../servicios/conexion.php";

header('Content-Type: application/json; charset=utf-8');

// Verificar sesión
if (!isset($_SESSION['docente_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$docente_id = (int)$_SESSION['docente_id'];

// Obtener datos enviados
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
    exit;
}

$nuevoUsuario = trim($input['usuario'] ?? '');
$contrasenaActual = $input['contrasena_actual'] ?? '';
$contrasenaNueva = $input['contrasena_nueva'] ?? '';

// Validaciones
if (empty($nuevoUsuario)) {
    echo json_encode(['success' => false, 'message' => 'El usuario no puede estar vacío']);
    exit;
}

if (strlen($nuevoUsuario) < 5) {
    echo json_encode(['success' => false, 'message' => 'El usuario debe tener al menos 5 caracteres']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $nuevoUsuario)) {
    echo json_encode(['success' => false, 'message' => 'El usuario solo puede contener letras, números y guión bajo']);
    exit;
}

// Obtener el idUsuario del docente
$sqlDocente = "SELECT idUsuario FROM docente WHERE idDocente = $docente_id";
$resultDocente = buscar_datos($sqlDocente);

if (!$resultDocente) {
    echo json_encode(['success' => false, 'message' => 'Docente no encontrado']);
    exit;
}

$idUsuario = (int)$resultDocente[0]['idUsuario'];

// Verificar que el usuario no esté tomado por otro
$sqlCheck = "SELECT idUsuario FROM usuarios WHERE usuario = '$nuevoUsuario' AND idUsuario != $idUsuario";
$resultCheck = buscar_datos($sqlCheck);

if ($resultCheck && count($resultCheck) > 0) {
    echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está en uso']);
    exit;
}

// Iniciar transacción
$conexion = conectar_bd();
$conexion->begin_transaction();

try {
    // Construir la consulta de actualización
    $sql = "UPDATE usuarios SET usuario = '$nuevoUsuario', modificado = NOW() WHERE idUsuario = $idUsuario";
    
    // Si se envió contraseña nueva, validar y actualizar
    if (!empty($contrasenaNueva) || !empty($contrasenaActual)) {
        // Validar que ambas estén presentes
        if (empty($contrasenaActual)) {
            throw new Exception('Debe ingresar su contraseña actual para cambiarla', 401);
        }
        
        if (empty($contrasenaNueva)) {
            throw new Exception('Debe ingresar una nueva contraseña', 400);
        }
        
        if (
            strlen($contrasenaNueva) < 8 ||
            !preg_match('/[A-Z]/', $contrasenaNueva) ||
            !preg_match('/[0-9]/', $contrasenaNueva) ||
            !preg_match('/[^A-Za-z0-9]/', $contrasenaNueva)
        ) {
            throw new Exception(
                'La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un carácter especial.',
                400
            );
        }
        
        // Obtener contraseña actual
        $sqlPass = "SELECT password FROM usuarios WHERE idUsuario = $idUsuario";
        $resultPass = buscar_datos($sqlPass);
        
        if (!$resultPass) {
            throw new Exception('Usuario no encontrado', 404);
        }
        
        // Verificar contraseña actual
        if (!password_verify($contrasenaActual, $resultPass[0]['password'])) {
            throw new Exception('Contraseña actual incorrecta', 401);
        }
        
        // Hashear nueva contraseña
        $hash = password_hash($contrasenaNueva, PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET usuario = '$nuevoUsuario', password = '$hash', modificado = NOW() WHERE idUsuario = $idUsuario";
    }
    
    // Ejecutar actualización
    if (!actualizar_datos($sql)) {
        throw new Exception('Error al actualizar los datos', 500);
    }
    
    // Registrar en auditoría
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $sqlAudit = "INSERT INTO auditoria_usuario 
                (idUsuario_afectado, accion, idUsuario_ejecutor, ip, detalle) 
                VALUES ($idUsuario, 'MODIFICAR', $idUsuario, '$ip', 'Usuario actualizado')";
    actualizar_datos($sqlAudit);
    
    // Confirmar transacción
    $conexion->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Configuración actualizada correctamente',
        'usuario_actualizado' => $nuevoUsuario
    ]);
    
} catch (Exception $e) {
    $conexion->rollback();
    
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    // Si es error de contraseña incorrecta, agregar código especial
    if ($e->getCode() === 401 && strpos($e->getMessage(), 'incorrecta') !== false) {
        $response['code'] = 'PASS_INCORRECT';
    }
    
    echo json_encode($response);
} finally {
    $conexion->close();
}