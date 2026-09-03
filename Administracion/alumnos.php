<?php
    $ruta = "../";
    include __DIR__ . '/includes/header.php';
    require_once __DIR__ . '/../servicios/conexion.php';

    // Lista completa de alumnos con aula/matrícula activa
    $sql_alumnos = "SELECT a.idAlumno, a.cedula, a.nombres, a.apellidos,
                           a.fecha_nac, a.sexo, a.direccion,
                           a.estado,
                           TIMESTAMPDIFF(YEAR, a.fecha_nac, CURDATE()) AS edad,
                           m.idMatricula, m.estado AS estado_matricula,
                           c.nombre AS nombre_curso,
                           e.nombre AS nombre_enfasis,
                           an.anio AS anio_lectivo,
                           t.turno AS turno,
                           COUNT(at2.idTutor) AS total_tutores
                    FROM alumno a
                    LEFT JOIN matricula m
                        ON m.idAlumno = a.idAlumno AND m.estado = 'Vigente'
                    LEFT JOIN aula au ON au.idAula = m.idAula
                    LEFT JOIN curso c ON c.idCurso = au.idCurso
                    LEFT JOIN enfasis e ON e.idEnfasis = au.idEnfasis
                    LEFT JOIN anio_lectivo an ON an.idAnio = au.idAnio
                    LEFT JOIN turno t ON t.idTurno = c.idTurno
                    LEFT JOIN alumno_tutor at2 ON at2.idAlumno = a.idAlumno
                    GROUP BY a.idAlumno
                    ORDER BY a.apellidos, a.nombres";
    $lista_alumnos = buscar_datos($sql_alumnos) ?: [];
?>

<link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css"/>

<div class="main-content">

    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-mortarboard-fill me-2 text-primary"></i>Mantenimiento de Alumnos
        </h1>
        <button type="button" class="btn btn-primary btn-sm"
                data-bs-toggle="modal" data-bs-target="#modalNuevo">
            <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Alumno
        </button>
    </div>

    <!-- TARJETAS RESUMEN -->
    <?php
        $total = count($lista_alumnos);
        $activos = count(array_filter($lista_alumnos, fn($r) => $r['estado'] === 'Activo'));
        $inactivos = count(array_filter($lista_alumnos, fn($r) => $r['estado'] === 'Inactivo'));
        $egresados = count(array_filter($lista_alumnos, fn($r) => $r['estado'] === 'Egresado'));
        $retirados = count(array_filter($lista_alumnos, fn($r) => $r['estado'] === 'Retirado'));
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h4 fw-bold text-primary mb-0"><?php echo $total; ?></div>
                <small class="text-muted">Total</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h4 fw-bold text-success mb-0"><?php echo $activos; ?></div>
                <small class="text-muted">Activos</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h4 fw-bold text-warning mb-0"><?php echo $egresados; ?></div>
                <small class="text-muted">Egresados</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h4 fw-bold text-danger mb-0"><?php echo $retirados + $inactivos; ?></div>
                <small class="text-muted">Retirados/Inactivos</small>
            </div>
        </div>
    </div>

    <!-- TABLA PRINCIPAL -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle"
                       id="tblAlumnos" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Cédula</th>
                            <th>Apellidos y Nombres</th>
                            <th>Edad</th>
                            <th>Sexo</th>
                            <th>Aula / Curso</th>
                            <th>Tutores</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lista_alumnos as $a):
                        $badge_estado = match($a['estado']) {
                            'Activo'   => 'success',
                            'Inactivo' => 'secondary',
                            'Egresado' => 'warning',
                            'Retirado' => 'danger',
                            default    => 'secondary'
                        };
                        $aula_txt = $a['nombre_curso']
                            ? htmlspecialchars($a['anio_lectivo'].' · '.$a['nombre_curso'].' · '.$a['nombre_enfasis'])
                            : '<span class="text-muted small">Sin matrícula</span>';
                    ?>
                        <tr>
                            <td><?php echo $a['idAlumno']; ?></td>
                            <td><?php echo $a['cedula'] ? htmlspecialchars($a['cedula']) : '<span class="text-muted">—</span>'; ?></td>
                            <td><?php echo htmlspecialchars($a['apellidos'].', '.$a['nombres']); ?></td>
                            <td class="text-center"><?php echo $a['edad']; ?></td>
                            <td class="text-center">
                                <?php echo $a['sexo'] === 'M'
                                    ? '<i class="bi bi-gender-male text-primary" title="Masculino"></i>'
                                    : '<i class="bi bi-gender-female text-danger" title="Femenino"></i>'; ?>
                            </td>
                            <td><?php echo $aula_txt; ?></td>
                            <td class="text-center">
                                <?php if ($a['total_tutores'] > 0): ?>
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-people-fill me-1"></i><?php echo $a['total_tutores']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sin tutor</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $badge_estado; ?>">
                                    <?php echo $a['estado']; ?>
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-warning btn-sm btnEditar"
                                    data-bs-toggle="modal" data-bs-target="#modalEditar"
                                    title="Editar"
                                    data-id="<?php echo $a['idAlumno']; ?>"
                                    data-cedula="<?php echo htmlspecialchars($a['cedula'] ?? '', ENT_QUOTES); ?>"
                                    data-nombres="<?php echo htmlspecialchars($a['nombres'], ENT_QUOTES); ?>"
                                    data-apellidos="<?php echo htmlspecialchars($a['apellidos'], ENT_QUOTES); ?>"
                                    data-fecha_nac="<?php echo $a['fecha_nac']; ?>"
                                    data-sexo="<?php echo $a['sexo']; ?>"
                                    data-direccion="<?php echo htmlspecialchars($a['direccion'] ?? '', ENT_QUOTES); ?>"
                                    data-estado="<?php echo $a['estado']; ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btnEliminar"
                                    title="Eliminar"
                                    data-id="<?php echo $a['idAlumno']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($a['nombres'].' '.$a['apellidos'], ENT_QUOTES); ?>"
                                    data-cedula="<?php echo htmlspecialchars($a['cedula'] ?? '*', ENT_QUOTES); ?>">
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


