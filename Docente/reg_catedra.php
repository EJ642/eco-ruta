<?php
/**
 * ARCHIVO: Docente/reg_catedra.php
 * Página para que los docentes registren el diario de cátedra.
 */
$ruta = "../";
include __DIR__ . '/includes/header.php';
require_once __DIR__ . "/../servicios/conexion.php";

// Verificar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_name('DOCENTE_SESSION');
    session_start();
}

// Obtener ID del docente desde sesión
$docente_id = isset($_SESSION['docente_id']) ? intval($_SESSION['docente_id']) : 0;

if (!$docente_id) {
    header('Location: index.php');
    exit;
}

// ============================================================
// CONSULTA DE ASIGNACIONES CON ÉNFASIS Y FORMATO ABREVIADO
// ============================================================
// Formato abreviado: "Matemática - 1° CBC"
$sql_asig = "SELECT 
                dam.idAsignacion, 
                m.nombre as materia, 
                c.numero as curso_numero,
                c.nombre as curso_nombre,
                e.nombre as enfasis,
                
                CONCAT(
                    m.nombre, 
                    ' - ', 
                    c.numero, '° ',
                    CASE 
                        WHEN e.nombre = 'Ciencias Básicas' THEN 'CBC'
                        WHEN e.nombre = 'Ciencias Sociales' THEN 'CS'
                        WHEN e.nombre = 'Bachillerato Técnico en Ciencias Contables' THEN 'CONT'
                        WHEN e.nombre = 'Ninguno' THEN ''
                        ELSE LEFT(e.nombre, 3)
                    END
                ) as nombre_completo
             FROM docente_aula_materia dam
             INNER JOIN aula_materia am ON dam.idAulaMateria = am.idAulaMateria
             INNER JOIN materia m ON am.idMateria = m.idMateria
             INNER JOIN aula a ON am.idAula = a.idAula
             INNER JOIN curso c ON a.idCurso = c.idCurso
             INNER JOIN enfasis e ON a.idEnfasis = e.idEnfasis
             WHERE dam.idDocente = $docente_id AND dam.activo = 1
             ORDER BY c.numero, e.nombre, m.nombre";
$lista_asig = buscar_datos($sql_asig);

// Verificar si tiene asignaciones
$tiene_asignaciones = !empty($lista_asig);
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

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 22px;
    }

    .page-header .titulo h2 {
        margin-bottom: 2px;
    }

    .btn-nuevo-registro {
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(25, 135, 84, .25);
    }

    .filtros-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }

    .filtros-card label {
        font-weight: 600;
        font-size: 14px;
        color: #495057;
    }

    select option {
        padding: 6px 10px;
    }
</style>

<div class="main-content">

    <!-- HEADER -->
    <div class="page-header">
        <div class="titulo">
            <h2 class="fw-bold mb-0">Diario de Cátedra</h2>
            <small class="text-muted">Registro de clases, temas y observaciones</small>
        </div>

        <?php if ($tiene_asignaciones): ?>
            <button type="button" class="btn btn-success btn-lg btn-nuevo-registro" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                <i class="bi bi-journal-plus"></i> Nuevo Registro
            </button>
        <?php else: ?>
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle-fill"></i>
                No tiene asignaciones activas. Contacte al director.
            </div>
        <?php endif; ?>
    </div>

    <!-- FILTROS -->
    <div class="filtros-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="filtroAsignacion" class="form-label">Asignación</label>
                <select class="form-select" id="filtroAsignacion">
                    <option value="" selected>Todas las asignaciones</option>
                    <?php if ($lista_asig): foreach ($lista_asig as $asig): ?>
                        <option value="<?php echo $asig['idAsignacion']; ?>">
                            <?php echo htmlspecialchars($asig['nombre_completo']); ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="filtroFechaInicio" class="form-label">Desde</label>
                <input type="date" class="form-control" id="filtroFechaInicio">
            </div>
            <div class="col-md-3">
                <label for="filtroFechaFin" class="form-label">Hasta</label>
                <input type="date" class="form-control" id="filtroFechaFin">
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
                            <i class="bi bi-journal-text" style="font-size: 2rem; color: #0d6efd;"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0">Total Registros</p>
                            <h4 class="mb-0" id="totalRegistros">0</h4>
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
                            <i class="bi bi-clock-history" style="font-size: 2rem; color: #198754;"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0">Última Clase</p>
                            <h4 class="mb-0" id="ultimaClase">--</h4>
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
                            <i class="bi bi-calendar-week" style="font-size: 2rem; color: #fd7e14;"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0">Clases este mes</p>
                            <h4 class="mb-0" id="clasesMes">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card shadow-sm">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>Historial de Clases</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="tablaCatedra" width="100%">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Docente</th>
                            <th>Materia / Curso</th>
                            <th>Unidad</th>
                            <th>Tema</th>
                            <th>Observaciones</th>
                            <th>Horario</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyCatedra"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: NUEVO REGISTRO -->
