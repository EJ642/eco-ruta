<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = ['status' => false, 'msg' => ''];

    if (empty($_POST)) {
        $response['msg'] = 'No se recibieron datos.';
        echo json_encode($response);
        exit;
    }

    // Recoger y limpiar campos
    $id = (int)limpiar_cadena($_POST['id_tutor'] ?? '');
    $cedula = limpiar_cadena($_POST['cedula'] ?? '');
    $nombres = limpiar_cadena($_POST['nombres'] ?? '');
    $apellidos = limpiar_cadena($_POST['apellidos'] ?? '');
    $parentesco = limpiar_cadena($_POST['parentesco'] ?? '');
    $telefono = limpiar_cadena($_POST['telefono'] ?? '');
    $direccion = limpiar_cadena($_POST['direccion'] ?? '');
    $estado = limpiar_cadena($_POST['estado'] ?? '');

    // Validar ID
    if (!$id) {
        $response['msg'] = 'Identificador de tutor no válido.';
        echo json_encode($response);
        exit;
    }

    // Validar campos obligatorios
    if (empty($cedula) || empty($nombres) || empty($apellidos) ||
        empty($parentesco) || empty($telefono) || empty($estado)) {
        $response['msg'] = 'Faltan datos obligatorios.';
        echo json_encode($response);
        exit;
    }

    // Validar formato de cédula
    if (!preg_match('/^[A-Za-z0-9\-]{3,20}$/', $cedula)) {
        $response['msg'] = 'La cédula debe tener entre 3 y 20 caracteres alfanuméricos.';
        echo json_encode($response);
        exit;
    }

    // Validar nombres y apellidos
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

    // Verificar que el tutor exista
    $sql_existe = "SELECT idTutor FROM tutor WHERE idTutor = $id";
    if (!buscar_datos($sql_existe)) {
        $response['msg'] = 'El tutor no fue encontrado.';
        echo json_encode($response);
        exit;
    }

    // Verificar unicidad de cédula
    $sql_check_ci = "SELECT idTutor FROM tutor
                     WHERE cedula = '$cedula' AND idTutor != $id";
    if (buscar_datos($sql_check_ci)) {
        $response['msg'] = 'La cédula ya pertenece a otro tutor registrado.';
        echo json_encode($response);
        exit;
    }

    // Validación de negocio: no inactivar tutor con alumnos activos
    if ($estado === 'Inactivo') {
        $sql_alumnos = "SELECT at2.idAlumnoTutor
                        FROM alumno_tutor at2
                        INNER JOIN alumno a ON a.idAlumno = at2.idAlumno
                        WHERE at2.idTutor = $id AND a.estado = 'Activo'";
        $alumnos_activos = buscar_datos($sql_alumnos);
        if ($alumnos_activos) {
            $response['msg'] = 'No se puede inactivar el tutor porque tiene alumnos activos asignados. Desasigne los alumnos primero.';
            echo json_encode($response);
            exit;
        }
    }

    // UPDATE
    $sql_update = "UPDATE tutor
                   SET cedula = '$cedula',
                       nombres = '$nombres',
                       apellidos = '$apellidos',
                       parentesco = '$parentesco',
                       telefono = '$telefono',
                       direccion = '$direccion',
                       estado = '$estado',
                       modificado = NOW()
                   WHERE idTutor = $id";

    $resultado = actualizar_datos($sql_update);

    if ($resultado) {
        $response['status'] = true;
        $response['msg'] = 'Tutor actualizado correctamente.';
    } else {
        $response['msg'] = 'Error al actualizar en la base de datos.';
    }

    echo json_encode($response);
?>