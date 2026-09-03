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
    json_response(false, 'No autorizado. Solo el Director puede cerrar el año.');
}

$usuario_id = (int)($_SESSION['usuario_id'] ?? 0);

// Año activo
$resAnio = buscar_datos("SELECT idAnio, anio FROM anio_lectivo WHERE activo = 'Sí' LIMIT 1");
if (!$resAnio) json_response(false, 'No hay año lectivo activo');
$anio   = $resAnio[0];
$idAnio = (int)$anio['idAnio'];

// ── Validación 1: todos los semestres deben estar cerrados ──
$periodos = buscar_datos("SELECT activo FROM periodo WHERE idAnio = $idAnio");
if (!$periodos) json_response(false, 'No hay períodos configurados');
foreach ($periodos as $per) {
    if ($per['activo'] === 'Sí') {
        json_response(false, 'Hay semestres aún abiertos. Cerrá todos los semestres antes de cerrar el año.');
    }
}

// ── Validación 2: no deben quedar recuperatorios pendientes ──
$resRec = buscar_datos("
    SELECT COUNT(*) AS total FROM nota_final_materia nf
    JOIN matricula m ON m.idMatricula = nf.idMatricula
    JOIN aula au ON au.idAula = m.idAula
    WHERE au.idAnio = $idAnio
      AND nf.estado IN ('Recuperatorio1','Recuperatorio2')
");
$recPendientes = $resRec ? (int)$resRec[0]['total'] : 0;
if ($recPendientes > 0) {
    json_response(false, "Hay $recPendientes alumno(s) con recuperatorio pendiente. Deben resolverse antes de cerrar el año.");
}

// ── Procesar cierre ──────────────────────────────────────
$conn = conectar_bd();
$conn->autocommit(false);

try {
    // ── Lock anti-doble-cierre ───────────────────────────────────────────────
    // SELECT ... FOR UPDATE bloquea la fila del año lectivo hasta que esta
    // transacción termine (commit o rollback). Si el Director hace doble clic,
    // refresca y reintenta, o abre dos pestañas, la segunda llamada queda
    // esperando acá hasta que la primera termine — y al continuar, vuelve a
    // evaluar el estado real (que ya estará actualizado), evitando que ambas
    // procesen las mismas matrículas en paralelo.
    $lock = $conn->query("SELECT idAnio FROM anio_lectivo WHERE idAnio = $idAnio FOR UPDATE");
    if (!$lock) {
        throw new Exception('No se pudo bloquear el año lectivo para el cierre: ' . $conn->error);
    }

    // ── Validación 3 (re-chequeada dentro del lock): año no debe estar ya cerrado ──
    // Se compara contra el TOTAL de matrículas del año, no solo si "alguna"
    // matrícula ya tiene estado Promovido/Reprobado. Esto evita bloquear un
    // cierre legítimo si quedó una matrícula vigente suelta (alta tardía,
    // caso administrativo especial, etc.) mezclada con otras ya procesadas.
    $resTotalMat = $conn->query("
        SELECT COUNT(*) AS total
        FROM matricula m
        JOIN aula au ON au.idAula = m.idAula
        WHERE au.idAnio = $idAnio
    ")->fetch_assoc();
    $totalMatriculas = (int)$resTotalMat['total'];

    $resCerradas = $conn->query("
        SELECT COUNT(*) AS total
        FROM matricula m
        JOIN aula au ON au.idAula = m.idAula
        WHERE au.idAnio = $idAnio AND m.estado IN ('Promovido','Reprobado')
    ")->fetch_assoc();
    $totalCerradas = (int)$resCerradas['total'];

    if ($totalMatriculas > 0 && $totalCerradas === $totalMatriculas) {
        throw new Exception('El año lectivo ya fue cerrado (todas las matrículas ya están procesadas).');
    }

    // Traer todas las matrículas vigentes del año (misma conexión que el lock)
    $resMatriculas = $conn->query("
        SELECT m.idMatricula, m.idAula
        FROM matricula m
        JOIN aula au ON au.idAula = m.idAula
        WHERE au.idAnio = $idAnio AND m.estado = 'Vigente'
    ");
    if (!$resMatriculas) {
        throw new Exception('Error al obtener matrículas: ' . $conn->error);
    }
    $matriculas = $resMatriculas->fetch_all(MYSQLI_ASSOC);

    if (!$matriculas) {
        $conn->commit();
        $conn->close();
        json_response(true, 'No hay matrículas vigentes para procesar.');
    }

    $promovidos = 0;
    $reprobados = 0;

    foreach ($matriculas as $mat) {
        $idMatricula = (int)$mat['idMatricula'];

        // Verificar si tiene alguna materia reprobada (nota_definitiva < 3 o null)
        $resRep = $conn->query("
            SELECT COUNT(*) AS total
            FROM nota_final_materia
            WHERE idMatricula = $idMatricula
              AND (estado = 'Reprobado' OR nota_definitiva IS NULL OR nota_definitiva < 3)
        ");
        if (!$resRep) {
            throw new Exception("Error al verificar reprobación de matrícula $idMatricula: " . $conn->error);
        }
        $rowRep = $resRep->fetch_assoc();
        $tieneReprobada = $rowRep && (int)$rowRep['total'] > 0;

        $nuevoEstado = $tieneReprobada ? 'Reprobado' : 'Promovido';

        if (!$conn->query("
            UPDATE matricula SET estado = '$nuevoEstado' WHERE idMatricula = $idMatricula
        ")) {
            throw new Exception("Error al actualizar matrícula $idMatricula: " . $conn->error);
        }

        $tieneReprobada ? $reprobados++ : $promovidos++;
    }

    $conn->commit();
    $conn->close();

    json_response(true, 'Año lectivo cerrado correctamente.', [
        'promovidos' => $promovidos,
        'reprobados' => $reprobados,
        'total'      => $promovidos + $reprobados,
    ]);

} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    json_response(false, 'Error: ' . $e->getMessage());
}
