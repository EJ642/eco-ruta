<?php
    $ruta = "../";
    include __DIR__ . '/includes/header.php';

    // 2. Incluimos la conexión
    require_once __DIR__ . '/../servicios/conexion.php';

    
    // 3. Traemos los enfasis para los selects
    $sql_enfasis = "SELECT idEnfasis, nombre FROM enfasis
                    WHERE nombre not like 'Ninguno' ORDER BY nombre";
    $lista_enfasis = buscar_datos($sql_enfasis);

    $sql_curso = "SELECT idCurso, nombre FROM curso";
    $lista_curso = buscar_datos($sql_curso);

    $sql_alumno = "SELECT idAlumno, CONCAT(nombres , ' ' , apellidos) as nombre FROM alumno a
                   WHERE a.idAlumno NOT IN (SELECT idAlumno FROM matricula)";
    $lista_alumno = buscar_datos($sql_alumno);

    // Lista de TODOS los alumnos para el modal de editar
    $sql_alumno_todos = "SELECT idAlumno, CONCAT(nombres , ' ' , apellidos) as nombre FROM alumno ORDER BY nombre";
    $lista_alumno_todos = buscar_datos($sql_alumno_todos);

    $sql_aula = "SELECT idAula, CONCAT(c.nombre , '-' , e.nombre) as nombreAula FROM aula a
                JOIN curso c ON a.idCurso = c.idCurso
                JOIN enfasis e ON a.idEnfasis = e.idEnfasis";
    $lista_aula = buscar_datos($sql_aula);

?>

<!-- DataTables -->
<link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css">
<script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>
<script src="<?php echo $ruta; ?>dt/jquery.dataTables.min.js"></script>
<script src="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.js"></script>

<style>
    .main-content {
        margin-left: 17rem;
        width: calc(100% - 17rem);
        padding: 20px;
        display: block;
        transition: margin-left 0.3s ease, width 0.3s ease;
    }

    .card {
        border-radius: 12px;
    }

    h2 {
        font-weight: bold;
    }

    .btn {
        border-radius: 8px;
    }

    #totalAlumnos {
        font-weight: bold;
    }

    #tablaMatriculas thead th {
        background:#f8f9fa; font-size:15px; font-weight:600; color:#495057; vertical-align:middle;
    }
    #tablaMatriuculas td { vertical-align:middle; font-size:13px; }
    
</style>

<div class="main-content">

    <h2 class="h3 mb-0 text-gray-800"> Matriculaciones</h2>

    <!-- FILTROS -->
    
    <div class="card p-3 mb-3 shadow-sm">
        <h2 class="h3 mb-0 text-gray-800"> Filtros</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="idCurso" class="form-label">Curso</label>
                    <select class="form-select" name="idCurso" id="idCurso">
                        <option value="Todos" selected>Todos los cursos...</option>
                        <?php if($lista_curso): foreach($lista_curso as $curso): ?>
                        <option value="<?php echo $curso['idCurso']; ?>"><?php echo $curso['nombre']; ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3">
                    <label for="idEnfasis" class="form-label">Enfasis</label>
                    <select class="form-select" name="idEnfasis" id="idEnfasis" disabled>
                        <option value="Todos" selected>Todos los énfasis...</option>
                        <?php if($lista_enfasis): foreach($lista_enfasis as $enfasis): ?>
                        <option value="<?php echo $enfasis['idEnfasis']; ?>"><?php echo $enfasis['nombre']; ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>

            <div class=col-md-4>
                <div class="mb-3">
                    <label>Año Lectivo</label>
                    <input type="text" class="form-control" readonly value="<?php echo date('Y'); ?>">

                </div>

            </div>

            
        </div>
    </div>

    <!-- ACCIONES -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="row">
            <span id="totalAlumnos">Total:</span>
            <span id="totalAlumnosNoMatriculados">Total No Matriculados:</span>
        </div>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistro">
            <i class="bi bi-person-plus-fill"></i> Nuevo Alumno
        </button>
    </div>

    <!-- TABLA -->
    <div class="card p-3 shadow-sm mb-4">
        <div class="card-body">
            <div id="div-alumnos" class="table-responsvie d-none">

                <table id="tablaMatriculas" class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">#</th>
                            <th width="30%">Nombres y Apellidos</th>
                            <th style="width:30%;" class="text-center">CI</th>
                            <th class="text-center">Aula</th>
                            <th class="text-center">Fecha Matriculado</th>
                            <th style="width:30%;" class="text-center">Observacion</th>
                            <th style="width:30%;" class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        
        </div>
    </div>

