<?php
/**
 * ARCHIVO: Docente/evaluaciones.php
 * Página para que los docentes creen, editen y administren evaluaciones.
 */
    $ruta = "../";
    include __DIR__ . '/includes/header.php';
    require_once __DIR__ . "/../servicios/conexion.php";

    $docente_id = $_SESSION['docente_id'];
    if (!$docente_id) { header('Location: index.php'); exit; }

    //Traemos las aulas asociadas al docente
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

    $sql_periodo = "SELECT idPeriodo, nombre, fecha_inicio, fecha_fin FROM periodo WHERE activo = 'Sí' LIMIT 1";
    $periodo_activo = buscar_datos($sql_periodo);
    $periodo_nombre  = $periodo_activo[0]['nombre']       ?? null;
    $idPeriodo       = $periodo_activo[0]['idPeriodo']    ?? null;
    $periodo_inicio  = $periodo_activo[0]['fecha_inicio'] ?? null;
    $periodo_fin     = $periodo_activo[0]['fecha_fin']    ?? null;

    $sql_tipos = "SELECT idTipoNota, nombre, unico_por_periodo FROM tipo_nota WHERE activo = 'Sí'";
    $lista_tipos = buscar_datos($sql_tipos);
?>

    <link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css">
    <script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>
    <script src="<?php echo $ruta; ?>dt/jquery.dataTables.min.js"></script>
    <script src="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.js"></script>

