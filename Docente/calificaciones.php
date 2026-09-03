<?php
/**
 * ARCHIVO: Docente/calificaciones.php
 */
    $ruta = "../";
    include __DIR__ . '/includes/header.php';
    require_once __DIR__ . "/../servicios/conexion.php";

    $docente_id = $_SESSION['docente_id'];
    if (!$docente_id) { header('Location: index.php'); exit; }

    $sql_aula = "SELECT DISTINCT a.idAula, CONCAT(c.numero, '° ', e.nombre, ' - ', t.turno) as curso
            FROM docente_aula_materia dam
            JOIN aula_materia am ON dam.idAulaMateria = am.idAulaMateria
            JOIN aula a ON am.idAula = a.idAula
            JOIN curso c ON a.idCurso = c.idCurso
            JOIN enfasis e ON a.idEnfasis = e.idEnfasis
            JOIN turno t ON c.idTurno = t.idTurno
            JOIN anio_lectivo an ON a.idAnio = an.idAnio
            WHERE dam.idDocente = $docente_id 
            AND dam.activo = 1 
            AND a.activo = 'Sí'
            AND an.activo = 'Sí'
            ORDER BY c.numero, e.nombre";
    $lista_aula = buscar_datos($sql_aula);

    $sql_anio = "SELECT idAnio, anio FROM anio_lectivo WHERE activo = 'Sí' LIMIT 1";
    $anio_activo = buscar_datos($sql_anio);
    $año_lectivo = $anio_activo[0]['anio'] ?? '-';

    $sql_periodo = "SELECT idPeriodo, nombre FROM periodo WHERE activo = 'Sí' LIMIT 1";
    $periodo_activo = buscar_datos($sql_periodo);
    $periodo_nombre = $periodo_activo[0]['nombre'] ?? null;
    $idPeriodo      = $periodo_activo[0]['idPeriodo'] ?? null;

    $fecha_hoy_input    = date('Y-m-d');
    $fecha_60_dias_atras = date('Y-m-d', strtotime('-60 days'));
?>

<link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css">
<script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>
<script src="<?php echo $ruta; ?>dt/jquery.dataTables.min.js"></script>
<script src="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.js"></script>

