<?php

    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {

        $nombre = limpiar_cadena($_POST['nombre']);
        $codigo = limpiar_cadena($_POST['codigo']);
        $horas_sem = limpiar_cadena($_POST['horas_sem']);
        $idEnfasis = limpiar_cadena($_POST['idEnfasis']);
        $plan = limpiar_cadena($_POST['plan']);
        $idCurso = limpiar_cadena($_POST['idCurso']);

        if (empty($nombre) || empty($codigo) || empty($horas_sem) || empty($idEnfasis) || empty($plan) || empty($idCurso)) {
            $response['msg'] = 'Todos los campos son obligatorios.';
            echo json_encode($response);
            exit;
        }

        //Validar que el codigo solo contenga letras, numeros y guiones
        if (!preg_match('/^[A-Za-z0-9\-]+$/', $codigo)) {
            $response['msg'] = 'El campo Código solo puede contener letras, números y guiones.';
            echo json_encode($response);
            exit;
        }

        //Validar que el nombre solo contenga letras, numeros y espacios
        if (!preg_match('/^[A-Za-z0-9\s\-]+$/', $nombre)) {
            $response['msg'] = 'El campo Nombre solo puede contener letras, números y espacios.';
            echo json_encode($response);
            exit;
        }

        // Verificar si el codigo o el nombre ya existe
        $sql_verificar = "SELECT * FROM materia WHERE codigo = '$codigo'
                          OR nombre = '$nombre'";
        $existe = buscar_datos($sql_verificar);

        if ($existe) {
            $response['msg'] = 'El código y/o el nombre de la materia ya existe.';
        } else {
            
            $sql_insert = "INSERT INTO materia (nombre, codigo, horas_sem, idEnfasis, activo, plan, idCurso) 
                           VALUES ('$nombre', '$codigo', '$horas_sem', '$idEnfasis', '1', '$plan', '$idCurso')";

            $resultado = insertar_datos($sql_insert);

            if ($resultado) {
                $response['status'] = true;
                $response['msg'] = 'Materia creada correctamente.';
            } else {
                $response['msg'] = 'Error al guardar en la base de datos.';
            }
        }

    } else {
        $response['msg'] = 'No se recibieron datos.';
    }

    echo json_encode($response);




?>