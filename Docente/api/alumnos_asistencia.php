<?php
// API: Obtener alumnos para registro de asistencia (nueva estructura: cabecera + detalle)
header('Content-Type: application/json');

require_once __DIR__ . '/../../servicios/conexion.php';

session_name('DOCENTE_SESSION');
session_start();

if (!isset($_SESSION['docente_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$docente_id = $_SESSION['docente_id'];
$idAulaMateria = isset($_GET['idAulaMateria']) ? (int)$_GET['idAulaMateria'] : 0;
$fecha = $_GET['fecha'] ?? date('Y-m-d');

if (!$idAulaMateria) {
    echo json_encode(['success' => false, 'message' => 'ID de aula-materia requerido']);
    exit;
}

// Verificar que el docente tiene acceso a esta aula-materia
$sql_check = "SELECT dam.idAsignacion 
FROM docente_aula_materia dam
WHERE dam.idDocente = $docente_id 
  AND dam.idAulaMateria = $idAulaMateria 
  AND dam.activo = 1";

$check = buscar_datos($sql_check);
if (!$check) {
    echo json_encode(['success' => false, 'message' => 'No tiene acceso a esta materia']);
    exit;
}

// Obtener el idAula de la aula_materia
$sql_aula = "SELECT idAula FROM aula_materia WHERE idAulaMateria = $idAulaMateria";
$aula_data = buscar_datos($sql_aula);
$idAula = $aula_data[0]['idAula'] ?? 0;

// Obtener alumnos matriculados en el aula con su asistencia de la fecha seleccionada
// Usando la nueva estructura: asistencia_sesion (cabecera) + asistencia_detalle
$sql = "SELECT 
    m.idMatricula,
    a.cedula,
    a.nombres,
    a.apellidos,
    COALESCE(ad.estado, 'Ausente') as estado,
    COALESCE(ad.observacion, '') as observacion,
    COALESCE(asess.cantidad_horas, 1) as cantidad_horas
FROM matricula m
JOIN alumno a ON m.idAlumno = a.idAlumno
LEFT JOIN asistencia_sesion asess ON asess.idAulaMateria = $idAulaMateria AND asess.fecha = '$fecha'
LEFT JOIN asistencia_detalle ad ON asess.idSesion = ad.idSesion AND ad.idMatricula = m.idMatricula
WHERE m.idAula = $idAula 
  AND m.estado = 'Vigente'  /* para que traiga alumnos de matricula vigente*/
  AND a.estado = 'Activo'
ORDER BY a.apellidos, a.nombres";

$datos = buscar_datos($sql);

if ($datos) {
    echo json_encode(['success' => true, 'data' => $datos]);
} else {
    echo json_encode(['success' => false, 'message' => 'No hay alumnos matriculados']);
}