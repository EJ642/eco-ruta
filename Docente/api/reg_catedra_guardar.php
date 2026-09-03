<?php
/**
 * API: Docente/api/reg_catedra_guardar.php
 * Crea un nuevo registro de cátedra
 */

// Verificar si la sesión ya está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_name('DOCENTE_SESSION');
    session_start();
}


require_once "../../servicios/conexion.php";

header('Content-Type: application/json; charset=utf-8');

// ===============================
// VALIDAR SESIÓN DOCENTE
// ===============================
if (!isset($_SESSION['docente_id'])) {
    echo json_encode(["status" => false, "msg" => "Sesión no válida"]);
    exit;
}
$docente_id = (int)$_SESSION['docente_id'];

// ===============================
// RECIBIR DATOS
// ===============================
$idAsignacion = isset($_POST['idAsignacion']) ? (int)$_POST['idAsignacion'] : 0;
$fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
$horaInicio = isset($_POST['horaInicio']) ? trim($_POST['horaInicio']) : '';
$horaFin = isset($_POST['horaFin']) ? trim($_POST['horaFin']) : '';
$unidad = isset($_POST['unidad']) ? trim($_POST['unidad']) : '';
$tema = isset($_POST['tema']) ? trim($_POST['tema']) : '';
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

// ===============================
// VALIDACIONES
// ===============================
if (!$idAsignacion || !$fecha || !$horaInicio || !$horaFin || !$unidad || !$tema) {
    echo json_encode(["status" => false, "msg" => "Todos los campos obligatorios deben ser llenados"]);
    exit;
}

if ($horaInicio >= $horaFin) {
    echo json_encode(["status" => false, "msg" => "La hora de inicio debe ser menor que la hora de fin"]);
    exit;
}

// Validar que la fecha no sea futura
$fecha_actual = date('Y-m-d');
if ($fecha > $fecha_actual) {
    echo json_encode(["status" => false, "msg" => "No puede registrar clases en fechas futuras"]);
    exit;
}

// ===============================
// CONEXIÓN
// ===============================
$conexion = conectar_bd();
if (!$conexion) {
    echo json_encode(["status" => false, "msg" => "Error de conexión a la base de datos"]);
    exit;
}

// ===============================
// VERIFICAR PERMISO DEL DOCENTE
// ===============================
$sql_verificar = "SELECT idAsignacion FROM docente_aula_materia 
                  WHERE idAsignacion = $idAsignacion AND idDocente = $docente_id AND activo = 1";

$resultado = $conexion->query($sql_verificar);

if ($resultado === false) {
    echo json_encode(["status" => false, "msg" => "Error en consulta: " . $conexion->error]);
    $conexion->close();
    exit;
}

if ($resultado->num_rows === 0) {
    echo json_encode(["status" => false, "msg" => "No tiene permiso para registrar en esta asignación"]);
    $conexion->close();
    exit;
}

// ===============================
// VERIFICAR DUPLICADO
// ===============================
$sql_duplicado = "SELECT idRegCatedra FROM registro_catedra 
                  WHERE idAsignacion = $idAsignacion AND fecha = '$fecha'";

$resultado_dup = $conexion->query($sql_duplicado);

if ($resultado_dup === false) {
    echo json_encode(["status" => false, "msg" => "Error al verificar duplicado: " . $conexion->error]);
    $conexion->close();
    exit;
}

if ($resultado_dup->num_rows > 0) {
    echo json_encode(["status" => false, "msg" => "Ya existe un registro para esta asignación en esa fecha"]);
    $conexion->close();
    exit;
}

// ===============================
// LIMPIAR DATOS
// ===============================
$unidad = limpiar_cadena($unidad);
$tema = limpiar_cadena($tema);
$observaciones = limpiar_cadena($observaciones);

// ===============================
// INSERTAR
// ===============================
$sql_insert = "INSERT INTO registro_catedra (idAsignacion, fecha, horaInicio, horaFin, unidad, tema, observaciones) 
               VALUES ($idAsignacion, '$fecha', '$horaInicio', '$horaFin', '$unidad', '$tema', '$observaciones')";

if ($conexion->query($sql_insert)) {
    echo json_encode(["status" => true, "msg" => "Cátedra registrada correctamente."]);
} else {
    echo json_encode(["status" => false, "msg" => "Error al guardar: " . $conexion->error]);
}

$conexion->close();
?>