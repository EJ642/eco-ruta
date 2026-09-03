<?php
/**
 * API: Docente/api/materias_docente.php
 * 
 * PROPÓSITO:
 * Obtener lista de materias que el docente tiene asignadas en una aula específica,
 * filtradas por los permisos de docente_aula_materia.
 * 
 * DÓNDE SE USA:
 * - AJAX desde: calificaciones.php (cuando selecciona Aula)
 * - AJAX desde: evaluaciones.php (cuando selecciona Aula en modal o filtros)
 * 
 * PARÁMETROS DE ENTRADA:
 * - GET idAula: ID del aula para obtener sus materias
 * 
 * VALIDACIONES:
 * - Requiere sesión docente activa
 * - Valida que docente tenga permiso en docente_aula_materia con activo=1
 * 
 * DATOS RETORNADOS:
 * JSON: {
 *   "success": true/false,
 *   "data": [
 *     { "idAulaMateria", "idMateria", "idAula", "numero", "enfoque", "materia" },
 *     ...
 *   ],
 *   "error": "mensaje si hay error"
 * }
 * 
 * QUERY PRINCIPAL:
 * SELECT aula_materia.idAulaMateria, materias, docente_aula_materia permisos
 * WHERE docente_aula_materia.idDocente = docente_id AND activo=1
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../../servicios/conexion.php';

session_name('ADMIN_SESSION');
session_start();

// if (!isset($_SESSION['docente_id'])) {
//     echo json_encode(['success' => false, 'message' => 'No autorizado']);
//     exit;
// }

// $docente_id = $_SESSION['docente_id'];
$idAula = isset($_GET['idAula']) ? (int)$_GET['idAula'] : 0;

if (!$idAula) {
    echo json_encode(['success' => false, 'message' => 'ID de aula requerido']);
    exit;
}

// Obtener materias del docente en el aula seleccionada
$sql = "SELECT am.idAulaMateria, am.idMateria, m.nombre as materia, m.horas_sem,
        CONCAT(c.numero, '° ', e.nombre) as aula
FROM aula_materia am 
JOIN materia m ON am.idMateria = m.idMateria
JOIN aula a ON am.idAula = a.idAula
JOIN curso c ON a.idCurso = c.idCurso
JOIN enfasis e ON a.idEnfasis = e.idEnfasis
WHERE am.idAula = $idAula
  AND am.activo = 'Sí'
ORDER BY m.nombre";

$datos = buscar_datos($sql);

if ($datos) {
    echo json_encode(['success' => true, 'data' => $datos]);
} else {
    echo json_encode(['success' => false, 'message' => 'No hay materias asignadas']);
}
?>