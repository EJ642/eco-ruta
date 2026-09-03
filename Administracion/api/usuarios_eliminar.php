<?php

    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = ['status' => false, 'msg' => ''];

    if (empty($_POST)) {
        $response['msg'] = 'No se recibieron datos.';
        echo json_encode($response);
        exit;
    }

    $id = (int)limpiar_cadena($_POST['id_usuario'] ?? '');

    if (!$id) {
        $response['msg'] = 'Identificador no válido.';
        echo json_encode($response);
        exit;
    }

    // Verificar que no sea el usuario de la sesión activa
    $id_sesion = (int)($_SESSION['idUsuario'] ?? 0);
    if ($id_sesion && $id_sesion === $id) {
        $response['msg'] = 'No podés eliminar tu propio usuario mientras estás conectado.';
        echo json_encode($response);
        exit;
    }

    // Verificar que el usuario existe y obtener su rol
    $sql_buscar = "SELECT idUsuario, idRol FROM usuarios WHERE idUsuario = $id";
    $usuario_data = buscar_datos($sql_buscar);

    if (!$usuario_data) {
        $response['msg'] = 'El usuario no fue encontrado.';
        echo json_encode($response);
        exit;
    }

    $idRol = (int)$usuario_data[0]['idRol'];

    // Desvincular docente antes de eliminar (FK → NULL)
    // Deja al docente disponible para asignar una nueva cuenta en el futuro
    if ($idRol === 3) {
        actualizar_datos("UPDATE docente SET idUsuario = NULL WHERE idUsuario = $id");
    }

    // Desvincular tutor antes de eliminar (FK → NULL)
    if ($idRol === 4) {
        actualizar_datos("UPDATE tutor SET idUsuario = NULL WHERE idUsuario = $id");
    }

    // Eliminar el usuario
    $sql_delete = "DELETE FROM usuarios WHERE idUsuario = $id";
    $resultado  = eliminar_datos($sql_delete);

    if ($resultado) {
        $response['status'] = true;
        $response['msg'] = 'Usuario eliminado correctamente.';
    } else {
        $response['msg'] = 'Error al eliminar el registro. Puede tener registros relacionados (asistencias, notas, etc.).';
    }

    echo json_encode($response);
?>