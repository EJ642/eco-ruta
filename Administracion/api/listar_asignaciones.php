<?php 

    require_once __DIR__ . '/../../servicios/conexion.php';

    $idDocente = $_GET['idDocente'] ?? null;

    if($idDocente){
       $sql = "SELECT dam.idAsignacion,
                     dam.idDocente,
                     am.idAulaMateria,
                     CONCAT(c.numero, '° ', e.nombre, ' - ', t.turno) as nombre_aula,
                     m.nombre as nombre_materia,
                     dam.activo,
                     am.idAula, am.idMateria
               FROM docente_aula_materia dam
               JOIN aula_materia am ON dam.idAulaMateria = am.idAulaMateria
               JOIN aula a ON am.idAula = a.idAula
               JOIN curso c ON a.idCurso = c.idCurso
               JOIN enfasis e ON a.idEnfasis = e.idEnfasis
               JOIN turno t ON c.idTurno = t.idTurno
               JOIN materia m ON am.idMateria = m.idMateria
               WHERE dam.idDocente = '$idDocente'";
        $asignaciones = buscar_datos($sql);
        echo json_encode([
            "data" => $asignaciones
        ]); 
    }




?>