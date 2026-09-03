<?php
/**
 * ARCHIVO: Docente/configuracion.php
 * Página de configuración de usuario para docentes
 * VERSIÓN VISUAL REDISEÑADA – Con ajuste para sidebar y funcionalidad de modales
 */
$ruta = "../";
include __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="<?php echo $ruta; ?>bt/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo $ruta; ?>bt-icons/bootstrap-icons.min.css">

<style>
/* ===== BASE ===== */
body {
    background: #f4f6f9;
    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
}

/* ===== AJUSTE PARA SIDEBAR ===== */
.main-content {
    background: transparent;
    min-height: calc(100vh - 60px);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 6px 16px 10px 16px;
    margin: 0;
    margin-left: 0;
    transition: margin-left 0.3s ease;
}

/* Cuando la sidebar está minimizada (oculta) */
.sidebar.minimize ~ .main-content,
.sidebar.minimize + .main-content,
body:not(.sidebar-minimized) .main-content {
    margin-left: 0;
}

/* Cuando la sidebar está desplegada */
.sidebar:not(.minimize) ~ .main-content,
.sidebar:not(.minimize) + .main-content,
body.sidebar-minimized .main-content {
    margin-left: 250px; /* Ancho de la sidebar */
}

/* ===== CONTENEDOR PRINCIPAL – más angosto ===== */
.config-container {
    max-width: 680px;
    width: 100%;
    margin: 0 auto;
}

/* ===== TARJETA ===== */
.config-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.02);
    border: none;
    overflow: hidden;
    width: 100%;
}

/* ===== HEADER – con toque amarillo ===== */
.config-card-header {
    background: #fefcf3;
    padding: 10px 22px;
    border-bottom: 2px solid #f5e6b0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-icon {
    width: 34px;
    height: 34px;
    background: #f5e6b0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #7a6200;
    font-size: 1.2rem;
}

.header-title {
    line-height: 1.2;
}

.header-title h4 {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
    color: #1f2a3f;
    letter-spacing: -0.01em;
}

.header-title small {
    font-size: 0.65rem;
    color: #7a8b9f;
    font-weight: 400;
    display: block;
    margin-top: -1px;
}

/* Badge de rol con amarillo */
.badge-rol-header {
    background: #f5e6b0;
    color: #6b5700;
    font-weight: 500;
    font-size: 0.65rem;
    padding: 3px 14px;
    border-radius: 30px;
    letter-spacing: 0.02em;
}

/* ===== CUERPO ===== */
.config-card-body {
    padding: 14px 22px 16px 22px;
}

/* ===== INFO USUARIO – con fondo verde suave ===== */
.info-usuario {
    background: #f1f8f4;
    border-radius: 16px;
    padding: 10px 16px;
    margin-bottom: 12px;
    border: 1px solid #d9ebe2;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
}

.info-item {
    display: flex;
    flex-direction: column;
    padding: 1px 0;
}

.info-item .label {
    font-size: 0.55rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #5f7d6e;
    font-weight: 600;
    margin-bottom: 0px;
}

.info-item .valor {
    font-size: 0.8rem;
    font-weight: 500;
    color: #1a2e2a;
    line-height: 1.3;
}

.badge-rol {
    background: #cde0d5;
    color: #1d4a3a;
    font-weight: 500;
    font-size: 0.65rem;
    padding: 3px 14px;
    border-radius: 30px;
    letter-spacing: 0.02em;
    display: inline-block;
}

/* ===== DIVISOR ===== */
.divider {
    height: 1px;
    background: #e6ecf2;
    border: 0;
    margin: 8px 0 12px 0;
}

/* ===== SECCIÓN TÍTULO – con verde ===== */
.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}

.section-title i {
    width: 28px;
    height: 28px;
    border-radius: 10px;
    background: #e1f0e8;
    color: #1f7a5a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.section-title h6 {
    font-size: 0.78rem;
    font-weight: 600;
    margin: 0;
    color: #1a3a30;
}

.section-title small {
    font-size: 0.6rem;
    color: #6f8a7c;
}

.text-muted.small {
    font-size: 0.62rem;
    color: #7a8f82;
    margin-bottom: 2px;
}

