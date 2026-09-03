<?php
/**
 * API: Docente/api/eliminar_evaluacion.php
 *
 * PROPÓSITO:
 * Eliminar una evaluación SIN notas registradas.
 *
 * ORDEN DE VALIDACIONES:
 * 1. Sesión docente
 * 2. Permiso del docente sobre la evaluación
 * 3. Período permite la acción (abierto o con excepción)
 * 4. La evaluación NO debe tener notas registradas
 *
 * Si tiene notas registradas, NO se elimina (evita perder
 * información y romper cálculos de nota parcial).
 */

session_name('DOCENTE_SESSION');
session_start();
require_once "../../servicios/conexion.php";
require_once "validar_periodo.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['docente_id'])) {
    echo json_encode(["success" => false, "error" => "Sesión no válida"]);
    exit;
}
$docente_id = (int)$_SESSION['docente_id'];

$data = json_decode(file_get_contents("php://input"), true);
$idEvaluacion = isset($data['idEvaluacion']) ? (int)$data['idEvaluacion'] : 0;

if (!$idEvaluacion) {
    echo json_encode(["success" => false, "error" => "Falta el ID de la evaluación"]);
    exit;
}

// ===============================
// 1. VALIDAR PERMISO Y OBTENER DATOS
// ===============================
$sql_verificar = "SELECT e.idEvaluacion, e.idAulaMateria, e.idPeriodo
                  FROM evaluacion e
                  JOIN aula_materia am ON e.idAulaMateria = am.idAulaMateria
                  JOIN docente_aula_materia dam ON am.idAulaMateria = dam.idAulaMateria
                  WHERE e.idEvaluacion = $idEvaluacion
                  AND dam.idDocente = $docente_id
                  AND dam.activo = 1";
$resultado = buscar_datos($sql_verificar);

if (empty($resultado)) {
    echo json_encode(["success" => false, "error" => "No tienes permiso para eliminar esta evaluación"]);
    exit;
}

$idAulaMateria = (int)$resultado[0]['idAulaMateria'];
$idPeriodo     = (int)$resultado[0]['idPeriodo'];

// ===============================
// 2. VALIDAR PERÍODO (abierto o con excepción)
// ===============================
$chkPeriodo = puede_cargar_notas($idAulaMateria, $idPeriodo);
if (!$chkPeriodo['permitido']) {
    echo json_encode(["success" => false, "error" => $chkPeriodo['motivo']]);
    exit;
}

// ===============================
// 3. VALIDAR QUE NO TENGA NOTAS REGISTRADAS
// ===============================
$sql_existe = "SELECT idNota FROM nota WHERE idEvaluacion = $idEvaluacion LIMIT 1";
$resultado_existe = buscar_datos($sql_existe);

if ($resultado_existe) {
    echo json_encode([
        "success" => false,
        "error" => "No se puede eliminar la evaluación porque tiene notas registradas"
    ]);
    exit;
}

// ===============================
// 4. ELIMINAR
// ===============================
try {
    $sql_eliminar_eval = "DELETE FROM evaluacion WHERE idEvaluacion = $idEvaluacion";

    if (eliminar_datos($sql_eliminar_eval)) {
        echo json_encode(["success" => true, "message" => "Evaluación eliminada correctamente"]);
    } else {
        echo json_encode(["success" => false, "error" => "Error al eliminar la evaluación"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Error: " . $e->getMessage()]);
}
