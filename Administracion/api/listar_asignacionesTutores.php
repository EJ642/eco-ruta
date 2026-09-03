<?php 

    require_once __DIR__ . '/../../servicios/conexion.php';

    $idTutor = isset($_GET['idTutor']) ? intval($_GET['idTutor']) : 0;
    $idAlumno = isset($_GET['idAlumno']) ? intval($_GET['idAlumno']) : 0;

    if ($idTutor > 0 || $idAlumno > 0) {
        $sql = "SELECT alt.idAlumnoTutor,
                      t.idTutor,
                      a.idAlumno,
                      CONCAT(t.nombres, ' ', t.apellidos, '-', t.cedula) as nombre_tutor,
                      CONCAT(a.nombres, ' ', a.apellidos, '-', a.cedula) as nombre_alumno,
                      t.parentesco,
                      alt.es_principal
                     FROM alumno_tutor alt
                     INNER JOIN tutor t ON alt.idTutor = t.idTutor
                     INNER JOIN alumno a ON alt.idAlumno = a.idAlumno";

        $filters = [];
        if ($idTutor > 0) {
            $filters[] = "alt.idTutor = $idTutor";
        }
        if ($idAlumno > 0) {
            $filters[] = "alt.idAlumno = $idAlumno";
        }

        if ($filters) {
            $sql .= ' WHERE ' . implode(' OR ', $filters);
        }

        $asignacionesTutores = buscar_datos($sql);
        echo json_encode([
            "data" => $asignacionesTutores
        ]);
    } else {
        echo json_encode(["data" => []]);
    }

?>
     