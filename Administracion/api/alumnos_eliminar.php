<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = ['status' => false, 'msg' => ''];

    if (empty($_POST)) {
        $response['msg'] = 'No se recibieron datos.';
        echo json_encode($response);
        exit;
    }

    $id = (int)limpiar_cadena($_POST['id_alumno'] ?? '');

    if (!$id) {
        $response['msg'] = 'Identificador no válido.';
        echo json_encode($response);
        exit;
    }

    // Verificar que el alumno exista
    $sql_buscar = "SELECT idAlumno, nombres, apellidos, estado FROM alumno WHERE idAlumno = $id";
    $alumno = buscar_datos($sql_buscar);

    if (!$alumno) {
        $response['msg'] = 'El alumno no fue encontrado.';
        echo json_encode($response);
        exit;
    }

    // No eliminar si tiene matrícula vigente
    $sql_mat = "SELECT idMatricula FROM matricula
                WHERE idAlumno = $id AND estado = 'Vigente'";
    if (buscar_datos($sql_mat)) {
        $response['msg'] = 'No se puede eliminar el alumno porque tiene una matrícula vigente. '
                         . 'Primero cambie el estado de la matrícula.';
        echo json_encode($response);
        exit;
    }

    // No eliminar si tiene calificaciones registradas
    $sql_notas = "SELECT n.idNota FROM nota n
                  INNER JOIN matricula m ON m.idMatricula = n.idMatricula
                  WHERE m.idAlumno = $id LIMIT 1";
    if (buscar_datos($sql_notas)) {
        $response['msg'] = 'No se puede eliminar el alumno porque tiene calificaciones registradas. '
                         . 'Si desea inhabilitarlo, cambie su estado a Retirado o Inactivo.';
        echo json_encode($response);
        exit;
    }

    // No eliminar si tiene asistencias registradas
    $sql_asistencia = "SELECT a2.idDetalle FROM asistencia_detalle a2
                       INNER JOIN matricula m ON m.idMatricula = a2.idMatricula
                       WHERE m.idAlumno = $id LIMIT 1";
    if (buscar_datos($sql_asistencia)) {
        $response['msg'] = 'No se puede eliminar el alumno porque tiene asistencias registradas. '
                         . 'Cambie su estado a Retirado o Inactivo para inhabilitarlo.';
        echo json_encode($response);
        exit;
    }

    // Desvincular tutores (FK en alumno_tutor)
    eliminar_datos("DELETE FROM alumno_tutor WHERE idAlumno = $id");

    // Eliminar matrículas históricas sin notas ni asistencias
    eliminar_datos("DELETE FROM matricula WHERE idAlumno = $id");

    // Eliminar el alumno
    $sql_delete = "DELETE FROM alumno WHERE idAlumno = $id";
    $resultado  = eliminar_datos($sql_delete);

    if ($resultado) {
        $response['status'] = true;
        $response['msg'] = 'Alumno eliminado correctamente.';
    } else {
        $response['msg'] = 'Error al eliminar el alumno. Puede tener otros registros relacionados.';
    }

    echo json_encode($response);
?>