<!-- MODAL: NUEVO ALUMNO -->
<div class="modal fade" id="modalNuevo" data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Nuevo Alumno</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formNuevo" novalidate>

          <!-- Datos personales -->
          <h6 class="fw-semibold border-bottom pb-1 mb-3">
            <i class="bi bi-person-badge me-1 text-primary"></i>Datos Personales
          </h6>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Nombres <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombres" id="n_nombres"
                     placeholder="Ej: María José" maxlength="100" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Apellidos <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="apellidos" id="n_apellidos"
                     placeholder="Ej: González Pérez" maxlength="100" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Cédula de Identidad</label>
              <input type="text" class="form-control" name="cedula" id="n_cedula"
                     placeholder="Opcional si es menor" maxlength="20">
              <div class="form-text">Puede dejarse vacío si el alumno aún no tiene CI.</div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="fecha_nac" id="n_fecha_nac" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Sexo <span class="text-danger">*</span></label>
              <select class="form-select" name="sexo" id="n_sexo" required>
                <option value="" disabled selected>Seleccione...</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Estado</label>
              <select class="form-select" name="estado" id="n_estado">
                <option value="Activo" selected>Activo</option>
                <option value="Inactivo">Inactivo</option>
                <option value="Egresado">Egresado</option>
                <option value="Retirado">Retirado</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" class="form-control" name="direccion" id="n_direccion"
                   placeholder="Dirección domiciliaria (opcional)" maxlength="250">
          </div>

          <div class="modal-footer px-0 pb-0 mt-2">
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


