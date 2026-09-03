<?php
/**
 * API: Docente/api/historial_calificaciones.php
 * 
 * PROPÓSITO:
 * Obtener el historial de calificaciones registradas con filtros por
 * curso, materia, evaluación, período y rango de fechas.
 * 
 * MÉTODO: GET
 * 
 * PARÁMETROS (todos opcionales):
 * - idAula: Filtrar por aula
 * - idAulaMateria: Filtrar por materia
 * - idEvaluacion: Filtrar por evaluación
 * - desde: Fecha inicio (Y-m-d)
 * - hasta: Fecha fin (Y-m-d)
 * 
 * RETORNA:
 * JSON: {
 *   "success": true,
 *   "data": [
 *     {
 *       "idEvaluacion": number,
 *       "evaluacion": "Nombre Evaluación",
 *       "curso": "2° A",
 *       "materia": "Matemática",
 *       "fecha_creacion": "2024-05-20",
 *       "total_alumnos": number,
 *       "calificados": number,
 *       "pendientes": number
 *     },
 *     ...
 *   ]
 * }
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../servicios/conexion.php';
session_name('DOCENTE_SESSION');
session_start();

if (!isset($_SESSION['docente_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$docente_id = (int)$_SESSION['docente_id'];
$idAula = isset($_GET['idAula']) ? (int)$_GET['idAula'] : 0;
$idAulaMateria = isset($_GET['idAulaMateria']) ? (int)$_GET['idAulaMateria'] : 0;
$idEvaluacion = isset($_GET['idEvaluacion']) ? (int)$_GET['idEvaluacion'] : 0;
$desde = isset($_GET['desde']) ? trim($_GET['desde']) : '';
$hasta = isset($_GET['hasta']) ? trim($_GET['hasta']) : '';

// ── OBTENER AÑO ACTIVO ──────────────────────────────────
$sql_anio = "SELECT idAnio FROM anio_lectivo WHERE activo = 'Sí' LIMIT 1";
$anio_activo = buscar_datos($sql_anio);
$idAnio = $anio_activo[0]['idAnio'] ?? 0;

// ── OBTENER PERÍODO ACTIVO ──────────────────────────────────
// Buscar período activo O con excepción para esta materia
$condPeriodo = $idAulaMateria > 0
    ? "AND (p.activo = 'Sí' OR EXISTS (
            SELECT 1 FROM periodo_excepcion pe
            WHERE pe.idPeriodo = p.idPeriodo
              AND pe.idAulaMateria = $idAulaMateria
       ))"
    : "AND p.activo = 'Sí'";

$sql_periodo = "SELECT p.idPeriodo, p.fecha_inicio, p.fecha_fin
                FROM periodo p
                WHERE p.idAnio = $idAnio
                $condPeriodo
                ORDER BY p.numero DESC LIMIT 1";
$periodo_activo = buscar_datos($sql_periodo);

if (!$periodo_activo) {
    echo json_encode(['success' => true, 'data' => [], 'message' => 'No hay período activo ni con excepción']);
    exit;
}

/*
if ($idAnio == 0 || !$periodo_activo) {
    echo json_encode(['success' => true, 'data' => [], 'message' => 'No hay año o período activo']);
    exit;
}*/

$idPeriodo = $periodo_activo[0]['idPeriodo'];
$fecha_inicio_periodo = $periodo_activo[0]['fecha_inicio'];
$fecha_fin_periodo    = $periodo_activo[0]['fecha_fin'];

// ── SI NO VIENEN FECHAS, USAR LAS DEL PERÍODO ACTIVO ──
if (empty($desde)) {
    $desde = $fecha_inicio_periodo;
}
if (empty($hasta)) {
    $hasta = $fecha_fin_periodo;
}

// ── VALIDAR FECHAS ──────────────────────────────────
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
    $desde = $fecha_inicio_periodo;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
    $hasta = $fecha_fin_periodo;
}

$desde = limpiar_cadena($desde);
$hasta = limpiar_cadena($hasta);

// ── CONSTRUIR QUERY ──────────────────────────────────
$sql = "SELECT e.idEvaluacion, 
                e.nombre as evaluacion, 
                e.creado as fecha_creacion,
                CONCAT(c.numero, '° ', en.nombre) as curso,
                m.nombre as materia,
                am.idAulaMateria,
                (SELECT COUNT(*) FROM matricula mat 
                 WHERE mat.idAula = a.idAula AND mat.estado = 'Vigente') as total_alumnos,
                (SELECT COUNT(*) FROM nota n WHERE n.idEvaluacion = e.idEvaluacion) as calificados
        FROM evaluacion e
        JOIN aula_materia am ON e.idAulaMateria = am.idAulaMateria
        JOIN aula a ON am.idAula = a.idAula
        JOIN materia m ON am.idMateria = m.idMateria
        JOIN curso c ON a.idCurso = c.idCurso
        JOIN enfasis en ON a.idEnfasis = en.idEnfasis
        JOIN docente_aula_materia dam ON am.idAulaMateria = dam.idAulaMateria
        JOIN anio_lectivo an ON a.idAnio = an.idAnio
        WHERE dam.idDocente = $docente_id 
          AND dam.activo = 1
          AND an.activo = 'Sí'
          AND (
                e.idPeriodo = $idPeriodo
                OR EXISTS (
                    SELECT 1 FROM periodo_excepcion pe
                    WHERE pe.idPeriodo = e.idPeriodo
                    AND pe.idAulaMateria = am.idAulaMateria
                )
            )
          AND (
    EXISTS (
        SELECT 1 FROM nota n2
        WHERE n2.idEvaluacion = e.idEvaluacion
          AND (DATE(n2.fecha_registro) BETWEEN '$desde' AND '$hasta'
               OR DATE(n2.modificado)  BETWEEN '$desde' AND '$hasta')
    )
OR EXISTS (
        SELECT 1 FROM periodo_excepcion pe2
        WHERE pe2.idPeriodo     = e.idPeriodo
          AND pe2.idAulaMateria = e.idAulaMateria
    )
)";


// ── FILTROS ADICIONALES ──────────────────────────────────
if ($idAula > 0) {
    $sql .= " AND a.idAula = $idAula";
}
if ($idAulaMateria > 0) {
    $sql .= " AND am.idAulaMateria = $idAulaMateria";
}
if ($idEvaluacion > 0) {
    $sql .= " AND e.idEvaluacion = $idEvaluacion";
}

$sql .= " ORDER BY e.creado DESC";

$resultados = buscar_datos($sql);

if (!$resultados) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

// Calcular pendientes para cada evaluación
foreach ($resultados as &$row) {
    $row['pendientes'] = $row['total_alumnos'] - $row['calificados'];
}

echo json_encode(['success' => true, 'data' => $resultados]);
?>