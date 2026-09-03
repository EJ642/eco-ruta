<?php
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {

    $idAlumnoTutor = isset($_POST['idAlumnoTutor']) ? intval($_POST['idAlumnoTutor']) : 0;

    if ($idAlumnoTutor <= 0) {
    $response['msg'] = 'Error: Identificador no válido.';
    echo json_encode($response);
    exit;
    }
        
        $sql_delete = "DELETE FROM alumno_tutor WHERE idAlumnoTutor = $idAlumnoTutor";
        $resultado = eliminar_datos($sql_delete);

        if ($resultado) {
            $response['status'] = true;
            $response['msg'] = 'Asignación eliminada correctamente.';
        } else {
            $response['msg'] = 'Error al eliminar el registro.';
        }
    
    

        

    } else {
        $response['msg'] = 'No se recibieron datos.';
    }

    echo json_encode($response);

?>