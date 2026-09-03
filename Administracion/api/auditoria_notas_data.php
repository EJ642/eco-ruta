<?php
/**
 * API: Administracion/auditoria_notas_data.php
 *
 * PROPÓSITO:
 * Devolver el historial de cambios de auditoria_nota, ya enriquecido con
 * el contexto completo de cada registro (alumno, curso, materia, evaluación,
 * y el nombre de quién hizo el cambio).
 *
 * Solo accesible para el rol Director.
 *
 * FILTROS SOPORTADOS (todos opcionales, combinables):
 * - idAula        : filtra por curso/énfasis
 * - idAulaMateria : filtra por materia específica dentro de un curso
 * - idUsuario     : filtra por quién hizo el cambio
 * - alumno        : búsqueda de texto libre por nombre/apellido/cédula
 * - desde / hasta : rango de fechas sobre auditoria_nota.fecha
 *
 * Por defecto, si no se especifica "desde", se limita a los últimos 30 días
 * para evitar cargar el historial completo en cada consulta.
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

// Solo Director
if (empty($_SESSION['active']) || $_SESSION['rol'] !== 'Director') {
    json_response(false, 'No autorizado. Solo el Director puede ver la auditoría de notas.');
}

// ── Parámetros de filtro ─────────────────────────────────────────────────
$idAula        = isset($_GET['idAula'])        ? (int)$_GET['idAula']        : 0;
$idAulaMateria = isset($_GET['idAulaMateria']) ? (int)$_GET['idAulaMateria'] : 0;
$idUsuario     = isset($_GET['idUsuario'])     ? (int)$_GET['idUsuario']     : 0;
$alumnoBusq    = isset($_GET['alumno'])        ? trim($_GET['alumno'])      : '';

$hastaRaw = isset($_GET['hasta']) ? trim($_GET['hasta']) : date('Y-m-d');
$desdeRaw = isset($_GET['desde']) ? trim($_GET['desde']) : date('Y-m-d', strtotime('-30 days'));

function validar_fecha_param($valor, $fallback) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) return $fallback;
    $d = DateTime::createFromFormat('Y-m-d', $valor);
    return ($d && $d->format('Y-m-d') === $valor) ? $valor : $fallback;
}

$desde = validar_fecha_param($desdeRaw, date('Y-m-d', strtotime('-30 days')));
$hasta = validar_fecha_param($hastaRaw, date('Y-m-d'));
if ($desde > $hasta) { $tmp = $desde; $desde = $hasta; $hasta = $tmp; }

$desde = limpiar_cadena($desde);
$hasta = limpiar_cadena($hasta);

// ── Query principal ───────────────────────────────────────────────────────
// auditoria_nota -> nota -> evaluacion -> aula_materia -> materia / aula -> curso / enfasis
//                       -> matricula -> alumno
// auditoria_nota.idUsuario -> usuarios -> docente (LEFT JOIN, por si el usuario no es docente)
$sql = "SELECT
    an.idAuditoria,
    an.accion,
    an.valor_antes,
    an.valor_despues,
    an.fecha,
    an.ip,
    an.detalle,
    al.cedula     AS alumno_cedula,
    al.nombres    AS alumno_nombres,
    al.apellidos  AS alumno_apellidos,
    mat.nombre    AS materia,
    ev.nombre     AS evaluacion,
    ev.puntos_total,
    CONCAT(c.numero, '° ', e.nombre) AS curso,
    a.idAula,
    am.idAulaMateria,
    u.idUsuario,
    u.usuario     AS usuario_login,
    d.nombres     AS docente_nombres,
    d.apellidos   AS docente_apellidos
FROM auditoria_nota an
JOIN nota n            ON n.idNota = an.idNota
JOIN evaluacion ev      ON ev.idEvaluacion = n.idEvaluacion
JOIN aula_materia am    ON am.idAulaMateria = ev.idAulaMateria
JOIN materia mat        ON mat.idMateria = am.idMateria
JOIN aula a             ON a.idAula = am.idAula
JOIN curso c            ON c.idCurso = a.idCurso
JOIN enfasis e          ON e.idEnfasis = a.idEnfasis
JOIN matricula m        ON m.idMatricula = n.idMatricula
JOIN alumno al           ON al.idAlumno = m.idAlumno
LEFT JOIN usuarios u    ON u.idUsuario = an.idUsuario
LEFT JOIN docente d     ON d.idUsuario = u.idUsuario
WHERE DATE(an.fecha) BETWEEN '$desde' AND '$hasta'";

if ($idAula > 0) {
    $sql .= " AND a.idAula = $idAula";
}
if ($idAulaMateria > 0) {
    $sql .= " AND am.idAulaMateria = $idAulaMateria";
}
if ($idUsuario > 0) {
    $sql .= " AND an.idUsuario = $idUsuario";
}
if ($alumnoBusq !== '') {
    $alumnoBusqEsc = limpiar_cadena($alumnoBusq);
    $sql .= " AND (al.nombres LIKE '%$alumnoBusqEsc%' OR al.apellidos LIKE '%$alumnoBusqEsc%' OR al.cedula LIKE '%$alumnoBusqEsc%')";
}

$sql .= " ORDER BY an.fecha DESC LIMIT 500";

$datos = buscar_datos($sql);

// Armar nombre legible de quien hizo el cambio (docente si existe, sino el login)
$resultado = [];
if ($datos) {
    foreach ($datos as $row) {
        $quien = 'Usuario eliminado';
        if (!empty($row['docente_nombres'])) {
            $quien = trim($row['docente_nombres'] . ' ' . $row['docente_apellidos']);
        } elseif (!empty($row['usuario_login'])) {
            $quien = $row['usuario_login'];
        }

        $resultado[] = [
            'idAuditoria'   => (int)$row['idAuditoria'],
            'accion'        => $row['accion'],
            'valor_antes'   => $row['valor_antes'],
            'valor_despues' => $row['valor_despues'],
            'puntos_total'  => (int)$row['puntos_total'],
            'fecha'         => $row['fecha'],
            'ip'            => $row['ip'],
            'detalle'       => $row['detalle'],
            'alumno'        => trim($row['alumno_apellidos'] . ', ' . $row['alumno_nombres']),
            'alumno_cedula' => $row['alumno_cedula'],
            'curso'         => $row['curso'],
            'materia'       => $row['materia'],
            'evaluacion'    => $row['evaluacion'],
            'idAula'        => (int)$row['idAula'],
            'idAulaMateria' => (int)$row['idAulaMateria'],
            'idUsuario'     => $row['idUsuario'] !== null ? (int)$row['idUsuario'] : null,
            'quien'         => $quien,
        ];
    }
}

json_response(true, 'OK', [
    'registros' => $resultado,
    'total'     => count($resultado),
    'desde'     => $desde,
    'hasta'     => $hasta,
]);