<style>
.tabs-wrapper { display: flex; justify-content: center; margin-bottom: 24px; }
.tab-group { display: flex; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; }
.tab-btn {
    flex: 0 1 auto; padding: 11px 20px; border: none;
    background: #fff; color: #6c757d; font-size: 14px; font-weight: 400;
    cursor: pointer; display: flex; align-items: center; gap: 8px;
    transition: background .15s, color .15s;
}
.tab-btn + .tab-btn { border-left: 1px solid #dee2e6; }
.tab-btn.active { background: #0d6efd; color: #fff; font-weight: 500; }
.tab-btn:not(.active):hover { background: #f8f9fa; }
.panel { display: none; }
.panel.active { display: block; }
.form-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px; margin-bottom: 16px;
}
@media (max-width: 900px) { .form-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 540px) { .form-grid { grid-template-columns: 1fr; } }
.info-bar {
    background: #e8f4fd; border: 1px solid #b6d4fe;
    border-radius: 8px; padding: 10px 16px; font-size: 13px;
    color: #0a58ca; display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 16px;
}
.info-bar strong { color: #084298; }
.card { border-radius: 14px; border: none; }
.card-header-custom {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    color: white; border-radius: 14px 14px 0 0; padding: 18px;
}
.table th { background: #f8f9fa; vertical-align: middle; }
.table td { vertical-align: middle; font-size: 13px; }
.badge-registrado { background: #d1e7dd !important; color: #0a3622 !important; }
.badge-pendiente  { background: #fff3cd !important; color: #664d03 !important; }
.empty-state { text-align: center; padding: 48px 0; color: #adb5bd; }
.empty-state i { font-size: 3rem; display: block; margin-bottom: 12px; }
.empty-state p { font-size: 14px; margin: 0; }
.alert-periodo-cerrado {
    background: #fef3c7; border: 1px solid #fde68a; color: #92400e;
    border-radius: 10px; padding: 10px 16px; font-size: 13px;
    margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
}
/* Fila sin nota previa en modo editar */
.fila-sin-nota td { background: #fffbeb; }
.aviso-sin-nota { font-size: 11px; color: #b45309; }
</style>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Registro de Calificaciones</h2>
            <small class="text-muted">
                Año: <strong><?php echo $año_lectivo; ?></strong> |
                Período: <strong><?php echo $periodo_nombre ?? 'Sin período activo'; ?></strong>
            </small>
        </div>
        <a href="evaluaciones.php" class="badge bg-info text-decoration-none" style="font-size:14px;padding:8px 12px;">
            <i class="bi bi-pencil"></i> Crear Evaluaciones
        </a>
    </div>

    <?php if (!$idPeriodo): ?>
    <div class="alert-periodo-cerrado">
        <i class="bi bi-exclamation-triangle-fill"></i>
        No hay un período activo. Solo podés editar calificaciones si el Director habilitó una excepción para tu materia.
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="tabs-wrapper">
        <div class="tab-group">
            <button class="tab-btn active" id="tab-nueva" onclick="switchTab('nueva')">
                <i class="bi bi-plus-circle"></i> Nueva Calificación
            </button>
            <button class="tab-btn" id="tab-historial" onclick="switchTab('historial')">
                <i class="bi bi-clock-history"></i> Historial
            </button>
            <button class="tab-btn d-none" id="tab-editar" onclick="switchTab('editar')">
                <i class="bi bi-pencil-square"></i> Editar
            </button>
        </div>
    </div>

    <!-- ══ PANEL: NUEVA CALIFICACIÓN ══ -->
    <div class="panel active" id="panel-nueva">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-4 fw-semibold">
                    <i class="bi bi-plus-circle text-primary"></i> Registrar Nueva Calificación
                </h5>

                <div class="form-grid">
                    <div>
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            <span class="badge bg-primary me-1" style="font-size:10px;">1</span> Curso
                        </label>
                        <select id="select-curso" class="form-select form-select-sm">
                            <option value="">-- Seleccione un curso --</option>
                            <?php if($lista_aula): foreach($lista_aula as $aula): ?>
                            <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['curso']; ?></option>
                            <?php endforeach; endif; ?>
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
                            <span class="badge bg-primary me-1" style="font-size:10px;">3</span> Evaluación
                        </label>
                        <select id="select-evaluacion" class="form-select form-select-sm" disabled>
                            <option value="">-- Seleccione una evaluación --</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">&nbsp;</label>
                        <button id="btn-cargar-alumnos" class="btn btn-primary btn-sm w-100" disabled>
                            <i class="bi bi-arrow-repeat"></i> Cargar Alumnos
                        </button>
                    </div>
                </div>

                <div id="info-aula" class="info-bar d-none">
                    <span><strong>Aula:</strong> <span id="nombre-aula"></span></span>
                    <span><strong>Materia:</strong> <span id="nombre-materia"></span></span>
                    <span><strong>Total Puntos:</strong> <span id="total-puntos">-</span></span>
                </div>

                <!-- Advertencia calificaciones existentes -->
                <div id="advertencia-existentes" class="alert alert-warning d-none">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Esta evaluación ya tiene calificaciones registradas.</strong>
                    <p class="mb-2 mt-1" style="font-size:13px;">Podés ver los valores actuales abajo o editarlos directamente.</p>
                    <button type="button" id="btn-editar-existentes" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Editar Calificaciones Existentes
                    </button>
                </div>

                <!-- Tabla de alumnos -->
                <div id="div-alumnos" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th style="width:100px;">Cédula</th>
                                    <th>Alumno</th>
                                    <th style="width:140px;">Puntos Obtenidos</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-alumnos"></tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" id="btn-cancelar" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </button>
                        <button type="button" id="btn-guardar" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-save me-1"></i> Guardar Calificaciones
                        </button>
                    </div>
                </div>

                <div id="msg-seleccionar" class="empty-state">
                    <i class="bi bi-arrow-right-circle"></i>
                    <p>Seleccione un curso, materia y evaluación para ver los alumnos</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ PANEL: HISTORIAL ══ -->
    <div class="panel" id="panel-historial">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-4 fw-semibold">
                    <i class="bi bi-clock-history text-secondary"></i> Historial de Calificaciones
                </h5>
                <div class="form-grid">
                    <div>
                        <label class="form-label" style="font-size:13px;">Curso</label>
                        <select id="historial-curso" class="form-select form-select-sm">
                            <option value="">-- Todos los cursos --</option>
                            <?php if($lista_aula): foreach($lista_aula as $aula): ?>
                            <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['curso']; ?></option>
                            <?php endforeach; endif; ?>
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
                               value="<?php echo $fecha_60_dias_atras; ?>">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:13px;">Hasta</label>
                        <input type="date" id="historial-hasta" class="form-control form-control-sm"
                               value="<?php echo $fecha_hoy_input; ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <button type="button" id="btn-limpiar-filtros" class="btn btn-secondary btn-sm px-4">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar Filtros
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Curso</th>
                                <th>Materia</th>
                                <th>Evaluación</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Calificados</th>
                                <th class="text-center">Pendientes</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-historial"></tbody>
                    </table>
                </div>
                <div id="msg-historial-vacio" class="empty-state d-none">
                    <i class="bi bi-inbox"></i>
                    <p>No hay evaluaciones en el rango de fechas seleccionado</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ PANEL: EDITAR ══ -->
    <div class="panel" id="panel-editar">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-pencil-square text-warning"></i> Editar Calificaciones
                    </h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="volverAlHistorial()">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Historial
                    </button>
                </div>
                <div id="info-aula-editar" class="info-bar">
                    <span><strong>Curso:</strong> <span id="editar-curso"></span></span>
                    <span><strong>Materia:</strong> <span id="editar-materia"></span></span>
                    <span><strong>Evaluación:</strong> <span id="editar-evaluacion"></span></span>
                    <span><strong>Puntos Totales:</strong> <span id="editar-puntos-totales" class="badge bg-info">0</span></span>
                </div>

                <!-- Leyenda de fila nueva -->
                <div class="alert alert-info py-2 px-3 mb-3" style="font-size:12px;">
                    <i class="bi bi-info-circle me-1"></i>
                    Las filas <span style="background:#fffbeb;padding:1px 6px;border-radius:4px;font-weight:600;">resaltadas</span>
                    no tenían nota previa. Dejá el campo vacío si no querés registrar puntaje para ese alumno.
                </div>

                <div id="div-alumnos-editar">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tabla-alumnos-editar">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th style="width:100px;">Cédula</th>
                                    <th>Alumno</th>
                                    <th style="width:140px;">Puntos Obtenidos</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-alumnos-editar"></tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="volverAlHistorial()">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </button>
                        <button type="button" id="btn-guardar-editar" class="btn btn-warning btn-sm px-4">
                            <i class="bi bi-save me-1"></i> Actualizar Calificaciones
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
// ═══════════════════════════════════════════
// VARIABLES GLOBALES
// ═══════════════════════════════════════════
let estado = {
    modo: 'registro',
    idAulaMateria: null,
    idEvaluacion: null,
};

// ═══════════════════════════════════════════
// TABS
// ═══════════════════════════════════════════
function switchTab(tab) {
    ['nueva','historial','editar'].forEach(t => {
        document.getElementById('panel-' + t)?.classList.toggle('active', t === tab);
        document.getElementById('tab-'   + t)?.classList.toggle('active', t === tab);
    });

    const tabEditar = document.getElementById('tab-editar');
    if (tab === 'editar') {
        tabEditar?.classList.remove('d-none');
    } else {
        tabEditar?.classList.add('d-none');
    }

    if (tab === 'historial') cargarHistorial();
    if (tab === 'nueva')     resetFormularioNueva();
}

function volverAlHistorial() { switchTab('historial'); }

function resetFormularioNueva() {
    estado.idAulaMateria = null;
    estado.idEvaluacion  = null;
    estado.modo          = 'registro';

    $('#select-curso').val('');
    $('#select-materia').html('<option value="">-- Seleccione una materia --</option>').prop('disabled', true);
    $('#select-evaluacion').html('<option value="">-- Seleccione una evaluación --</option>').prop('disabled', true);
    $('#btn-cargar-alumnos').prop('disabled', true);
    $('#info-aula').addClass('d-none');
    $('#advertencia-existentes').addClass('d-none');
    $('#div-alumnos').addClass('d-none');
    $('#msg-seleccionar').removeClass('d-none');
    $('#tbody-alumnos').html('');
}

// ═══════════════════════════════════════════
// CARGA DE SELECTS
// ═══════════════════════════════════════════
function cargarMaterias(idAula, $select, callback) {
    $select.html('<option value="">Cargando...</option>').prop('disabled', true);
    $.getJSON('api/materias_docente.php', { idAula })
        .done(function(res) {
            let opts = '<option value="">-- Seleccione una materia --</option>';
            if (res.success && Array.isArray(res.data)) {
                res.data.forEach(m => opts += `<option value="${m.idAulaMateria}">${m.materia}</option>`);
                $select.html(opts).prop('disabled', false);
            } else {
                $select.html('<option value="">Sin materias</option>').prop('disabled', true);
            }
            if (callback) callback();
        })
        .fail(() => $select.html('<option value="">Error</option>').prop('disabled', true));
}

function cargarEvaluaciones(idAulaMateria) {
    $('#select-evaluacion').html('<option value="">Cargando...</option>').prop('disabled', true);
    $('#btn-cargar-alumnos').prop('disabled', true);

    $.getJSON('api/listar_evaluaciones.php', { idAulaMateria })
        .done(function(res) {
            let opts = '<option value="">-- Seleccione una evaluación --</option>';
            if (res.success && Array.isArray(res.data) && res.data.length > 0) {
                res.data.forEach(ev => {
                    opts += `<option value="${ev.idEvaluacion}"
                                     data-puntos="${ev.puntos_total}"
                                     data-tipo="${ev.tipo_nota}">
                                ${ev.nombre} (${ev.tipo_nota})
                             </option>`;
                });
                $('#select-evaluacion').html(opts).prop('disabled', false);
            } else {
                $('#select-evaluacion').html('<option value="">Sin evaluaciones</option>').prop('disabled', true);
            }
        })
        .fail(() => alertify.error('Error al cargar evaluaciones'));
}

// ═══════════════════════════════════════════
// CARGAR ALUMNOS PARA NUEVA CALIFICACIÓN
// ═══════════════════════════════════════════
function cargarAlumnos() {
    const idEvaluacion  = $('#select-evaluacion').val();
    const idAulaMateria = $('#select-materia').val();
    if (!idEvaluacion || !idAulaMateria) {
        alertify.warning('Seleccione materia y evaluación');
        return;
    }

    $('#btn-cargar-alumnos').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Cargando...');

    // Verificar si ya tiene calificaciones existentes + traer alumnos en paralelo
    $.when(
        $.getJSON('api/obtener_calificaciones.php', { idEvaluacion }),
        $.getJSON('api/listar_alumnos_calificaciones.php', { idAulaMateria })
    ).done(function(resExist, resAlumnos) {
        $('#btn-cargar-alumnos').prop('disabled', false).html('<i class="bi bi-arrow-repeat"></i> Cargar Alumnos');

        const hayExistentes = resExist[0].success &&
                              resExist[0].data?.existe === true &&
                              resExist[0].data?.calificaciones?.length > 0;

        const alumnos = (resAlumnos[0].success && Array.isArray(resAlumnos[0].data))
            ? resAlumnos[0].data : [];

        if (alumnos.length === 0) {
            alertify.error('No hay alumnos vigentes en este curso');
            return;
        }

        // Info bar
        $('#nombre-aula').text($('#select-curso option:selected').text());
        $('#nombre-materia').text($('#select-materia option:selected').text());
        $('#total-puntos').text($('#select-evaluacion option:selected').data('puntos'));
        $('#info-aula').removeClass('d-none');
        $('#msg-seleccionar').addClass('d-none');

        estado.idEvaluacion  = idEvaluacion;
        estado.idAulaMateria = idAulaMateria;

        if (hayExistentes) {
            // Mostrar advertencia + tabla en modo lectura con valores actuales
            const califs = resExist[0].data.calificaciones;
            $('#advertencia-existentes').removeClass('d-none');
            renderizarTablaNuevas(alumnos, true, califs);
            $('#div-alumnos').removeClass('d-none');

            $('#btn-editar-existentes').off('click').on('click', function() {
                cargarParaEditar(idEvaluacion, resExist[0].data);
            });
        } else {
            $('#advertencia-existentes').addClass('d-none');
            renderizarTablaNuevas(alumnos, false, []);
            $('#div-alumnos').removeClass('d-none');
        }
    }).fail(function() {
        $('#btn-cargar-alumnos').prop('disabled', false).html('<i class="bi bi-arrow-repeat"></i> Cargar Alumnos');
        alertify.error('Error al cargar datos');
    });
}

// soloLectura = true cuando ya hay calificaciones (modo visualización)
function renderizarTablaNuevas(alumnos, soloLectura, califs) {
    let html = '';
    alumnos.forEach(function(alu, i) {
        const calif = califs.find(c => c.idMatricula == alu.idMatricula);
        const puntos = calif ? calif.puntos_obtenidos : '';
        const obs    = calif ? (calif.observacion || '') : '';
        const nombreCompleto = `${alu.apellidos}, ${alu.nombres}`;
        const cedula = alu.cedula || '-';

        if (soloLectura) {
            html += `<tr>
                <td class="text-muted">${i+1}</td>
                <td class="alumno-cedula text-muted">${cedula}</td>
                <td class="alumno-nombre">${nombreCompleto}</td>
                <td class="text-center fw-bold">${puntos !== '' ? puntos : '<span class="text-muted">-</span>'}</td>
                <td><small class="text-muted">${obs || '-'}</small></td>
            </tr>`;
        } else {
            html += `<tr>
                <td class="text-muted">${i+1}</td>
                <td class="alumno-cedula text-muted">${cedula}</td>
                <td class="alumno-nombre">${nombreCompleto}</td>
                <td>
                    <input type="number" class="form-control form-control-sm puntos-input"
                           min="0" step="1" placeholder="0"
                           data-id-matricula="${alu.idMatricula}"
                           data-alumno-nombre="${nombreCompleto}"
                           data-alumno-cedula="${cedula}">
                </td>
                <td>
                    <textarea class="form-control form-control-sm" rows="1"
                              data-id-matricula="${alu.idMatricula}"
                              placeholder="Observación..."></textarea>
                </td>
            </tr>`;
        }
    });
    $('#tbody-alumnos').html(html);

    $('#btn-guardar').toggleClass('d-none', soloLectura);
    $('#btn-cancelar').toggleClass('d-none', soloLectura);
}

// ═══════════════════════════════════════════
// CARGAR PARA EDITAR (unificado)
// ═══════════════════════════════════════════
function cargarParaEditar(idEvaluacion, dataExistente) {
    function procesarEdicion(evalData, califsExistentes, idAulaMateria) {
        $('#editar-curso').text(
            evalData.curso || $('#select-curso option:selected').text() || '-'
        );
        $('#editar-materia').text(
            evalData.materia || $('#select-materia option:selected').text() || '-'
        );
        $('#editar-evaluacion').text(evalData.nombre || '-');
        $('#editar-puntos-totales').text(evalData.puntos_total || 0);

        $.getJSON('api/listar_alumnos_calificaciones.php', { idAulaMateria })
            .done(function(resAlu) {
                if (!resAlu.success || !Array.isArray(resAlu.data)) {
                    alertify.error('No hay alumnos para editar');
                    return;
                }

                renderizarTablaEditar(resAlu.data, califsExistentes);
                estado.idEvaluacion = idEvaluacion;
                estado.modo         = 'edicion';
                switchTab('editar');
            })
            .fail(() => alertify.error('Error al cargar alumnos'));
    }

    // Si ya tenemos los datos (viene desde advertencia-existentes), los usamos
    if (dataExistente) {
        const ev = dataExistente.evaluacion;
        procesarEdicion(ev, dataExistente.calificaciones, ev.idAulaMateria);
    } else {
        // Viene desde historial — hay que pedir los datos
        $.getJSON('api/obtener_calificaciones.php', { idEvaluacion })
            .done(function(res) {
                if (!res.success || !res.data) {
                    alertify.error('Error al cargar calificaciones');
                    return;
                }
                const ev = res.data.evaluacion;
                procesarEdicion(ev, res.data.calificaciones, ev.idAulaMateria);
            })
            .fail(() => alertify.error('Error al cargar evaluación'));
    }
}

function renderizarTablaEditar(alumnos, califsExistentes) {
    let html = '';
    alumnos.forEach(function(alu, i) {
        const calif    = califsExistentes.find(c => c.idMatricula == alu.idMatricula);
        const tienNota = calif && calif.puntos_obtenidos !== null && calif.puntos_obtenidos !== undefined;
        const puntos   = tienNota ? calif.puntos_obtenidos : '';
        const obs      = calif ? (calif.observacion || '') : '';
        const clsFila  = tienNota ? '' : 'fila-sin-nota';
        const nombreCompleto = `${alu.apellidos}, ${alu.nombres}`;
        const cedula = alu.cedula || '-';
        const avisoHtml = tienNota ? '' :
            '<div class="aviso-sin-nota"><i class="bi bi-info-circle"></i> Sin nota previa — dejá vacío para no guardar</div>';

        html += `<tr class="${clsFila}">
            <td class="text-muted">${i+1}</td>
            <td class="alumno-cedula text-muted">${cedula}</td>
            <td class="alumno-nombre">${nombreCompleto}</td>
            <td>
                <input type="number" class="form-control form-control-sm puntos-input-editar"
                       min="0" step="1"
                       value="${puntos}"
                       placeholder="${tienNota ? '0' : 'Sin nota'}"
                       data-id-matricula="${alu.idMatricula}"
                       data-tiene-nota="${tienNota ? '1' : '0'}"
                       data-alumno-nombre="${nombreCompleto}"
                       data-alumno-cedula="${cedula}">
                ${avisoHtml}
            </td>
            <td>
                <textarea class="form-control form-control-sm" rows="1"
                          data-id-matricula="${alu.idMatricula}"
                          placeholder="Observación...">${obs}</textarea>
            </td>
        </tr>`;
    });
    $('#tbody-alumnos-editar').html(html);
}

// ═══════════════════════════════════════════
// HISTORIAL
// ═══════════════════════════════════════════
function cargarHistorial() {
    const idAula        = $('#historial-curso').val();
    const idAulaMateria = $('#historial-materia').val();
    const desde         = $('#historial-desde').val();
    const hasta         = $('#historial-hasta').val();

    $.ajax({
        url: 'api/historial_calificaciones.php',
        data: { idAula, idAulaMateria, desde, hasta },
        dataType: 'json',
        success: function(res) {
            const tbody = $('#tbody-historial').html('');
            if (res.success && Array.isArray(res.data) && res.data.length > 0) {
                $('#msg-historial-vacio').addClass('d-none');
                res.data.forEach(function(item) {
                    const fecha = item.fecha_creacion.substring(0, 10);
                    const bCalif = item.calificados > 0
                        ? `<span class="badge badge-registrado">${item.calificados}</span>`
                        : `<span class="badge bg-secondary">${item.calificados}</span>`;
                    const bPend = item.pendientes > 0
                        ? `<span class="badge badge-pendiente">${item.pendientes}</span>`
                        : `<span class="badge bg-success">0</span>`;
                    tbody.append(`<tr>
                        <td>${fecha}</td>
                        <td>${item.curso}</td>
                        <td>${item.materia}</td>
                        <td>${item.evaluacion}</td>
                        <td class="text-center">${item.total_alumnos}</td>
                        <td class="text-center">${bCalif}</td>
                        <td class="text-center">${bPend}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                    onclick="cargarParaEditar(${item.idEvaluacion}, null)">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                        </td>
                    </tr>`);
                });
            } else {
                $('#msg-historial-vacio').removeClass('d-none');
            }
        },
        error: () => alertify.error('Error al cargar historial')
    });
}

// ═══════════════════════════════════════════
// VALIDACIÓN DE PUNTOS (enteros, 0 a max)
// ═══════════════════════════════════════════
function validarPuntos(valor, max, $input) {
    const $row = $input.closest('tr');
    const idMatricula = $input.data('id-matricula');
    const nombreAlumno = obtenerNombreAlumno($input);
    
    // Limpiar estilos previos
    $row.removeClass('fila-error');
    $input.removeClass('is-invalid');
    
    // Si está vacío, es válido (se saltea)
    if (valor === '' || valor === null) {
        return { ok: true, saltar: true };
    }
    
    const n = Number(valor);
    
    // Validación: debe ser número entero
    if (!Number.isInteger(n)) {
        marcarError($row, $input);
        return { 
            ok: false, 
            msg: `${nombreAlumno}: solo se permiten números enteros`,
            $input: $input,
            $row: $row
        };
    }
    
    // Validación: no negativo
    if (n < 0) {
        marcarError($row, $input);
        return { 
            ok: false, 
            msg: `${nombreAlumno}: la nota no puede ser negativa`,
            $input: $input,
            $row: $row
        };
    }
    
    // Validación: no excede máximo
    if (n > max) {
        marcarError($row, $input);
        return { 
            ok: false, 
            msg: `${nombreAlumno}: excede el máximo permitido (${max})`,
            $input: $input,
            $row: $row
        };
    }
    
    // Validación exitosa: limpiar estilos
    $row.removeClass('fila-error');
    $input.removeClass('is-invalid');
    
    return { ok: true, saltar: false, valor: n };
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================

// Obtener nombre completo del alumno desde el DOM
function obtenerNombreAlumno($input) {
    const $row = $input.closest('tr');
    
    // Intentar obtener desde data attributes (más rápido)
    const nombreData = $input.data('alumno-nombre');
    const cedulaData = $input.data('alumno-cedula');
    
    if (nombreData && cedulaData) {
        return `${cedulaData} - ${nombreData}`;
    }
    
    // Fallback: buscar en la tabla
    const nombre = $row.find('td.alumno-nombre').text().trim();
    const cedula = $row.find('td.alumno-cedula').text().trim();
    
    return cedula ? `${cedula} - ${nombre}` : nombre;
}

// Marcar visualmente un error
function marcarError($row, $input) {
    $row.addClass('fila-error');
    $input.addClass('is-invalid');
}

// Enfocar el primer error encontrado
function enfocarPrimerError(errores) {
    if (!errores || errores.length === 0) return;
    
    const primerError = errores[0];
    if (primerError && primerError.$input) {
        // Quitar highlight anterior si existe
        $('.error-highlight').removeClass('error-highlight');
        
        // Agregar highlight
        const $row = primerError.$input.closest('tr');
        if ($row.length) {
            $row.addClass('error-highlight');
        }
        
        // Enfocar y seleccionar el campo
        setTimeout(function() {
            primerError.$input.focus();
            primerError.$input.select();
            
            // Scroll suave al campo
            if ($row.length) {
                $row[0].scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        }, 100);
    }
}

// ═══════════════════════════════════════════
// GUARDAR NUEVAS CALIFICACIONES
// ═══════════════════════════════════════════
$('#btn-guardar').on('click', function() {
    const idEvaluacion  = estado.idEvaluacion;
    const puntosTotales = parseInt($('#total-puntos').text()) || 0;

    if (!idEvaluacion) { 
        alertify.error('Evaluación no seleccionada'); 
        return; 
    }

    let califs = [], errores = [], sinNota = 0;

    // Limpiar estilos de error previos
    $('.fila-error').removeClass('fila-error');
    $('.puntos-input.is-invalid').removeClass('is-invalid');

    // Validar cada fila
    $('#tbody-alumnos tr').each(function() {
        const $row      = $(this);
        const $input    = $row.find('.puntos-input');
        const idMat     = $input.data('id-matricula');
        const puntoRaw  = $input.val().trim();
        const obs       = $row.find('textarea').val().trim();
        
        // Validar con la función mejorada
        const chk = validarPuntos(puntoRaw, puntosTotales, $input);

        if (chk.saltar) { 
            sinNota++; 
            return; 
        }
        
        if (!chk.ok) { 
            errores.push(chk); 
            return; 
        }

        califs.push({ 
            idMatricula: idMat, 
            idEvaluacion, 
            puntos_obtenidos: chk.valor, 
            observacion: obs || null 
        });
    });

    // Si hay errores, mostrar y enfocar el primero
    if (errores.length > 0) {
        const mensajes = errores.map(e => e.msg).join('\n');
        alertify.error(mensajes);
        enfocarPrimerError(errores);
        return;
    }

    if (califs.length === 0) { 
        alertify.warning('Ingresá al menos un puntaje'); 
        return; 
    }

    function ejecutarGuardado() {
        const $btn = $('#btn-guardar').prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');

        $.ajax({
            type: 'POST', 
            url: 'api/guardar_calificaciones_lote.php',
            data: JSON.stringify({ evaluaciones: califs }),
            contentType: 'application/json', 
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Guardar Calificaciones');
                if (res.success) {
                    alertify.success(`✓ ${res.total} calificación(es) guardada(s)`);
                    resetFormularioNueva();
                } else {
                    alertify.error(res.message || res.error || 'Error desconocido');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Guardar Calificaciones');
                alertify.error('Error al guardar calificaciones');
            }
        });
    }

    if (sinNota > 0) {
        alertify.confirm(
            'Alumnos sin puntaje',
            `${sinNota} alumno(s) no tienen puntaje ingresado y no serán guardados. ¿Desea continuar?`,
            ejecutarGuardado,
            function() {}
        ).set('labels', { ok: 'Continuar', cancel: 'Volver' });
    } else {
        ejecutarGuardado();
    }
});

// ═══════════════════════════════════════════
// ACTUALIZAR CALIFICACIONES (EDITAR)
// ═══════════════════════════════════════════
$('#btn-guardar-editar').on('click', function() {
    const idEvaluacion  = estado.idEvaluacion;
    const puntosTotales = parseInt($('#editar-puntos-totales').text()) || 0;

    if (!idEvaluacion) { 
        alertify.error('Evaluación no seleccionada'); 
        return; 
    }

    let califs = [], errores = [], sinNota = 0;

    // Limpiar estilos de error previos
    $('.fila-error').removeClass('fila-error');
    $('.puntos-input-editar.is-invalid').removeClass('is-invalid');

    // Validar cada fila
    $('#tbody-alumnos-editar tr').each(function() {
        const $row     = $(this);
        const $input   = $row.find('.puntos-input-editar');
        const idMat    = $input.data('id-matricula');
        const puntoRaw = $input.val().trim();
        const obs      = $row.find('textarea').val().trim();
        
        // Validar con la función mejorada
        const chk = validarPuntos(puntoRaw, puntosTotales, $input);

        if (chk.saltar) { 
            sinNota++; 
            return; 
        }
        
        if (!chk.ok) { 
            errores.push(chk); 
            return; 
        }

        califs.push({ 
            idMatricula: idMat, 
            puntos_obtenidos: chk.valor, 
            observacion: obs || null 
        });
    });

    // Si hay errores, mostrar y enfocar el primero
    if (errores.length > 0) {
        const mensajes = errores.map(e => e.msg).join('\n');
        alertify.error(mensajes);
        enfocarPrimerError(errores);
        return;
    }

    if (califs.length === 0) { 
        alertify.warning('No hay datos para actualizar'); 
        return; 
    }

    function ejecutarActualizacion() {
        const $btn = $('#btn-guardar-editar').prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm me-1"></span> Actualizando...');

        $.ajax({
            type: 'POST', 
            url: 'api/editar_calificaciones_lote.php',
            data: JSON.stringify({ idEvaluacion, calificaciones: califs }),
            contentType: 'application/json', 
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Actualizar Calificaciones');
                if (res.success) {
                    alertify.success(`✓ ${res.total} calificación(es) actualizada(s)`);
                    volverAlHistorial();
                } else {
                    alertify.error(res.error || 'Error desconocido');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Actualizar Calificaciones');
                alertify.error('Error al actualizar calificaciones');
            }
        });
    }

    if (sinNota > 0) {
        alertify.confirm(
            'Alumnos sin puntaje',
            `${sinNota} alumno(s) tienen el campo vacío y no serán actualizados. ¿Desea continuar?`,
            ejecutarActualizacion,
            function() {}
        ).set('labels', { ok: 'Continuar', cancel: 'Volver' });
    } else {
        ejecutarActualizacion();
    }
});

// ═══════════════════════════════════════════
// EVENTOS - FILTROS NUEVA
// ═══════════════════════════════════════════
$('#select-curso').on('change', function() {
    const idAula = $(this).val();
    $('#select-evaluacion').html('<option value="">-- Seleccione una evaluación --</option>').prop('disabled', true);
    $('#btn-cargar-alumnos').prop('disabled', true);
    $('#advertencia-existentes').addClass('d-none');
    $('#div-alumnos').addClass('d-none');
    $('#msg-seleccionar').removeClass('d-none');

    if (idAula) {
        cargarMaterias(idAula, $('#select-materia'));
    } else {
        $('#select-materia').html('<option value="">-- Seleccione una materia --</option>').prop('disabled', true);
    }
});

$('#select-materia').on('change', function() {
    const idAulaMateria = $(this).val();
    if (idAulaMateria) {
        cargarEvaluaciones(idAulaMateria);
    } else {
        $('#select-evaluacion').html('<option value="">-- Seleccione una evaluación --</option>').prop('disabled', true);
        $('#btn-cargar-alumnos').prop('disabled', true);
    }
});

$('#select-evaluacion').on('change', function() {
    $('#btn-cargar-alumnos').prop('disabled', !$(this).val());
    $('#advertencia-existentes').addClass('d-none');
    $('#div-alumnos').addClass('d-none');
    $('#msg-seleccionar').removeClass('d-none');
});

$('#btn-cargar-alumnos').on('click', cargarAlumnos);
$('#btn-cancelar').on('click', resetFormularioNueva);

// ═══════════════════════════════════════════
// EVENTOS - FILTROS HISTORIAL
// ═══════════════════════════════════════════
$('#historial-curso').on('change', function() {
    const idAula = $(this).val();
    if (idAula) {
        $.getJSON('api/materias_docente.php', { idAula })
            .done(function(res) {
                let opts = '<option value="">-- Todas las materias --</option>';
                if (res.success && res.data.length > 0) {
                    res.data.forEach(m => opts += `<option value="${m.idAulaMateria}">${m.materia}</option>`);
                }
                $('#historial-materia').html(opts).prop('disabled', false);
            });
    } else {
        $('#historial-materia').html('<option value="">-- Todas las materias --</option>').prop('disabled', true);
    }
    cargarHistorial();
});

$('#historial-materia, #historial-desde, #historial-hasta').on('change', cargarHistorial);

$('#btn-limpiar-filtros').on('click', function() {
    $('#historial-curso').val('');
    $('#historial-materia').html('<option value="">-- Todas las materias --</option>').prop('disabled', true);
    $('#historial-desde').val('<?php echo $fecha_60_dias_atras; ?>');
    $('#historial-hasta').val('<?php echo $fecha_hoy_input; ?>');
    cargarHistorial();
});

// Historial NO carga al inicio — solo cuando el docente va al tab
</script>
