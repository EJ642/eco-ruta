<?php

    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {

        $turno = limpiar_cadena($_POST['turno']);
        $descripcion = limpiar_cadena($_POST['descripcion']);
        
        if (empty($turno) || empty($descripcion)) {
            $response['msg'] = 'Todos los campos son obligatorios.';
            echo json_encode($response);
            exit;
        }

        //Validar que solo se ingrese una letra para el turno
        if (strlen($turno) !== 1 || !ctype_alpha($turno)) {
            $response['msg'] = 'El turno debe ser una sola letra.';
            echo json_encode($response);
            exit;
        }

        // Verificar si el codigo o el nombre ya existe
        $sql_verificar = "SELECT * FROM turno WHERE turno = '$turno'
                          OR descripcion = '$descripcion'";
        $existe = buscar_datos($sql_verificar);

        if ($existe) {
            $response['msg'] = 'El turno ya existe.';
        } else {
            
            $sql_insert = "INSERT INTO turno (turno, descripcion) 
                           VALUES ('$turno', '$descripcion')";

            $resultado = insertar_datos($sql_insert);

            if ($resultado) {
                $response['status'] = true;
                $response['msg'] = 'Turno creado correctamente.';
            } else {
                $response['msg'] = 'Error al guardar en la base de datos.';
            }
        }

    } else {
        $response['msg'] = 'No se recibieron datos.';
    }

    echo json_encode($response);




?>