<!--MODAL: EDITAR ALUMNO -->
<div class="modal fade" id="modalEditar" data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Alumno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditar" novalidate>
          <input type="hidden" name="id_alumno" id="e_id">

          <h6 class="fw-semibold border-bottom pb-1 mb-3">
            <i class="bi bi-person-badge me-1 text-warning"></i>Datos Personales
          </h6>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Nombres <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombres" id="e_nombres" maxlength="100" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Apellidos <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="apellidos" id="e_apellidos" maxlength="100" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Cédula de Identidad</label>
              <input type="text" class="form-control" name="cedula" id="e_cedula" maxlength="20">
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="fecha_nac" id="e_fecha_nac" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Sexo <span class="text-danger">*</span></label>
              <select class="form-select" name="sexo" id="e_sexo" required>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Estado <span class="text-danger">*</span></label>
              <select class="form-select" name="estado" id="e_estado" required>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
                <option value="Egresado">Egresado</option>
                <option value="Retirado">Retirado</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" class="form-control" name="direccion" id="e_direccion" maxlength="250">
          </div>

          <!-- Alerta para cambios de estado críticos -->
          <div id="alerta_estado_edit" class="alert alert-warning py-2 d-none">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <span id="texto_alerta_estado"></span>
          </div>

          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning">
              <i class="bi bi-pencil-square"></i> Actualizar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- MODAL: ELIMINAR ALUMNO -->
<div class="modal fade" id="modalEliminar" data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-trash-fill me-2"></i>Eliminar Alumno</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger py-2">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>
          <strong>¡Atención!</strong> Se eliminarán también las vinculaciones con tutores.
          Las matrículas y calificaciones históricas se conservarán.
        </div>
        <form id="formEliminar">
          <input type="hidden" name="id_alumno" id="del_id">
          <div class="mb-2">
            <label class="form-label">Alumno</label>
            <input type="text" class="form-control bg-light" id="del_nombre" readonly>
          </div>
          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="form-label">Cédula</label>
              <input type="text" class="form-control bg-light" id="del_cedula" readonly>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label">Nro. Matrícula</label>
              <input type="text" class="form-control bg-light" id="del_matricula" readonly>
            </div>
          </div>
          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">
              <i class="bi bi-trash-fill"></i> Eliminar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- DataTables -->
