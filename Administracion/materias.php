<?php 
    // DEFINIMOS LA RUTA RELATIVA (Importante: antes de los includes)
    $ruta = "../";

    // 1. Incluimos el header
    include __DIR__ . '/includes/header.php'; 
    
    // 2. Incluimos la conexión
    require_once __DIR__ . '/../servicios/conexion.php';

    // 3. Consulta SQL — JOIN con tabla rol para obtener el nombre del rol
    $sql = "SELECT m.idMateria, m.plan, m.nombre, m.codigo, m.horas_sem, m.idEnfasis, m.idCurso, e.nombre AS nombre_enfasis, c.nombre AS nombre_curso, m.activo
            FROM materia m 
            INNER JOIN enfasis e ON m.idEnfasis = e.idEnfasis
            INNER JOIN curso c ON m.idCurso = c.idCurso";
    $lista_materias = buscar_datos($sql);

    // 4. Traemos los enfasis para los selects
    $sql_enfasis = "SELECT idEnfasis, nombre FROM enfasis";
    $lista_enfasis = buscar_datos($sql_enfasis);

    $sql_curso = "SELECT idCurso, nombre FROM curso";
    $lista_curso = buscar_datos($sql_curso);
?>


    <link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" href="<?php echo $ruta; ?>bt/bootstrap.min.css"/>
    <link rel="stylesheet" href="<?php echo $ruta; ?>bt-icons/bootstrap-icons.min.css"/>
    


    <div class="main-content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Materias</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                <i class="bi bi-person-plus-fill"></i> Nueva Materia
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tblMaterias" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Plan</th>
                                <th>Disciplinas</th>
                                <th>Codigo</th>
                                <th>Horas Semanales</th>
                                <th>Énfasis</th>
                                <th>Curso</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                        if($lista_materias){
                            foreach ($lista_materias as $dato) {
                                $estadoColor = ($dato['activo'] == 'Sí') ? 'success' : 'danger';
                        ?>
                            <tr>
                                <td class="text-center">
                                    <?php echo $dato['idMateria']; ?>
                                </td>
                                <td>
                                    <?php echo $dato['plan']; ?>
                                </td>
                                <td>
                                    <?php echo $dato['nombre']; ?>
                                </td>
                                <td>
                                    <?php echo $dato['codigo']; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $dato['horas_sem']; ?>
                                </td>
                                <td>
                                    <?php echo $dato['nombre_enfasis']; ?>
                                </td>
                                <td>
                                    <?php echo $dato['nombre_curso']; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo $estadoColor; ?>">
                                        <?php echo $dato['activo']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm btnEditar"
                                        data-id="<?php echo $dato['idMateria']; ?>"
                                        data-plan="<?php echo $dato['plan']; ?>"
                                        data-nombre="<?php echo $dato['nombre']; ?>"
                                        data-codigo="<?php echo $dato['codigo']; ?>"
                                        data-horas-sem="<?php echo $dato['horas_sem']; ?>"
                                        data-id-enfasis="<?php echo $dato['idEnfasis']; ?>"
                                        data-id-curso="<?php echo $dato['idCurso']; ?>"
                                        data-activo="<?php echo $dato['activo']; ?>" data-bs-toggle="modal"
                                        data-bs-target="#modalEditar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btnEliminar"
                                        data-id="<?php echo $dato['idMateria']; ?>"
                                        data-plan="<?php echo $dato['plan']; ?>"
                                        data-nombre="<?php echo $dato['nombre']; ?>"
                                        data-codigo="<?php echo $dato['codigo']; ?>"
                                        data-horas-sem="<?php echo $dato['horas_sem']; ?>"
                                        data-id-enfasis="<?php echo $dato['idEnfasis']; ?>"
                                        data-id-curso="<?php echo $dato['idCurso']; ?>"
                                        data-activo="<?php echo $dato['activo']; ?>" data-bs-toggle="modal"
                                        data-bs-target="#modalEliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php 
                            }
                        } 
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <!-- MODAL NUEVA MATERIA -->
    <div class="modal fade" id="modalRegistro" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Nueva Materia</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <form action="api/materias_guardar.php" method="POST" id="formMateria">

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Disciplina</label>
                            <input type="text" class="form-control" minlength="5" maxlength="40" name="nombre" id="nombre"
                                placeholder="Ej: Ciencias Sociales" required>
                        </div>

                        <div class="mb-3">
                            <label for="plan" class="form-label">Plan de Estudios</label>
                            <select class="form-select" name="plan" id="plan" readonly>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <option value="Plan Común">Plan Común</option>
                                <option value="Plan Específico">Plan Específico</option>
                                <option value="Sin Plan">Sin Plan</option>
                                <option value="Institucional">Institucional</option>
                                <option value="Optativo">Optativo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código de la Materia</label>
                            <input type="text" class="form-control text-uppercase" minlength="5" maxlength="10" name="codigo" id="codigo"
                                placeholder="Ej: Cs-101" required>
                        </div>

                        <div class="mb-3">
                            <label for="horas_sem" class="form-label">Horas Semanales</label>
                            <input type="number" class="form-control" name="horas_sem" min="1" max="6" id="horas_sem" required>
                        </div>

                        <div class="mb-3">
                            <label for="idEnfasis" class="form-label">Enfasis</label>
                            <select class="form-select" name="idEnfasis" id="idEnfasis" required>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <?php if($lista_enfasis): foreach($lista_enfasis as $enfasis): ?>
                                <option value="<?php echo $enfasis['idEnfasis']; ?>"><?php echo $enfasis['nombre']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="idCurso" class="form-label">Curso</label>
                            <select class="form-select" name="idCurso" id="idCurso" required>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <?php if($lista_curso): foreach($lista_curso as $curso): ?>
                                <option value="<?php echo $curso['idCurso']; ?>"><?php echo $curso['nombre']; ?></option>
                                <?php endforeach; endif; ?>
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
    <!-- FIN NUEVA MATERIA -->


    <!-- MODAL MODIFICAR MATERIA -->
    <div class="modal fade" id="modalEditar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-warning text-dark">
                    <h1 class="modal-title fs-5">Editar Materia</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="formEditarMateria">

                        <input type="hidden" name="id_materia" id="id_edit">

                        <div class="mb-3">
                            <label for="nombre_edit" class="form-label">Nombre de la Disciplina</label>
                            <input type="text" class="form-control" minlength="5" maxlength="40" name="nombre" id="nombre_edit" required>
                        </div>

                        
                        <div class="mb-3">
                            <label for="plan_edit" class="form-label">Plan de Estudios</label>
                            <select class="form-select" name="plan" id="plan_edit" required>
                                <option value="Plan Común">Plan Común</option>
                                <option value="Plan Específico">Plan Específico</option>
                                <option value="Sin Plan">Sin Plan</option>
                                <option value="Institucional">Institucional</option>
                                <option value="Optativo">Optativo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="codigo_edit" class="form-label">Código de la Materia</label>
                            <input type="text" class="form-control text-uppercase" minlength="5" maxlength="10" name="codigo" id="codigo_edit" required>
                        </div>

                        <div class="mb-3">
                            <label for="horas_sem_edit" class="form-label">Horas Semanales</label>
                            <input type="number" class="form-control" name="horas_sem" min="1" max="6" id="horas_sem_edit" required>
                        </div>

                        <div class="mb-3">
                            <label for="idEnfasis_edit" class="form-label">Enfasis</label>
                            <select class="form-select" name="idEnfasis" id="idEnfasis_edit" required>
                                <?php if($lista_enfasis): foreach($lista_enfasis as $enfasis): ?>
                                <option value="<?php echo $enfasis['idEnfasis']; ?>"><?php echo $enfasis['nombre']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="idCurso_edit" class="form-label">Curso</label>
                            <select class="form-select" name="idCurso" id="idCurso_edit" required>
                                <?php if($lista_curso): foreach($lista_curso as $curso): ?>
                                <option value="<?php echo $curso['idCurso']; ?>"><?php echo $curso['nombre']; ?></option>
                                <?php endforeach; endif; ?>
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
    <!-- FIN MODIFICAR MATERIA -->


    <!-- MODAL ELIMINAR MATERIA -->
    <div class="modal fade" id="modalEliminar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-danger text-white">
                    <h1 class="modal-title fs-5">Eliminar Materia</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-danger fw-bold">¿Estás seguro de que deseas eliminar este registro?</p>

                    <form id="formEliminarMatera">

                        <input type="hidden" name="id_materia" id="id_delete">

                        <div class="mb-3">
                            <label for="nombre_delete" class="form-label">Nombre de la Disciplina</label>
                            <input type="text" class="form-control" name="nombre" id="nombre_delete" readonly>
                        </div>

                        
                        <div class="mb-3">
                            <label for="plan_delete" class="form-label">Plan de Estudios</label>
                            <select class="form-select" name="plan" id="plan_delete" readonly disabled>
                                <?php if($lista_materias): foreach($lista_materias as $dato): ?>
                                <option value="<?php echo $dato['plan'] ?>"><?php echo $dato['plan'] ?></option> 
                                <?php endforeach; endif; ?>                        
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="codigo_delete" class="form-label">Código de la Materia</label>
                            <input type="text" class="form-control" name="codigo" id="codigo_delete" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="horas_sem_delete" class="form-label">Horas Semanales</label>
                            <input type="number" class="form-control" name="horas_sem" id="horas_sem_delete" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="idEnfasis_delete" class="form-label">Enfasis</label>
                            <select class="form-select" name="idEnfasis" id="idEnfasis_delete" readonly disabled>
                                <?php if($lista_enfasis): foreach($lista_enfasis as $enfasis): ?>
                                <option value="<?php echo $enfasis['idEnfasis']; ?>"><?php echo $enfasis['nombre']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="idCurso_delete" class="form-label">Curso</label>
                            <select class="form-select" name="idCurso" id="idCurso_delete" readonly disabled>
                                <?php if($lista_curso): foreach($lista_curso as $curso): ?>
                                <option value="<?php echo $curso['idCurso']; ?>"><?php echo $curso['nombre']; ?></option>
                                <?php endforeach; endif; ?>
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
        $('#tblMaterias').DataTable({
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
                    title:     'Lista de Materias',
                    filename:  'Reporte_Materias',
                    exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7 ] }
                },
                {
                    extend:'pdfHtml5',

                    text:'<i class="bi bi-file-earmark-pdf"></i>',

                    className:'btn btn-danger',

                    title:'Lista de Materias',

                    filename:'Reporte_Materias',

                    exportOptions:{
                        columns:[0,1,2,3,4,5,6,7]
                    },

                    customize:function(doc){
                                    
                                    
                        doc.content[1].table.widths = 
                            Array(doc.content[1].table.body[0].length + 1).join('*').split('');

                        encabezadoPDF(
                            doc,
                            'REPORTE DE MATERIAS',
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

                                /* Ajuste de anchos para 8 columnas */
                                .tabla-reporte th:nth-child(1),
                                .tabla-reporte td:nth-child(1) { width: 8%; text-align: center; }

                                .tabla-reporte th:nth-child(2),
                                .tabla-reporte td:nth-child(2) { width: 38%; text-align: center; }

                                .tabla-reporte th:nth-child(3),
                                .tabla-reporte td:nth-child(3) { width: 38%; text-align: center; }

                                .tabla-reporte th:nth-child(4),
                                .tabla-reporte td:nth-child(4) { width: 16%; text-align: center; }

                                .tabla-reporte th:nth-child(5),
                                .tabla-reporte td:nth-child(5) { width: 8%; text-align: center; }

                                .tabla-reporte th:nth-child(6),
                                .tabla-reporte td:nth-child(6) { width: 38%; text-align: center; }

                                .tabla-reporte th:nth-child(7),
                                .tabla-reporte td:nth-child(7) { width: 38%; text-align: center; }

                                .tabla-reporte th:nth-child(8),
                                .tabla-reporte td:nth-child(8) { width: 16%; text-align: center; }

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
                                                <div class="titulo-reporte">REPORTE DE MATERIAS</div>
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
                { "orderable": false, "targets": 8 }
            ]
        });
    });

    // 2. LÓGICA DE GUARDADO CON AJAX
    document.getElementById('formMateria').addEventListener('submit', function(e) {
        e.preventDefault(); 

        var nombre = document.getElementById('nombre').value.trim();
        var plan = document.getElementById('plan').value;
        var codigo = document.getElementById('codigo').value.trim();
        var horasSem = document.getElementById('horas_sem').value;
        var idEnfasis = document.getElementById('idEnfasis').value;
        var idCurso = document.getElementById('idCurso').value;

        if (nombre === '' || codigo === '' || plan === '' || idEnfasis === '' || idCurso === '' || horasSem === '') {
            alertify.error("Todos los campos son obligatorios.");
            return;
        }

        
        var formData = new FormData(document.getElementById('formMateria'));

        fetch('api/materias_guardar.php', {
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
                if(data.msg.toLowerCase().includes("codigo")) {
                    var inputCodigo = document.getElementById('codigo');
                    inputCodigo.focus();
                    inputCodigo.select();
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
        document.getElementById('formMateria').reset();
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
            var plan = button.getAttribute('data-plan');
            var nombre = button.getAttribute('data-nombre');
            var codigo = button.getAttribute('data-codigo');
            var horasSem = button.getAttribute('data-horas-sem');
            var idEnfasis = button.getAttribute('data-id-enfasis');
            var idCurso = button.getAttribute('data-id-curso');

            document.getElementById('id_edit').value = id;
            document.getElementById('plan_edit').value = plan;
            document.getElementById('nombre_edit').value = nombre;
            document.getElementById('codigo_edit').value = codigo;
            document.getElementById('horas_sem_edit').value = horasSem;
            document.getElementById('idEnfasis_edit').value = idEnfasis;
            document.getElementById('idCurso_edit').value = idCurso;
        }
    });


    // --- PARTE B: GUARDAR LOS CAMBIOS ---
    document.getElementById('formEditarMateria').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(document.getElementById('formEditarMateria'));

        fetch('api/materias_actualizar.php', { 
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
            var plan = button.getAttribute('data-plan');
            var nombre = button.getAttribute('data-nombre');
            var codigo = button.getAttribute('data-codigo');
            var horasSem = button.getAttribute('data-horas-sem');
            var idEnfasis = button.getAttribute('data-id-enfasis');
            var idCurso = button.getAttribute('data-id-curso');

            document.getElementById('id_delete').value = id;
            document.getElementById('plan_delete').value = plan;
            document.getElementById('nombre_delete').value = nombre;
            document.getElementById('codigo_delete').value = codigo;
            document.getElementById('horas_sem_delete').value = horasSem;
            document.getElementById('idEnfasis_delete').value = idEnfasis;
            document.getElementById('idCurso_delete').value = idCurso;
        }
    });

    // 2. CONFIRMAR Y ENVIAR ELIMINACIÓN
    document.getElementById('formEliminarMatera').addEventListener('submit', function(e) {
        e.preventDefault();

        alertify.confirm("Eliminar Materia", "¿Está seguro que desea eliminar este registro permanentemente?",
            function() {
                var formData = new FormData(document.getElementById('formEliminarMatera'));
                fetch('api/materias_eliminar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === true) {
                        alertify.success(data.msg);
                        setTimeout(function(){ window.location.reload(); }, 500);
                    } else if (data.code === 'HAS_DEPENDENCIES') {
                        alertify.confirm('Registro vigente', data.msg + ' ¿Desea desactivarla en lugar de eliminarla?',
                            function() {
                                var fallbackForm = new FormData();
                                fallbackForm.append('id_materia', document.getElementById('id_delete').value);
                                fallbackForm.append('action', 'deactivate');

                                fetch('api/materias_eliminar.php', {
                                    method: 'POST',
                                    body: fallbackForm
                                })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.status === true) {
                                        alertify.success(result.msg);
                                        setTimeout(function(){ window.location.reload(); }, 500);
                                    } else {
                                        alertify.error(result.msg);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alertify.error('Error en el servidor.');
                                });
                            },
                            function() {
                                alertify.error('Cancelado');
                            }
                        ).set('labels', {ok:'Sí, Desactivar', cancel:'Cancelar'});
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


