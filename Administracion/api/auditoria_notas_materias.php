<?php
/**
 * API: Administracion/auditoria_notas_materias.php
 *
 * Devuelve las materias de un curso específico, para la cascada del filtro
 * Curso -> Materia en la vista de auditoría. A diferencia de
 * Docente/api/materias_docente.php, esta no filtra por docente: el Director
 * necesita ver todas las materias del curso, sin importar quién las dicta.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../servicios/conexion.php';
session_name('ADMIN_SESSION');
session_start();

function json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['active']) || $_SESSION['rol'] !== 'Director') {
    json_response(false, 'No autorizado.');
}

$idAula = isset($_GET['idAula']) ? (int)$_GET['idAula'] : 0;
if ($idAula <= 0) json_response(false, 'idAula inválido');

$materias = buscar_datos("
    SELECT am.idAulaMateria, m.nombre AS materia
    FROM aula_materia am
    JOIN materia m ON m.idMateria = am.idMateria
    WHERE am.idAula = $idAula AND am.activo = 'Sí'
    ORDER BY m.nombre
") ?: [];

json_response(true, 'OK', $materias);
