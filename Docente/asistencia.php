<?php
    $ruta = '../';
    include __DIR__ . '/includes/header.php';

    require_once __DIR__ . '/../servicios/conexion.php';
    $docente_id = $_SESSION['docente_id'] ?? null;
    if (!$docente_id) { header('Location: index.php'); exit; }
?>

<!-- DataTables -->
<link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css">
<script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>
<script src="<?php echo $ruta; ?>dt/jquery.dataTables.min.js"></script>
<script src="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.js"></script>

<style>
.main-content { display: block; }

/* ── Tabs ── */
.tabs-wrapper { display:flex; justify-content:center; margin-bottom:24px; }
.tab-group    { display:flex; border:1px solid #dee2e6; border-radius:8px; overflow:hidden; width:360px; }
.tab-btn {
    flex:1; padding:11px 20px; border:none;
    background:#fff; color:#6c757d;
    font-size:14px; font-weight:400; cursor:pointer;
    display:flex; align-items:center; justify-content:center; gap:8px;
    transition:background .15s, color .15s;
}
.tab-btn + .tab-btn { border-left:1px solid #dee2e6; }
.tab-btn.active { background:#0d6efd; color:#fff; font-weight:500; }
.tab-btn:not(.active):hover { background:#f8f9fa; }

/* ── Panels ── */
.panel { display:none; }
.panel.active { display:block; }

/* ── Form grid 4 columnas ── */
.form-grid {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:14px; margin-bottom:16px;
}
@media(max-width:900px){ .form-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:540px){ .form-grid{ grid-template-columns:1fr; } }

/* ── Info bar ── */
.info-bar {
    background:#e8f4fd; border:1px solid #b6d4fe;
    border-radius:8px; padding:10px 16px;
    font-size:13px; color:#0a58ca;
    display:flex; gap:24px; flex-wrap:wrap; margin-bottom:16px;
}
.info-bar strong { color:#084298; }

/* ── Horas: texto plano, sin barra visual ── */
.horas-info {
    background:#f8f9fa; border:1px solid #e9ecef;
    border-radius:8px; padding:9px 16px;
    font-size:13px; color:#495057;
    display:flex; align-items:center; gap:8px; flex-wrap:wrap;
    margin-bottom:14px;
}
.horas-info strong { color:#212529; }
.horas-info.exceso { background:#fff3cd; border-color:#ffe69c; color:#664d03; }
.horas-info.exceso strong { color:#664d03; }

/* ── Leyenda ── */
.legend-bar {
    background:#f8f9fa; border:1px solid #e9ecef;
    border-radius:8px; padding:8px 14px;
    font-size:12px; color:#6c757d;
    display:flex; align-items:center; gap:18px;
    margin-bottom:14px; flex-wrap:wrap;
}
.legend-dot { width:10px; height:10px; border-radius:50%; display:inline-block; margin-right:5px; vertical-align:middle; }

/* ── Toolbar "seleccionar todos" ── */
.select-all-bar {
    display:flex; align-items:center; gap:10px;
    padding:8px 14px; background:#f0f4ff;
    border:1px solid #c7d7fd; border-radius:8px;
    margin-bottom:10px; flex-wrap:wrap;
}
.select-all-bar .bar-label { font-size:12px; font-weight:600; color:#3d5af1; }
.btn-select-all {
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 12px; border-radius:6px; font-size:12px;
    font-weight:500; cursor:pointer; border:none; transition:background .15s;
}
.btn-select-all.presentes { background:#d1e7dd; color:#0a3622; }
.btn-select-all.presentes:hover { background:#a3cfbb; }
.btn-select-all.ausentes  { background:#f8d7da; color:#58151c; }
.btn-select-all.ausentes:hover  { background:#f1aeb5; }

/* ── Switch personalizado ── */
.asist-switch { position:relative; display:inline-block; width:46px; height:26px; }
.asist-switch input { opacity:0; width:0; height:0; }
.asist-slider {
    position:absolute; cursor:pointer;
    top:0; left:0; right:0; bottom:0;
    background:#dc3545; border-radius:26px; transition:.2s;
}
.asist-slider:before {
    position:absolute; content:"";
    height:20px; width:20px; left:3px; bottom:3px;
    background:#fff; border-radius:50%; transition:.2s;
}
.asist-switch input:checked + .asist-slider { background:#198754; }
.asist-switch input:checked + .asist-slider:before { transform:translateX(20px); }

/* ── Estado badges ── */
.badge-presente { background:#d1e7dd !important; color:#0a3622 !important; font-size:11px; }
.badge-ausente  { background:#f8d7da !important; color:#58151c !important; font-size:11px; }
.badge-tardanza { background:#fff3cd !important; color:#664d03 !important; font-size:11px; }
.badge-justif   { background:#cfe2ff !important; color:#084298 !important; font-size:11px; }

/* ── Tabla ── */
#tabla-alumnos thead th, #tabla-historial thead th,
#tabla-alumnos-editar thead th {
    background:#f8f9fa; font-size:12px; font-weight:600; color:#495057; vertical-align:middle;
}
#tabla-alumnos td, #tabla-historial td,
#tabla-alumnos-editar td { vertical-align:middle; font-size:13px; }

/* ── Empty state ── */
.empty-state { text-align:center; padding:48px 0; color:#adb5bd; }
.empty-state i { font-size:3rem; display:block; margin-bottom:12px; }
.empty-state p { font-size:14px; margin:0; }

/* ── Feedback fecha ── */
.fecha-feedback { font-size:11px; margin-top:4px; min-height:16px; }
.fecha-feedback.ok      { color:#198754; }
.fecha-feedback.error   { color:#dc3545; font-weight:600; }
.fecha-feedback.loading { color:#6c757d; }

.fecha-feedback.warning { color:#856404; background:#fff3cd; padding:4px 10px; border-radius:4px; }
</style>

    <div class="main-content">

        <!-- ── Tabs centrados ── -->
        <div class="tabs-wrapper">
            <div class="tab-group">
                <button class="tab-btn active" id="tab-nueva" onclick="switchTab('nueva')">
                    <i class="bi bi-plus-circle"></i> Nueva Asistencia
                </button>
                <button class="tab-btn" id="tab-historial" onclick="switchTab('historial')">
                    <i class="bi bi-clock-history"></i> Historial
                </button>
                <button class="tab-btn d-none" id="tab-editar" onclick="switchTab('editar')">
                    <i class="bi bi-pencil-square"></i> Editar
                </button>
            </div>
        </div>

        <!-- ══ PANEL: NUEVA ASISTENCIA ══ -->
        <div class="panel active" id="panel-nueva">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-4 fw-semibold">
                        <i class="bi bi-calendar-check text-primary"></i> Registro de Asistencia
                    </h5>

                    <!-- Filtros en grilla 4 columnas -->
                    <div class="form-grid">
                        <div>
                            <label class="form-label fw-semibold" style="font-size:13px;">
                                <span class="badge bg-primary me-1" style="font-size:10px;">1</span> Curso
                            </label>
                            <select id="select-curso" class="form-select form-select-sm">
                                <option value="">-- Seleccione un curso --</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" style="font-size:13px;">
                                <span class="badge bg-primary me-1" style="font-size:10px;">2</span> Materia
                            </label>
                            <select id="select-materia" class="form-select form-select-sm" disabled>
                                <option value="">-- Seleccione una materia --</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" style="font-size:13px;">
                                <span class="badge bg-primary me-1" style="font-size:10px;">3</span> Fecha
                            </label>
                            <input type="date" id="fecha-asistencia" class="form-control form-control-sm" disabled>
                            <div class="fecha-feedback" id="fecha-feedback-nueva"></div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" style="font-size:13px;">
                                <span class="badge bg-primary me-1" style="font-size:10px;">4</span> Horas de clase
                            </label>
                            <input type="number" id="cantidad-horas" class="form-control form-control-sm"
                                   min="1" max="10" value="1" disabled>
                            <small class="text-muted" style="font-size:11px;">Horas dictadas esta sesión</small>
                        </div>
                    </div>

                    <!-- Info bar -->
                    <div id="info-aula" class="info-bar d-none">
                        <span><strong>Aula:</strong> <span id="nombre-aula"></span></span>
                        <span><strong>Materia:</strong> <span id="nombre-materia"></span></span>
                        <span><strong>Horas semanales:</strong> <span id="horas-semanales">-</span></span>
                    </div>

                    <!-- Horas: texto plano -->
                    <div id="horas-info" class="horas-info d-none">
                        <i class="bi bi-clock"></i>
                        <span id="horas-info-texto">Horas usadas esta semana: 0 / 0 — Disponibles: 0</span>
                    </div>

                    <!-- Leyenda -->
                    <div class="legend-bar">
                        <strong style="color:#495057;">Leyenda:</strong>
                        <span><span class="legend-dot" style="background:#198754;"></span>= Presente</span>
                        <span><span class="legend-dot" style="background:#dc3545;"></span>= Ausente</span>
                    </div>

                    <!-- Tabla de alumnos -->
                    <div id="div-alumnos" class="d-none">

                        <!-- Toolbar seleccionar todos -->
                        <div class="select-all-bar">
                            <span class="bar-label"><i class="bi bi-person-check me-1"></i> Selección rápida:</span>
                            <button type="button" class="btn-select-all presentes" onclick="seleccionarTodos('nueva', true)">
                                <i class="bi bi-check-all"></i> Todos Presentes
                            </button>
                            <button type="button" class="btn-select-all ausentes" onclick="seleccionarTodos('nueva', false)">
                                <i class="bi bi-x-lg"></i> Todos Ausentes
                            </button>
                        </div>

                        <table id="tabla-alumnos" class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th style="width:100px;">Cédula</th>
                                    <th>Apellidos y Nombres</th>
                                    <th style="width:140px; text-align:center;">Asistencia</th>
                                    <th style="width:100px; text-align:center;">Estado</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-alumnos"></tbody>
                        </table>
                        <div class="text-end mt-3">
                            <button type="button" id="btn-cancelar" class="btn btn-outline-secondary btn-sm me-2">
                                <i class="bi bi-x-circle me-1"></i> Cancelar
                            </button>
                            <button type="button" id="btn-guardar" class="btn btn-primary btn-sm px-4">
                                <i class="bi bi-save me-1"></i> Guardar Asistencia
                            </button>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div id="msg-seleccionar" class="empty-state">
                        <i class="bi bi-arrow-right-circle"></i>
                        <p>Seleccione un curso y luego una materia para comenzar</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ PANEL: HISTORIAL ══ -->
        <div class="panel" id="panel-historial">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-4 fw-semibold">
                        <i class="bi bi-clock-history text-secondary"></i> Historial de Asistencias
                    </h5>

                    <div class="form-grid">
                        <div>
                            <label class="form-label" style="font-size:13px;">Curso</label>
                            <select id="historial-curso" class="form-select form-select-sm">
                                <option value="">-- Todos los cursos --</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" style="font-size:13px;">Materia</label>
                            <select id="historial-materia" class="form-select form-select-sm" disabled>
                                <option value="">-- Todas las materias --</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" style="font-size:13px;">Desde</label>
                            <input type="date" id="historial-desde" class="form-control form-control-sm"
                                   value="<?php echo date('Y-m-01'); ?>">
                        </div>
                        <div>
                            <label class="form-label" style="font-size:13px;">Hasta</label>
                            <input type="date" id="historial-hasta" class="form-control form-control-sm"
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <button type="button" id="btn-limpiar-historial" class="btn btn-outline-secondary btn-sm px-4">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar Filtros
                        </button>
                    </div>

                    <table id="tabla-historial" class="table table-striped table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Curso</th>
                                <th>Materia</th>
                                <th style="text-align:center;">Horas</th>
                                <th style="text-align:center;">Total</th>
                                <th style="text-align:center;">Presentes</th>
                                <th style="text-align:center;">Ausentes</th>
                                <th style="text-align:center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-historial"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══ PANEL: EDITAR ASISTENCIA ══ -->
        <div class="panel" id="panel-editar">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 fw-semibold">
                            <i class="bi bi-pencil-square text-warning"></i> Editar Asistencia
                        </h5>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="volverAlHistorial()">
                            <i class="bi bi-arrow-left me-1"></i> Volver al Historial
                        </button>
                    </div>

                    <!-- Info bar edición (curso, materia, fecha — todo solo lectura) -->
                    <div id="info-aula-editar" class="info-bar">
                        <span><strong>Curso:</strong> <span id="editar-curso"></span></span>
                        <span><strong>Materia:</strong> <span id="editar-materia"></span></span>
                        <span><strong>Fecha:</strong> <span id="editar-fecha-label"></span></span>
                    </div>
                    <div class="alert alert-secondary py-2 px-3 mb-3" style="font-size:12px;">
                        <i class="bi bi-info-circle me-1"></i>
                        La fecha de la sesión no es editable. Si la fecha es incorrecta, registre una nueva asistencia
                        en la fecha correcta desde la pestaña "Nueva Asistencia".
                    </div>

                    <!-- Horas: texto plano edición -->
                    <div id="horas-info-editar" class="horas-info d-none">
                        <i class="bi bi-clock"></i>
                        <span id="horas-info-texto-editar">Horas usadas esta semana: 0 / 0 — Disponibles: 0</span>
                    </div>

                    <!-- Campos edición -->
                    <div class="form-grid">
                        <div>
                            <label class="form-label fw-semibold" style="font-size:13px;">Curso</label>
                            <input type="text" id="editar-curso-input" class="form-control form-control-sm" readonly>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" style="font-size:13px;">Materia</label>
                            <input type="text" id="editar-materia-input" class="form-control form-control-sm" readonly>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" style="font-size:13px;">Fecha</label>
                            <input type="text" id="editar-fecha-input" class="form-control form-control-sm" readonly>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" style="font-size:13px;">Horas de clase</label>
                            <input type="number" id="editar-cantidad-horas" class="form-control form-control-sm"
                                   min="1" max="20" value="1">
                        </div>
                    </div>

                    <!-- Leyenda -->
                    <div class="legend-bar">
                        <strong style="color:#495057;">Leyenda:</strong>
                        <span><span class="legend-dot" style="background:#198754;"></span>= Presente</span>
                        <span><span class="legend-dot" style="background:#dc3545;"></span>= Ausente</span>
                    </div>

                    <!-- Tabla edición -->
                    <div id="div-alumnos-editar">

                        <!-- Toolbar seleccionar todos edición -->
                        <div class="select-all-bar">
                            <span class="bar-label"><i class="bi bi-person-check me-1"></i> Selección rápida:</span>
                            <button type="button" class="btn-select-all presentes" onclick="seleccionarTodos('editar', true)">
                                <i class="bi bi-check-all"></i> Todos Presentes
                            </button>
                            <button type="button" class="btn-select-all ausentes" onclick="seleccionarTodos('editar', false)">
                                <i class="bi bi-x-lg"></i> Todos Ausentes
                            </button>
                        </div>

                        <table id="tabla-alumnos-editar" class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th style="width:100px;">Cédula</th>
                                    <th>Apellidos y Nombres</th>
                                    <th style="width:140px; text-align:center;">Asistencia</th>
                                    <th style="width:100px; text-align:center;">Estado</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-alumnos-editar"></tbody>
                        </table>
                        <div class="text-end mt-3">
                            <button type="button" id="btn-guardar-editar" class="btn btn-warning btn-sm px-4">
                                <i class="bi bi-save me-1"></i> Actualizar Asistencia
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
// ═══════════════════════════════════════════════════════════════════════════════
// ESTADO GLOBAL
// ═══════════════════════════════════════════════════════════════════════════════
const attendanceState = {
    // nueva
    idAulaMateria      : null,
    horasSemanales     : 0,
    horasUsadas        : 0,   // ya registradas esta semana (sin la sesión actual)
    fechaValida        : false,
    // edición
    editingSesionId       : null,
    editingFecha          : '',
    editingAulaMateria    : null,
    editingHorasSemanales : 0,
    editingHorasUsadas    : 0,
    editingFechaValida    : false,
};

let fechaValidationTimer = null; // debounce para validación de fecha

// ═══════════════════════════════════════════════════════════════════════════════
// UTILIDADES
// ═══════════════════════════════════════════════════════════════════════════════

function escapeHtml(text) {
    return String(text || '').replace(/[&<>"'`]/g, function(c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;'}[c];
    });
}

function getFechaHoy()      { return new Date().toISOString().split('T')[0]; }
function getFechaInicioMes(){ const h=new Date(); return new Date(h.getFullYear(),h.getMonth(),1).toISOString().split('T')[0]; }

function setButtonLoading(selector, loading, label) {
    const $b = $(selector);
    if (loading) {
        $b.prop('disabled', true).data('orig', $b.html())
          .html(`<span class="spinner-border spinner-border-sm"></span> ${label}`);
    } else {
        $b.prop('disabled', false);
        if ($b.data('orig')) $b.html($b.data('orig'));
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// HORAS — texto plano informativo (no bloquea nada por sí solo)
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * @param {string} modo        'nueva' | 'editar'
 * @param {number} usadas      horas ya registradas esta semana (sin contar la sesión actual)
 * @param {number} semanales   total semanal de la materia
 * @param {number} sesionHoras horas ingresadas en el input de la sesión actual
 */
function actualizarInfoHoras(modo, usadas, semanales, sesionHoras) {
    const sufijo = modo === 'editar' ? '-editar' : '';
    const $cont  = $(`#horas-info${sufijo}`);
    const $texto = $(`#horas-info-texto${sufijo}`);

    if (semanales <= 0) { $cont.addClass('d-none'); return; }

    const total       = usadas + (sesionHoras || 0);
    const disponibles = Math.max(0, semanales - usadas);

    $cont.removeClass('d-none');
    $texto.html(`Horas usadas esta semana: <strong>${total} / ${semanales}</strong> — Disponibles: <strong>${Math.max(0, semanales - total)}</strong>`);

    $cont.toggleClass('exceso', total > semanales);
}

// ═══════════════════════════════════════════════════════════════════════════════
// VALIDACIÓN DE FECHA (informativa + bloqueo de guardado, NO bloquea carga de alumnos)
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * @param {string} modo            'nueva' | 'editar'
 * @param {string} fecha           YYYY-MM-DD
 * @param {number} idAulaMateria
 * @param {number} idSesionEditar  0 = nueva, >0 = edición
 * @returns {Promise<object|null>} data del endpoint o null si hubo error/rechazo
 */
function validarFecha(modo, fecha, idAulaMateria, idSesionEditar = 0) {
    const sufijo    = modo === 'editar' ? '-editar' : '-nueva';
    const $feedback = $(`#fecha-feedback${sufijo}`);

    $feedback.attr('class','fecha-feedback loading').text('Verificando fecha...');

    return fetch(`api/validar_fecha_asistencia.php?idAulaMateria=${idAulaMateria}&fecha=${fecha}&idSesion=${idSesionEditar}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                $feedback.attr('class','fecha-feedback error').text(res.message);
                if (modo === 'nueva') attendanceState.fechaValida = false;
                else                  attendanceState.editingFechaValida = false;
                return null;
            }

            const d = res.data;

            if (modo === 'nueva') {
                attendanceState.horasUsadas = d.horas_semana.usadas;
            } else {
                attendanceState.editingHorasUsadas = d.horas_semana.usadas;
            }

            // Aplicar min/max al input de fecha (solo aplica en modo nueva; en editar es readonly)
            if (modo === 'nueva') {
                $('#fecha-asistencia').attr('min', d.limites_input.min).attr('max', d.limites_input.max);
            }

            if (d.ya_existe) {
                $feedback.attr('class','fecha-feedback error')
                         .text(`Ya hay asistencia registrada para esta fecha (sesión #${d.idSesion_exist})`);
                if (modo === 'nueva') attendanceState.fechaValida = false;
                else                  attendanceState.editingFechaValida = false;
                return d; // se devuelve igual para que el caller ofrezca ir a editar
            }

            if (modo === 'nueva') attendanceState.fechaValida = true;
            else                  attendanceState.editingFechaValida = true;

const semana = d.horas_semana;

// ── Mostrar mensaje base ──
let mensaje = `Período: ${d.periodo.nombre} · ${semana.usadas}h usadas esta semana, ${semana.disponibles}h disponibles`;

// ── Agregar advertencia de fin de semana si existe ──
if (d.advertencia) {
    mensaje = ` ${d.advertencia} · ${mensaje}`;
    $feedback.attr('class','fecha-feedback warning');
} else {
    $feedback.attr('class','fecha-feedback ok');
}

$feedback.text(mensaje);

            return d;
        })
        .catch(() => {
            $feedback.attr('class','fecha-feedback error').text('Error al verificar la fecha');
            if (modo === 'nueva') attendanceState.fechaValida = false;
            else                  attendanceState.editingFechaValida = false;
            return null;
        });
}

// ═══════════════════════════════════════════════════════════════════════════════
// RENDER TABLA DE ALUMNOS
// ═══════════════════════════════════════════════════════════════════════════════

function renderAttendanceTable(alumnos, tableSelector, tbodySelector, switchClass, badgePrefix) {
    let rows = '';
    alumnos.forEach(function(alumno, index) {
        const idMatricula = alumno.idMatricula || 0;
        const estado      = String(alumno.estado || 'Ausente');
        const observacion = alumno.observacion || '';
        const isPresente   = estado === 'Presente';
        let badgeClass     = 'badge-ausente';
        if      (estado === 'Presente')    badgeClass = 'badge-presente';
        else if (estado === 'Tardanza')    badgeClass = 'badge-tardanza';
        else if (estado === 'Justificado') badgeClass = 'badge-justif';

        const obsClass = switchClass === 'estado-switch' ? 'observacion-input' : 'observacion-input-editar';

        rows += `
            <tr>
                <td class="text-muted">${index + 1}</td>
                <td class="text-muted" style="font-size:12px;">${escapeHtml(alumno.cedula || '-')}</td>
                <td>${escapeHtml(alumno.apellidos)} ${escapeHtml(alumno.nombres)}</td>
                <td style="text-align:center;">
                    <label class="asist-switch">
                        <input class="${switchClass}" type="checkbox" data-idmatricula="${idMatricula}" ${isPresente ? 'checked' : ''}>
                        <span class="asist-slider"></span>
                    </label>
                </td>
                <td style="text-align:center;">
                    <span class="badge ${badgeClass}" id="${badgePrefix}${idMatricula}">${escapeHtml(estado)}</span>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm ${obsClass}"
                           placeholder="Observación" value="${escapeHtml(observacion)}"
                           data-idmatricula="${idMatricula}">
                </td>
            </tr>`;
    });

    $(tbodySelector).html(rows);

    if ($.fn.DataTable.isDataTable(tableSelector)) {
        $(tableSelector).DataTable().destroy();
    }
    $(tableSelector).DataTable({
        language: { url: '../dt/es-ES.json' },
        paging: false, searching: true, ordering: true, info: true, destroy: true
    });

    // Evento switch individual
    $(`${tableSelector} .${switchClass}`).off('change').on('change', function() {
        const idM     = $(this).data('idmatricula');
        const checked = $(this).is(':checked');
        const $badge  = $(`#${badgePrefix}${idM}`);
        if (checked) $badge.removeClass('badge-ausente badge-tardanza badge-justif').addClass('badge-presente').text('Presente');
        else         $badge.removeClass('badge-presente badge-tardanza badge-justif').addClass('badge-ausente').text('Ausente');
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
// SWITCH SELECCIONAR TODOS
// ═══════════════════════════════════════════════════════════════════════════════

function seleccionarTodos(modo, presente) {
    const switchClass   = modo === 'nueva' ? '.estado-switch' : '.estado-switch-editar';
    const tableSelector  = modo === 'nueva' ? '#tabla-alumnos' : '#tabla-alumnos-editar';

    $(`${tableSelector} ${switchClass}`).each(function() {
        $(this).prop('checked', presente).trigger('change');
    });

    alertify.success(presente ? 'Todos los alumnos marcados como Presente' : 'Todos los alumnos marcados como Ausente');
}

// ═══════════════════════════════════════════════════════════════════════════════
// PAYLOAD
// ═══════════════════════════════════════════════════════════════════════════════

function buildAttendancePayload(switchClass, observationClass) {
    const payload = [];
    $(`.${switchClass}`).each(function() {
        payload.push({
            idMatricula : $(this).data('idmatricula'),
            estado      : $(this).is(':checked') ? 'Presente' : 'Ausente',
            observacion : $(this).closest('tr').find(`.${observationClass}`).val() || ''
        });
    });
    return payload;
}

// ═══════════════════════════════════════════════════════════════════════════════
// GUARDAR / EDITAR
// ═══════════════════════════════════════════════════════════════════════════════

function ejecutarGuardarAsistencia(payload) {
    setButtonLoading('#btn-guardar', true, 'Guardando...');
    fetch('api/guardar_asistencia.php', {
        method  : 'POST',
        headers : { 'Content-Type': 'application/json' },
        body    : JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alertify.success(res.message || 'Asistencia guardada correctamente');
            resetNewForm();
            cargarHistorial();
        } else {
            alertify.error(res.message || 'Error al guardar asistencia');
        }
    })
    .catch(() => alertify.error('Error de red al guardar asistencia'))
    .finally(() => setButtonLoading('#btn-guardar', false));
}

function ejecutarEditarAsistencia(payload) {
    setButtonLoading('#btn-guardar-editar', true, 'Actualizando...');
    fetch('api/editar_asistencia.php', {
        method  : 'POST',
        headers : { 'Content-Type': 'application/json' },
        body    : JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alertify.success(res.message || 'Asistencia actualizada correctamente');
            switchTab('historial');
        } else {
            alertify.error(res.message || 'Error al actualizar asistencia');
        }
    })
    .catch(() => alertify.error('Error de red al actualizar asistencia'))
    .finally(() => setButtonLoading('#btn-guardar-editar', false));
}

function intentarGuardar(modo) {
    const esEditar    = modo === 'editar';
    const switchClass = esEditar ? 'estado-switch-editar' : 'estado-switch';
    const obsClass    = esEditar ? 'observacion-input-editar' : 'observacion-input';
    const horasInput  = esEditar ? '#editar-cantidad-horas' : '#cantidad-horas';

    // ── Paso 1: hay alumnos cargados ──
    const asistenciaData = buildAttendancePayload(switchClass, obsClass);
    if (asistenciaData.length === 0) {
        alertify.warning('No hay alumnos para registrar. Seleccione curso y materia primero.');
        return;
    }

    if (esEditar && (!attendanceState.editingAulaMateria || !attendanceState.editingFecha)) {
        alertify.error('Datos de asistencia incompletos'); return;
    }
    if (!esEditar && !attendanceState.idAulaMateria) {
        alertify.warning('Seleccione una materia primero'); return;
    }

    // ── Paso 2: fecha válida — bloqueo duro, sin confirm ──
    if (esEditar && !attendanceState.editingFechaValida) {
        alertify.error('No se puede actualizar: la fecha de esta sesión ya no es válida (fuera de ventana o período).');
        return;
    }
    if (!esEditar && !attendanceState.fechaValida) {
        alertify.error('La fecha seleccionada no es válida. Verifique el mensaje debajo del campo fecha.');
        return;
    }

    const horasStr = String($(horasInput).val() || '').trim();
    if (!/^\d+$/.test(horasStr)) { alertify.warning('Ingrese solo números enteros en horas'); return; }
    const cantidadHoras = parseInt(horasStr, 10);
    if (cantidadHoras < 1) { alertify.warning('La cantidad de horas debe ser mayor a 0'); return; }

    const semanales = esEditar ? attendanceState.editingHorasSemanales : attendanceState.horasSemanales;
    const usadas    = esEditar ? attendanceState.editingHorasUsadas    : attendanceState.horasUsadas;
    const total     = usadas + cantidadHoras;

    const idAulaMateria = esEditar ? attendanceState.editingAulaMateria : attendanceState.idAulaMateria;
    const fecha          = esEditar ? attendanceState.editingFecha : $('#fecha-asistencia').val();

    const payload = {
        idAulaMateria : idAulaMateria,
        fecha         : fecha,
        cantidadHoras : cantidadHoras,
        asistencia    : asistenciaData
    };

    // ── Paso 3: horas excedidas — confirm, el docente decide ──
    if (semanales > 0 && total > semanales) {
        const exceso = total - semanales;
        alertify.confirm(
            'Horas semanales superadas',
            `Esta sesión suma <strong>${total}h</strong> en la semana, superando el límite de <strong>${semanales}h semanales</strong> de la materia (exceso: ${exceso}h).<br><br>¿Desea guardar de todas formas?`,
            function() {
                if (esEditar) ejecutarEditarAsistencia(payload);
                else          ejecutarGuardarAsistencia(payload);
            },
            function() {}
        ).set('labels', { ok: 'Guardar igual', cancel: 'Volver' });
        return;
    }

    // ── Paso 4: todo OK ──
    if (esEditar) ejecutarEditarAsistencia(payload);
    else          ejecutarGuardarAsistencia(payload);
}

// ═══════════════════════════════════════════════════════════════════════════════
// TABS
// ═══════════════════════════════════════════════════════════════════════════════

function switchTab(tab) {
    ['nueva','historial','editar'].forEach(t => {
        document.getElementById('panel-' + t).classList.toggle('active', t === tab);
        document.getElementById('tab-'   + t).classList.toggle('active', t === tab);
    });
    document.getElementById('tab-editar').classList.toggle('d-none', tab !== 'editar');

    if (tab === 'historial') cargarHistorial();
    if (tab === 'nueva')     resetNewForm();
}

function volverAlHistorial() { switchTab('historial'); }

// ═══════════════════════════════════════════════════════════════════════════════
// RESET FORMULARIOS
// ═══════════════════════════════════════════════════════════════════════════════

function resetNewForm() {
    attendanceState.idAulaMateria  = null;
    attendanceState.horasSemanales = 0;
    attendanceState.horasUsadas    = 0;
    attendanceState.fechaValida    = false;

    $('#select-curso').val('').prop('disabled', false);
    $('#select-materia').html('<option value="">-- Seleccione una materia --</option>').prop('disabled', true);
    $('#fecha-asistencia').val(getFechaHoy()).prop('disabled', true).removeAttr('min max');
    $('#cantidad-horas').val(1).prop('disabled', true);
    $('#info-aula').addClass('d-none');
    $('#horas-info').addClass('d-none');
    $('#fecha-feedback-nueva').text('').attr('class','fecha-feedback');
    $('#div-alumnos').addClass('d-none');
    $('#msg-seleccionar').removeClass('d-none');
    if ($.fn.DataTable.isDataTable('#tabla-alumnos')) $('#tabla-alumnos').DataTable().destroy();
    $('#tbody-alumnos').html('');
}

function resetEditForm() {
    attendanceState.editingSesionId       = null;
    attendanceState.editingFecha          = '';
    attendanceState.editingAulaMateria    = null;
    attendanceState.editingHorasSemanales = 0;
    attendanceState.editingHorasUsadas    = 0;
    attendanceState.editingFechaValida    = false;

    $('#editar-curso').text('');
    $('#editar-materia').text('');
    $('#editar-fecha-label').text('');
    $('#editar-curso-input').val('');
    $('#editar-materia-input').val('');
    $('#editar-fecha-input').val('');
    $('#editar-cantidad-horas').val(1);
    $('#horas-info-editar').addClass('d-none');
    $('#fecha-feedback-editar').text('').attr('class','fecha-feedback');
    if ($.fn.DataTable.isDataTable('#tabla-alumnos-editar')) $('#tabla-alumnos-editar').DataTable().destroy();
    $('#tbody-alumnos-editar').html('');
}

// ═══════════════════════════════════════════════════════════════════════════════
// CARGA DE CURSOS Y MATERIAS (sin tocar la lógica/contratos originales)
// ═══════════════════════════════════════════════════════════════════════════════

function cargarCursos(selectId, placeholder) {
    $.ajax({
        url:'api/cursos_docente.php', type:'GET', dataType:'json',
        success: function(res) {
            if (res.success) {
                let opts = `<option value="">${placeholder}</option>`;
                res.data.forEach(c => { opts += `<option value="${c.idAula}">${escapeHtml(c.curso)}</option>`; });
                $(`#${selectId}`).html(opts);
            } else {
                alertify.warning(res.message || 'No hay cursos asignados');
            }
        },
        error: () => alertify.error('Error al cargar cursos')
    });
}

function cargarMaterias(idAula, selectId, placeholder, withData) {
    $.ajax({
        url:'api/materias_docente.php', type:'GET', data:{idAula}, dataType:'json',
        success: function(res) {
            if (res.success && res.data.length > 0) {
                let opts = `<option value="">${placeholder}</option>`;
                res.data.forEach(m => {
                    const extra = withData
                        ? `data-aula="${escapeHtml(m.aula)}" data-materia="${escapeHtml(m.materia)}" data-horas-sem="${m.horas_sem}"`
                        : '';
                    opts += `<option value="${m.idAulaMateria}" ${extra}>${escapeHtml(m.materia)}</option>`;
                });
                $(`#${selectId}`).html(opts).prop('disabled', false);
            } else {
                alertify.warning(res.message || 'No hay materias asignadas en este curso');
                $(`#${selectId}`).html(`<option value="">${placeholder}</option>`).prop('disabled', true);
            }
        },
        error: () => alertify.error('Error al cargar materias')
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
// CARGA DE ALUMNOS (independiente de la validación de fecha)
// ═══════════════════════════════════════════════════════════════════════════════

function cargarAlumnosNueva(idAulaMateria, fecha) {
    $.ajax({
        url:'api/alumnos_asistencia.php', type:'GET', data:{idAulaMateria, fecha}, dataType:'json',
        success: function(res) {
            if (res.success) {
                renderAttendanceTable(res.data, '#tabla-alumnos', '#tbody-alumnos', 'estado-switch', 'badge-');
                $('#div-alumnos').removeClass('d-none');
                $('#msg-seleccionar').addClass('d-none');
            } else {
                alertify.warning(res.message || 'No hay alumnos matriculados');
                $('#div-alumnos').addClass('d-none');
                $('#msg-seleccionar').removeClass('d-none');
            }
        },
        error: () => alertify.error('Error al cargar alumnos')
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
// LÓGICA AL CAMBIAR MATERIA (panel nueva) — carga alumnos directo, sin esperar validación
// ═══════════════════════════════════════════════════════════════════════════════

function onMateriaChange() {
    const $sel          = $('#select-materia');
    const idAulaMateria = $sel.val();
    const selected       = $sel.find('option:selected');

    $('#info-aula').addClass('d-none');
    $('#horas-info').addClass('d-none');
    $('#fecha-feedback-nueva').text('').attr('class','fecha-feedback');
    attendanceState.idAulaMateria = null;
    attendanceState.fechaValida   = false;

    if (!idAulaMateria) {
        $('#fecha-asistencia').prop('disabled', true);
        $('#cantidad-horas').prop('disabled', true);
        $('#div-alumnos').addClass('d-none');
        $('#msg-seleccionar').removeClass('d-none');
        return;
    }

    const aula     = selected.data('aula')    || '';
    const materia  = selected.data('materia') || '';
    const horasSem = parseInt(selected.data('horas-sem') || 0, 10);

    attendanceState.idAulaMateria  = idAulaMateria;
    attendanceState.horasSemanales = horasSem;

    $('#nombre-aula').text(aula);
    $('#nombre-materia').text(materia);
    $('#horas-semanales').text(horasSem + ' horas');
    $('#info-aula').removeClass('d-none');
    $('#fecha-asistencia').prop('disabled', false);
    $('#cantidad-horas').prop('disabled', false).val(1);

    // Si no hay fecha cargada, usar hoy por defecto
    if (!$('#fecha-asistencia').val()) {
        $('#fecha-asistencia').val(getFechaHoy());
    }
    const fecha = $('#fecha-asistencia').val();

    actualizarInfoHoras('nueva', 0, horasSem, parseInt($('#cantidad-horas').val()||1,10));

    // 1) Validar PRIMERO si esa materia+fecha ya tiene asistencia registrada.
    //    Si ya existe, ofrecemos editar en vez de cargar la tabla con datos falsos.
    verificarYCargar(idAulaMateria, fecha);
}

/**
 * Verifica si ya existe asistencia para idAulaMateria+fecha.
 * - Si ya existe: muestra el confirm de "ir a editar" y NO carga la tabla de nueva.
 * - Si no existe: carga la tabla de alumnos para registrar nueva asistencia.
 */
function verificarYCargar(idAulaMateria, fecha) {
    validarFecha('nueva', fecha, idAulaMateria).then(d => {
        if (!d) {
            // Error de validación (sin período, fuera de ventana, etc.) — igual dejamos
            // ver a los alumnos para que el docente no quede bloqueado visualmente,
            // pero no podrá guardar hasta corregir la fecha.
            cargarAlumnosNueva(idAulaMateria, fecha);
            return;
        }

        if (d.ya_existe) {
            $('#div-alumnos').addClass('d-none');
            $('#msg-seleccionar').removeClass('d-none');
            alertify.confirm(
                'Asistencia ya registrada',
                `Ya existe asistencia registrada para la fecha <strong>${fecha}</strong> en esta materia.<br>¿Desea cargarla para editar?`,
                function() { editarAsistencia(idAulaMateria, fecha); },
                function() { resetNewForm(); }
            ).set('labels', { ok: 'Ir a editar', cancel: 'Cancelar' });
            return;
        }

        // No existe sesión todavía: flujo normal de nueva asistencia
        actualizarInfoHoras('nueva', d.horas_semana.usadas, attendanceState.horasSemanales, parseInt($('#cantidad-horas').val()||1,10));
        cargarAlumnosNueva(idAulaMateria, fecha);
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
// EDITAR ASISTENCIA (desde historial)
// ═══════════════════════════════════════════════════════════════════════════════

function editarAsistencia(idAulaMateria, fecha) {
    if (!idAulaMateria || !fecha) { alertify.error('Datos incompletos'); return; }

    switchTab('editar');
    resetEditForm();

    attendanceState.editingAulaMateria = idAulaMateria;
    attendanceState.editingFecha       = fecha;

    fetch(`api/obtener_asistencia.php?idAulaMateria=${idAulaMateria}&fecha=${fecha}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                alertify.error(res.message || 'No se encontró la asistencia');
                switchTab('historial'); return;
            }
            const cab = res.data.cabecera;
            const det = res.data.detalle || [];

            attendanceState.editingSesionId = parseInt(cab.idSesion, 10);

            $('#editar-curso').text(cab.curso);
            $('#editar-materia').text(cab.materia);
            $('#editar-fecha-label').text(cab.fecha);
            $('#editar-curso-input').val(cab.curso);
            $('#editar-materia-input').val(cab.materia);
            $('#editar-fecha-input').val(cab.fecha);
            $('#editar-cantidad-horas').val(cab.cantidad_horas);

            renderAttendanceTable(det, '#tabla-alumnos-editar', '#tbody-alumnos-editar', 'estado-switch-editar', 'badge-editar-');

            // Validar la sesión (ventana retroactiva, período, horas) — solo informativo + habilita guardado
            validarFecha('editar', fecha, idAulaMateria, attendanceState.editingSesionId)
                .then(d => {
                    if (!d) return;
                    attendanceState.editingHorasSemanales = d.horas_semana.semanales;
                    attendanceState.editingHorasUsadas    = d.horas_semana.usadas;
                    actualizarInfoHoras('editar',
                        d.horas_semana.usadas,
                        d.horas_semana.semanales,
                        parseInt(cab.cantidad_horas, 10)
                    );
                });
        })
        .catch(() => { alertify.error('Error al cargar datos de edición'); switchTab('historial'); });
}

// ═══════════════════════════════════════════════════════════════════════════════
// HISTORIAL
// ═══════════════════════════════════════════════════════════════════════════════
let historialCargando = false;

function cargarHistorial() {

    if (historialCargando) return;
    historialCargando = true;

    const idAula        = $('#historial-curso').val();
    const idAulaMateria = $('#historial-materia').val();
    const desde         = $('#historial-desde').val();
    const hasta         = $('#historial-hasta').val();

    if (desde && hasta && desde > hasta) {
        alertify.warning('La fecha "Desde" no puede ser mayor que "Hasta"'); return;
    }

    $.ajax({
        url:'api/historial_asistencias.php', type:'GET',
        data:{idAula, idAulaMateria, desde, hasta}, dataType:'json',
        success: function(res) {
            if ($.fn.DataTable.isDataTable('#tabla-historial')) {
                $('#tabla-historial').DataTable().clear().destroy();
            }

            let tbody = '';
            if (res.success && Array.isArray(res.data) && res.data.length > 0) {
                res.data.forEach(item => {
                    tbody += `
                        <tr>
                            <td>${escapeHtml(item.fecha)}</td>
                            <td>${escapeHtml(item.curso)}</td>
                            <td>${escapeHtml(item.materia)}</td>
                            <td style="text-align:center;">${item.cantidad_horas}</td>
                            <td style="text-align:center;">${item.total_alumnos}</td>
                            <td style="text-align:center;"><span class="badge badge-presente">${item.presentes}</span></td>
                            <td style="text-align:center;"><span class="badge badge-ausente">${item.ausentes}</span></td>
                            <td style="text-align:center;">
                                <button class="btn btn-sm btn-outline-primary btn-editar py-0 px-2"
                                        data-idaulamateria="${item.idAulaMateria}"
                                        data-fecha="${item.fecha}" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>`;
                });
            } else {
                tbody = '';
            }

            $('#tbody-historial').html(tbody);

            historialCargando = false;
            

            $('#tabla-historial').DataTable({
                language: { url: '../dt/es-ES.json' },
                paging: true, searching: true, ordering: true, info: true
            });
        },
        error: () => alertify.error('Error al cargar historial')
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
// DOCUMENT READY
// ═══════════════════════════════════════════════════════════════════════════════

$(document).ready(function() {

    // Cargar selects iniciales
    cargarCursos('select-curso', '-- Seleccione un curso --');
    cargarCursos('historial-curso', '-- Todos los cursos --');

    // ── Panel nueva: cascada curso → materia ──
    $('#select-curso').change(function() {
        const idAula = $(this).val();
        resetNewForm();
        $('#select-curso').val(idAula); // resetNewForm limpia el select, lo reponemos
        $('#select-materia').html('<option value="">-- Seleccione una materia --</option>').prop('disabled', !idAula);
        if (idAula) cargarMaterias(idAula, 'select-materia', '-- Seleccione una materia --', true);
    });

    $('#select-materia').change(onMateriaChange);

    // ── Cambio de fecha nueva (con debounce) — revalida y decide si carga tabla o pide editar ──
    $('#fecha-asistencia').on('change', function() {
        const fecha          = $(this).val();
        const idAulaMateria  = attendanceState.idAulaMateria;
        if (!idAulaMateria || !fecha) return;

        clearTimeout(fechaValidationTimer);
        attendanceState.fechaValida = false;

        // Ocultar tabla mientras se revalida, para no mostrar datos de la fecha anterior
        $('#div-alumnos').addClass('d-none');
        $('#msg-seleccionar').removeClass('d-none');

        fechaValidationTimer = setTimeout(() => {
            verificarYCargar(idAulaMateria, fecha);
        }, 350);
    });

    // ── Cambio horas sesión nueva → actualiza texto informativo ──
    $('#cantidad-horas').on('input change', function() {
        const h = parseInt($(this).val() || 0, 10);
        actualizarInfoHoras('nueva', attendanceState.horasUsadas, attendanceState.horasSemanales, h);
    });

    // ── Cambio horas sesión edición → actualiza texto informativo ──
    $('#editar-cantidad-horas').on('input change', function() {
        const h = parseInt($(this).val() || 0, 10);
        actualizarInfoHoras('editar', attendanceState.editingHorasUsadas, attendanceState.editingHorasSemanales, h);
    });

    // ── Botones guardar ──
    $('#btn-guardar').click(function()        { intentarGuardar('nueva');  });
    $('#btn-guardar-editar').click(function() { intentarGuardar('editar'); });
    $('#btn-cancelar').click(function()       { resetNewForm(); });

    // ── Historial: filtros ──
    $('#historial-curso').change(function() {
        const idAula = $(this).val();
        $('#historial-materia').html('<option value="">-- Todas las materias --</option>').prop('disabled', !idAula);
        if (idAula) cargarMaterias(idAula, 'historial-materia', '-- Todas las materias --', false);
        cargarHistorial();
    });

    $('#historial-materia').change(cargarHistorial);
    $('#historial-desde').change(cargarHistorial);
    $('#historial-hasta').change(cargarHistorial);

    $('#historial-materia, #historial-desde, #historial-hasta').change(function() {
        cargarHistorial();
    });

    $('#btn-limpiar-historial').click(function() {
        $('#historial-curso').val('');
        $('#historial-materia').html('<option value="">-- Todas las materias --</option>').prop('disabled', true);
        $('#historial-desde').val(getFechaInicioMes());
        $('#historial-hasta').val(getFechaHoy());
        cargarHistorial();
    });

    // ── Historial: botón editar ──
    $('#tabla-historial').on('click', '.btn-editar', function() {
        editarAsistencia($(this).data('idaulamateria'), $(this).data('fecha'));
    });

    // Estado inicial del input fecha
    $('#fecha-asistencia').val(getFechaHoy());

    // Carga inicial del historial
    cargarHistorial();
});
</script>
