<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = ['status' => false, 'msg' => ''];

    if (empty($_POST)) {
        $response['msg'] = 'No se recibieron datos.';
        echo json_encode($response);
        exit;
    }

    $id = (int)limpiar_cadena($_POST['id_tutor'] ?? '');

    if (!$id) {
        $response['msg'] = 'Identificador no válido.';
        echo json_encode($response);
        exit;
    }

    // Verificar que el tutor exista
    $sql_buscar = "SELECT idTutor, idUsuario FROM tutor WHERE idTutor = $id";
    $tutor_data = buscar_datos($sql_buscar);

    if (!$tutor_data) {
        $response['msg'] = 'El tutor no fue encontrado.';
        echo json_encode($response);
        exit;
    }

    // Desvincular de tabla alumno_tutor antes de eliminar
    actualizar_datos("DELETE FROM alumno_tutor WHERE idTutor = $id");

    // Desvincular cuenta de usuario (FK → NULL) si tiene una asignada
    $idUsuario = (int)($tutor_data[0]['idUsuario'] ?? 0);
    if ($idUsuario > 0) {
        actualizar_datos("UPDATE usuarios SET estado = 'Inactivo', modificado = NOW()
                          WHERE idUsuario = $idUsuario");
        // Dejar idUsuario en NULL para no romper FK
        actualizar_datos("UPDATE tutor SET idUsuario = NULL WHERE idTutor = $id");
    }

    // Eliminar el tutor
    $sql_delete = "DELETE FROM tutor WHERE idTutor = $id";
    $resultado  = eliminar_datos($sql_delete);

    if ($resultado) {
        $response['status'] = true;
        $response['msg'] = 'Tutor eliminado correctamente.';
    } else {
        $response['msg'] = 'Error al eliminar el tutor. Puede tener registros relacionados que impiden la eliminación.';
    }

    echo json_encode($response);
?>