<?php
     require_once __DIR__ . '/../../servicios/conexion.php';

     $response = array('status' => false, 'msg' => '');

     if (!empty($_POST)) {

        $id = limpiar_cadena($_POST['id_turno']);
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

        // Verificar si el numero o el nombre ya existe
        $sql_verificar = "SELECT * FROM turno WHERE turno = '$turno'
                          OR descripcion = '$descripcion' HAVING idTurno != '$id'";
        $existe = buscar_datos($sql_verificar);

        if ($existe) {
            $response['msg'] = 'El turno ya existe.';
        } else {


            $sql_update = "UPDATE turno 
                           SET turno = '$turno', descripcion = '$descripcion'
                           WHERE idTurno = $id";

               
               $resultado = actualizar_datos($sql_update);

               if ($resultado) {
                    $response['status'] = true;
                    $response['msg'] = 'Turno actualizado correctamente.';
               } else {
                    $response['msg'] = 'Error al actualizar en la base de datos.';
               }
          }

     } else {
          $response['msg'] = 'No se recibieron datos.';
     }

     echo json_encode($response);
?>