</div>

    <!-- NUEVO ALUMNO -->
<div class="modal fade" id="modalRegistro" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Nuevo Alumno</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <form action="api/matriculas_guardar.php" method="POST" id="formMatriculacion">

                        <div class="mb-3">
                            <label for="idAlumno" class="form-label">Alumno</label>
                            <select class="form-select" name="idAlumno" id="idAlumno" required>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <?php if($lista_alumno): foreach($lista_alumno as $alumno): ?>
                                <option value="<?php echo $alumno['idAlumno']; ?>"><?php echo $alumno['nombre']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="idAula" class="form-label">Aula</label>
                            <select class="form-select" name="idAula" id="idAula" required>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <?php if($lista_aula): foreach($lista_aula as $aula): ?>
                                <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['nombreAula']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha de Matriculación</label>
                            <input type="text" class="form-control" name="fecha" id="fecha" value="<?php echo date('d/m/Y'); ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="estado" required>
                                <option value="Vigente">Vigente</option>
                                <option value="Retirado">Retirado</option>
                                <option value="Sin Plan">Trasladado</option>
                                <option value="Promovido">Promovido</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="Observacion" class="form-label">Observacion</label>
                            <input type="text-area" class="form-control" maxlenght="235" name="Observacion" id="Observacion">
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
    <!-- FIN NUEVO ALUMNO -->


    <!-- EDITAR MATRICULA -->
<div class="modal fade" id="modalEditar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-warning text-dark">
                    <h1 class="modal-title fs-5">Editar Matrícula</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <form id="formEditarMatricula">

                        <input type="hidden" name="id_matricula" id="id_edit">

                        <div class="mb-3">
                            <label for="idAlumno_edit_display" class="form-label">Alumno</label>
                            <input type="hidden" name="idAlumno" id="idAlumno_edit">
                            <input type="text" class="form-control" id="idAlumno_edit_display" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="idAula_edit" class="form-label">Aula</label>
                            <select class="form-select" name="idAula" id="idAula_edit">
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <?php if($lista_aula): foreach($lista_aula as $aula): ?>
                                <option value="<?php echo $aula['idAula']; ?>"><?php echo $aula['nombreAula']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_edit" class="form-label">Fecha de Matriculación</label>
                            <input type="text" class="form-control" name="fecha" id="fecha_edit" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="estado_edit" class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="estado_edit">
                                <option value="Vigente">Vigente</option>
                                <option value="Retirado">Retirado</option>
                                <option value="Sin Plan">Trasladado</option>
                                <option value="Promovido">Promovido</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="Observacion_edit" class="form-label">Observacion</label>
                            <input type="text-area" class="form-control" maxlenght="235" name="Observacion" id="Observacion_edit">
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle-fill"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i>Actualizar</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
    <!-- FIN EDITAR MATRICULA -->



<?php include __DIR__ . '/includes/footer.php'; ?>


