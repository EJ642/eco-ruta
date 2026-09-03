<?php
/**
 * API: Solicitar Enlace de Recuperación de Contraseña
 * ====================================================
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'msg' => 'Método no permitido.']);
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['status' => false, 'msg' => 'Por favor, ingrese un correo electrónico válido.']);
    exit;
}

require_once __DIR__ . '/../servicios/conexion.php';
require_once __DIR__ . '/../servicios/mailer.php';

$conexion = conectar_bd();

try {
    // 1. Buscar usuario activo
    $stmt = $conexion->prepare('SELECT id_usuario, nombre_completo, email, activo FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        // Mensaje seguro para no revelar emails registrados si no existe
        $conexion->close();
        echo json_encode([
            'status' => true,
            'msg' => 'Si el correo está registrado en EcoRuta, recibirás un enlace de recuperación en los próximos minutos.'
        ]);
        exit;
    }

    if (!(int) $usuario['activo']) {
        $conexion->close();
        echo json_encode([
            'status' => false,
            'msg' => 'Tu cuenta está inactiva. Por favor, comunícate con la administración.'
        ]);
        exit;
    }

    // 2. Generar token único y seguro
    $token = bin2hex(random_bytes(32));

    // 3. Invalidar tokens anteriores pendientes de este correo
    $stmtInvalidar = $conexion->prepare('UPDATE recuperacion_claves SET usado = 1 WHERE email = ? AND usado = 0');
    $stmtInvalidar->bind_param('s', $email);
    $stmtInvalidar->execute();
    $stmtInvalidar->close();

    // 4. Guardar nuevo token con vigencia de 1 hora
    $stmtInsertar = $conexion->prepare(
        'INSERT INTO recuperacion_claves (email, token, expiracion, usado)
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), 0)'
    );
    $stmtInsertar->bind_param('ss', $email, $token);
    $stmtInsertar->execute();
    $stmtInsertar->close();
    $conexion->close();

    // 5. Enviar correo electrónico
    $resultadoEnvio = enviar_correo_recuperacion($usuario['email'], $usuario['nombre_completo'], $token);

    if ($resultadoEnvio['status']) {
        echo json_encode([
            'status' => true,
            'msg' => '¡Correo enviado con éxito! Revisa tu bandeja de entrada o spam para restablecer tu contraseña.'
        ]);
    } else {
        // En caso de fallo SMTP en local (ej. sin internet), devolvemos el enlace para pruebas de desarrollo
        echo json_encode([
            'status' => true,
            'msg' => 'Se generó la solicitud de recuperación. Verificá tu correo electrónico.',
            'debug_link' => (defined('DEBUG_MAIL') && DEBUG_MAIL) ? $resultadoEnvio['link'] : null
        ]);
    }

} catch (Exception $e) {
    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->close();
    }
    error_log("Error en solicitar_recuperacion: " . $e->getMessage());
    echo json_encode([
        'status' => false,
        'msg' => 'Ocurrió un error al procesar tu solicitud. Intenta nuevamente.'
    ]);
}

