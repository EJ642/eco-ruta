<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {
        $idAulaMateria = limpiar_cadena($_POST['idAulaMateria'] ?? '');
        $idAula = limpiar_cadena($_POST['idAula'] ?? '');
        $idMateria = limpiar_cadena($_POST['idMateria'] ?? '');
        $activo = limpiar_cadena($_POST['activo'] ?? '');

        if (!$idAula || !$idMateria || !$activo) {
            $response['msg'] = 'Aula, materia y estado son obligatorios.';
        } elseif (!in_array($activo, ['Sí', 'No'], true)) {
            $response['msg'] = 'El estado indicado no es válido.';
        } else {
            // Si el campo idMateria trae nombre en lugar de ID, buscar el id real.
            if (!ctype_digit($idMateria)) {
                $sql_materia = "SELECT idMateria FROM materia WHERE nombre = '$idMateria' AND activo = 'Sí' LIMIT 1";
                $materia_info = buscar_datos($sql_materia);

                if (!$materia_info) {
                    $response['msg'] = 'La materia ingresada no es válida.';
                } else {
                    $idMateria = $materia_info[0]['idMateria'];
                }
            }

            if (empty($response['msg'])) {
                $sql_check = "SELECT idAulaMateria FROM aula_materia
                              WHERE idAula = '$idAula'
                                AND idMateria = '$idMateria'
                                AND activo = 'Sí'
                                HAVING idAulaMateria != '$idAulaMateria'";

                if (buscar_datos($sql_check)) {
                    $response['msg'] = 'Esta asignación ya existe.';
                } else {
                    $sql = "UPDATE aula_materia SET idAula = '$idAula', idMateria = '$idMateria', activo = '$activo'"
                         . " WHERE idAulaMateria = '$idAulaMateria'";

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
