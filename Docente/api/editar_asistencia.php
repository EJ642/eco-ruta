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

if ($cantidadHoras < 1) {
    json_response(false, 'Cantidad de horas debe ser mayor a 0');
}

if (empty($asistenciaList)) {
    json_response(false, 'No hay registros de asistencia para actualizar');
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

$sql_find = "SELECT s.idSesion, s.fecha FROM asistencia_sesion s WHERE s.idAulaMateria = $idAulaMateria AND s.fecha = '$fecha' LIMIT 1";
$found = buscar_datos($sql_find);
if (!$found) {
    json_response(false, 'No existe el registro de asistencia para editar');
}

$idSesion = (int)$found[0]['idSesion'];

// ── Validación de ventana retroactiva ────────────────────────────────────────
// Protege contra edición de sesiones muy viejas, incluso si alguien llama
// a este endpoint directamente sin pasar por la UI.
$hoy = new DateTime('today');
$fechaSesion = new DateTime($found[0]['fecha']);
$limiteRetroactivo = (clone $hoy)->modify("-{$dias_retroactivos_permitidos} days");

if ($fechaSesion < $limiteRetroactivo) {
    json_response(false, "Esta asistencia ya no se puede editar: supera el límite de {$dias_retroactivos_permitidos} días permitidos para modificaciones.");
}

$conn = conectar_bd();
$conn->autocommit(false);

try {
    $sql_update = "UPDATE asistencia_sesion SET cantidad_horas = $cantidadHoras, modificado = NOW() WHERE idSesion = $idSesion";
    if (!$conn->query($sql_update)) {
        throw new Exception('Error al actualizar cabecera: ' . $conn->error);
    }

    $sql_delete = "DELETE FROM asistencia_detalle WHERE idSesion = $idSesion";
    if (!$conn->query($sql_delete)) {
        throw new Exception('Error al eliminar detalles anteriores: ' . $conn->error);
    }

    foreach ($asistenciaList as $item) {
        $idMatricula = (int)$item['idMatricula'];
        $estado = limpiar_cadena($item['estado']);
        $observacion = isset($item['observacion']) ? limpiar_cadena($item['observacion']) : '';

        $sql_insert = "INSERT INTO asistencia_detalle (idSesion, idMatricula, estado, observacion) VALUES ($idSesion, $idMatricula, '$estado', '$observacion')";
        if (!$conn->query($sql_insert)) {
            throw new Exception('Error al guardar detalle para alumno ' . $idMatricula . ': ' . $conn->error);
        }
    }

    $conn->commit();
    $conn->close();
    json_response(true, 'Asistencia actualizada correctamente');
} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    json_response(false, 'Error al actualizar asistencia: ' . $e->getMessage());
}