<!-- ============================================================ -->
<div class="modal fade" id="modalRegistro" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-journal-plus me-2"></i>Registrar Nueva Clase</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCatedra">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="idAsignacion" class="form-label fw-semibold">Asignación *</label>
                            <select name="idAsignacion" id="idAsignacion" class="form-select" required>
                                <option value="" selected disabled>Seleccione...</option>
                                <?php if ($lista_asig): foreach ($lista_asig as $asig): ?>
                                    <option value="<?php echo $asig['idAsignacion']; ?>">
                                        <?php echo htmlspecialchars($asig['nombre_completo']); ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <small class="text-muted">Formato: Materia - Curso (Énfasis)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha" class="form-label fw-semibold">Fecha *</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="horaInicio" class="form-label fw-semibold">Hora Inicio *</label>
                            <input type="time" name="horaInicio" id="horaInicio" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="horaFin" class="form-label fw-semibold">Hora Fin *</label>
                            <input type="time" name="horaFin" id="horaFin" class="form-control" required>
                            <small class="text-muted">Debe ser mayor a la hora de inicio</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="unidad" class="form-label fw-semibold">Unidad *</label>
                        <input type="text" name="unidad" id="unidad" class="form-control" 
                               placeholder="Ej: Unidad 1 - Números Reales" required>
                    </div>
                    <div class="mb-3">
                        <label for="tema" class="form-label fw-semibold">Tema *</label>
                        <textarea name="tema" id="tema" class="form-control" rows="2" 
                                  placeholder="Describa el tema desarrollado en la clase" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label fw-semibold">Observaciones</label>
                        <input type="text" name="observaciones" id="observaciones" class="form-control"
                               placeholder="Observaciones adicionales (opcional)">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarRegistro">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: EDITAR REGISTRO -->
<!-- ============================================================ -->
<div class="modal fade" id="modalEditar" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Registro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarCatedra">
                    <input type="hidden" name="id_catedra" id="id_edit">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="asig_edit" class="form-label fw-semibold">Asignación *</label>
                            <select name="idAsignacion" id="asig_edit" class="form-select" required>
                                <?php if ($lista_asig): foreach ($lista_asig as $asig): ?>
                                    <option value="<?php echo $asig['idAsignacion']; ?>">
                                        <?php echo htmlspecialchars($asig['nombre_completo']); ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <small class="text-muted">Formato: Materia - Curso (Énfasis)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_edit" class="form-label fw-semibold">Fecha *</label>
                            <input type="date" name="fecha" id="fecha_edit" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ini_edit" class="form-label fw-semibold">Hora Inicio *</label>
                            <input type="time" name="horaInicio" id="ini_edit" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fin_edit" class="form-label fw-semibold">Hora Fin *</label>
                            <input type="time" name="horaFin" id="fin_edit" class="form-control" required>
                            <small class="text-muted">Debe ser mayor a la hora de inicio</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="uni_edit" class="form-label fw-semibold">Unidad *</label>
                        <input type="text" name="unidad" id="uni_edit" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="tema_edit" class="form-label fw-semibold">Tema *</label>
                        <textarea name="tema" id="tema_edit" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="obs_edit" class="form-label fw-semibold">Observaciones</label>
                        <input type="text" name="observaciones" id="obs_edit" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="btnActualizarRegistro">
                    <i class="bi bi-pencil-square"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: ELIMINAR REGISTRO -->
