<?php 
    $ruta = "../";

    // 1. Incluimos el header
    include __DIR__ . '/includes/header.php'; 
    
    // 2. Incluimos la conexión
    require_once __DIR__ . '/../servicios/conexion.php';

    // 3. Obtenemos los datos de la tabla "docente_aula_materia"

    $aulas = "SELECT idAula, CONCAT(c.numero, '° ', e.nombre, ' - ', t.turno) as nombre_aula
              FROM aula a
              JOIN curso c ON a.idCurso = c.idCurso
              JOIN enfasis e ON a.idEnfasis = e.idEnfasis
              JOIN turno t ON c.idTurno = t.idTurno
              JOIN anio_lectivo an ON a.idAnio = an.idAnio
              WHERE a.activo = 'Sí' AND an.activo = 'Sí'";
    $lista_aulas = buscar_datos($aulas);

    $materias = "SELECT idMateria, nombre FROM materia WHERE activo = 'Sí'";
    $lista_materias = buscar_datos($materias);

?>

<!-- DataTables -->
<link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css">
<script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>
<script src="<?php echo $ruta; ?>dt/jquery.dataTables.min.js"></script>
<script src="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.js"></script>

    <div class="main-content">

        <h2 class="h3 mb-0 text-gray-800"> Asignaciones de Materias</h2>
        <br>


        <!-- FILTROS -->
        
        <div class="card p-3 mb-3 shadow-sm">
            <h2 class="h3 mb-0 text-gray-800"> Filtros</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="idAula" class="form-label">Aulas</label>
                        <select class="form-select" name="idAula" id="idAula">
                            <option value="Todos" selected>Seleccione un aula...</option>
                            <?php if($lista_aulas): foreach($lista_aulas as $aula): ?>
                            <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['nombre_aula']; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <button type="button" class="btn btn-success" id="btnCargarAsignacion" disabled>
                            <i class="bi bi-arrow-repeat"></i> Cargar Asignación
                        </button>
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
                <div id="div-docentes" class="table-responsvie d-none">

                    <table id="tblAulasMaterias" class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;">#</th>
                                <th style="width:30%;" class="text-center">Aula</th>
                                <th width="20%" class="text-center">Materia</th>
                                <th class="text-center">Activo</th>
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

                    <form action="api/asignacionesMateria_guardar.php" method="POST" id="formAsignacion">

                        
                        <div class="mb-3">
                            <label for="idAula" class="form-label">Aulas</label>
                            <select class="form-select" name="idAula" id="idAula" required>
                                <option value="" selected disabled>Seleccione un aula...</option>
                                <?php if($lista_aulas): foreach($lista_aulas as $aula): ?>
                                <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['nombre_aula']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="idMateria" class="form-label">Materias</label>
                            <input class="form-control" name="idMateria" id="idMateria" list="materiasList" required placeholder="Seleccione una materia...">
                            <datalist id="materiasList">
                                <?php if($lista_materias): foreach($lista_materias as $materia): ?>
                                <option value="<?php echo $materia['nombre']; ?>">
                                <?php endforeach; endif; ?>
                            </datalist>
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

                       <input type="hidden" name="idAulaMateria" id="id_edit">


                        <div class="mb-3">
                            <label for="idAulaModal_editar" class="form-label">Aulas</label>
                            <select class="form-select" name="idAula" id="idAulaModal_editar" required>
                                <option value="" selected disabled>Seleccione un aula...</option>
                                <?php if($lista_aulas): foreach($lista_aulas as $aula): ?>
                                <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['nombre_aula']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="idMateriaModal_editar" class="form-label">Materias</label>
                            <input class="form-control" name="idMateria" id="idMateriaModal_editar" list="materiasList" required placeholder="Seleccione una materia...">
                            <datalist id="materiasList">
                                <?php if($lista_materias): foreach($lista_materias as $materia): ?>
                                <option value="<?php echo $materia['nombre']; ?>">
                                <?php endforeach; endif; ?>
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="EstadoModal_editar" class="form-label">Activo</label>
                            <select class="form-select" name="activo" id="EstadoModal_editar" required>
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

                       <input type="hidden" name="idAulaMateria" id="id_eliminar">

                       <div class="mb-3">
                            <label for="idAulaModal_eliminar" class="form-label">Aulas</label>
                            <select class="form-select" name="idAula" id="idAulaModal_eliminar" disabled>
                                <option value="" selected disabled>Seleccione un aula...</option>
                                <?php if($lista_aulas): foreach($lista_aulas as $aula): ?>
                                <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['nombre_aula']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="idMateriaModal_eliminar" class="form-label">Materias</label>
                            <input class="form-control" name="idMateria" id="idMateriaModal_eliminar" list="materiasList" disabled placeholder="Seleccione una materia...">
                            <datalist id="materiasList">
                                <?php if($lista_materias): foreach($lista_materias as $materia): ?>
                                <option value="<?php echo $materia['nombre']; ?>">
                                <?php endforeach; endif; ?>
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="EstadoModal_eliminar" class="form-label">Activo</label>
                            <select class="form-select" name="activo" id="EstadoModal_eliminar" disabled>
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

    <script src="<?php echo $ruta; ?>/dt/jquery-3.7.0.js"></script>
    <script src="<?php echo $ruta; ?>/dt/jquery.dataTables.min.js"></script>
    <script src="<?php echo $ruta; ?>/dt/dataTables.bootstrap5.min.js"></script>

    <script src="<?php echo $ruta; ?>/dt/botones/jszip.min.js"></script>
    <script src="<?php echo $ruta; ?>/dt/botones/pdfmake.min.js"></script>
    <script src="<?php echo $ruta; ?>/dt/botones/vfs_fonts.js"></script>

    <script src="<?php echo $ruta; ?>/dt/botones/dataTables.buttons.min.js"></script>
    <script src="<?php echo $ruta; ?>/dt/botones/buttons.bootstrap5.min.js"></script>
    <script src="<?php echo $ruta; ?>/dt/botones/buttons.html5.min.js"></script>
    <script src="<?php echo $ruta; ?>/dt/botones/buttons.print.min.js"></script>

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

    const idAulaSelect = document.getElementById('idAula');
    const btnCargar = document.getElementById('btnCargarAsignacion');
    const divTabla = document.getElementById('divTablaAsignaciones');
    let tablaInstance = null;

    // Habilitar/deshabilitar botón al cambiar aula en el filtro
    idAulaSelect.addEventListener('change', function() {
        if (this.value !== "Todos") {
            btnCargar.disabled = false;
            divTabla.style.display = "block";
        } else {
            btnCargar.disabled = true;
            divTabla.style.display = "none";
            // Destruir tabla si existe
            if (tablaInstance) {
                tablaInstance.destroy();
                tablaInstance = null;
                document.querySelector('#tblAulasMaterias tbody').innerHTML = '';
            }
        }
    });

    function cargarAsignaciones() {
        const idAula = idAulaSelect.value;

        if (idAula !== "Todos") {
            // Destruir tabla anterior si existe
            if (tablaInstance) {
                tablaInstance.destroy();
            }

            // Crear nueva instancia de DataTable
            tablaInstance = $('#tblAulasMaterias').DataTable({
                ajax: {
                    url: 'listar_asignacionesMateria.php',
                    data: function(d) {
                        d.idAula = idAula;
                    }
                },
                language: {
                    url: '<?php echo $ruta; ?>dt/es-ES.json'
                },
                columns: [
                    { data: 'idAulaMateria', className: 'text-center' },
                    { data: 'nombre_aula' },
                    { data: 'nombre_materia', className: 'text-center' },
                    { data: 'activo', 
                            render: function(data) {
                                return data === 'Sí' ? 
                                    '<span class="badge bg-success">Sí</span>' : 
                                    '<span class="badge bg-danger">No</span>';
                            },
                    className: 'text-center' },
                    {
                        data: null,
                        className: 'text-center',
                        render: function(data) {
                            return `<button class="btn btn-warning bi bi-pencil-square btn-editar"
                                    data-id="${data.idAulaMateria}"
                                    data-aula="${data.idAula}"
                                    data-materia="${data.idMateria}"
                                    data-nombre-materia="${data.nombre_materia}"
                                    data-estado="${data.activo}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar"></button> ` +
                                    `<button class="btn btn-danger bi bi-trash btn-eliminar"
                                    data-id="${data.idAulaMateria}"
                                    data-aula="${data.idAula}"
                                    data-materia="${data.idMateria}"
                                    data-nombre-materia="${data.nombre_materia}"
                                    data-estado="${data.activo}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminar"></button>`;
                        }
                    }
                ],
                responsive: "true",
                dom: 'Bfrtip',
                "pageLength": 5,
                buttons: [
                    {
                        extend:    'excelHtml5',
                        text:      '<i class="bi bi-file-earmark-excel"></i> ',
                        titleAttr: 'Exportar a Excel',
                        className: 'btn btn-success',
                        title:     'Lista de Asignaciones',
                        filename:  'Reporte_Asignaciones',
                        exportOptions: { columns: [ 0, 1, 2, 3] }
                    },
                    {
                        extend:'pdfHtml5',

                        text:'<i class="bi bi-file-earmark-pdf"></i>',

                        className:'btn btn-danger',

                        title:'Lista de Asignaciones del Aula ' + $('#idAula option:selected').text(),

                        filename:'Reporte_AsignacionesAulas',

                        exportOptions:{
                            columns:[0,1,2]
                        },

                        customize:function(doc){

                            encabezadoPDF(
                                doc,
                                'REPORTE DE ASIGNACIONES',
                                logoMEC,
                                logoSanta
                            );

                        }

                    },
                    {
                        extend:'print',

                        text:'<i class="bi bi-printer"></i>',

                        className:'btn btn-info',

                        title:'Lista de Asignaciones del Aula ' + $('#idAula option:selected').text(),

                        filename:'Reporte_AsignacionesAulas',

                        exportOptions:{
                            columns:[0,1,2,3]
                        },

                        customize:function(win){

                            encabezadoPrint(
                                win,
                                'REPORTE DE ASIGNACIONES',
                                logoMEC,
                                logoSanta
                            );

                        }
                        

                    }
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 4 }
                ]
            });
        }
    }

    // Cargar asignaciones al hacer click en el botón
    btnCargar.addEventListener('click', cargarAsignaciones);

    // Manejar envío del formulario
    const formAsignacion = document.getElementById('formAsignacion');
    const modalRegistro = document.getElementById('modalRegistro');
    
    formAsignacion.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('api/asignacionesMateria_guardar.php', {
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
            var idAulaMateria = button.getAttribute('data-id');
            var idAula = button.getAttribute('data-aula');
            var nombreMateria = button.getAttribute('data-nombre-materia');
            var Estado = button.getAttribute('data-estado');
            
            if (Estado === '1') Estado = 'Sí';
            else if (Estado === '0') Estado = 'No';

            document.getElementById('id_edit').value = idAulaMateria;
            document.getElementById('idAulaModal_editar').value = idAula;
            document.getElementById('idMateriaModal_editar').value = nombreMateria;
            document.getElementById('EstadoModal_editar').value = Estado;
        }
    });

    document.getElementById('formEditarAsignacion').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(document.getElementById('formEditarAsignacion'));
        // const formData = new FormData(this);
        console.log([...formData.entries()]);

        fetch('api/asignacionesMateria_actualizar.php', { 
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
            const idAulaMateria = button.getAttribute('data-id');
            const idAula = button.getAttribute('data-aula');
            const nombreMateria = button.getAttribute('data-nombre-materia');
            let Estado = button.getAttribute('data-estado');
            
            if (Estado === 'Sí') Estado = 'Sí';
            else if (Estado === 'No') Estado = 'No';

            document.getElementById('id_eliminar').value = idAulaMateria;
            document.getElementById('idAulaModal_eliminar').value = idAula;
            document.getElementById('idMateriaModal_eliminar').value = nombreMateria;
            document.getElementById('EstadoModal_eliminar').value = Estado;
        }
    });

    // 2. CONFIRMAR Y ENVIAR ELIMINACIÓN
    document.getElementById('formEliminarAsignacion').addEventListener('submit', function(e) {
        e.preventDefault();

        alertify.confirm("Eliminar Asignación", "¿Está seguro que desea eliminar esta asignación permanentemente?",
            function() {
                var formData = new FormData(document.getElementById('formEliminarAsignacion'));
                fetch('api/asignacionesMateria_eliminar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === true) {
                        alertify.success(data.msg);
                        setTimeout(function(){ window.location.reload(); }, 500);
                    } else if (data.code === 'HAS_DEPENDENCIES') {
                        alertify.error("No se puede eliminar la asignación porque tiene docentes asignados. Elimine primero los registros relacionados.");
                    } else {
                        alertify.error(data.msg);
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

