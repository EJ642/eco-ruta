<?php
/**
 * API: Docente/api/listar_catedra.php
 * Lista los registros de cátedra para DataTables
 */

//Verificar si la sesión ya está iniciada
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
    echo json_encode(["success" => false, "error" => "Sesión no válida"]);
    exit;
}
$docente_id = (int)$_SESSION['docente_id'];

// ===============================
// PARÁMETROS DE FILTRO
// ===============================
$idAsignacion = isset($_GET['idAsignacion']) ? (int)$_GET['idAsignacion'] : 0;
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : null;
$fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : null;

// ===============================
// CONSTRUIR CONSULTA
// ===============================
$sql = "SELECT rc.*, m.nombre as materia, c.nombre as curso, d.nombres, d.apellidos
        FROM registro_catedra rc
        INNER JOIN docente_aula_materia dam ON rc.idAsignacion = dam.idAsignacion
        INNER JOIN docente d ON dam.idDocente = d.idDocente
        INNER JOIN aula_materia am ON dam.idAulaMateria = am.idAulaMateria
        INNER JOIN materia m ON am.idMateria = m.idMateria
        INNER JOIN aula a ON am.idAula = a.idAula
        INNER JOIN curso c ON a.idCurso = c.idCurso
        WHERE dam.idDocente = $docente_id";

// Filtro por asignación
if ($idAsignacion > 0) {
    $sql .= " AND rc.idAsignacion = $idAsignacion";
}

// Filtro por fechas
if ($fechaInicio) {
    $sql .= " AND rc.fecha >= '$fechaInicio'";
}
if ($fechaFin) {
    $sql .= " AND rc.fecha <= '$fechaFin'";
}

$sql .= " ORDER BY rc.fecha DESC, rc.horaInicio DESC";

$resultado = buscar_datos($sql);

// ===============================
// RESPUESTA
// ===============================
echo json_encode([
    "success" => true,
    "data" => $resultado
]);
?>