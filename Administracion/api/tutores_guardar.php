<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = ['status' => false, 'msg' => ''];

    if (empty($_POST)) {
        $response['msg'] = 'No se recibieron datos.';
        echo json_encode($response);
        exit;
    }

    // Recoger y limpiar campos
    $cedula = limpiar_cadena($_POST['cedula'] ?? '');
    $nombres = limpiar_cadena($_POST['nombres'] ?? '');
    $apellidos = limpiar_cadena($_POST['apellidos'] ?? '');
    $parentesco = limpiar_cadena($_POST['parentesco'] ?? '');
    $telefono = limpiar_cadena($_POST['telefono'] ?? '');
    $direccion = limpiar_cadena($_POST['direccion'] ?? '');
    $estado = limpiar_cadena($_POST['estado'] ?? 'Activo');

    // Arrays de alumnos (pueden venir vacíos o con valores vacíos)
    $alumnos = $_POST['alumnos']   ?? [];
    $principal = $_POST['principal'] ?? [];

    // Validaciones de campos obligatorios 
    if (empty($cedula) || empty($nombres) || empty($apellidos) ||
        empty($parentesco) || empty($telefono)) {
        $response['msg'] = 'Faltan datos obligatorios (cédula, nombres, apellidos, parentesco, teléfono).';
        echo json_encode($response);
        exit;
    }

    // Validar formato de cédula
    if (!preg_match('/^[A-Za-z0-9\-]{3,20}$/', $cedula)) {
        $response['msg'] = 'La cédula debe tener entre 3 y 20 caracteres alfanuméricos.';
        echo json_encode($response);
        exit;
    }

    // Validar que nombres y apellidos sean solo letras
    if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑüÜ\s\'\-]+$/u', $nombres)) {
        $response['msg'] = 'El campo Nombres solo debe contener letras y espacios.';
        echo json_encode($response);
        exit;
    }
    if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑüÜ\s\'\-]+$/u', $apellidos)) {
        $response['msg'] = 'El campo Apellidos solo debe contener letras y espacios.';
        echo json_encode($response);
        exit;
    }

    // Validar longitud mínima
    if (mb_strlen($nombres) < 2) {
        $response['msg'] = 'El nombre debe tener al menos 2 caracteres.';
        echo json_encode($response);
        exit;
    }
    if (mb_strlen($apellidos) < 2) {
        $response['msg'] = 'El apellido debe tener al menos 2 caracteres.';
        echo json_encode($response);
        exit;
    }

    // Validar teléfono
    if (!preg_match('/^[0-9\s\+\-\(\)]{6,20}$/', $telefono)) {
        $response['msg'] = 'El teléfono no es válido. Use solo números, espacios o guiones.';
        echo json_encode($response);
        exit;
    }

    // Validar parentesco
    $parentescos_validos = ['Padre','Madre','Abuelo','Abuela','Tío','Tía','Hermano','Hermana','Tutor legal','Otro'];
    if (!in_array($parentesco, $parentescos_validos)) {
        $response['msg'] = 'El parentesco seleccionado no es válido.';
        echo json_encode($response);
        exit;
    }

    // Validar estado
    if (!in_array($estado, ['Activo', 'Inactivo'])) {
        $response['msg'] = 'Estado no válido.';
        echo json_encode($response);
        exit;
    }

    // Verificar unicidad de cédula
    $sql_check = "SELECT idTutor FROM tutor WHERE cedula = '$cedula'";
    if (buscar_datos($sql_check)) {
        $response['msg'] = 'Ya existe un tutor registrado con esa cédula.';
        echo json_encode($response);
        exit;
    }

    // Validar alumnos enviados
    $asignaciones = []; 
    $conteo_principal = 0;

    foreach ($alumnos as $idx => $idAlumno) {
        $idAlumno = (int)$idAlumno;
        if ($idAlumno <= 0) continue;

        if (isset($asignaciones[$idAlumno])) {
            $response['msg'] = 'Hay alumnos duplicados en la lista de asignación.';
            echo json_encode($response);
            exit;
        }

        $esPrincipal = (isset($principal[$idx]) && $principal[$idx] === 'Sí') ? 'Sí' : 'No';
        if ($esPrincipal === 'Sí') $conteo_principal++;

        $asignaciones[$idAlumno] = $esPrincipal;
    }

    if ($conteo_principal > 1) {
        $response['msg'] = 'Solo puede haber un tutor marcado como Principal por asignación.';
        echo json_encode($response);
        exit;
    }

    // Verificar que los alumnos existan en la BD
    foreach (array_keys($asignaciones) as $idAlumno) {
        $chk = buscar_datos("SELECT idAlumno FROM alumno WHERE idAlumno = $idAlumno AND estado = 'Activo'");
        if (!$chk) {
            $response['msg'] = "El alumno con ID $idAlumno no existe o no está activo.";
            echo json_encode($response);
            exit;
        }
    }

    // INSERT tutor
    $sql_insert = "INSERT INTO tutor (cedula, nombres, apellidos, parentesco, telefono, direccion, estado)
                   VALUES ('$cedula', '$nombres', '$apellidos', '$parentesco',
                           '$telefono', '$direccion', '$estado')";

    $resultado = insertar_datos($sql_insert);

    if (!$resultado) {
        $response['msg'] = 'Error al guardar el tutor en la base de datos.';
        echo json_encode($response);
        exit;
    }

    // Obtener el ID recién insertado
    $fila = buscar_datos("SELECT idTutor FROM tutor WHERE cedula = '$cedula' ORDER BY idTutor DESC LIMIT 1");
    $nuevo_id = $fila ? (int)$fila[0]['idTutor'] : 0;

    // INSERT en alumno_tutor
    $errores_asignacion = [];
    if ($nuevo_id > 0 && !empty($asignaciones)) {
        foreach ($asignaciones as $idAlumno => $esPrincipal) {

            // Verificar si este alumno ya tiene un tutor principal asignado
            if ($esPrincipal === 'Sí') {
                $chk_principal = buscar_datos(
                    "SELECT idAlumnoTutor FROM alumno_tutor
                     WHERE idAlumno = $idAlumno AND es_principal = 'Sí'"
                );
                if ($chk_principal) {
                    $errores_asignacion[] = "El alumno ID $idAlumno ya tiene un tutor principal asignado; se guardó como Secundario.";
                    $esPrincipal = 'No';
                }
            }

            $sql_rel = "INSERT INTO alumno_tutor (idAlumno, idTutor, es_principal)
                        VALUES ($idAlumno, $nuevo_id, '$esPrincipal')";
            insertar_datos($sql_rel);
        }
    }

    $response['status'] = true;
    $response['msg'] = 'Tutor registrado correctamente.';
    if (!empty($errores_asignacion)) {
        $response['msg'] .= ' Advertencia: ' . implode(' ', $errores_asignacion);
    }

    echo json_encode($response);
?>