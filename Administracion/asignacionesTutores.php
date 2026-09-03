<?php 
    $ruta = "../";

    // 1. Incluimos el header
    include __DIR__ . '/includes/header.php'; 
    
    // 2. Incluimos la conexión
    require_once __DIR__ . '/../servicios/conexion.php';

    // 3. Obtenemos los datos de la tabla "alumno" para mostrar en el select del formulario

    $alumnos = "SELECT idAlumno, CONCAT(nombres, ' ', apellidos, '-', cedula) as nombre_alumno FROM alumno WHERE estado = 'Activo'";
    $lista_alumnos = buscar_datos($alumnos);

    $tutores = "SELECT idTutor, CONCAT(nombres, ' ', apellidos, '-', cedula) as nombre_tutor FROM tutor WHERE estado = 'Activo'";
    $lista_tutores = buscar_datos($tutores);

?>

<!-- DataTables -->
<link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css">
<script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>
<script src="<?php echo $ruta; ?>dt/jquery.dataTables.min.js"></script>
<script src="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.js"></script>

<script src="<?php echo $ruta; ?>/dt/botones/jszip.min.js"></script>
<script src="<?php echo $ruta; ?>/dt/botones/pdfmake.min.js"></script>
<script src="<?php echo $ruta; ?>/dt/botones/vfs_fonts.js"></script>

