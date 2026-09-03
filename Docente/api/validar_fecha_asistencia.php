<?php
// API: Validar fecha de asistencia y obtener horas usadas en la semana
//
// Reglas de negocio:
// - No se permite registrar fechas futuras (max = hoy)
// - Ventana retroactiva configurable (por defecto 7 días) para evitar carga
//   descontrolada de fechas muy viejas
// - La fecha debe estar dentro del período/semestre activo
// - Si el período está cerrado, solo se permite con excepción del Director
// - No puede haber dos sesiones para la misma materia en la misma fecha
//   (salvo que se esté validando la propia sesión en modo edición)
header('Content-Type: application/json');
require_once __DIR__ . '/../../servicios/conexion.php';
session_name('DOCENTE_SESSION');
session_start();

// ── Configuración ────────────────────────────────────────────────────────────
$dias_retroactivos_permitidos = 7;

function json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit;
}

if (!isset($_SESSION['docente_id'])) {
    json_response(false, 'No autorizado');
}

$docente_id    = (int)$_SESSION['docente_id'];
$idAulaMateria = isset($_GET['idAulaMateria']) ? (int)$_GET['idAulaMateria'] : 0;
$fecha         = isset($_GET['fecha'])         ? trim($_GET['fecha'])         : '';
// En modo edición se ignora la propia sesión al chequear duplicado
$idSesionEditar = isset($_GET['idSesion']) ? (int)$_GET['idSesion'] : 0;

// ── Validaciones básicas ──────────────────────────────────────────────────────

if ($idAulaMateria <= 0) {
    json_response(false, 'ID de aula-materia inválido');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    json_response(false, 'Formato de fecha inválido');
}

$dateObj = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$dateObj || $dateObj->format('Y-m-d') !== $fecha) {
    json_response(false, 'Fecha inválida');
}

// ── Verificar ownership del docente ───────────────────────────────────────────

$sql_check = "SELECT dam.idAsignacion
              FROM docente_aula_materia dam
              WHERE dam.idDocente = $docente_id
                AND dam.idAulaMateria = $idAulaMateria
                AND dam.activo = 1";
if (!buscar_datos($sql_check)) {
    json_response(false, 'No tiene acceso a esta materia');
}

// ── Regla 1: nunca fechas futuras ─────────────────────────────────────────────

$hoy     = new DateTime('today');
$fechaDt = new DateTime($fecha);

if ($fechaDt > $hoy) {
    json_response(false, 'No se puede registrar asistencia de una fecha futura', [
        'fecha_futura' => true
    ]);
}

// ── Regla 2: ventana retroactiva ──────────────────────────────────────────────

$limiteRetroactivo = (clone $hoy)->modify("-{$dias_retroactivos_permitidos} days");

if ($fechaDt < $limiteRetroactivo) {
    json_response(false, "Solo se puede registrar o editar asistencia de los últimos {$dias_retroactivos_permitidos} días", [
        'fuera_de_ventana'   => true,
        'limite_retroactivo' => $limiteRetroactivo->format('Y-m-d')
    ]);
}

// ── Obtener periodo activo para el aula ───────────────────────────────────────

$sql_periodo = "SELECT p.idPeriodo, p.nombre, p.fecha_inicio, p.fecha_fin, p.activo
                FROM periodo p
                JOIN aula_materia am ON am.idAulaMateria = $idAulaMateria
                JOIN aula a ON a.idAula = am.idAula
                WHERE p.idAnio = a.idAnio
                  AND p.activo = 'Sí'
                LIMIT 1";
$periodo = buscar_datos($sql_periodo);

if (!$periodo) {
    json_response(false, 'No hay un período/semestre activo. No se puede registrar asistencia.', [
        'sin_periodo' => true
    ]);
}

$periodo       = $periodo[0];
$fechaInicio   = $periodo['fecha_inicio'];
$fechaFin      = $periodo['fecha_fin'];
$idPeriodo     = (int)$periodo['idPeriodo'];
$nombrePeriodo = $periodo['nombre'];
$periodoActivo = $periodo['activo'];

// ── Regla 3: dentro del periodo activo ────────────────────────────────────────

$fechaTs  = strtotime($fecha);
$inicioTs = strtotime($fechaInicio);
$finTs    = strtotime($fechaFin);

if ($fechaTs < $inicioTs || $fechaTs > $finTs) {
    json_response(false, "La fecha debe estar dentro del período activo: $nombrePeriodo ($fechaInicio al $fechaFin)", [
        'fuera_de_periodo' => true,
        'fecha_inicio'     => $fechaInicio,
        'fecha_fin'        => $fechaFin,
        'periodo_nombre'   => $nombrePeriodo
    ]);
}

// ── Regla 3.5: Verificar si el período está cerrado (solo se permite con excepción) ──

