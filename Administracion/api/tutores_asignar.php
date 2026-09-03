<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = ['status' => false, 'msg' => ''];

    if (empty($_POST)) {
        $response['msg'] = 'No se recibieron datos.';
        echo json_encode($response);
        exit;
    }

    $idTutor = (int)limpiar_cadena($_POST['id_tutor'] ?? '');
    $idAlumno = (int)limpiar_cadena($_POST['idAlumno'] ?? '');
    $esPrincipal = limpiar_cadena($_POST['es_principal'] ?? 'No');

    // Validaciones básicas
    if (!$idTutor || !$idAlumno) {
        $response['msg'] = 'Datos incompletos. Indique el tutor y el alumno.';
        echo json_encode($response);
        exit;
    }

    if (!in_array($esPrincipal, ['Sí', 'No'])) {
        $response['msg'] = 'Valor de "tutor principal" no válido.';
        echo json_encode($response);
        exit;
    }

    // Verificar que el tutor exista y esté activo
    $sql_tutor = "SELECT idTutor FROM tutor WHERE idTutor = $idTutor AND estado = 'Activo'";
    if (!buscar_datos($sql_tutor)) {
        $response['msg'] = 'El tutor no existe o no está activo.';
        echo json_encode($response);
        exit;
    }

    // Verificar que el alumno exista y esté activo
    $sql_alumno = "SELECT idAlumno FROM alumno WHERE idAlumno = $idAlumno AND estado = 'Activo'";
    if (!buscar_datos($sql_alumno)) {
        $response['msg'] = 'El alumno no existe o no está activo.';
        echo json_encode($response);
        exit;
    }

    // Verificar que la relación no exista ya
    $sql_existe = "SELECT idAlumnoTutor FROM alumno_tutor
                   WHERE idAlumno = $idAlumno AND idTutor = $idTutor";
    if (buscar_datos($sql_existe)) {
        $response['msg'] = 'Este alumno ya está asignado a este tutor.';
        echo json_encode($response);
        exit;
    }

    // Si se marca como principal, verificar que el alumno no tenga ya uno
    if ($esPrincipal === 'Sí') {
        $sql_principal = "SELECT idAlumnoTutor FROM alumno_tutor
                          WHERE idAlumno = $idAlumno AND es_principal = 'Sí'";
        if (buscar_datos($sql_principal)) {
            $response['msg'] = 'Este alumno ya tiene un tutor principal asignado. Cambie el tipo a Secundario o modifique el tutor principal existente.';
            echo json_encode($response);
            exit;
        }
    }

    // INSERT
    $sql_insert = "INSERT INTO alumno_tutor (idAlumno, idTutor, es_principal)
                   VALUES ($idAlumno, $idTutor, '$esPrincipal')";
    $resultado = insertar_datos($sql_insert);

    if ($resultado) {
        $response['status'] = true;
        $response['msg'] = 'Alumno asignado correctamente al tutor.';
    } else {
        $response['msg'] = 'Error al guardar la asignación.';
    }

    echo json_encode($response);
?>