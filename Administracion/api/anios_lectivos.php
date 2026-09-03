<?php
/**
 * API: Docente/api/anios_lectivos.php
 *
 * Devuelve todos los años lectivos del sistema ordenados de más reciente
 * a más antiguo, para poblar el select global de año en resumen_alumno.php.
 * El año activo viene marcado con activo = 'Sí'.
 */
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

$anios = buscar_datos("
    SELECT idAnio, anio, activo
    FROM anio_lectivo
    ORDER BY anio DESC
");

if (!$anios) {
    json_response(false, 'No hay años lectivos registrados');
}

json_response(true, 'OK', $anios);
