<?php
/**
 * API: Docente/api/obtener_datos_usuario.php
 * Obtiene los datos del usuario logueado para la configuración
 */
session_name('DOCENTE_SESSION');
session_start();
require_once "../../servicios/conexion.php";

header('Content-Type: application/json; charset=utf-8');

// Verificar sesión
if (!isset($_SESSION['docente_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$docente_id = (int)$_SESSION['docente_id'];

// Consultar datos del docente y su usuario
$sql = "SELECT 
            d.idDocente,
            d.cedula,
            d.nombres,
            d.apellidos,
            d.correo,
            u.idUsuario,
            u.usuario,
            u.correo AS correo_usuario,
            r.rol
        FROM docente d
        INNER JOIN usuarios u ON d.idUsuario = u.idUsuario
        INNER JOIN rol r ON u.idRol = r.idRol
        WHERE d.idDocente = $docente_id
        AND d.estado = 'Activo'";

$resultado = buscar_datos($sql);

if (!$resultado || empty($resultado)) {
    echo json_encode(['success' => false, 'message' => 'Docente no encontrado']);
    exit;
}

$docente = $resultado[0];

echo json_encode([
    'success' => true,
    'usuario' => [
        'idDocente' => (int)$docente['idDocente'],
        'idUsuario' => (int)$docente['idUsuario'],
        'cedula' => $docente['cedula'],
        'nombres' => $docente['nombres'],
        'apellidos' => $docente['apellidos'],
        'correo' => $docente['correo'] ?? $docente['correo_usuario'],
        'usuario' => $docente['usuario'],
        'rol' => $docente['rol']
    ]
]);