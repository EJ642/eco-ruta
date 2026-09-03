<?php
/**
 * API: Docente/api/reg_catedra_actualizar.php
 * Actualiza un registro de cátedra existente
 */

if (session_status() === PHP_SESSION_NONE) {
    session_name('DOCENTE_SESSION');
    session_start();
}

//RUTA : ../../servicios/conexion.php
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
$id = isset($_POST['id_catedra']) ? (int)$_POST['id_catedra'] : 0;
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
if (!$id || !$idAsignacion || !$fecha || !$horaInicio || !$horaFin || !$unidad || !$tema) {
    echo json_encode(["status" => false, "msg" => "Todos los campos obligatorios deben ser llenados"]);
    exit;
}

if ($horaInicio >= $horaFin) {
    echo json_encode(["status" => false, "msg" => "La hora de inicio debe ser menor que la hora de fin"]);
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
$sql_verificar = "SELECT rc.idRegCatedra 
                  FROM registro_catedra rc
                  INNER JOIN docente_aula_materia dam ON rc.idAsignacion = dam.idAsignacion
                  WHERE rc.idRegCatedra = $id AND dam.idDocente = $docente_id";

$resultado = $conexion->query($sql_verificar);

if ($resultado === false) {
    echo json_encode(["status" => false, "msg" => "Error en consulta: " . $conexion->error]);
    $conexion->close();
    exit;
}

if ($resultado->num_rows === 0) {
    echo json_encode(["status" => false, "msg" => "No tiene permiso para editar este registro"]);
    $conexion->close();
    exit;
}

// ===============================
// VERIFICAR NUEVA ASIGNACIÓN
// ===============================
$sql_verificar_asig = "SELECT idAsignacion FROM docente_aula_materia 
                       WHERE idAsignacion = $idAsignacion AND idDocente = $docente_id AND activo = 1";

$resultado_asig = $conexion->query($sql_verificar_asig);

if ($resultado_asig === false) {
    echo json_encode(["status" => false, "msg" => "Error en consulta: " . $conexion->error]);
    $conexion->close();
    exit;
}

if ($resultado_asig->num_rows === 0) {
    echo json_encode(["status" => false, "msg" => "La asignación seleccionada no es válida"]);
    $conexion->close();
    exit;
}

// ===============================
// LIMPIAR DATOS Y ACTUALIZAR
// ===============================
$unidad = limpiar_cadena($unidad);
$tema = limpiar_cadena($tema);
$observaciones = limpiar_cadena($observaciones);

$sql_update = "UPDATE registro_catedra 
               SET idAsignacion = $idAsignacion, fecha = '$fecha', horaInicio = '$horaInicio', 
                   horaFin = '$horaFin', unidad = '$unidad', tema = '$tema', observaciones = '$observaciones'
               WHERE idRegCatedra = $id";

if ($conexion->query($sql_update)) {
    echo json_encode(["status" => true, "msg" => "Registro actualizado correctamente."]);
} else {
    echo json_encode(["status" => false, "msg" => "Error al actualizar: " . $conexion->error]);
}

$conexion->close();
?>