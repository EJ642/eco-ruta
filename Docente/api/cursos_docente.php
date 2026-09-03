<?php
// API: Obtener cursos donde el docente tiene materias asignadas
header('Content-Type: application/json');

require_once __DIR__ . '/../../servicios/conexion.php';

session_name('DOCENTE_SESSION');
session_start();

// Verificar que sea docente
if (!isset($_SESSION['docente_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$docente_id = $_SESSION['docente_id'];

// Query para obtener cursos distintos donde el docente tiene materias
$sql = "SELECT DISTINCT a.idAula, CONCAT(c.numero, '° ', e.nombre, ' - ', t.turno) as curso
FROM docente_aula_materia dam
JOIN aula_materia am ON dam.idAulaMateria = am.idAulaMateria
JOIN aula a ON am.idAula = a.idAula
JOIN curso c ON a.idCurso = c.idCurso
JOIN enfasis e ON a.idEnfasis = e.idEnfasis
JOIN turno t ON c.idTurno = t.idTurno
JOIN anio_lectivo an ON a.idAnio = an.idAnio
WHERE dam.idDocente = $docente_id 
  AND dam.activo = 1 
  AND a.activo = 'Sí'
  AND an.activo = 'Sí'
ORDER BY c.numero, e.nombre";

$datos = buscar_datos($sql);

if ($datos) {
    echo json_encode(['success' => true, 'data' => $datos]);
} else {
    echo json_encode(['success' => false, 'message' => 'No hay cursos asignados']);
}