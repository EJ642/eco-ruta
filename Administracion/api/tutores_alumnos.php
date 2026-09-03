<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    header('Content-Type: application/json; charset=utf-8');

    $idTutor = (int)($_GET['id_tutor'] ?? 0);

    if (!$idTutor) {
        echo json_encode([]);
        exit;
    }

    // Verificar que el tutor exista
    $sql_tutor = "SELECT idTutor FROM tutor WHERE idTutor = $idTutor";
    if (!buscar_datos($sql_tutor)) {
        echo json_encode([]);
        exit;
    }

    // Obtener alumnos asignados al tutor
    $sql = "SELECT at2.idAlumnoTutor,
                   at2.es_principal,
                   a.idAlumno,
                   a.cedula,
                   a.nombres,
                   a.apellidos,
                   a.estado AS estado_alumno
            FROM alumno_tutor at2
            INNER JOIN alumno a ON a.idAlumno = at2.idAlumno
            WHERE at2.idTutor = $idTutor
            ORDER BY at2.es_principal DESC, a.apellidos, a.nombres";

    $resultado = buscar_datos($sql);

    echo json_encode($resultado ?: []);
?>