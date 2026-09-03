<?php 

    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if(!empty($_POST)){
        $id_matricula = limpiar_cadena($_POST['id_matricula']);
        $idAlumno = limpiar_cadena($_POST['idAlumno']);
        $idAula = limpiar_cadena($_POST['idAula']);
        $fecha_input = $_POST['fecha'];

        $fecha = DateTime::createFromFormat('d/m/Y', $fecha_input);
        $fecha = $fecha->format('Y-m-d');

        $estado = limpiar_cadena($_POST['estado']);
        $observacion = limpiar_cadena($_POST['Observacion']);

        if(empty($id_matricula) || empty($idAlumno) || empty($idAula) || empty($fecha) || empty($estado)){
            $response['msg'] = 'Todos los campos son obligatorios.';
            echo json_encode($response);
            exit;
        }

        $sql_update = "UPDATE matricula 
                       SET idAlumno = '$idAlumno', 
                           idAula = '$idAula', 
                           fecha_matricula = '$fecha', 
                           estado = '$estado', 
                           observacion = '$observacion' 
                       WHERE idMatricula = '$id_matricula'";

        $resultado = actualizar_datos($sql_update);

        if($resultado){
            $response['status'] = true;
            $response['msg'] = 'Matrícula actualizada correctamente.';
        } else {
            $response['msg'] = 'Error al actualizar en la base de datos.';
        }

    } else {
        $response['msg'] = 'No se recibieron datos.';
    }

    echo json_encode($response);

?>