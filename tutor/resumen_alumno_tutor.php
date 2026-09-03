<?php
    $ruta = '../';

    // Parámetros recibidos desde el menú del tutor
    $preIdAlumno = isset($_GET['idAlumno']) ? intval($_GET['idAlumno']) : 0;
    $preNombreAl = isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : '';
    $preIdAula = isset($_GET['idAula']) ? intval($_GET['idAula']) : 0;

    // Bloquear acceso directo sin parámetros válidos
    if ($preIdAlumno === 0 || $preIdAula === 0) {
        header('Location: ' . $ruta . 'tutor/menuTutor.php');
        exit;
    }

    include $ruta . "tutor/includes/header.php";
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
<link rel="stylesheet" href="<?php echo $ruta; ?>css/stylesTutores.css">

<div class="main-content">
<div class="resumen-shell">

    <div class="page-title">
        <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary btn-volver">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <div>
            <h2>Resumen académico</h2>
            <p id="resumen-subtitulo-page" style="margin:4px 0 0; color:#6b7280; font-size:13px;">Cargando datos del alumno...</p>
        </div>
    </div>

    <div id="spinner-resumen">
        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
        <span class="ms-2">Cargando resumen...</span>
    </div>

    <div id="contenido-resumen" style="display:none;">

        <div class="summary-head">
            <div>
                <h3 id="resumen-titulo"></h3>
                <p  id="resumen-subtitulo"></p>
            </div>
            <div class="meta-row" id="resumen-meta"></div>
        </div>

        <div class="export-bar">
            <div class="export-group">
                <span class="export-group-label"><i class="bi bi-file-earmark-excel text-success me-1"></i> Excel</span>
                <button id="btn-excel" class="btn btn-success btn-sm"><i class="bi bi-download me-1"></i> Descargar</button>
            </div>
            <div class="export-divider"></div>
            <div class="export-group">
                <span class="export-group-label"><i class="bi bi-file-earmark-pdf text-danger me-1"></i> PDF</span>
                <div class="orientacion-opts">
                    <label><input type="radio" name="orient-pdf" value="portrait" checked> Vertical</label>
                    <label><input type="radio" name="orient-pdf" value="landscape"> Horizontal</label>
                </div>
                <button id="btn-pdf" class="btn btn-danger btn-sm"><i class="bi bi-download me-1"></i> Descargar</button>
            </div>
            <div class="export-divider"></div>
            <div class="export-group">
                <span class="export-group-label"><i class="bi bi-printer me-1"></i> Imprimir</span>
                <div class="orientacion-opts">
                    <label><input type="radio" name="orient-print" value="portrait" checked> Vertical</label>
                    <label><input type="radio" name="orient-print" value="landscape"> Horizontal</label>
                </div>
                <button id="btn-print" class="btn btn-secondary btn-sm"><i class="bi bi-printer me-1"></i> Imprimir</button>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table table-bordered tabla-resumen" id="tabla-resumen">
                <thead id="thead-resumen"></thead>
                <tbody id="tbody-resumen"></tbody>
            </table>
        </div>

    </div>

    <div class="empty-state" id="estado-inicial" style="display:none;">
        <i class="bi bi-info-circle me-1"></i> No se encontraron datos académicos para este alumno en el año lectivo activo.
    </div>

</div>
</div>

<?php include $ruta . "tutor/includes/footer.php"; ?>

<script>
// Constantes inyectadas desde PHP
const PRE_ID_ALUMNO = <?php echo $preIdAlumno; ?>;
const PRE_NOMBRE = <?php echo json_encode($preNombreAl); ?>;
const PRE_ID_AULA = <?php echo $preIdAula; ?>;

// Estado global
let datosExport = {};
let dtInstance = null;
let logoMEC = '';
let logoSanta = '';

// Logos
function imgToBase64(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = function () {
            const canvas = document.createElement('canvas');
            canvas.width = img.width; canvas.height = img.height;
            canvas.getContext('2d').drawImage(img, 0, 0);
            resolve(canvas.toDataURL('image/png'));
        };
        img.onerror = () => reject(new Error('No se pudo cargar ' + url));
        img.src = url + '?t=' + Date.now();
    });
}
async function cargarLogos() {
    try {
        [logoMEC, logoSanta] = await Promise.all([
            imgToBase64('../img/logo-mec.png'),
            imgToBase64('../img/logo-Santa.jpeg'),
        ]);
    } catch(e) { console.warn('Logos no disponibles:', e.message); }
}

