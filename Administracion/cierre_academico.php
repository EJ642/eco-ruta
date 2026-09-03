<?php
$ruta = '../';
include __DIR__ . '/includes/header.php';
?>

<script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>

<style>
.main-content { display: block; }
.page-title { margin-bottom: 22px; }
.page-title h2 { font-size: 1.35rem; font-weight: 700; margin: 0; color: #111827; }
.page-title p  { margin: 4px 0 0; color: #6b7280; font-size: 13px; }

/* Cards de semestre */
.semestres-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}
.sem-card {
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
}
.sem-card.abierto  { border-color: #fbbf24; }
.sem-card.cerrado  { border-color: #10b981; }
.sem-card h5 { margin: 0 0 4px; font-size: 15px; font-weight: 700; color: #111827; }
.sem-card p  { margin: 0 0 14px; font-size: 12px; color: #6b7280; }

.sem-stat { display: flex; gap: 16px; margin-bottom: 14px; flex-wrap: wrap; }
.sem-stat-item { font-size: 12px; color: #374151; }
.sem-stat-item strong { display: block; font-size: 18px; font-weight: 700; color: #111827; }

/* Estado badge */
.badge-abierto  { background: #fef3c7; color: #92400e; border-radius:999px; padding:3px 10px; font-size:11px; font-weight:700; }
.badge-cerrado  { background: #ecfdf5; color: #047857; border-radius:999px; padding:3px 10px; font-size:11px; font-weight:700; }

/* Card cierre de año */
.anio-card {
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}
.anio-card.bloqueado { border-color: #e5e7eb; opacity: .7; }
.anio-card.listo     { border-color: #4f46e5; }
.anio-card h5 { margin: 0 0 6px; font-size: 15px; font-weight: 700; color: #111827; }
.anio-card p  { margin: 0 0 14px; font-size: 13px; color: #6b7280; }

.checks-list { list-style: none; padding: 0; margin: 0 0 16px; }
.checks-list li {
    font-size: 13px;
    padding: 6px 0;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #374151;
}
.checks-list li:last-child { border-bottom: 0; }
.check-ok   { color: #10b981; font-size: 15px; }
.check-fail { color: #ef4444; font-size: 15px; }
.check-wait { color: #9ca3af; font-size: 15px; }

/* Resultado */
.resultado-box {
    display: none;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 16px;
    margin-top: 14px;
}
.resultado-box.error {
    background: #fef2f2;
    border-color: #fecaca;
}
.resultado-stat {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    margin-top: 10px;
}
.resultado-stat-item { font-size: 13px; color: #374151; }
.resultado-stat-item strong { display: block; font-size: 22px; font-weight: 700; }
.resultado-stat-item strong.verde  { color: #047857; }
.resultado-stat-item strong.rojo   { color: #b91c1c; }

#spinner-page { display:none; text-align:center; padding:40px 0; color:#6b7280; }

/* Checklist */
.chk-completo  { background: #ecfdf5; color: #047857; border-radius:999px; padding:3px 9px; font-size:11px; font-weight:700; }
.chk-pendiente { background: #fef2f2; color: #b91c1c; border-radius:999px; padding:3px 9px; font-size:11px; font-weight:700; }
.chk-excepcion { background: #eef2ff; color: #4338ca; border-radius:999px; padding:3px 9px; font-size:11px; font-weight:700; }
.alert-resumen-ok   { background:#ecfdf5; border:1px solid #bbf7d0; border-radius:8px; padding:10px 14px; font-size:13px; color:#047857; }
.alert-resumen-warn { background:#fef3c7; border:1px solid #fde68a; border-radius:8px; padding:10px 14px; font-size:13px; color:#92400e; }

/* ── Grid de cursos (vista intermedia del checklist) ── */
.cursos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
}
.curso-card {
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 16px;
    cursor: pointer;
    transition: border-color .15s, transform .1s;
}
.curso-card:hover { transform: translateY(-1px); }
.curso-card.completo  { border-color: #10b981; }
.curso-card.pendiente { border-color: #f59e0b; }
.curso-card h6 { margin: 0 0 8px; font-size: 13.5px; font-weight: 700; color: #111827; }
.curso-card .curso-resumen { font-size: 12px; color: #6b7280; display:flex; align-items:center; gap:6px; }
.curso-card .curso-resumen i { font-size: 14px; }
.curso-card.completo .curso-resumen  { color: #047857; }
.curso-card.pendiente .curso-resumen { color: #b45309; }

/* Botón volver dentro del modal, en la vista de detalle */
#btn-volver-cursos { display:none; }
#checklist-breadcrumb { font-size: 12px; color: #6b7280; margin-bottom: 10px; }
#checklist-breadcrumb a { color: #4f46e5; text-decoration: none; cursor: pointer; }
#checklist-breadcrumb a:hover { text-decoration: underline; }
</style>

<div class="main-content">

    <div class="page-title">
        <h2><i class="bi bi-calendar-check me-2 text-primary"></i>Cierre Académico</h2>
        <p>Gestioná el cierre de semestres y el cierre final del año lectivo.</p>
    </div>

    <div id="spinner-page">
        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
        <span class="ms-2">Cargando estado del año...</span>
    </div>

    <div id="contenido" style="display:none;">

        <!-- Info año -->
        <div class="mb-3">
            <span class="fw-bold" style="font-size:14px;">Año lectivo activo: </span>
            <span id="txt-anio" class="badge bg-dark" style="font-size:13px;"></span>
        </div>

        <!-- Semestres -->
        <h6 class="fw-bold text-uppercase" style="font-size:11px;color:#6b7280;letter-spacing:.05em;margin-bottom:12px;">
            Semestres
        </h6>
        <div class="semestres-grid" id="grid-semestres"></div>

        <!-- Cierre de año -->
        <h6 class="fw-bold text-uppercase" style="font-size:11px;color:#6b7280;letter-spacing:.05em;margin-bottom:12px;">
            Cierre del Año Lectivo
        </h6>
        <div class="anio-card" id="card-anio">
            <h5><i class="bi bi-lock me-2"></i>Cerrar Año Lectivo</h5>
            <p>Una vez cerrado el año, el estado de todas las matrículas será actualizado a Promovido o Reprobado según las notas finales.</p>
            <ul class="checks-list" id="checks-anio"></ul>
            <button id="btn-cerrar-anio" class="btn btn-danger" disabled>
                <i class="bi bi-lock-fill me-2"></i>Cerrar Año Lectivo
            </button>
            <div class="resultado-box" id="resultado-anio"></div>
        </div>

    </div>

    <!-- Modal Checklist -->
    <div class="modal fade" id="modalChecklist" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checklist-titulo">Checklist</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Vista 1: grid de cursos/énfasis -->
                    <div id="vista-cursos">
                        <div id="checklist-resumen" class="mb-3"></div>
                        <div class="cursos-grid" id="grid-cursos"></div>
                    </div>

                    <!-- Vista 2: detalle de materias de un curso específico -->
                    <div id="vista-detalle" style="display:none;">
                        <div id="checklist-breadcrumb">
                            <a id="btn-volver-cursos-link"><i class="bi bi-arrow-left me-1"></i>Volver a cursos</a>
                            &nbsp;/&nbsp;<strong id="detalle-curso-nombre"></strong>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="tabla-checklist">
                                <thead class="table-light">
                                    <tr>
                                        <th>Materia</th>
                                        <th>Docente</th>
                                        <th class="text-center">Notas cargadas</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-checklist"></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
let estadoGlobal = null;

function showMessage(type, text) {
    if (typeof alertify !== 'undefined') {
        alertify[type === 'success' ? 'success' : type === 'error' ? 'error' : 'warning'](text);
    } else { alert(text); }
}

// ── Cargar estado ─────────────────────────────────────────
function cargarEstado() {
    $('#spinner-page').show();
    $('#contenido').hide();

    fetch('api/get_estado_cierre.php')
        .then(r => r.json())
        .then(data => {
            $('#spinner-page').hide();
            if (!data.success) { showMessage('error', data.message); return; }
            estadoGlobal = data.data;
            renderEstado(data.data);
            $('#contenido').show();
        })
        .catch(() => { $('#spinner-page').hide(); showMessage('error', 'Error al cargar estado'); });
}

// ── Render ────────────────────────────────────────────────
function renderEstado(data) {
    $('#txt-anio').text('Año ' + data.anio.anio);

    // Semestres
    let htmlSem = '';
    data.periodos.forEach(per => {
        const cls    = per.cerrado ? 'cerrado' : 'abierto';
        const badge  = per.cerrado
            ? '<span class="badge-cerrado"><i class="bi bi-check-circle-fill me-1"></i>Cerrado</span>'
            : '<span class="badge-abierto"><i class="bi bi-circle me-1"></i>Abierto</span>';

        const btnCerrar = !per.cerrado && !data.anioCerrado
            ? `<button class="btn btn-sm btn-dark btn-cerrar-sem" data-id="${per.idPeriodo}" data-nombre="${per.nombre}">
                <i class="bi bi-lock me-1"></i>Cerrar Semestre
               </button>`
            : '';
        const btnAbrir = per.cerrado && !data.anioCerrado
            ? `<button class="btn btn-sm btn-outline-warning ms-2 btn-abrir-sem" data-id="${per.idPeriodo}" data-nombre="${per.nombre}">
                <i class="bi bi-unlock me-1"></i>Reabrir
               </button>`
            : '';

        htmlSem += `
        <div class="sem-card ${cls}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5>${per.nombre}</h5>
                ${badge}
            </div>
            <div class="sem-stat">
                <div class="sem-stat-item">
                    <strong>${per.totalEval}</strong>Evaluaciones
                </div>
                <div class="sem-stat-item">
                    <strong>${per.totalNotas}</strong>Notas cargadas
                </div>
            </div>
            <div>${btnCerrar}${btnAbrir}
                <button class="btn btn-sm btn-outline-secondary ms-2 btn-checklist" data-id="${per.idPeriodo}" data-nombre="${per.nombre}">
                    <i class="bi bi-list-check me-1"></i>Checklist
                </button>
            </div>
        </div>`;
    });
    $('#grid-semestres').html(htmlSem);

    // Checks para cierre de año
    const sem1Cerrado = data.periodos.find(p => p.numero === 1)?.cerrado ?? false;
    const sem2Cerrado = data.periodos.find(p => p.numero === 2)?.cerrado ?? false;
    const sinRecPend  = data.recPendientes === 0;

    const iconOk   = '<i class="bi bi-check-circle-fill check-ok"></i>';
    const iconFail = '<i class="bi bi-x-circle-fill check-fail"></i>';
    const iconWait = '<i class="bi bi-dash-circle check-wait"></i>';

    let htmlChecks = `
        <li>${sem1Cerrado ? iconOk : iconFail} 1° Semestre cerrado</li>
        <li>${sem2Cerrado ? iconOk : iconFail} 2° Semestre cerrado</li>
        <li>${!sem2Cerrado ? iconWait : (sinRecPend ? iconOk : iconFail)}
            ${data.recPendientes > 0 ? `${data.recPendientes} recuperatorio(s) pendiente(s)` : 'Sin recuperatorios pendientes'}
        </li>`;
    $('#checks-anio').html(htmlChecks);

    // Habilitar botón cierre de año
    const puedesCerrar = sem1Cerrado && sem2Cerrado && sinRecPend && !data.anioCerrado;
    $('#btn-cerrar-anio').prop('disabled', !puedesCerrar);

    if (data.anioCerrado) {
        $('#card-anio').addClass('cerrado').removeClass('bloqueado listo');
        $('#btn-cerrar-anio').hide();
        $('#card-anio h5').html('<i class="bi bi-check-circle-fill text-success me-2"></i>Año lectivo cerrado');
    } else if (puedesCerrar) {
        $('#card-anio').addClass('listo').removeClass('bloqueado');
    }
}

// ── Cerrar semestre ───────────────────────────────────────
$(document).on('click', '.btn-cerrar-sem', function () {
    const idPeriodo = $(this).data('id');
    const nombre    = $(this).data('nombre');

    alertify.confirm(
        'Cerrar Semestre',
        `¿Confirma el cierre del <strong>${nombre}</strong>?<br>
        <small class="text-muted">Los docentes no podrán editar notas de este período.</small>`,
        function () { ejecutarAccionSemestre(idPeriodo, 'cerrar'); },
        null
    ).set('labels', { ok: 'Cerrar', cancel: 'Cancelar' });
});

// ── Reabrir semestre ──────────────────────────────────────
$(document).on('click', '.btn-abrir-sem', function () {
    const idPeriodo = $(this).data('id');
    const nombre    = $(this).data('nombre');

    alertify.confirm(
        'Reabrir Semestre',
        `¿Confirma la reapertura del <strong>${nombre}</strong>?`,
        function () { ejecutarAccionSemestre(idPeriodo, 'abrir'); },
        null
    ).set('labels', { ok: 'Reabrir', cancel: 'Cancelar' });
});

function ejecutarAccionSemestre(idPeriodo, accion) {
    fetch('api/cerrar_semestre.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idPeriodo, accion })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { showMessage('error', data.message); return; }
        showMessage('success', data.message);
        setTimeout(cargarEstado, 600);
    })
    .catch(() => showMessage('error', 'Error al procesar la acción'));
}

// ── Cerrar año ────────────────────────────────────────────
$('#btn-cerrar-anio').on('click', function () {
    alertify.confirm(
        'Cerrar Año Lectivo',
        `<strong>¿Confirma el cierre del año lectivo?</strong><br>
        <small class="text-danger">Esta acción actualizará el estado de todas las matrículas y no puede deshacerse.</small>`,
        function () {
            $('#btn-cerrar-anio').prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span>Procesando...');

            fetch('api/cerrar_anio.php', { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    $('#btn-cerrar-anio')
                        .prop('disabled', true)
                        .html('<i class="bi bi-lock-fill me-2"></i>Cerrar Año Lectivo');

                    const box = $('#resultado-anio');
                    if (!data.success) {
                        box.addClass('error').removeClass('').show()
                           .html(`<strong>Error:</strong> ${data.message}`);
                        return;
                    }

                    box.removeClass('error').show().html(`
                        <strong><i class="bi bi-check-circle-fill text-success me-1"></i>Año cerrado correctamente</strong>
                        <div class="resultado-stat">
                            <div class="resultado-stat-item">
                                <strong class="verde">${data.data.promovidos}</strong>Promovidos
                            </div>
                            <div class="resultado-stat-item">
                                <strong class="rojo">${data.data.reprobados}</strong>Reprobados
                            </div>
                            <div class="resultado-stat-item">
                                <strong>${data.data.total}</strong>Total procesados
                            </div>
                        </div>
                    `);
                    showMessage('success', data.message);
                    setTimeout(cargarEstado, 1200);
                })
                .catch(() => {
                    $('#btn-cerrar-anio').prop('disabled', false)
                        .html('<i class="bi bi-lock-fill me-2"></i>Cerrar Año Lectivo');
                    showMessage('error', 'Error al cerrar el año');
                });
        },
        null
    ).set('labels', { ok: 'Confirmar cierre', cancel: 'Cancelar' });
});

document.addEventListener('DOMContentLoaded', cargarEstado);

// ════════════════════════════════════════════════════════
// CHECKLIST POR PERÍODO — vista por curso/énfasis + detalle
// ════════════════════════════════════════════════════════
let checklistPeriodoActual = null;
let checklistDataActual    = null; // se guarda la respuesta completa para no re-pedir al volver

$(document).on('click', '.btn-checklist', function () {
    const idPeriodo = $(this).data('id');
    const nombre    = $(this).data('nombre');
    checklistPeriodoActual = idPeriodo;

    $('#checklist-titulo').text(`Checklist — ${nombre}`);
    mostrarVistaCursos();
    $('#grid-cursos').html('<p class="text-muted text-center py-3 mb-0">Cargando...</p>');

    const modal = new bootstrap.Modal(document.getElementById('modalChecklist'));
    modal.show();

    cargarChecklist(idPeriodo);
});

function cargarChecklist(idPeriodo) {
    fetch(`api/checklist_periodo.php?idPeriodo=${idPeriodo}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                $('#grid-cursos').html(`<p class="text-danger text-center py-3 mb-0">${data.message}</p>`);
                return;
            }
            checklistDataActual = data.data;
            renderResumenGeneral(data.data);
            renderCursos(data.data);
        })
        .catch(() => {
            $('#grid-cursos').html('<p class="text-danger text-center py-3 mb-0">Error al cargar checklist</p>');
        });
}

// ── Navegación entre las dos vistas del modal ──
function mostrarVistaCursos() {
    $('#vista-cursos').show();
    $('#vista-detalle').hide();
}
function mostrarVistaDetalle() {
    $('#vista-cursos').hide();
    $('#vista-detalle').show();
}
$(document).on('click', '#btn-volver-cursos-link', function () {
    mostrarVistaCursos();
});

// ── Resumen general (arriba del grid de cursos) ──
function renderResumenGeneral(data) {
    const periodoCerrado = data.periodo.activo === 'No';
    if (data.pendientes === 0) {
        $('#checklist-resumen').html(`
            <div class="alert-resumen-ok">
                <i class="bi bi-check-circle-fill me-1"></i>
                Todas las materias tienen sus notas completas para este período.
            </div>`);
    } else {
        $('#checklist-resumen').html(`
            <div class="alert-resumen-warn">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                ${data.pendientes} materia(s) con notas incompletas en total.
                ${periodoCerrado ? 'Entrá a cada curso para reabrir individualmente las materias que falten.' : ''}
            </div>`);
    }
}

// ── Vista 1: grid de cursos/énfasis con resumen de completitud ──
function renderCursos(data) {
    if (!data.cursos || data.cursos.length === 0) {
        $('#grid-cursos').html('<p class="text-muted text-center py-3 mb-0">No hay cursos con materias asignadas.</p>');
        return;
    }

    let html = '';
    data.cursos.forEach(curso => {
        const cls = curso.todoCompleto ? 'completo' : 'pendiente';
        const icono = curso.todoCompleto
            ? '<i class="bi bi-check-circle-fill"></i>'
            : '<i class="bi bi-exclamation-triangle-fill"></i>';

        let resumenTexto = curso.todoCompleto
            ? 'Todo completo'
            : `${curso.pendientes} pendiente(s)`;
        if (curso.conExcepcion > 0) {
            resumenTexto += ` · ${curso.conExcepcion} reabierta(s)`;
        }

        html += `
        <div class="curso-card ${cls}" data-idaula="${curso.idAula}">
            <h6>${curso.curso}</h6>
            <div class="curso-resumen">
                ${icono}
                <span>${resumenTexto} (${curso.completas}/${curso.totalMaterias} materias)</span>
            </div>
        </div>`;
    });

    $('#grid-cursos').html(html);
}

// Guarda qué curso está abierto en la vista de detalle, para volver a él tras una acción
let checklistIdAulaActual = null;

// Click en una card de curso → abre el detalle de materias de ese curso
$(document).on('click', '.curso-card', function () {
    const idAula = parseInt($(this).data('idaula'), 10);
    abrirDetalleCurso(idAula);
});

function abrirDetalleCurso(idAula) {
    if (!checklistDataActual) return;
    checklistIdAulaActual = idAula;

    const curso = checklistDataActual.cursos.find(c => c.idAula === idAula);
    const materiasCurso = checklistDataActual.materias.filter(m => m.idAula === idAula);

    $('#detalle-curso-nombre').text(curso ? curso.curso : '');
    renderDetalleMaterias(materiasCurso, checklistDataActual.periodo.activo === 'No');
    mostrarVistaDetalle();
}

// ── Vista 2: detalle de materias de un curso específico ──
function renderDetalleMaterias(materias, periodoCerrado) {
    let html = '';
    materias.forEach(mat => {
        const estadoBadge = mat.tieneExcepcion
            ? '<span class="chk-excepcion"><i class="bi bi-unlock-fill me-1"></i>Reabierta</span>'
            : mat.completo
                ? '<span class="chk-completo"><i class="bi bi-check-circle-fill me-1"></i>Completo</span>'
                : '<span class="chk-pendiente"><i class="bi bi-x-circle-fill me-1"></i>Incompleto</span>';

        let accion = '<span class="text-muted">-</span>';
        if (periodoCerrado) {
            if (mat.tieneExcepcion) {
                accion = `<button class="btn btn-sm btn-outline-danger btn-quitar-excepcion"
                            data-idaulamateria="${mat.idAulaMateria}">
                            <i class="bi bi-lock me-1"></i>Cerrar
                          </button>`;
            } else {
                accion = `<button class="btn btn-sm btn-outline-primary btn-crear-excepcion"
                            data-idaulamateria="${mat.idAulaMateria}" data-materia="${mat.materia}">
                            <i class="bi bi-unlock me-1"></i>Reabrir
                          </button>`;
            }
        }

        html += `
        <tr>
            <td>${mat.materia}</td>
            <td>${mat.docente}</td>
            <td class="text-center">${mat.cargadas} / ${mat.esperadas}</td>
            <td class="text-center">${estadoBadge}</td>
            <td class="text-center">${accion}</td>
        </tr>`;
    });

    $('#tbody-checklist').html(html || '<tr><td colspan="5" class="text-center text-muted py-3">Sin materias para mostrar</td></tr>');
}

// Recarga todo el checklist desde el servidor, y si había un curso abierto en
// el detalle, vuelve a mostrarlo actualizado (en vez de saltar a la vista de cursos)
function recargarChecklistManteniendoVista() {
    fetch(`api/checklist_periodo.php?idPeriodo=${checklistPeriodoActual}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { showMessage('error', data.message); return; }
            checklistDataActual = data.data;
            renderResumenGeneral(data.data);
            renderCursos(data.data);
            if (checklistIdAulaActual !== null) {
                abrirDetalleCurso(checklistIdAulaActual);
            }
        })
        .catch(() => showMessage('error', 'Error al actualizar el checklist'));
}

// ── Reabrir materia (crear excepción) ────────────────────
$(document).on('click', '.btn-crear-excepcion', function () {
    const idAulaMateria = $(this).data('idaulamateria');
    const materia       = $(this).data('materia');

    alertify.confirm(
        'Reabrir Materia',
        `¿Reabrir <strong>"${materia}"</strong> para este período?<br>
        <small class="text-muted">El docente podrá editar notas hasta que vuelvas a cerrarla.</small>`,
        function () {
            fetch('api/gestionar_excepcion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idPeriodo: checklistPeriodoActual, idAulaMateria, accion: 'crear' })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) { showMessage('error', data.message); return; }
                showMessage('success', data.message);
                recargarChecklistManteniendoVista();
            })
            .catch(() => showMessage('error', 'Error al reabrir la materia'));
        },
        null
    ).set('labels', { ok: 'Reabrir', cancel: 'Cancelar' });
});

// ── Cerrar materia (eliminar excepción) ──────────────────
$(document).on('click', '.btn-quitar-excepcion', function () {
    const idAulaMateria = $(this).data('idaulamateria');

    alertify.confirm(
        'Cerrar Materia',
        `¿Cerrar nuevamente esta materia?<br>
        <small class="text-muted">El docente ya no podrá editar notas de este período.</small>`,
        function () {
            fetch('api/gestionar_excepcion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idPeriodo: checklistPeriodoActual, idAulaMateria, accion: 'eliminar' })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) { showMessage('error', data.message); return; }
                showMessage('success', data.message);
                recargarChecklistManteniendoVista();
            })
            .catch(() => showMessage('error', 'Error al cerrar la materia'));
        },
        null
    ).set('labels', { ok: 'Cerrar', cancel: 'Cancelar' });
});

// Al cerrar el modal, resetear el estado de navegación interna
$('#modalChecklist').on('hidden.bs.modal', function () {
    checklistIdAulaActual = null;
    mostrarVistaCursos();
});
</script>
