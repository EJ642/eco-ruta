<?php 
    $ruta = "../";

    // 1. Incluimos el header
    include __DIR__ . '/includes/header.php'; 
    
    // 2. Incluimos la conexión
    require_once __DIR__ . '/../servicios/conexion.php';

    // 3. Obtenemos los datos de la tabla "docente_aula_materia"
    $sql = "SELECT DISTINCT a.idAula, CONCAT(c.numero, '° ', e.nombre, ' - ', t.turno) as curso
            FROM docente_aula_materia dam
            JOIN aula_materia am ON dam.idAulaMateria = am.idAulaMateria
            JOIN aula a ON am.idAula = a.idAula
            JOIN curso c ON a.idCurso = c.idCurso
            JOIN enfasis e ON a.idEnfasis = e.idEnfasis
            JOIN turno t ON c.idTurno = t.idTurno
            JOIN anio_lectivo an ON a.idAnio = an.idAnio
            WHERE dam.activo = 1 
            AND a.activo = 'Sí'
            AND an.activo = 'Sí'
            ORDER BY c.numero, e.nombre";
    $lista_asignaciones = buscar_datos($sql);

    $docentes = "SELECT idDocente, CONCAT(nombres, ' ', apellidos, ' - ', cedula) as nombre_docente, estado FROM docente WHERE estado = 'Activo'";
    $lista_docentes = buscar_datos($docentes);

    $aulas = "SELECT idAula, CONCAT(c.numero, '° ', e.nombre, ' - ', t.turno) as nombre_aula
              FROM aula a
              JOIN curso c ON a.idCurso = c.idCurso
              JOIN enfasis e ON a.idEnfasis = e.idEnfasis
              JOIN turno t ON c.idTurno = t.idTurno
              JOIN anio_lectivo an ON a.idAnio = an.idAnio
              WHERE a.activo = 'Sí' AND an.activo = 'Sí'";
    $lista_aulas = buscar_datos($aulas);

?>

