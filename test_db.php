<?php

require_once __DIR__ . '/servicios/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$datos = buscar_datos("SELECT * FROM usuarios");

if ($datos !== false) {
    echo json_encode(['success' => true, 'rows' => $datos], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'error' => 'No se recuperaron filas o error de conexión.']);
}

?>
