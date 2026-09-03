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

$docente_id = (int)($_SESSION['docente_id'] ?? 0);
if ($docente_id <= 0) json_response(false, 'Docente no identificado');

// Año activo
$resAnio = buscar_datos("SELECT idAnio FROM anio_lectivo WHERE activo = 'Sí' LIMIT 1");
if (!$resAnio) json_response(false, 'No hay año lectivo activo');
$idAnio = (int)$resAnio[0]['idAnio'];

$idAulaMateria = isset($_GET['idAulaMateria']) ? (int)$_GET['idAulaMateria'] : 0;
if ($idAulaMateria <= 0) json_response(false, 'idAulaMateria inválido');

// Verificar que la materia pertenece al docente
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

// Alumnos en Recuperatorio1 o Recuperatorio2
$alumnos = buscar_datos("
    SELECT a.idAlumno, a.nombres, a.apellidos, a.cedula,
           m.idMatricula,
           nf.idNotaFinal, nf.nota_sem1, nf.nota_sem2,
           nf.nota_final, nf.nota_rec1, nf.nota_rec2,
           nf.nota_definitiva, nf.estado AS estado_final
    FROM nota_final_materia nf
    JOIN matricula m ON m.idMatricula = nf.idMatricula
    JOIN alumno a ON a.idAlumno = m.idAlumno
    WHERE nf.idAulaMateria = $idAulaMateria
      AND nf.estado IN ('Recuperatorio1', 'Recuperatorio2')
    ORDER BY a.apellidos, a.nombres
");

json_response(true, 'OK', $alumnos ?: []);
?>
