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
    json_response(false, 'No autorizado. Solo el Director puede cerrar semestres.');
}

$usuario_id = (int)($_SESSION['usuario_id'] ?? 0);
$input      = json_decode(file_get_contents('php://input'), true);
$idPeriodo  = isset($input['idPeriodo']) ? (int)$input['idPeriodo'] : 0;
$accion     = isset($input['accion'])    ? trim($input['accion'])   : ''; // 'cerrar' o 'abrir'

if ($idPeriodo <= 0)               json_response(false, 'idPeriodo inválido');
if (!in_array($accion, ['cerrar','abrir'])) json_response(false, 'Acción inválida');

// Verificar que el período existe y pertenece al año activo
$resPer = buscar_datos("
    SELECT p.idPeriodo, p.nombre, p.numero, p.activo, al.idAnio
    FROM periodo p
    JOIN anio_lectivo al ON al.idAnio = p.idAnio
    WHERE p.idPeriodo = $idPeriodo AND al.activo = 'Sí'
    LIMIT 1
");
if (!$resPer) json_response(false, 'Período no encontrado o no pertenece al año activo');
$periodo = $resPer[0];

// Validaciones según acción
if ($accion === 'cerrar' && $periodo['activo'] === 'No') {
    json_response(false, 'Este semestre ya está cerrado');
}
if ($accion === 'abrir' && $periodo['activo'] === 'Sí') {
    json_response(false, 'Este semestre ya está abierto');
}

// Si es el 2° semestre y se quiere cerrar, verificar que el 1° ya esté cerrado
if ($accion === 'cerrar' && (int)$periodo['numero'] === 2) {
    $resSem1 = buscar_datos("
        SELECT activo FROM periodo
        WHERE idAnio = {$periodo['idAnio']} AND numero = 1
        LIMIT 1
    ");
    if ($resSem1 && $resSem1[0]['activo'] === 'Sí') {
        json_response(false, 'Debe cerrar el 1° Semestre antes de cerrar el 2°');
    }
}

// ── Regla de negocio: nunca puede haber dos semestres activos a la vez ──────
// Esto protege la integridad de todo el sistema (asistencia, notas, etc.) que
// asume "el período activo" como único por año (LIMIT 1 en varias queries).
if ($accion === 'abrir') {
    $resOtrosActivos = buscar_datos("
        SELECT idPeriodo, nombre FROM periodo
        WHERE idAnio = {$periodo['idAnio']}
          AND activo = 'Sí'
          AND idPeriodo <> $idPeriodo
    ");
    if ($resOtrosActivos) {
        $otroNombre = $resOtrosActivos[0]['nombre'];
        json_response(false, "No se puede abrir este semestre: \"$otroNombre\" ya está activo. Cierre primero el otro semestre antes de abrir este.");
    }
}

// Si es cierre del 2° semestre: calcular notas finales y crear registros en nota_final_materia
$conn = conectar_bd();
$conn->autocommit(false);

try {
    $nuevoActivo = $accion === 'cerrar' ? 'No' : 'Sí';
    $idAnio      = (int)$periodo['idAnio'];

    // Actualizar estado del período
    if (!$conn->query("UPDATE periodo SET activo = '$nuevoActivo' WHERE idPeriodo = $idPeriodo")) {
        throw new Exception('Error al actualizar período: ' . $conn->error);
    }

    $procesados = 0;

    // Solo al cerrar el 2° semestre calculamos notas finales
    if ($accion === 'cerrar' && (int)$periodo['numero'] === 2) {

        // Traer todos los períodos del año para calcular notas parciales
        $periodos = buscar_datos("
            SELECT idPeriodo, numero FROM periodo
            WHERE idAnio = $idAnio ORDER BY numero
        ");
        $periodos = $periodos ?: [];

        // Traer todas las matrículas vigentes del año
        $matriculas = buscar_datos("
            SELECT m.idMatricula, m.idAula
            FROM matricula m
            JOIN aula au ON au.idAula = m.idAula
            WHERE au.idAnio = $idAnio AND m.estado = 'Vigente'
        ");
        if (!$matriculas) {
            $conn->commit();
            $conn->close();
            json_response(true, 'Semestre cerrado. No hay matrículas vigentes para procesar.');
        }

        foreach ($matriculas as $mat) {
            $idMatricula = (int)$mat['idMatricula'];
            $idAula      = (int)$mat['idAula'];

            // Materias del aula
            $materias = buscar_datos("
                SELECT idAulaMateria FROM aula_materia
                WHERE idAula = $idAula AND activo = 'Sí'
            ");
            if (!$materias) continue;

            foreach ($materias as $materia) {
                $idAulaMateria = (int)$materia['idAulaMateria'];

                // ── Calcular nota parcial por semestre ──────────────────────
                $notaSem = [];
                foreach ($periodos as $per) {
                    $idP = (int)$per['idPeriodo'];
                    $resN = buscar_datos("
                        SELECT 
                            COALESCE(SUM(n.puntos_obtenidos), 0) AS obt,
                            SUM(ev.puntos_total) AS max
                        FROM evaluacion ev
                        LEFT JOIN nota n ON n.idEvaluacion = ev.idEvaluacion
                                        AND n.idMatricula = $idMatricula
                        WHERE ev.idAulaMateria = $idAulaMateria
                        AND ev.idPeriodo = $idP
                    ");
                    $row = $resN ? $resN[0] : null;
                    if ($row && (float)$row['max'] > 0) {
                        $obt  = (float)$row['obt'];
                        $max  = (float)$row['max'];
                        $pmin = (int)ceil($max * 0.70);
                        $rango = ($max - $pmin) + 1;
                        $dist  = (int)floor($rango / 4);
                        $res   = (int)($rango % 4);
                        $limites = [
                            1 => 0,
                            2 => $pmin,
                            3 => $pmin + $dist + ($res >= 3 ? 1 : 0),
                            4 => $pmin + $dist * 2 + ($res >= 2 ? 1 : 0) + ($res >= 3 ? 1 : 0),
                            5 => $pmin + $dist * 3 + ($res >= 1 ? 1 : 0) + ($res >= 2 ? 1 : 0) + ($res >= 3 ? 1 : 0),
                        ];
                        $nota = 1;
                        for ($n = 5; $n >= 1; $n--) {
                            if ($obt >= $limites[$n]) { $nota = $n; break; }
                        }
                        $notaSem[$per['numero']] = $nota;
                    } else {
                        $notaSem[$per['numero']] = null;
                    }
                }
                
                $ns1 = $notaSem[1] ?? null;
                $ns2 = $notaSem[2] ?? null;

                // ── POLÍTICA DE NOTA FINAL ──────────────────────────────
                $notaFinal = null;
                $estado    = 'Pendiente';

                if ($ns1 !== null && $ns2 !== null) {
                    // REGLA 1: Si semestre 2 = 1 → nota final = 1 (SIEMPRE)
                    if ($ns2 == 1) {
                        $notaFinal = 1;
                        $estado = 'Recuperatorio1';
                    }
                    // REGLA 2: Si sem1 = 1 y sem2 = 2 → nota final = 1 (NO aprueba)
                    else if ($ns1 == 1 && $ns2 == 2) {
                        $notaFinal = 1;
                        $estado = 'Recuperatorio1';
                    }
                    // REGLA 3: Si sem1 = 1 y sem2 = 3 → nota final = 2 y APRUEBA (excepción)
                    else if ($ns1 == 1 && $ns2 == 3) {
                        $notaFinal = 2;
                        $estado = 'Aprobado';
                    }
                    // REGLA 4: Promedio normal para el resto de casos
                    // NOTA: Si promedio da 2, APRUEBA (nota mínima es 2)
                    else {
                        $notaFinal = (int)round(($ns1 + $ns2) / 2);
                        $estado = $notaFinal >= 2 ? 'Aprobado' : 'Recuperatorio1';
                    }
                }

                $ns1SQL        = $ns1        !== null ? $ns1        : 'NULL';
                $ns2SQL        = $ns2        !== null ? $ns2        : 'NULL';
                $nfSQL         = $notaFinal  !== null ? $notaFinal  : 'NULL';
                $ndSQL         = ($estado === 'Aprobado') ? $notaFinal : 'NULL';


/*
                $ns1 = $notaSem[1] ?? null;
                $ns2 = $notaSem[2] ?? null;

                // Nota final = round((sem1+sem2)/2), solo si ambas existen
                $notaFinal = null;
                $estado    = 'Pendiente';
                if ($ns1 !== null && $ns2 !== null) {
                    $notaFinal = (int)round(($ns1 + $ns2) / 2);
                    $estado    = $notaFinal >= 3 ? 'Aprobado' : 'Recuperatorio1';
                }

                $ns1SQL        = $ns1        !== null ? $ns1        : 'NULL';
                $ns2SQL        = $ns2        !== null ? $ns2        : 'NULL';
                $nfSQL         = $notaFinal  !== null ? $notaFinal  : 'NULL';
                $ndSQL         = ($estado === 'Aprobado') ? $notaFinal : 'NULL';*/
                $estadoEsc     = $conn->real_escape_string($estado);

                // INSERT o UPDATE (por si se reabre y vuelve a cerrar)
                $sql = "INSERT INTO nota_final_materia
                            (idMatricula, idAulaMateria, nota_sem1, nota_sem2,
                             nota_final, nota_definitiva, estado, registrado_por)
                        VALUES
                            ($idMatricula, $idAulaMateria, $ns1SQL, $ns2SQL,
                             $nfSQL, $ndSQL, '$estadoEsc', $usuario_id)
                        ON DUPLICATE KEY UPDATE
                            nota_sem1       = $ns1SQL,
                            nota_sem2       = $ns2SQL,
                            nota_final      = $nfSQL,
                            nota_definitiva = $ndSQL,
                            estado          = '$estadoEsc',
                            modificado      = NOW()";

                if (!$conn->query($sql)) {
                    throw new Exception("Error al guardar nota final (mat $idAulaMateria, mat $idMatricula): " . $conn->error);
                }
                $procesados++;
            }
        }
    }

    $conn->commit();
    $conn->close();

    $msg = $accion === 'cerrar'
        ? "Semestre cerrado correctamente" . ($procesados > 0 ? ". Se calcularon $procesados registros de nota final." : ".")
        : "Semestre reabierto correctamente.";

    json_response(true, $msg, ['procesados' => $procesados]);

} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    json_response(false, 'Error: ' . $e->getMessage());
}
