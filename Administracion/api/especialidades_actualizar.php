<?php
     require_once __DIR__ . '/../../servicios/conexion.php';

     $response = array('status' => false, 'msg' => '');

     if (!empty($_POST)) {

        $id = limpiar_cadena($_POST['id_enfasis']);
        $nombre = limpiar_cadena($_POST['nombre']);
        $descripcion = limpiar_cadena($_POST['descripcion']);
        $estado = limpiar_cadena($_POST['estado']);
        
        if (empty($nombre) || empty($descripcion) || empty($estado)) {
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

        // Verificar si el numero o el nombre ya existe
        $sql_verificar = "SELECT * FROM enfasis WHERE nombre = '$nombre' HAVING idEnfasis != '$id'";
        $existe = buscar_datos($sql_verificar);

        if ($existe) {
            $response['msg'] = 'El nombre de la especialidad ya existe.';
        } else {


            $sql_update = "UPDATE enfasis 
                           SET nombre = '$nombre', descripcion = '$descripcion', activo = '$estado'
                           WHERE idEnfasis = $id";

               
               $resultado = actualizar_datos($sql_update);

               if ($resultado) {
                    $response['status'] = true;
                    $response['msg'] = 'Especialidad actualizada correctamente.';
               } else {
                    $response['msg'] = 'Error al actualizar en la base de datos.';
               }
          }

     } else {
          $response['msg'] = 'No se recibieron datos.';
     }

     echo json_encode($response);
?>
