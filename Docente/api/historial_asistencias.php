<?php
// API: Historial de asistencias del docente
header('Content-Type: application/json');
require_once __DIR__ . '/../../servicios/conexion.php';
session_name('DOCENTE_SESSION');
session_start();

if (!isset($_SESSION['docente_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$docente_id    = (int)$_SESSION['docente_id'];
$idAula        = isset($_GET['idAula'])        ? (int)$_GET['idAula']        : 0;
$idAulaMateria = isset($_GET['idAulaMateria']) ? (int)$_GET['idAulaMateria'] : 0;

// ── Sanitizar y validar fechas ───────────────────────────────────────────────

$desdeRaw = isset($_GET['desde']) ? trim($_GET['desde']) : date('Y-m-01');
$hastaRaw = isset($_GET['hasta']) ? trim($_GET['hasta']) : date('Y-m-d');

function validar_fecha_param($valor, $fallback) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) return $fallback;
    $d = DateTime::createFromFormat('Y-m-d', $valor);
    return ($d && $d->format('Y-m-d') === $valor) ? $valor : $fallback;
}

$desde = validar_fecha_param($desdeRaw, date('Y-m-01'));
$hasta = validar_fecha_param($hastaRaw, date('Y-m-d'));

// Asegurar orden lógico
if ($desde > $hasta) {
    $tmp   = $desde;
    $desde = $hasta;
    $hasta = $tmp;
}

$desde = limpiar_cadena($desde);
$hasta = limpiar_cadena($hasta);

// ── Query principal ──────────────────────────────────────────────────────────

$sql = "SELECT
    s.idSesion,
    s.fecha,
    CONCAT(c.numero, '° ', e.nombre) AS curso,
    m.nombre AS materia,
    am.idAulaMateria,
    s.cantidad_horas,
    COUNT(DISTINCT d.idMatricula) AS total_alumnos,
    SUM(CASE WHEN d.estado = 'Presente'                    THEN 1 ELSE 0 END) AS presentes,
    SUM(CASE WHEN d.estado IN ('Ausente','Tardanza')        THEN 1 ELSE 0 END) AS ausentes,
    SUM(CASE WHEN d.estado = 'Tardanza'                    THEN 1 ELSE 0 END) AS tardanzas,
    SUM(CASE WHEN d.estado = 'Justificado'                 THEN 1 ELSE 0 END) AS justificados
FROM asistencia_sesion s
JOIN asistencia_detalle d  ON s.idSesion      = d.idSesion
JOIN aula_materia am        ON s.idAulaMateria = am.idAulaMateria
JOIN materia m              ON am.idMateria    = m.idMateria
JOIN aula a                 ON am.idAula       = a.idAula
JOIN curso c                ON a.idCurso       = c.idCurso
JOIN enfasis e              ON a.idEnfasis     = e.idEnfasis
JOIN docente_aula_materia dam ON am.idAulaMateria = dam.idAulaMateria
WHERE dam.idDocente = $docente_id
  AND dam.activo    = 1
  AND s.fecha BETWEEN '$desde' AND '$hasta'";

if ($idAula > 0) {
    $sql .= " AND am.idAula = $idAula";
}
if ($idAulaMateria > 0) {
    $sql .= " AND s.idAulaMateria = $idAulaMateria";
}

$sql .= " GROUP BY s.idSesion, s.fecha, s.idAulaMateria, s.cantidad_horas,
                   c.numero, e.nombre, m.nombre
          ORDER BY s.fecha DESC, m.nombre";

$datos = buscar_datos($sql);

if ($datos) {
    echo json_encode(['success' => true, 'data' => $datos]);
} else {
    echo json_encode(['success' => false, 'message' => 'No hay registros de asistencia']);
}
