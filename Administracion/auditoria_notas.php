<?php
$ruta = '../';
include __DIR__ . '/includes/header.php';
?>

<!-- DataTables -->
<link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css">
<script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>
<script src="<?php echo $ruta; ?>dt/jquery.dataTables.min.js"></script>
<script src="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.js"></script>

<style>
.main-content { display: block; }
.page-title { margin-bottom: 22px; }
.page-title h2 { font-size: 1.35rem; font-weight: 700; margin: 0; color: #111827; }
.page-title p  { margin: 4px 0 0; color: #6b7280; font-size: 13px; }

/* ── Filtros ── */
.filtros-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 18px 20px;
    margin-bottom: 20px;
}
.filtros-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 14px;
}
@media (max-width: 1100px) { .filtros-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 700px)  { .filtros-grid { grid-template-columns: repeat(2, 1fr); } }
.filtros-grid label { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; display: block; }

/* ── Resumen ── */
.resumen-bar {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 9px 16px;
    font-size: 13px;
    color: #495057;
    margin-bottom: 16px;
}
.resumen-bar strong { color: #111827; }

/* ── Tabla ── */
#tabla-auditoria thead th {
    background: #f8f9fa; font-size: 12px; font-weight: 600; color: #495057; vertical-align: middle;
}
#tabla-auditoria td { vertical-align: middle; font-size: 13px; }

