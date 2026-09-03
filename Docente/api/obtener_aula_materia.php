<?php
// API: Obtener datos de un aula-materia específica
header('Content-Type: application/json');

require_once __DIR__ . '/../../servicios/conexion.php';

session_name('DOCENTE_SESSION');
session_start();

if (!isset($_SESSION['docente_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$docente_id = $_SESSION['docente_id'];

// Permitir recibir por GET o POST
$idAulaMateria = 0;
if (isset($_GET['idAulaMateria'])) {
    $idAulaMateria = (int)$_GET['idAulaMateria'];
} elseif (isset($_POST['idAulaMateria'])) {
    $idAulaMateria = (int)$_POST['idAulaMateria'];
}
if (!$idAulaMateria) {
    echo json_encode(['success' => false, 'message' => 'ID de aula-materia requerido']);
    exit;
}

// Verificar que el docente tiene acceso
$sql = "SELECT am.idAulaMateria, am.idMateria, a.idAula, c.numero, e.nombre as enfoque, m.nombre as materia
FROM aula_materia am
JOIN aula a ON am.idAula = a.idAula
JOIN curso c ON a.idCurso = c.idCurso
JOIN enfasis e ON a.idEnfasis = e.idEnfasis
JOIN materia m ON am.idMateria = m.idMateria
JOIN docente_aula_materia dam ON am.idAulaMateria = dam.idAulaMateria
WHERE dam.idDocente = $docente_id 
  AND dam.activo = 1
  AND am.idAulaMateria = $idAulaMateria";

$datos = buscar_datos($sql);

if ($datos) {
    echo json_encode(['success' => true, 'data' => $datos[0]]);
} else {
    echo json_encode(['success' => false, 'message' => 'No tiene acceso a esta materia']);
}