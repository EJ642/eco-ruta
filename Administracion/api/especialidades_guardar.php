<?php

    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {

        $nombre = limpiar_cadena($_POST['nombre']);
        $descripcion = limpiar_cadena($_POST['descripcion']);
        
        if (empty($nombre) || empty($descripcion)) {
            $response['msg'] = 'Todos los campos son obligatorios.';
            echo json_encode($response);
            exit;
        }

        //Verificar que el nombre solo contenga letras y espacios
        if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑüÜ\s\'\-]+$/u', $nombre)) {
            $response['msg'] = 'El campo Nombre solo debe contener letras y espacios.';
            echo json_encode($response);
            exit;
        }

        // Verificar si el codigo o el nombre ya existe
        $sql_verificar = "SELECT * FROM enfasis WHERE nombre = '$nombre'";
        $existe = buscar_datos($sql_verificar);

        if ($existe) {
            $response['msg'] = 'El nombre de la especialidad ya existe.';
        } else {
            
            $sql_insert = "INSERT INTO enfasis (nombre, descripcion, activo) 
                           VALUES ('$nombre', '$descripcion', 'Sí')";

            $resultado = insertar_datos($sql_insert);

            if ($resultado) {
                $response['status'] = true;
                $response['msg'] = 'Especialidad creada correctamente.';
            } else {
                $response['msg'] = 'Error al guardar en la base de datos.';
            }
        }

    } else {
        $response['msg'] = 'No se recibieron datos.';
    }

    echo json_encode($response);




?>