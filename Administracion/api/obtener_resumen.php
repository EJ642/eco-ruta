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

// function validar_docente() {
//     if (empty($_SESSION['active']) || ($_SESSION['rol'] ?? '') !== 'Docente') {
//         json_response(false, 'No autorizado');
//     }
//     $docente_id = (int)($_SESSION['docente_id'] ?? 0);
//     if ($docente_id <= 0) json_response(false, 'Docente no identificado');
//     return $docente_id;
// }

function obtener_anio() {
    if (!empty($_GET['idAnio'])) {
        $idAnio = (int)$_GET['idAnio'];
        $res = buscar_datos("SELECT idAnio, anio FROM anio_lectivo WHERE idAnio = $idAnio LIMIT 1");
    } else {
        $res = buscar_datos("SELECT idAnio, anio FROM anio_lectivo WHERE activo = 'Sí' LIMIT 1");
    }
    if (!$res) json_response(false, 'No hay año lectivo activo');
    return $res[0];
}

function obtener_periodos($idAnio) {
    return buscar_datos("
        SELECT idPeriodo, nombre, numero, fecha_inicio, fecha_fin, activo
        FROM periodo WHERE idAnio = $idAnio ORDER BY numero
    ") ?: [];
}

function obtener_tipos() {
    return buscar_datos("
        SELECT idTipoNota, nombre FROM tipo_nota WHERE activo = 'Sí' ORDER BY idTipoNota
    ") ?: [];
}

/**
 * Calcula la nota parcial usando el sistema de distribución proporcional
 * con 70% de exigencia.
 *
 * Ejemplo con total = 75:
 *   puntaje_minimo = ceil(75 * 0.70) = 53   → nota 2
 *   rango_superior = 75 - 53 = 22
 *   rango_superior + 1 = 23
 *   distribucion = floor(23 / 4) = 5
 *   residuo = 23 % 4 = 3
 *
 *   Tabla resultante:
 *     nota 1 → 0   a 52
 *     nota 2 → 53  a 57   (base)
 *     nota 3 → 58  a 63   (+1 por residuo >= 3)
 *     nota 4 → 64  a 69   (+1 por residuo >= 2... etc)
 *     nota 5 → 70  a 75
 *
 * Regla del residuo: se agrega 1 punto extra a las notas 3 y 4
 * cuando sobra 2, a las notas 2, 3 y 4 cuando sobra 3.
 */
function calcular_nota_paraguaya($puntos_obtenidos, $puntos_total) {
    if ($puntos_total <= 0) return null;

    $pmin = (int)ceil($puntos_total * 0.70); // puntaje mínimo para nota 2
    $rango = ($puntos_total - $pmin) + 1;    // puntos a distribuir en notas 2-5... pero en 4 tramos
    $dist  = (int)floor($rango / 4);
    $res   = $rango % 4;

    // Construir límites inferiores de cada nota
    // nota 1: 0 a pmin-1
    // nota 2: pmin
    // nota 3: pmin + dist [+ residuo si res >= 3 → +1]
    // nota 4: pmin + dist*2 [+ residuo ajustado]
    // nota 5: pmin + dist*3 [+ residuo ajustado] ... hasta total
    $limites = [
        1 => 0,
        2 => $pmin,
        3 => $pmin + $dist + ($res >= 3 ? 1 : 0),
        4 => $pmin + $dist * 2 + ($res >= 2 ? 1 : 0) + ($res >= 3 ? 1 : 0),
        5 => $pmin + $dist * 3 + ($res >= 1 ? 1 : 0) + ($res >= 2 ? 1 : 0) + ($res >= 3 ? 1 : 0),
    ];

    $nota = 1;
    for ($n = 5; $n >= 1; $n--) {
        if ($puntos_obtenidos >= $limites[$n]) {
            $nota = $n;
            break;
        }
    }

    return [
        'nota'    => $nota,
        'limites' => $limites,
        'pmin'    => $pmin,
        'dist'    => $dist,
        'res'     => $res,
    ];
}

// ── FUNCIÓN DE POLÍTICA DE NOTA FINAL (local) ────────────
function calcularNotaFinalPolitica($ns1, $ns2) {
    if ($ns1 === null || $ns2 === null) {
        return ['nota_final' => null, 'estado' => 'Pendiente'];
    }
    
    // REGLA 1: Si semestre 2 = 1 → nota final = 1 (SIEMPRE)
    if ($ns2 == 1) {
        return ['nota_final' => 1, 'estado' => 'Recuperatorio1'];
    }
    // REGLA 2: Si sem1 = 1 y sem2 = 2 → nota final = 1 (NO aprueba)
    if ($ns1 == 1 && $ns2 == 2) {
        return ['nota_final' => 1, 'estado' => 'Recuperatorio1'];
    }
    // REGLA 3: Si sem1 = 1 y sem2 = 3 → nota final = 2 y APRUEBA (excepción)
    if ($ns1 == 1 && $ns2 == 3) {
        return ['nota_final' => 2, 'estado' => 'Aprobado'];
    }
    // REGLA 4: Promedio normal para el resto de casos
    // NOTA: Si promedio da 2, APRUEBA (nota mínima es 2)
    $notaFinal = (int)round(($ns1 + $ns2) / 2);
    $estado = $notaFinal >= 2 ? 'Aprobado' : 'Recuperatorio1';
    return ['nota_final' => $notaFinal, 'estado' => $estado];
}



// ── Cálculo de proceso de materia para un alumno ─────────────
//trae desde evaluacion, para que cargue todas las evaluaciones, no solo los registrados del alumno
function calcular_proceso_materia($idMatricula, $idAulaMateria, $periodos, $matInfo = []) {

    $resNotas = buscar_datos("
        SELECT COALESCE(n.puntos_obtenidos, 0) AS puntos_obtenidos,
               ev.puntos_total, ev.idTipoNota, ev.idPeriodo,
               tn.nombre AS tipo,
               ev.nombre AS nombreEval, ev.fecha_evaluacion,
               (n.idNota IS NOT NULL) AS tiene_nota
        FROM evaluacion ev
        JOIN tipo_nota tn ON tn.idTipoNota   = ev.idTipoNota
        LEFT JOIN nota n  ON n.idEvaluacion  = ev.idEvaluacion
                         AND n.idMatricula   = $idMatricula
        WHERE ev.idAulaMateria = $idAulaMateria
        ORDER BY ev.idPeriodo, ev.idTipoNota, ev.fecha_evaluacion
    ");
    $notas = $resNotas ?: [];

    $resAsist = buscar_datos("
        SELECT
            COUNT(DISTINCT s.idSesion)               AS total_sesiones,
            COALESCE(SUM(s.cantidad_horas), 0)        AS total_horas,
            COALESCE(SUM(d.estado = 'Presente'),   0) AS presentes,
            COALESCE(SUM(d.estado = 'Ausente'),    0) AS ausentes,
            COALESCE(SUM(d.estado = 'Tardanza'),   0) AS tardanzas,
            COALESCE(SUM(d.estado = 'Justificado'),0) AS justificados
        FROM asistencia_detalle d
        JOIN asistencia_sesion s ON s.idSesion = d.idSesion
        WHERE d.idMatricula = $idMatricula
          AND s.idAulaMateria = $idAulaMateria
    ");
    $asist = $resAsist ? $resAsist[0] : [];

    $totalSesiones = (int)($asist['total_sesiones'] ?? 0);
    $presentes     = (int)($asist['presentes']      ?? 0);
    $ausentes      = (int)($asist['ausentes']       ?? 0);
    $tardanzas     = (int)($asist['tardanzas']      ?? 0);
    $justificados  = (int)($asist['justificados']   ?? 0);
    $asistidos     = $presentes + $tardanzas + $justificados;
    $pctAsist      = $totalSesiones > 0 ? round($asistidos * 100 / $totalSesiones, 1) : null;

    // Organizar notas por período
    $notasPorPeriodo = [];
    foreach ($periodos as $per) {
        $idP = (int)$per['idPeriodo'];
        $notasPorPeriodo[$idP] = [
            'idPeriodo'   => $idP,
            'periodo'     => $per['nombre'],
            'numero'      => (int)$per['numero'],
            'activo'      => $per['activo'],
            'porTipo'     => [],
            'totalPuntos' => 0,
            'totalMax'    => 0,
            'notaParcial' => null,
            'pctParcial'  => null,
            'limites'     => null,
        ];
    }

    foreach ($notas as $nota) {
        $idP = (int)$nota['idPeriodo'];
        $idT = (int)$nota['idTipoNota'];
        if (!isset($notasPorPeriodo[$idP])) continue;

        if (!isset($notasPorPeriodo[$idP]['porTipo'][$idT])) {
            $notasPorPeriodo[$idP]['porTipo'][$idT] = [
                'idTipoNota'   => $idT,
                'tipo'         => $nota['tipo'],
                'puntos'       => 0,
                'total'        => 0,
                'completo'     => true, // false si alguna evaluación de este tipo no tiene nota
                'evaluaciones' => [],
            ];
        }

        $tieneNota = (bool)$nota['tiene_nota'];

        // Total SIEMPRE suma: todos los alumnos de la misma materia/período
        // tienen el mismo denominador real, sin importar cuántas notas tengan cargadas.
        $notasPorPeriodo[$idP]['porTipo'][$idT]['total'] += (float)$nota['puntos_total'];

        if ($tieneNota) {
            $notasPorPeriodo[$idP]['porTipo'][$idT]['puntos'] += (float)$nota['puntos_obtenidos'];
        } else {
            // Falta nota para esta evaluación → marcar tipo como incompleto
            $notasPorPeriodo[$idP]['porTipo'][$idT]['completo'] = false;
        }

        $notasPorPeriodo[$idP]['porTipo'][$idT]['evaluaciones'][] = [
            'nombre'   => $nota['nombreEval'],
            'fecha'    => $nota['fecha_evaluacion'],
            'obtenido' => $tieneNota ? (float)$nota['puntos_obtenidos'] : null,
            'maximo'   => (float)$nota['puntos_total'],
        ];
    }

    foreach ($notasPorPeriodo as &$datP) {
        $sumaObt = 0;
        $sumaMax = 0;
        foreach ($datP['porTipo'] as &$datT) {
            $datT['pct'] = $datT['total'] > 0
                ? round($datT['puntos'] * 100 / $datT['total'], 1)
                : null;
            $sumaObt += $datT['puntos'];
            $sumaMax += $datT['total'];
        }
        unset($datT);

        $datP['totalPuntos'] = $sumaObt;
        $datP['totalMax']    = $sumaMax;

        // ── Cálculo nota con distribución proporcional 70% ──
        if ($sumaMax > 0) {
            $datP['pctParcial'] = round($sumaObt * 100 / $sumaMax, 1);
            $calc = calcular_nota_paraguaya($sumaObt, $sumaMax);
            $datP['notaParcial'] = $calc['nota'];
            $datP['limites']     = $calc['limites'];
            $datP['pmin']        = $calc['pmin'];
        }

        $datP['porTipo'] = array_values($datP['porTipo']);
    }
    unset($datP);

    // Estado: Regular si nota >= 3 en todos los períodos con notas cargadas
    // Usamos $sumaObt acumulada: si ningún período tiene puntos obtenidos reales, está Pendiente
    $hayAlgunaNota = false;
    foreach ($notasPorPeriodo as $datP) {
        if ($datP['totalPuntos'] > 0) { $hayAlgunaNota = true; break; }
    }
    $estadoParcial = 'Pendiente';
    if ($hayAlgunaNota) {
        $hayBaja = false;
        foreach ($notasPorPeriodo as $datP) {
            if ($datP['totalMax'] > 0 && $datP['notaParcial'] !== null && $datP['notaParcial'] < 3) {
                $hayBaja = true;
                break;
            }
        }
        $estadoParcial = $hayBaja ? 'Irregular' : 'Regular';
    }

    // Nota final desde nota_final_materia
    $resNF = buscar_datos("
        SELECT nota_sem1, nota_sem2, nota_final,
               nota_rec1, nota_rec2, nota_definitiva, estado
        FROM nota_final_materia
        WHERE idMatricula = $idMatricula AND idAulaMateria = $idAulaMateria
        LIMIT 1
    ");
    $nf = $resNF ? $resNF[0] : null;


    // Calcular NF on-the-fly si no está guardado(segundo semestre no cerrado)
    $notaFinalCalc = null;
    if (!$nf) {
        $notasSem = [];
        foreach ($notasPorPeriodo as $datP) {
            if ($datP['notaParcial'] !== null) {
                $notasSem[] = (int)$datP['notaParcial'];
            }
        }
        if (count($notasSem) === 2) {
            $resultado = calcularNotaFinalPolitica($notasSem[0], $notasSem[1]);
            $notaFinalCalc = $resultado['nota_final'];
        }
    }



    $notaFinal = [
        'nota_sem1'       => $nf && $nf['nota_sem1']       !== null ? (int)$nf['nota_sem1']       : null,
        'nota_sem2'       => $nf && $nf['nota_sem2']       !== null ? (int)$nf['nota_sem2']       : null,
        'nota_final'      => $nf && $nf['nota_final']      !== null ? (int)$nf['nota_final']      : $notaFinalCalc,
        'nota_rec1'       => $nf && $nf['nota_rec1']       !== null ? (int)$nf['nota_rec1']       : null,
        'nota_rec2'       => $nf && $nf['nota_rec2']       !== null ? (int)$nf['nota_rec2']       : null,
        'nota_definitiva' => $nf && $nf['nota_definitiva'] !== null ? (int)$nf['nota_definitiva'] : null,
        'estado_final'    => $nf ? $nf['estado']                                                  : 'Pendiente',
        'guardado'        => $nf ? true                                                            : false,
    ];

    return array_merge([
        'idAulaMateria' => $idAulaMateria,
        'asistencia' => [
            'total_sesiones' => $totalSesiones,
            'total_horas'    => (int)($asist['total_horas'] ?? 0),
            'presentes'      => $presentes,
            'ausentes'       => $ausentes,
            'tardanzas'      => $tardanzas,
            'justificados'   => $justificados,
            'pct'            => $pctAsist,
        ],
        'periodos'  => array_values($notasPorPeriodo),
        'estado'    => $estadoParcial,
        'notaFinal' => $notaFinal,
    ], $matInfo);
}

// ── Main ──────────────────────────────────────────────────
// $docente_id = validar_docente();
$modo       = $_GET['modo'] ?? 'alumno';
$anio       = obtener_anio();
$idAnio     = (int)$anio['idAnio'];
$periodos   = obtener_periodos($idAnio);
$tipos      = obtener_tipos();

// ══ MODO: MATERIA ═════════════════════════════════════════
if ($modo === 'materia') {
    $idAulaMateria = isset($_GET['idAulaMateria']) ? (int)$_GET['idAulaMateria'] : 0;
    if ($idAulaMateria <= 0) json_response(false, 'idAulaMateria inválido');

    $resMateria = buscar_datos("
        SELECT am.idAulaMateria, am.idAula, mat.idMateria,
               mat.nombre AS materia, mat.codigo, mat.horas_sem,
               c.numero AS numCurso, c.nombre AS nombreCurso,
               e.nombre AS enfasis, t.descripcion AS turno
        FROM aula_materia am 
        JOIN materia mat       ON mat.idMateria    = am.idMateria
        JOIN aula au           ON au.idAula        = am.idAula
        JOIN curso c           ON c.idCurso        = au.idCurso
        JOIN enfasis e         ON e.idEnfasis      = au.idEnfasis
        JOIN turno t           ON t.idTurno        = c.idTurno
        WHERE am.activo = 1
          AND am.idAulaMateria = $idAulaMateria
          AND au.idAnio = $idAnio
        LIMIT 1
    ");
    if (!$resMateria) json_response(false, 'Materia no encontrada o sin permiso');
    $materia = $resMateria[0];
    $idAula  = (int)$materia['idAula'];

    $alumnos = buscar_datos("
        SELECT a.idAlumno, a.nombres, a.apellidos, a.cedula,
               m.idMatricula, m.estado AS estadoMatricula
        FROM matricula m
        JOIN alumno a ON a.idAlumno = m.idAlumno
        WHERE m.idAula = $idAula
          AND m.estado = 'Vigente'
          AND a.estado = 'Activo'
        ORDER BY a.apellidos, a.nombres
    ") ?: [];

    $resultado = [];
    foreach ($alumnos as $alumno) {
        $proceso = calcular_proceso_materia((int)$alumno['idMatricula'], $idAulaMateria, $periodos);
        $resultado[] = array_merge($alumno, $proceso);
    }

    json_response(true, 'OK', [
        'modo'    => 'materia',
        'anio'    => $anio,
        'materia' => $materia,
        'periodos'=> $periodos,
        'tipos'   => $tipos,
        'alumnos' => $resultado,
    ]);
}

// ══ MODO: ALUMNO ══════════════════════════════════════════
$idAlumno = isset($_GET['idAlumno']) ? (int)$_GET['idAlumno'] : 0;
if ($idAlumno <= 0) json_response(false, 'idAlumno inválido');

$resAlu = buscar_datos("
    SELECT a.idAlumno, a.nombres, a.apellidos, a.cedula, a.sexo, a.estado AS estadoAlumno,
           c.numero AS numCurso, c.nombre AS nombreCurso,
           e.nombre AS enfasis, t.descripcion AS turno,
           m.idMatricula, m.estado AS estadoMatricula, m.fecha_matricula,
           au.idAula
    FROM alumno a
    JOIN matricula m ON m.idAlumno  = a.idAlumno
    JOIN aula au     ON au.idAula   = m.idAula
    JOIN curso c     ON c.idCurso   = au.idCurso
    JOIN enfasis e   ON e.idEnfasis = au.idEnfasis
    JOIN turno t     ON t.idTurno   = c.idTurno
    WHERE a.idAlumno = $idAlumno
      AND au.idAnio  = $idAnio
      AND m.estado   = 'Vigente'
    LIMIT 1
");
if (!$resAlu) json_response(false, 'Alumno no encontrado en este año lectivo');

$alumno      = $resAlu[0];
$idMatricula = (int)$alumno['idMatricula'];
$idAula      = (int)$alumno['idAula'];

$materias = buscar_datos("
    SELECT am.idAulaMateria, mat.idMateria, mat.nombre AS materia,
           mat.horas_sem, mat.codigo
    FROM docente_aula_materia dam
    JOIN aula_materia am ON am.idAulaMateria = dam.idAulaMateria
    JOIN materia mat     ON mat.idMateria    = am.idMateria
    WHERE am.idAula      = $idAula
      
      AND dam.activo     = 1
      AND am.activo      = 'Sí'
    ORDER BY mat.nombre
") ?: [];

$resultado = [];
foreach ($materias as $mat) {
    $resultado[] = calcular_proceso_materia($idMatricula, (int)$mat['idAulaMateria'], $periodos, [
        'materia'   => $mat['materia'],
        'codigo'    => $mat['codigo'],
        'horas_sem' => (int)$mat['horas_sem'],
    ]);
}

json_response(true, 'OK', [
    'modo'    => 'alumno',
    'anio'    => $anio,
    'alumno'  => $alumno,
    'periodos'=> $periodos,
    'tipos'   => $tipos,
    'materias'=> $resultado,
]);
