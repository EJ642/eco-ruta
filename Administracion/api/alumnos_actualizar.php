<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = ['status' => false, 'msg' => ''];

    if (empty($_POST)) {
        $response['msg'] = 'No se recibieron datos.';
        echo json_encode($response);
        exit;
    }

    // Recoger y limpiar campos
    $id = (int)limpiar_cadena($_POST['id_alumno'] ?? '');
    $nombres = limpiar_cadena($_POST['nombres'] ?? '');
    $apellidos = limpiar_cadena($_POST['apellidos'] ?? '');
    $cedula = limpiar_cadena($_POST['cedula'] ?? '');
    $fecha_nac = limpiar_cadena($_POST['fecha_nac'] ?? '');
    $sexo = limpiar_cadena($_POST['sexo'] ?? '');
    $direccion = limpiar_cadena($_POST['direccion'] ?? '');
    $estado = limpiar_cadena($_POST['estado'] ?? '');

    // Validar ID 
    if (!$id) {
        $response['msg'] = 'Identificador de alumno no válido.';
        echo json_encode($response);
        exit;
    }

    // Validar campos obligatorios 
    if (empty($nombres) || empty($apellidos) || empty($fecha_nac) ||
        empty($sexo) || empty($estado)) {
        $response['msg'] = 'Faltan datos obligatorios.';
        echo json_encode($response);
        exit;
    }

    // Validar nombres y apellidos
    if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑüÜ\s\'\-]+$/u', $nombres) || mb_strlen($nombres) < 2) {
        $response['msg'] = 'El campo Nombres solo debe contener letras y espacios (mínimo 2 caracteres).';
        echo json_encode($response);
        exit;
    }
    if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑüÜ\s\'\-]+$/u', $apellidos) || mb_strlen($apellidos) < 2) {
        $response['msg'] = 'El campo Apellidos solo debe contener letras y espacios (mínimo 2 caracteres).';
        echo json_encode($response);
        exit;
    }

    // Validar cédula (opcional)
    if (!empty($cedula) && !preg_match('/^[0-9A-Za-z\-]{3,20}$/', $cedula)) {
        $response['msg'] = 'La cédula debe tener entre 3 y 20 caracteres alfanuméricos.';
        echo json_encode($response);
        exit;
    }

    // Validar fecha de nacimiento
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nac)) {
        $response['msg'] = 'El formato de fecha de nacimiento no es válido.';
        echo json_encode($response);
        exit;
    }
    $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_nac);
    if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha_nac) {
        $response['msg'] = 'La fecha de nacimiento no es una fecha real.';
        echo json_encode($response);
        exit;
    }
    $hoy = new DateTime();
    if ($fecha_obj >= $hoy) {
        $response['msg'] = 'La fecha de nacimiento no puede ser igual o posterior a hoy.';
        echo json_encode($response);
        exit;
    }
    $edad = $hoy->diff($fecha_obj)->y;
    if ($edad < 3 || $edad > 25) {
        $response['msg'] = "La edad calculada ($edad años) está fuera del rango permitido (3 a 25 años).";
        echo json_encode($response);
        exit;
    }

    // Validar sexo
    if (!in_array($sexo, ['M', 'F'])) {
        $response['msg'] = 'El valor de Sexo no es válido.';
        echo json_encode($response);
        exit;
    }

    // Validar estado
    $estados_validos = ['Activo', 'Inactivo', 'Egresado', 'Retirado'];
    if (!in_array($estado, $estados_validos)) {
        $response['msg'] = 'El estado indicado no es válido.';
        echo json_encode($response);
        exit;
    }

    // Verificar que el alumno exista
    $sql_existe = "SELECT idAlumno, estado FROM alumno WHERE idAlumno = $id";
    $alumno_actual = buscar_datos($sql_existe);
    if (!$alumno_actual) {
        $response['msg'] = 'El alumno no fue encontrado.';
        echo json_encode($response);
        exit;
    }
    $estado_actual = $alumno_actual[0]['estado'];

    // Validar unicidad de cédula (excluyendo el propio)
    if (!empty($cedula)) {
        $sql_check_ci = "SELECT idAlumno FROM alumno
                         WHERE cedula = '$cedula' AND idAlumno != $id";
        if (buscar_datos($sql_check_ci)) {
            $response['msg'] = 'La cédula ya pertenece a otro alumno registrado.';
            echo json_encode($response);
            exit;
        }
    }

    // Validar duplicado de nombre+apellido+fecha (excluyendo el propio)
    $sql_dup = "SELECT idAlumno FROM alumno
                WHERE nombres = '$nombres' AND apellidos = '$apellidos'
                AND fecha_nac = '$fecha_nac' AND idAlumno != $id";
    if (buscar_datos($sql_dup)) {
        $response['msg'] = 'Ya existe otro alumno con los mismos nombres, apellidos y fecha de nacimiento.';
        echo json_encode($response);
        exit;
    }

    // No se puede inactivar/retirar/egresar un alumno con matrícula vigente
    if (in_array($estado, ['Inactivo', 'Retirado', 'Egresado']) && $estado_actual === 'Activo') {
        $sql_mat_vigente = "SELECT idMatricula FROM matricula
                            WHERE idAlumno = $id AND estado = 'Vigente'";
        if (buscar_datos($sql_mat_vigente)) {
            $response['msg'] = "No se puede cambiar el estado a '$estado' porque el alumno tiene una matrícula vigente. "
                             . "Primero actualice el estado de la matrícula correspondiente.";
            echo json_encode($response);
            exit;
        }
    }

    //  UPDATE
    $cedula_sql = empty($cedula) ? 'NULL' : "'$cedula'";

    $sql_update = "UPDATE alumno
                   SET cedula = $cedula_sql,
                       nombres = '$nombres',
                       apellidos = '$apellidos',
                       fecha_nac = '$fecha_nac',
                       sexo = '$sexo',
                       direccion = '$direccion',
                       estado = '$estado',
                       modificado = NOW()
                   WHERE idAlumno = $id";

    $resultado = actualizar_datos($sql_update);

    if ($resultado) {
        $response['status'] = true;
        $response['msg'] = 'Alumno actualizado correctamente.';
    } else {
        $response['msg'] = 'Error al actualizar en la base de datos.';
    }

    echo json_encode($response);
?>