<script src="<?php echo $ruta; ?>/dt/botones/dataTables.buttons.min.js"></script>
<script src="<?php echo $ruta; ?>/dt/botones/buttons.bootstrap5.min.js"></script>
<script src="<?php echo $ruta; ?>/dt/botones/buttons.html5.min.js"></script>
<script src="<?php echo $ruta; ?>/dt/botones/buttons.print.min.js"></script>

    <div class="main-content">

        <h2 class="h3 mb-0 text-gray-800"> Asignaciones de Tutores</h2>
        <br>


        <!-- FILTROS -->
        <div class="row">
            <div class="col-md-6">
                <div class="card p-3 mb-3 shadow-sm">
                    <h2 class="h3 mb-0 text-gray-800"> Filtros por Tutor</h2>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="idTutorSelect" class="form-label">Tutores</label>
                                <input class="form-control" name="idTutor" id="idTutorSelect" list="tutoresList" required placeholder="Seleccione un tutor...">
                                <datalist id="tutoresList">
                                    <?php if($lista_tutores): foreach($lista_tutores as $tutor): ?>
                                    <option value="<?php echo $tutor['nombre_tutor']; ?>" data-id="<?php echo $tutor['idTutor']; ?>"></option>
                                    <?php endforeach; endif; ?>
                                </datalist>
                            </div>
                        </div>

                        <div class="col-mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <button type="button" class="btn btn-success" id="btnCargarAsignacionTutores" disabled>
                                    <i class="bi bi-arrow-repeat"></i> Cargar Alumnos Asignados
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-3 mb-3 shadow-sm">
                    <h2 class="h3 mb-0 text-gray-800"> Filtros por Alumno</h2>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="idAlumnoSelect" class="form-label">Alumnos</label>
                                <input class="form-control" name="idAlumno" id="idAlumnoSelect" list="alumnosList" required placeholder="Seleccione un alumno...">
                                <datalist id="alumnosList">
                                    <?php if($lista_alumnos): foreach($lista_alumnos as $alumno): ?>
                                    <option value="<?php echo $alumno['nombre_alumno']; ?>" data-id="<?php echo $alumno['idAlumno']; ?>"></option>
                                    <?php endforeach; endif; ?>
                                </datalist>
                            </div>
                        </div>

                        <div class="col-mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <button type="button" class="btn btn-success" id="btnCargarAsignacionAlumnos" disabled>
                                    <i class="bi bi-arrow-repeat"></i> Cargar Tutores Asignados
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACCIONES -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                <i class="bi bi-person-plus-fill"></i> Nueva Asignación
            </button>
        </div>

        <!-- TABLA -->
        <div class="card p-3 shadow-sm mb-4" style="display:none;" id="divTablaAsignaciones">
            <div class="card-body">
                <div id="div-docentes" class="table-responsive">

                    <table id="tblAlumnoTutor" class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;">#</th>
                                <th style="width:30%;" class="text-center">Tutor</th>
                                <th width="20%" class="text-center">Alumno</th>
                                <th class="text-center">Parentesco</th>
                                <th class="text-center">Tutor Principal</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            
            </div>
        </div>
    </div>

    <!-- MODAL NUEVA ASIGNACION -->
    <div class="modal fade" id="modalRegistro" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Nueva Asignación</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <form action="api/asignacionesTutores_guardar.php" method="POST" id="formAsignacion">

                        
                        <div class="mb-3">
                            <label for="idTutor" class="form-label">Tutores</label>
                            <input class="form-control" name="idTutor" id="idTutor" list="tutoresList" required placeholder="Seleccione un tutor...">
                            <datalist id="tutoresList">
                                <?php if($lista_tutores): foreach($lista_tutores as $tutor): ?>
                                <option value="<?php echo $tutor['nombre_tutor']; ?>" data-id="<?php echo $tutor['idTutor']; ?>"></option>
                                <?php endforeach; endif; ?>
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="idAlumno" class="form-label">Alumnos</label>
                            <input class="form-control" name="idAlumno" id="idAlumno" list="alumnosList" required placeholder="Seleccione un alumno...">
                            <datalist id="alumnosList">
                                <?php if($lista_alumnos): foreach($lista_alumnos as $alumno): ?>
                                <option value="<?php echo $alumno['nombre_alumno']; ?>" data-id="<?php echo $alumno['idAlumno']; ?>"></option>
                                <?php endforeach; endif; ?>
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="principal" class="form-label">Es el Tutor Principal?</label>
                            <select class="form-select" name="principal" id="principal" required>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>


                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle-fill"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
    <!-- FIN NUEVA ASIGNACION -->

    <!-- MODAL MODIFICAR ASIGNACION -->
    <div class="modal fade" id="modalEditar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-warning text-dark">
                    <h1 class="modal-title fs-5">Editar Asignación</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="formEditarAsignacion">

                       <input type="hidden" name="idAlumnoTutor" id="id_edit">

                        <div class="mb-3">
                            <label for="idTutor_edit" class="form-label">Tutores</label>
                            <input class="form-control" name="idTutor" id="idTutor_edit" list="tutoresListEdit" required placeholder="Seleccione un tutor...">
                            <datalist id="tutoresListEdit">
                                <?php if($lista_tutores): foreach($lista_tutores as $tutor): ?>
                                <option value="<?php echo $tutor['nombre_tutor']; ?>" data-id="<?php echo $tutor['idTutor']; ?>"></option>
                                <?php endforeach; endif; ?>
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="idAlumno_edit" class="form-label">Alumnos</label>
                            <input class="form-control" name="idAlumno" id="idAlumno_edit" list="alumnosListEdit" required placeholder="Seleccione un alumno...">
                            <datalist id="alumnosListEdit">
                                <?php if($lista_alumnos): foreach($lista_alumnos as $alumno): ?>
                                <option value="<?php echo $alumno['nombre_alumno']; ?>" data-id="<?php echo $alumno['idAlumno']; ?>"></option>
                                <?php endforeach; endif; ?>
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="principal_edit" class="form-label">Es el Tutor Principal?</label>
                            <select class="form-select" name="principal" id="principal_edit" required>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                            
                       
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i>Actualizar</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
    <!-- FIN MODIFICAR ASIGNACION -->


    <!-- MODAL ELIMINAR ASIGNACION -->
    <div class="modal fade" id="modalEliminar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-danger text-white">
                    <h1 class="modal-title fs-5">Eliminar Asignación</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-danger fw-bold">¿Estás seguro de que deseas eliminar este registro?</p>

                    <form id="formEliminarAsignacion">

                       <input type="hidden" name="idAlumnoTutor" id="id_eliminar">

                       <div class="mb-3">
                            <label for="idTutor_delete" class="form-label">Tutores</label>
                            <input class="form-control" name="idTutor" id="idTutor_delete" list="tutoresListDelete" readonly disabled placeholder="Seleccione un tutor...">
                            <datalist id="tutoresListDelete">
                                <?php if($lista_tutores): foreach($lista_tutores as $tutor): ?>
                                <option value="<?php echo $tutor['nombre_tutor']; ?>" data-id="<?php echo $tutor['idTutor']; ?>"></option>
                                <?php endforeach; endif; ?>
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="idAlumno_delete" class="form-label">Alumnos</label>
                            <input class="form-control" name="idAlumno" id="idAlumno_delete" list="alumnosListDelete" readonly disabled placeholder="Seleccione un alumno...">
                            <datalist id="alumnosListDelete">
                                <?php if($lista_alumnos): foreach($lista_alumnos as $alumno): ?>
                                <option value="<?php echo $alumno['nombre_alumno']; ?>" data-id="<?php echo $alumno['idAlumno']; ?>"></option>
                                <?php endforeach; endif; ?>
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="principal_delete" class="form-label">Es el Tutor Principal?</label>
                            <select class="form-select" name="principal" id="principal_delete" readonly disabled>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                       
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i>Eliminar</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
    <!-- FIN ELIMINAR ASIGNACION -->


