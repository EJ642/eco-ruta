<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../servicios/conexion.php';
session_name('DOCENTE_SESSION');
session_start();

function json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['active']) || $_SESSION['rol'] !== 'Docente') {
    json_response(false, 'No autorizado');
}

$docente_id  = (int)($_SESSION['docente_id']  ?? 0);
$usuario_id  = (int)($_SESSION['usuario_id']  ?? 0);
if ($docente_id <= 0) json_response(false, 'Docente no identificado');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) json_response(false, 'Datos inválidos');

$idNotaFinal   = isset($input['idNotaFinal'])   ? (int)$input['idNotaFinal']   : 0;
$idAulaMateria = isset($input['idAulaMateria']) ? (int)$input['idAulaMateria'] : 0;
$nota          = isset($input['nota'])          ? (int)$input['nota']          : -1;

if ($idNotaFinal   <= 0) json_response(false, 'idNotaFinal inválido');
if ($idAulaMateria <= 0) json_response(false, 'idAulaMateria inválido');
if ($nota < 1 || $nota > 5) json_response(false, 'La nota debe ser un valor entre 1 y 5');

// Año activo
$resAnio = buscar_datos("SELECT idAnio FROM anio_lectivo WHERE activo = 'Sí' LIMIT 1");
if (!$resAnio) json_response(false, 'No hay año lectivo activo');
$idAnio = (int)$resAnio[0]['idAnio'];

// Verificar acceso del docente a la materia
$acceso = buscar_datos("
    SELECT dam.idAsignacion
    FROM docente_aula_materia dam
    JOIN aula_materia am ON am.idAulaMateria = dam.idAulaMateria
    JOIN aula au ON au.idAula = am.idAula
    WHERE dam.idDocente = $docente_id
      AND dam.idAulaMateria = $idAulaMateria
      AND dam.activo = 1
      AND au.idAnio = $idAnio
    LIMIT 1
");
if (!$acceso) json_response(false, 'No tenés acceso a esta materia');

// Traer el registro actual
$resNF = buscar_datos("
    SELECT idNotaFinal, estado, nota_rec1, nota_rec2
    FROM nota_final_materia
    WHERE idNotaFinal = $idNotaFinal
      AND idAulaMateria = $idAulaMateria
    LIMIT 1
");
if (!$resNF) json_response(false, 'Registro no encontrado');
$nf = $resNF[0];

$estadoActual = $nf['estado'];

// Solo se puede cargar si está en recuperatorio
if (!in_array($estadoActual, ['Recuperatorio1', 'Recuperatorio2'])) {
    json_response(false, 'Este alumno no está en estado de recuperatorio');
}

// Determinar qué recuperatorio cargar y el nuevo estado
if ($estadoActual === 'Recuperatorio1') {
    // Cargar rec1
    if ($nf['nota_rec1'] !== null) {
        json_response(false, 'El 1° Recuperatorio ya fue registrado');
    }
    $nuevoEstado    = $nota >= 3 ? 'Aprobado' : 'Recuperatorio2';
    $notaDefinitiva = $nota >= 3 ? $nota : null;
    $campoNota      = 'nota_rec1';

} else {
    // Cargar rec2
    if ($nf['nota_rec2'] !== null) {
        json_response(false, 'El 2° Recuperatorio ya fue registrado');
    }
    $nuevoEstado    = $nota >= 3 ? 'Aprobado' : 'Reprobado';
    $notaDefinitiva = $nota; // sea cual sea, es la definitiva
    $campoNota      = 'nota_rec2';
}

$notaDefinitivaSQL = $notaDefinitiva !== null ? $notaDefinitiva : 'NULL';
$nota      = limpiar_cadena((string)$nota);
$nuevoEstado = limpiar_cadena($nuevoEstado);

$conn = conectar_bd();
$conn->autocommit(false);

try {
    $sql = "UPDATE nota_final_materia
            SET $campoNota = $nota,
                nota_definitiva = $notaDefinitivaSQL,
                estado = '$nuevoEstado',
                modificado = NOW()
            WHERE idNotaFinal = $idNotaFinal";

    if (!$conn->query($sql)) {
        throw new Exception('Error al guardar: ' . $conn->error);
    }

    $conn->commit();
    $conn->close();

    json_response(true, 'Recuperatorio registrado correctamente', [
        'estado'          => $nuevoEstado,
        'nota_definitiva' => $notaDefinitiva,
    ]);

} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    json_response(false, $e->getMessage());
}
?>