<style>
    .card {
        border-radius: 14px;
        border: none;
    }

    .card-header-custom {
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        color: white;
        border-radius: 14px 14px 0 0;
        padding: 18px;
    }

    .table th {
        background: #f8f9fa;
        vertical-align: middle;
    }

    .badge-periodo {
        font-size: 14px;
        padding: 8px 12px;
    }

    /* ── Header con acción principal separada ── */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 22px;
    }
    .page-header .titulo h2 { margin-bottom: 2px; }

    .btn-nueva-eval {
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(25,135,84,.25);
    }

    /* ── Aviso de período cerrado ── */
    .alert-periodo-cerrado {
        background: #fef3c7;
        border: 1px solid #fde68a;
        color: #92400e;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 13px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ── Opciones de tipo deshabilitadas por "único" ── */
    select option:disabled {
        color: #adb5bd;
    }

    /* ── Campos bloqueados por tener notas ── */
    .campo-bloqueado {
        background-color: #f8f9fa !important;
        cursor: not-allowed;
    }
    .aviso-bloqueo {
        font-size: 12px;
        color: #b45309;
        margin-top: 4px;
        display: none;
    }
</style>

<div class="main-content">

    <!-- HEADER: título + acción principal claramente separada -->
    <div class="page-header">
        <div class="titulo">
            <h2 class="fw-bold mb-0">Evaluaciones</h2>
            <small class="text-muted">Crear y administrar evaluaciones, exámenes y trabajos prácticos</small>
        </div>

        <button type="button" class="btn btn-success btn-lg btn-nueva-eval" data-bs-toggle="modal" data-bs-target="#modalNuevaEvaluacion">
            <i class="bi bi-plus-circle"></i> Nueva Evaluación
        </button>
    </div>

    <?php if (!$idPeriodo): ?>
    <div class="alert-periodo-cerrado">
        <i class="bi bi-exclamation-triangle-fill"></i>
        No hay un período activo en este momento. No podés crear evaluaciones hasta que el Director abra un semestre,
        o solicitá la reapertura de una materia específica si necesitás editar evaluaciones.
    </div>
    <?php endif; ?>

    <!-- FILTROS DE TABLA -->
    <div class="card shadow-sm mb-4">

        <div class="card-header-custom">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros de búsqueda</h5>
        </div>

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-6">
                    <label for="filtroAula" class="form-label fw-semibold">Aula</label>
                    <select class="form-select" name="filtroAula" id="filtroAula">
                        <option value="" selected>Seleccione...</option>
                        <?php if($lista_aula): foreach($lista_aula as $aula): ?>
                        <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['curso']; ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="filtroMateria" class="form-label fw-semibold">Materia</label>
                    <select class="form-select" name="filtroMateria" id="filtroMateria" disabled>
                        <option value="" selected>Seleccione...</option>
                    </select>
                </div>

            </div>

        </div>
    </div>

    <!-- RESUMEN -->
    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-file-earmark-text" style="font-size: 2rem; color: #0d6efd;"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0">Total Evaluaciones</p>
                            <h4 class="mb-0" id="totalEvaluaciones">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-pencil-square" style="font-size: 2rem; color: #198754;"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0">Notas Registradas</p>
                            <h4 class="mb-0" id="totalNotasRegistradas">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-calendar-event" style="font-size: 2rem; color: #fd7e14;"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0">Período Actual</p>
                            <h4 class="mb-0" id="periodoActual"><?php echo $periodo_nombre ?? 'Sin período activo'; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- TABLA DE EVALUACIONES -->
    <div class="card shadow-sm">

        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Listado de Evaluaciones</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle" id="tablaEvaluaciones">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Puntos</th>
                            <th>Fecha</th>
                            <th>Notas Registradas</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody id="tbodyEvaluaciones"></tbody>
                </table>

            </div>

        </div>

    </div>

</div>

<!-- MODAL: NUEVA EVALUACIÓN -->
<div class="modal fade" id="modalNuevaEvaluacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Nueva Evaluación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="modalAula" class="form-label fw-semibold">Aula *</label>
                        <select class="form-select" id="modalAula">
                            <option value="" selected>Seleccione...</option>
                            <?php if($lista_aula): foreach($lista_aula as $aula): ?>
                            <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['curso']; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="modalMateria" class="form-label fw-semibold">Materia *</label>
                        <select class="form-select" id="modalMateria" disabled>
                            <option value="" selected>Seleccione...</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="evalNombre" class="form-label fw-semibold">Nombre de la Evaluación *</label>
                    <input type="text" class="form-control" id="evalNombre" minlength="5" maxlength="40" placeholder="Ej: 1er Parcial de Matemática" required>
                </div>

                <div class="mb-3">
                    <label for="evalTipo" class="form-label fw-semibold">Tipo de Evaluación *</label>
                    <select class="form-select" id="evalTipo" disabled>
                        <option value="" selected>Primero seleccione Aula y Materia...</option>
                    </select>
                    <small class="text-muted">Los tipos "Primera Parcial", "Segunda Parcial" y "Examen Final" solo pueden crearse una vez por período.</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="evalPuntos" class="form-label fw-semibold">Puntos Totales</label>
                        <input type="number" class="form-control" id="evalPuntos" value="0" min="1" max="100" step="1" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="evalFecha" class="form-label fw-semibold">Fecha</label>
                        <input type="date" class="form-control" id="evalFecha"
                               <?php if($periodo_inicio): ?>min="<?php echo $periodo_inicio; ?>"<?php endif; ?>
                               <?php if($periodo_fin): ?>max="<?php echo $periodo_fin; ?>"<?php endif; ?>
                               required>
                        <?php if ($periodo_inicio && $periodo_fin): ?>
                        <small class="text-muted">Debe estar entre <?php echo date('d/m/Y', strtotime($periodo_inicio)); ?> y <?php echo date('d/m/Y', strtotime($periodo_fin)); ?></small>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarEvaluacion" <?php echo $idPeriodo ? '' : 'disabled'; ?>>Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: EDITAR EVALUACIÓN -->
<div class="modal fade" id="modalEditarEvaluacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Editar Evaluación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIdEvaluacion">
                <input type="hidden" id="editTieneNotas">

                <div class="mb-3">
                    <label for="editNombre" class="form-label fw-semibold">Nombre de la Evaluación *</label>
                    <input type="text" class="form-control" id="editNombre">
                </div>

                <div class="mb-3">
                    <label for="editTipo" class="form-label fw-semibold">Tipo de Evaluación *</label>
                    <select class="form-select" id="editTipo">
                        <option selected>Seleccione...</option>
                        <?php if($lista_tipos): foreach($lista_tipos as $tipo): ?>
                        <option value="<?php echo $tipo['idTipoNota']; ?>"><?php echo $tipo['nombre']; ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                    <div class="aviso-bloqueo" id="avisoTipoBloqueado">
                        <i class="bi bi-lock-fill"></i> No editable: esta evaluación ya tiene notas registradas.
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="editPuntos" class="form-label fw-semibold">Puntos Totales</label>
                        <input type="number" class="form-control" id="editPuntos" value="0" min="1" max="100" step="1">
                        <div class="aviso-bloqueo" id="avisoPuntosBloqueado">
                            <i class="bi bi-lock-fill"></i> No editable: esta evaluación ya tiene notas registradas.
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="editFecha" class="form-label fw-semibold">Fecha</label>
                        <input type="date" class="form-control" id="editFecha"
                               <?php if($periodo_inicio): ?>min="<?php echo $periodo_inicio; ?>"<?php endif; ?>
                               <?php if($periodo_fin): ?>max="<?php echo $periodo_fin; ?>"<?php endif; ?>>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="btnActualizarEvaluacion">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>

// ===============================
// VARIABLES GLOBALES
// ===============================
let dataTableEvaluaciones;
let idAulaMateria_modal = null;
let tiposDisponibles = <?php echo $lista_tipos ? json_encode($lista_tipos) : '[]'; ?>;
let idPeriodoActivo = <?php echo $idPeriodo ?? 'null'; ?>;
let tiposUsadosUnicosModal = []; // Tipos "únicos por período" que ya están en uso en la materia seleccionada del modal

// ===============================
// INICIALIZAR DATATABLE
// ===============================
dataTableEvaluaciones = $('#tablaEvaluaciones').DataTable({
    deferLoading: 0,
    language: {
        url: '<?php echo $ruta; ?>dt/es-ES.json'
    },
    paging: true,
    searching: true,
    info: true,
    columns: [
        { data: 'nombre' },
        { data: 'tipo_nota' },
        { data: 'puntos_total', className: 'text-center' },
        { data: 'fecha_evaluacion', className: 'text-center' },
        { data: 'total_notas_registradas', className: 'text-center' },
        {
            data: null,
            className: 'text-center',
            render: function(data) {
                return `<button class="btn btn-info btn-sm bi bi-pencil btn-editar-eval"
                                data-id="${data.idEvaluacion}"
                                data-nombre="${data.nombre}"
                                data-tipo="${data.idTipoNota}"
                                data-puntos="${data.puntos_total}"
                                data-fecha="${data.fecha_evaluacion || ''}"
                                data-notas="${data.total_notas_registradas}"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditarEvaluacion"></button>
                        <button class="btn btn-danger btn-sm bi bi-trash btn-eliminar-eval"
                                data-id="${data.idEvaluacion}"
                                data-notas="${data.total_notas_registradas}"></button>`;
            }
        }
    ]
});

// ===============================
// FUNCIONES
// ===============================
function cargarMaterias(idAula, $select, callback) {
    $select.html('<option value="">Cargando...</option>').prop('disabled', true);
    $.getJSON('api/materias_docente.php', { idAula: idAula })
        .done(function(response) {
            let options = '<option value="">Seleccione...</option>';
            if (response.success && Array.isArray(response.data)) {
                response.data.forEach(function(materia) {
                    options += `<option value="${materia.idAulaMateria}">${materia.materia}</option>`;
                });
                $select.html(options).prop('disabled', false);
            }
            if (callback) callback();
        })
        .fail(function() {
            $select.html('<option value="">Error</option>').prop('disabled', true);
        });
}

function cargarEvaluaciones() {
    let idAulaMateria = $('#filtroMateria').val();

    if (!idAulaMateria) {
        dataTableEvaluaciones.clear().draw();
        $('#totalEvaluaciones').text('0');
        $('#totalNotasRegistradas').text('0');
        return;
    }

    $.ajax({
        url: 'api/listar_evaluaciones.php',
        data: { idAulaMateria: idAulaMateria },
        dataType: 'json',
        success: function(response) {
            if (response.success && Array.isArray(response.data)) {
                dataTableEvaluaciones.clear();
                dataTableEvaluaciones.rows.add(response.data).draw();

                let totalEvals = response.data.length;
                let totalNotas = response.data.reduce(function(sum, ev) {
                    return sum + parseInt(ev.total_notas_registradas || 0);
                }, 0);

                $('#totalEvaluaciones').text(totalEvals);
                $('#totalNotasRegistradas').text(totalNotas);

                // Guardar qué tipos "únicos" ya están usados en este período
                tiposUsadosUnicos = response.data
                    .filter(ev => ev.unico_por_periodo === 'Sí')
                    .map(ev => parseInt(ev.idTipoNota));
            } else {
                dataTableEvaluaciones.clear().draw();
                $('#totalEvaluaciones').text('0');
                $('#totalNotasRegistradas').text('0');
                tiposUsadosUnicos = [];
            }
        },
        error: function() {
            alertify.error('Error al cargar evaluaciones');
        }
    });
}

// Tipos "únicos por período" que ya están en uso en la materia seleccionada del modal
//let tiposUsadosUnicosModal = [];

function poblarSelectTipo($select, tiposUsados) {
    let options = '<option value="" selected>Seleccione...</option>';
    tiposDisponibles.forEach(function(tipo) {
        const esUnico = tipo.unico_por_periodo === 'Sí';
        const yaUsado = esUnico && tiposUsados.includes(parseInt(tipo.idTipoNota));
        const disabled = yaUsado ? 'disabled' : '';
        const sufijo = yaUsado ? ' (ya existe este período)' : '';
        options += `<option value="${tipo.idTipoNota}" ${disabled}>${tipo.nombre}${sufijo}</option>`;
    });
    $select.html(options).prop('disabled', false);
}

// ===============================
// EVENTOS - FILTRADO (solo tabla)
// ===============================
$('#filtroAula').change(function() {
    let aula = $(this).val();
    if (aula) {
        cargarMaterias(aula, $('#filtroMateria'));
    } else {
        $('#filtroMateria').html('<option value="">Seleccione...</option>').prop('disabled', true);
        dataTableEvaluaciones.clear().draw();
    }
});

$('#filtroMateria').change(function() {
    cargarEvaluaciones();

    // Autocompletar el modal de Nueva Evaluación con la misma Aula/Materia
    let idAula = $('#filtroAula').val();
    let idAulaMateria = $(this).val();
    if (idAula && idAulaMateria) {
        $('#modalAula').val(idAula);
        cargarMaterias(idAula, $('#modalMateria'), function() {
            $('#modalMateria').val(idAulaMateria).trigger('change');
        });
    }
});

// ===============================
// EVENTOS - MODAL NUEVA EVALUACIÓN
// ===============================
$('#modalAula').change(function() {
    let aula = $(this).val();
    if (aula) {
        cargarMaterias(aula, $('#modalMateria'));
    } else {
        $('#modalMateria').html('<option value="">Seleccione...</option>').prop('disabled', true);
        $('#evalTipo').html('<option value="">Primero seleccione Aula y Materia...</option>').prop('disabled', true);
    }
});

$('#modalMateria').change(function() {
    idAulaMateria_modal = $(this).val();

    if (!idAulaMateria_modal) {
        $('#evalTipo').html('<option value="">Primero seleccione Aula y Materia...</option>').prop('disabled', true);
        return;
    }

    // Cargar evaluaciones existentes de esta materia/período para saber qué tipos "únicos" ya están usados
    $.getJSON('api/listar_evaluaciones.php', { idAulaMateria: idAulaMateria_modal, idPeriodo: idPeriodoActivo })
        .done(function(response) {
            let usados = [];
            if (response.success && Array.isArray(response.data)) {
                usados = response.data
                    .filter(ev => ev.unico_por_periodo === 'Sí')
                    .map(ev => parseInt(ev.idTipoNota));
            }
            poblarSelectTipo($('#evalTipo'), usados);
        })
        .fail(function() {
            poblarSelectTipo($('#evalTipo'), []);
        });
});

// Limpiar modal al cerrar
$('#modalNuevaEvaluacion').on('hidden.bs.modal', function() {
    $('#modalAula').val('');
    $('#modalMateria').html('<option value="">Seleccione...</option>').prop('disabled', true);
    $('#evalTipo').html('<option value="">Primero seleccione Aula y Materia...</option>').prop('disabled', true);
    $('#evalNombre').val('');
    $('#evalPuntos').val('0');
    $('#evalFecha').val('');
    idAulaMateria_modal = null;
});

$('#btnGuardarEvaluacion').on('click', function() {
    let nombre = $('#evalNombre').val().trim();
    let tipo   = $('#evalTipo').val();
    let puntos = $('#evalPuntos').val();
    let fecha  = $('#evalFecha').val();

    if (!nombre || !tipo || !idAulaMateria_modal || !fecha) {
        alertify.warning('Por favor completa todos los campos');
        return;
    }

    if (isNaN(tipo) || tipo === "") {
        alertify.warning('Seleccione un tipo de evaluación válido');
        return;
    }

    if (puntos < 1 || puntos > 100) {
        alertify.warning('Los puntos totales deben estar entre 1 y 100');
        return;
    }

    if (!idPeriodoActivo) {
        alertify.error('No hay un período activo. No se puede crear la evaluación.');
        return;
    }

    let datos = {
        idAulaMateria: idAulaMateria_modal,
        idPeriodo: idPeriodoActivo,
        idTipoNota: tipo,
        nombre: nombre,
        puntos_total: puntos,
        fecha_evaluacion: fecha
    };
//para evitar múltiples clicks mientras se procesa la solicitud
    $('#btnGuardarEvaluacion').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');
    
    $.ajax({
        type: 'POST',
        url: 'api/guardar_evaluacion.php',
        data: JSON.stringify(datos),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            $('#btnGuardarEvaluacion').prop('disabled', false).html('Guardar');
            if (response.success) {
                alertify.success(response.message);
                let modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaEvaluacion'));
                if (modal) modal.hide();
                setTimeout(function() {
                    cargarEvaluaciones();
                }, 300);
            } else {
                alertify.error(response.error || 'Error al guardar');
            }
        },
        error: function() {
            $('#btnGuardarEvaluacion').prop('disabled', false).html('Guardar');
            alertify.error('Error en la comunicación');
        }
    });
});

