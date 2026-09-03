<?php
/**
 * API: Docente/api/guardar_evaluacion.php
 *
 * PROPÓSITO:
 * Crear una nueva evaluación (examen, trabajo práctico, etc.) en una aula-materia.
 * SOLO crea — para editar usar actualizar_evaluacion.php.
 *
 * VALIDACIONES:
 * - Sesión docente requerida
 * - Campos obligatorios: idAulaMateria, idPeriodo, idTipoNota, nombre
 * - Docente debe tener permiso en docente_aula_materia
 * - El período (semestre) debe permitir carga de notas (abierto o con excepción)
 * - puntos_total entre 1 y 100
 * - fecha_evaluacion (si viene) debe estar dentro del rango del período
 * - Si idTipoNota es "único por período" (1ra/2da Parcial, Examen Final),
 *   no debe existir ya una evaluación de ese tipo en esa materia/período
 */

session_name('DOCENTE_SESSION');
session_start();
require_once "../../servicios/conexion.php";
require_once "validar_periodo.php";

header('Content-Type: application/json; charset=utf-8');

// ===============================
// VALIDAR SESIÓN DOCENTE
// ===============================
if (!isset($_SESSION['docente_id'])) {
    echo json_encode(["success" => false, "error" => "Sesión no válida"]);
    exit;
}
$docente_id = (int)$_SESSION['docente_id'];

// ===============================
// RECIBIR DATOS
// ===============================
$data = json_decode(file_get_contents("php://input"), true);

$idAulaMateria    = isset($data['idAulaMateria']) ? (int)$data['idAulaMateria'] : 0;
$idPeriodo        = isset($data['idPeriodo'])     ? (int)$data['idPeriodo']     : 0;
$idTipoNota       = isset($data['idTipoNota'])    ? (int)$data['idTipoNota']    : 0;
$nombre           = trim($data['nombre'] ?? '');
$puntos_total     = isset($data['puntos_total']) ? (float)$data['puntos_total'] : 0;
$fecha_evaluacion = $data['fecha_evaluacion'] ?? null;

// ===============================
// VALIDACIONES BÁSICAS
// ===============================
if (!$idAulaMateria || !$idPeriodo || !$idTipoNota || !$nombre) {
    echo json_encode(["success" => false, "error" => "Faltan datos obligatorios"]);
    exit;
}

if ($puntos_total < 1 || $puntos_total > 100) {
    echo json_encode(["success" => false, "error" => "Los puntos totales deben estar entre 1 y 100"]);
    exit;
}

if ($fecha_evaluacion !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_evaluacion)) {
    echo json_encode(["success" => false, "error" => "Formato de fecha inválido"]);
    exit;
}

// ===============================
// VALIDAR PERMISO DEL DOCENTE
// ===============================
$sql_verificar = "SELECT dam.idAsignacion FROM docente_aula_materia dam
                   WHERE dam.idAulaMateria = $idAulaMateria
                   AND dam.idDocente = $docente_id
                   AND dam.activo = 1";
$resultado = buscar_datos($sql_verificar);

if (empty($resultado)) {
    echo json_encode(["success" => false, "error" => "No tienes permiso para crear evaluaciones en esta materia"]);
    exit;
}

// ===============================
// VALIDAR PERÍODO (abierto o con excepción)
// ===============================
$chkPeriodo = puede_cargar_notas($idAulaMateria, $idPeriodo);
if (!$chkPeriodo['permitido']) {
    echo json_encode(["success" => false, "error" => $chkPeriodo['motivo']]);
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
                "success" => false,
                "error"   => "La fecha de evaluación debe estar entre $inicio y $fin (rango del período)"
            ]);
            exit;
        }
    }
}

// ===============================
// VALIDAR TIPO ÚNICO POR PERÍODO
// ===============================
$resTipo = buscar_datos("SELECT nombre, unico_por_periodo FROM tipo_nota WHERE idTipoNota = $idTipoNota LIMIT 1");
if (!$resTipo) {
    echo json_encode(["success" => false, "error" => "Tipo de evaluación no válido"]);
    exit;
}

if ($resTipo[0]['unico_por_periodo'] === 'Sí') {
    $resExiste = buscar_datos("
        SELECT idEvaluacion FROM evaluacion
        WHERE idAulaMateria = $idAulaMateria
          AND idPeriodo = $idPeriodo
          AND idTipoNota = $idTipoNota
        LIMIT 1
    ");
    if ($resExiste) {
        $tipoNombre = $resTipo[0]['nombre'];
        echo json_encode([
            "success" => false,
            "error"   => "Ya existe una evaluación de '$tipoNombre' para este período. Edítela en lugar de crear una nueva."
        ]);
        exit;
    }
}

// ===============================
// INSERTAR
// ===============================
$nombre = limpiar_cadena($nombre);

try {
    $sql = "INSERT INTO evaluacion
            (idAulaMateria, idPeriodo, idTipoNota, nombre, puntos_total, fecha_evaluacion, creado, modificado)
            VALUES
            ($idAulaMateria, $idPeriodo, $idTipoNota, '$nombre', $puntos_total,
             " . ($fecha_evaluacion ? "'$fecha_evaluacion'" : "NULL") . ",
             NOW(), NOW())";

    $newId = insertar_datos($sql);
    if ($newId) {
        echo json_encode([
            "success" => true,
            "message" => "Evaluación creada correctamente",
            "idEvaluacion" => $newId
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Error al crear la evaluación"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Error: " . $e->getMessage()]);
}
