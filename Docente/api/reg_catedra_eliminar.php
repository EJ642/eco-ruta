<?php
/**
 * API: Docente/api/reg_catedra_eliminar.php
 * Elimina un registro de cátedra
 */

if (session_status() === PHP_SESSION_NONE) {
    session_name('DOCENTE_SESSION');
    session_start();
}

// RUTA: ../../servicios/conexion.php
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
$data = json_decode(file_get_contents("php://input"), true);

if ($data === null) {
    echo json_encode(["status" => false, "msg" => "Error al decodificar JSON"]);
    exit;
}

$id = isset($data['id_catedra']) ? (int)$data['id_catedra'] : 0;

// ===============================
// VALIDACIONES
// ===============================
if (!$id) {
    echo json_encode(["status" => false, "msg" => "ID no válido"]);
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
    echo json_encode(["status" => false, "msg" => "No tiene permiso para eliminar este registro"]);
    $conexion->close();
    exit;
}

// ===============================
// ELIMINAR
// ===============================
$sql_delete = "DELETE FROM registro_catedra WHERE idRegCatedra = $id";

if ($conexion->query($sql_delete)) {
    echo json_encode(["status" => true, "msg" => "Registro eliminado correctamente."]);
} else {
    echo json_encode(["status" => false, "msg" => "Error al eliminar: " . $conexion->error]);
}

$conexion->close();
?>