<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../servicios/conexion.php';
session_name('ADMIN_SESSION');
session_start();

function json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// if (empty($_SESSION['active']) || $_SESSION['rol'] !== 'Docente') {
//     json_response(false, 'No autorizado');
// }

// $docente_id = (int)($_SESSION['docente_id'] ?? 0);
// if ($docente_id <= 0) json_response(false, 'Docente no identificado');

$idAula = isset($_GET['idAula']) ? (int)$_GET['idAula'] : 0;
if ($idAula <= 0) json_response(false, 'idAula invalido');

// $tieneAcceso = buscar_datos("
//     SELECT dam.idAsignacion
//     FROM docente_aula_materia dam
//     JOIN aula_materia am ON am.idAulaMateria = dam.idAulaMateria
//     WHERE dam.idDocente = $docente_id
//       AND dam.activo = 1
//       AND am.idAula = $idAula
//     LIMIT 1
// ");
// if (!$tieneAcceso) json_response(false, 'No tienes acceso a este curso');

$alumnos = buscar_datos("
    SELECT a.idAlumno, a.cedula,
           CONCAT(a.apellidos, ', ', a.nombres) AS nombre_completo,
           a.nombres, a.apellidos,
           m.idMatricula, m.estado AS estadoMatricula
    FROM matricula m
    JOIN alumno a ON a.idAlumno = m.idAlumno
    WHERE m.idAula = $idAula
      AND m.estado = 'Vigente'
      AND a.estado = 'Activo'
    ORDER BY a.apellidos, a.nombres
");

json_response(true, 'OK', $alumnos ?: []);
