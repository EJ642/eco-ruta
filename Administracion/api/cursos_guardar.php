<?php

    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {

        $nombre = limpiar_cadena($_POST['nombre']);
        $numero = limpiar_cadena($_POST['numero']);
        $idTurno = limpiar_cadena($_POST['idTurno']);
        
        if (empty($nombre) || empty($numero) || empty($idTurno)) {
            $response['msg'] = 'Todos los campos son obligatorios.';
            echo json_encode($response);
            exit;
        }

        // Verificar si el codigo o el nombre ya existe
        $sql_verificar = "SELECT * FROM curso WHERE numero = '$numero'
                          OR nombre = '$nombre'";
        $existe = buscar_datos($sql_verificar);

        if ($existe) {
            $response['msg'] = 'El número y/o el nombre del curso ya existe.';
        } else {
            
            $sql_insert = "INSERT INTO curso (nombre, numero, idTurno) 
                           VALUES ('$nombre', '$numero', '$idTurno')";

            $resultado = insertar_datos($sql_insert);

            if ($resultado) {
                $response['status'] = true;
                $response['msg'] = 'Curso creado correctamente.';
            } else {
                $response['msg'] = 'Error al guardar en la base de datos.';
            }
        }

    } else {
        $response['msg'] = 'No se recibieron datos.';
    }

    echo json_encode($response);




?>