<?php
     header('Content-Type: application/json; charset=utf-8');
     require_once __DIR__ . '/../../servicios/conexion.php';

     $response = array('status' => false, 'msg' => '');

     if (!empty($_POST)) {

        $id = isset($_POST['id_materia']) ? intval($_POST['id_materia']) : 0;

        if ($id <= 0) {
        $response['msg'] = 'Error: Identificador no válido.';
        echo json_encode($response);
        exit;
        }

    
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        // Si se solicita desactivar en lugar de eliminar
        if ($action === 'deactivate') {
            $sql_desactivar = "UPDATE materia SET activo = 0 WHERE idMateria = $id";
            $resultado_desactivar = actualizar_datos($sql_desactivar);

            if ($resultado_desactivar) {
                $response['status'] = true;
                $response['msg'] = 'Materia desactivada correctamente.';
            } else {
                $response['msg'] = 'Error al desactivar el registro.';
            }
        } else {
            // Verificar si la materia tiene registros en otras tablas antes de eliminar
            $sql_verificar = "SELECT * FROM aula_materia WHERE idMateria = $id";
            $existe = buscar_datos($sql_verificar);

            if ($existe) {
                $response['msg'] = 'Esta materia tiene registros en otra tabla y no se puede eliminar directamente.';
                $response['code'] = 'HAS_DEPENDENCIES';
            } else {
                $sql_delete = "DELETE FROM materia WHERE idMateria = $id";
                $resultado = eliminar_datos($sql_delete);

                if ($resultado) {
                    $response['status'] = true;
                    $response['msg'] = 'Materia eliminada correctamente.';
                } else {
                    $response['msg'] = 'Error al eliminar el registro.';
                }
            }
        }

          

     } else {
        $response['msg'] = 'No se recibieron datos.';
     }

     echo json_encode($response);
?>