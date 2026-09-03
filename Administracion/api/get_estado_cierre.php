<?php
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

// Solo Director
if (empty($_SESSION['active']) || $_SESSION['rol'] !== 'Director') {
    json_response(false, 'No autorizado. Solo el Director puede realizar esta acción.');
}

// Año activo
$resAnio = buscar_datos("SELECT idAnio, anio FROM anio_lectivo WHERE activo = 'Sí' LIMIT 1");
if (!$resAnio) json_response(false, 'No hay año lectivo activo');
$anio   = $resAnio[0];
$idAnio = (int)$anio['idAnio'];

// Períodos del año
$periodos = buscar_datos("
    SELECT idPeriodo, nombre, numero, fecha_inicio, fecha_fin, activo
    FROM periodo WHERE idAnio = $idAnio ORDER BY numero
");
if (!$periodos) json_response(false, 'No hay períodos configurados para este año');

$resumen = [];
foreach ($periodos as $per) {
    $idPeriodo = (int)$per['idPeriodo'];

    $resEval = buscar_datos("SELECT COUNT(*) AS total FROM evaluacion WHERE idPeriodo = $idPeriodo");
    $totalEval = $resEval ? (int)$resEval[0]['total'] : 0;

    $resNotas = buscar_datos("
        SELECT COUNT(*) AS total FROM nota n
        JOIN evaluacion ev ON ev.idEvaluacion = n.idEvaluacion
        WHERE ev.idPeriodo = $idPeriodo
    ");
    $totalNotas = $resNotas ? (int)$resNotas[0]['total'] : 0;

    $resumen[] = [
        'idPeriodo'  => $idPeriodo,
        'nombre'     => $per['nombre'],
        'numero'     => (int)$per['numero'],
        'cerrado'    => $per['activo'] === 'No',
        'totalEval'  => $totalEval,
        'totalNotas' => $totalNotas,
    ];
}

// Recuperatorios pendientes
$todosCerrados = !in_array('Sí', array_column($periodos, 'activo'));
$recPendientes = 0;
if ($todosCerrados) {
    $resRec = buscar_datos("
        SELECT COUNT(*) AS total FROM nota_final_materia
        WHERE estado IN ('Recuperatorio1','Recuperatorio2')
    ");
    $recPendientes = $resRec ? (int)$resRec[0]['total'] : 0;
}

// Año ya cerrado si hay matrículas con Promovido/Reprobado
$resAnioEstado = buscar_datos("
    SELECT COUNT(*) AS total FROM matricula
    WHERE idAula IN (SELECT idAula FROM aula WHERE idAnio = $idAnio)
      AND estado IN ('Promovido','Reprobado')
");
$anioCerrado = $resAnioEstado && (int)$resAnioEstado[0]['total'] > 0;

json_response(true, 'OK', [
    'anio'          => $anio,
    'periodos'      => $resumen,
    'todosCerrados' => $todosCerrados,
    'recPendientes' => $recPendientes,
    'anioCerrado'   => $anioCerrado,
]);
