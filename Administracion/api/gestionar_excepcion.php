<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../servicios/conexion.php';
session_start();

function json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['active']) || $_SESSION['rol'] !== 'Director') {
    json_response(false, 'No autorizado. Solo el Director puede gestionar excepciones.');
}

$usuario_id = (int)($_SESSION['usuario_id'] ?? 0);
$input      = json_decode(file_get_contents('php://input'), true);

$idPeriodo     = isset($input['idPeriodo'])     ? (int)$input['idPeriodo']     : 0;
$idAulaMateria = isset($input['idAulaMateria']) ? (int)$input['idAulaMateria'] : 0;
$accion        = isset($input['accion'])        ? trim($input['accion'])      : ''; // 'crear' o 'eliminar'
$motivo        = isset($input['motivo'])        ? trim($input['motivo'])      : '';

if ($idPeriodo <= 0)     json_response(false, 'idPeriodo inválido');
if ($idAulaMateria <= 0) json_response(false, 'idAulaMateria inválido');
if (!in_array($accion, ['crear','eliminar'])) json_response(false, 'Acción inválida');

// Verificar que el período exista y esté cerrado (las excepciones solo aplican a períodos cerrados)
$resPer = buscar_datos("
    SELECT p.activo, al.idAnio
    FROM periodo p
    JOIN anio_lectivo al ON al.idAnio = p.idAnio
    WHERE p.idPeriodo = $idPeriodo LIMIT 1
");
if (!$resPer) json_response(false, 'Período no encontrado');
$idAnioPeriodo = (int)$resPer[0]['idAnio'];

// ── Validar que idAulaMateria pertenezca al mismo año lectivo que el período ──
// Sin esto, se podría crear una excepción cruzando un período de un año con
// una materia de otro año si alguien llama al endpoint directamente.
$resMateria = buscar_datos("
    SELECT a.idAnio
    FROM aula_materia am
    JOIN aula a ON a.idAula = am.idAula
    WHERE am.idAulaMateria = $idAulaMateria
    LIMIT 1
");
if (!$resMateria) json_response(false, 'Materia no encontrada');
if ((int)$resMateria[0]['idAnio'] !== $idAnioPeriodo) {
    json_response(false, 'La materia no pertenece al año lectivo de este período');
}

if ($accion === 'crear') {
    if ($resPer[0]['activo'] === 'Sí') {
        json_response(false, 'El período está abierto, no es necesario crear una excepción');
    }

    // Verificar si ya existe
    $existe = buscar_datos("
        SELECT idExcepcion FROM periodo_excepcion
        WHERE idPeriodo = $idPeriodo AND idAulaMateria = $idAulaMateria
    ");
    if ($existe) json_response(false, 'Esta materia ya tiene una excepción activa');

    $motivoEsc = $motivo !== '' ? "'" . limpiar_cadena($motivo) . "'" : 'NULL';

    $sql = "INSERT INTO periodo_excepcion (idPeriodo, idAulaMateria, motivo, autorizado_por)
            VALUES ($idPeriodo, $idAulaMateria, $motivoEsc, $usuario_id)";

    if (!insertar_datos($sql)) {
        json_response(false, 'Error al crear la excepción');
    }
    json_response(true, 'Materia reabierta correctamente. El docente ya puede editar notas de este período.');

} else {
    // Eliminar excepción
    $existe = buscar_datos("
        SELECT idExcepcion FROM periodo_excepcion
        WHERE idPeriodo = $idPeriodo AND idAulaMateria = $idAulaMateria
    ");
    if (!$existe) json_response(false, 'No existe una excepción para esta materia');

    $sql = "DELETE FROM periodo_excepcion
            WHERE idPeriodo = $idPeriodo AND idAulaMateria = $idAulaMateria";

    if (!eliminar_datos($sql)) {
        json_response(false, 'Error al eliminar la excepción');
    }
    json_response(true, 'Excepción eliminada. La materia vuelve a estar bajo el cierre global.');
}
