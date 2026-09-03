<?php
/**
 * Helper: validar_periodo.php
 *
 * Función reutilizable para validar si el docente puede
 * cargar/editar notas en una evaluación, considerando:
 *   1. Si el período (semestre) está abierto (periodo.activo = 'Sí') → permite
 *   2. Si el período está cerrado PERO existe una excepción
 *      en periodo_excepcion para esa materia → permite
 *   3. Caso contrario → bloquea
 *
 * Uso:
 *   require_once __DIR__ . '/validar_periodo.php';
 *   $chk = puede_cargar_notas($idAulaMateria, $idPeriodo);
 *   if (!$chk['permitido']) {
 *       echo json_encode(['success' => false, 'error' => $chk['motivo']]);
 *       exit;
 *   }
 */

function puede_cargar_notas($idAulaMateria, $idPeriodo) {
    $idAulaMateria = (int)$idAulaMateria;
    $idPeriodo     = (int)$idPeriodo;

    $resPer = buscar_datos("SELECT activo, nombre FROM periodo WHERE idPeriodo = $idPeriodo LIMIT 1");
    if (!$resPer) {
        return ['permitido' => false, 'motivo' => 'Período no encontrado'];
    }
    $periodo = $resPer[0];

    // Período abierto → siempre permitido
    if ($periodo['activo'] === 'Sí') {
        return ['permitido' => true, 'motivo' => null];
    }

    // Período cerrado → revisar excepción para esta materia
    $resExc = buscar_datos("
        SELECT idExcepcion FROM periodo_excepcion
        WHERE idPeriodo = $idPeriodo AND idAulaMateria = $idAulaMateria
        LIMIT 1
    ");
    if ($resExc) {
        return ['permitido' => true, 'motivo' => null];
    }

    return [
        'permitido' => false,
        'motivo' => "El {$periodo['nombre']} está cerrado. Solicite al Director que reabra esta materia para poder editar notas."
    ];
}
