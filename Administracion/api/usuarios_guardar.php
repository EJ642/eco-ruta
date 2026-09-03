<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {

        // Datos comunes
        $idRol = limpiar_cadena($_POST['idRol'] ?? '');
        $clave = $_POST['clave'] ?? '';
        $tipo_registro = limpiar_cadena($_POST['tipo_registro'] ?? 'admin');
        $idDocente = isset($_POST['idDocente']) ? (int)$_POST['idDocente'] : 0;
        $idTutor = isset($_POST['idTutor'])   ? (int)$_POST['idTutor']   : 0;

        // Validaciones básicas
        if (empty($clave) || empty($idRol)) {
            $response['msg'] = 'Todos los campos son obligatorios.';
            echo json_encode($response);
            exit;
        }

        // Validar política de contraseña
        if (strlen($clave) < 8 ||
            !preg_match('/[A-Z]/', $clave) ||
            !preg_match('/[0-9]/', $clave) ||
            !preg_match('/[^A-Za-z0-9]/', $clave)) {
            $response['msg'] = 'La contrasena debe tener minimo 8 caracteres, una mayuscula, un numero y un caracter especial.';
            echo json_encode($response);
            exit;
        }

        // Obtener usuario y correo según tipo de registro
        if ($tipo_registro === 'docente') {

            if ($idDocente <= 0) {
                $response['msg'] = 'Debe seleccionar un docente.';
                echo json_encode($response);
                exit;
            }

            $sql_doc  = "SELECT nombres, apellidos FROM docente 
                         WHERE idDocente = '$idDocente' AND idUsuario IS NULL";
            $doc_data = buscar_datos($sql_doc);

            if (!$doc_data) {
                $response['msg'] = 'El docente no fue encontrado o ya tiene cuenta asignada.';
                echo json_encode($response);
                exit;
            }

            $nombre_completo = $doc_data[0]['nombres'] . ' ' . $doc_data[0]['apellidos'];
            $usuario = $nombre_completo;
            $correo  = limpiar_cadena($_POST['correo'] ?? '');

        } elseif ($tipo_registro === 'tutor') {

            if ($idTutor <= 0) {
                $response['msg'] = 'Debe seleccionar un tutor.';
                echo json_encode($response);
                exit;
            }

            $sql_tut  = "SELECT nombres, apellidos FROM tutor 
                         WHERE idTutor = '$idTutor' AND idUsuario IS NULL";
            $tut_data = buscar_datos($sql_tut);

            if (!$tut_data) {
                $response['msg'] = 'El tutor no fue encontrado o ya tiene cuenta asignada.';
                echo json_encode($response);
                exit;
            }

            $nombre_completo = $tut_data[0]['nombres'] . ' ' . $tut_data[0]['apellidos'];
            $usuario = $nombre_completo;
            $correo = limpiar_cadena($_POST['correo'] ?? '');

        } else {
            // Admin: usuario y correo vienen del formulario
            $usuario = limpiar_cadena($_POST['usuario'] ?? '');
            $correo = limpiar_cadena($_POST['correo']  ?? '');
        }

        // Validar longitud mínima del usuario
        if (mb_strlen($usuario) < 5) {
            $response['msg'] = 'El nombre de usuario debe tener al menos 5 caracteres.';
            echo json_encode($response);
            exit;
        }

        // Validar formato de correo
        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $response['msg'] = 'El correo electronico no es valido o esta vacio.';
            echo json_encode($response);
            exit;
        }

        // Verificar unicidad del usuario
        $existe_usuario = buscar_datos("SELECT idUsuario FROM usuarios WHERE usuario = '$usuario'");
        if ($existe_usuario) {
            $response['msg'] = 'El nombre de usuario ya esta en uso. Intenta con una variacion.';
            echo json_encode($response);
            exit;
        }

        // Verificar unicidad del correo
        $existe_correo = buscar_datos("SELECT idUsuario FROM usuarios WHERE correo = '$correo'");
        if ($existe_correo) {
            $response['msg'] = 'El correo electronico ya esta registrado.';
            echo json_encode($response);
            exit;
        }

        // Hashear contraseña
        $clave_hash = password_hash($clave, PASSWORD_BCRYPT);

        // INSERT 
        $sql_insert = "INSERT INTO usuarios (usuario, password, correo, estado, idRol) 
                       VALUES ('$usuario', '$clave_hash', '$correo', 'Activo', '$idRol')";

        $resultado = insertar_datos($sql_insert);

        if ($resultado) {

            // Obtener el ID real buscando por usuario
            $fila = buscar_datos("SELECT idUsuario FROM usuarios WHERE usuario = '$usuario' LIMIT 1");
            $nuevo_id = $fila ? (int)$fila[0]['idUsuario'] : 0;

            // Vincular con docente o tutor si corresponde
            if ($nuevo_id > 0) {
                if ($tipo_registro === 'docente') {
                    actualizar_datos("UPDATE docente SET idUsuario = '$nuevo_id' WHERE idDocente = '$idDocente'");
                }
                if ($tipo_registro === 'tutor') {
                    actualizar_datos("UPDATE tutor SET idUsuario = '$nuevo_id' WHERE idTutor = '$idTutor'");
                }
            }

            $response['status'] = true;
            $response['msg'] = 'Usuario creado correctamente.';

        } else {
            $response['msg'] = 'Error al guardar en la base de datos.';
        }

    } else {
        $response['msg'] = 'No se recibieron datos.';
    }

    echo json_encode($response);
?>