<?php
/**
 * Vista: resumen_alumno.php
 * Consulta del proceso académico por alumno o por materia.
 * Exportación a Excel, PDF e Impresión usando reportes.js
 */
$ruta = "../";
    include __DIR__ . '/includes/header.php';
    require_once __DIR__ . '/../servicios/conexion.php';
?>

<!-- jQuery y DataTables -->
<script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>
<link  rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css">
<script src="<?php echo $ruta; ?>dt/jquery.dataTables.min.js"></script>
<script src="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.js"></script>

<!-- Librerías de exportación -->
<script src="<?php echo $ruta; ?>dt/botones/jszip.min.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/pdfmake.min.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/vfs_fonts.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/dataTables.buttons.min.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/buttons.html5.min.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/buttons.print.min.js"></script>



<script src="<?php echo $ruta; ?>dt/botones/xlsx.full.min.js"></script>


<!-- Helper institucional de reportes -->
<script src="reportes.js"></script>

<style>
.main-content { display: block; color: #1f2937; }
.resumen-shell { max-width: 1200px; margin: 0 auto; }

/* ── Título ── */
.page-title { margin-bottom: 18px; }
.page-title h2 { font-size: 1.35rem; font-weight: 700; margin: 0; color: #111827; }
.page-title p  { margin: 4px 0 0; color: #6b7280; font-size: 13px; }

/* ── Tabs de modo ── */
.mode-tabs {
    display: inline-flex;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 16px;
    background: #fff;
}
.mode-tab {
    border: 0; background: #fff; color: #4b5563;
    padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer;
}
.mode-tab + .mode-tab { border-left: 1px solid #d1d5db; }
.mode-tab.active { background: #0547d6; color: #fff; }

/* ── Panel de selección ── */
.selector-panel {
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 8px; padding: 16px; margin-bottom: 16px;
}
.selector-grid {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 12px;
    align-items: end;
}
.selector-panel label { font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 5px; display: block; }

/* ── Input de selección con botón ── */
.input-selector-wrap { position: relative; display: flex; gap: 6px; }
.input-selector-wrap input[readonly] {
    background: #f9fafb; cursor: default;
    font-size: 13px; color: #111827; border-radius: 6px 0 0 6px;
}
.btn-abrir-modal {
    border: 1px solid #d1d5db; background: #fff;
    border-radius: 0 6px 6px 0; padding: 0 10px;
    cursor: pointer; color: #374151; font-size: 14px;
    transition: background .15s;
    white-space: nowrap;
}
.btn-abrir-modal:hover { background: #f3f4f6; }

/* ── Modal de selección ── */
#modal-selector .modal-header { background: #111827; color: #fff; border-radius: 8px 8px 0 0; }
#modal-selector .modal-title  { font-size: 14px; font-weight: 700; }
#modal-selector .btn-close     { filter: invert(1); }
.selector-item {
    padding: 10px 14px; border-bottom: 1px solid #f3f4f6;
    cursor: pointer; font-size: 13px; transition: background .12s;
}
.selector-item:hover        { background: #eff6ff; }
.selector-item .item-main   { font-weight: 600; color: #111827; }
.selector-item .item-sub    { font-size: 11px; color: #6b7280; }
#modal-selector-body        { max-height: 420px; overflow-y: auto; }
#modal-selector-buscar      { border-radius: 6px; font-size: 13px; }

/* ── Cabecera del resultado ── */
.summary-head {
    display: flex; justify-content: space-between; gap: 16px;
    align-items: center; padding: 14px 0;
    border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;
    margin-bottom: 14px;
}
.summary-head h3 { margin: 0; font-size: 1rem; font-weight: 700; color: #111827; }
.summary-head p  { margin: 3px 0 0; color: #6b7280; font-size: 12px; }
.meta-row { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
.meta-pill {
    border: 1px solid #e5e7eb; border-radius: 999px;
    padding: 4px 9px; color: #4b5563; background: #f9fafb; font-size: 12px;
}

/* ── Barra de exportación ── */
.export-bar {
    background: #f8f9fa; border: 1px solid #e9ecef;
    border-radius: 8px; padding: 10px 14px;
    display: flex; flex-wrap: wrap; align-items: center;
    gap: 14px; margin-bottom: 14px;
}
.export-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.export-group-label { font-size: 12px; font-weight: 700; color: #374151; white-space: nowrap; }
.orientacion-opts { display: flex; gap: 10px; align-items: center; font-size: 12px; color: #4b5563; }
.orientacion-opts label { display: flex; align-items: center; gap: 4px; cursor: pointer; font-weight: 500; }
.export-divider { width: 1px; height: 28px; background: #dee2e6; margin: 0 4px; }

/* ── Tabla ── */
.table-wrap {
    background: #fff; border: 1px solid #e5e7eb;
    border-radius: 8px; overflow-x: auto;
}
.tabla-resumen { margin: 0; min-width: 940px; }
.tabla-resumen th {
    background: #111827; color: #fff;
    font-size: 11px; font-weight: 700; text-align: center;
    border-color: #374151 !important; vertical-align: middle; white-space: nowrap;
}
.tabla-resumen thead tr:last-child th { background: #374151; font-size: 11px; }
.tabla-resumen td {
    color: #374151; font-size: 12px; border-color: #e5e7eb !important;
    vertical-align: middle; text-align: center;
}
.tabla-resumen td.td-main { text-align: left; font-weight: 700; color: #111827; min-width: 180px; }
.subtle { color: #6b7280; font-weight: 400; font-size: 11px; }

/* ── Badges ── */
.badge-soft      { border-radius: 999px; padding: 3px 8px; font-size: 11px; font-weight: 700; }
.badge-regular   { background: #ecfdf5; color: #047857; }
.badge-irregular { background: #fef2f2; color: #b91c1c; }
.badge-pendiente { background: #f3f4f6; color: #4b5563; }
.badge-rec       { background: #fff7ed; color: #c2410c; }

/* ── Colores de nota ── */
.nota-alta  { color: #047857; font-weight: 700; }
.nota-media { color: #b45309; font-weight: 700; }
.nota-baja  { color: #b91c1c; font-weight: 700; }

/* ── Asterisco incompleto ── */
.asterisco-incompleto {
    color: #d97706; font-weight: 700;
    margin-left: 1px; font-size: 10px; vertical-align: super;
}

/* ── Colores de bloque por sección ── */
.tabla-resumen thead tr:first-child th.bloque-sem1 { background-color: #1d4ed8 !important; color: #fff !important; border-color: #1e40af !important; }
.tabla-resumen thead tr:last-child  th.bloque-sem1 { background-color: #bfdbfe !important; color: #000 !important; border-color: #93c5fd !important; }
.tabla-resumen td.bloque-sem1                      { background-color: #eff6ff !important; color: #000 !important; border-color: #bfdbfe !important; }

.tabla-resumen thead tr:first-child th.bloque-sem2 { background-color: #15803d !important; color: #fff !important; border-color: #166534 !important; }
.tabla-resumen thead tr:last-child  th.bloque-sem2 { background-color: #bbf7d0 !important; color: #000 !important; border-color: #86efac !important; }
.tabla-resumen td.bloque-sem2                      { background-color: #f0fdf4 !important; color: #000 !important; border-color: #bbf7d0 !important; }

.tabla-resumen thead tr:first-child th.bloque-final { background-color: #a16207 !important; color: #fff !important; border-color: #92400e !important; }
.tabla-resumen thead tr:last-child  th.bloque-final { background-color: #fde68a !important; color: #000 !important; border-color: #fcd34d !important; }
.tabla-resumen td.bloque-final                      { background-color: #fefce8 !important; color: #000 !important; border-color: #fde68a !important; }

/* ── Estado inicial ── */
.empty-state {
    border: 1px dashed #d1d5db; border-radius: 8px;
    padding: 42px 16px; text-align: center; color: #6b7280; background: #fff;
}
#spinner-resumen { display: none; text-align: center; padding: 36px 0; color: #6b7280; }

@media(max-width:900px) {
    .selector-grid { grid-template-columns: 1fr 1fr; }
    .summary-head  { flex-direction: column; align-items: flex-start; }
    .meta-row      { justify-content: flex-start; }
}
@media(max-width:560px) { .selector-grid { grid-template-columns: 1fr; } }

/* ── Impresión ── */
@media print {
    .selector-panel, .mode-tabs, nav, .sidebar, footer,
    .export-bar, #modal-selector, #spinner-resumen { display: none !important; }
    .main-content { padding: 0 !important; }
    .table-wrap { border: 0; overflow: visible; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>

<div class="main-content">
<div class="resumen-shell">

    <div class="page-title">
        <h2>Resumen académico</h2>
        <p>Consultá el avance por alumno o revisá una materia con todos sus alumnos.</p>
    </div>

    <!-- Selector de año lectivo global -->
    <div class="d-flex align-items-center gap-3 mb-3">
        <label for="sel-anio-global" style="font-size:13px; font-weight:700; color:#374151; white-space:nowrap; margin:0;">
            <i class="bi bi-calendar3 me-1"></i> Año lectivo:
        </label>
        <select id="sel-anio-global" class="form-select form-select-sm" style="max-width:200px;">
            <option value="">Cargando...</option>
        </select>
    </div>

    <!-- Tabs de modo -->
    <div class="mode-tabs">
        <button type="button" class="mode-tab active" data-mode="alumno">
            <i class="bi bi-person-lines-fill me-1"></i> Por alumno
        </button>
        <button type="button" class="mode-tab" data-mode="materia">
            <i class="bi bi-journal-text me-1"></i> Por materia
        </button>
    </div>

    <!-- Panel Por alumno -->
    <div class="selector-panel" id="panel-alumno">
        <div class="selector-grid">
            <div>
                <label>Curso</label>
                <select id="sel-aula" class="form-select form-select-sm">
                    <option value="">Seleccione un curso</option>
                </select>
            </div>
            <div>
                <label>Alumno</label>
                <!-- Input visible + botón; el select oculto guarda el ID -->
                <div class="input-selector-wrap">
                    <input type="text" id="display-alumno" class="form-control form-control-sm"
                           placeholder="Seleccione un alumno..." readonly>
                    <button type="button" class="btn-abrir-modal"
                            id="btn-modal-alumno" disabled title="Elegir alumno">
                        <i class="bi bi-people"></i>
                    </button>
                </div>
                <input type="hidden" id="sel-alumno">
            </div>
            <div style="padding-bottom:2px;">
                <button id="btn-buscar-alumno" class="btn btn-dark btn-sm w-100" disabled>
                    <i class="bi bi-search me-1"></i> Ver
                </button>
            </div>
        </div>
    </div>

    <!-- Panel Por materia -->
    <div class="selector-panel d-none" id="panel-materia">
        <div class="selector-grid">
            <div>
                <label>Curso</label>
                <select id="sel-aula-materia" class="form-select form-select-sm">
                    <option value="">Seleccione un curso</option>
                </select>
            </div>
            <div>
                <label>Materia</label>
                <div class="input-selector-wrap">
                    <input type="text" id="display-materia" class="form-control form-control-sm"
                           placeholder="Seleccione una materia..." readonly>
                    <button type="button" class="btn-abrir-modal"
                            id="btn-modal-materia" disabled title="Elegir materia">
                        <i class="bi bi-journal-bookmark"></i>
                    </button>
                </div>
                <input type="hidden" id="sel-materia">
            </div>
            <div style="padding-bottom:2px;">
                <button id="btn-buscar-materia" class="btn btn-dark btn-sm w-100" disabled>
                    <i class="bi bi-search me-1"></i> Ver
                </button>
            </div>
        </div>
    </div>

    <!-- Spinner -->
    <div id="spinner-resumen">
        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
        <span class="ms-2">Cargando resumen...</span>
    </div>

    <!-- Resultado -->
    <div id="contenido-resumen" style="display:none;">

        <div class="summary-head">
            <div>
                <h3 id="resumen-titulo"></h3>
                <p  id="resumen-subtitulo"></p>
            </div>
            <div class="meta-row" id="resumen-meta"></div>
        </div>

        <!-- Barra de exportación con opciones de orientación -->
        <div class="export-bar">

            <div class="export-group">
                <span class="export-group-label"><i class="bi bi-file-earmark-excel text-success me-1"></i> Excel</span>
                <button id="btn-excel" class="btn btn-success btn-sm">
                    <i class="bi bi-download me-1"></i> Descargar
                </button>
            </div>

            <div class="export-divider"></div>

            <div class="export-group">
                <span class="export-group-label"><i class="bi bi-file-earmark-pdf text-danger me-1"></i> PDF</span>
                <div class="orientacion-opts">
                    <label><input type="radio" name="orient-pdf" value="portrait"  checked> Vertical</label>
                    <label><input type="radio" name="orient-pdf" value="landscape">         Horizontal</label>
                </div>
                <button id="btn-pdf" class="btn btn-danger btn-sm">
                    <i class="bi bi-download me-1"></i> Descargar
                </button>
            </div>

            <div class="export-divider"></div>

            <div class="export-group">
                <span class="export-group-label"><i class="bi bi-printer me-1"></i> Imprimir</span>
                <div class="orientacion-opts">
                    <label><input type="radio" name="orient-print" value="portrait"  checked> Vertical</label>
                    <label><input type="radio" name="orient-print" value="landscape">         Horizontal</label>
                </div>
                <button id="btn-print" class="btn btn-secondary btn-sm">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </button>
            </div>

        </div>

        <div class="table-wrap">
            <table class="table table-bordered tabla-resumen" id="tabla-resumen">
                <thead id="thead-resumen"></thead>
                <tbody id="tbody-resumen"></tbody>
            </table>
        </div>

    </div>

    <div class="empty-state" id="estado-inicial">
        Seleccioná los filtros para ver el proceso académico.
    </div>

</div>
</div>

<!-- ══ Modal de selección (alumno o materia) ══ -->
<div class="modal fade" id="modal-selector" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title" id="modal-selector-titulo">Seleccionar</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom">
                    <input type="text" id="modal-selector-buscar"
                           class="form-control form-control-sm"
                           placeholder="Buscar...">
                </div>
                <div id="modal-selector-body"></div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
// ═══════════════════════════════════════════════════════════════════════════
// ESTADO GLOBAL
// ═══════════════════════════════════════════════════════════════════════════
let modoActual   = 'alumno';
let datosExport  = {};          // datos en bruto para el Excel personalizado
let dtInstance   = null;        // instancia DataTable sobre #tabla-resumen
let modalTipoActual = 'alumno'; // 'alumno' o 'materia' — qué tipo abrió el modal
let idAnioSeleccionado = '';    // año actualmente seleccionado en el select global

// Logos en base64 — se cargan una sola vez al iniciar y se reutilizan
let logoMEC    = '';
let logoSanta  = '';

// ── Convertir imagen a base64 ──
function imgToBase64(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = function () {
            const canvas = document.createElement('canvas');
            canvas.width  = img.width;
            canvas.height = img.height;
            canvas.getContext('2d').drawImage(img, 0, 0);
            resolve(canvas.toDataURL('image/png'));
        };
        img.onerror = () => reject(new Error('No se pudo cargar ' + url));
        img.src = url + '?t=' + Date.now(); // evitar caché
    });
}

// ── Cargar logos al iniciar ──
async function cargarLogos() {
    try {
        [logoMEC, logoSanta] = await Promise.all([
            imgToBase64('../img/logo-mec.png'),
            imgToBase64('../img/logo-Santa.jpeg'),
        ]);
    } catch(e) {
        console.warn('No se pudieron cargar los logos:', e.message);
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// UTILIDADES
// ═══════════════════════════════════════════════════════════════════════════

function showMessage(type, text) {
    if (typeof alertify !== 'undefined') {
        alertify[type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'error'](text);
    } else { alert(text); }
}

function setLoading(loading) {
    $('#spinner-resumen').toggle(loading);
    if (loading) $('#contenido-resumen, #estado-inicial').hide();
}

function abrevTipo(nombre) {
    if (/escrita/i.test(nombre))          return 'E';
    if (/trabajo/i.test(nombre))          return 'T';
    if (/primera|1ra/i.test(nombre))      return 'PP';
    if (/segunda|2da/i.test(nombre))      return 'SP';
    if (/examen/i.test(nombre))           return 'EF';
    if (/exposici/i.test(nombre))         return 'Ex';
    if (/participaci|oral/i.test(nombre)) return 'P';
    return nombre.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
}

function claseNota(nota) {
    if (nota === null || nota === undefined) return '';
    if (nota >= 4) return 'nota-alta';
    if (nota >= 3) return 'nota-media';
    return 'nota-baja';
}

function fmtNota(nota) {
    if (nota === null || nota === undefined || nota === '') return '<span class="subtle">-</span>';
    const n = parseFloat(nota);
    return `<span class="${claseNota(n)}">${parseInt(n)}</span>`;
}

function fmtPuntos(obt, max, completo = true) {
    if (!max || parseFloat(max) === 0) return '<span class="subtle">-</span>';
    const valor = `${parseFloat(obt).toFixed(0)}/${parseFloat(max).toFixed(0)}`;
    if (!completo) {
        return `<span title="Hay evaluaciones de este tipo sin calificar todavía">${valor}<sup class="asterisco-incompleto">*</sup></span>`;
    }
    return valor;
}

function fmtAsist(asist) {
    if (!asist || asist.pct === null || asist.pct === undefined)
        return '<span class="subtle">-</span>';
    const cls = asist.pct >= 75 ? 'nota-alta' : asist.pct >= 60 ? 'nota-media' : 'nota-baja';
    return `<span class="${cls}">${asist.pct}%</span><br><span class="subtle">${asist.presentes}P / ${asist.ausentes}A</span>`;
}

function fmtEstado(estado) {
    const mapa = {
        'Regular'       : 'badge-regular',
        'Irregular'     : 'badge-irregular',
        'Aprobado'      : 'badge-regular',
        'Recuperatorio1': 'badge-rec',
        'Recuperatorio2': 'badge-rec',
        'Reprobado'     : 'badge-irregular',
        'Pendiente'     : 'badge-pendiente',
    };
    return `<span class="badge-soft ${mapa[estado] || 'badge-pendiente'}">${estado || 'Pendiente'}</span>`;
}

function fmtNotaFinal(nf) {
    if (!nf) return '<span class="subtle">-</span>';
    const nota = nf.nota_definitiva ?? nf.nota_final;
    if (nota === null || nota === undefined) return '<span class="subtle">-</span>';
    return `<span class="${nota >= 3 ? 'nota-alta' : 'nota-baja'}">${nota}</span>`;
}

// ═══════════════════════════════════════════════════════════════════════════
// MODAL DE SELECCIÓN — alumno o materia
// ═══════════════════════════════════════════════════════════════════════════

let listaModalItems = []; // cache de ítems cargados en el modal

function abrirModal(tipo) {
    modalTipoActual = tipo;
    const esAlumno  = tipo === 'alumno';
    $('#modal-selector-titulo').text(esAlumno ? 'Seleccionar alumno' : 'Seleccionar materia');
    $('#modal-selector-buscar').val('');
    renderItemsModal(listaModalItems);
    new bootstrap.Modal(document.getElementById('modal-selector')).show();
}

function renderItemsModal(items) {
    if (!items || items.length === 0) {
        $('#modal-selector-body').html('<p class="text-muted text-center py-4 mb-0">No hay elementos para mostrar</p>');
        return;
    }
    let html = '';
    items.forEach(item => {
        html += `<div class="selector-item" data-id="${item.id}" data-label="${item.label}">
            <div class="item-main">${item.label}</div>
            ${item.sub ? `<div class="item-sub">${item.sub}</div>` : ''}
        </div>`;
    });
    $('#modal-selector-body').html(html);
}

// Filtro de búsqueda dentro del modal
$('#modal-selector-buscar').on('input', function() {
    const q = $(this).val().toLowerCase().trim();
    if (!q) { renderItemsModal(listaModalItems); return; }
    const filtrado = listaModalItems.filter(i =>
        i.label.toLowerCase().includes(q) || (i.sub || '').toLowerCase().includes(q)
    );
    renderItemsModal(filtrado);
});

// Clic en un ítem del modal
$('#modal-selector-body').on('click', '.selector-item', function() {
    const id    = $(this).data('id');
    const label = $(this).data('label');

    if (modalTipoActual === 'alumno') {
        $('#sel-alumno').val(id);
        $('#display-alumno').val(label);
        $('#btn-buscar-alumno').prop('disabled', false);
    } else {
        $('#sel-materia').val(id);
        $('#display-materia').val(label);
        $('#btn-buscar-materia').prop('disabled', false);
    }

    bootstrap.Modal.getInstance(document.getElementById('modal-selector')).hide();
});

// ═══════════════════════════════════════════════════════════════════════════
// CARGA DE CURSOS / ALUMNOS / MATERIAS
// ═══════════════════════════════════════════════════════════════════════════

// ── Cargar todos los años lectivos disponibles ──
function cargarAnios() {
    fetch('api/anios_lectivos.php')
        .then(r => r.json())
        .then(data => {
            if (!data.success) { showMessage('error', 'Error al cargar años lectivos'); return; }
            const $sel = $('#sel-anio-global');
            $sel.empty();
            (data.data || []).forEach(a => {
                const activo = a.activo === 'Sí' ? ' (activo)' : '';
                $sel.append(`<option value="${a.idAnio}" ${a.activo === 'Sí' ? 'selected' : ''}>${a.anio}${activo}</option>`);
            });
            // Tomar el valor que quedó seleccionado (el año activo por defecto)
            idAnioSeleccionado = $sel.val();
            // Con el año ya seleccionado, cargar los cursos
            cargarAulas(idAnioSeleccionado);
        })
        .catch(() => showMessage('error', 'Error al cargar años lectivos'));
}

function cargarAulas(idAnio) {
    const param = idAnio ? `?idAnio=${idAnio}` : '';
    fetch(`api/aulas_anio.php${param}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { showMessage('error', data.message || 'Error al cargar cursos'); return; }
            const items = data.data || [];
            ['#sel-aula', '#sel-aula-materia'].forEach(id => {
                const sel = $(id).find('option:not(:first)').remove().end();
                items.forEach(a => sel.append(`<option value="${a.idAula}">${a.label}</option>`));
            });
        })
        .catch(() => showMessage('error', 'Error al cargar cursos'));
}

function cargarAlumnosModal(idAula) {
    listaModalItems = [];
    $('#display-alumno').val('').attr('placeholder', 'Cargando alumnos...');
    $('#btn-modal-alumno').prop('disabled', true);
    $('#sel-alumno').val('');
    $('#btn-buscar-alumno').prop('disabled', true);

    if (!idAula) {
        $('#display-alumno').attr('placeholder', 'Seleccione un curso primero...');
        return;
    }

    const anioParam = idAnioSeleccionado ? `&idAnio=${idAnioSeleccionado}` : '';
    fetch(`api/alumnos_aula.php?idAula=${idAula}${anioParam}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showMessage('error', data.message || 'Error al cargar alumnos');
                $('#display-alumno').attr('placeholder', 'Sin alumnos en este curso');
                return;
            }
            listaModalItems = (data.data || []).map(a => ({
                id   : a.idAlumno,
                label: a.nombre_completo,
                sub  : a.cedula ? `CI: ${a.cedula}` : '',
            }));
            $('#display-alumno').attr('placeholder', `${listaModalItems.length} alumno(s) disponibles`);
            $('#btn-modal-alumno').prop('disabled', false);
        })
        .catch(() => showMessage('error', 'Error al cargar alumnos'));
}

function cargarMateriasModal(idAula) {
    listaModalItems = [];
    $('#display-materia').val('').attr('placeholder', 'Cargando materias...');
    $('#btn-modal-materia').prop('disabled', true);
    $('#sel-materia').val('');
    $('#btn-buscar-materia').prop('disabled', true);

    if (!idAula) {
        $('#display-materia').attr('placeholder', 'Seleccione un curso primero...');
        return;
    }

    fetch(`api/materias_docente.php?idAula=${idAula}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showMessage('error', data.message || 'Error al cargar materias');
                return;
            }
            listaModalItems = (data.data || []).map(m => ({
                id   : m.idAulaMateria,
                label: m.materia,
                sub  : `${m.horas_sem || 0}h/semana`,
            }));
            $('#display-materia').attr('placeholder', `${listaModalItems.length} materia(s) disponibles`);
            $('#btn-modal-materia').prop('disabled', false);
        })
        .catch(() => showMessage('error', 'Error al cargar materias'));
}

// ═══════════════════════════════════════════════════════════════════════════
// TABLA — encabezado y filas (con clases de bloque)
// ═══════════════════════════════════════════════════════════════════════════

function construirEncabezado(labelPrincipal, periodos, tipos) {
    let th1 = `<tr><th rowspan="2">${labelPrincipal}</th><th rowspan="2">Asistencia</th>`;
    let th2 = '<tr>';

    periodos.forEach((per, idx) => {
        const bloque = idx === 0 ? 'bloque-sem1' : 'bloque-sem2';
        const cols   = tipos.length + 2; 
        
        th1 += `<th colspan="${cols}" class="${bloque}">${per.nombre}</th>`;
        
        // Tipos de nota (ya tenías el title, está perfecto)
        tipos.forEach(t => th2 += `<th class="${bloque}" title="${t.nombre}">${abrevTipo(t.nombre)}</th>`);
        
        //Agrega el atributo title a TP y NP
        th2 += `<th class="${bloque}" title="Total de Puntos">TP</th>`;
        th2 += `<th class="${bloque}" title="Nota Parcial">NP</th>`;
    });


/*
    periodos.forEach((per, idx) => {
        const bloque = idx === 0 ? 'bloque-sem1' : 'bloque-sem2';
        const cols   = tipos.length + 2; // tipos + TP acumulado + NP
        th1 += `<th colspan="${cols}" class="${bloque}">${per.nombre}</th>`;
        tipos.forEach(t => th2 += `<th class="${bloque}" title="${t.nombre}">${abrevTipo(t.nombre)}</th>`);
        th2 += `<th class="${bloque}">TP</th><th class="${bloque}">NP</th>`;
    });
*/
    th1 += '<th colspan="4" class="bloque-final">Nota Final</th>';
    th2 += `<th class="bloque-final" title="1° Recuperatorio">Rec1</th>
            <th class="bloque-final" title="2° Recuperatorio">Rec2</th>
            <th class="bloque-final" title="Nota Final">NF</th>
            <th class="bloque-final">Estado</th>`;
    th1 += '</tr>'; th2 += '</tr>';

    $('#thead-resumen').html(th1 + th2);
}

function construirFilas(items, tipos, principalFn) {
    let html = '';
    items.forEach(item => {
        let tds = `<td class="td-main">${principalFn(item)}</td>`;
        tds += `<td>${fmtAsist(item.asistencia)}</td>`;

        (item.periodos || []).forEach((per, idx) => {
            const bloque = idx === 0 ? 'bloque-sem1' : 'bloque-sem2';
            tipos.forEach(tipo => {
                const datTipo = (per.porTipo || []).find(t => t.idTipoNota == tipo.idTipoNota);
                tds += `<td class="${bloque}">${datTipo ? fmtPuntos(datTipo.puntos, datTipo.total, datTipo.completo) : '<span class="subtle">-</span>'}</td>`;
            });
            tds += `<td class="${bloque}">${per.totalMax > 0 ? fmtPuntos(per.totalPuntos, per.totalMax) : '<span class="subtle">-</span>'}</td>`;
            tds += `<td class="${bloque}">${fmtNota(per.notaParcial)}</td>`;
        });

        const nf = item.notaFinal || null;
        tds += `<td class="bloque-final">${nf?.nota_rec1       !== null && nf?.nota_rec1       !== undefined ? fmtNota(nf.nota_rec1)       : '<span class="subtle">-</span>'}</td>`;
        tds += `<td class="bloque-final">${nf?.nota_rec2       !== null && nf?.nota_rec2       !== undefined ? fmtNota(nf.nota_rec2)       : '<span class="subtle">-</span>'}</td>`;
        tds += `<td class="bloque-final">${fmtNotaFinal(nf)}</td>`;
        tds += `<td class="bloque-final">${fmtEstado(nf ? nf.estado_final : item.estado)}</td>`;

        html += `<tr>${tds}</tr>`;
    });

    $('#tbody-resumen').html(html || '<tr><td colspan="99" class="py-4 text-muted text-center">Sin datos</td></tr>');
}

// Inicializar DataTable sobre la tabla ya rendereada (sin paginación ni búsqueda)
function inicializarDataTable() {
    if (dtInstance) {
        try { dtInstance.destroy(); } catch(e) {}
        dtInstance = null;
        // Vaciar DESPUÉS del destroy: DataTables restaura el HTML anterior al destruirse,
        // por eso hay que limpiar manualmente para que el próximo render no muestre datos viejos.
        $('#thead-resumen').empty();
        $('#tbody-resumen').empty();
    }
    // Solo inicializar si ya hay columnas construidas en el thead
    if ($('#thead-resumen th').length === 0) return;
    dtInstance = $('#tabla-resumen').DataTable({
        paging   : false,
        searching: false,
        ordering : false,
        info     : false,
        destroy  : true,
    });
}

// ═══════════════════════════════════════════════════════════════════════════
// RENDER RESULTADOS
// ═══════════════════════════════════════════════════════════════════════════

function renderAlumno(data) {
    const { alumno, periodos, tipos, materias, anio } = data;
    $('#resumen-titulo').text(`${alumno.apellidos}, ${alumno.nombres}`);
    $('#resumen-subtitulo').text(`CI: ${alumno.cedula || 'S/N'} | ${alumno.numCurso}° - ${alumno.enfasis}`);
    $('#resumen-meta').html(`
        <span class="meta-pill">Año ${anio.anio}</span>
        <span class="meta-pill">${alumno.turno || ''}</span>
        <span class="meta-pill">Matrícula ${alumno.estadoMatricula}</span>
    `);
    inicializarDataTable();
    construirEncabezado('Materia', periodos, tipos);
    construirFilas(materias || [], tipos, item =>
        `${item.materia}<br><span class="subtle">${item.codigo || ''}</span>`
    );
    

    datosExport = {
        titulo   : `${alumno.apellidos}, ${alumno.nombres}`,
        subtitulo: `CI: ${alumno.cedula || 'S/N'} | ${alumno.numCurso}° - ${alumno.enfasis} | Año ${anio.anio}`,
        periodos, tipos,
        items    : materias || [],
        getFila  : item => item.materia,
    };
    $('#contenido-resumen').show();
}

function renderMateria(data) {
    const { materia, periodos, tipos, alumnos, anio } = data;
    $('#resumen-titulo').text(materia.materia);
    $('#resumen-subtitulo').text(`${materia.numCurso}° - ${materia.enfasis}`);
    $('#resumen-meta').html(`
        <span class="meta-pill">Año ${anio.anio}</span>
        <span class="meta-pill">${materia.turno || ''}</span>
        <span class="meta-pill">${alumnos.length} alumno(s)</span>
    `);
    inicializarDataTable();
    construirEncabezado('Alumno', periodos, tipos);
    construirFilas(alumnos || [], tipos, item =>
        `${item.apellidos}, ${item.nombres}<br><span class="subtle">CI: ${item.cedula || 'S/N'}</span>`
    );
    

    datosExport = {
        titulo   : materia.materia,
        subtitulo: `${materia.numCurso}° - ${materia.enfasis} | Año ${anio.anio}`,
        periodos, tipos,
        items    : alumnos || [],
        getFila  : item => `${item.apellidos}, ${item.nombres}`,
    };
    $('#contenido-resumen').show();
}

// ═══════════════════════════════════════════════════════════════════════════
// EXPORTACIÓN
// ═══════════════════════════════════════════════════════════════════════════

// ── Excel: columnas numéricas separadas ─────────────────────────────────────

function colIndiceLetter(n) {
    let result = ''; n += 1;
    while (n > 0) {
        const rem = (n - 1) % 26;
        result = String.fromCharCode(65 + rem) + result;
        n = Math.floor((n - 1) / 26);
    }
    return result;
}

function exportarExcel() {
    const { periodos, tipos, items, titulo, subtitulo, getFila } = datosExport;
    if (!items || items.length === 0) { showMessage('warning', 'No hay datos para exportar'); return; }

    // ── Fila 1: secciones (1° Semestre, 2° Semestre, Nota Final) ──
    const cab1 = ['', 'Asistencia %'];
    // ── Fila 2: tipos de nota (E, T, TP, NP) ──
    const cab2 = ['Nombre', ''];
    // ── Fila 3: total de puntos por tipo (sin /) ──
    const cab3 = ['', ''];

    // Para calcular las fusiones necesito saber en qué columna empieza cada sección
    const merges = [];
    let colActual = 2; // empieza después de Nombre y Asistencia

    periodos.forEach((per, idx) => {
        const colInicio = colActual;
        tipos.forEach(t => {
            cab1.push(per.nombre);
            cab2.push(abrevTipo(t.nombre));
            // Buscar el total real de puntos de este tipo para este período
            // tomándolo del primer ítem que tenga datos (todos comparten el mismo total)
            let totalPts = '';
            for (let i = 0; i < items.length; i++) {
                const per2 = (items[i].periodos || [])[idx];
                if (per2) {
                    const d = (per2.porTipo || []).find(tt => tt.idTipoNota == t.idTipoNota);
                    if (d && d.total > 0) { totalPts = d.total; break; }
                }
            }
            cab3.push(totalPts);
            colActual++;
        });
        // TP acumulado
        cab1.push(per.nombre);
        cab2.push('TP');
        let totalTP = '';
        for (let i = 0; i < items.length; i++) {
            const per2 = (items[i].periodos || [])[idx];
            if (per2 && per2.totalMax > 0) { totalTP = per2.totalMax; break; }
        }
        cab3.push(totalTP);
        colActual++;
        // NP
        cab1.push(per.nombre);
        cab2.push('NP');
        cab3.push('');
        colActual++;

        // Registrar fusión para el nombre del semestre (fila 0)
        merges.push({ s: { r: 0, c: colInicio }, e: { r: 0, c: colActual - 1 } });
    });

    // Nota Final
    const colInicioFinal = colActual;
    ['Rec1','Rec2','NF','Estado'].forEach(h => {
        cab1.push('Nota Final');
        cab2.push(h);
        cab3.push('');
        colActual++;
    });
    merges.push({ s: { r: 0, c: colInicioFinal }, e: { r: 0, c: colActual - 1 } });

    // ── Filas de datos ──
    const filas = items.map(item => {
        const fila = [getFila(item), item.asistencia?.pct ?? ''];
        (item.periodos || []).forEach(per => {
            tipos.forEach(tipo => {
                const d = (per.porTipo || []).find(t => t.idTipoNota == tipo.idTipoNota);
                fila.push(d ? parseFloat(d.puntos) : 0);
            });
            fila.push(parseFloat(per.totalPuntos) || 0);
            fila.push(per.notaParcial !== null ? parseInt(per.notaParcial) : '');
        });
        const nf = item.notaFinal || null;
        fila.push(nf?.nota_rec1       != null ? parseInt(nf.nota_rec1)       : '');
        fila.push(nf?.nota_rec2       != null ? parseInt(nf.nota_rec2)       : '');
        fila.push(nf?.nota_definitiva != null ? parseInt(nf.nota_definitiva) : (nf?.nota_final ?? ''));
        fila.push(nf?.estado_final || item.estado || 'Pendiente');
        return fila;
    });

    // ── Construir hoja ──
    const ws_data = [cab1, cab2, cab3, ...filas];
    const ws = XLSX.utils.aoa_to_sheet(ws_data);

    // ── Fusiones de celdas ──
    ws['!merges'] = merges;

    // ── Anchos de columna ──
    // Col 0: nombre alumno/materia — ancha
    // Col 1: asistencia — chica
    // Resto: columnas numéricas — medianas, Estado un poco más ancha
    // ── Anchos de columna ─────────────────────────────────────────────
    const totalCols = cab1.length;
    const widths = [];

    // Cantidad de columnas de datos (sin Nombre, Asistencia y Estado)
    const columnasDatos = Math.max(1, totalCols - 3);

    // Ancho dinámico para que el Excel quede equilibrado
    let anchoNumerico;

    if (columnasDatos <= 8) {
        anchoNumerico = 12;
    } else if (columnasDatos <= 12) {
        anchoNumerico = 10;
    } else if (columnasDatos <= 18) {
        anchoNumerico = 8;
    } else if (columnasDatos <= 24) {
        anchoNumerico = 7;
    } else {
        anchoNumerico = 6;
    }

    for (let i = 0; i < totalCols; i++) {

        if (i === 0) {
            // Nombre del alumno o materia
            widths.push({ wch: 35 });
        }
        else if (i === 1) {
            // Asistencia
            widths.push({ wch: 12 });
        }
        else if (i === totalCols - 1) {
            // Estado
            widths.push({ wch: 16 });
        }
        else {
            // Evaluaciones
            widths.push({ wch: anchoNumerico });
        }

    }

    ws['!cols'] = widths;

    // ── Exportar ──
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Resumen');
    const fecha    = new Date().toISOString().split('T')[0];
    const filename = `Resumen_${titulo.replace(/[^a-zA-Z0-9]/g,'_')}_${fecha}.xlsx`;
    XLSX.writeFile(wb, filename);
}

// ── PDF con reportes.js ──────────────────────────────────────────────────────

function exportarPDF() {
    if (!datosExport.items?.length) {
        if (typeof showMessage !== 'undefined') showMessage('warning', 'No hay datos para exportar');
        return;
    }

    const { periodos, tipos } = datosExport;
    const totalColumnas = 2 + (periodos.length * (tipos.length + 2)) + 4;
    
    // Forzamos a horizontal si hay más de 12 columnas
    let orientacion = $('input[name="orient-pdf"]:checked').val() || 'portrait';
    if (totalColumnas > 12) orientacion = 'landscape';

    const tituloDoc = `${datosExport.titulo} — ${datosExport.subtitulo}`;
    
    // Ajuste dinámico de fuente: vital para que los números quepan
    const fontSizeDinamico = totalColumnas > 25 ? 5 : (totalColumnas > 18 ? 6 : 7);
    const fontHeader = fontSizeDinamico + 1;

    const cabecera1 = [];
    const cabecera2 = [];

    // Achicamos la palabra "Asistencia" a "Asist." para no anchar la columna en vano
    cabecera1.push({ text: datosExport.getFila({}) ? 'Nombre' : 'Materia', bold: true, rowSpan: 2, alignment: 'center', fontSize: fontHeader });
    cabecera1.push({ text: 'Asist.', bold: true, rowSpan: 2, alignment: 'center', fontSize: fontHeader });
    cabecera2.push({}); cabecera2.push({});

    // Períodos
    periodos.forEach((per, idx) => {
        const cols = tipos.length + 2;
        cabecera1.push({
            text: per.nombre, bold: true, colSpan: cols, alignment: 'center',
            fillColor: idx === 0 ? '#1d4ed8' : '#15803d', color: '#ffffff', fontSize: fontHeader
        });
        for (let i = 1; i < cols; i++) cabecera1.push({});

        tipos.forEach(t => {
            cabecera2.push({
                text: abrevTipo(t.nombre), bold: true, alignment: 'center',
                fillColor: idx === 0 ? '#bfdbfe' : '#bbf7d0', color: '#000000', fontSize: fontSizeDinamico
            });
        });
        cabecera2.push({ text: 'TP', bold: true, alignment: 'center', fillColor: idx === 0 ? '#bfdbfe' : '#bbf7d0', color: '#000000', fontSize: fontSizeDinamico });
        cabecera2.push({ text: 'NP', bold: true, alignment: 'center', fillColor: idx === 0 ? '#bfdbfe' : '#bbf7d0', color: '#000000', fontSize: fontSizeDinamico });
    });

    // Nota Final
    cabecera1.push({ text: 'Nota Final', bold: true, colSpan: 4, alignment: 'center', fillColor: '#a16207', color: '#ffffff', fontSize: fontHeader });
    cabecera1.push({}); cabecera1.push({}); cabecera1.push({});

    ['Rec1', 'Rec2', 'NF', 'Estado'].forEach(h => {
        cabecera2.push({ text: h, bold: true, alignment: 'center', fillColor: '#fde68a', color: '#000000', fontSize: fontSizeDinamico });
    });

    // Filas de datos
    const filasPDF = datosExport.items.map(item => {
        const fila = [
            { text: datosExport.getFila(item), alignment: 'left', fontSize: fontSizeDinamico },
            { text: item.asistencia?.pct != null ? item.asistencia.pct + '%' : '-', alignment: 'center', fontSize: fontSizeDinamico }
        ];

        (item.periodos || []).forEach(per => {
            tipos.forEach(tipo => {
                const d = (per.porTipo || []).find(t => t.idTipoNota == tipo.idTipoNota);
                let txt = '-';
                if (d && d.total > 0) {
                    txt = `${parseFloat(d.puntos).toFixed(0)}/${parseFloat(d.total).toFixed(0)}`;
                    if (!d.completo) txt += '*';
                }
                fila.push({ text: txt, alignment: 'center', fontSize: fontSizeDinamico });
            });
            fila.push({ text: per.totalMax > 0 ? `${parseFloat(per.totalPuntos).toFixed(0)}/${parseFloat(per.totalMax).toFixed(0)}` : '-', alignment: 'center', fontSize: fontSizeDinamico });
            fila.push({ text: per.notaParcial != null ? String(per.notaParcial) : '-', alignment: 'center', fontSize: fontSizeDinamico });
        });

        const nf = item.notaFinal || null;
        fila.push({ text: nf?.nota_rec1 != null ? String(nf.nota_rec1) : '-', alignment: 'center', fontSize: fontSizeDinamico });
        fila.push({ text: nf?.nota_rec2 != null ? String(nf.nota_rec2) : '-', alignment: 'center', fontSize: fontSizeDinamico });
        fila.push({
            text: nf?.nota_definitiva != null ? String(nf.nota_definitiva) : (nf?.nota_final != null ? String(nf.nota_final) : '-'),
            alignment: 'center', fontSize: fontSizeDinamico + 0.5, bold: true,
            color: nf?.nota_definitiva >= 3 ? '#047857' : (nf?.nota_definitiva >= 2 ? '#b45309' : '#b91c1c')
        });
        fila.push({ text: nf?.estado_final || item.estado || 'Pendiente', alignment: 'center', fontSize: fontSizeDinamico });

        return fila;
    });

// 1. Definimos un ancho fijo para el nombre (ej: 120px) 
    //    y un ancho mínimo para las columnas de datos (ej: 25px)
    const anchoNombre = 120; 
    const anchoNota = 28; 
    
    const widths = [anchoNombre]; // Primera columna fija
    
    // El resto de las columnas (Asistencia + Notas)
    for (let i = 1; i < totalColumnas; i++) {
        widths.push(anchoNota);
    }

    // 2. IMPORTANTE: Si la suma total de anchos (120 + (totalColumnas * 28)) 
    //    es MENOR que el ancho de la hoja (ej: 800px en landscape), 
    //    pdfMake dejará un espacio vacío a la derecha. 
    //    Podemos decirle que la última columna tome el resto:
    widths[totalColumnas - 1] = '*';

    const docDefinition = {
        pageOrientation: orientacion,
        pageSize: totalColumnas > 25 ? 'LEGAL' : 'A4', // Si hay muchísimas, pasamos a Oficio
        pageMargins: [15, 110, 15, 50], // Márgenes súper finos de 15px
        content: [{
            table: {
                headerRows: 2,
                widths: widths,
                dontBreakRows: true,
                body: [cabecera1, cabecera2, ...filasPDF]
            },
            layout: {
                hLineWidth: function(i, node) { return (i === 0 || i === node.table.body.length) ? 0.5 : 0.3; },
                vLineWidth: function(i, node) { return 0.3; },
                hLineColor: function(i, node) { return '#666666'; },
                vLineColor: function(i, node) { return '#999999'; },
                paddingLeft: function(i, node) { return 2; },
                paddingRight: function(i, node) { return 2; },
                paddingTop: function(i, node) { return 2; },
                paddingBottom: function(i, node) { return 2; },
                fillColor: function(i, node) {
                    if (i === 0 || i === 1) return null;
                    const row = node.table.body[i];
                    return (!row) ? null : ((i % 2 === 0) ? '#f9fafb' : null);
                }
            },
            margin: [0, 0, 0, 0]
        }],
        defaultStyle: { fontSize: fontSizeDinamico, alignment: 'center' }
    };

    const fecha = new Date().toISOString().split('T')[0];
    const filename = `Resumen_${datosExport.titulo.replace(/[^a-zA-Z0-9]/g, '_')}_${fecha}`;
    encabezadoPDF(docDefinition, tituloDoc, logoMEC, logoSanta, null, orientacion);
    pdfMake.createPdf(docDefinition).download(filename + '.pdf');
}

function encabezadoPDF(doc, titulo, logoMEC, logoSanta, widthMapCustom, orientacion) {
    const ahora = new Date();
    const fecha = ahora.toLocaleDateString();
    const hora = ahora.toLocaleTimeString();

    // Mantenemos los márgenes finos definidos arriba
    const margenLateral = 15; 
    
    // Encabezado institucional
    doc.header = {
        margin: [margenLateral, 15, margenLateral, 0],
        columns: [
            logoMEC ? { image: logoMEC, width: 60, alignment: 'left' } : { text: '', width: 60 },
            {
                width: '*',
                alignment: 'center',
                stack: [
                    { text: 'DIRECCIÓN GENERAL DE EDUCACIÓN MEDIA', bold: true, fontSize: 9, alignment: 'center' },
                    { text: 'INSTITUCIÓN EDUCATIVA DIOCESANA', bold: true, fontSize: 10, alignment: 'center' },
                    { text: 'SANTA TERESITA', bold: true, fontSize: 12, alignment: 'center' },
                    { text: 'Concepción - Paraguay', fontSize: 8, alignment: 'center' },
                    { text: titulo || '', margin: [0, 4, 0, 0], bold: true, fontSize: 10, alignment: 'center' }
                ]
            },
            logoSanta ? { image: logoSanta, width: 60, alignment: 'right' } : { text: '', width: 60 }
        ]
    };

    // Pie de página
    doc.footer = function(currentPage, pageCount) {
        return {
            margin: [margenLateral, 10, margenLateral, 10],
            columns: [
                { text: 'Generado: ' + fecha + ' ' + hora, alignment: 'left', fontSize: 7, color: '#666666' },
                { text: 'Página ' + currentPage + ' de ' + pageCount, alignment: 'right', fontSize: 7, color: '#666666' }
            ]
        };
    };
}

// ── Imprimir con reportes.js ─────────────────────────────────────────────────

function exportarPrint() {
    if (!datosExport.items?.length) { showMessage('warning', 'No hay datos para imprimir'); return; }
    const orientacion = $('input[name="orient-print"]:checked').val() || 'portrait';
    const tituloDoc   = `${datosExport.titulo} — ${datosExport.subtitulo}`;

    // Clonar la tabla actual del DOM y abrirla en una ventana de impresión
    const tablaHTML = document.getElementById('tabla-resumen').outerHTML;
    const win = window.open('', '_blank');
    win.document.write(`<!DOCTYPE html>
    <html><head><meta charset="utf-8"><title>${tituloDoc}</title>
    <style>
        @page { size: A4 ${orientacion}; margin: 15mm; }
        body  { font-family: Arial, sans-serif; font-size: 8pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444 !important; padding: 3px 5px; font-size: 7.5pt; }
        thead th { font-weight: bold; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        /* Colores de bloque */
        .bloque-sem1 { background-color: #eff6ff !important; }
        thead tr:first-child .bloque-sem1 { background-color: #1d4ed8 !important; color: #fff !important; }
        thead tr:last-child  .bloque-sem1 { background-color: #bfdbfe !important; }
        .bloque-sem2 { background-color: #f0fdf4 !important; }
        thead tr:first-child .bloque-sem2 { background-color: #15803d !important; color: #fff !important; }
        thead tr:last-child  .bloque-sem2 { background-color: #bbf7d0 !important; }
        .bloque-final { background-color: #fefce8 !important; }
        thead tr:first-child .bloque-final { background-color: #a16207 !important; color: #fff !important; }
        thead tr:last-child  .bloque-final { background-color: #fde68a !important; }
        .subtle { color: #6b7280; font-size: 7pt; }
        .asterisco-incompleto { color: #d97706; font-weight: bold; font-size: 8pt; }
    </style></head><body>${tablaHTML}</body></html>`);
    win.document.close();

    encabezadoPrint(win, tituloDoc, logoMEC, logoSanta);
    setTimeout(() => { win.focus(); win.print(); }, 600);
}

// ═══════════════════════════════════════════════════════════════════════════
// CARGA DE DATOS DEL RESUMEN
// ═══════════════════════════════════════════════════════════════════════════

function cargarResumenAlumno() {
    const idAlumno = $('#sel-alumno').val();
    if (!idAlumno) { showMessage('warning', 'Seleccioná un alumno'); return; }

    setLoading(true);
    const anioParam = idAnioSeleccionado ? `&idAnio=${idAnioSeleccionado}` : '';
    fetch(`api/obtener_resumen.php?modo=alumno&idAlumno=${idAlumno}${anioParam}`)
        .then(r => r.json())
        .then(data => {
            setLoading(false);
            if (!data.success) { $('#estado-inicial').show(); showMessage('error', data.message || 'Error'); return; }
            if (!data.data.materias || data.data.materias.length === 0) {
                $('#estado-inicial').html('<i class="bi bi-info-circle me-1"></i> Este alumno no tiene materias disponibles en el año seleccionado.').show();
                return;
            }
            renderAlumno(data.data);
        })
        .catch(() => { setLoading(false); $('#estado-inicial').show(); showMessage('error', 'Error al cargar resumen'); });
}

function cargarResumenMateria() {
    const idAulaMateria = $('#sel-materia').val();
    if (!idAulaMateria) { showMessage('warning', 'Seleccioná una materia'); return; }

    setLoading(true);
    const anioParam = idAnioSeleccionado ? `&idAnio=${idAnioSeleccionado}` : '';
    fetch(`api/obtener_resumen.php?modo=materia&idAulaMateria=${idAulaMateria}${anioParam}`)
        .then(r => r.json())
        .then(data => {
            setLoading(false);
            if (!data.success) { $('#estado-inicial').show(); showMessage('error', data.message || 'Error'); return; }
            // Corrección: modo materia devuelve data.data.alumnos, no data.data.materias
            if (!data.data.alumnos || data.data.alumnos.length === 0) {
                $('#estado-inicial').html('<i class="bi bi-info-circle me-1"></i> Esta materia no tiene alumnos inscriptos en el año seleccionado.').show();
                return;
            }
            renderMateria(data.data);
        })
        .catch(() => { setLoading(false); $('#estado-inicial').show(); showMessage('error', 'Error al cargar resumen'); });
}



// ═══════════════════════════════════════════════════════════════════════════
// EVENTOS
// ═══════════════════════════════════════════════════════════════════════════

$(document).ready(function() {

    cargarAnios();  // carga todos los años y al terminar llama a cargarAulas(idAnio)
    cargarLogos();

    // Cambio de año global: resetea todo y recarga cursos del año elegido
    $('#sel-anio-global').on('change', function() {
        idAnioSeleccionado = $(this).val();

        // Resetear cursos
        ['#sel-aula', '#sel-aula-materia'].forEach(id =>
            $(id).find('option:not(:first)').remove()
        );
        // Resetear selecciones de alumno/materia
        ['#display-alumno','#display-materia'].forEach(id => $(id).val(''));
        ['#sel-alumno','#sel-materia'].forEach(id => $(id).val(''));
        ['#btn-buscar-alumno','#btn-buscar-materia'].forEach(id => $(id).prop('disabled', true));
        ['#btn-modal-alumno','#btn-modal-materia'].forEach(id => $(id).prop('disabled', true));
        listaModalItems = [];

        // Ocultar resultado anterior
        $('#contenido-resumen').hide();
        $('#estado-inicial').text('Seleccioná los filtros para ver el proceso académico.').show();

        // Cargar cursos del año elegido
        if (idAnioSeleccionado) cargarAulas(idAnioSeleccionado);
    });

    // Cambio de modo
    $('.mode-tab').on('click', function() {
        modoActual = $(this).data('mode');
        $('.mode-tab').removeClass('active');
        $(this).addClass('active');
        $('#panel-alumno').toggleClass('d-none', modoActual !== 'alumno');
        $('#panel-materia').toggleClass('d-none', modoActual !== 'materia');
        $('#contenido-resumen').hide();
        $('#estado-inicial').text('Seleccioná los filtros para ver el proceso académico.').show();
        // Limpiar selecciones al cambiar modo
        ['#display-alumno','#display-materia'].forEach(id => $(id).val(''));
        ['#sel-alumno','#sel-materia'].forEach(id => $(id).val(''));
        ['#btn-buscar-alumno','#btn-buscar-materia'].forEach(id => $(id).prop('disabled', true));
    });

    $('.mode-tab.active').trigger('click');

    // Cambio de curso — modo alumno
    $('#sel-aula').on('change', function() {
        cargarAlumnosModal(this.value);
    });

    // Cambio de curso — modo materia
    $('#sel-aula-materia').on('change', function() {
        cargarMateriasModal(this.value);
    });

    // Botones abrir modal
    $('#btn-modal-alumno').on('click', function() {
        abrirModal('alumno');
    });
    $('#btn-modal-materia').on('click', function() {
        abrirModal('materia');
    });

    // Botones buscar
    $('#btn-buscar-alumno').on('click',  cargarResumenAlumno);
    $('#btn-buscar-materia').on('click', cargarResumenMateria);

    // Botones de exportación
    $('#btn-excel').on('click', exportarExcel);
    $('#btn-pdf').on('click',   exportarPDF);
    $('#btn-print').on('click', exportarPrint);
});
</script>
