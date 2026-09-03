<?php
/**
 * Helper: api/registrar_auditoria.php
 *
 * PROPÓSITO:
 * Registrar en auditoria_nota cada vez que se inserta o actualiza una nota,
 * dejando rastro de quién hizo el cambio, cuándo, desde qué IP, y el valor
 * anterior vs el nuevo.
 *
 * USO:
 * require_once __DIR__ . '/registrar_auditoria.php';
 * registrar_auditoria_nota($idNota, 'INSERT', null, $puntosNuevos, $idUsuario, 'guardar_calificaciones_lote');
 * registrar_auditoria_nota($idNota, 'UPDATE', $puntosAntes, $puntosNuevos, $idUsuario, 'editar_calificaciones_lote');
 *
 * Requiere que conexion.php ya esté incluido antes (usa insertar_datos y limpiar_cadena).
 */

function registrar_auditoria_nota($idNota, $accion, $valor_antes, $valor_despues, $idUsuario, $detalle = null) {
    $idNota   = (int)$idNota;
    $idUsuario = (int)$idUsuario;

    // Solo se auditan estos tres tipos de acción sobre la tabla nota
    if (!in_array($accion, ['INSERT', 'UPDATE', 'DELETE'], true)) {
        return false;
    }

    $valorAntesSql   = ($valor_antes === null)   ? 'NULL' : (float)$valor_antes;
    $valorDespuesSql = ($valor_despues === null) ? 'NULL' : (float)$valor_despues;

    $ip    = $_SERVER['REMOTE_ADDR'] ?? '';
    $ipSql = $ip !== '' ? "'" . limpiar_cadena($ip) . "'" : 'NULL';

    $detalleSql = $detalle ? "'" . limpiar_cadena($detalle) . "'" : 'NULL';

    $sql = "INSERT INTO auditoria_nota
                (idNota, accion, valor_antes, valor_despues, campo, idUsuario, fecha, ip, detalle)
            VALUES
                ($idNota, '$accion', $valorAntesSql, $valorDespuesSql, 'puntos_obtenidos', $idUsuario, NOW(), $ipSql, $detalleSql)";

    return insertar_datos($sql);
}