<!-- DataTables -->
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


    <div class="main-content">

        <h2 class="h3 mb-0 text-gray-800"> Asignaciones de Docentes</h2>
        <br>


        <!-- FILTROS -->
        
        <div class="card p-3 mb-3 shadow-sm">
            <h2 class="h3 mb-0 text-gray-800"> Filtros</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="idDocente" class="form-label">Docente</label>
                        <input class="form-control" id="idDocente" list="DocentesList" required placeholder="Seleccione un tutor...">
                        <input type="hidden" id="idDocenteHidden" name="idDocenteHidden">
                            <datalist id="DocentesList">
                                <?php if($lista_docentes): foreach($lista_docentes as $docente): ?>
                                <option value="<?php echo $docente['nombre_docente']; ?>" data-id="<?php echo $docente['idDocente']; ?>"></option>
                                <?php endforeach; endif; ?>
                            </datalist>
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

                    <table id="tblDocentes" class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;">#</th>
                                <th style="width:30%;" class="text-center">Aula</th>
                                <th width="20%" class="text-center">Materia</th>
                                <th class="text-center">Estado</th>
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

                    <form action="api/asignaciones_guardar.php" method="POST" id="formAsignacion">

                        <div class="mb-3">
                            <label for="idDocenteModal" class="form-label">Docente</label>
                            <select class="form-select" name="idDocente" id="idDocenteModal" required>
                                <option value="" selected>Seleccione un docente...</option>
                                <?php if($lista_docentes): foreach($lista_docentes as $docente): ?>
                                <option value="<?php echo $docente['idDocente']; ?>"><?php echo $docente['nombre_docente']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

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
                            <label for="idAulaMateria" class="form-label">Materias del Aula Respectiva</label>
                            <select class="form-select" name="idAulaMateria" id="idAulaMateria" required disabled>
                                <option value="" selected disabled>Seleccione un aula primero...</option>
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

                       <input type="hidden" name="idAsignacion" id="id_edit">

                       <div class="mb-3">
                            <label for="idDocenteModal_editar" class="form-label">Docente</label>
                            <input type="hidden" name="idDocente" id="idDocenteModal_editar_hidden">
                            <select class="form-select" id="idDocenteModal_editar" disabled>
                                <option value="" selected disabled>Seleccione un docente...</option>
                                <?php if($lista_docentes): foreach($lista_docentes as $docente): ?>
                                <option value="<?php echo $docente['idDocente']; ?>"><?php echo $docente['nombre_docente']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="idAulaModal_editar" class="form-label">Aulas</label>
                            <select class="form-select" name="idAula" id="idAulaModal_editar" required>
                                <option value="" selected>Seleccione un aula...</option>
                                <?php if($lista_aulas): foreach($lista_aulas as $aula): ?>
                                <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['nombre_aula']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="idAulaMateriaModal_editar" class="form-label">Materias del Aula Respectiva</label>
                            <select class="form-select" name="idAulaMateria" id="idAulaMateriaModal_editar" required disabled>
                                <option value="" selected disabled>Seleccione un aula primero...</option>
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

                       <input type="hidden" name="idAsignacion" id="id_eliminar">
                       <input type="hidden" name="idAulaMateria" id="idAulaMateriaModal_eliminar_hidden">

                       <div class="mb-3">
                            <label for="idDocenteModal_eliminar" class="form-label">Docente</label>
                            <select class="form-select" name="idDocente" id="idDocenteModal_eliminar" disabled>
                                <option value="" selected disabled>Seleccione un docente...</option>
                                <?php if($lista_docentes): foreach($lista_docentes as $docente): ?>
                                <option value="<?php echo $docente['idDocente']; ?>"><?php echo $docente['nombre_docente']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="idAulaModal_eliminar" class="form-label">Aulas</label>
                            <select class="form-select" name="idAula" id="idAulaModal_eliminar" disabled>
                                <option value="" selected>Seleccione un aula...</option>
                                <?php if($lista_aulas): foreach($lista_aulas as $aula): ?>
                                <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['nombre_aula']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="idAulaMateriaModal_eliminar" class="form-label">Materias del Aula Respectiva</label>
                            <select class="form-select" name="idAulaMateria" id="idAulaMateriaModal_eliminar" disabled>
                                <option value="" selected disabled>Seleccione un aula primero...</option>
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

    const idDocenteSelect = document.getElementById('idDocente');
    const idDocenteHidden = document.getElementById('idDocenteHidden');
    const btnCargar = document.getElementById('btnCargarAsignacion');
    const divTabla = document.getElementById('divTablaAsignaciones');
    const idAulaSelect = document.getElementById('idAula');
    const idAulaMateriaSelect = document.getElementById('idAulaMateria');
    let tablaInstance = null;

    const docenteOptions = Array.from(document.querySelectorAll('#DocentesList option'));

    // Habilitar/deshabilitar botón al cambiar docente
    idDocenteSelect.addEventListener('input', function() {
        const value = this.value.trim();
        const matchedOption = docenteOptions.find(option => option.value === value);

        if (matchedOption) {
            idDocenteHidden.value = matchedOption.dataset.id || '';
            btnCargar.disabled = false;
            divTabla.style.display = "block";
        } else {
            idDocenteHidden.value = '';
            btnCargar.disabled = true;
        }
    });

    // Validar docente al perder foco
    idDocenteSelect.addEventListener('blur', function() {
        const value = this.value.trim();
        if (value && !docenteOptions.some(option => option.value === value)) {
            alertify.error('Debe seleccionar un docente válido de la lista.');
            this.value = '';
            idDocenteHidden.value = '';
            btnCargar.disabled = true;
            divTabla.style.display = 'none';
        }
    });

    idAulaSelect.addEventListener('change', function() {
        const idAula = this.value;
        idAulaMateriaSelect.innerHTML = '<option value="" selected disabled>Cargando materias...</option>';
        idAulaMateriaSelect.disabled = true;

        if (!idAula) {
            idAulaMateriaSelect.innerHTML = '<option value="" selected disabled>Seleccione un aula primero...</option>';
            return;
        }

        fetch(`api/materias_por_aula.php?idAula=${encodeURIComponent(idAula)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                    let options = '<option value="" selected disabled>Seleccione una materia...</option>';
                    data.data.forEach(materia => {
                        options += `<option value="${materia.idAulaMateria}">${materia.materia}</option>`;
                    });
                    idAulaMateriaSelect.innerHTML = options;
                    idAulaMateriaSelect.disabled = false;
                } else {
                    idAulaMateriaSelect.innerHTML = '<option value="" selected disabled>No hay materias para esta aula</option>';
                    idAulaMateriaSelect.disabled = true;
                }
            })
            .catch(() => {
                idAulaMateriaSelect.innerHTML = '<option value="" selected disabled>Error al cargar materias</option>';
                idAulaMateriaSelect.disabled = true;
            });
    });

    function cargarAsignaciones() {
        const idDocente = idDocenteHidden.value;

        if (!idDocente) {
            alertify.error('Seleccione un docente válido antes de cargar las asignaciones.');
            return;
        }

        // Destruir tabla anterior si existe
        if (tablaInstance) {
            tablaInstance.destroy();
        }

            // Crear nueva instancia de DataTable
            tablaInstance = $('#tblDocentes').DataTable({
                ajax: {
                    url: 'listar_asignaciones.php',
                    data: function(d) {
                        d.idDocente = idDocente;
                    }
                },
                language: {
                    url: '<?php echo $ruta; ?>dt/es-ES.json'
                },
                columns: [
                    { data: 'idAsignacion', className: 'text-center' },
                    { data: 'nombre_aula' },
                    { data: 'nombre_materia', className: 'text-center' },
                    { data: 'activo', 
                            render: function(data) {
                                return data === '1' ? 
                                    '<span class="badge bg-success">Activo</span>' : 
                                    '<span class="badge bg-danger">Inactivo</span>';
                            },
                    className: 'text-center' },
                    {
                        data: null,
                        className: 'text-center',
                        render: function(data) {
                            return `<button class="btn btn-warning bi bi-pencil-square btn-editar"
                                    data-id="${data.idAsignacion}"
                                    data-docente="${data.idDocente}"
                                    data-aula="${data.idAula}"
                                    data-materia="${data.idAulaMateria}"
                                    data-estado="${data.activo}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar"></button> ` +
                                    `<button class="btn btn-danger bi bi-trash btn-eliminar"
                                    data-id="${data.idAsignacion}"
                                    data-docente="${data.idDocente}"
                                    data-aula="${data.idAula}"
                                    data-materia="${data.idAulaMateria}"
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

                        title:'Lista de Asignaciones del docente ' + idDocenteSelect.value,

                        filename:'Reporte_AsignacionesDocente_' + idDocenteSelect.value,

                        exportOptions:{
                            columns:[0,1,2,3]
                        },

                        customize:function(doc){

                            doc.content[1].table.widths = 
                                Array(doc.content[1].table.body[0].length + 1).join('*').split('');
     

                            encabezadoPDF(
                                doc,
                                'REPORTE DE ASIGNACIONES',
                                logoMEC,
                                logoSanta
                            );

                        }

                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i>',
                        titleAttr: 'Imprimir',
                        className: 'btn btn-info',
                        title: '',
                        exportOptions: { columns: [0, 1, 2, 3] },
                        customize: function (win) {

                            const fecha = new Date().toLocaleDateString();
                            const hora = new Date().toLocaleTimeString();

                            // Tomar la tabla que DataTables generó en la ventana de impresión
                            const tablaHTML = $(win.document.body).find('table').prop('outerHTML');

                            // Reemplazar TODO el contenido del body por nuestro diseño
                            win.document.body.innerHTML = `
                                <style>
                                    @page {
                                        size: A4 portrait;
                                        margin: 15mm;
                                    }

                                    body {
                                        font-family: Arial, sans-serif;
                                        color: #000;
                                        font-size: 11px;
                                        margin: 0;
                                        padding: 0;
                                    }

                                    .reporte-container {
                                        width: 100%;
                                        max-width: 180mm;
                                        margin: 0 auto;
                                    }

                                    .encabezado {
                                        width: 100%;
                                        margin-bottom: 10px;
                                    }

                                    .encabezado table {
                                        width: 100%;
                                        border-collapse: collapse;
                                        border: none !important;
                                    }

                                    .encabezado td {
                                        border: none !important;
                                        vertical-align: middle;
                                    }

                                    .logo-izq {
                                        width: 18%;
                                        text-align: left;
                                    }

                                    .logo-centro {
                                        width: 64%;
                                        text-align: center;
                                        line-height: 1.2;
                                    }

                                    .logo-der {
                                        width: 18%;
                                        text-align: right;
                                    }

                                    .logo-izq img,
                                    .logo-der img {
                                        height: 60px;
                                        max-width: 100%;
                                    }

                                    .titulo1 { font-size: 11pt; font-weight: bold; }
                                    .titulo2 { font-size: 12pt; font-weight: bold; }
                                    .titulo3 { font-size: 14pt; font-weight: bold; }
                                    .subtitulo { font-size: 9pt; }
                                    .titulo-reporte {
                                        margin-top: 8px;
                                        font-size: 10pt;
                                        font-weight: bold;
                                    }

                                    .meta {
                                        text-align: right;
                                        margin: 6px 0 12px 0;
                                        font-size: 9pt;
                                    }

                                    .linea {
                                        border-top: 1px solid #555;
                                        margin-top: 8px;
                                        margin-bottom: 6px;
                                    }

                                    /* TABLA DEL REPORTE */
                                    .tabla-reporte {
                                        width: 100%;
                                        border-collapse: collapse !important;
                                        table-layout: fixed;
                                        margin: 0 auto;
                                        font-size: 9pt;
                                    }

                                    .tabla-reporte th,
                                    .tabla-reporte td {
                                        border: 1px solid #000 !important;
                                        padding: 6px 5px;
                                        text-align: center;
                                        vertical-align: middle;
                                        word-wrap: break-word;
                                        white-space: normal;
                                    }

                                    .tabla-reporte th {
                                        background: #f2f2f2 !important;
                                        font-weight: bold;
                                    }

                                    /* Ajuste de anchos para 4 columnas */
                                    .tabla-reporte th:nth-child(1),
                                    .tabla-reporte td:nth-child(1) { width: 8%; }

                                    .tabla-reporte th:nth-child(2),
                                    .tabla-reporte td:nth-child(2) { width: 38%; }

                                    .tabla-reporte th:nth-child(3),
                                    .tabla-reporte td:nth-child(3) { width: 38%; }

                                    .tabla-reporte th:nth-child(4),
                                    .tabla-reporte td:nth-child(4) { width: 16%; }

                                    .pie {
                                        margin-top: 10px;
                                        font-size: 8pt;
                                        display: flex;
                                        justify-content: space-between;
                                    }
                                </style>

                                <div class="reporte-container">

                                    <div class="encabezado">
                                        <table>
                                            <tr>
                                                <td class="logo-izq">
                                                    <img src="${logoMEC}">
                                                </td>

                                                <td class="logo-centro">
                                                    <div class="titulo1">DIRECCIÓN GENERAL DE EDUCACIÓN MEDIA</div>
                                                    <div class="titulo2">INSTITUCIÓN EDUCATIVA DIOCESANA</div>
                                                    <div class="titulo3">SANTA TERESITA</div>
                                                    <div class="subtitulo">Concepción - Paraguay</div>
                                                    <div class="titulo-reporte">REPORTE DE ASIGNACIONES</div>
                                                </td>

                                                <td class="logo-der">
                                                    <img src="${logoSanta}">
                                                </td>
                                            </tr>
                                        </table>

                                        <div class="linea"></div>
                                        <div class="meta">Fecha de emisión: ${fecha} ${hora}</div>
                                    </div>

                                    <div id="contenedor-tabla"></div>

                                </div>
                            `;

                            // insertar la tabla exportada dentro del contenedor
                            $(win.document.body).find('#contenedor-tabla').html(tablaHTML);

                            // poner clase a la tabla para aplicarle los estilos
                            $(win.document.body).find('#contenedor-tabla table').addClass('tabla-reporte');
                        }
                    }
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 4 }
                ]
            });
    }
    

    // Cargar asignaciones al hacer click en el botón
    btnCargar.addEventListener('click', cargarAsignaciones);

    // Manejar envío del formulario
    const formAsignacion = document.getElementById('formAsignacion');
    const modalRegistro = document.getElementById('modalRegistro');
    
    formAsignacion.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('api/asignaciones_guardar.php', {
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


    const idAulaSelect_editar = document.getElementById('idAulaModal_editar');
    const idAulaMateriaSelect_editar = document.getElementById('idAulaMateriaModal_editar');
    const idAulaMateriaSelect_eliminar = document.getElementById('idAulaMateriaModal_eliminar');

    // Listener para cargar materias en el modal de edición
    idAulaSelect_editar.addEventListener('change', function() {
        const idAula = this.value;
        idAulaMateriaSelect_editar.innerHTML = '<option value="" selected disabled>Cargando materias...</option>';
        idAulaMateriaSelect_editar.disabled = true;

        if (!idAula) {
            idAulaMateriaSelect_editar.innerHTML = '<option value="" selected disabled>Seleccione un aula primero...</option>';
            return;
        }

        fetch(`api/materias_por_aula.php?idAula=${encodeURIComponent(idAula)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                    let options = '<option value="" selected disabled>Seleccione una materia...</option>';
                    data.data.forEach(materia => {
                        options += `<option value="${materia.idAulaMateria}">${materia.materia}</option>`;
                    });
                    idAulaMateriaSelect_editar.innerHTML = options;
                    idAulaMateriaSelect_editar.disabled = false;
                } else {
                    idAulaMateriaSelect_editar.innerHTML = '<option value="" selected disabled>No hay materias para esta aula</option>';
                    idAulaMateriaSelect_editar.disabled = true;
                }
            })
            .catch(() => {
                idAulaMateriaSelect_editar.innerHTML = '<option value="" selected disabled>Error al cargar materias</option>';
                idAulaMateriaSelect_editar.disabled = true;
            });
    });

    // Cargar asignaciones al abrir el modal de edición
    var modalEditar = document.getElementById('modalEditar');

    modalEditar.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (button) {
            const idAsignacion = button.getAttribute('data-id');
            const idDocente = button.getAttribute('data-docente');
            const idAula = button.getAttribute('data-aula');
            const idAulaMateria = button.getAttribute('data-materia');
            
            document.getElementById('id_edit').value = idAsignacion;
            document.getElementById('idDocenteModal_editar_hidden').value = idDocente;
            document.getElementById('idDocenteModal_editar').value = idDocente;
            document.getElementById('idAulaModal_editar').value = idAula;
            
            // Cargar materias y luego seleccionar la correcta
            fetch(`api/materias_por_aula.php?idAula=${encodeURIComponent(idAula)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                        let options = '<option value="" selected disabled>Seleccione una materia...</option>';
                        data.data.forEach(materia => {
                            options += `<option value="${materia.idAulaMateria}">${materia.materia}</option>`;
                        });
                        idAulaMateriaSelect_editar.innerHTML = options;
                        idAulaMateriaSelect_editar.disabled = false;
                        
                        // Seleccionar la materia correcta después de cargar las opciones
                        setTimeout(() => {
                            idAulaMateriaSelect_editar.value = idAulaMateria;
                        }, 100);
                    } else {
                        idAulaMateriaSelect_editar.innerHTML = '<option value="" selected disabled>No hay materias para esta aula</option>';
                        idAulaMateriaSelect_editar.disabled = true;
                    }
                })
                .catch(() => {
                    idAulaMateriaSelect_editar.innerHTML = '<option value="" selected disabled>Error al cargar materias</option>';
                    idAulaMateriaSelect_editar.disabled = true;
                });
        }
    });

    document.getElementById('formEditarAsignacion').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(document.getElementById('formEditarAsignacion'));
        // const formData = new FormData(this);
        console.log([...formData.entries()]);

        fetch('api/asignaciones_actualizar.php', { 
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
            const idAsignacion = button.getAttribute('data-id');
            const idDocente = button.getAttribute('data-docente');
            const idAula = button.getAttribute('data-aula');
            const idAulaMateria = button.getAttribute('data-materia');
            
            document.getElementById('id_eliminar').value = idAsignacion;
            document.getElementById('idDocenteModal_eliminar').value = idDocente;
            document.getElementById('idAulaModal_eliminar').value = idAula;
            document.getElementById('idAulaMateriaModal_eliminar_hidden').value = idAulaMateria;
            // Cargar materias y luego seleccionar la correcta
            fetch(`api/materias_por_aula.php?idAula=${encodeURIComponent(idAula)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                        let options = '<option value="" selected disabled>Seleccione una materia...</option>';
                        data.data.forEach(materia => {
                            options += `<option value="${materia.idAulaMateria}">${materia.materia}</option>`;
                        });
                        idAulaMateriaSelect_eliminar.innerHTML = options;
                        
                        // Seleccionar la materia correcta después de cargar las opciones
                        setTimeout(() => {
                            idAulaMateriaSelect_eliminar.value = idAulaMateria;
                        }, 100);
                    } else {
                        idAulaMateriaSelect_eliminar.innerHTML = '<option value="" selected disabled>No hay materias para esta aula</option>';
                    }
                })
                .catch(() => {
                    idAulaMateriaSelect_eliminar.innerHTML = '<option value="" selected disabled>Error al cargar materias</option>';
                });
        }
    });

    // 2. CONFIRMAR Y ENVIAR ELIMINACIÓN
    document.getElementById('formEliminarAsignacion').addEventListener('submit', function(e) {
        e.preventDefault();

        alertify.confirm("Eliminar Asignación", "¿Está seguro que desea eliminar esta asignación permanentemente?",
            function() {
                var formData = new FormData(document.getElementById('formEliminarAsignacion'));
                fetch('api/asignaciones_eliminar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === true) {
                        alertify.success(data.msg);
                        setTimeout(function(){ window.location.reload(); }, 500);
                    } else if (data.code === 'HAS_DEPENDENCIES') {
                        alertify.error("No se puede eliminar la asignación porque tiene evaluaciones, asistencias asociadas. Elimine primero los registros relacionados.");
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

