<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = ['status' => false, 'msg' => ''];

    if (empty($_POST)) {
        $response['msg'] = 'No se recibieron datos.';
        echo json_encode($response);
        exit;
    }

    $id = (int)limpiar_cadena($_POST['id_docente'] ?? '');

    if (!$id) {
        $response['msg'] = 'Identificador no válido.';
        echo json_encode($response);
        exit;
    }

    // Verificar que el docente exista
    $sql_buscar = "SELECT idDocente, nombres, apellidos, estado, idUsuario FROM docente WHERE idDocente = $id";
    $docente = buscar_datos($sql_buscar);

    if (!$docente) {
        $response['msg'] = 'El docente no fue encontrado.';
        echo json_encode($response);
        exit;
    }

    // Verificar si el docente tiene un usuario asociado
    if (!empty($docente['idUsuario'])) {
        $response['msg'] = 'No se puede eliminar el docente porque tiene un usuario asociado.';
        echo json_encode($response);
        exit;
    }

    // Eliminar el docente
    $sql_delete = "DELETE FROM docente WHERE idDocente = $id";
    $resultado  = eliminar_datos($sql_delete);

    if ($resultado) {
        $response['status'] = true;
        $response['msg'] = 'Docente eliminado correctamente.';
    } else {
        $response['msg'] = 'Error al eliminar el docente. Puede tener otros registros relacionados.';
    }

    echo json_encode($response);
?>