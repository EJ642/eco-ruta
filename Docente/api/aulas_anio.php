<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../servicios/conexion.php';
session_name('DOCENTE_SESSION');
session_start();

function json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['active']) || $_SESSION['rol'] !== 'Docente') {
    json_response(false, 'No autorizado');
}

$docente_id = (int)($_SESSION['docente_id'] ?? 0);
if ($docente_id <= 0) json_response(false, 'Docente no identificado');

if (!empty($_GET['idAnio'])) {
    $idAnio  = (int)$_GET['idAnio'];
    $rowAnio = buscar_datos("SELECT idAnio, anio FROM anio_lectivo WHERE idAnio = $idAnio LIMIT 1");
} else {
    $rowAnio = buscar_datos("SELECT idAnio, anio FROM anio_lectivo WHERE activo = 'Sí' LIMIT 1");
}

if (!$rowAnio) json_response(false, 'No hay año lectivo activo');

$anio   = $rowAnio[0];
$idAnio = (int)$anio['idAnio'];

$aulas = buscar_datos("
    SELECT DISTINCT au.idAula,
           CONCAT(c.numero, '° Curso - ', e.nombre) AS label,
           c.numero, c.nombre AS nombreCurso, e.nombre AS enfasis
    FROM docente_aula_materia dam
    JOIN aula_materia am ON am.idAulaMateria = dam.idAulaMateria
    JOIN aula au ON au.idAula = am.idAula
    JOIN curso c ON c.idCurso = au.idCurso
    JOIN enfasis e ON e.idEnfasis = au.idEnfasis
    WHERE dam.idDocente = $docente_id
      AND dam.activo = 1
      AND au.idAnio = $idAnio
      AND au.activo = 'Sí'
    ORDER BY c.numero, e.nombre
");

echo json_encode([
    'success' => true,
    'message' => 'OK',
    'anio'    => $anio,
    'data'    => $aulas ?: [],
], JSON_UNESCAPED_UNICODE);
