<?php
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/../../servicios/conexion.php';

     $response = array('status' => false, 'msg' => '');

     if (!empty($_POST)) {

        $id = isset($_POST['idAsignacion']) ? intval($_POST['idAsignacion']) : 0;
        $idAulaMateria = isset($_POST['idAulaMateria']) ? intval($_POST['idAulaMateria']) : 0;

        if ($id <= 0) {
        $response['msg'] = 'Error: Identificador no válido.';
        echo json_encode($response);
        exit;
        }
    
            // Verificar si el registro tiene dependencias en otras tablas antes de eliminar
            $sql_verificar = "SELECT COUNT(*) as total FROM asistencia_sesion
                                WHERE idAulaMateria = $idAulaMateria
                                UNION ALL
                                SELECT COUNT(*) as total FROM evaluacion
                                WHERE idAulaMateria = $idAulaMateria";
            $resultado_verificar = buscar_datos($sql_verificar);
            
            $tiene_dependencias = false;
            if ($resultado_verificar) {
                foreach ($resultado_verificar as $fila) {
                    if ($fila['total'] > 0) {
                        $tiene_dependencias = true;
                        break;
                    }
                }
            }

            if ($tiene_dependencias) {
                $response['msg'] = 'Esta asignación tiene registros en otra tabla y no se puede eliminar directamente.';
                $response['code'] = 'HAS_DEPENDENCIES';
            } else {
                $sql_delete = "DELETE FROM docente_aula_materia WHERE idAsignacion = $id";
                $resultado = eliminar_datos($sql_delete);

                if ($resultado) {
                    $response['status'] = true;
                    $response['msg'] = 'Asignación eliminada correctamente.';
                } else {
                    $response['msg'] = 'Error al eliminar el registro.';
                }
            }
        

          

     } else {
        $response['msg'] = 'No se recibieron datos.';
     }

     echo json_encode($response);

?>