<script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>
<script src="<?php echo $ruta; ?>dt/jquery.dataTables.min.js"></script>
<script src="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/jszip.min.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/pdfmake.min.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/vfs_fonts.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/dataTables.buttons.min.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/buttons.bootstrap5.min.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/buttons.html5.min.js"></script>
<script src="<?php echo $ruta; ?>dt/botones/buttons.print.min.js"></script>

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
$(document).ready(function () {
    $('#tblAlumnos').DataTable({
        language: { url: "<?php echo $ruta; ?>dt/es-ES.json" },
        responsive: true,
        dom: 'Bfrtip',
        pageLength: 15,
        order: [[2, 'asc']],
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i>',
                titleAttr: 'Exportar a Excel',
                className: 'btn btn-success btn-sm',
                title: 'Lista de Alumnos',
                filename: 'Reporte_Alumnos',
                exportOptions: { columns: [0,1,2,4,5,6,7] }
            },
            {
                        extend:'pdfHtml5',

                        text:'<i class="bi bi-file-earmark-pdf"></i>',

                        className:'btn btn-danger',

                        title:'Lista de Alumnos' ,

                        filename:'Reporte_Alumnos',

                        exportOptions:{
                            columns:[0,1,2,3,5,6,7]
                        },

                        customize:function(doc){

                            doc.content[1].table.widths = 
                                Array(doc.content[1].table.body[0].length + 1).join('*').split('');
     

                            encabezadoPDF(
                                doc,
                                'REPORTE DE ALUMNOS',
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
                      exportOptions: { columns: [0, 1, 2, 3, 5, 6, 7] },
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
                                  .tabla-reporte td:nth-child(1) { width: 8%; text-align: center;}

                                  .tabla-reporte th:nth-child(2),
                                  .tabla-reporte td:nth-child(2) { width: 38%; text-align: center;}

                                  .tabla-reporte th:nth-child(3),
                                  .tabla-reporte td:nth-child(3) { width: 38%; text-align: center;}

                                  .tabla-reporte th:nth-child(4),
                                  .tabla-reporte td:nth-child(4) { width: 16%; text-align: center;}

                                  .tabla-reporte th:nth-child(5),
                                  .tabla-reporte td:nth-child(5) { width: 16%; text-align: center;}

                                  .tabla-reporte th:nth-child(6),
                                  .tabla-reporte td:nth-child(6) { width: 16%; text-align: center;}

                                  .tabla-reporte th:nth-child(7),
                                  .tabla-reporte td:nth-child(7) { width: 16%; text-align: center;}

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
                                                  <div class="titulo-reporte">REPORTE DE ALUMNOS</div>
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
        columnDefs: [{ orderable: false, targets: 7, searchable: false }, { orderable: false, targets: 8, searchable: false }]
    });
});


// VALIDACIONES CLIENTE
function validarCedulaOpcional(cedula) {
    if (cedula === '') return true;         
    return /^[0-9A-Za-z\-]{3,20}$/.test(cedula.trim());
}
function validarNombre(str) {
    return /^[A-Za-záéíóúÁÉÍÓÚñÑüÜ\s'\-]+$/u.test(str.trim()) && str.trim().length >= 2;
}
function validarFechaNac(fecha) {
    if (!fecha) return false;
    var hoy  = new Date();
    var fnac = new Date(fecha);
    if (isNaN(fnac.getTime())) return false;
    var edad = (hoy - fnac) / (1000 * 60 * 60 * 24 * 365.25);
    return edad >= 3 && edad <= 25; // rango razonable para un alumno escolar
}
function validarMatricula(mat) {
    if (mat === '') return true; // opcional
    return /^[A-Za-z0-9\-_\/]{3,30}$/.test(mat.trim());
}


//  GUARDAR NUEVO ALUMNO
document.getElementById('formNuevo').addEventListener('submit', function (e) {
    e.preventDefault();

    var nombres = document.getElementById('n_nombres').value.trim();
    var apellidos = document.getElementById('n_apellidos').value.trim();
    var cedula = document.getElementById('n_cedula').value.trim();
    var fecha_nac = document.getElementById('n_fecha_nac').value;

    if (!validarNombre(nombres)) {
        alertify.error('El campo Nombres es inválido. Solo letras y espacios, mínimo 2 caracteres.');
        return;
    }
    if (!validarNombre(apellidos)) {
        alertify.error('El campo Apellidos es inválido. Solo letras y espacios, mínimo 2 caracteres.');
        return;
    }
    if (!validarCedulaOpcional(cedula)) {
        alertify.error('La cédula debe tener entre 3 y 20 caracteres alfanuméricos.');
        return;
    }
    if (!validarFechaNac(fecha_nac)) {
        alertify.error('La fecha de nacimiento no es válida o está fuera del rango permitido (3 a 25 años).');
        return;
    }

    enviarFormulario('formNuevo', 'alumnos_guardar.php');
});


//  LLENAR MODAL EDITAR
document.addEventListener('click', function (e) {
    var boton = e.target.closest('.btnEditar');
    if (!boton) return;

    document.getElementById('e_id').value = boton.dataset.id;
    document.getElementById('e_cedula').value = boton.dataset.cedula;
    document.getElementById('e_nombres').value = boton.dataset.nombres;
    document.getElementById('e_apellidos').value = boton.dataset.apellidos;
    document.getElementById('e_fecha_nac').value = boton.dataset.fecha_nac;
    document.getElementById('e_direccion').value = boton.dataset.direccion;

    setSelectValue('e_sexo',   boton.dataset.sexo);
    setSelectValue('e_estado', boton.dataset.estado);

    // Ocultar alerta de estado al abrir
    document.getElementById('alerta_estado_edit').classList.add('d-none');
});

// Alerta dinámica al cambiar estado en edición
document.getElementById('e_estado').addEventListener('change', function () {
    var alertaBox  = document.getElementById('alerta_estado_edit');
    var alertaTxt  = document.getElementById('texto_alerta_estado');
    var estadoNuevo = this.value;
    var msgs = {
        'Inactivo' : 'Al inactivar el alumno no podrá ser matriculado ni aparecer en listas activas.',
        'Egresado' : 'Marcar como Egresado indica que el alumno completó su trayectoria en la institución.',
        'Retirado' : 'El retiro implica que el alumno dejó la institución antes de finalizar el ciclo.'
    };
    if (msgs[estadoNuevo]) {
        alertaTxt.textContent = msgs[estadoNuevo];
        alertaBox.classList.remove('d-none');
    } else {
        alertaBox.classList.add('d-none');
    }
});


// ACTUALIZAR ALUMNO
document.getElementById('formEditar').addEventListener('submit', function (e) {
    e.preventDefault();

    var nombres = document.getElementById('e_nombres').value.trim();
    var apellidos = document.getElementById('e_apellidos').value.trim();
    var cedula = document.getElementById('e_cedula').value.trim();
    var fecha_nac = document.getElementById('e_fecha_nac').value;

    if (!validarNombre(nombres)) {
        alertify.error('El campo Nombres es inválido. Solo letras y espacios, mínimo 2 caracteres.');
        return;
    }
    if (!validarNombre(apellidos)) {
        alertify.error('El campo Apellidos es inválido. Solo letras y espacios, mínimo 2 caracteres.');
        return;
    }
    if (!validarCedulaOpcional(cedula)) {
        alertify.error('La cédula debe tener entre 3 y 20 caracteres alfanuméricos.');
        return;
    }
    if (!validarFechaNac(fecha_nac)) {
        alertify.error('La fecha de nacimiento no es válida o está fuera del rango permitido (3 a 25 años).');
        return;
    }

    var formData = new FormData(this);
    fetch('api/alumnos_actualizar.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.status) {
                alertify.success(data.msg);
                setTimeout(() => window.location.reload(), 600);
            } else {
                alertify.error(data.msg);
            }
        })
        .catch(() => alertify.error('Error de comunicación con el servidor.'));
});


// MODAL ELIMINAR
document.addEventListener('click', function (e) {
    var boton = e.target.closest('.btnEliminar');
    if (!boton) return;
    document.getElementById('del_id').value = boton.dataset.id;
    document.getElementById('del_nombre').value = boton.dataset.nombre;
    document.getElementById('del_cedula').value = boton.dataset.cedula;
    document.getElementById('del_matricula').value = boton.dataset.matricula;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
});

document.getElementById('formEliminar').addEventListener('submit', function (e) {
    e.preventDefault();
    var formData = new FormData(this);

    alertify.confirm(
        'Eliminar Alumno',
        '¿Está seguro? Las vinculaciones con tutores serán eliminadas. Las matrículas históricas se conservarán',
        function () {
            fetch('api/alumnos_eliminar.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status) {
                        alertify.success(data.msg);
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        alertify.error(data.msg);
                    }
                })
                .catch(() => alertify.error('Error en el servidor.'));
        },
        function () { alertify.error('Cancelado'); }
    ).set('labels', { ok: 'Sí, Eliminar', cancel: 'Cancelar' });
});


//  UTILIDADES
function setSelectValue(id, valor) {
    var sel = document.getElementById(id);
    for (var i = 0; i < sel.options.length; i++)
        sel.options[i].selected = (sel.options[i].value === valor);
}

function enviarFormulario(formId, url) {
    var formData = new FormData(document.getElementById(formId));
    fetch(url, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.status) {
                alertify.success(data.msg);
                setTimeout(() => window.location.reload(), 600);
            } else {
                alertify.error(data.msg);
            }
        })
        .catch(() => alertify.error('Error de comunicación con el servidor.'));
}

// Limpiar modal Nuevo al abrir
document.getElementById('modalNuevo').addEventListener('show.bs.modal', function () {
    this.querySelector('form').reset();
});
</script>