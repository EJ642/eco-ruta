<?php
/**
 * API: Docente/api/obtener_calificaciones.php
 * 
 * PROPÓSITO:
 * Obtener todas las calificaciones registradas para una evaluación específica.
 * Verifica si existen registros previos de calificaciones.
 * 
 * MÉTODO: GET
 * 
 * PARÁMETROS:
 * - idEvaluacion: ID de la evaluación (integer)
 * 
 * RETORNA:
 * JSON: {
 *   "success": true,
 *   "data": {
 *     "evaluacion": { idEvaluacion, nombre, puntos_total, ... },
 *     "calificaciones": [ { idMatricula, cedula, apellidos, nombres, puntos_obtenidos, observacion }, ... ]
 *   },
 *   "message": "Calificaciones encontradas"
 * }
 * 
 * O si no existe:
 * {
 *   "success": false,
 *   "message": "No hay calificaciones para esta evaluación"
 * }
 */

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
$idEvaluacion = isset($_GET['idEvaluacion']) ? (int)$_GET['idEvaluacion'] : 0;

if (!$idEvaluacion || $idEvaluacion <= 0) {
    json_response(false, 'ID de evaluación inválido');
}

// Verificar que el docente tiene acceso a esta evaluación
$sql_check = "SELECT e.idEvaluacion, e.nombre, e.puntos_total, am.idAulaMateria, am.idAula, 
                     m.nombre as materia,
                     CONCAT(c.numero, '° ', en.nombre) as curso
              FROM evaluacion e
              JOIN aula_materia am ON e.idAulaMateria = am.idAulaMateria
              JOIN aula a ON am.idAula = a.idAula
              JOIN curso c ON a.idCurso = c.idCurso
              JOIN enfasis en ON a.idEnfasis = en.idEnfasis
              JOIN materia m ON am.idMateria = m.idMateria
              JOIN docente_aula_materia dam ON am.idAulaMateria = dam.idAulaMateria
              WHERE e.idEvaluacion = $idEvaluacion
                AND dam.idDocente = $docente_id
                AND dam.activo = 1
              LIMIT 1";

$eval_data = buscar_datos($sql_check);
if (!$eval_data) {
    json_response(false, 'No tiene acceso a esta evaluación');
}

$evaluacion = $eval_data[0];

// Obtener calificaciones registradas
$sql_calificaciones = "SELECT n.idMatricula, n.puntos_obtenidos, n.observacion,
                              a.cedula, a.apellidos, a.nombres
                       FROM nota n
                       JOIN matricula m ON n.idMatricula = m.idMatricula
                       JOIN alumno a ON m.idAlumno = a.idAlumno
                       WHERE n.idEvaluacion = $idEvaluacion
                       ORDER BY a.apellidos, a.nombres";

$calificaciones = buscar_datos($sql_calificaciones);

if (!$calificaciones || count($calificaciones) === 0) {
    json_response(true, 'Sin calificaciones registradas', [
        'evaluacion' => $evaluacion,
        'calificaciones' => [],
        'existe' => false
    ]);
}

json_response(true, 'Calificaciones encontradas', [
    'evaluacion' => $evaluacion,
    'calificaciones' => $calificaciones,
    'existe' => true
]);
