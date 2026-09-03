<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = ['status' => false, 'msg' => ''];

    if (empty($_POST)) {
        $response['msg'] = 'No se recibieron datos.';
        echo json_encode($response);
        exit;
    }

    $id = (int)limpiar_cadena($_POST['id_usuario'] ?? '');
    $usuario = limpiar_cadena($_POST['usuario'] ?? '');
    $correo = limpiar_cadena($_POST['correo'] ?? '');
    $idRol = (int)limpiar_cadena($_POST['idRol'] ?? '');
    $estado = limpiar_cadena($_POST['estado'] ?? '');
    $clave = $_POST['clave'] ?? '';

    // Validaciones de campos obligatorios
    if (!$id || empty($usuario) || empty($correo) || !$idRol || empty($estado)) {
        $response['msg'] = 'Faltan datos obligatorios.';
        echo json_encode($response);
        exit;
    }

    // Validar longitud mínima del nombre de usuario
    if (mb_strlen($usuario) < 5) {
        $response['msg'] = 'El nombre de usuario debe tener al menos 5 caracteres.';
        echo json_encode($response);
        exit;
    }

    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $response['msg'] = 'El formato del correo no es válido.';
        echo json_encode($response);
        exit;
    }

    // Validar estado
    if (!in_array($estado, ['Activo', 'Inactivo'])) {
        $response['msg'] = 'Estado no válido.';
        echo json_encode($response);
        exit;
    }

    // Verificar que el nombre de usuario no lo use otro
    $sql_check_user = "SELECT idUsuario FROM usuarios 
                       WHERE usuario = '$usuario' AND idUsuario != $id";
    if (buscar_datos($sql_check_user)) {
        $response['msg'] = 'El nombre de usuario ya está en uso por otro registro.';
        echo json_encode($response);
        exit;
    }

    // Verificar que el correo no lo use otro
    $sql_check_correo = "SELECT idUsuario FROM usuarios 
                         WHERE correo = '$correo' AND idUsuario != $id";
    if (buscar_datos($sql_check_correo)) {
        $response['msg'] = 'El correo ya pertenece a otro usuario.';
        echo json_encode($response);
        exit;
    }

    // Construir consulta según si cambia o no la contraseña
    if (empty($clave)) {
        $sql_update = "UPDATE usuarios 
                       SET usuario = '$usuario',
                           correo = '$correo',
                           idRol = $idRol,
                           estado = '$estado',
                           modificado = NOW()
                       WHERE idUsuario = $id";

    } else {

        // Validar política de contraseña segura
        if (strlen($clave) < 8 ||
            !preg_match('/[A-Z]/', $clave) ||
            !preg_match('/[0-9]/', $clave) ||
            !preg_match('/[^A-Za-z0-9]/', $clave)) {
            $response['msg'] = 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un carácter especial.';
            echo json_encode($response);
            exit;
        }

        $clave_hash = password_hash($clave, PASSWORD_BCRYPT);

        $sql_update = "UPDATE usuarios 
                       SET usuario = '$usuario',
                           correo = '$correo',
                           idRol = $idRol,
                           password = '$clave_hash',
                           estado = '$estado',
                           modificado = NOW()
                       WHERE idUsuario = $id";
    }

    $resultado = actualizar_datos($sql_update);

    if ($resultado) {
        $response['status'] = true;
        $response['msg']= 'Usuario actualizado correctamente.';
    } else {
        $response['msg'] = 'Error al actualizar en la base de datos.';
    }

    echo json_encode($response);
?>