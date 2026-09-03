<?php
/**
 * API: Docente/api/listar_evaluaciones.php
 * 
 * PROPÓSITO:
 * Obtener lista de evaluaciones para una aula-materia específica,
 * con información de tipo de nota, puntos totales y cantidad de notas registradas.
 * 
 * DÓNDE SE USA:
 * - AJAX desde: calificaciones.php (cuando selecciona Materia, carga select Evaluación)
 * - AJAX desde: evaluaciones.php (cuando selecciona Materia, carga tabla)
 * 
 * PARÁMETROS DE ENTRADA:
 * - GET idAulaMateria: ID de la combinación aula-materia
 * - GET idPeriodo (opcional): Filtro por período
 * 
 * VALIDACIONES:
 * - Requiere sesión docente
 * - Valida que docente tenga permiso en docente_aula_materia
 * 
 * DATOS RETORNADOS:
 * JSON: {
 *   "success": true/false,
 *   "data": [
 *     {
 *       "idEvaluacion", "nombre", "tipo_nota", "puntos_total",
 *       "fecha_evaluacion", "periodo", "total_notas_registradas"
 *     },
 *     ...
 *   ]
 * }
 * 
 * USO:
 * - En calificaciones.php: para obtener puntos_total en validación de inputs
 * - En evaluaciones.php: para llenar tabla de listado
 */
session_name('DOCENTE_SESSION');
session_start();
require_once "../../servicios/conexion.php";

// ===============================
// VALIDAR SESIÓN DOCENTE
// ===============================
if(!isset($_SESSION['docente_id'])){
    echo json_encode([
        "success" => false,
        "data" => [],
        "error" => "Sesión no válida"
    ]);
    exit;
}

$docente_id = $_SESSION['docente_id'];

// ===============================
// RECIBIR FILTROS
// ===============================
$idAulaMateria = $_GET['idAulaMateria'] ?? null;
$idPeriodo = $_GET['idPeriodo'] ?? null;

// ===============================
// VALIDACIONES
// ===============================
if (!$idAulaMateria) {
    echo json_encode([
        "success" => false,
        "data" => [],
        "error" => "Falta parámetro idAulaMateria"
    ]);
    exit;
}

// Validar que el docente tenga permiso
$sql_verificar = "SELECT dam.idAsignacion FROM docente_aula_materia dam
                   WHERE dam.idAulaMateria = '$idAulaMateria' 
                   AND dam.idDocente = '$docente_id' 
                   AND dam.activo = 1";
$resultado = buscar_datos($sql_verificar);

if (empty($resultado)) {
    echo json_encode([
        "success" => false,
        "data" => [],
        "error" => "No tienes permiso para ver estas evaluaciones"
    ]);
    exit;
}

// ===============================
// OBTENER EVALUACIONES
// ===============================

$periodoFiltro = '';
if ($idPeriodo && $idPeriodo !== 'Todos') {
    $periodoFiltro = "AND e.idPeriodo = '$idPeriodo'";
}

$sql = "SELECT e.idEvaluacion,
               e.idAulaMateria,
               e.idPeriodo,
               e.idTipoNota,
               e.nombre,
               e.puntos_total,
               e.fecha_evaluacion,
               e.creado,
               e.modificado,  
               tn.nombre as tipo_nota,
               tn.porcentaje,
               tn.unico_por_periodo,
               p.nombre as periodo,
               COUNT(n.idNota) as total_notas_registradas
        FROM evaluacion e
        LEFT JOIN tipo_nota tn ON e.idTipoNota = tn.idTipoNota
        LEFT JOIN periodo p ON e.idPeriodo = p.idPeriodo
        LEFT JOIN nota n ON n.idEvaluacion = e.idEvaluacion
        WHERE e.idAulaMateria = '$idAulaMateria'
        AND (
            p.activo = 'Sí'
            OR EXISTS (
                SELECT 1 FROM periodo_excepcion pe
                WHERE pe.idPeriodo = e.idPeriodo
                AND pe.idAulaMateria = e.idAulaMateria
            )
        ) 
        $periodoFiltro
        GROUP BY e.idEvaluacion
        ORDER BY e.fecha_evaluacion DESC, e.creado DESC";

$evaluaciones = buscar_datos($sql);

echo json_encode([
    "success" => true,
    "data" => $evaluaciones ?: []
]);

?>