// ===============================
// EVENTOS - MODAL EDITAR EVALUACIÓN
// ===============================
$('#modalEditarEvaluacion').on('show.bs.modal', function(e) {
    let btn = e.relatedTarget;
    if (!btn) return;

    const tieneNotas = parseInt(btn.dataset.notas || 0) > 0;

    $('#editIdEvaluacion').val(btn.dataset.id);
    $('#editTieneNotas').val(tieneNotas ? '1' : '0');
    $('#editNombre').val(btn.dataset.nombre);
    $('#editTipo').val(btn.dataset.tipo);
    $('#editPuntos').val(btn.dataset.puntos);
    $('#editFecha').val(btn.dataset.fecha);

    // Bloquear Tipo y Puntos si ya tiene notas registradas
    if (tieneNotas) {
        $('#editTipo').addClass('campo-bloqueado').prop('disabled', true);
        $('#editPuntos').addClass('campo-bloqueado').prop('disabled', true);
        $('#avisoTipoBloqueado, #avisoPuntosBloqueado').show();
    } else {
        $('#editTipo').removeClass('campo-bloqueado').prop('disabled', false);
        $('#editPuntos').removeClass('campo-bloqueado').prop('disabled', false);
        $('#avisoTipoBloqueado, #avisoPuntosBloqueado').hide();
    }
});

