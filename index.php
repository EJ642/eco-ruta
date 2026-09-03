<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: router.php');
    exit;
}

$errores = [
    'credenciales'    => 'El correo electrónico o la contraseña son incorrectos.',
    'inactiva'        => 'Tu cuenta está inactiva. Contacta al administrador del sistema.',
    'sesion_expirada' => 'Tu sesión ha expirado por inactividad. Ingresa nuevamente.',
    'acceso_indebido' => 'Por favor, inicia sesión para acceder al sistema.',
];

$mensajes = [
    'restablecido'    => '¡Tu contraseña ha sido actualizada con éxito! Ingresa con tus nuevas credenciales.',
    'sesion_cerrada'  => 'Has cerrado sesión correctamente.',
];

$error = $errores[$_GET['error'] ?? ''] ?? '';
$mensaje = $mensajes[$_GET['msg'] ?? ($_GET['restablecido'] ?? false ? 'restablecido' : '')] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | EcoRuta - Logística Verde</title>
    
    <!-- Google Fonts & Bootstrap Icons -->
    <link rel="stylesheet" href="bt-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="alertify/alertify.min.css">
    <link rel="stylesheet" href="alertify/themes/default.min.css">
    
    <!-- Estilos de Autenticación -->
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <main class="auth-layout">
        <!-- Panel de Identidad & Marca -->
        <section class="auth-brand" aria-label="EcoRuta">
            <div class="brand-header">
                <div class="brand-badge">
                    <i class="bi bi-tree-fill"></i>
                    <span>Logística Sostenible</span>
                </div>
                <h1>EcoRuta</h1>
                <p class="brand-copy">
                    Plataforma inteligente de gestión y optimización de entregas de última milla con vehículos 100% ecológicos.
                </p>
            </div>

            <div class="brand-features">
                <div class="feature-item">
                    <span class="feature-icon"><i class="bi bi-bicycle"></i></span>
                    <span>Flota de bicicletas y vehículos eléctricos</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon"><i class="bi bi-geo-alt-fill"></i></span>
                    <span>Rutas optimizadas sin emisión de gases contaminantes</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon"><i class="bi bi-shield-check"></i></span>
                    <span>Confirmación de entrega mediante firma digital y QR</span>
                </div>
            </div>

            <div class="route-line" aria-hidden="true">
                <span></span><i></i><span></span>
            </div>
        </section>

        <!-- Panel de Formularios -->
        <section class="auth-panel">
            <div class="auth-card">
                
                <!-- ==================== VISTA 1: INICIO DE SESIÓN ==================== -->
                <div id="view-login" class="auth-view active">
                    <p class="eyebrow">Acceso al Sistema</p>
                    <h2>Bienvenido</h2>
                    <p class="muted">Ingresa tus credenciales para acceder a tu panel.</p>

                    <?php if ($error): ?>
                        <div class="alert" role="alert">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle-fill"></i>
                            <div><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="autenticar.php" method="post" id="formLogin">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ??= bin2hex(random_bytes(32)), ENT_QUOTES, 'UTF-8'); ?>">
                        
                        <div class="form-group">
                            <label for="email">Correo Electrónico</label>
                            <div class="input-group-custom">
                                <i class="bi bi-envelope input-icon"></i>
                                <input id="email" name="email" type="email" autocomplete="email" placeholder="ejemplo@ecoruta.com" required autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="label-row">
                                <label for="password">Contraseña</label>
                                <button type="button" class="link-subtle" id="btn-to-recuperar" tabindex="-1">
                                    ¿Olvidaste tu contraseña?
                                </button>
                            </div>
                            <div class="input-group-custom has-toggle">
                                <i class="bi bi-lock input-icon"></i>
                                <input id="password" name="password" type="password" autocomplete="current-password" placeholder="••••••••" required>
                                <button type="button" class="password-toggle" data-target="password" title="Mostrar/Ocultar contraseña" aria-label="Mostrar contraseña">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="btnLogin">
                            <span>Ingresar al Sistema</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>

                <!-- ==================== VISTA 2: RECUPERAR CONTRASEÑA ==================== -->
                <div id="view-recuperar" class="auth-view">
                    <p class="eyebrow">Recuperación Segura</p>
                    <h2>¿Olvidaste tu contraseña?</h2>
                    <p class="muted">Ingresa el correo electrónico asociado a tu cuenta y te enviaremos las instrucciones para restablecerla.</p>

                    <div id="alertRecuperar" class="alert d-none" style="display:none;" role="alert">
                        <i class="bi bi-info-circle-fill"></i>
                        <span id="alertRecuperarTexto"></span>
                    </div>

                    <form id="formRecuperar">
                        <div class="form-group">
                            <label for="recuperar_email">Correo Electrónico Registrado</label>
                            <div class="input-group-custom">
                                <i class="bi bi-envelope input-icon"></i>
                                <input id="recuperar_email" name="email" type="email" placeholder="ejemplo@ecoruta.com" required>
                            </div>
                        </div>

                        <button type="submit" id="btnEnviarRecuperacion">
                            <span id="btnRecuperarTexto">Enviar enlace de recuperación</span>
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>

                    <div class="back-btn-row">
                        <button type="button" class="btn-link-back" id="btn-to-login">
                            <i class="bi bi-arrow-left"></i>
                            <span>Volver al inicio de sesión</span>
                        </button>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <!-- AlertifyJS -->
    <script src="alertify/alertify.min.js"></script>

    <script>
    // 1. Alternancia entre Vistas (Login <-> Recuperar Contraseña)
    var viewLogin = document.getElementById('view-login');
    var viewRecuperar = document.getElementById('view-recuperar');
    var btnToRecuperar = document.getElementById('btn-to-recuperar');
    var btnToLogin = document.getElementById('btn-to-login');

    function mostrarVista(vista) {
        if (vista === 'recuperar') {
            viewLogin.classList.remove('active');
            viewRecuperar.classList.add('active');
            document.getElementById('recuperar_email').focus();
        } else {
            viewRecuperar.classList.remove('active');
            viewLogin.classList.add('active');
            document.getElementById('email').focus();
        }
    }

    btnToRecuperar.addEventListener('click', function(e) {
        e.preventDefault();
        mostrarVista('recuperar');
    });

    btnToLogin.addEventListener('click', function(e) {
        e.preventDefault();
        mostrarVista('login');
    });

    // 2. Toggle para ver/ocultar contraseña
    document.querySelectorAll('.password-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var input = document.getElementById(targetId);
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });
    });

    // 3. Envío Asíncrono de Solicitud de Recuperación
    document.getElementById('formRecuperar').addEventListener('submit', function(e) {
        e.preventDefault();
        var emailInput = document.getElementById('recuperar_email');
        var email = emailInput.value.trim();

        if (!email) {
            alertify.error('Por favor, ingresa tu correo electrónico.');
            return;
        }

        var btn = document.getElementById('btnEnviarRecuperacion');
        var btnTexto = document.getElementById('btnRecuperarTexto');
        btn.disabled = true;
        btnTexto.textContent = 'Enviando correo...';

        var formData = new FormData();
        formData.append('email', email);

        fetch('api/solicitar_recuperacion.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btnTexto.textContent = 'Enviar enlace de recuperación';

            if (data.status) {
                alertify.success(data.msg);
                emailInput.value = '';
                
                // Mostrar alerta informativa en la tarjeta
                var alertBox = document.getElementById('alertRecuperar');
                var alertText = document.getElementById('alertRecuperarTexto');
                alertBox.className = 'alert alert-success';
                alertBox.style.display = 'flex';
                alertText.textContent = data.msg;

                // Si viene enlace de debug (entorno local sin SMTP)
                if (data.debug_link) {
                    console.log('Enlace de recuperación (Debug): ' + data.debug_link);
                }
            } else {
                alertify.error(data.msg);
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btnTexto.textContent = 'Enviar enlace de recuperación';
            alertify.error('Error de comunicación con el servidor.');
        });
    });
    </script>
</body>
</html>
