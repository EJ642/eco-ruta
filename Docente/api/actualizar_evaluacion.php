<?php
/**
 * API: Docente/api/actualizar_evaluacion.php
 *
 * PROPÓSITO:
 * Actualizar datos de una evaluación existente.
 * NO permite cambiar idAulaMateria ni idPeriodo.
 *
 * REGLA CLAVE:
 * - Si la evaluación NO tiene notas registradas → se puede editar todo
 *   (nombre, idTipoNota, puntos_total, fecha_evaluacion)
 * - Si la evaluación YA tiene notas registradas → SOLO se permite
 *   editar nombre y fecha_evaluacion. idTipoNota y puntos_total quedan
 *   bloqueados porque alterarlos rompería el cálculo de nota parcial
 *   (distribución 70%) y la interpretación de las notas ya cargadas.
 *
 * VALIDACIONES:
 * - Sesión docente requerida
 * - Campos obligatorios: idEvaluacion, nombre
 * - Evaluación debe existir y docente debe tener permiso
 * - El período debe permitir edición (abierto o con excepción)
 * - fecha_evaluacion (si viene) dentro del rango del período
 * - Si tiene notas: idTipoNota y puntos_total deben coincidir
 *   con los valores actuales (si el front los manda igual, no pasa nada;
 *   si vienen distintos, se rechaza)
 */

session_name('DOCENTE_SESSION');
session_start();
require_once "../../servicios/conexion.php";
require_once "validar_periodo.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['docente_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}
$docente_id = (int)$_SESSION['docente_id'];

$data = json_decode(file_get_contents("php://input"), true);

$idEvaluacion     = isset($data['idEvaluacion']) ? (int)$data['idEvaluacion'] : 0;
$idTipoNota       = isset($data['idTipoNota'])   ? (int)$data['idTipoNota']   : 0;
$nombre           = trim($data['nombre'] ?? '');
$puntos_total     = isset($data['puntos_total']) ? (float)$data['puntos_total'] : 0;
$fecha_evaluacion = $data['fecha_evaluacion'] ?? null;

if (!$idEvaluacion || !$idTipoNota || !$nombre) {
    echo json_encode(['success' => false, 'error' => 'Datos obligatorios faltantes']);
    exit;
}

if ($puntos_total < 1 || $puntos_total > 100) {
    echo json_encode(['success' => false, 'error' => 'Los puntos totales deben estar entre 1 y 100']);
    exit;
}

if ($fecha_evaluacion !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_evaluacion)) {
    echo json_encode(['success' => false, 'error' => 'Formato de fecha inválido']);
    exit;
}

// ===============================
// VERIFICAR PERMISO Y OBTENER DATOS ACTUALES
// ===============================
$sql_check = "SELECT e.idEvaluacion, e.idAulaMateria, e.idPeriodo,
                      e.idTipoNota AS idTipoNota_actual,
                      e.puntos_total AS puntos_total_actual
              FROM evaluacion e
              JOIN docente_aula_materia dam ON e.idAulaMateria = dam.idAulaMateria
              WHERE e.idEvaluacion = $idEvaluacion
              AND dam.idDocente = $docente_id
              AND dam.activo = 1";
$check = buscar_datos($sql_check);

if (!$check) {
    echo json_encode(['success' => false, 'error' => 'Evaluación no encontrada o sin permiso']);
    exit;
}

$idAulaMateria = (int)$check[0]['idAulaMateria'];
$idPeriodo     = (int)$check[0]['idPeriodo'];

// ===============================
// VALIDAR PERÍODO (abierto o con excepción)
// ===============================
$chkPeriodo = puede_cargar_notas($idAulaMateria, $idPeriodo);
if (!$chkPeriodo['permitido']) {
    echo json_encode(['success' => false, 'error' => $chkPeriodo['motivo']]);
    exit;
}

// ===============================
// VALIDAR FECHA DENTRO DEL RANGO DEL PERÍODO
// ===============================
if ($fecha_evaluacion !== null) {
    $resPeriodo = buscar_datos("SELECT fecha_inicio, fecha_fin FROM periodo WHERE idPeriodo = $idPeriodo LIMIT 1");
    if ($resPeriodo) {
        $inicio = $resPeriodo[0]['fecha_inicio'];
        $fin    = $resPeriodo[0]['fecha_fin'];
        if (($inicio && $fecha_evaluacion < $inicio) || ($fin && $fecha_evaluacion > $fin)) {
            echo json_encode([
                'success' => false,
                'error'   => "La fecha de evaluación debe estar entre $inicio y $fin (rango del período)"
            ]);
            exit;
        }
    }
}

// ===============================
// VERIFICAR SI TIENE NOTAS REGISTRADAS
// ===============================
$resNotas = buscar_datos("SELECT COUNT(*) AS total FROM nota WHERE idEvaluacion = $idEvaluacion");
$tieneNotas = $resNotas && (int)$resNotas[0]['total'] > 0;

if ($tieneNotas) {
    $tipoActual   = (int)$check[0]['idTipoNota_actual'];
    $puntosActual = (float)$check[0]['puntos_total_actual'];

    if ($idTipoNota !== $tipoActual) {
        echo json_encode([
            'success' => false,
            'error'   => 'No se puede cambiar el tipo de evaluación porque ya tiene notas registradas'
        ]);
        exit;
    }

    if (abs($puntos_total - $puntosActual) > 0.001) {
        echo json_encode([
            'success' => false,
            'error'   => 'No se puede cambiar el puntaje total porque ya tiene notas registradas'
        ]);
        exit;
    }
}

// ===============================
// ACTUALIZAR
// ===============================
$nombre = limpiar_cadena($nombre);

$sql_update = "UPDATE evaluacion
               SET idTipoNota = $idTipoNota,
                   nombre = '$nombre',
                   puntos_total = $puntos_total,
                   fecha_evaluacion = " . ($fecha_evaluacion ? "'$fecha_evaluacion'" : "NULL") . ",
                   modificado = NOW()
               WHERE idEvaluacion = $idEvaluacion";

if (actualizar_datos($sql_update)) {
    echo json_encode(['success' => true, 'message' => 'Evaluación actualizada correctamente']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al actualizar evaluación']);
}
