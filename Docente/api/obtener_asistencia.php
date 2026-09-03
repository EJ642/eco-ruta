<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../servicios/conexion.php';
session_name('DOCENTE_SESSION');
session_start();

function json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

if (!isset($_SESSION['docente_id'])) {
    json_response(false, 'No autorizado');
}

$docente_id = (int)$_SESSION['docente_id'];
$idAulaMateria = isset($_GET['idAulaMateria']) ? (int)$_GET['idAulaMateria'] : 0;
$fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';

if (!$idAulaMateria || $idAulaMateria <= 0) {
    json_response(false, 'ID de aula-materia inválido');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    json_response(false, 'Fecha inválida o formato incorrecto');
}

$dateObj = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$dateObj || $dateObj->format('Y-m-d') !== $fecha) {
    json_response(false, 'Fecha inválida');
}

$fecha = limpiar_cadena($fecha);
$sql_check = "SELECT dam.idAsignacion FROM docente_aula_materia dam WHERE dam.idDocente = $docente_id AND dam.idAulaMateria = $idAulaMateria AND dam.activo = 1";
if (!buscar_datos($sql_check)) {
    json_response(false, 'No tiene acceso a esta materia');
}

$sql_header = "SELECT s.idSesion, s.cantidad_horas, s.fecha, m.nombre AS materia, CONCAT(c.numero, '° ', e.nombre) AS curso
               FROM asistencia_sesion s
               JOIN aula_materia am ON s.idAulaMateria = am.idAulaMateria
               JOIN materia m ON am.idMateria = m.idMateria
               JOIN aula a ON am.idAula = a.idAula
               JOIN curso c ON a.idCurso = c.idCurso
               JOIN enfasis e ON a.idEnfasis = e.idEnfasis
               WHERE s.idAulaMateria = $idAulaMateria
                 AND s.fecha = '$fecha'
               LIMIT 1";

$header = buscar_datos($sql_header);
if (!$header) {
    json_response(false, 'No existe la asistencia para la fecha indicada');
}

$idSesion = (int)$header[0]['idSesion'];
$sql_detail = "SELECT m.idMatricula, a.cedula, a.apellidos, a.nombres, d.estado, d.observacion
               FROM asistencia_detalle d
               JOIN matricula m ON d.idMatricula = m.idMatricula
               JOIN alumno a ON m.idAlumno = a.idAlumno
               WHERE d.idSesion = $idSesion
               ORDER BY a.apellidos, a.nombres";

$details = buscar_datos($sql_detail);
if (!$details) {
    $details = [];
}

json_response(true, 'Asistencia encontrada', [
    'cabecera' => $header[0],
    'detalle' => $details
]);
