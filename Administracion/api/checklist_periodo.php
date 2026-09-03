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

if (empty($_SESSION['active']) || $_SESSION['rol'] !== 'Director') {
    json_response(false, 'No autorizado. Solo el Director puede ver el checklist.');
}

$idPeriodo = isset($_GET['idPeriodo']) ? (int)$_GET['idPeriodo'] : 0;
if ($idPeriodo <= 0) json_response(false, 'idPeriodo inválido');

// Año del período
$resPer = buscar_datos("
    SELECT p.idPeriodo, p.nombre, p.activo, al.idAnio
    FROM periodo p
    JOIN anio_lectivo al ON al.idAnio = p.idAnio
    WHERE p.idPeriodo = $idPeriodo LIMIT 1
");
if (!$resPer) json_response(false, 'Período no encontrado');
$periodo = $resPer[0];
$idAnio  = (int)$periodo['idAnio'];

// Todas las materias activas del año + cuántas evaluaciones tienen en este período
// + cuántos alumnos del aula NO tienen nota registrada en alguna de esas evaluaciones
$materias = buscar_datos("
    SELECT am.idAulaMateria, am.idAula,
           mat.nombre AS materia,
           CONCAT(c.numero, '° - ', e.nombre) AS curso,
           d.nombres AS docNombres, d.apellidos AS docApellidos
    FROM aula_materia am
    JOIN materia mat ON mat.idMateria = am.idMateria
    JOIN aula au     ON au.idAula     = am.idAula
    JOIN curso c     ON c.idCurso     = au.idCurso
    JOIN enfasis e   ON e.idEnfasis   = au.idEnfasis
    LEFT JOIN docente_aula_materia dam ON dam.idAulaMateria = am.idAulaMateria AND dam.activo = 1
    LEFT JOIN docente d ON d.idDocente = dam.idDocente
    WHERE au.idAnio = $idAnio AND am.activo = 'Sí'
    ORDER BY c.numero, mat.nombre
") ?: [];

// Excepciones actuales para este período
$excepciones = buscar_datos("
    SELECT idAulaMateria FROM periodo_excepcion WHERE idPeriodo = $idPeriodo
") ?: [];
$idsExcepcion = array_column($excepciones, 'idAulaMateria');

$resultado = [];
foreach ($materias as $mat) {
    $idAulaMateria = (int)$mat['idAulaMateria'];
    $idAula        = (int)$mat['idAula'];

    // Evaluaciones de esta materia en este período
    $resEval = buscar_datos("
        SELECT idEvaluacion, puntos_total FROM evaluacion
        WHERE idAulaMateria = $idAulaMateria AND idPeriodo = $idPeriodo
    ") ?: [];
    $totalEval = count($resEval);

    // Alumnos del aula
    $resAlu = buscar_datos("
        SELECT COUNT(*) AS total FROM matricula
        WHERE idAula = $idAula AND estado = 'Vigente'
    ");
    $totalAlumnos = $resAlu ? (int)$resAlu[0]['total'] : 0;

    // Notas cargadas vs esperadas (alumnos x evaluaciones)
    $esperadas = $totalAlumnos * $totalEval;
    $cargadas  = 0;
    if ($totalEval > 0) {
        $idsEval = implode(',', array_column($resEval, 'idEvaluacion'));
        $resNotas = buscar_datos("
            SELECT COUNT(*) AS total FROM nota WHERE idEvaluacion IN ($idsEval)
        ");
        $cargadas = $resNotas ? (int)$resNotas[0]['total'] : 0;
    }

    $completo = ($totalEval > 0) && ($cargadas >= $esperadas) && ($esperadas > 0);

    $resultado[] = [
        'idAulaMateria' => $idAulaMateria,
        'idAula'        => $idAula,
        'materia'       => $mat['materia'],
        'curso'         => $mat['curso'],
        'docente'       => $mat['docNombres'] ? "{$mat['docNombres']} {$mat['docApellidos']}" : 'Sin asignar',
        'totalEval'     => $totalEval,
        'totalAlumnos'  => $totalAlumnos,
        'esperadas'     => $esperadas,
        'cargadas'      => $cargadas,
        'completo'      => $completo,
        'tieneExcepcion'=> in_array($idAulaMateria, $idsExcepcion),
    ];
}

// ── Agrupar por curso (idAula) para que el frontend pueda mostrar primero
//    un panorama por curso/énfasis, y solo el detalle de materias al entrar ──
$grupos = [];
foreach ($resultado as $item) {
    $idAula = $item['idAula'];
    if (!isset($grupos[$idAula])) {
        $grupos[$idAula] = [
            'idAula'    => $idAula,
            'curso'     => $item['curso'],
            'materias'  => [],
        ];
    }
    $grupos[$idAula]['materias'][] = $item;
}

// Calcular resumen de completitud por grupo y ordenar por número de curso
$cursosResumen = [];
foreach ($grupos as $g) {
    $totalMat      = count($g['materias']);
    $completasMat  = count(array_filter($g['materias'], fn($m) => $m['completo']));
    $conExcepcion  = count(array_filter($g['materias'], fn($m) => $m['tieneExcepcion']));

    $cursosResumen[] = [
        'idAula'         => $g['idAula'],
        'curso'          => $g['curso'],
        'totalMaterias'  => $totalMat,
        'completas'      => $completasMat,
        'pendientes'     => $totalMat - $completasMat - $conExcepcion,
        'conExcepcion'   => $conExcepcion,
        'todoCompleto'   => ($totalMat > 0) && ($completasMat + $conExcepcion === $totalMat),
    ];
}

// Orden natural por número de curso (extraído del texto "N° - Énfasis")
usort($cursosResumen, function($a, $b) {
    return strnatcasecmp($a['curso'], $b['curso']);
});

json_response(true, 'OK', [
    'periodo'    => $periodo,
    'materias'   => $resultado,
    'cursos'     => $cursosResumen,
    'pendientes' => count(array_filter($resultado, fn($m) => !$m['completo'])),
]);
