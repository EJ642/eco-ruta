<?php 

    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if(!empty($_POST)){
        $idAlumno = limpiar_cadena($_POST['idAlumno']);
        $idAula = limpiar_cadena($_POST['idAula']);
        //Formtato fecha
        $fecha_input = $_POST['fecha'];

        $fecha = DateTime::createFromFormat('d/m/Y', $fecha_input);
        $fecha = $fecha->format('Y-m-d');

        $estado = limpiar_cadena($_POST['estado']);
        $observacion = limpiar_cadena($_POST['Observacion']);

        if(empty($idAlumno) || empty($idAula) || empty($fecha) || empty($estado)){
            $response['msg'] = 'Todos los campos son obligatorios.';
            echo json_encode($response);
            exit;
        }

        // Verificar si el alumno ya está matriculado
        $sql_verificar = "SELECT * FROM matricula 
                          WHERE idAlumno = '$idAlumno' 
                          AND idAula = '$idAula'";
        $existe = buscar_datos($sql_verificar);

        if($existe){
            $response['msg'] = 'El alumno ya está matriculado.';
        } else {

            // AÑO ACTUAL
            // $anio = date("Y");

            // // BUSCAR ÚLTIMO NRO DE MATRÍCULA DEL AÑO
            // $sql_nro = "SELECT nro_matricula 
            //             FROM alumno 
            //             WHERE nro_matricula LIKE 'MAT-$anio-%' 
            //             ORDER BY nro_matricula DESC 
            //             LIMIT 1";

            // $resultado_nro = buscar_datos($sql_nro);

            // if($resultado_nro){
            //     $ultimo = $resultado_nro[0]['nro_matricula'];

            //     // Extraer número final
            //     $partes = explode('-', $ultimo);
            //     $numero = intval($partes[2]) + 1;
            // } else {
            //     $numero = 1;
            // }

            // // Formatear con ceros (001, 002, 100...)
            // $nuevo_nro = 'MAT-' . $anio . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);

            
            $sql_insert = "INSERT INTO matricula (idAlumno, idAula, fecha_matricula, estado, observacion) 
                           VALUES ('$idAlumno', '$idAula', '$fecha', '$estado', '$observacion')";

            $resultado = insertar_datos($sql_insert);

            if(empty($fecha)){
                $response['msg'] = 'Fecha inválida';
                echo json_encode($response);
                exit;
            }

            if($resultado){
                $response['status'] = true;
                $response['msg'] = 'Alumno matriculado correctamente.';
            } else {
                $response['msg'] = 'Error al guardar en la base de datos.';
            }
        }

    } else {
        $response['msg'] = 'No se recibieron datos.';
    }

    echo json_encode($response);

?>