<?php
$ruta = '../';
include __DIR__ . '/includes/header.php';
?>

<script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>

<style>
.main-content { display: block; }

.page-title { margin-bottom: 20px; }
.page-title h2 { font-size: 1.35rem; font-weight: 700; margin: 0; color: #111827; }
.page-title p  { margin: 4px 0 0; color: #6b7280; font-size: 13px; }

/* Tarjetas de materia */
.materias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.materia-card {
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
}
.materia-card:hover  { border-color: #6366f1; box-shadow: 0 2px 8px rgba(99,102,241,.15); }
.materia-card.active { border-color: #4f46e5; background: #eef2ff; }
.materia-card h5 { margin: 0 0 4px; font-size: 14px; font-weight: 700; color: #111827; }
.materia-card p  { margin: 0; font-size: 12px; color: #6b7280; }
.badge-pendientes {
    display: inline-block;
    background: #fef3c7;
    color: #92400e;
    border-radius: 999px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 700;
    margin-top: 8px;
}

/* Panel de alumnos */
.alumnos-panel {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}
.alumnos-panel-header {
    background: #111827;
    color: #fff;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.alumnos-panel-header h5 { margin: 0; font-size: 14px; font-weight: 700; }
.alumnos-panel-header p  { margin: 0; font-size: 12px; opacity: .75; }

/* Tabla */
.tabla-rec thead th {
    background: #1f2937;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    text-align: center;
    border-color: #374151 !important;
    vertical-align: middle;
}
.tabla-rec td {
    font-size: 13px;
    vertical-align: middle;
    text-align: center;
    border-color: #e5e7eb !important;
}
.tabla-rec td.td-alumno { text-align: left; font-weight: 600; color: #111827; }
.subtle { color: #9ca3af; font-size: 11px; }

/* Notas */
.nota-alta  { color: #047857; font-weight: 700; }
.nota-baja  { color: #b91c1c; font-weight: 700; }
.nota-media { color: #b45309; font-weight: 700; }

/* Badges estado */
.badge-soft       { border-radius: 999px; padding: 3px 9px; font-size: 11px; font-weight: 700; }
.badge-rec        { background: #fff7ed; color: #c2410c; }
.badge-rec2       { background: #fdf2f8; color: #86198f; }
.badge-aprobado   { background: #ecfdf5; color: #047857; }
.badge-reprobado  { background: #fef2f2; color: #b91c1c; }

/* Input nota */
.input-nota {
    width: 64px;
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 4px 6px;
}
.input-nota:focus { border-color: #4f46e5; outline: none; box-shadow: 0 0 0 2px rgba(79,70,229,.2); }

/* Empty / spinner */
.empty-state {
    text-align: center;
    padding: 50px 16px;
    color: #9ca3af;
    font-size: 14px;
}
#spinner-alumnos { display: none; text-align: center; padding: 36px 0; color: #6b7280; }
#spinner-materias { display: none; text-align: center; padding: 36px 0; color: #6b7280; }

@media(max-width:600px) {
    .materias-grid { grid-template-columns: 1fr; }
}
</style>

<div class="main-content">

    <div class="page-title">
        <h2><i class="bi bi-journal-check me-2 text-warning"></i>Recuperatorios</h2>
        <p>Cargá las notas de recuperatorio de tus alumnos habilitados.</p>
    </div>

    <!-- Materias con pendientes -->
    <div id="spinner-materias">
        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
        <span class="ms-2">Cargando materias...</span>
    </div>

    <div id="contenedor-materias"></div>

    <!-- Panel alumnos -->
    <div id="panel-alumnos" style="display:none;">
        <div class="alumnos-panel">
            <div class="alumnos-panel-header">
                <div>
                    <h5 id="panel-titulo">-</h5>
                    <p id="panel-subtitulo">-</p>
                </div>
            </div>
            <div class="p-0">
                <div id="spinner-alumnos" class="p-4">
                    <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                    <span class="ms-2">Cargando alumnos...</span>
                </div>
                <div id="contenedor-tabla"></div>
            </div>
        </div>
    </div>

    <div class="empty-state" id="estado-inicial">
        <i class="bi bi-award" style="font-size:2.5rem;display:block;margin-bottom:10px;"></i>
        No tenés materias con recuperatorios pendientes.
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
let materiaActiva = null;

function showMessage(type, text) {
    if (typeof alertify !== 'undefined') {
        alertify[type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'error'](text);
    } else { alert(text); }
}

function fmtNota(nota) {
    if (nota === null || nota === undefined) return '<span class="subtle">-</span>';
    const n = parseInt(nota);
    const cls = n >= 3 ? 'nota-alta' : 'nota-baja';
    return `<span class="${cls}">${n}</span>`;
}

function fmtEstado(estado) {
    const mapa = {
        'Recuperatorio1': ['badge-rec',       '1° Recuperatorio'],
        'Recuperatorio2': ['badge-rec2',      '2° Recuperatorio'],
        'Aprobado'      : ['badge-aprobado',  'Aprobado'],
        'Reprobado'     : ['badge-reprobado', 'Reprobado'],
    };
    const [cls, label] = mapa[estado] || ['badge-soft', estado];
    return `<span class="badge-soft ${cls}">${label}</span>`;
}

// ── Cargar materias con pendientes ────────────────────────
function cargarMaterias() {
    $('#spinner-materias').show();
    $('#contenedor-materias, #panel-alumnos, #estado-inicial').hide();

    fetch('api/materias_recuperatorio.php')
        .then(r => r.json())
        .then(data => {
            $('#spinner-materias').hide();
            if (!data.success) { showMessage('error', data.message); return; }

            if (!data.data || data.data.length === 0) {
                $('#estado-inicial').show();
                return;
            }

            let html = '<div class="materias-grid">';
            data.data.forEach(mat => {
                html += `
                <div class="materia-card" data-id="${mat.idAulaMateria}"
                     data-nombre="${mat.materia}" data-curso="${mat.curso}">
                    <h5>${mat.materia}</h5>
                    <p>${mat.curso}</p>
                    <span class="badge-pendientes">
                        <i class="bi bi-person-exclamation me-1"></i>
                        ${mat.total_pendientes} pendiente(s)
                    </span>
                </div>`;
            });
            html += '</div>';

            $('#contenedor-materias').html(html).show();
        })
        .catch(() => {
            $('#spinner-materias').hide();
            showMessage('error', 'Error al cargar materias');
        });
}

// ── Click en tarjeta de materia ───────────────────────────
$(document).on('click', '.materia-card', function () {
    $('.materia-card').removeClass('active');
    $(this).addClass('active');

    const idAulaMateria = $(this).data('id');
    const nombre        = $(this).data('nombre');
    const curso         = $(this).data('curso');

    materiaActiva = { idAulaMateria, nombre, curso };

    $('#panel-titulo').text(nombre);
    $('#panel-subtitulo').text(curso);
    $('#panel-alumnos').show();

    cargarAlumnos(idAulaMateria);
});

// ── Cargar alumnos en recuperatorio ──────────────────────
function cargarAlumnos(idAulaMateria) {
    $('#spinner-alumnos').show();
    $('#contenedor-tabla').empty();

    fetch(`api/get_recuperatorios.php?idAulaMateria=${idAulaMateria}`)
        .then(r => r.json())
        .then(data => {
            $('#spinner-alumnos').hide();
            if (!data.success) { showMessage('error', data.message); return; }

            if (!data.data || data.data.length === 0) {
                $('#contenedor-tabla').html('<div class="empty-state">Sin alumnos pendientes en esta materia.</div>');
                return;
            }

            let html = `
            <div class="table-responsive">
            <table class="table table-bordered tabla-rec mb-0">
                <thead>
                    <tr>
                        <th style="text-align:left;min-width:200px;">Alumno</th>
                        <th>NP Sem 1</th>
                        <th>NP Sem 2</th>
                        <th>Nota Final</th>
                        <th>Rec 1</th>
                        <th>Rec 2</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>`;

            data.data.forEach(alu => {
                const esRec1 = alu.estado_final === 'Recuperatorio1';
                const esRec2 = alu.estado_final === 'Recuperatorio2';

                // Input solo si corresponde y aún no fue cargado
                let accion = '<span class="subtle">-</span>';
                if (esRec1 && alu.nota_rec1 === null) {
                    accion = `
                    <div class="d-flex align-items-center gap-2 justify-content-center">
                        <input type="number" class="input-nota" min="1" max="5"
                               placeholder="1-5"
                               data-id="${alu.idNotaFinal}"
                               data-aulamateria="${idAulaMateria}">
                        <button class="btn btn-sm btn-primary btn-guardar-rec"
                                data-id="${alu.idNotaFinal}"
                                data-aulamateria="${idAulaMateria}">
                            <i class="bi bi-check-lg"></i>
                        </button>
                    </div>`;
                } else if (esRec2 && alu.nota_rec2 === null) {
                    accion = `
                    <div class="d-flex align-items-center gap-2 justify-content-center">
                        <input type="number" class="input-nota" min="1" max="5"
                               placeholder="1-5"
                               data-id="${alu.idNotaFinal}"
                               data-aulamateria="${idAulaMateria}">
                        <button class="btn btn-sm btn-warning btn-guardar-rec"
                                data-id="${alu.idNotaFinal}"
                                data-aulamateria="${idAulaMateria}">
                            <i class="bi bi-check-lg"></i>
                        </button>
                    </div>`;
                }

                html += `
                <tr id="fila-${alu.idNotaFinal}">
                    <td class="td-alumno">
                        ${alu.apellidos}, ${alu.nombres}
                        <br><span class="subtle">CI: ${alu.cedula || 'S/N'}</span>
                    </td>
                    <td>${fmtNota(alu.nota_sem1)}</td>
                    <td>${fmtNota(alu.nota_sem2)}</td>
                    <td>${fmtNota(alu.nota_final)}</td>
                    <td>${fmtNota(alu.nota_rec1)}</td>
                    <td>${fmtNota(alu.nota_rec2)}</td>
                    <td>${fmtEstado(alu.estado_final)}</td>
                    <td>${accion}</td>
                </tr>`;
            });

            html += '</tbody></table></div>';
            $('#contenedor-tabla').html(html);
        })
        .catch(() => {
            $('#spinner-alumnos').hide();
            showMessage('error', 'Error al cargar alumnos');
        });
}

// ── Guardar recuperatorio ─────────────────────────────────
$(document).on('click', '.btn-guardar-rec', function () {
    const idNotaFinal   = $(this).data('id');
    const idAulaMateria = $(this).data('aulamateria');
    const nota          = parseInt($(`.input-nota[data-id="${idNotaFinal}"]`).val());

    if (isNaN(nota) || nota < 1 || nota > 5) {
        showMessage('warning', 'Ingresá una nota entre 1 y 5');
        return;
    }

    const btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    fetch('api/guardar_recuperatorio.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idNotaFinal, idAulaMateria, nota })
    })
    .then(r => r.json())
    .then(data => {
        btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i>');
        if (!data.success) { showMessage('error', data.message); return; }

        showMessage('success', 'Recuperatorio registrado correctamente');

        // Recargar tabla para reflejar nuevo estado
        setTimeout(() => cargarAlumnos(idAulaMateria), 600);
    })
    .catch(() => {
        btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i>');
        showMessage('error', 'Error al guardar');
    });
});

// ── Init ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', cargarMaterias);
</script>
