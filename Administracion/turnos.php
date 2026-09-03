<?php 
    // DEFINIMOS LA RUTA RELATIVA (Importante: antes de los includes)
    $ruta = "../";

    // 1. Incluimos el header
    include __DIR__ . '/includes/header.php'; 
    
    // 2. Incluimos la conexión
    require_once __DIR__ . '/../servicios/conexion.php';

    
    // 4. Traemos los turnos
    $sql_turno = "SELECT * FROM turno";
    $lista_turno = buscar_datos($sql_turno);

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
            <h1 class="h3 mb-0 text-gray-800">Turnos</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                <i class="bi bi-person-plus-fill"></i> Nuevo Turno
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tblTurnos" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Turno</th>                  
                                <th class="text-center">Descripción</th>
                                <th class="text-center">Acciones</th>                  
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lista_turno as $dato): ?>
                            <tr>
                                <td class="text-center">
                                    <?php echo $dato['idTurno']; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $dato['turno']; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $dato['descripcion']; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-sm btnEditar"
                                        data-id="<?php echo $dato['idTurno']; ?>"
                                        data-turno="<?php echo $dato['turno']; ?>"
                                        data-descripcion="<?php echo $dato['descripcion']; ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btnEliminar"
                                        data-id="<?php echo $dato['idTurno']; ?>"
                                        data-turno="<?php echo $dato['turno']; ?>"
                                        data-descripcion="<?php echo $dato['descripcion']; ?>"
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
    <!-- MODAL NUEVO TURNO -->
    <div class="modal fade" id="modalRegistro" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Nuevo Turno</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <form action="api/turnos_guardar.php" method="POST" id="formTurno">

                        <div class="mb-3">
                            <label for="turno" class="form-label">Nombre del Turno</label>
                            <input type="text" class="form-control text-uppercase" minlength="1" maxlength="1" name="turno" id="turno"
                                placeholder="Ej: N (Noche)" required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" minlength="10" maxlength="100" name="descripcion" id="descripcion"
                                placeholder="Ej: Turno de noche" required></textarea>
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
    <!-- FIN NUEVO TURNO -->


    <!-- MODAL MODIFICAR TURNO -->
    <div class="modal fade" id="modalEditar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-warning text-dark">
                    <h1 class="modal-title fs-5">Editar Turno</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="formEditarTurno">

                       <input type="hidden" name="id_turno" id="id_edit">

                       <div class="mb-3">
                            <label for="turno_edit" class="form-label">Nombre del Turno</label>
                            <input type="text" class="form-control text-uppercase" minlength="1" maxlength="1" name="turno" id="turno_edit"
                                placeholder="Ej: N (Noche)" required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion_edit" class="form-label">Descripción</label>
                            <textarea class="form-control" minlength="10" maxlength="100" name="descripcion" id="descripcion_edit"
                                placeholder="Ej: Turno de noche" required></textarea>
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
    <!-- FIN MODIFICAR TURNO -->


    <!-- MODAL ELIMINAR TURNO -->
    <div class="modal fade" id="modalEliminar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-danger text-white">
                    <h1 class="modal-title fs-5">Eliminar Turno</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-danger fw-bold">¿Estás seguro de que deseas eliminar este registro?</p>

                    <form id="formEliminarTurno">

                        <input type="hidden" name="id_turno" id="id_delete">

                       <div class="mb-3">
                            <label for="turno_delete" class="form-label">Nombre del Turno</label>
                            <input type="text" class="form-control text-uppercase" minlength="1" maxlength="1" name="turno" id="turno_delete"
                                placeholder="Ej: N (Noche)" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion_delete" class="form-label">Descripción</label>
                            <textarea class="form-control" minlength="10" maxlength="100" name="descripcion" id="descripcion_delete"
                                placeholder="Ej: Turno de noche" readonly></textarea>
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
    // 1. Configuración de DataTables
    $(document).ready(function() {
        $('#tblTurnos').DataTable({
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
                    title:     'Lista de Turnos',
                    filename:  'Reporte_Turnos',
                    exportOptions: { columns: [ 0, 1, 2] }
                },
                {
                    extend:'pdfHtml5',

                    text:'<i class="bi bi-file-earmark-pdf"></i>',

                    className:'btn btn-danger',

                    title:'Lista de Turnos',

                    filename:'Reporte_Turnos',

                    exportOptions:{
                        columns:[0,1,2]
                    },

                    customize:function(doc){

                        doc.content[1].table.widths = 
                                Array(doc.content[1].table.body[0].length + 1).join('*').split('');

                        encabezadoPDF(
                            doc,
                            'REPORTE DE TURNOS',
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
                    exportOptions: { columns: [0, 1, 2] },
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

                                /* Ajuste de anchos para 3 columnas */
                                .tabla-reporte th:nth-child(1),
                                .tabla-reporte td:nth-child(1) { width: 8%; text-align: center; }

                                .tabla-reporte th:nth-child(2),
                                .tabla-reporte td:nth-child(2) { width: 38%; text-align: center; }

                                .tabla-reporte th:nth-child(3),
                                .tabla-reporte td:nth-child(3) { width: 38%; text-align: center; }


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
                                                <div class="titulo-reporte">REPORTE DE TURNOS</div>
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
                { "orderable": false, "targets": 3 }
            ]
        });
    });

    // 2. LÓGICA DE GUARDADO CON AJAX
    document.getElementById('formTurno').addEventListener('submit', function(e) {
        e.preventDefault(); 

        var nombre = document.getElementById('turno').value.trim();
        var desc = document.getElementById('descripcion').value.trim();
        
        if (nombre === '' || desc === '') {
            alertify.error("Todos los campos son obligatorios.");
            return;
        }

        
        var formData = new FormData(document.getElementById('formTurno'));

        fetch('api/turnos_guardar.php', {
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
                    var inputnumero = document.getElementById('nombre');
                    inputnumero.focus();
                    inputnumero.select();
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
        document.getElementById('formTurno').reset();
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
            var nombre = button.getAttribute('data-turno');
            var desc = button.getAttribute('data-descripcion');

            document.getElementById('id_edit').value = id;
            document.getElementById('turno_edit').value = nombre;
            document.getElementById('descripcion_edit').value = desc;
        }
    });


    // --- PARTE B: GUARDAR LOS CAMBIOS ---
    document.getElementById('formEditarTurno').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(document.getElementById('formEditarTurno'));

        fetch('api/turnos_actualizar.php', { 
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
            var nombre = button.getAttribute('data-turno');
            var desc = button.getAttribute('data-descripcion');

            document.getElementById('id_delete').value = id;
            document.getElementById('turno_delete').value = nombre;
            document.getElementById('descripcion_delete').value = desc;
        }
    });

    // 2. CONFIRMAR Y ENVIAR ELIMINACIÓN
    document.getElementById('formEliminarTurno').addEventListener('submit', function(e) {
        e.preventDefault();

        alertify.confirm("Eliminar Turno", "¿Está seguro que desea eliminar este registro permanentemente?",
            function() {
                var formData = new FormData(document.getElementById('formEliminarTurno'));
                fetch('api/turnos_eliminar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === true) {
                        alertify.success(data.msg);
                        setTimeout(function(){ window.location.reload(); }, 500);
                    } else if (data.code === 'HAS_DEPENDENCIES') {
                        alertify.error("No se puede eliminar el turno porque tiene cursos asociados. Elimine primero los cursos relacionados.");
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


