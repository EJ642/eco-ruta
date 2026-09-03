<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../servicios/conexion.php';
session_name('DOCENTE_SESSION');
session_start();

// Ventana retroactiva: misma regla que validar_fecha_asistencia.php
$dias_retroactivos_permitidos = 7;

function json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

if (!isset($_SESSION['docente_id'])) {
    json_response(false, 'No autorizado');
}

$docente_id = (int)$_SESSION['docente_id'];
$usuario_id = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : $docente_id;
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !is_array($input)) {
    json_response(false, 'No se recibieron datos válidos');
}

$idAulaMateria = isset($input['idAulaMateria']) ? (int)$input['idAulaMateria'] : 0;
$fecha = isset($input['fecha']) ? trim($input['fecha']) : '';
$cantidadHoras = isset($input['cantidadHoras']) ? (int)$input['cantidadHoras'] : 0;
$asistenciaList = isset($input['asistencia']) && is_array($input['asistencia']) ? $input['asistencia'] : [];

if (!$idAulaMateria || $idAulaMateria <= 0) {
    json_response(false, 'ID de aula-materia inválido');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    json_response(false, 'Fecha inválida o formato incorrecto');
}

$dateObj = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$dateObj || $dateObj->format('Y-m-d') !== $fecha) {
    json_response(false, 'Fecha inválida');
}

// ── Reglas de fecha: nunca futura, dentro de ventana retroactiva ────────────
$hoy = new DateTime('today');
$fechaDt = new DateTime($fecha);

if ($fechaDt > $hoy) {
    json_response(false, 'No se puede registrar asistencia de una fecha futura');
}

$limiteRetroactivo = (clone $hoy)->modify("-{$dias_retroactivos_permitidos} days");
if ($fechaDt < $limiteRetroactivo) {
    json_response(false, "Solo se puede registrar asistencia de los últimos {$dias_retroactivos_permitidos} días");
}

if ($cantidadHoras < 1) {
    json_response(false, 'Cantidad de horas debe ser mayor a 0');
}

if (empty($asistenciaList)) {
    json_response(false, 'No hay registros de asistencia para guardar');
}

$estados_validos = ['Presente', 'Ausente', 'Tardanza', 'Justificado'];
foreach ($asistenciaList as $index => $item) {
    if (!isset($item['idMatricula']) || (int)$item['idMatricula'] <= 0) {
        json_response(false, 'Elemento ' . ($index + 1) . ': idMatricula inválido');
    }
    if (!isset($item['estado']) || !in_array($item['estado'], $estados_validos, true)) {
        json_response(false, 'Elemento ' . ($index + 1) . ': estado inválido');
    }
}

$fecha = limpiar_cadena($fecha);
$sql_check = "SELECT dam.idAsignacion FROM docente_aula_materia dam WHERE dam.idDocente = $docente_id AND dam.idAulaMateria = $idAulaMateria AND dam.activo = 1";
if (!buscar_datos($sql_check)) {
    json_response(false, 'No tiene acceso a esta materia');
}

// ── Validar que la fecha esté dentro del período/semestre activo ────────────
$sql_periodo = "SELECT p.fecha_inicio, p.fecha_fin, p.nombre
                FROM periodo p
                JOIN aula_materia am ON am.idAulaMateria = $idAulaMateria
                JOIN aula a ON a.idAula = am.idAula
                WHERE p.idAnio = a.idAnio
                  AND p.activo = 'Sí'
                LIMIT 1";
$periodo = buscar_datos($sql_periodo);

if (!$periodo) {
    json_response(false, 'No hay un período/semestre activo. No se puede registrar asistencia.');
}

$periodo = $periodo[0];
if ($fecha < $periodo['fecha_inicio'] || $fecha > $periodo['fecha_fin']) {
    json_response(false, "La fecha debe estar dentro del período activo: {$periodo['nombre']} ({$periodo['fecha_inicio']} al {$periodo['fecha_fin']})");
}

$sql_duplicate = "SELECT idSesion FROM asistencia_sesion WHERE idAulaMateria = $idAulaMateria AND fecha = '$fecha'";
if (buscar_datos($sql_duplicate)) {
    json_response(false, 'Ya existe asistencia registrada para esta fecha. Use editar si desea modificarla.');
}

$conn = conectar_bd();
$conn->autocommit(false);

try {
    $sql_sesion = "INSERT INTO asistencia_sesion (idAulaMateria, fecha, cantidad_horas, registrado_por, creado, modificado) VALUES ($idAulaMateria, '$fecha', $cantidadHoras, $usuario_id, NOW(), NOW())";
    if (!$conn->query($sql_sesion)) {
        throw new Exception('Error al guardar sesión: ' . $conn->error);
    }

    $idSesion = $conn->insert_id;
    $guardados = 0;

    foreach ($asistenciaList as $item) {
        $idMatricula = (int)$item['idMatricula'];
        $estado = limpiar_cadena($item['estado']);
        $observacion = isset($item['observacion']) ? limpiar_cadena($item['observacion']) : '';

        $sql_detalle = "INSERT INTO asistencia_detalle (idSesion, idMatricula, estado, observacion) VALUES ($idSesion, $idMatricula, '$estado', '$observacion')";
        if (!$conn->query($sql_detalle)) {
            throw new Exception('Error al guardar detalle para alumno ' . $idMatricula . ': ' . $conn->error);
        }
        $guardados++;
    }

    $conn->commit();
    $conn->close();
    json_response(true, 'Asistencia registrada correctamente', ['idSesion' => $idSesion, 'alumnos_guardados' => $guardados]);
} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    json_response(false, 'Error al guardar asistencia: ' . $e->getMessage());
}
