<?php
/**
 * Servicio de Envío de Correos Electrónicos - EcoRuta
 * ====================================================
 * Utiliza PHPMailer para el envío seguro de correos transaccionales
 * con plantillas HTML responsivas y accesibles.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/mailer_config.php';

/**
 * Envía un correo con enlace seguro para restablecer contraseña
 * 
 * @param string $emailDestino Correo del usuario
 * @param string $nombreDestino Nombre del usuario
 * @param string $token Token criptográfico generado
 * @return array ['status' => bool, 'msg' => string, 'link' => string]
 */
function enviar_correo_recuperacion($emailDestino, $nombreDestino, $token) {
    $enlace = rtrim(URL_SISTEMA, '/') . '/restablecer_clave.php?token=' . urlencode($token);
    $nombreSeguro = htmlspecialchars($nombreDestino, ENT_QUOTES, 'UTF-8');

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = (MAIL_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 15;

        // Opciones SSL para entornos de desarrollo local (evita rechazos por certificados locales)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        // Remitente y Destinatario
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($emailDestino, $nombreDestino);

        // Contenido del Correo
        $mail->isHTML(true);
        $mail->Subject = '🌿 EcoRuta - Restablecimiento de contraseña';

        $cuerpoHTML = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - EcoRuta</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f5f7f1; font-family: 'Segoe UI', Arial, sans-serif; color: #16241f; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f5f7f1; padding: 30px 0; }
        .main-card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #dce5d8; overflow: hidden; box-shadow: 0 4px 20px rgba(22, 36, 31, 0.06); }
        .header { background: #16724d; padding: 32px 30px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 700; letter-spacing: -0.5px; }
        .header p { margin: 6px 0 0; color: #c7e86b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; }
        .content { padding: 36px 32px; font-size: 15px; line-height: 1.6; color: #2d3e37; }
        .greeting { font-size: 18px; font-weight: 700; color: #16241f; margin-bottom: 12px; }
        .btn-container { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background: #16724d; color: #ffffff !important; padding: 14px 28px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 15px; }
        .info-box { background: #f0f7f2; border-left: 4px solid #16724d; padding: 14px 18px; margin: 24px 0; font-size: 13px; color: #3c5449; border-radius: 0 6px 6px 0; }
        .footer { background: #fafcfa; border-top: 1px solid #e7efe5; padding: 22px 30px; text-align: center; font-size: 12px; color: #788c83; line-height: 1.5; }
        .link-alt { word-break: break-all; color: #16724d; font-size: 12px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-card" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <p>Plataforma de Logística Verde</p>
                    <h1>EcoRuta</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <div class="greeting">¡Hola, {$nombreSeguro}!</div>
                    <p>Recibimos una solicitud para restablecer la contraseña de acceso a tu cuenta en <strong>EcoRuta</strong>.</p>
                    <p>Para ingresar una nueva contraseña, haz clic en el siguiente botón:</p>
                    
                    <div class="btn-container">
                        <a href="{$enlace}" class="btn" target="_blank">Restablecer mi Contraseña →</a>
                    </div>

                    <div class="info-box">
                        <strong>⏳ Importante:</strong> Este enlace es de un solo uso y expirará en <strong>60 minutos</strong> por motivos de seguridad.<br>
                        Si no solicitaste este cambio, puedes ignorar este mensaje; tu cuenta permanece protegida.
                    </div>

                    <p style="font-size: 13px; color: #6d7d76; margin-top: 24px;">
                        Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                        <a href="{$enlace}" class="link-alt">{$enlace}</a>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    EcoRuta S.A. — Transformando la última milla en entregas cero emisiones.<br>
                    © 2026 EcoRuta. Todos los derechos reservados.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
HTML;

        $mail->Body = $cuerpoHTML;
        $mail->AltBody = "Hola {$nombreDestino},\n\nPara restablecer tu contraseña en EcoRuta ingresa al siguiente enlace:\n{$enlace}\n\nEste enlace expira en 60 minutos.\nSi no solicitaste este cambio, ignora este mensaje.";

        $mail->send();
        return [
            'status' => true,
            'msg' => 'Te enviamos un enlace de recuperación a tu correo electrónico.',
            'link' => $enlace
        ];
    } catch (Exception $e) {
        error_log("Error al enviar correo PHPMailer: " . $mail->ErrorInfo);
        return [
            'status' => false,
            'msg' => 'No pudimos enviar el correo en este momento. Verifique su conexión o intente más tarde.',
            'error_info' => $mail->ErrorInfo,
            'link' => $enlace // Disponible para fallback/pruebas
        ];
    }
}
?>

