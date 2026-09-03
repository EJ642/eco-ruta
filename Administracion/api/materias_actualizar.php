<?php
     require_once __DIR__ . '/../../servicios/conexion.php';

     $response = array('status' => false, 'msg' => '');

     if (!empty($_POST)) {

        $id      = limpiar_cadena($_POST['id_materia']);
        $nombre = limpiar_cadena($_POST['nombre']);
        $codigo = limpiar_cadena($_POST['codigo']);
        $horas_sem = limpiar_cadena($_POST['horas_sem']);
        $idEnfasis = limpiar_cadena($_POST['idEnfasis']);
        $plan = limpiar_cadena($_POST['plan']);
        $idCurso = limpiar_cadena($_POST['idCurso']);

        if (empty($nombre) || empty($codigo) || empty($horas_sem) || empty($idEnfasis) || empty($plan) || empty($idCurso)) {
            $response['msg'] = 'No deje vacio ningun campo.';
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
                          OR nombre = '$nombre' HAVING idMateria != '$id'";
        $existe = buscar_datos($sql_verificar);

        if ($existe) {
            $response['msg'] = 'El código y/o el nombre de la materia ya existe.';
        } else {


            $sql_update = "UPDATE materia 
                           SET nombre = '$nombre', codigo = '$codigo', horas_sem = '$horas_sem', idEnfasis = '$idEnfasis', 
                                plan = '$plan', idCurso = '$idCurso'
                           WHERE idMateria = $id";

               
               $resultado = actualizar_datos($sql_update);

               if ($resultado) {
                    $response['status'] = true;
                    $response['msg'] = 'Materia actualizada correctamente.';
               } else {
                    $response['msg'] = 'Error al actualizar en la base de datos.';
               }
          }

     } else {
          $response['msg'] = 'No se recibieron datos.';
     }

     echo json_encode($response);
?>