<script>
    let tabla = $('#tablaMatriculas').DataTable({
    ajax: {
        url: 'listar_matriculas.php',
        data: function(d) {
            d.idCurso = $('#idCurso').val();
            d.idEnfasis = $('#idEnfasis').val();
        }
    },
    language: {
        url: '<?php echo $ruta; ?>dt/es-ES.json'
    },
    columns: [
        { data: 'idMatricula', className: 'text-center' },
        { 
            data: 'nombres',
            render: function(data, type, row) {
                return row.nombres + ' ' + row.apellidos;
            }
        },
        { data: 'cedula' },
        { data: 'nombreAula' },
        { data: 'fecha_matricula', className: 'text-center' },
        { data: 'observacion' },
        { data: 'estado', className: 'text-center' },
        {
            data: null,
            render: function(data) {
                return `<button class="btn btn-warning bi bi-pencil-square btn-editar"
                        data-id="${data.idMatricula}"
                        data-alumno="${data.idAlumno}"
                        data-aula="${data.idAula}"
                        data-fecha="${data.fecha_matricula}"
                        data-estado="${data.estado}"
                        data-observacion="${data.observacion}"
                        data-nombres="${data.nombres}"
                        data-apellidos="${data.apellidos}"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditar"></button>`;
            }
        }
    ]
    });

    $('#idCurso, #idEnfasis').change(function() {

        let curso = $('#idCurso').val();

        // Mostrar tabla solo si hay curso
        if(curso){
            $('#div-alumnos').removeClass('d-none');
            tabla.ajax.reload();
        }

    });
    

    

    tabla.on('xhr', function() {
        let json = tabla.ajax.json();
        if(json.dataN.length == undefined){
            $('#totalAlumnos').text("Total Alumnos: " + json.data.length);
            $('#totalAlumnosNoMatriculados').text("Total Alumnos No Matriculados: 0");
        }else{
            $('#totalAlumnos').text("Total Alumnos: " + json.data.length);
            $('#totalAlumnosNoMatriculados').text("Total Alumnos No Matriculados: " + json.dataN.length);
        }
    });

    const CursoSelect = document.getElementById('idCurso');
    const EnfasisSelect = document.getElementById('idEnfasis');
    CursoSelect.addEventListener('change', function() {
        if (this.value && this.value !== 'Todos') {
            EnfasisSelect.disabled = false;
        } else {
            EnfasisSelect.value = 'Todos';
            EnfasisSelect.disabled = false;
        }
    });

    // 2. LÓGICA DE GUARDADO CON AJAX
    document.getElementById('formMatriculacion').addEventListener('submit', function(e) {
        e.preventDefault(); 

        var idAlumno = document.getElementById('idAlumno').value.trim();
        var idAula = document.getElementById('idAula').value;
        var fecha = document.getElementById('fecha').value;
        var estado = document.getElementById('estado').value;
        var observacion = document.getElementById('Observacion').value;

        if (idAlumno === '' || idAula === '' || fecha === '' || estado === '') {
            alertify.error("Todos los campos son obligatorios.");
            return;
        }

        
        var formData = new FormData(document.getElementById('formMatriculacion'));

        fetch('api/matriculas_guardar.php', {
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
                if(data.msg.toLowerCase().includes("el alumno ya está matriculado")) {
                    var inputIdAlumno = document.getElementById('idAlumno');
                    inputIdAlumno.focus();
                    inputIdAlumno.select();
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
        document.getElementById('formMatriculacion').reset();
    });

    myModalEl.addEventListener('shown.bs.modal', function (event) {
        document.getElementById('idAlumno').focus();
    });

    // 4. LÓGICA PARA LLENAR MODAL DE EDITAR
    $(document).on('click', '.btn-editar', function() {
        $('#id_edit').val($(this).data('id'));
        $('#idAlumno_edit').val($(this).data('alumno'));
        $('#idAlumno_edit_display').val($(this).data('nombres') + ' ' + $(this).data('apellidos'));
        $('#idAula_edit').val($(this).data('aula'));
        $('#fecha_edit').val($(this).data('fecha'));
        $('#estado_edit').val($(this).data('estado'));
        $('#Observacion_edit').val($(this).data('observacion'));
    });

    // 5. LÓGICA DE EDITAR CON AJAX
    document.getElementById('formEditarMatricula').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('id_matricula', $('#id_edit').val());

        fetch('api/matriculas_actualizar.php', {
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
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alertify.error("Error en la comunicación con el servidor.");
        });
    });

    
    
</script>




                                                                                                                                                                                                                                                                                                    