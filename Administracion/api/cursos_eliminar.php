<?php
     header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/../../servicios/conexion.php';

     $response = array('status' => false, 'msg' => '');

     if (!empty($_POST)) {

        $id = isset($_POST['id_curso']) ? intval($_POST['id_curso']) : 0;

        if ($id <= 0) {
        $response['msg'] = 'Error: Identificador no válido.';
        echo json_encode($response);
        exit;
        }

    
            // Verificar si el curso tiene registros en otras tablas antes de eliminar
            $sql_verificar = "SELECT * FROM materia WHERE idCurso = $id";
            $existe = buscar_datos($sql_verificar);

            if ($existe) {
                $response['msg'] = 'Este curso tiene registros en otra tabla y no se puede eliminar directamente.';
                $response['code'] = 'HAS_DEPENDENCIES';
            } else {
                $sql_delete = "DELETE FROM curso WHERE idCurso = $id";
                $resultado = eliminar_datos($sql_delete);

                if ($resultado) {
                    $response['status'] = true;
                    $response['msg'] = 'Curso eliminado correctamente.';
                } else {
                    $response['msg'] = 'Error al eliminar el registro.';
                }
            }
        

          

     } else {
        $response['msg'] = 'No se recibieron datos.';
     }

     echo json_encode($response);
?>