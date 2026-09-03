<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = ['status' => false, 'msg' => ''];

    if (empty($_POST)) {
        $response['msg'] = 'No se recibieron datos.';
        echo json_encode($response);
        exit;
    }

    // Recoger y limpiar campos
    $nombres = limpiar_cadena($_POST['nombres'] ?? '');
    $apellidos = limpiar_cadena($_POST['apellidos'] ?? '');
    $cedula = limpiar_cadena($_POST['cedula'] ?? '');
    $fecha_nac = limpiar_cadena($_POST['fecha_nac'] ?? '');
    $sexo = limpiar_cadena($_POST['sexo'] ?? '');
    $direccion = limpiar_cadena($_POST['direccion'] ?? '');
    $estado = limpiar_cadena($_POST['estado'] ?? 'Activo');

    // Validar campos obligatorios
    if (empty($nombres) || empty($apellidos) || empty($fecha_nac) || empty($sexo)) {
        $response['msg'] = 'Faltan datos obligatorios: nombres, apellidos, fecha de nacimiento y sexo.';
        echo json_encode($response);
        exit;
    }

    // Validar nombres y apellidos (solo letras, tildes, guiones)
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

    // Validar que la fecha no sea futura
    $hoy = new DateTime();
    if ($fecha_obj >= $hoy) {
        $response['msg'] = 'La fecha de nacimiento no puede ser igual o posterior a hoy.';
        echo json_encode($response);
        exit;
    }

    // Validar rango de edad razonable para alumnos (3 a 25 años)
    $edad = $hoy->diff($fecha_obj)->y;
    if ($edad < 3 || $edad > 25) {
        $response['msg'] = "La edad calculada ($edad años) está fuera del rango permitido para alumnos (3 a 25 años).";
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

    // Validar unicidad de cédula (solo si se proporcionó)
    if (!empty($cedula)) {
        $sql_check_ci = "SELECT idAlumno FROM alumno WHERE cedula = '$cedula'";
        if (buscar_datos($sql_check_ci)) {
            $response['msg'] = 'Ya existe un alumno registrado con esa cédula de identidad.';
            echo json_encode($response);
            exit;
        }
    }

    // Validar nombre duplicado (mismo nombre + apellidos + fecha de nacimiento)
    // Previene doble registro accidental del mismo alumno
    $sql_dup = "SELECT idAlumno FROM alumno
                WHERE nombres = '$nombres' AND apellidos = '$apellidos'
                AND fecha_nac = '$fecha_nac'";
    if (buscar_datos($sql_dup)) {
        $response['msg'] = 'Ya existe un alumno con los mismos nombres, apellidos y fecha de nacimiento. Verifique si ya está registrado.';
        echo json_encode($response);
        exit;
    }

    // INSERT
    $cedula_sql = empty($cedula) ? 'NULL' : "'$cedula'";

    $sql_insert = "INSERT INTO alumno
                       (cedula, nombres, apellidos, fecha_nac, sexo, direccion, estado)
                   VALUES
                       ($cedula_sql, '$nombres', '$apellidos', '$fecha_nac',
                        '$sexo', '$direccion', '$estado')";

    $resultado = insertar_datos($sql_insert);

    if ($resultado) {
        $response['status'] = true;
        $response['msg'] = "Alumno registrado correctamente.";
    } else {
        $response['msg'] = 'Error al guardar el alumno en la base de datos.';
    }

    echo json_encode($response);
?>
