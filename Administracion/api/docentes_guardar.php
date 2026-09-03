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
    $telefono  = limpiar_cadena($_POST['telefono'] ?? '');
    $direccion = limpiar_cadena($_POST['direccion'] ?? '');
    $fecha_nac = limpiar_cadena($_POST['fecha_nac'] ?? '');
    $titulo = limpiar_cadena($_POST['titulo'] ?? '');
    $estado = limpiar_cadena($_POST['estado'] ?? 'Activo');

    // Validar campos obligatorios
    if (empty($nombres) || empty($apellidos) || empty($cedula)) {
        $response['msg'] = 'Faltan datos obligatorios: nombres, apellidos y cédula.';
        echo json_encode($response);
        exit;
    }

    // Validar nombres y apellidos
    if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑüÜ\s\'\-]+$/u', $nombres) || mb_strlen($nombres) < 4) {
        $response['msg'] = 'El campo Nombres solo debe contener letras y espacios (mínimo 4 caracteres).';
        echo json_encode($response);
        exit;
    }
    if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑüÜ\s\'\-]+$/u', $apellidos) || mb_strlen($apellidos) < 4) {
        $response['msg'] = 'El campo Apellidos solo debe contener letras y espacios (mínimo 4 caracteres).';
        echo json_encode($response);
        exit;
    }

    // Validar cédula (obligatoria para docentes)
    if (!preg_match('/^[0-9A-Za-z\-]{3,20}$/', $cedula)) {
        $response['msg'] = 'La cédula debe tener entre 3 y 20 caracteres alfanuméricos.';
        echo json_encode($response);
        exit;
    }

    // Validar teléfono (opcional)
    if (!empty($telefono) && !preg_match('/^[0-9\+\-\s\(\)]{6,20}$/', $telefono)) {
        $response['msg'] = 'El teléfono solo puede contener números, espacios, +, - y paréntesis (6-20 caracteres).';
        echo json_encode($response);
        exit;
    }

    // Validar fecha de nacimiento (opcional para docentes)
    if (!empty($fecha_nac)) {
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
        if ($edad < 20 || $edad > 80) {
            $response['msg'] = "La edad calculada ($edad años) está fuera del rango permitido para docentes (20 a 80 años).";
            echo json_encode($response);
            exit;
        }
    }

    // Validar estado
    if (!in_array($estado, ['Activo', 'Inactivo'])) {
        $response['msg'] = 'El estado indicado no es válido.';
        echo json_encode($response);
        exit;
    }

    // Validar unicidad de cédula
    $sql_check_ci = "SELECT idDocente FROM docente WHERE cedula = '$cedula'";
    if (buscar_datos($sql_check_ci)) {
        $response['msg'] = 'Ya existe un docente registrado con esa cédula de identidad.';
        echo json_encode($response);
        exit;
    }

    // Validar duplicado de nombre+apellido (previene doble registro accidental)
    $sql_dup = "SELECT idDocente FROM docente
                WHERE nombres = '$nombres' AND apellidos = '$apellidos'";
    if (buscar_datos($sql_dup)) {
        $response['msg'] = 'Ya existe un docente con los mismos nombres y apellidos. Verifique si ya está registrado.';
        echo json_encode($response);
        exit;
    }

    // Preparar valores SQL para campos opcionales
    $telefono_sql = empty($telefono) ? 'NULL' : "'$telefono'";
    $fecha_nac_sql = empty($fecha_nac) ? 'NULL' : "'$fecha_nac'";
    $titulo_sql = empty($titulo) ? 'NULL' : "'$titulo'";
    $direccion_sql = empty($direccion) ? 'NULL' : "'$direccion'";

    // INSERT
    $sql_insert = "INSERT INTO docente
                       (cedula, nombres, apellidos, telefono,
                        direccion, fecha_nac, titulo, estado)
                   VALUES
                       ('$cedula', '$nombres', '$apellidos', $telefono_sql,
                        $direccion_sql, $fecha_nac_sql, $titulo_sql, '$estado')";

    $resultado = insertar_datos($sql_insert);

    if ($resultado) {
        $response['status'] = true;
        $response['msg'] = 'Docente registrado correctamente.';
    } else {
        $response['msg'] = 'Error al guardar el docente en la base de datos.';
    }

    echo json_encode($response);
?>