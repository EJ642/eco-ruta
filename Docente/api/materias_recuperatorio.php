<?php
header('Content-Type: application/json');
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

// Año activo
$resAnio = buscar_datos("SELECT idAnio FROM anio_lectivo WHERE activo = 'Sí' LIMIT 1");
if (!$resAnio) json_response(false, 'No hay año lectivo activo');
$idAnio = (int)$resAnio[0]['idAnio'];

// Materias del docente que tienen AL MENOS UN alumno en recuperatorio
$materias = buscar_datos("
    SELECT DISTINCT am.idAulaMateria,
           mat.nombre AS materia,
           CONCAT(c.numero, '° Curso - ', e.nombre) AS curso,
           COUNT(nf.idNotaFinal) AS total_pendientes
    FROM docente_aula_materia dam
    JOIN aula_materia am  ON am.idAulaMateria  = dam.idAulaMateria
    JOIN materia mat       ON mat.idMateria      = am.idMateria
    JOIN aula au           ON au.idAula          = am.idAula
    JOIN curso c           ON c.idCurso          = au.idCurso
    JOIN enfasis e         ON e.idEnfasis        = au.idEnfasis
    JOIN nota_final_materia nf ON nf.idAulaMateria = am.idAulaMateria
    WHERE dam.idDocente = $docente_id
      AND dam.activo = 1
      AND au.idAnio  = $idAnio
      AND nf.estado IN ('Recuperatorio1', 'Recuperatorio2')
    GROUP BY am.idAulaMateria
    ORDER BY c.numero, mat.nombre
");

json_response(true, 'OK', $materias ?: []);
?>
