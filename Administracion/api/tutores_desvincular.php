<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = ['status' => false, 'msg' => ''];

    if (empty($_POST)) {
        $response['msg'] = 'No se recibieron datos.';
        echo json_encode($response);
        exit;
    }

    $idRelacion = (int)limpiar_cadena($_POST['id_alumno_tutor'] ?? '');

    if (!$idRelacion) {
        $response['msg'] = 'Identificador de relación no válido.';
        echo json_encode($response);
        exit;
    }

    // Verificar que la relación exista
    $sql_buscar = "SELECT idAlumnoTutor, idAlumno, idTutor, es_principal
                   FROM alumno_tutor
                   WHERE idAlumnoTutor = $idRelacion";
    $relacion = buscar_datos($sql_buscar);

    if (!$relacion) {
        $response['msg'] = 'La relación alumno-tutor no fue encontrada.';
        echo json_encode($response);
        exit;
    }

    // Eliminar la relación
    $sql_delete = "DELETE FROM alumno_tutor WHERE idAlumnoTutor = $idRelacion";
    $resultado  = eliminar_datos($sql_delete);

    if ($resultado) {
        $response['status'] = true;
        $response['msg'] = 'Alumno desvinculado del tutor correctamente.';
    } else {
        $response['msg'] = 'Error al desvincular el alumno.';
    }

    echo json_encode($response);
?>