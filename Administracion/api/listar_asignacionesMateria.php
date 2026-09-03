<?php 

    require_once __DIR__ . '/../../servicios/conexion.php';

    $idAula = $_GET['idAula'] ?? null;

    if($idAula){
       $sql = "SELECT am.idAulaMateria,
                      a.idAula,
                      m.idMateria,
                     CONCAT(c.numero, '° ', e.nombre, ' - ', t.turno) as nombre_aula,
                     m.nombre as nombre_materia,
                     am.activo
                     FROM aula_materia am
                     INNER JOIN aula a ON am.idAula = a.idAula
                     iNNER JOIN curso c ON a.idCurso = c.idCurso
                     INNER JOIN enfasis e ON a.idEnfasis = e.idEnfasis
                     INNER JOIN turno t ON c.idTurno = t.idTurno
                     INNER JOIN materia m ON am.idMateria = m.idMateria
               WHERE am.idAula = '$idAula'";
        $asignacionesMateria = buscar_datos($sql);
        echo json_encode([
            "data" => $asignacionesMateria
        ]); 
    }




?>