<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $idCurso = $_GET['idCurso'] ?? null;
    $idEnfasis = $_GET['idEnfasis'] ?? null;

    $sql = "SELECT 
                m.idMatricula,
                m.idAlumno,
                m.idAula,
                a.nombres,
                a.apellidos,
                a.cedula,
                DATE_FORMAT(m.fecha_matricula, '%d/%m/%Y') as fecha_matricula,
                m.observacion,
                m.estado,
                CONCAT(c.nombre, ' - ', e.nombre) as nombreAula
            FROM matricula m
            JOIN alumno a ON m.idAlumno = a.idAlumno
            JOIN aula au ON m.idAula = au.idAula
            JOIN curso c ON au.idCurso = c.idCurso
            JOIN enfasis e ON au.idEnfasis = e.idEnfasis
            WHERE 1=1";

                
    $sqlNoMatriculados = "SELECT idAlumno FROM alumno a
                        WHERE a.idAlumno NOT IN (SELECT idAlumno FROM matricula)"; 
    // FILTRO CURSO
    if($idCurso && $idCurso != 'Todos'){
        $sql .= " AND au.idCurso = '$idCurso'";
    }

    // FILTRO ENFASIS
    if($idEnfasis && $idEnfasis != 'Todos'){
        $sql .= " AND au.idEnfasis = '$idEnfasis'";
    }

    //FILTRO FECHA

    $datos = buscar_datos($sql);
    $datosN = buscar_datos($sqlNoMatriculados);

    // RESPUESTA JSON - Un solo JSON con ambos datos
    echo json_encode([
        "data" => $datos,
        "dataN" => $datosN
    ]); 




?>