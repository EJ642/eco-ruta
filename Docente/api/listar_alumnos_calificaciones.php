<?php
/**
 * API: Docente/api/listar_alumnos_calificaciones.php
 * 
 * PROPÓSITO:
 * Obtener lista de alumnos vigentes en un aula para cargar sus calificaciones.
 * 
 * DÓNDE SE USA:
 * - AJAX desde: calificaciones.php (cuando click en botón "Cargar Alumnos")
 * 
 * PARÁMETROS DE ENTRADA:
 * - GET idAulaMateria: ID de aula-materia (solo para validar permiso docente)
 * - Se obtiene idAula desde aula_materia table
 * 
 * VALIDACIONES:
 * - Requiere sesión docente activa
 * - Valida permiso en docente_aula_materia con activo=1
 * - Alumnos VIGENTES: matricula.estado = 'Vigente' 
 * - Alumno activo: alumno.estado = 'Activo'
 * 
 * DATOS RETORNADOS:
 * JSON: {
 *   "success": true/false,
 *   "data": [
 *     { "idMatricula", "cedula", "nombres", "apellidos" },
 *     ...
 *   ],
 *   "error": "mensaje si hay error"
 * }
 * 
 * NOTA IMPORTANTE:
 * - El filtro idMatricula usa estado='Vigente' 
 * - Esto diferencia entre alumnos que pueden recibir notas vs. otros estados
 */
header('Content-Type: application/json; charset=utf-8');

session_name('DOCENTE_SESSION');
session_start();
require_once __DIR__ . '/../../servicios/conexion.php';

// Validar sesión
if (!isset($_SESSION['docente_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$docente_id = $_SESSION['docente_id'];
$idAulaMateria = isset($_GET['idAulaMateria']) ? (int)$_GET['idAulaMateria'] : 0;

if (!$idAulaMateria) {
    echo json_encode(['success' => false, 'error' => 'ID aula-materia requerido']);
    exit;
}

// Verificar que docente tiene acceso
$sql_check = "SELECT dam.idAsignacion 
              FROM docente_aula_materia dam
              WHERE dam.idDocente = $docente_id 
              AND dam.idAulaMateria = $idAulaMateria 
              AND dam.activo = 1";

$check = buscar_datos($sql_check);
if (!$check) {
    echo json_encode(['success' => false, 'error' => 'Sin acceso a esta materia']);
    exit;
}

// Obtener idAula
$sql_aula = "SELECT idAula FROM aula_materia WHERE idAulaMateria = $idAulaMateria";
$aula_data = buscar_datos($sql_aula);
$idAula = $aula_data[0]['idAula'] ?? 0;

// Obtener alumnos del aula
$sql = "SELECT 
    m.idMatricula,
    a.cedula,
    a.nombres,
    a.apellidos
FROM matricula m
JOIN alumno a ON m.idAlumno = a.idAlumno
WHERE m.idAula = $idAula 
AND m.estado = 'Vigente'
AND a.estado = 'Activo'
ORDER BY a.apellidos ASC, a.nombres ASC";

$datos = buscar_datos($sql);

if ($datos) {
    echo json_encode(['success' => true, 'data' => $datos]);
} else {
    echo json_encode(['success' => false, 'error' => 'No hay alumnos', 'data' => []]);
}
?>