/* ===== FORMULARIO – CAMPOS COMPACTOS ===== */
.form-label {
    font-size: 0.68rem;
    font-weight: 500;
    color: #2a3f4a;
    margin-bottom: 1px;
    letter-spacing: 0.01em;
}

.requerido::after {
    content: " *";
    color: #c95a5a;
    font-weight: 600;
}

.form-control {
    border-radius: 12px;
    border: 1px solid #d5dee8;
    padding: 6px 12px;
    font-size: 0.78rem;
    height: 34px;
    background: #ffffff;
    transition: border 0.2s, box-shadow 0.2s;
    color: #1a2a33;
}

.form-control:focus {
    border-color: #a8c4b8;
    box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.08);
}

.form-control-lg {
    height: 34px;
    font-size: 0.78rem;
    padding: 6px 12px;
}

.form-text {
    font-size: 0.58rem;
    color: #7a8f82;
    margin-top: 1px;
}

/* ===== CAMPOS DE CONTRASEÑA ===== */
.password-toggle {
    position: relative;
}

.password-toggle .toggle-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #8a9faf;
    padding: 0 4px;
    font-size: 0.85rem;
    cursor: pointer;
    border-radius: 6px;
    transition: color 0.2s;
    height: 26px;
    display: flex;
    align-items: center;
}

.password-toggle .toggle-btn:hover {
    color: #2a4a3a;
    background: transparent;
}

.password-toggle .toggle-btn i {
    font-size: 0.9rem;
}

/* ===== BOTONES ===== */
.btn {
    font-weight: 500;
    border-radius: 40px;
    padding: 6px 18px;
    font-size: 0.72rem;
    letter-spacing: 0.01em;
    transition: all 0.12s;
}

.btn-cancelar {
    background: #f2f5f8;
    border: 1px solid #dfe6ef;
    color: #3a4f5e;
    padding: 6px 16px;
    border-radius: 40px;
}

.btn-cancelar:hover {
    background: #e8edf4;
    border-color: #ccd6e3;
    color: #1a2f3f;
}

.btn-guardar {
    background: #1a5a3e;
    border: none;
    color: white;
    padding: 6px 28px;
    border-radius: 40px;
    font-weight: 500;
    box-shadow: 0 2px 6px rgba(26, 90, 62, 0.10);
    transition: background 0.12s, transform 0.08s;
}

.btn-guardar:hover:not(:disabled) {
    background: #124a32;
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(26, 90, 62, 0.12);
}

.btn-guardar:disabled {
    opacity: 0.6;
    cursor: default;
    transform: none;
}

.d-flex.gap-2 {
    gap: 8px !important;
}

/* ===== ALERTA FLOTANTE ===== */
.alert-floating {
    position: fixed;
    top: 16px;
    right: 16px;
    z-index: 9999;
    min-width: 220px;
    max-width: 380px;
    border-radius: 16px;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.06);
    padding: 10px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.8rem;
    animation: slideIn 0.2s ease;
    background: #ffffff;
    border: 1px solid #e6edf4;
}

