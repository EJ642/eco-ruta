<?php
     header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/../../servicios/conexion.php';

     $response = array('status' => false, 'msg' => '');

     if (!empty($_POST)) {

        $id = isset($_POST['id_enfasis']) ? intval($_POST['id_enfasis']) : 0;

        if ($id <= 0) {
        $response['msg'] = 'Error: Identificador no válido.';
        echo json_encode($response);
        exit;
        }
    
            // Verificar si la especialidad tiene registros en otras tablas antes de eliminar
            $sql_verificar = "SELECT * FROM materia WHERE idEnfasis = $id";
            $existe = buscar_datos($sql_verificar);

            if ($existe) {
                $response['msg'] = 'Esta especialidad tiene registros en otra tabla y no se puede eliminar directamente.';
                $response['code'] = 'HAS_DEPENDENCIES';
            } else {
                $sql_delete = "DELETE FROM enfasis WHERE idEnfasis = $id";
                $resultado = eliminar_datos($sql_delete);

                if ($resultado) {
                    $response['status'] = true;
                    $response['msg'] = 'Especialidad eliminada correctamente.';
                } else {
                    $response['msg'] = 'Error al eliminar el registro.';
                }
            }
        

          

     } else {
        $response['msg'] = 'No se recibieron datos.';
     }

     echo json_encode($response);
?>