<?php
     require_once __DIR__ . '/../../servicios/conexion.php';

     $response = array('status' => false, 'msg' => '');

     if (!empty($_POST)) {

        $id = limpiar_cadena($_POST['id_curso']);
        $nombre = limpiar_cadena($_POST['nombre']);
        $numero = limpiar_cadena($_POST['numero']);
        $idTurno = limpiar_cadena($_POST['idTurno']);
        
        if (empty($nombre) || empty($numero) || empty($idTurno)) {
            $response['msg'] = 'Todos los campos son obligatorios.';
            echo json_encode($response);
            exit;
        }

        // Verificar si el numero o el nombre ya existe
        $sql_verificar = "SELECT * FROM curso WHERE numero = '$numero'
                          OR nombre = '$nombre' HAVING idCurso != '$id'";
        $existe = buscar_datos($sql_verificar);

        if ($existe) {
            $response['msg'] = 'El número y/o el nombre del curso ya existe.';
        } else {


            $sql_update = "UPDATE curso 
                           SET nombre = '$nombre', numero = '$numero', idTurno = '$idTurno'
                           WHERE idCurso = $id";

               
               $resultado = actualizar_datos($sql_update);

               if ($resultado) {
                    $response['status'] = true;
                    $response['msg'] = 'Curso actualizado correctamente.';
               } else {
                    $response['msg'] = 'Error al actualizar en la base de datos.';
               }
          }

     } else {
          $response['msg'] = 'No se recibieron datos.';
     }

     echo json_encode($response);
?>
