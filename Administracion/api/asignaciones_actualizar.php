<?php
    require_once __DIR__ . '/../../servicios/conexion.php';

    $response = array('status' => false, 'msg' => '');

    if (!empty($_POST)) {
        $idDocente = limpiar_cadena($_POST['idDocente'] ?? '');
        $idAulaMateria = limpiar_cadena($_POST['idAulaMateria'] ?? '');
        $idAsignacion = limpiar_cadena($_POST['idAsignacion'] ?? '');

        if (!$idDocente || !$idAulaMateria || !$idAsignacion) {
            $response['msg'] = 'Todos los campos son obligatorios.';
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
                            AND dam.activo = 1
                            AND dam.idAsignacion != '$idAsignacion'";
            
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
                $sql = "UPDATE docente_aula_materia SET idDocente = '$idDocente', idAulaMateria = '$idAulaMateria', activo = 1
                        WHERE idAsignacion = '$idAsignacion'";

                $actualizarId = actualizar_datos($sql);

                if ($actualizarId) {
                    $response['status'] = true;
                    $response['msg'] = 'Asignación actualizada exitosamente.';
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
