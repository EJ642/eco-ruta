<?php
/**
 * Pantalla para Restablecer Contraseña - EcoRuta
 * ===============================================
 */

require_once __DIR__ . '/servicios/conexion.php';

$token = trim($_GET['token'] ?? '');
$tokenValido = false;
$mensajeError = '';

if ($token === '') {
    $mensajeError = 'El enlace de recuperación es inválido o no contiene un token.';
} else {
    $conexion = conectar_bd();
    try {
        $stmt = $conexion->prepare(
            'SELECT id, email, expiracion, usado
             FROM recuperacion_claves
             WHERE token = ? LIMIT 1'
        );
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $registro = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conexion->close();

        if (!$registro) {
            $mensajeError = 'El enlace de recuperación no existe o ha sido modificado.';
        } elseif ((int) $registro['usado'] === 1) {
            $mensajeError = 'Este enlace de recuperación ya fue utilizado previamente.';
        } elseif (strtotime($registro['expiracion']) < time()) {
            $mensajeError = 'Este enlace ha expirado. Por favor, solicita uno nuevo.';
        } else {
            $tokenValido = true;
        }
    } catch (Exception $e) {
        $mensajeError = 'Error al verificar el enlace. Por favor, intente más tarde.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña | EcoRuta</title>
    
    <!-- Bootstrap Icons & Alertify (100% Local) -->
    <link rel="stylesheet" href="bt-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="alertify/alertify.min.css">
    <link rel="stylesheet" href="alertify/themes/default.min.css">
    <link rel="stylesheet" href="css/auth.css">
    
    <style>
        .req-list { list-style: none; padding: 0; margin: 12px 0 0; font-size: 0.82rem; }
        .req-list li { display: flex; align-items: center; gap: 8px; margin-bottom: 5px; color: var(--muted); }
        .req-list li.valid { color: #16724d; font-weight: 600; }
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 2.8rem; }
        .password-toggle { position: absolute; right: 0.75rem; background: none; border: none; padding: 0.25rem; color: var(--muted); cursor: pointer; font-size: 1.15rem; width: auto; margin: 0; }
        .password-toggle:hover { color: var(--green); }
        .back-link { display: inline-flex; align-items: center; gap: 6px; margin-top: 1.5rem; color: var(--green); text-decoration: none; font-weight: 600; font-size: 0.88rem; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main class="auth-layout">
        <!-- Panel Izquierdo: Branding -->
        <section class="auth-brand" aria-label="EcoRuta">
            <div class="brand-header">
                <div class="brand-badge">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Seguridad & Sostenibilidad</span>
                </div>
                <h1>EcoRuta</h1>
                <p class="brand-copy">
                    Crea una nueva contraseña segura para seguir gestionando tus operaciones logísticas con total protección.
                </p>
            </div>
            <div class="route-line" aria-hidden="true">
                <span></span><i></i><span></span>
            </div>
        </section>

        <!-- Panel Derecho: Formulario o Error -->
        <section class="auth-panel">
            <div class="auth-card">
                <?php if (!$tokenValido): ?>
                    <p class="eyebrow" style="color: var(--coral);">Enlace no disponible</p>
                    <h2>No se pudo continuar</h2>
                    <div class="alert" style="margin-top: 1rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <p class="muted" style="margin-top: 1rem;">Los enlaces de recuperación son de un solo uso y tienen una vigencia máxima de 60 minutos.</p>
                    <a href="index.php" class="back-link">
                        <i class="bi bi-arrow-left"></i> Volver a Iniciar Sesión
                    </a>
                <?php else: ?>
                    <p class="eyebrow">Recuperación de Cuenta</p>
                    <h2>Crea tu nueva contraseña</h2>
                    <p class="muted">Ingresa tu nueva clave de acceso y confírmala para actualizarla.</p>

                    <form id="formRestablecer" autocomplete="off">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="form-group">
                            <label for="nueva_clave">Nueva Contraseña</label>
                            <div class="password-wrapper">
                                <input type="password" id="nueva_clave" name="nueva_clave" placeholder="••••••••" required autofocus>
                                <button type="button" class="password-toggle" data-target="nueva_clave" title="Mostrar/Ocultar contraseña" aria-label="Mostrar contraseña">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <ul class="req-list">
                            <li id="req-len"><i class="bi bi-circle text-muted"></i> Mínimo 8 caracteres</li>
                            <li id="req-mayus"><i class="bi bi-circle text-muted"></i> Al menos una letra mayúscula</li>
                            <li id="req-num"><i class="bi bi-circle text-muted"></i> Al menos un número</li>
                            <li id="req-esp"><i class="bi bi-circle text-muted"></i> Al menos un carácter especial (@$!%*#?&)</li>
                        </ul>

                        <div class="form-group mt-3">
                            <label for="confirmar_clave">Confirmar Nueva Contraseña</label>
                            <div class="password-wrapper">
                                <input type="password" id="confirmar_clave" name="confirmar_clave" placeholder="••••••••" required>
                                <button type="button" class="password-toggle" data-target="confirmar_clave" title="Mostrar/Ocultar contraseña" aria-label="Mostrar contraseña">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="btnGuardar">
                            <span>Guardar y Acceder</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>

                    <a href="index.php" class="back-link">
                        <i class="bi bi-arrow-left"></i> Cancelar y volver al inicio
                    </a>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="alertify/alertify.min.js"></script>
    <script>
    // Toggle para mostrar/ocultar contraseñas
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

    <?php if ($tokenValido): ?>
    var inputClave = document.getElementById('nueva_clave');
    var inputConfirma = document.getElementById('confirmar_clave');

    function actualizarIconoReq(id, valido) {
        var li = document.getElementById(id);
        li.classList.toggle('valid', valido);
        var icon = li.querySelector('i');
        icon.className = valido ? 'bi bi-check-circle-fill text-success' : 'bi bi-circle text-muted';
    }

    function validarRequisitos(clave) {
        var len = clave.length >= 8;
        var mayus = /[A-Z]/.test(clave);
        var num = /[0-9]/.test(clave);
        var esp = /[^A-Za-z0-9]/.test(clave);

        actualizarIconoReq('req-len', len);
        actualizarIconoReq('req-mayus', mayus);
        actualizarIconoReq('req-num', num);
        actualizarIconoReq('req-esp', esp);

        return len && mayus && num && esp;
    }

    inputClave.addEventListener('input', function() {
        validarRequisitos(this.value);
    });

    document.getElementById('formRestablecer').addEventListener('submit', function(e) {
        e.preventDefault();
        var clave = inputClave.value;
        var confirma = inputConfirma.value;

        if (!validarRequisitos(clave)) {
            alertify.error('La contraseña no cumple con todos los requisitos de seguridad.');
            return;
        }

        if (clave !== confirma) {
            alertify.error('Las contraseñas no coinciden.');
            return;
        }

        var btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.innerHTML = 'Actualizando contraseña...';

        var formData = new FormData(this);

        fetch('api/restablecer_clave_guardar.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.status) {
                alertify.success(data.msg);
                setTimeout(function() {
                    window.location.href = 'index.php?restablecido=1';
                }, 1200);
            } else {
                alertify.error(data.msg);
                btn.disabled = false;
                btn.innerHTML = '<span>Guardar y Acceder</span> <i class="bi bi-arrow-right"></i>';
            }
        })
        .catch(function() {
            alertify.error('Error de comunicación con el servidor.');
            btn.disabled = false;
            btn.innerHTML = '<span>Guardar y Acceder</span> <i class="bi bi-arrow-right"></i>';
        });
    });
    <?php endif; ?>
    </script>
</body>
</html>