$sql_cerrado = "SELECT activo FROM periodo WHERE idPeriodo = $idPeriodo";
$estado_periodo = buscar_datos($sql_cerrado);
$periodo_cerrado = $estado_periodo && $estado_periodo[0]['activo'] === 'No';

$excepcion_activa = false;
if ($periodo_cerrado) {
    // Verificar si hay excepción para esta materia
    $sql_excepcion = "SELECT idExcepcion FROM periodo_excepcion 
                      WHERE idPeriodo = $idPeriodo 
                        AND idAulaMateria = $idAulaMateria";
    $excepcion = buscar_datos($sql_excepcion);
    $excepcion_activa = $excepcion && count($excepcion) > 0;
    
    if (!$excepcion_activa) {
        json_response(false, 'El período está cerrado. Solo se puede editar si el Director habilitó una excepción.', [
            'periodo_cerrado' => true,
            'excepcion' => false
        ]);
    }
}

// ── Regla 4: sin duplicado (excluyendo la propia sesión en modo edición) ─────

$sql_dup = "SELECT idSesion FROM asistencia_sesion
            WHERE idAulaMateria = $idAulaMateria AND fecha = '$fecha'";
$sesionExistente = buscar_datos($sql_dup);

$yaExiste      = false;
$idSesionExist = 0;

if ($sesionExistente) {
    $idSesionExist = (int)$sesionExistente[0]['idSesion'];
    if ($idSesionEditar > 0 && $idSesionEditar === $idSesionExist) {
        $yaExiste = false; // es la propia sesión que se está editando
    } else {
        $yaExiste = true;
    }
}

// ── Regla 5: Día hábil (solo advertencia, NO bloquea) ─────────────────────────

$diaSemanaNum = (int)$fechaDt->format('N'); // 1=Lun, 7=Dom
$esFinDeSemana = $diaSemanaNum >= 6;

// ── Horas ya usadas en la semana calendario (lunes a domingo) ────────────────
// Si modo edición, excluir la sesión que se está editando del cálculo

$diaSemana = (int)$fechaDt->format('N'); // 1=lunes … 7=domingo
$lunes     = (clone $fechaDt)->modify('-' . ($diaSemana - 1) . ' days')->format('Y-m-d');
$domingo   = (clone $fechaDt)->modify('+' . (7 - $diaSemana) . ' days')->format('Y-m-d');

$excluirSesion = $idSesionEditar > 0 ? "AND s.idSesion <> $idSesionEditar" : '';

$sql_horas = "SELECT COALESCE(SUM(s.cantidad_horas), 0) AS horas_usadas
              FROM asistencia_sesion s
              WHERE s.idAulaMateria = $idAulaMateria
                AND s.fecha BETWEEN '$lunes' AND '$domingo'
                $excluirSesion";
$resHoras    = buscar_datos($sql_horas);
$horasUsadas = $resHoras ? (int)$resHoras[0]['horas_usadas'] : 0;

// ── Horas semanales de la materia ─────────────────────────────────────────────

$sql_mat = "SELECT m.horas_sem, m.nombre AS materia_nombre
            FROM materia m
            JOIN aula_materia am ON am.idMateria = m.idMateria
            WHERE am.idAulaMateria = $idAulaMateria
            LIMIT 1";
$materia        = buscar_datos($sql_mat);
$horasSemanales = $materia ? (int)$materia[0]['horas_sem']    : 0;
$materiaNombre  = $materia ? $materia[0]['materia_nombre']     : '';

// ── Respuesta ──────────────────────────────────────────────────────────────────

json_response(true, 'OK', [
    'fecha_valida'    => true,
    'ya_existe'       => $yaExiste,
    'idSesion_exist'  => $idSesionExist,
    'limites_input'   => [
        // lo más restrictivo entre la ventana retroactiva y el inicio del período
        'min' => max($limiteRetroactivo->format('Y-m-d'), $fechaInicio),
        'max' => min($hoy->format('Y-m-d'), $fechaFin),
    ],
    'periodo'         => [
        'idPeriodo'    => $idPeriodo,
        'nombre'       => $nombrePeriodo,
        'fecha_inicio' => $fechaInicio,
        'fecha_fin'    => $fechaFin,
        'cerrado'      => $periodo_cerrado,
    ],
    'periodo_cerrado' => $periodo_cerrado,
    'excepcion_activa' => $excepcion_activa,
    'horas_semana'    => [
        'semanales'    => $horasSemanales,
        'usadas'       => $horasUsadas,
        'disponibles'  => max(0, $horasSemanales - $horasUsadas),
        'semana_desde' => $lunes,
        'semana_hasta' => $domingo,
    ],
    'materia_nombre'  => $materiaNombre,
    'advertencia'     => $esFinDeSemana ? 'La fecha seleccionada es fin de semana' : null,
]);