// Utilidades de formato y renderizado
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
    if (/escrita/i.test(nombre)) return 'E';
    if (/trabajo/i.test(nombre)) return 'T';
    if (/primera|1ra/i.test(nombre)) return 'PP';
    if (/segunda|2da/i.test(nombre)) return 'SP';
    if (/examen/i.test(nombre)) return 'EF';
    if (/exposici/i.test(nombre)) return 'Ex';
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
    if (!completo) return `<span title="Evaluaciones sin calificar">${valor}<sup class="asterisco-incompleto">*</sup></span>`;
    return valor;
}
function fmtAsist(asist) {
    if (!asist || asist.pct === null || asist.pct === undefined) return '<span class="subtle">-</span>';
    const cls = asist.pct >= 75 ? 'nota-alta' : asist.pct >= 60 ? 'nota-media' : 'nota-baja';
    return `<span class="${cls}">${asist.pct}%</span><br><span class="subtle">${asist.presentes}P / ${asist.ausentes}A</span>`;
}
function fmtEstado(estado) {
    const mapa = {
        'Regular':'badge-regular','Irregular':'badge-irregular','Aprobado':'badge-regular',
        'Recuperatorio1':'badge-rec','Recuperatorio2':'badge-rec',
        'Reprobado':'badge-irregular','Pendiente':'badge-pendiente',
    };
    return `<span class="badge-soft ${mapa[estado]||'badge-pendiente'}">${estado||'Pendiente'}</span>`;
}
function fmtNotaFinal(nf) {
    if (!nf) return '<span class="subtle">-</span>';
    const nota = nf.nota_definitiva ?? nf.nota_final;
    if (nota === null || nota === undefined) return '<span class="subtle">-</span>';
    return `<span class="${nota >= 3 ? 'nota-alta' : 'nota-baja'}">${nota}</span>`;
}

function construirEncabezado(labelPrincipal, periodos, tipos) {
    let th1 = `<tr><th rowspan="2">${labelPrincipal}</th><th rowspan="2">Asistencia</th>`;
    let th2 = '<tr>';

    periodos.forEach((per, idx) => {
        const bloque = idx === 0 ? 'bloque-sem1' : 'bloque-sem2';
        const cols = tipos.length + 2; 
        
        th1 += `<th colspan="${cols}" class="${bloque}">${per.nombre}</th>`;
        
        // Tipos de nota
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
        tds += `<td class="bloque-final">${nf?.nota_rec1 !== null && nf?.nota_rec1 !== undefined ? fmtNota(nf.nota_rec1) : '<span class="subtle">-</span>'}</td>`;
        tds += `<td class="bloque-final">${nf?.nota_rec2 !== null && nf?.nota_rec2 !== undefined ? fmtNota(nf.nota_rec2) : '<span class="subtle">-</span>'}</td>`;
        tds += `<td class="bloque-final">${fmtNotaFinal(nf)}</td>`;
        tds += `<td class="bloque-final">${fmtEstado(nf ? nf.estado_final : item.estado)}</td>`;

        html += `<tr>${tds}</tr>`;
    });

    $('#tbody-resumen').html(html || '<tr><td colspan="99" class="py-4 text-muted text-center">Sin datos</td></tr>');
}

// Inicializar DataTable sobre la tabla ya rendereada
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
        paging : false,
        searching: false,
        ordering : false,
        info : false,
        destroy : true,
    });
}

// RENDER RESULTADOS
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

// Carga del resumen año lectivo activo, alumno y aula pre-definidos
function cargarResumenAlumno() {
    setLoading(true);
    fetch(`api/resumen_alumno.php?modo=alumno&idAlumno=${PRE_ID_ALUMNO}`)
        .then(r => r.json())
        .then(data => {
            setLoading(false);
            if (!data.success) {
                $('#estado-inicial').show();
                showMessage('error', data.message || 'Error al cargar el resumen');
                return;
            }
            if (!data.data.materias || data.data.materias.length === 0) {
                $('#estado-inicial').show();
                return;
            }
            // Mostrar nombre del alumno y su curso en el subtítulo de la cabecera
            if (data.data.alumno) {
                const al = data.data.alumno;
                const cursoTxt = [al.numCurso ? al.numCurso + '°' : '', al.enfasis].filter(Boolean).join(' - ');
                $('#resumen-subtitulo-page').text(`${al.apellidos}, ${al.nombres}` + (cursoTxt ? ' — ' + cursoTxt : ''));
            }
            renderAlumno(data.data);
        })
        .catch(() => {
            setLoading(false);
            $('#estado-inicial').show();
            showMessage('error', 'Error de conexión al cargar el resumen');
        });
}

// INICIO
$(document).ready(function() {
    cargarLogos();
    $('#btn-excel').on('click', function() {
        showMessage('warning', 'La exportación a Excel aún no está habilitada para este resumen.');
    });
    $('#btn-pdf').on('click', function() {
        showMessage('warning', 'La exportación a PDF aún no está habilitada para este resumen.');
    });
    $('#btn-print').on('click', function() {
        window.print();
    });
    cargarResumenAlumno();
});
</script>