<?php include __DIR__ . '/includes/footer.php'?>

    <script src="reportes.js"></script>

    <?php
        $logo = base64_encode(file_get_contents('../img/Logo-Santa.jpeg'));
        $logoMEC = base64_encode(file_get_contents('../img/Logo-MEC.png'));
    ?>

    <script>
        const logoSanta = "data:image/jpeg;base64,<?php echo $logo; ?>";
        const logoMEC = "data:image/jpeg;base64,<?php echo $logoMEC; ?>";
    </script>

<script>

    const idTutorSelect = document.getElementById('idTutorSelect');
    const idAlumnoSelect = document.getElementById('idAlumnoSelect');
    const btnCargarT = document.getElementById('btnCargarAsignacionTutores');
    const btnCargarA = document.getElementById('btnCargarAsignacionAlumnos');
    const divTabla = document.getElementById('divTablaAsignaciones');
    let tablaInstance = null;

    // Habilitar/deshabilitar botón al cambiar tutor en el filtro
    idTutorSelect.addEventListener('change', function() {
        if (this.value && this.value !== "Todos") {
            btnCargarT.disabled = false;
            divTabla.style.display = "block";
        } else {
            btnCargarT.disabled = true;
            divTabla.style.display = "none";
            // Destruir tabla si existe
            if (tablaInstance) {
                tablaInstance.destroy();
                tablaInstance = null;
                document.querySelector('#tblAlumnoTutor tbody').innerHTML = '';
            }
        }
    });

    idAlumnoSelect.addEventListener('change', function() {
        if (this.value && this.value !== "Todos") {
            btnCargarA.disabled = false;
            divTabla.style.display = "block";
        } else {
            btnCargarA.disabled = true;
            divTabla.style.display = "none";
            // Destruir tabla si existe
            if (tablaInstance) {
                tablaInstance.destroy();
                tablaInstance = null;
                document.querySelector('#tblAlumnoTutor tbody').innerHTML = '';
            }
        }
    });

    function cargarAsignaciones(tipo) {
        const idTutor = idTutorSelect.dataset.selectedId;
        const idAlumno = idAlumnoSelect.dataset.selectedId;

        if (tipo === 'tutor' && !idTutor) {
            alertify.error("Por favor, seleccione un tutor válido de la lista.");
            return;
        }

        if (tipo === 'alumno' && !idAlumno) {
            alertify.error("Por favor, seleccione un alumno válido de la lista.");
            return;
        }

        if (tablaInstance) {
            tablaInstance.destroy();
            tablaInstance = null;
            document.querySelector('#tblAlumnoTutor tbody').innerHTML = '';
        }

        tablaInstance = $('#tblAlumnoTutor').DataTable({
            destroy: true,
            ajax: {
                url: 'listar_asignacionesTutores.php',
                data: function(d) {
                    if (idTutor) d.idTutor = idTutor;
                    if (idAlumno) d.idAlumno = idAlumno;
                }
            },
            language: {
                url: '<?php echo $ruta; ?>dt/es-ES.json'
            },
            responsive: true,
            dom: 'Bfrtip',
            pageLength: 5,
            buttons: [
                {
                    extend:    'excelHtml5',
                    text:      '<i class="bi bi-file-earmark-excel"></i> ',
                    titleAttr: 'Exportar a Excel',
                    className: 'btn btn-success',
                    title:     'Lista de Asignaciones',
                    filename:  'Reporte_Asignaciones',
                    exportOptions: { columns: [ 0, 1, 2, 3, 4 ] }
                },
                {
                    extend:'pdfHtml5',
                    text:'<i class="bi bi-file-earmark-pdf"></i>',
                    className:'btn btn-danger',
                    title:'Lista de Asignaciones de Tutor-Alumno',
                    filename:'Reporte_AsignacionesAlumnoTutor',
                    exportOptions:{ columns:[0,1,2,3,4] },
                    customize:function(doc){
                        doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                        encabezadoPDF(doc, 'REPORTE DE ASIGNACIONES', logoMEC, logoSanta);
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer"></i>',
                    titleAttr: 'Imprimir',
                    className: 'btn btn-info',
                    title: '',
                    exportOptions: { columns: [0, 1, 2, 3, 4] },
                    customize: function (win) {
                        const fecha = new Date().toLocaleDateString();
                        const hora = new Date().toLocaleTimeString();
                        const tablaHTML = $(win.document.body).find('table').prop('outerHTML');
                        win.document.body.innerHTML = `
                            <style>
                                @page { size: A4 portrait; margin: 15mm; }
                                body { font-family: Arial, sans-serif; color: #000; font-size: 11px; margin: 0; padding: 0; }
                                .reporte-container { width: 100%; max-width: 180mm; margin: 0 auto; }
                                .encabezado { width: 100%; margin-bottom: 10px; }
                                .encabezado table { width: 100%; border-collapse: collapse; border: none !important; }
                                .encabezado td { border: none !important; vertical-align: middle; }
                                .logo-izq { width: 18%; text-align: left; }
                                .logo-centro { width: 64%; text-align: center; line-height: 1.2; }
                                .logo-der { width: 18%; text-align: right; }
                                .logo-izq img, .logo-der img { height: 60px; max-width: 100%; }
                                .titulo1 { font-size: 11pt; font-weight: bold; }
                                .titulo2 { font-size: 12pt; font-weight: bold; }
                                .titulo3 { font-size: 14pt; font-weight: bold; }
                                .subtitulo { font-size: 9pt; }
                                .titulo-reporte { margin-top: 8px; font-size: 10pt; font-weight: bold; }
                                .meta { text-align: right; margin: 6px 0 12px 0; font-size: 9pt; }
                                .linea { border-top: 1px solid #555; margin-top: 8px; margin-bottom: 6px; }
                                .tabla-reporte { width: 100%; border-collapse: collapse !important; table-layout: fixed; margin: 0 auto; font-size: 9pt; }
                                .tabla-reporte th, .tabla-reporte td { border: 1px solid #000 !important; padding: 6px 5px; text-align: center; vertical-align: middle; word-wrap: break-word; white-space: normal; }
                                .tabla-reporte th { background: #f2f2f2 !important; font-weight: bold; }
                                .tabla-reporte th:nth-child(1), .tabla-reporte td:nth-child(1) { width: 8%; text-align: center; }
                                .tabla-reporte th:nth-child(2), .tabla-reporte td:nth-child(2) { width: 38%; text-align: center; }
                                .tabla-reporte th:nth-child(3), .tabla-reporte td:nth-child(3) { width: 38%; text-align: center; }
                                .tabla-reporte th:nth-child(4), .tabla-reporte td:nth-child(4) { width: 16%; text-align: center; }
                                .tabla-reporte th:nth-child(5), .tabla-reporte td:nth-child(5) { width: 16%; text-align: center; }
                                .pie { margin-top: 10px; font-size: 8pt; display: flex; justify-content: space-between; }
                            </style>
                            <div class="reporte-container">
                                <div class="encabezado">
                                    <table>
                                        <tr>
                                            <td class="logo-izq"><img src="${logoMEC}"></td>
                                            <td class="logo-centro">
                                                <div class="titulo1">DIRECCIÓN GENERAL DE EDUCACIÓN MEDIA</div>
                                                <div class="titulo2">INSTITUCIÓN EDUCATIVA DIOCESANA</div>
                                                <div class="titulo3">SANTA TERESITA</div>
                                                <div class="subtitulo">Concepción - Paraguay</div>
                                                <div class="titulo-reporte">REPORTE DE ASIGNACIONES</div>
                                            </td>
                                            <td class="logo-der"><img src="${logoSanta}"></td>
                                        </tr>
                                    </table>
                                    <div class="linea"></div>
                                    <div class="meta">Fecha de emisión: ${fecha} ${hora}</div>
                                </div>
                                <div id="contenedor-tabla"></div>
                            </div>
                        `;
                        $(win.document.body).find('#contenedor-tabla').html(tablaHTML);
                        $(win.document.body).find('#contenedor-tabla table').addClass('tabla-reporte');
                    }
                }
            ],
            columnDefs: [
                { orderable: false, targets: 5 }
            ],
            columns: [
                { data: 'idAlumnoTutor', className: 'text-center' },
                { data: 'nombre_tutor' },
                { data: 'nombre_alumno', className: 'text-center' },
                { data: 'parentesco', className: 'text-center' },
                { data: 'es_principal', render: function(data) {
                        return data === 'Sí' ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>';
                    },
                    className: 'text-center'
                },
                { data: null, className: 'text-center', render: function(data) {
                        return `<button class="btn btn-warning bi bi-pencil-square btn-editar"
                                data-id="${data.idAlumnoTutor}"
                                data-tutor="${data.idTutor}"
                                data-nombre-tutor="${data.nombre_tutor}"
                                data-alumno="${data.idAlumno}"
                                data-nombre-alumno="${data.nombre_alumno}"
                                data-principal="${data.es_principal}"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditar"></button> ` +
                                `<button class="btn btn-danger bi bi-trash btn-eliminar"
                                data-id="${data.idAlumnoTutor}"
                                data-tutor="${data.idTutor}"
                                data-nombre-tutor="${data.nombre_tutor}"
                                data-alumno="${data.idAlumno}"
                                data-nombre-alumno="${data.nombre_alumno}"
                                data-principal="${data.es_principal}"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEliminar"></button>`;
                    }
                }
            ]
        });
    }

    // Extraer ID del datalist cuando se selecciona
    function obtenerIdDeDatalist(inputId, datalistId) {
        const input = document.getElementById(inputId);
        const datalist = document.getElementById(datalistId);
        
        input.addEventListener('change', function() {
            const selectedOption = Array.from(datalist.options).find(option => option.value === input.value);
            if (selectedOption && selectedOption.dataset.id) {
                input.dataset.selectedId = selectedOption.dataset.id;
            }
        });
    }

    obtenerIdDeDatalist('idTutorSelect', 'tutoresList');
    obtenerIdDeDatalist('idAlumnoSelect', 'alumnosList');
    obtenerIdDeDatalist('idTutor', 'tutoresList');
    obtenerIdDeDatalist('idAlumno', 'alumnosList');
    obtenerIdDeDatalist('idTutor_edit', 'tutoresListEdit');
    obtenerIdDeDatalist('idAlumno_edit', 'alumnosListEdit');

    // Cargar asignaciones al hacer click en los botones
    btnCargarT.addEventListener('click', () => cargarAsignaciones('tutor'));
    btnCargarA.addEventListener('click', () => cargarAsignaciones('alumno'));

    // Manejar envío del formulario
    const formAsignacion = document.getElementById('formAsignacion');
    const modalRegistro = document.getElementById('modalRegistro');
    
    formAsignacion.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        // Reemplazar los nombres con los IDs en el FormData
        const idTutorInput = document.getElementById('idTutor');
        const idAlumnoInput = document.getElementById('idAlumno');
        
        if (idTutorInput.dataset.selectedId) {
            formData.set('idTutor', idTutorInput.dataset.selectedId);
        }
        if (idAlumnoInput.dataset.selectedId) {
            formData.set('idAlumno', idAlumnoInput.dataset.selectedId);
        }

        fetch('api/asignacionesTutores_guardar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                alertify.success(data.msg);
                setTimeout(function(){ window.location.reload(); }, 500);
            } else {
                alertify.error(data.msg || 'Error al guardar la asignación.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alertify.error('Error en la solicitud: ' + error.message);
        });
    });



    // Cargar asignaciones al abrir el modal de edición
    var modalEditar = document.getElementById('modalEditar');

    modalEditar.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (button) {
            var idAlumnoTutor = button.getAttribute('data-id');
            var idTutor = button.getAttribute('data-tutor');
            var nombreTutor = button.getAttribute('data-nombre-tutor');
            var idAlumno = button.getAttribute('data-alumno');
            var nombreAlumno = button.getAttribute('data-nombre-alumno');
            var principal = button.getAttribute('data-principal');

            var idTutorEditInput = document.getElementById('idTutor_edit');
            var idAlumnoEditInput = document.getElementById('idAlumno_edit');

            document.getElementById('id_edit').value = idAlumnoTutor;
            idTutorEditInput.value = nombreTutor;
            idTutorEditInput.dataset.selectedId = idTutor;
            
            idAlumnoEditInput.value = nombreAlumno;
            idAlumnoEditInput.dataset.selectedId = idAlumno;
            
            document.getElementById('principal_edit').value = principal;
        }
    });

    document.getElementById('formEditarAsignacion').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(document.getElementById('formEditarAsignacion'));
        
        // Reemplazar los nombres con los IDs en el FormData
        const idTutorEditInput = document.getElementById('idTutor_edit');
        const idAlumnoEditInput = document.getElementById('idAlumno_edit');
        
        if (idTutorEditInput.dataset.selectedId) {
            formData.set('idTutor', idTutorEditInput.dataset.selectedId);
        }
        if (idAlumnoEditInput.dataset.selectedId) {
            formData.set('idAlumno', idAlumnoEditInput.dataset.selectedId);
        }

        console.log([...formData.entries()]);

        fetch('api/asignacionesTutores_actualizar.php', { 
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === true) {
                alertify.success(data.msg);
                setTimeout(function(){ window.location.reload(); }, 800);
            } else {
                alertify.error(data.msg);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alertify.error("Error en la comunicación con el servidor.");
        });
    });

    //Cargar datos al abrir el modal de eliminación
    var modalEliminar = document.getElementById('modalEliminar');

    modalEliminar.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (button) {
            var idAlumnoTutor = button.getAttribute('data-id');
            var idTutor = button.getAttribute('data-tutor');
            var nombreTutor = button.getAttribute('data-nombre-tutor');
            var idAlumno = button.getAttribute('data-alumno');
            var nombreAlumno = button.getAttribute('data-nombre-alumno');
            var principal = button.getAttribute('data-principal');

            document.getElementById('id_eliminar').value = idAlumnoTutor;
            document.getElementById('idTutor_delete').value = nombreTutor;
            document.getElementById('idAlumno_delete').value = nombreAlumno;
            document.getElementById('principal_delete').value = principal;
        }
    });

    // 2. CONFIRMAR Y ENVIAR ELIMINACIÓN
    document.getElementById('formEliminarAsignacion').addEventListener('submit', function(e) {
        e.preventDefault();

        alertify.confirm("Eliminar Asignación", "¿Está seguro que desea eliminar esta asignación permanentemente?",
            function() {
                var formData = new FormData(document.getElementById('formEliminarAsignacion'));
                fetch('api/asignacionesTutores_eliminar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === true) {
                        alertify.success(data.msg);
                        setTimeout(function(){ window.location.reload(); }, 500);
                    } else if (data.code === 'HAS_DEPENDENCIES') {
                        alertify.error(data.msg);
                    } else {
                        alertify.error(data.msg || 'Error al eliminar la asignación.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alertify.error("Error en el servidor.");
                });
            },
            function() {
                alertify.error('Cancelado');
            }
        ).set('labels', {ok:'Sí, Eliminar', cancel:'Cancelar'});
    });


</script>

