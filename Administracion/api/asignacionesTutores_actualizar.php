<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {
        $idAlumnoTutor = limpiar_cadena($_POST['idAlumnoTutor'] ?? '');
        $idTutor = limpiar_cadena($_POST['idTutor'] ?? '');
        $idAlumno = limpiar_cadena($_POST['idAlumno'] ?? '');
        $Esprincipal = limpiar_cadena($_POST['principal'] ?? '');

        if (!$idTutor || !$idAlumno) {
            $response['msg'] = 'Tutor y alumno son obligatorios.';
        } else {
            if (!ctype_digit($idTutor) || !ctype_digit($idAlumno)) {
                $response['msg'] = 'El alumno o tutor ingresado no es válido.';
            }

            if (empty($response['msg'])) {
                $sql_check = "SELECT idAlumnoTutor FROM alumno_tutor
                            WHERE idTutor = '$idTutor'
                            AND idAlumno = '$idAlumno'
                            HAVING idAlumnoTutor != '$idAlumnoTutor'";

                if (buscar_datos($sql_check)) {
                    $response['msg'] = 'Esta asignación ya existe.';
                } else {
                    $sql = "UPDATE alumno_tutor SET idAlumno = '$idAlumno', idTutor = '$idTutor', es_principal = '$Esprincipal'
                            WHERE idAlumnoTutor = '$idAlumnoTutor'";

                    $updateId = actualizar_datos($sql);

                    if ($updateId) {
                        $response['status'] = true;
                        $response['msg'] = 'Asignación actualizada exitosamente.';
                    } else {
                        $response['msg'] = 'No se pudo actualizar la asignación.';
                    }
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
