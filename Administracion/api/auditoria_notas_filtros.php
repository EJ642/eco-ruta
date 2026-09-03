<?php
/**
 * API: Administracion/auditoria_notas_filtros.php
 *
 * Devuelve las opciones para poblar los selects de filtro de la vista de
 * auditoría: cursos/énfasis del año activo, y la lista de usuarios que
 * tienen al menos un registro en auditoria_nota (para el filtro "Docente").
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

// Año activo
$resAnio = buscar_datos("SELECT idAnio FROM anio_lectivo WHERE activo = 'Sí' LIMIT 1");
$idAnio  = $resAnio ? (int)$resAnio[0]['idAnio'] : 0;

// Cursos/énfasis del año activo
$cursos = [];
if ($idAnio > 0) {
    $cursos = buscar_datos("
        SELECT a.idAula, CONCAT(c.numero, '° ', e.nombre) AS curso
        FROM aula a
        JOIN curso c   ON c.idCurso = a.idCurso
        JOIN enfasis e ON e.idEnfasis = a.idEnfasis
        WHERE a.idAnio = $idAnio AND a.activo = 'Sí'
        ORDER BY c.numero, e.nombre
    ") ?: [];
}

// Docentes que tienen al menos un registro de auditoría (evita listar a todo el personal)
$docentes = buscar_datos("
    SELECT DISTINCT u.idUsuario, d.nombres, d.apellidos, u.usuario
    FROM auditoria_nota an
    JOIN usuarios u ON u.idUsuario = an.idUsuario
    LEFT JOIN docente d ON d.idUsuario = u.idUsuario
    ORDER BY d.apellidos, d.nombres
") ?: [];

$docentesFmt = array_map(function($d) {
    $nombre = !empty($d['nombres']) ? trim($d['apellidos'] . ', ' . $d['nombres']) : $d['usuario'];
    return ['idUsuario' => (int)$d['idUsuario'], 'nombre' => $nombre];
}, $docentes);

json_response(true, 'OK', [
    'cursos'   => $cursos,
    'docentes' => $docentesFmt,
]);
