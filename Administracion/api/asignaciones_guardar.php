<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {
        $idDocente = limpiar_cadena($_POST['idDocente'] ?? '');
        $idAulaMateria = limpiar_cadena($_POST['idAulaMateria'] ?? '');

        if (!$idDocente || !$idAulaMateria) {
            $response['msg'] = 'Docente y materia son obligatorios.';
        } else {
            $sql_check = "SELECT idAsignacion FROM docente_aula_materia
                        WHERE idDocente = '$idDocente'
                            AND idAulaMateria = '$idAulaMateria'
                            AND activo = 1";

            $sqlExiste = "SELECT d.nombres, d.apellidos
                            FROM docente_aula_materia dam
                            INNER JOIN docente d
                                    ON dam.idDocente = d.idDocente
                            WHERE dam.idAulaMateria = '$idAulaMateria'
                            AND dam.activo = 1";
            
            $existe = buscar_datos($sqlExiste);

            if($existe){

                $docente = $existe[0]['nombres']." ".$existe[0]['apellidos'];

                $response['status']=false;

                $response['msg']="Esta materia ya está asignada al docente ".$docente;

                echo json_encode($response);

                exit;

            }


            if (buscar_datos($sql_check)) {
                $response['msg'] = 'Esta asignación ya existe.';
            } else {
                $sql = "INSERT INTO docente_aula_materia (idDocente, idAulaMateria, activo)
                        VALUES ('$idDocente', '$idAulaMateria', 1)";

                $insertId = insertar_datos($sql);

                if ($insertId) {
                    $response['status'] = true;
                    $response['msg'] = 'Asignación guardada exitosamente.';
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