<!-- ============================================================ -->
<div class="modal fade" id="modalEliminar" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Eliminar Registro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEliminarCatedra">
                    <input type="hidden" name="id_catedra" id="id_delete">
                    <p class="text-danger fw-bold">¿Desea eliminar permanentemente este registro?</p>
                    <div class="alert alert-warning">
                        <strong>Tema:</strong> <span id="tema_delete"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnEliminarRegistro">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
    // ===============================
    // VARIABLES GLOBALES
    // ===============================
    let dataTableCatedra;
    let filtroAsignacion = '';
    let filtroFechaInicio = '';
    let filtroFechaFin = '';

    // ===============================
    // INICIALIZAR DATATABLE
    // ===============================
    $(document).ready(function() {
        // Destruir instancia anterior si existe
        if ($.fn.DataTable.isDataTable('#tablaCatedra')) {
            $('#tablaCatedra').DataTable().destroy();
        }

        dataTableCatedra = $('#tablaCatedra').DataTable({
            "language": {
                "url": "<?php echo $ruta; ?>dt/es-ES.json",
                "emptyTable": "No hay registros de cátedra"
            },
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": "api/listar_catedra.php",
                "dataSrc": "data",
                "type": "GET",
                "data": function(d) {
                    return {
                        idAsignacion: filtroAsignacion,
                        fechaInicio: filtroFechaInicio,
                        fechaFin: filtroFechaFin
                    };
                }
            },
            "columns": [{
                    "data": "fecha",
                    "render": function(data) {
                        if (data) {
                            let date = new Date(data + 'T00:00:00');
                            return date.toLocaleDateString('es-ES');
                        }
                        return '';
                    },
                    "className": "text-nowrap"
                },
                {
                    "data": null,
                    "render": function(data) {
                        return data.nombres + " " + data.apellidos;
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        return '<strong>' + data.materia + '</strong><br><small>' + data.curso + '</small>';
                    }
                },
                {
                    "data": "unidad"
                },
                {
                    "data": "tema"
                },
                {
                    "data": "observaciones",
                    "render": function(data) {
                        return data ? '<small>' + data + '</small>' : '---';
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        return data.horaInicio + " - " + data.horaFin;
                    },
                    "className": "text-nowrap"
                },
                {
                    "data": null,
                    "className": "text-center",
                    "orderable": false,
                    "render": function(data) {
                        return `<button class="btn btn-info btn-sm btn-editar-registro" 
                                    data-id="${data.idRegCatedra}"
                                    data-asig="${data.idAsignacion}"
                                    data-fecha="${data.fecha}"
                                    data-ini="${data.horaInicio}"
                                    data-fin="${data.horaFin}"
                                    data-uni="${data.unidad ? data.unidad.replace(/"/g, '&quot;') : ''}"
                                    data-tema="${data.tema ? data.tema.replace(/"/g, '&quot;') : ''}"
                                    data-obs="${data.observaciones ? data.observaciones.replace(/"/g, '&quot;') : ''}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-danger btn-sm btn-eliminar-registro" 
                                    data-id="${data.idRegCatedra}"
                                    data-tema="${data.tema ? data.tema.replace(/"/g, '&quot;') : ''}">
                                    <i class="bi bi-trash"></i>
                                </button>`;
                    }
                }
            ],
            "order": [
                [0, "desc"]
            ],
            "responsive": true,
            "autoWidth": false,
            "drawCallback": function(settings) {
                actualizarEstadisticas();
            }
        });

        // ===============================
        // EVENTOS DE FILTRO
        // ===============================
        $('#filtroAsignacion').change(function() {
            filtroAsignacion = $(this).val();
            dataTableCatedra.ajax.reload();
        });

        $('#filtroFechaInicio').change(function() {
            filtroFechaInicio = $(this).val();
            dataTableCatedra.ajax.reload();
        });

        $('#filtroFechaFin').change(function() {
            filtroFechaFin = $(this).val();
            dataTableCatedra.ajax.reload();
        });

        // ===============================
        // FUNCIÓN: Actualizar estadísticas
        // ===============================
        function actualizarEstadisticas() {
            let data = dataTableCatedra.rows().data();
            let total = data.length;
            $('#totalRegistros').text(total);

            if (total > 0) {
                let ultima = data[0];
                let fecha = new Date(ultima.fecha + 'T00:00:00');
                $('#ultimaClase').text(fecha.toLocaleDateString('es-ES'));
            } else {
                $('#ultimaClase').text('--');
            }

            let hoy = new Date();
            let mes = hoy.getMonth();
            let anio = hoy.getFullYear();
            let countMes = 0;
            data.each(function(row) {
                let fecha = new Date(row.fecha + 'T00:00:00');
                if (fecha.getMonth() === mes && fecha.getFullYear() === anio) {
                    countMes++;
                }
            });
            $('#clasesMes').text(countMes);
        }

        // ===============================
        // EVENTOS - EDITAR
        // ===============================
        $('#tablaCatedra tbody').on('click', '.btn-editar-registro', function() {
            const btn = $(this);
            $('#id_edit').val(btn.data('id'));
            $('#asig_edit').val(btn.data('asig'));
            $('#fecha_edit').val(btn.data('fecha'));
            $('#ini_edit').val(btn.data('ini'));
            $('#fin_edit').val(btn.data('fin'));
            $('#uni_edit').val(btn.data('uni'));
            $('#tema_edit').val(btn.data('tema'));
            $('#obs_edit').val(btn.data('obs') || '');

            var myModal = new bootstrap.Modal(document.getElementById('modalEditar'));
            myModal.show();
        });

        // ===============================
        // EVENTOS - ELIMINAR
        // ===============================
        $('#tablaCatedra tbody').on('click', '.btn-eliminar-registro', function() {
            const btn = $(this);
            $('#id_delete').val(btn.data('id'));
            $('#tema_delete').text(btn.data('tema') || 'Sin tema');

            var myModal = new bootstrap.Modal(document.getElementById('modalEliminar'));
            myModal.show();
        });

        // ===============================
        // GUARDAR NUEVO REGISTRO
        // ===============================
        $('#btnGuardarRegistro').on('click', function() {
            const form = document.getElementById('formCatedra');
            const formData = new FormData(form);

            const idAsignacion = formData.get('idAsignacion');
            const fecha = formData.get('fecha');
            const horaInicio = formData.get('horaInicio');
            const horaFin = formData.get('horaFin');
            const unidad = formData.get('unidad');
            const tema = formData.get('tema');

            if (!idAsignacion || !fecha || !horaInicio || !horaFin || !unidad || !tema) {
                alertify.warning('Todos los campos obligatorios deben ser llenados');
                return;
            }

            if (horaInicio >= horaFin) {
                alertify.warning('La hora de inicio debe ser menor que la hora de fin');
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Guardando...');

            $.ajax({
                type: 'POST',
                url: 'api/reg_catedra_guardar.php',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="bi bi-save"></i> Guardar');
                    if (response.status) {
                        alertify.success(response.msg);
                        let modal = bootstrap.Modal.getInstance(document.getElementById('modalRegistro'));
                        if (modal) modal.hide();
                        dataTableCatedra.ajax.reload();
                        form.reset();
                        document.getElementById('fecha').value = '<?php echo date('Y-m-d'); ?>';
                    } else {
                        alertify.error(response.msg || 'Error al guardar');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-save"></i> Guardar');
                    console.error('Error:', xhr.responseText);
                    alertify.error('Error en la comunicación con el servidor');
                }
            });
        });

        // ===============================
        // ACTUALIZAR REGISTRO
        // ===============================
        $('#btnActualizarRegistro').on('click', function() {
            const form = document.getElementById('formEditarCatedra');
            const formData = new FormData(form);

            const id = formData.get('id_catedra');
            const idAsignacion = formData.get('idAsignacion');
            const fecha = formData.get('fecha');
            const horaInicio = formData.get('horaInicio');
            const horaFin = formData.get('horaFin');
            const unidad = formData.get('unidad');
            const tema = formData.get('tema');

            if (!id || !idAsignacion || !fecha || !horaInicio || !horaFin || !unidad || !tema) {
                alertify.warning('Todos los campos obligatorios deben ser llenados');
                return;
            }

            if (horaInicio >= horaFin) {
                alertify.warning('La hora de inicio debe ser menor que la hora de fin');
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Actualizando...');

            $.ajax({
                type: 'POST',
                url: 'api/reg_catedra_actualizar.php',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="bi bi-pencil-square"></i> Actualizar');
                    if (response.status) {
                        alertify.success(response.msg);
                        let modal = bootstrap.Modal.getInstance(document.getElementById('modalEditar'));
                        if (modal) modal.hide();
                        dataTableCatedra.ajax.reload();
                    } else {
                        alertify.error(response.msg || 'Error al actualizar');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-pencil-square"></i> Actualizar');
                    console.error('Error:', xhr.responseText);
                    alertify.error('Error en la comunicación con el servidor');
                }
            });
        });

        // ===============================
        // ELIMINAR REGISTRO
        // ===============================
        $('#btnEliminarRegistro').on('click', function() {
            const id = $('#id_delete').val();

            if (!id) {
                alertify.warning('ID no válido');
                return;
            }

            alertify.confirm(
                'Confirmar Eliminación',
                '¿Está seguro que desea eliminar permanentemente este registro? Esta acción no se puede deshacer.',
                function() {
                    const btn = $('#btnEliminarRegistro');
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Eliminando...');

                    $.ajax({
                        type: 'POST',
                        url: 'api/reg_catedra_eliminar.php',
                        data: JSON.stringify({ id_catedra: id }),
                        contentType: 'application/json',
                        dataType: 'json',
                        success: function(response) {
                            btn.prop('disabled', false).html('<i class="bi bi-trash"></i> Eliminar');
                            if (response.status) {
                                alertify.success(response.msg);
                                let modal = bootstrap.Modal.getInstance(document.getElementById('modalEliminar'));
                                if (modal) modal.hide();
                                dataTableCatedra.ajax.reload();
                            } else {
                                alertify.error(response.msg || 'Error al eliminar');
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html('<i class="bi bi-trash"></i> Eliminar');
                            console.error('Error:', xhr.responseText);
                            alertify.error('Error en la comunicación con el servidor');
                        }
                    });
                },
                function() {}
            ).set('labels', { ok: 'Eliminar', cancel: 'Cancelar' });
        });

        // ===============================
        // LIMPIAR MODAL AL CERRAR
        // ===============================
        $('#modalRegistro').on('hidden.bs.modal', function() {
            document.getElementById('formCatedra').reset();
            document.getElementById('fecha').value = '<?php echo date('Y-m-d'); ?>';
        });

        $('#modalEditar').on('hidden.bs.modal', function() {
            document.getElementById('formEditarCatedra').reset();
        });

        $('#modalEliminar').on('hidden.bs.modal', function() {
            $('#id_delete').val('');
            $('#tema_delete').text('');
        });

        setTimeout(function() {
            actualizarEstadisticas();
        }, 500);
    });
</script>