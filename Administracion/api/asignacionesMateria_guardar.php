<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {
        $idAula = limpiar_cadena($_POST['idAula'] ?? '');
        $idMateria = limpiar_cadena($_POST['idMateria'] ?? '');

        if (!$idAula || !$idMateria) {
            $response['msg'] = 'Aula y materia son obligatorios.';
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
                                AND activo = 'Sí'";

                if (buscar_datos($sql_check)) {
                    $response['msg'] = 'Esta asignación ya existe.';
                } else {
                    $sql = "INSERT INTO aula_materia (idAula, idMateria, activo)
                            VALUES ('$idAula', '$idMateria', 'Sí')";

                    $insertId = insertar_datos($sql);

                    if ($insertId) {
                        $response['status'] = true;
                        $response['msg'] = 'Asignación guardada exitosamente.';
                    } else {
                        $response['msg'] = 'No se pudo guardar la asignación.';
                    }
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
