<?php
/**
 * API: Docente/api/guardar_calificaciones_lote.php
 * 
 * PROPÓSITO:
 * Guardar múltiples calificaciones de alumnos para una evaluación específica.
 * Realiza INSERT si la nota no existe, UPDATE si ya existe (UNIQUE: idEvaluacion+idMatricula).
 * 
 * DÓNDE SE USA:
 * - AJAX desde: calificaciones.php (cuando click "Guardar Calificaciones")
 * 
 * MÉTODO: POST
 * 
 * DATOS DE ENTRADA (JSON):
 * {
 *   "evaluaciones": [
 *     {
 *       "idMatricula": number,
 *       "idEvaluacion": number,
 *       "puntos_obtenidos": number (0 a puntos_total),
 *       "observacion": string (opcional)
 *     },
 *     ...
 *   ]
 * }
 * 
 * VALIDACIONES REALIZADAS:
 * - Sesión docente requerida
 * - puntos_obtenidos >= 0
 * - puntos_obtenidos <= puntos_total (de evaluacion table)
 * - Matrícula debe estar en estado 'Vigente' (NO 'Activo')
 * - Docente debe tener permiso en docente_aula_materia para la evaluación
 * 
 * LÓGICA DE GUARDADO:
 * - SELECT nota WHERE idEvaluacion + idMatricula (incluye puntos_obtenidos actual)
 * - Si existe: UPDATE puntos_obtenidos, observacion, modificado_por, modificado
 *   + registra en auditoria_nota (accion=UPDATE, valor_antes, valor_despues)
 * - Si no existe: INSERT puntos_obtenidos, observacion, registrado_por, fecha_registro
 *   + registra en auditoria_nota (accion=INSERT, valor_antes=NULL, valor_despues)
 * - NUNCA TOCA: valor, nota, idPeriodo, idTipoNota, idAulaMateria (columnas removidas)
 * 
 * DATOS RETORNADOS:
 * JSON: {
 *   "success": true,
 *   "total": número de calificaciones guardadas,
 *   "errores": [ "detalle error 1", "detalle error 2" ],
 *   "message": "X calificación(es) guardada(s)"
 * }
 * 
 * O si falla:
 * {
 *   "success": false,
 *   "error": "descripción del error",
 *   "detalles": [ "error por alumno 1", "error por alumno 2" ]
 * }
 */
    session_name('DOCENTE_SESSION');
    session_start();
    require_once __DIR__ . "/../../servicios/conexion.php";
    require_once __DIR__ . "/validar_periodo.php";
    require_once __DIR__ . "/registrar_auditoria.php";

    $docente_id = $_SESSION['docente_id'] ?? null;
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    if (!$docente_id) {
        http_response_code(200);
        echo json_encode(['success' => false, 'error' => 'No autenticado', 'detalles' => []]);
        exit;
    }
    if (!$usuario_id) {
        $usuario_id = $docente_id;
    }

    // Recibir datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || !isset($data['evaluaciones']) || !is_array($data['evaluaciones'])) {
        http_response_code(200);
        echo json_encode(['success' => false, 'error' => 'Datos inválidos', 'detalles' => []]);
        exit;
    }

    $evaluaciones = $data['evaluaciones'];
    $total_guardadas = 0;
    $sin_cambios = 0;
    $errores = [];

    foreach ($evaluaciones as $item) {
        $idEvaluacion = $item['idEvaluacion'] ?? null;
        $idMatricula = $item['idMatricula'] ?? null;
        $puntos_obtenidos = floatval($item['puntos_obtenidos'] ?? 0);
        $observacion = trim($item['observacion'] ?? '');

        // Validaciones
        if (!$idEvaluacion || !$idMatricula) {
            $errores[] = "Falta idEvaluacion o idMatricula";
            continue;
        }

        // Validar puntos_obtenidos >= 0
        if ($puntos_obtenidos < 0) {
            $errores[] = "Puntos no pueden ser negativos para matrícula $idMatricula";
            continue;
        }

        // Verificar que evaluación existe, docente tiene permiso y obtener puntos_total
        $sql_eval = "SELECT e.idEvaluacion, e.idAulaMateria, e.puntos_total, e.idPeriodo, dam.idDocente 
                     FROM evaluacion e 
                     JOIN docente_aula_materia dam ON e.idAulaMateria = dam.idAulaMateria 
                     WHERE e.idEvaluacion = $idEvaluacion 
                     AND dam.idDocente = $docente_id
                     AND dam.activo = 1";
        $resultado_eval = buscar_datos($sql_eval);
        
        if (!$resultado_eval || empty($resultado_eval)) {
            $errores[] = "Evaluación $idEvaluacion no encontrada o sin permiso";
            continue;
        }

        // Validar que el período permita cargar notas (abierto o con excepción)
        $chkPeriodo = puede_cargar_notas($resultado_eval[0]['idAulaMateria'], $resultado_eval[0]['idPeriodo']);
        if (!$chkPeriodo['permitido']) {
            $errores[] = "Matrícula $idMatricula: " . $chkPeriodo['motivo'];
            continue;
        }

        // Validar puntos_obtenidos no excedan puntos_total
        $puntos_total = floatval($resultado_eval[0]['puntos_total'] ?? 0);
        if ($puntos_obtenidos > $puntos_total) {
            $errores[] = "Matrícula $idMatricula: puntos exceden máximo ($puntos_obtenidos > $puntos_total)";
            continue;
        }

        // Verificar que matrícula existe y está activa en la aula
        $idAulaMateria = $resultado_eval[0]['idAulaMateria'];
        $sql_matricula = "SELECT m.idMatricula, m.idAula
                          FROM matricula m
                          JOIN aula_materia am ON am.idAula = m.idAula
                          WHERE m.idMatricula = $idMatricula 
                          AND am.idAulaMateria = $idAulaMateria
                          AND m.estado = 'Vigente'";
        $resultado_matricula = buscar_datos($sql_matricula);
        
        if (!$resultado_matricula || empty($resultado_matricula)) {
            $errores[] = "Matrícula $idMatricula no válida o inactiva";
            continue;
        }

        // Escapar observación usando función de conexion.php
        $observacion = limpiar_cadena($observacion);

        // Verificar si ya existe nota (traemos también el puntaje actual para auditoría)
        $sql_existe = "SELECT idNota, puntos_obtenidos FROM nota 
                       WHERE idEvaluacion = $idEvaluacion 
                       AND idMatricula = $idMatricula";
        $nota_existe = buscar_datos($sql_existe);

        if ($nota_existe && !empty($nota_existe)) {
            // ACTUALIZAR: solo puntos_obtenidos y observacion
            $idNota = $nota_existe[0]['idNota'];
            $puntos_antes = (float)$nota_existe[0]['puntos_obtenidos'];

            // Si el valor no cambió, no se toca la nota ni se audita nada
            if ($puntos_antes === $puntos_obtenidos) {
                $sin_cambios++;
                continue;
            }

            $sql_update = "UPDATE nota 
                        SET puntos_obtenidos = $puntos_obtenidos, 
                            observacion = '$observacion',
                            modificado_por = $usuario_id,
                            modificado = NOW()
                        WHERE idNota = $idNota";
            
            if (actualizar_datos($sql_update)) {
                $total_guardadas++;
                registrar_auditoria_nota($idNota, 'UPDATE', $puntos_antes, $puntos_obtenidos, $usuario_id, 'guardar_calificaciones_lote');
            } else {
                $errores[] = "Error al actualizar matrícula $idMatricula";
            }
        } else {
            // INSERTAR: solo puntos_obtenidos, observacion, registrado_por
            $sql_insert = "INSERT INTO nota 
                           (idEvaluacion, idMatricula, puntos_obtenidos, observacion, registrado_por, fecha_registro, modificado) 
                           VALUES 
                           ($idEvaluacion, $idMatricula, $puntos_obtenidos, '$observacion', $usuario_id, NOW(), NOW())";
            
            $idNotaNueva = insertar_datos($sql_insert);
            if ($idNotaNueva) {
                $total_guardadas++;
                registrar_auditoria_nota($idNotaNueva, 'INSERT', null, $puntos_obtenidos, $usuario_id, 'guardar_calificaciones_lote');
            } else {
                $errores[] = "Error al insertar matrícula $idMatricula";
            }
        }
    }

    // Retornar respuesta
    // NOTA: Devolvemos SIEMPRE HTTP 200 para que jQuery ejecute success()
    // El indicador real de éxito es el campo 'success' en JSON
if ($total_guardadas > 0 || $sin_cambios > 0) {
    http_response_code(200);
    $mensaje = $total_guardadas > 0
        ? "$total_guardadas calificación(es) guardada(s)" . ($sin_cambios > 0 ? " ($sin_cambios sin cambios)" : "")
        : "Sin cambios: todas las calificaciones ya tenían estos valores";
    echo json_encode([
        'success' => true,
        'total' => $total_guardadas,
        'sin_cambios' => $sin_cambios,
        'errores' => $errores,
        'message' => $mensaje
    ]);
} else {
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'error' => 'No se guardó ninguna calificación',
            'detalles' => $errores
        ]);
    }
?>
