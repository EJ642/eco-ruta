<?php 
    // DEFINIMOS LA RUTA RELATIVA (Importante: antes de los includes)
    $ruta = "../";

    // 1. Incluimos el header
    include __DIR__ . '/includes/header.php'; 
    
    // 2. Incluimos la conexión
    require_once __DIR__ . '/../servicios/conexion.php';

    // 3. Consulta SQL — JOIN con tabla rol para obtener el nombre del rol
    $sql = "SELECT * 
            FROM enfasis";
    $lista_enfasis = buscar_datos($sql);


?>

<style>
    .main-content {
    display: block;
}
</style>


    <link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="<?php echo $ruta; ?>bt/bootstrap.min.css"/>
    <link rel="stylesheet" href="<?php echo $ruta; ?>bt-icons/bootstrap-icons.min.css"/>
    


    <div class="main-content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Especialidades</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                <i class="bi bi-book-open-fill"></i> Nueva Especialidad
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tblEspecialidades" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Descripción</th>
                                <th class="text-center">Activo</th>                  
                                <th class="text-center">Acciones</th>                  
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lista_enfasis as $dato):
                                $estadoColor = ($dato['activo'] == 'Sí') ? 'success' : 'danger'; ?>
                                
                            <tr>
                                <td class="text-center">
                                    <?php echo $dato['idEnfasis']; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $dato['nombre']; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $dato['descripcion']; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo $estadoColor; ?>">
                                        <?php echo $dato['activo']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-sm btnEditar"
                                        data-id="<?php echo $dato['idEnfasis']; ?>"
                                        data-nombre="<?php echo $dato['nombre']; ?>"
                                        data-descripcion="<?php echo $dato['descripcion']; ?>"
                                        data-estado="<?php echo $dato['activo']; ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btnEliminar"
                                        data-id="<?php echo $dato['idEnfasis']; ?>"
                                        data-nombre="<?php echo $dato['nombre']; ?>"
                                        data-descripcion="<?php echo $dato['descripcion']; ?>"
                                        data-estado="<?php echo $dato['activo']; ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <!-- MODAL NUEVA ESPECIALIDAD -->
    <div class="modal fade" id="modalRegistro" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Nueva Especialidad</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <form action="api/especialidades_guardar.php" method="POST" id="formEspecialidad">

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Especialidad</label>
                            <input type="text" class="form-control" minlength="5" maxlength="40" name="nombre" id="nombre"
                                placeholder="Ej: Medicina General" required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" minlength="10" maxlength="100" name="descripcion" id="descripcion"
                                placeholder="Ej: Especialidad en Medicina General" required></textarea>
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
    <!-- FIN NUEVO ESPECIALIDAD -->


    <!-- MODAL MODIFICAR ESPECIALIDAD -->
    <div class="modal fade" id="modalEditar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-warning text-dark">
                    <h1 class="modal-title fs-5">Editar Especialidad</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="formEditarEspecialidad">

                       <input type="hidden" name="id_enfasis" id="id_edit">

                       <div class="mb-3">
                            <label for="nombre_edit" class="form-label">Nombre de la Especialidad</label>
                            <input type="text" class="form-control" minlength="5" maxlength="40" name="nombre" id="nombre_edit"
                                placeholder="Ej: Medicina General" required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion_edit" class="form-label">Descripción</label>
                            <textarea class="form-control" minlength="10" maxlength="100" name="descripcion" id="descripcion_edit"
                                placeholder="Ej: Especialidad en Medicina General" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="estado_edit" class="form-label">Activo</label>
                            <select class="form-select" name="estado" id="estado_edit">
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
    <!-- FIN MODIFICAR ESPECIALIDAD -->


    <!-- MODAL ELIMINAR ESPECIALIDAD -->
    <div class="modal fade" id="modalEliminar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-danger text-white">
                    <h1 class="modal-title fs-5">Eliminar Especialidad</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-danger fw-bold">¿Estás seguro de que deseas eliminar este registro?</p>

                    <form id="formEliminarEspecialidad">

                        <input type="hidden" name="id_enfasis" id="id_delete">

                        <div class="mb-3">
                            <label for="nombre_delete" class="form-label">Nombre de la Especialidad</label>
                            <input type="text" class="form-control" minlength="5" maxlength="40" name="nombre" id="nombre_delete"
                                placeholder="Ej: Medicina General" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion_delete" class="form-label">Descripción</label>
                            <textarea class="form-control" minlength="10" maxlength="100" name="descripcion" id="descripcion_delete"
                                placeholder="Ej: Especialidad en Medicina General" readonly></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="estado_delete" class="form-label">Activo</label>
                            <select class="form-select" name="estado" id="estado_delete" disabled>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                                                
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger"><i class="bi bi-trash-fill"></i>Eliminar</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>

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

    <?php
        $logo = base64_encode(file_get_contents('../img/Logo-Santa.jpeg'));
        $logoMEC = base64_encode(file_get_contents('../img/Logo-MEC.png'));
    ?>

    <script>
        const logoSanta = "data:image/jpeg;base64,<?php echo $logo; ?>";
        const logoMEC = "data:image/jpeg;base64,<?php echo $logoMEC; ?>";
    </script>

    <script>
    // 1. Configuración de DataTables
    $(document).ready(function() {
        $('#tblEspecialidades').DataTable({
            "language": {
                "url": "<?php echo $ruta; ?>dt/es-ES.json"
            },
            responsive: "true",
            dom: 'Bfrtip',
            "pageLength": 5,
            buttons: [
                {
                    extend:    'excelHtml5',
                    text:      '<i class="bi bi-file-earmark-excel"></i> ',
                    titleAttr: 'Exportar a Excel',
                    className: 'btn btn-success',
                    title:     'Lista de Especialidades',
                    filename:  'Reporte_Especialidades',
                    exportOptions: { columns: [ 0, 1, 2, 3 ] }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-file-earmark-pdf"></i> ',
                    titleAttr: 'Exportar a PDF',
                    className: 'btn btn-danger',
                    title: 'Lista de Especialidades',
                    filename: 'Reporte_Especialidades',
                    exportOptions: { columns: [ 0, 1, 2, 3 ] },
                    orientation: 'portrait',
                    pageSize: 'A4',
                    customize: function (doc) {

                        const now = new Date();
                        const fecha = now.toLocaleDateString();
                        const hora = now.toLocaleTimeString();

                        doc.pageMargins = [40, 110, 40, 60];

                        doc.header = {
                            margin: [20, 10, 20, 0],
                            columns: [

                                {
                                    image: logoMEC,
                                    width: 80,
                                    alignment: 'left'
                                },
                                {
                                    width: '*',
                                    alignment:'center',
                                    stack:[]
                                },
                                {
                                    width: '*',
                                    alignment: 'center',
                                    margin: [0, 5, 0, 0],
                                    stack: [
                                        {
                                            text: 'INSTITUCIÓN EDUCATIVA DIOCSESANA SANTA TERESITA',
                                            bold: true,
                                            fontSize: 14
                                        },
                                        {
                                            text: 'Educación Media',
                                            fontSize: 9
                                        },
                                        {
                                            text: 'Concepción - Paraguay',
                                            fontSize: 10
                                        },
                                        {
                                            text: 'Reporte de Especialidades',
                                            bold: true,
                                            margin: [0, 5, 0, 0],
                                            fontSize: 11
                                        }
                                    ]
                                },

                                {
                                    width: '*',
                                    text: ''
                                },
                                {
                                    image: logoSanta,
                                    width: 80,
                                    alignment: 'right'
                                },

                            ]
                        };

                        doc.footer = function(currentPage, pageCount) {

                            return {

                                margin: [30, 5],

                                columns: [

                                    {
                                        text:
                                        'Generado: ' +
                                        fecha +
                                        ' ' +
                                        hora,
                                        alignment: 'left',
                                        fontSize: 8
                                    },

                                    {
                                        text:
                                        'Página ' +
                                        currentPage +
                                        ' de ' +
                                        pageCount,
                                        alignment: 'right',
                                        fontSize: 8
                                    }

                                ]
                            };
                        };

                        doc.styles.tableHeader = {
                            bold: true,
                            fontSize: 10,
                            alignment: 'center'
                        };

                        doc.defaultStyle = {
                            fontSize: 9,
                            alignment: 'center'
                        };

                        var body = doc.content[1].table.body;
                        var columnCount = body[0].length;

                        var widths = [];

                        var widthMap = {
                            0: 'auto',
                            1: '*',
                            2: '*',
                            3: 'auto'
                        };

                        for (var i = 0; i < columnCount; i++) {
                            widths.push(widthMap[i] || '*');
                        }

                        doc.content[1].table.widths = widths;
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
                                .tabla-reporte td:nth-child(1) { width: 8%; text-align: center; }

                                .tabla-reporte th:nth-child(2),
                                .tabla-reporte td:nth-child(2) { width: 38%; text-align: center; }

                                .tabla-reporte th:nth-child(3),
                                .tabla-reporte td:nth-child(3) { width: 38%; text-align: center; }

                                .tabla-reporte th:nth-child(4),
                                .tabla-reporte td:nth-child(4) { width: 16%; text-align: center; }

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
                                                <div class="titulo-reporte">REPORTE DE ESPECIALIDADES</div>
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
    });

    // 2. LÓGICA DE GUARDADO CON AJAX
    document.getElementById('formEspecialidad').addEventListener('submit', function(e) {
        e.preventDefault(); 

        var nombre = document.getElementById('nombre').value.trim();
        var descripcion = document.getElementById('descripcion').value.trim();
        
        if (nombre === '' || descripcion === '') {
            alertify.error("Todos los campos son obligatorios.");
            return;
        }

        
        var formData = new FormData(document.getElementById('formEspecialidad'));

        fetch('api/especialidades_guardar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === true) {
                alertify.success(data.msg);
                setTimeout(function(){ window.location.reload(); }, 500);
            } else {
                alertify.error(data.msg);
                if(data.msg.toLowerCase().includes("nombre")) {
                    var inputnombre = document.getElementById('nombre');
                    inputnombre.focus();
                    inputnombre.select();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alertify.error("Error en la comunicación con el servidor.");
        });
    });

    // 3. EVENTOS DEL MODAL NUEVO (limpiar y enfocar al abrir)
    var myModalEl = document.getElementById('modalRegistro');
    
    myModalEl.addEventListener('show.bs.modal', function (event) {
        document.getElementById('formEspecialidad').reset();
    });

    myModalEl.addEventListener('shown.bs.modal', function (event) {
        document.getElementById('nombre').focus();
    });


    // --- PARTE A: LLENAR EL MODAL DE EDICIÓN ---
    var modalEditar = document.getElementById('modalEditar');

    modalEditar.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (button) {
            var id = button.getAttribute('data-id');
            var nombre = button.getAttribute('data-nombre');
            var descripcion = button.getAttribute('data-descripcion');
            var estado = button.getAttribute('data-estado');

            document.getElementById('id_edit').value = id;
            document.getElementById('descripcion_edit').value = descripcion;
            document.getElementById('nombre_edit').value = nombre;
            document.getElementById('estado_edit').value = estado;
        }
    });


    // --- PARTE B: GUARDAR LOS CAMBIOS ---
    document.getElementById('formEditarEspecialidad').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(document.getElementById('formEditarEspecialidad'));

        fetch('api/especialidades_actualizar.php', { 
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

    // --- ELIMINAR MATERIA ---
    // 1. LLENAR EL MODAL DE ELIMINACIÓN
    var modalEliminar = document.getElementById('modalEliminar');

    modalEliminar.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        if (button) {
            var id = button.getAttribute('data-id');
            var nombre = button.getAttribute('data-nombre');
            var descripcion = button.getAttribute('data-descripcion');
            var estado = button.getAttribute('data-estado');

            document.getElementById('id_delete').value = id;
            document.getElementById('descripcion_delete').value = descripcion;
            document.getElementById('nombre_delete').value = nombre;
            document.getElementById('estado_delete').value = estado;
        }
    });

    // 2. CONFIRMAR Y ENVIAR ELIMINACIÓN
    document.getElementById('formEliminarEspecialidad').addEventListener('submit', function(e) {
        e.preventDefault();

        alertify.confirm("Eliminar Especialidad", "¿Está seguro que desea eliminar este registro permanentemente?",
            function() {
                var formData = new FormData(document.getElementById('formEliminarEspecialidad'));
                fetch('api/especialidades_eliminar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === true) {
                        alertify.success(data.msg);
                        setTimeout(function(){ window.location.reload(); }, 500);
                    } else if (data.code === 'HAS_DEPENDENCIES') {
                        alertify.error("No se puede eliminar la especialidad porque tiene materias asociadas. Elimine primero las materias relacionadas.");
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