.alert-floating i {
    font-size: 1.1rem;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .config-card-header {
        padding: 8px 14px;
        flex-wrap: wrap;
    }
    .header-title h4 {
        font-size: 0.9rem;
    }
    .config-card-body {
        padding: 12px 14px 14px 14px;
    }
    .info-usuario {
        flex-direction: column;
        align-items: stretch;
        gap: 4px;
        padding: 10px 12px;
    }
    .info-item {
        flex-direction: row;
        justify-content: space-between;
        border-bottom: 1px solid #dcebe2;
        padding: 2px 0;
    }
    .info-item:last-child {
        border-bottom: none;
    }
    .info-item .label {
        width: 38%;
    }
    .info-item .valor {
        width: 62%;
        text-align: right;
    }
    .d-flex.gap-2 {
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .btn-guardar {
        padding: 6px 20px;
    }
    
    /* Ajuste para sidebar en móvil */
    .sidebar:not(.minimize) ~ .main-content,
    .sidebar:not(.minimize) + .main-content,
    body.sidebar-minimized .main-content {
        margin-left: 0;
    }
}

@media (max-width: 576px) {
    .main-content {
        padding: 4px 8px 8px 8px;
        min-height: calc(100vh - 54px);
        margin-left: 0 !important;
    }
    .config-card-body {
        padding: 8px 10px 12px 10px;
    }
    .form-control {
        height: 30px;
        font-size: 0.72rem;
        padding: 4px 10px;
    }
    .btn {
        padding: 4px 14px;
        font-size: 0.68rem;
    }
}

/* ===== Ajuste para que todo quepa en una pantalla (escritorio) ===== */
@media (min-height: 680px) and (min-width: 1024px) {
    .main-content {
        padding-top: 4px;
        padding-bottom: 4px;
        min-height: calc(100vh - 54px);
        align-items: center;
    }
    .config-card-body {
        padding: 12px 22px 14px 22px;
    }
    .info-usuario {
        padding: 8px 14px;
        margin-bottom: 8px;
    }
    .divider {
        margin: 6px 0 10px 0;
    }
    .mb-3 {
        margin-bottom: 0.35rem !important;
    }
    .mb-4 {
        margin-bottom: 0.5rem !important;
    }
    .section-title {
        margin-bottom: 4px;
    }
    .form-text {
        font-size: 0.55rem;
    }
    .badge-rol-header {
        font-size: 0.6rem;
        padding: 2px 12px;
    }
}
</style>

<div class="main-content">
    <div class="config-container">

        <div class="config-card">

            <!-- HEADER CON AMARILLO -->
            <div class="config-card-header">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <div class="header-title">
                        <h4>Configuración de usuario</h4>
                        <small>Datos de acceso al sistema</small>
                    </div>
                </div>
                <span class="badge-rol-header" id="docenteRolHeader">Docente</span>
            </div>

            <div class="config-card-body">

                <!-- INFO USUARIO CON FONDO VERDE -->
                <div class="info-usuario" id="infoUsuario">
                    <div class="info-item">
                        <span class="label">Nombre</span>
                        <span class="valor" id="docenteNombre">Cargando...</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Correo</span>
                        <span class="valor" id="docenteCorreo">Cargando...</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Cédula</span>
                        <span class="valor" id="docenteCedula">Cargando...</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Rol</span>
                        <span class="valor"><span class="badge-rol" id="docenteRol">Cargando...</span></span>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- FORMULARIO -->
                <form id="configForm" autocomplete="off">

                    <!-- CAMPO USUARIO -->
                    <div class="mb-3">
                        <label for="usuario" class="form-label requerido">
                            <i class="bi bi-person me-1"></i>Nombre de usuario
                        </label>
                        <input type="text" class="form-control form-control-lg" id="usuario"
                               name="usuario" placeholder="ej. jperez"
                               pattern="[a-zA-Z0-9_]{4,}" required>
                        <div class="form-text"><i class="bi bi-info-circle me-1"></i>Mínimo 4 caracteres, letras, números y guión bajo</div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="divider"></div>

                    <!-- CAMBIAR CONTRASEÑA (con verde) -->
                    <div class="mb-2">
                        <div class="section-title">
                            <i class="bi bi-shield-lock"></i>
                            <div>
                                <h6>Cambiar contraseña</h6>
                                <small>Opcional: complete solo si desea actualizarla</small>
                            </div>
                        </div>
                    </div>

                    <!-- Contraseña actual -->
                    <div class="mb-2 password-toggle">
                        <label for="contrasena_actual" class="form-label">Contraseña actual</label>
                        <input type="password" class="form-control" id="contrasena_actual"
                               name="contrasena_actual" placeholder="Ingrese su contraseña actual">
                        <button type="button" class="toggle-btn" onclick="togglePassword('contrasena_actual')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Nueva contraseña -->
                    <div class="mb-2 password-toggle">
                        <label for="contrasena_nueva" class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control" id="contrasena_nueva"
                               name="contrasena_nueva" placeholder="Mínimo 8 caracteres">
                        <button type="button" class="toggle-btn" onclick="togglePassword('contrasena_nueva')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <div class="form-text"><i class="bi bi-info-circle me-1"></i>Mínimo 8 caracteres, incluya números, letras y caracteres especiales</div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Confirmar nueva contraseña -->
                    <div class="mb-3 password-toggle">
                        <label for="contrasena_confirmar" class="form-label">Confirmar nueva contraseña</label>
                        <input type="password" class="form-control" id="contrasena_confirmar"
                               name="contrasena_confirmar" placeholder="Repita la nueva contraseña">
                        <button type="button" class="toggle-btn" onclick="togglePassword('contrasena_confirmar')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="divider"></div>

                    <!-- BOTONES -->
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-cancelar" id="btnVolver">
                            <i class="bi bi-arrow-left me-1"></i> Volver
                        </button>
                        <button type="button" class="btn btn-guardar" id="guardarConfig">
                            <i class="bi bi-check-lg me-1"></i> Guardar cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ===== FUNCIONES VISUALES =====
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.parentElement.querySelector('.toggle-btn i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const btnGuardar = document.getElementById('guardarConfig');
    const btnVolver = document.getElementById('btnVolver');
    let usuarioLogueado = null;

    // ===== FUNCIÓN PARA CERRAR MODAL / VOLVER =====
    function cerrarModal() {
        // Cerrar cualquier modal de Bootstrap abierto
        const modales = document.querySelectorAll('.modal.show');
        modales.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
        
        // Redirigir a la página anterior o al menú principal
        const referrer = document.referrer;
        if (referrer && referrer.includes(window.location.hostname)) {
            window.location.href = referrer;
        } else {
            window.location.href = '<?php echo $ruta; ?>Docente/menu.php';
        }
    }

    // ===== EVENTO PARA BOTÓN VOLVER =====
    btnVolver.addEventListener('click', function(e) {
        e.preventDefault();
        cerrarModal();
    });

    // ===== FUNCIÓN PARA CARGAR DATOS DEL USUARIO =====
    function cargarDatosUsuario() {
        const nombreEl = document.getElementById('docenteNombre');
        const correoEl = document.getElementById('docenteCorreo');
        const cedulaEl = document.getElementById('docenteCedula');
        const rolEl = document.getElementById('docenteRol');
        const rolHeader = document.getElementById('docenteRolHeader');

        nombreEl.textContent = '...';
        correoEl.textContent = '...';
        cedulaEl.textContent = '...';
        rolEl.textContent = 'Cargando...';
        rolHeader.textContent = '...';

        fetch('api/obtener_datos_usuario.php', {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                usuarioLogueado = data.usuario;
                nombreEl.textContent = `${usuarioLogueado.nombres} ${usuarioLogueado.apellidos}`;
                correoEl.textContent = usuarioLogueado.correo;
                cedulaEl.textContent = usuarioLogueado.cedula || 'No registrada';
                rolEl.textContent = usuarioLogueado.rol || 'Docente';
                rolHeader.textContent = usuarioLogueado.rol || 'Docente';
                document.getElementById('usuario').value = usuarioLogueado.usuario;
            } else {
                nombreEl.textContent = 'Error';
                correoEl.textContent = '--';
                cedulaEl.textContent = '--';
                rolEl.textContent = 'Error';
                rolHeader.textContent = 'Error';
            }
        })
        .catch(() => {
            nombreEl.textContent = 'Error de conexión';
            correoEl.textContent = '--';
            cedulaEl.textContent = '--';
            rolEl.textContent = 'Error';
            rolHeader.textContent = 'Error';
        });
    }

    cargarDatosUsuario();

    // ===== FUNCIÓN PARA GUARDAR CAMBIOS =====
    function guardarCambios() {
        const usuario = document.getElementById('usuario').value.trim();
        const contrasenaActual = document.getElementById('contrasena_actual').value;
        const contrasenaNueva = document.getElementById('contrasena_nueva').value;
        const contrasenaConfirmar = document.getElementById('contrasena_confirmar').value;

        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        let valid = true;
        const usuarioRegex = /^[a-zA-Z0-9_]{4,}$/;

        if (!usuarioRegex.test(usuario)) {
            valid = false;
            const input = document.getElementById('usuario');
            input.classList.add('is-invalid');
            const feedback = input.parentElement.querySelector('.invalid-feedback');
            feedback.textContent = 'Usuario inválido. Mínimo 4 caracteres, solo letras, números y guión bajo';
        }

        const cambiandoPass = contrasenaActual || contrasenaNueva || contrasenaConfirmar;
        if (cambiandoPass) {
            if (!contrasenaActual) {
                valid = false;
                const input = document.getElementById('contrasena_actual');
                input.classList.add('is-invalid');
                const feedback = input.parentElement.querySelector('.invalid-feedback');
                feedback.textContent = 'Debe ingresar su contraseña actual';
            }
            if (contrasenaNueva.length < 6) {
                valid = false;
                const input = document.getElementById('contrasena_nueva');
                input.classList.add('is-invalid');
                const feedback = input.parentElement.querySelector('.invalid-feedback');
                feedback.textContent = 'La nueva contraseña debe tener al menos 8 caracteres';
            }
            if (contrasenaNueva !== contrasenaConfirmar) {
                valid = false;
                const input = document.getElementById('contrasena_confirmar');
                input.classList.add('is-invalid');
                const feedback = input.parentElement.querySelector('.invalid-feedback');
                feedback.textContent = 'Las contraseñas no coinciden';
            }
        }

        if (!valid) return;

        const datos = {
            usuario: usuario,
            contrasena_actual: contrasenaActual,
            contrasena_nueva: contrasenaNueva
        };

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';

        fetch('api/actualizar_usuario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        })
        .then(response => response.json())
        .then(data => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar cambios';

            if (data.success) {
                mostrarAlerta(data.message, 'success');
                cargarDatosUsuario();
                document.getElementById('contrasena_actual').value = '';
                document.getElementById('contrasena_nueva').value = '';
                document.getElementById('contrasena_confirmar').value = '';
                
                // Cerrar modal después de guardar exitosamente
                setTimeout(() => {
                    cerrarModal();
                }, 1500);
            } else {
                mostrarAlerta(data.message, 'danger');
                if (data.code === 'PASS_INCORRECT') {
                    const input = document.getElementById('contrasena_actual');
                    input.classList.add('is-invalid');
                    const feedback = input.parentElement.querySelector('.invalid-feedback');
                    feedback.textContent = 'Contraseña actual incorrecta';
                }
            }
        })
        .catch(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar cambios';
            mostrarAlerta('Error de conexión al servidor', 'danger');
        });
    }

    btnGuardar.addEventListener('click', guardarCambios);

    document.getElementById('configForm').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            guardarCambios();
        }
    });

    // ===== FUNCIÓN PARA MOSTRAR ALERTAS =====
    function mostrarAlerta(mensaje, tipo) {
        const oldAlert = document.querySelector('.alert-floating');
        if (oldAlert) oldAlert.remove();

        const alertDiv = document.createElement('div');
        const isSuccess = tipo === 'success';
        alertDiv.className = 'alert-floating';
        alertDiv.style.borderLeft = isSuccess ? '4px solid #1a7a4a' : '4px solid #c95a5a';
        alertDiv.innerHTML = `
            <i class="bi ${isSuccess ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'}"
               style="color: ${isSuccess ? '#1a7a4a' : '#c95a5a'};"></i>
            <span>${mensaje}</span>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => { if (alertDiv.parentNode) alertDiv.remove(); }, 5000);
    }

    // ===== OBSERVAR CAMBIOS EN LA SIDEBAR PARA AJUSTAR MARGEN =====
    function ajustarMargenSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        if (sidebar && mainContent) {
            if (sidebar.classList.contains('minimize')) {
                mainContent.style.marginLeft = '0';
            } else {
                mainContent.style.marginLeft = '250px';
            }
        }
    }

    // Escuchar cambios en la sidebar
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    ajustarMargenSidebar();
                }
            });
        });
        observer.observe(sidebar, { attributes: true });
        
        // Ajustar inicialmente
        setTimeout(ajustarMargenSidebar, 100);
    }

    // También ajustar cuando se redimensiona la ventana
    window.addEventListener('resize', ajustarMargenSidebar);
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>