.badge-insert { background:#dbeafe; color:#1e40af; border-radius:999px; padding:3px 10px; font-size:11px; font-weight:700; }
.badge-update { background:#fef3c7; color:#92400e; border-radius:999px; padding:3px 10px; font-size:11px; font-weight:700; }

.cambio-valor { font-family: 'Courier New', monospace; font-size:12.5px; }
.cambio-flecha { color:#9ca3af; margin: 0 4px; }
.valor-antes  { color:#b91c1c; }
.valor-despues{ color:#047857; font-weight:700; }

.ip-chip { font-size: 10.5px; color:#9ca3af; }

.empty-state { text-align:center; padding:48px 0; color:#adb5bd; }
.empty-state i { font-size:3rem; display:block; margin-bottom:12px; }
.empty-state p { font-size:14px; margin:0; }
</style>

<div class="main-content">

    <div class="page-title">
        <h2><i class="bi bi-clock-history me-2 text-primary"></i>Auditoría de Notas</h2>
        <p>Historial de cambios en calificaciones: quién, cuándo, y qué valor tenía antes y después.</p>
    </div>

    <!-- Filtros -->
    <div class="filtros-card">
        <div class="filtros-grid">
            <div>
                <label>Curso</label>
                <select id="f-curso" class="form-select form-select-sm">
                    <option value="">-- Todos los cursos --</option>
                </select>
            </div>
            <div>
                <label>Materia</label>
                <select id="f-materia" class="form-select form-select-sm" disabled>
                    <option value="">-- Todas las materias --</option>
                </select>
            </div>
            <div>
                <label>Docente</label>
                <select id="f-docente" class="form-select form-select-sm">
                    <option value="">-- Todos los docentes --</option>
                </select>
            </div>
            <div>
                <label>Desde</label>
                <input type="date" id="f-desde" class="form-control form-control-sm">
            </div>
            <div>
                <label>Hasta</label>
                <input type="date" id="f-hasta" class="form-control form-control-sm">
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-end">
            <div style="max-width:320px; width:100%;">
                <label style="font-size:12px; font-weight:600; color:#374151;">Buscar alumno</label>
                <input type="text" id="f-alumno" class="form-control form-control-sm" placeholder="Nombre, apellido o cédula...">
            </div>
            <div>
                <button type="button" id="btn-limpiar-filtros" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar Filtros
                </button>
                <button type="button" id="btn-buscar" class="btn btn-primary btn-sm px-4">
                    <i class="bi bi-search me-1"></i> Buscar
                </button>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div id="resumen-bar" class="resumen-bar d-none"></div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-auditoria" class="table table-striped table-bordered table-sm" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Alumno</th>
                            <th>Curso</th>
                            <th>Materia / Evaluación</th>
                            <th class="text-center">Acción</th>
                            <th class="text-center">Cambio</th>
                            <th>Realizado por</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-auditoria"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
let dtAuditoria = null;

function escapeHtml(text) {
    return String(text || '').replace(/[&<>"'`]/g, function(c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;'}[c];
    });
}

function showMessage(type, text) {
    if (typeof alertify !== 'undefined') {
        alertify[type === 'success' ? 'success' : type === 'error' ? 'error' : 'warning'](text);
    } else { alert(text); }
}

function getFechaHoy()        { return new Date().toISOString().split('T')[0]; }
function getFechaHace30Dias() { const d = new Date(); d.setDate(d.getDate() - 30); return d.toISOString().split('T')[0]; }

// ── Cargar filtros (cursos, docentes) ──
function cargarFiltrosIniciales() {
    fetch('api/auditoria_notas_filtros.php')
        .then(r => r.json())
        .then(res => {
            if (!res.success) { showMessage('error', res.message); return; }

            let optsCurso = '<option value="">-- Todos los cursos --</option>';
            res.data.cursos.forEach(c => {
                optsCurso += `<option value="${c.idAula}">${escapeHtml(c.curso)}</option>`;
            });
            $('#f-curso').html(optsCurso);

            let optsDoc = '<option value="">-- Todos los docentes --</option>';
            res.data.docentes.forEach(d => {
                optsDoc += `<option value="${d.idUsuario}">${escapeHtml(d.nombre)}</option>`;
            });
            $('#f-docente').html(optsDoc);
        })
        .catch(() => showMessage('error', 'Error al cargar filtros'));
}

// ── Cascada curso -> materia ──
$('#f-curso').change(function() {
    const idAula = $(this).val();
    $('#f-materia').html('<option value="">-- Todas las materias --</option>').prop('disabled', !idAula);
    if (!idAula) return;

    fetch(`api/auditoria_notas_materias.php?idAula=${idAula}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            let opts = '<option value="">-- Todas las materias --</option>';
            res.data.forEach(m => {
                opts += `<option value="${m.idAulaMateria}">${escapeHtml(m.materia)}</option>`;
            });
            $('#f-materia').html(opts).prop('disabled', false);
        });
});

// ── Buscar / aplicar filtros ──
function buscarAuditoria() {
    const idAula        = $('#f-curso').val();
    const idAulaMateria = $('#f-materia').val();
    const idUsuario      = $('#f-docente').val();
    const alumno         = $('#f-alumno').val().trim();
    const desde          = $('#f-desde').val();
    const hasta          = $('#f-hasta').val();

    if (desde && hasta && desde > hasta) {
        showMessage('warning', 'La fecha "Desde" no puede ser mayor que "Hasta"');
        return;
    }

    const params = new URLSearchParams({ idAula, idAulaMateria, idUsuario, alumno, desde, hasta });

    fetch(`api/auditoria_notas_data.php?${params.toString()}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                showMessage('error', res.message);
                renderTabla([]);
                return;
            }
            renderResumen(res.data);
            renderTabla(res.data.registros);
        })
        .catch(() => showMessage('error', 'Error al cargar la auditoría'));
}

function renderResumen(data) {
    $('#resumen-bar').removeClass('d-none').html(
        `<i class="bi bi-info-circle me-1"></i>
         <strong>${data.total}</strong> registro(s) entre <strong>${data.desde}</strong> y <strong>${data.hasta}</strong>.
         ${data.total >= 500 ? ' Mostrando los 500 más recientes — afiná los filtros para ver un rango más específico.' : ''}`
    );
}

function renderTabla(registros) {
    if (dtAuditoria) { dtAuditoria.destroy(); dtAuditoria = null; }

    if (!registros || registros.length === 0) {
        $('#tbody-auditoria').html('<tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><p>No se encontraron cambios de notas con estos filtros</p></div></td></tr>');
        return;
    }

    let html = '';

    registros.forEach(reg => {
        const accionBadge = reg.accion === 'INSERT'
            ? '<span class="badge-insert"><i class="bi bi-plus-circle-fill me-1"></i>Nueva</span>'
            : '<span class="badge-update"><i class="bi bi-pencil-fill me-1"></i>Modificada</span>';

        const valorAntes   = reg.valor_antes !== null ? reg.valor_antes : '—';
        const valorDespues = reg.valor_despues !== null ? reg.valor_despues : '—';
        const cambio = `<span class="cambio-valor">
                            <span class="valor-antes">${valorAntes}</span>
                            <span class="cambio-flecha">→</span>
                            <span class="valor-despues">${valorDespues}</span>
                            <span class="text-muted">/ ${reg.puntos_total}</span>
                        </span>`;

        const fecha = new Date(reg.fecha.replace(' ', 'T')).toLocaleString('es-PY', {
            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        html += `
        <tr>
            <td>${fecha}</td>
            <td>${escapeHtml(reg.alumno)}<br><small class="text-muted">${escapeHtml(reg.alumno_cedula || '')}</small></td>
            <td>${escapeHtml(reg.curso)}</td>
            <td>${escapeHtml(reg.materia)}<br><small class="text-muted">${escapeHtml(reg.evaluacion)}</small></td>
            <td class="text-center">${accionBadge}</td>
            <td class="text-center">${cambio}</td>
            <td>${escapeHtml(reg.quien)}${reg.ip ? `<br><span class="ip-chip">${escapeHtml(reg.ip)}</span>` : ''}</td>
        </tr>`;
    });

    $('#tbody-auditoria').html(html);
    dtAuditoria = $('#tabla-auditoria').DataTable({
        language: { url: '../dt/es-ES.json' },
        paging: true, searching: true, ordering: true, info: true,
        order: [] // mantiene el orden ya dado por el backend (fecha DESC)
    });
}

// ── Limpiar filtros ──
$('#btn-limpiar-filtros').click(function() {
    $('#f-curso').val('');
    $('#f-materia').html('<option value="">-- Todas las materias --</option>').prop('disabled', true);
    $('#f-docente').val('');
    $('#f-alumno').val('');
    $('#f-desde').val(getFechaHace30Dias());
    $('#f-hasta').val(getFechaHoy());
    buscarAuditoria();
});

$('#btn-buscar').click(buscarAuditoria);
$('#f-alumno').on('keypress', function(e) { if (e.which === 13) buscarAuditoria(); });

// ── Carga inicial ──
$(document).ready(function() {
    $('#f-desde').val(getFechaHace30Dias());
    $('#f-hasta').val(getFechaHoy());
    cargarFiltrosIniciales();
    buscarAuditoria();
});
</script>
