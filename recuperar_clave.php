<?php
session_start();
require_once "servicios/conexion.php";
require_once "servicios/mailer_config.php";

// 1. IMPORTAR LAS CLASES DE PHPMAILER
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$alert = '';
$origen = isset($_GET['origen']) ? strtolower(limpiar_cadena($_GET['origen'])) : 'administracion';
if (!in_array($origen, ['administracion', 'docente', 'tutor'], true)) {
    $origen = 'administracion';
}

if (!empty($_POST)) {
    if (empty($_POST['correo'])) {
        $alert = '<div class="alert alert-warning">Ingrese su correo electrónico.</div>';
    } else {
        $email = limpiar_cadena($_POST['correo']);

        $roles_permitidos = $origen === 'docente' ? '3' : '1,2,5';
        $query = buscar_datos("SELECT idUsuario, usuario FROM usuarios WHERE correo = '$email' AND estado = 'Activo' AND idRol IN ($roles_permitidos)");

        if ($query) {
            $data = $query[0];
            $id_usuario = $data['idUsuario'];
            $nombre = $data['usuario'];

            // Generar Token y Expiración
            $token = bin2hex(random_bytes(32));
            $sql_update = "UPDATE usuarios SET token = '$token', token_expira = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE idUsuario = $id_usuario";
            
            if (actualizar_datos($sql_update)) {

                // ======================================================
                // INICIO: CONFIGURACIÓN DE ENVÍO CON PHPMAILER
                // ======================================================
                
                $link_recuperacion = URL_SISTEMA . "cambiar_password.php?id=$id_usuario&token=$token&origen=$origen";

                $mail = new PHPMailer(true);

                try {
                    // A) Configuración del Servidor SMTP
                    if (DEBUG_MAIL) {
                        $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Muestra errores detallados
                    }
                    $mail->isSMTP();
                    $mail->Host       = MAIL_HOST;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = MAIL_USERNAME;
                    $mail->Password   = MAIL_PASSWORD;
                    $mail->SMTPSecure = (MAIL_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = MAIL_PORT;

                    // B) Destinatarios
                    $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME); // Quien lo envía
                    $mail->addAddress($email, $nombre);     // A quien le llega

                    // C) Contenido
                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8'; // Para que se vean bien las tildes y ñ
                    $mail->Subject = 'Recuperar Contraseña - Sistema Web';
                    
                    // Cuerpo del mensaje (HTML Bonito)
                    $mail->Body    = '
                        <h1>Hola, '.$nombre.'</h1>
                        <p>Has solicitado restablecer tu contraseña en nuestro sistema.</p>
                        <p>Haz clic en el siguiente botón para crear una nueva clave (este enlace vence en 1 hora):</p>
                        <br>
                        <a href="'.$link_recuperacion.'" style="background-color: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">RESTABLECER CONTRASEÑA</a>
                        <br><br>
                        <p><small>Si no solicitaste este cambio, ignora este correo.</small></p>
                    ';
                    
                    // Texto plano por si el cliente de correo no soporta HTML
                    $mail->AltBody = 'Hola '.$nombre.'. Para restablecer tu clave visita: ' . $link_recuperacion;

                    $mail->send();
                    
                    $alert = '<div class="alert alert-success text-center">
                                <h4><i class="bi bi-check-circle"></i> Correo Enviado</h4>
                                <p>Revisa tu bandeja de entrada (y spam) para continuar.</p>
                              </div>';

                } catch (Exception $e) {
                    $alert = '<div class="alert alert-danger">Error al enviar el correo. Mailer Error: ' . $mail->ErrorInfo . '</div>';
                }
                // ======================================================
                // FIN PHPMAILER
                // ======================================================

            } else {
                $alert = '<div class="alert alert-danger">Error al generar el token. Intente más tarde.</div>';
            }

        } else {
            $alert = '<div class="alert alert-danger">El correo no está registrado o el usuario está inactivo.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/recuperar.png">
    <title>Recuperar Contraseña</title>
    <link href="bt/bootstrap.min.css" rel="stylesheet">
    <link href="bt-icons/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-login { 
            width: 100%; 
            max-width: 420px; 
            border: none; 
            border-radius: 12px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .card-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 2rem 1.5rem; 
            text-align: center; 
        }
        .card-header h4 { 
            font-weight: 600;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
        }
        .card-header .bi { margin-right: 0.5rem; }
        .card-body { 
            padding: 2rem; 
            background: white;
        }
        .form-floating .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            font-weight: 600;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
        }
        .text-help { 
            color: #6c757d; 
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .alert { 
            border: none;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    
    <div class="card card-login">
        <div class="card-header">
            <h4 class="mb-0"><i class="bi bi-shield-lock"></i> Recuperar Contraseña</h4>
        </div>
        <div class="card-body">
            
            <?php echo $alert; ?>

            <form action="recuperar_clave.php?origen=<?php echo $origen; ?>" method="post">
                <p class="text-help">Ingrese su correo electrónico y le enviaremos un enlace para restablecer su contraseña.</p>
                
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" name="correo" id="correo" placeholder="name@example.com" required>
                    <label for="correo"><i class="bi bi-envelope me-2"></i>Correo Electrónico</label>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-send me-2"></i>Enviar Solicitud
                    </button>
                    <!-- Verificar si la solicitud es desde administracion, docente o tutor -->
                    <a href="<?php echo $origen === 'docente' ? 'Docente/index.php' : ($origen === 'tutor' ? 'tutor/index.php' : 'Administracion/index.php'); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al Login
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>