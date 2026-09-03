<?php
/**
 * API: Docente/api/editar_calificaciones_lote.php
 * 
 * PROPÓSITO:
 * Editar múltiples calificaciones existentes de una evaluación.
 * Realiza UPDATE en notas existentes (o INSERT si el alumno aún no tenía nota).
 * 
 * MÉTODO: POST
 * 
 * DATOS DE ENTRADA (JSON):
 * {
 *   "idEvaluacion": number,
 *   "calificaciones": [
 *     {
 *       "idMatricula": number,
 *       "puntos_obtenidos": number,
 *       "observacion": string (opcional)
 *     },
 *     ...
 *   ]
 * }
 * 
 * RETORNA:
 * JSON: {
 *   "success": true,
 *   "total": número de calificaciones actualizadas,
 *   "message": "X calificación(es) actualizada(s)"
 * }
 * 
 * O si falla:
 * {
 *   "success": false,
 *   "error": "descripción del error",
 *   "detalles": [ ... ]
 * }
 *
 * AUDITORÍA:
 * Cada UPDATE/INSERT sobre la tabla nota queda registrado en auditoria_nota
 * con el valor anterior, el nuevo valor, el usuario y la fecha/IP.
 */

header('Content-Type: application/json');
session_name('DOCENTE_SESSION');
session_start();
require_once __DIR__ . '/../../servicios/conexion.php';
require_once __DIR__ . '/validar_periodo.php';
require_once __DIR__ . '/registrar_auditoria.php';

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

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['idEvaluacion']) || !isset($data['calificaciones']) || !is_array($data['calificaciones'])) {
    http_response_code(200);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos', 'detalles' => []]);
    exit;
}

$idEvaluacion = (int)$data['idEvaluacion'];
$calificaciones = $data['calificaciones'];
$total_actualizadas = 0;
$sin_cambios = 0;
$errores = [];

// Verificar acceso a la evaluación
$sql_check = "SELECT e.idEvaluacion, e.puntos_total, e.idAulaMateria, e.idPeriodo, am.idAula
              FROM evaluacion e
              JOIN aula_materia am ON e.idAulaMateria = am.idAulaMateria
              JOIN docente_aula_materia dam ON am.idAulaMateria = dam.idAulaMateria
              WHERE e.idEvaluacion = $idEvaluacion
                AND dam.idDocente = $docente_id
                AND dam.activo = 1
              LIMIT 1";

$eval_data = buscar_datos($sql_check);
if (!$eval_data) {
    http_response_code(200);
    echo json_encode(['success' => false, 'error' => 'No tiene acceso a esta evaluación', 'detalles' => []]);
    exit;
}

$puntos_total = $eval_data[0]['puntos_total'];
$idAulaEvaluacion = (int)$eval_data[0]['idAula'];

// Validar que el período permita editar notas (abierto o con excepción)
$chkPeriodo = puede_cargar_notas($eval_data[0]['idAulaMateria'], $eval_data[0]['idPeriodo']);
if (!$chkPeriodo['permitido']) {
    http_response_code(200);
    echo json_encode(['success' => false, 'error' => $chkPeriodo['motivo'], 'detalles' => []]);
    exit;
}

foreach ($calificaciones as $item) {
    $idMatricula = $item['idMatricula'] ?? null;
    $puntos_obtenidos = $item['puntos_obtenidos'] ?? null;
    $observacion = $item['observacion'] ?? '';

    if (!$idMatricula) {
        $errores[] = "Matrícula no especificada";
        continue;
    }

    $idMatricula = (int)$idMatricula;
    
    // Validar puntos
    if ($puntos_obtenidos === null || $puntos_obtenidos === '') {
        $errores[] = "Alumno $idMatricula: puntos no especificados";
        continue;
    }

    $puntos_obtenidos = (float)$puntos_obtenidos;

    if ($puntos_obtenidos < 0) {
        $errores[] = "Alumno $idMatricula: puntos no pueden ser negativos";
        continue;
    }

    if ($puntos_obtenidos > $puntos_total) {
        $errores[] = "Alumno $idMatricula: puntos exceden máximo ($puntos_total)";
        continue;
    }

    // Escapar observación
    $sql_matricula = "SELECT idMatricula FROM matricula
                      WHERE idMatricula = $idMatricula
                        AND idAula = $idAulaEvaluacion
                        AND estado = 'Vigente'
                      LIMIT 1";
    if (!buscar_datos($sql_matricula)) {
        $errores[] = "Alumno $idMatricula: matricula no corresponde al aula de la evaluacion";
        continue;
    }

    $observacion = limpiar_cadena($observacion);

    // Verificar si la nota ya existe (traemos también el puntaje actual para auditoría)
    $sql_verify = "SELECT idNota, puntos_obtenidos FROM nota WHERE idEvaluacion = $idEvaluacion AND idMatricula = $idMatricula LIMIT 1";
    $nota_existe = buscar_datos($sql_verify);

    if ($nota_existe && !empty($nota_existe)) {
        // ACTUALIZAR: si la nota ya existe
        $idNota = $nota_existe[0]['idNota'];
        $puntos_antes = (float)$nota_existe[0]['puntos_obtenidos'];

        // Si el valor no cambió, no se toca la nota ni se audita nada
        if ($puntos_antes === $puntos_obtenidos) {
            $sin_cambios++;
            //$total_actualizadas++;
            continue;
        }

        $sql_update = "UPDATE nota 
                    SET puntos_obtenidos = $puntos_obtenidos,
                        observacion = " . ($observacion ? "'$observacion'" : "NULL") . ",
                        modificado_por = $usuario_id,
                        modificado = NOW()
                    WHERE idEvaluacion = $idEvaluacion AND idMatricula = $idMatricula";

        if (actualizar_datos($sql_update)) {
            $total_actualizadas++;
            registrar_auditoria_nota($idNota, 'UPDATE', $puntos_antes, $puntos_obtenidos, $usuario_id, 'editar_calificaciones_lote');
        } else {
            $errores[] = "Error al actualizar alumno $idMatricula";
        }
    } else {
        // INSERTAR: si la nota no existe (alumno sin calificación previa)
        $sql_insert = "INSERT INTO nota 
                       (idEvaluacion, idMatricula, puntos_obtenidos, observacion, registrado_por, fecha_registro, modificado) 
                       VALUES 
                       ($idEvaluacion, $idMatricula, $puntos_obtenidos, " . ($observacion ? "'$observacion'" : "NULL") . ", $usuario_id, NOW(), NOW())";

        $idNotaNueva = insertar_datos($sql_insert);
        if ($idNotaNueva) {
            $total_actualizadas++;
            registrar_auditoria_nota($idNotaNueva, 'INSERT', null, $puntos_obtenidos, $usuario_id, 'editar_calificaciones_lote');
        } else {
            $errores[] = "Error al insertar alumno $idMatricula";
        }
    }
}

if ($total_actualizadas > 0 || $sin_cambios > 0) {
    http_response_code(200);
    $mensaje = $total_actualizadas > 0
        ? "$total_actualizadas calificación(es) actualizada(s)" . ($sin_cambios > 0 ? " ($sin_cambios sin cambios)" : "")
        : "Sin cambios: todas las calificaciones ya tenían estos valores";
    echo json_encode([
        'success' => true,
        'total' => $total_actualizadas,
        'sin_cambios' => $sin_cambios,
        'message' => $mensaje,
        'detalles' => $errores
    ]);
} else {
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'error' => 'No se pudo actualizar ninguna calificación',
        'detalles' => $errores
    ]);
}