$('#modalEditarEvaluacion').on('hidden.bs.modal', function() {
    $('#editIdEvaluacion').val('');
    $('#editTieneNotas').val('0');
    $('#editNombre').val('');
    $('#editTipo').val('').prop('disabled', false).removeClass('campo-bloqueado');
    $('#editPuntos').val('0').prop('disabled', false).removeClass('campo-bloqueado');
    $('#editFecha').val('');
    $('#avisoTipoBloqueado, #avisoPuntosBloqueado').hide();
});

$('#btnActualizarEvaluacion').on('click', function() {
    let idEvaluacion = $('#editIdEvaluacion').val();
    let nombre = $('#editNombre').val().trim();
    let tipo   = $('#editTipo').val();
    let puntos = $('#editPuntos').val();
    let fecha  = $('#editFecha').val();

    if (!nombre || !tipo) {
        alertify.warning('Por favor no deje vacío los campos obligatorios');
        return;
    }

    if (!/^\d+$/.test(String(puntos).trim())) {
        alertify.warning('Ingrese solo números enteros para los puntos');
        return;
    }

    if (puntos < 1 || puntos > 100) {
        alertify.warning('Los puntos totales deben estar entre 1 y 100');
        return;
    }

    let datos = {
        idEvaluacion: idEvaluacion,
        idTipoNota: tipo,
        nombre: nombre,
        puntos_total: puntos,
        fecha_evaluacion: fecha || null
    };
    //para evitar múltiples clicks mientras se procesa la solicitud
    $('#btnActualizarEvaluacion').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Actualizando...');

    $.ajax({
        type: 'POST',
        url: 'api/actualizar_evaluacion.php',
        data: JSON.stringify(datos),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            $('#btnActualizarEvaluacion').prop('disabled', false).html('Actualizar');
            if (response.success) {
                alertify.success(response.message);
                let modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarEvaluacion'));
                if (modal) modal.hide();
                setTimeout(function() {
                    cargarEvaluaciones();
                }, 300);
            } else {
                alertify.error(response.error || 'Error al actualizar');
            }
        },
        error: function() {
            $('#btnActualizarEvaluacion').prop('disabled', false).html('Actualizar');
            alertify.error('Error en la comunicación');
        }
    });
});

// ===============================
// EVENTOS - ELIMINAR EVALUACIÓN
// ===============================
$(document).on('click', '.btn-eliminar-eval', function() {
    let idEval = $(this).data('id');
    let tieneNotas = parseInt($(this).data('notas') || 0) > 0;

    if (tieneNotas) {
        alertify.error('No se puede eliminar: esta evaluación ya tiene notas registradas');
        return;
    }

    alertify.confirm('¿Eliminar evaluación?', 'Esta acción eliminará la evaluación permanentemente',
        function() {
            //para evitar múltiples clicks mientras se procesa la solicitud
            const $btnElim = $(`.btn-eliminar-eval[data-id="${idEval}"]`);
            $btnElim.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            $.ajax({
                type: 'POST',
                url: 'api/eliminar_evaluacion.php',
                data: JSON.stringify({ idEvaluacion: idEval }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    $btnElim.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                    if (response.success) {
                        alertify.success('Evaluación eliminada');
                        cargarEvaluaciones();
                    } else {
                        alertify.error(response.error || 'Error al eliminar');
                    }
                }
            });
        },
        function() {}
    );
});

</script>
