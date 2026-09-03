<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../servicios/conexion.php';

$idAula = isset($_GET['idAula']) ? (int)$_GET['idAula'] : 0;

if (!$idAula) {
    echo json_encode(['success' => false, 'message' => 'ID de aula requerido.']);
    exit;
}

$sql = "SELECT am.idAulaMateria, am.idMateria, m.nombre as materia
        FROM aula_materia am
        JOIN materia m ON am.idMateria = m.idMateria
        JOIN aula a ON am.idAula = a.idAula
        JOIN curso c ON a.idCurso = c.idCurso
        JOIN enfasis e ON a.idEnfasis = e.idEnfasis
        JOIN turno t ON c.idTurno = t.idTurno
        JOIN anio_lectivo an ON a.idAnio = an.idAnio
        WHERE am.idAula = $idAula
          AND a.activo = 'Sí'
          AND an.activo = 'Sí'
        ORDER BY m.nombre";

$datos = buscar_datos($sql);

if ($datos) {
    echo json_encode(['success' => true, 'data' => $datos]);
} else {
    echo json_encode(['success' => false, 'message' => 'No hay materias para esta aula.']);
}
