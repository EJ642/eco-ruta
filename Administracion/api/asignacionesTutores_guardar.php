<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {
        $idTutor = limpiar_cadena($_POST['idTutor'] ?? '');
        $idAlumno = limpiar_cadena($_POST['idAlumno'] ?? '');
        $Esprincipal = limpiar_cadena($_POST['principal'] ?? '');

        if (!$idTutor || !$idAlumno) {
            $response['msg'] = 'Tutor y alumno son obligatorios.';
        } else {
            // Validar que el tutor existe y está activo
            $sql_tutor = "SELECT idTutor FROM tutor WHERE idTutor = '$idTutor' AND estado = 'Activo'";
            if (!buscar_datos($sql_tutor)) {
                $response['msg'] = 'El nombre del tutor no es válido o no está activo.';
            } 
            // Validar que el alumno existe y está activo
            elseif (!buscar_datos("SELECT idAlumno FROM alumno WHERE idAlumno = '$idAlumno' AND estado = 'Activo'")) {
                $response['msg'] = 'El nombre del alumno no es válido o no está activo.';
            } else {
                $sql_check = "SELECT idAlumnoTutor FROM alumno_tutor
                                WHERE idTutor = '$idTutor'
                                AND idAlumno = '$idAlumno'";

                if (buscar_datos($sql_check)) {
                    $response['msg'] = 'Esta asignación ya existe.';
                } else {
                    $sql = "INSERT INTO alumno_tutor (idAlumno, idTutor, es_principal)
                            VALUES ('$idAlumno', '$idTutor', '$Esprincipal')";

                    $insertId = insertar_datos($sql);

                    if ($insertId) {
                        $response['status'] = true;
                        $response['msg'] = 'Asignación guardada exitosamente.';
                    }
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
