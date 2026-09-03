<?php
	$ruta = "../";
  	include __DIR__ . '/includes/header.php';
 	require_once __DIR__ . '/../servicios/conexion.php';

	// Lista de tutores con conteo de alumnos asignados
	$sql_tutores = "SELECT t.idTutor, t.cedula, t.nombres, t.apellidos,
					t.parentesco, t.telefono, t.direccion, t.estado,
					t.idUsuario,
					COUNT(at2.idAlumno) AS total_alumnos
					FROM tutor t
					LEFT JOIN alumno_tutor at2 ON at2.idTutor = t.idTutor
					GROUP BY t.idTutor
					ORDER BY t.apellidos, t.nombres";
	$lista_tutores = buscar_datos($sql_tutores);

	// Alumnos activos para el selector del modal
	$sql_alumnos = "SELECT idAlumno, cedula, nombres, apellidos
					FROM alumno
					WHERE estado = 'Activo'
					ORDER BY apellidos, nombres";
	$lista_alumnos = buscar_datos($sql_alumnos);

	// Parentescos predefinidos
	$parentescos = ['Padre','Madre','Abuelo','Abuela','Tío','Tía','Hermano','Hermana','Tutor legal','Otro'];
?>

<link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css"/>

<div class="main-content">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    	<h1 class="h3 mb-0 text-gray-800"><i class="bi bi-people-fill me-2 text-primary"></i>Mantenimiento de Tutores</h1>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevo">
            <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Tutor
        </button>
    </div>
    <!-- Tabla -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tblTutores" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Cédula</th>
                            <th>Nombres y Apellidos</th>
                            <th>Parentesco</th>
                            <th>Teléfono</th>
                            <th>Alumnos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($lista_tutores): foreach ($lista_tutores as $t):
                        $color = $t['estado'] === 'Activo' ? 'success' : 'danger';
                    ?>
                        <tr>
                            <td><?php echo $t['idTutor']; ?></td>
                            <td><?php echo htmlspecialchars($t['cedula']); ?></td>
                            <td><?php echo htmlspecialchars($t['apellidos'] . ', ' . $t['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($t['parentesco']); ?></td>
                            <td><?php echo htmlspecialchars($t['telefono']); ?></td>
                            <td class="text-center">
                                <?php if ($t['total_alumnos'] > 0): ?>
                                    <button class="btn btn-outline-primary btn-sm btnVerAlumnos"
                                        data-id="<?php echo $t['idTutor']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($t['nombres'].' '.$t['apellidos'], ENT_QUOTES); ?>">
                                        <i class="bi bi-mortarboard-fill me-1"></i><?php echo $t['total_alumnos']; ?>
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sin alumnos</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $color; ?>">
                                    <?php echo $t['estado']; ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm btnEditar"
                                    data-bs-toggle="modal" data-bs-target="#modalEditar"
                                    data-id="<?php echo $t['idTutor']; ?>"
                                    data-cedula="<?php echo htmlspecialchars($t['cedula'], ENT_QUOTES); ?>"
                                    data-nombres="<?php echo htmlspecialchars($t['nombres'], ENT_QUOTES); ?>"
                                    data-apellidos="<?php echo htmlspecialchars($t['apellidos'], ENT_QUOTES); ?>"
                                    data-parentesco="<?php echo htmlspecialchars($t['parentesco'], ENT_QUOTES); ?>"
                                    data-telefono="<?php echo htmlspecialchars($t['telefono'], ENT_QUOTES); ?>"
                                    data-direccion="<?php echo htmlspecialchars($t['direccion'] ?? '', ENT_QUOTES); ?>"
                                    data-estado="<?php echo $t['estado']; ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <!-- <button type="button" class="btn btn-info btn-sm btnAsignarAlumno d-none"
                                    data-id="<?php echo $t['idTutor']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($t['nombres'].' '.$t['apellidos'], ENT_QUOTES); ?>">
                                    <i class="bi bi-person-plus-fill"></i>
                                </button> -->
                                <button type="button" class="btn btn-danger btn-sm btnEliminar"
                                    data-id="<?php echo $t['idTutor']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($t['nombres'].' '.$t['apellidos'], ENT_QUOTES); ?>"
                                    data-cedula="<?php echo htmlspecialchars($t['cedula'], ENT_QUOTES); ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Modal Nuevo Tutor -->
<div class="modal fade" id="modalNuevo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h1 class="modal-title fs-5"><i class="bi bi-person-plus-fill me-2"></i>Nuevo Tutor</h1>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
			</div>
		<div class="modal-body">
			<form id="formNuevo">
			<div class="row">
				<div class="col-md-4 mb-3">
					<label class="form-label">Cédula <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="cedula" id="n_cedula"
							placeholder="Ej: 4500123" maxlength="20" required>
				</div>
				<div class="col-md-4 mb-3">
					<label class="form-label">Nombres <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="nombres" id="n_nombres"
							placeholder="Ej: María" maxlength="100" required>
				</div>
				<div class="col-md-4 mb-3">
					<label class="form-label">Apellidos <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="apellidos" id="n_apellidos"
							placeholder="Ej: González Pérez" maxlength="100" required>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4 mb-3">
				<label class="form-label">Parentesco <span class="text-danger">*</span></label>
				<select class="form-select" name="parentesco" id="n_parentesco" required>
					<option value="" disabled selected>Seleccione</option>
					<?php foreach ($parentescos as $p): ?>
					<option value="<?php echo $p; ?>"><?php echo $p; ?></option>
					<?php endforeach; ?>
				</select>
				</div>
				<div class="col-md-4 mb-3">
				<label class="form-label">Teléfono <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="telefono" id="n_telefono"
						placeholder="Ej: 0981 123 456" maxlength="20" required>
				</div>
				<div class="col-md-4 mb-3">
				<label class="form-label">Estado</label>
				<select class="form-select" name="estado" id="n_estado">
					<option value="Activo" selected>Activo</option>
					<option value="Inactivo">Inactivo</option>
				</select>
				</div>
			</div>

			<div class="mb-3">
				<label class="form-label">Dirección</label>
				<input type="text" class="form-control" name="direccion" id="n_direccion"
					placeholder="Dirección domiciliaria (opcional)" maxlength="250">
			</div>

			<!-- Asignación de alumnos al crear -->
			<hr>
			<h6 class="fw-semibold mb-2"><i class="bi bi-mortarboard-fill me-1 text-primary"></i>Alumnos asignados</h6>
			<div class="alert alert-info py-2 small">
				<i class="bi bi-info-circle-fill me-1"></i>
				Puede asignar uno o más alumnos al crear el tutor. El primero seleccionado será marcado como <strong>tutor principal</strong>.
			</div>

			<div id="contenedor_alumnos_nuevo">
				<div class="fila-alumno row g-2 mb-2 align-items-center">
				<div class="col-md-8">
					<select class="form-select select-alumno" name="alumnos[]">
						<option value="">Sin asignar alumno aún</option>
						<?php if ($lista_alumnos): foreach ($lista_alumnos as $a): ?>
							<option value="<?php echo $a['idAlumno']; ?>">
								<?php echo htmlspecialchars($a['apellidos'].', '.$a['nombres'].' CI: '.$a['cedula']); ?>
							</option>
						<?php endforeach; endif; ?>
					</select>
				</div>
				<div class="col-md-3">
						<select class="form-select select-principal" name="principal[]">
							<option value="Sí">Principal</option>
							<option value="No">Secundario</option>
						</select>
				</div>
				<div class="col-md-1 text-end">
					<button type="button" class="btn btn-outline-danger btn-sm btn-quitar-alumno" title="Quitar fila">
					<i class="bi bi-x-lg"></i>
					</button>
				</div>
				</div>
			</div>
			<button type="button" class="btn btn-outline-primary btn-sm mt-1" id="btnAgregarAlumnoNuevo">
				<i class="bi bi-plus-circle me-1"></i>Agregar alumno
			</button>

			<div class="modal-footer px-0 pb-0 mt-3">
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


<!-- Modal Editar Tutor -->
<div class="modal fade" id="modalEditar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h1 class="modal-title fs-5"><i class="bi bi-pencil-square me-2"></i>Editar Tutor</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditar">
          <input type="hidden" name="id_tutor" id="e_id">

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Cédula <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="cedula" id="e_cedula" maxlength="20" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Nombres <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombres" id="e_nombres" maxlength="100" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Apellidos <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="apellidos" id="e_apellidos" maxlength="100" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Parentesco <span class="text-danger">*</span></label>
              <select class="form-select" name="parentesco" id="e_parentesco" required>
                <?php foreach ($parentescos as $p): ?>
                <option value="<?php echo $p; ?>"><?php echo $p; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Teléfono <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="telefono" id="e_telefono" maxlength="20" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Estado <span class="text-danger">*</span></label>
              <select class="form-select" name="estado" id="e_estado" required>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" class="form-control" name="direccion" id="e_direccion" maxlength="250">
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


<!-- Modal asignar alumno al tutor -->
<div class="modal fade" id="modalAsignar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h1 class="modal-title fs-5"><i class="bi bi-person-plus-fill me-2"></i>Gestionar Alumnos del Tutor</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="asignar_id_tutor">
        <p class="fw-semibold mb-1">Tutor: <span id="asignar_nombre_tutor" class="text-primary"></span></p>

        <!-- Alumnos ya asignados (cargados por AJAX) -->
        <div id="alumnos_actuales_wrapper">
          <h6 class="mt-3 mb-2"><i class="bi bi-list-check me-1"></i>Alumnos asignados actualmente</h6>
          <div id="tabla_alumnos_actuales">
            <div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando</div>
          </div>
        </div>

        <hr>
        <h6><i class="bi bi-plus-circle me-1 text-info"></i>Agregar nuevo alumno</h6>
        <form id="formAsignar">
          <input type="hidden" name="id_tutor" id="as_id_tutor">
          <div class="row g-2 align-items-end">
            <div class="col-md-7">
              <label class="form-label">Alumno <span class="text-danger">*</span></label>
              <select class="form-select" name="idAlumno" id="as_alumno" required>
                <option value="" disabled selected>Seleccione un alumno</option>
                <?php if ($lista_alumnos): foreach ($lista_alumnos as $a): ?>
                <option value="<?php echo $a['idAlumno']; ?>">
                  <?php echo htmlspecialchars($a['apellidos'].', '.$a['nombres'].' CI: '.$a['cedula']); ?>
                </option>
                <?php endforeach; endif; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Tipo</label>
              <select class="form-select" name="es_principal" id="as_principal">
                <option value="Sí">Principal</option>
                <option value="No">Secundario</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-info w-100">
                <i class="bi bi-plus-lg"></i> Agregar
              </button>
            </div>
          </div>
        </form>

        <div class="modal-footer px-0 pb-0 mt-3">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- MOdal para ver alumnos asignados -->
<div class="modal fade" id="modalVerAlumnos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h1 class="modal-title fs-5"><i class="bi bi-mortarboard-fill me-2"></i>Alumnos del Tutor</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="fw-semibold">Tutor: <span id="ver_nombre_tutor" class="text-primary"></span></p>
        <div id="ver_alumnos_contenido">
          <div class="text-center py-3"><div class="spinner-border text-primary"></div></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<!-- Modal Eliminar Tutor -->
<div class="modal fade" id="modalEliminar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h1 class="modal-title fs-5"><i class="bi bi-trash-fill me-2"></i>Eliminar Tutor</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger py-2">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>
          <strong>¿Estás seguro?</strong> Se desvincularán los alumnos asignados. Esta acción no se puede deshacer.
        </div>
        <form id="formEliminar">
          <input type="hidden" name="id_tutor" id="del_id">
          <div class="mb-2">
            <label class="form-label">Tutor</label>
            <input type="text" class="form-control bg-light" id="del_nombre" readonly>
          </div>
          <div class="mb-2">
            <label class="form-label">Cédula</label>
            <input type="text" class="form-control bg-light" id="del_cedula" readonly>
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
    $('#tblTutores').DataTable({
        language:   { url: "<?php echo $ruta; ?>dt/es-ES.json" },
        responsive: true,
        dom: 'Bfrtip',
        pageLength: 10,
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i>',
                titleAttr: 'Exportar a Excel',
                className: 'btn btn-success btn-sm',
                title: 'Lista de Tutores',
                filename: 'Reporte_Tutores',
                exportOptions: { columns: [0,1,2,3,4,5,6] }
            },
            {
                    extend:'pdfHtml5',

                    text:'<i class="bi bi-file-earmark-pdf"></i>',

                    className:'btn btn-danger',

                    title:'Lista de Tutores',

                    filename:'Reporte_Tutores',

                    exportOptions:{
                        columns:[0,1,2,3,4,5,6]
                    },

                    customize:function(doc){

                        encabezadoPDF(
                            doc,
                            'REPORTE DE TUTORES',
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
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] },
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

                                /* Ajuste de anchos para 7 columnas */
                                .tabla-reporte th:nth-child(1),
                                .tabla-reporte td:nth-child(1) { width: 6%; text-align: center; }

                                .tabla-reporte th:nth-child(2),
                                .tabla-reporte td:nth-child(2) { width: 13%; text-align: center; }

                                .tabla-reporte th:nth-child(3),
                                .tabla-reporte td:nth-child(3) { width: 38%; text-align: center; }

                                .tabla-reporte th:nth-child(4),
                                .tabla-reporte td:nth-child(4) { width: 16%; text-align: center; }

                                .tabla-reporte th:nth-child(5),
                                .tabla-reporte td:nth-child(5) { width: 16%; text-align: center; }

                                .tabla-reporte th:nth-child(6),
                                .tabla-reporte td:nth-child(6) { width: 16%; text-align: center; }

                                .tabla-reporte th:nth-child(7),
                                .tabla-reporte td:nth-child(7) { width: 12%; text-align: center; }

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
                                                <div class="titulo-reporte">REPORTE DE TUTORES</div>
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
        columnDefs: [{ orderable: false, targets: 7, searchable: false }]
    });
});


// template de alumnos para nuevo tutor
function filaAlumnoNuevo() {
    var opciones = <?php
        $opts = '<option value="">Sin asignar alumno aún</option>';
        if ($lista_alumnos) foreach ($lista_alumnos as $a)
            $opts .= '<option value="'.$a['idAlumno'].'">'.
                      htmlspecialchars($a['apellidos'].', '.$a['nombres'].' CI: '.$a['cedula']).'</option>';
        echo json_encode($opts);
    ?>;

    return `<div class="fila-alumno row g-2 mb-2 align-items-center">
        <div class="col-md-8">
            <select class="form-select select-alumno" name="alumnos[]">
                ${opciones}
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select select-principal" name="principal[]">
                <option value="Sí">Principal</option>
                <option value="No" selected>Secundario</option>
            </select>
        </div>
        <div class="col-md-1 text-end">
            <button type="button" class="btn btn-outline-danger btn-sm btn-quitar-alumno" title="Quitar fila">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>`;
}

// Agregar fila de alumno en modal Nuevo
document.getElementById('btnAgregarAlumnoNuevo').addEventListener('click', function () {
    document.getElementById('contenedor_alumnos_nuevo').insertAdjacentHTML('beforeend', filaAlumnoNuevo());
});

// Quitar fila de alumno
document.addEventListener('click', function (e) {
    if (e.target.closest('.btn-quitar-alumno')) {
        var fila = e.target.closest('.fila-alumno');
        // No eliminar si es la única fila
        var filas = fila.parentElement.querySelectorAll('.fila-alumno');
        if (filas.length > 1) {
            fila.remove();
        } else {
            fila.querySelector('.select-alumno').value = '';
        }
    }
});

// Limpiar modal Nuevo al abrir
document.getElementById('modalNuevo').addEventListener('show.bs.modal', function () {
    this.querySelector('form').reset();
    // Dejar solo una fila de alumno
    var contenedor = document.getElementById('contenedor_alumnos_nuevo');
    var filas = contenedor.querySelectorAll('.fila-alumno');
    for (var i = 1; i < filas.length; i++) filas[i].remove();
    if (filas[0]) filas[0].querySelector('.select-alumno').value = '';
});


// Validaciones
function validarCedula(cedula) {
    return /^[0-9A-Za-z\-]{3,20}$/.test(cedula.trim());
}
function validarTelefono(tel) {
    return /^[0-9\s\+\-\(\)]{6,20}$/.test(tel.trim());
}
function soloLetrasYEspacios(str) {
    // Permite letras (incluyendo tildes), espacios y guión
    return /^[A-Za-záéíóúÁÉÍÓÚñÑüÜ\s\-']+$/.test(str.trim());
}


// Guardar nuevo tutor
document.getElementById('formNuevo').addEventListener('submit', function (e) {
    e.preventDefault();

    var cedula = document.getElementById('n_cedula').value.trim();
    var nombres = document.getElementById('n_nombres').value.trim();
    var apellidos = document.getElementById('n_apellidos').value.trim();
    var telefono = document.getElementById('n_telefono').value.trim();

    if (!validarCedula(cedula)) {
        alertify.error('La cédula debe tener entre 3 y 20 caracteres alfanuméricos.');
        return;
    }
    if (!soloLetrasYEspacios(nombres)) {
        alertify.error('El campo Nombres solo debe contener letras y espacios.');
        return;
    }
    if (!soloLetrasYEspacios(apellidos)) {
        alertify.error('El campo Apellidos solo debe contener letras y espacios.');
        return;
    }
    if (!validarTelefono(telefono)) {
        alertify.error('El teléfono no es válido. Use solo números, espacios o guiones.');
        return;
    }

    // Verificar alumnos duplicados en las filas
    var selects = document.querySelectorAll('#contenedor_alumnos_nuevo .select-alumno');
    var ids = [];
    var hayDuplicado = false;
    selects.forEach(function (s) {
        if (s.value !== '' && ids.includes(s.value)) {
            hayDuplicado = true;
        }
        if (s.value !== '') ids.push(s.value);
    });
    if (hayDuplicado) {
        alertify.error('Hay alumnos duplicados en la lista. Cada alumno debe aparecer una sola vez.');
        return;
    }

    // Verificar que haya a lo sumo un tutor principal
    var principales = 0;
    document.querySelectorAll('#contenedor_alumnos_nuevo .select-principal').forEach(function (s, i) {
        var alumnoSel = document.querySelectorAll('#contenedor_alumnos_nuevo .select-alumno')[i];
        if (alumnoSel.value !== '' && s.value === 'Sí') principales++;
    });
    if (principales > 1) {
        alertify.error('Solo puede haber un tutor principal por alumno. Revise las asignaciones.');
        return;
    }

    enviarFormulario('formNuevo', 'tutores_guardar.php');
});


// llenar modal de editar
document.addEventListener('click', function (e) {
    var boton = e.target.closest('.btnEditar');
    if (!boton) return;
    document.getElementById('e_id').value = boton.dataset.id;
    document.getElementById('e_cedula').value = boton.dataset.cedula;
    document.getElementById('e_nombres').value = boton.dataset.nombres;
    document.getElementById('e_apellidos').value = boton.dataset.apellidos;
    document.getElementById('e_telefono').value = boton.dataset.telefono;
    document.getElementById('e_direccion').value = boton.dataset.direccion;

    var selP = document.getElementById('e_parentesco');
    for (var i = 0; i < selP.options.length; i++)
        selP.options[i].selected = (selP.options[i].value === boton.dataset.parentesco);

    var selE = document.getElementById('e_estado');
    for (var j = 0; j < selE.options.length; j++)
        selE.options[j].selected = (selE.options[j].value === boton.dataset.estado);
});


// actualizar tutor
document.getElementById('formEditar').addEventListener('submit', function (e) {
    e.preventDefault();

    var cedula = document.getElementById('e_cedula').value.trim();
    var nombres = document.getElementById('e_nombres').value.trim();
    var apellidos = document.getElementById('e_apellidos').value.trim();
    var telefono = document.getElementById('e_telefono').value.trim();

    if (!validarCedula(cedula)) {
        alertify.error('La cédula debe tener entre 3 y 20 caracteres alfanuméricos.');
        return;
    }
    if (!soloLetrasYEspacios(nombres)) {
        alertify.error('El campo Nombres solo debe contener letras y espacios.');
        return;
    }
    if (!soloLetrasYEspacios(apellidos)) {
        alertify.error('El campo Apellidos solo debe contener letras y espacios.');
        return;
    }
    if (!validarTelefono(telefono)) {
        alertify.error('El teléfono no es válido.');
        return;
    }

    var formData = new FormData(this);
    fetch('api/tutores_actualizar.php', { method: 'POST', body: formData })
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


// modal asignar alumno
document.addEventListener('click', function (e) {
    var boton = e.target.closest('.btnAsignarAlumno');
    if (!boton) return;

    var idTutor = boton.dataset.id;
    var nombre = boton.dataset.nombre;

    document.getElementById('asignar_id_tutor').value = idTutor;
    document.getElementById('as_id_tutor').value = idTutor;
    document.getElementById('asignar_nombre_tutor').textContent = nombre;

    // Resetear formulario de agregar
    document.getElementById('formAsignar').reset();
    document.getElementById('as_id_tutor').value = idTutor;

    // Cargar alumnos actuales
    cargarAlumnosActuales(idTutor);

    new bootstrap.Modal(document.getElementById('modalAsignar')).show();
});

function cargarAlumnosActuales(idTutor) {
    document.getElementById('tabla_alumnos_actuales').innerHTML =
        '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando...</div>';

    fetch('api/tutores_alumnos.php?id_tutor=' + idTutor)
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                document.getElementById('tabla_alumnos_actuales').innerHTML =
                    '<p class="text-muted small">Este tutor no tiene alumnos asignados aún.</p>';
                return;
            }
            var html = '<table class="table table-sm table-bordered table-hover"><thead class="table-light"><tr><th>Alumno</th><th>Cédula</th><th>Tipo</th><th></th></tr></thead><tbody>';
            data.forEach(function (a) {
                var badge = a.es_principal === 'Sí'
                    ? '<span class="badge bg-success">Principal</span>'
                    : '<span class="badge bg-secondary">Secundario</span>';
                html += `<tr>
                    <td>${escHtml(a.apellidos)}, ${escHtml(a.nombres)}</td>
                    <td>${escHtml(a.cedula)}</td>
                    <td>${badge}</td>
                    <td>
                        <button class="btn btn-danger btn-sm btn-desvincular"
                            data-id="${a.idAlumnoTutor}"
                            data-tutor="${idTutor}">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('tabla_alumnos_actuales').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('tabla_alumnos_actuales').innerHTML =
                '<p class="text-danger">Error al cargar los alumnos.</p>';
        });
}

// Desvincular alumno del tutor
document.addEventListener('click', function (e) {
    var boton = e.target.closest('.btn-desvincular');
    if (!boton) return;

    var idRelacion = boton.dataset.id;
    var idTutor = boton.dataset.tutor;

    alertify.confirm(
        'Desvincular alumno',
        '¿Desvincular este alumno del tutor?',
        function () {
            fetch('api/tutores_desvincular.php', {
                method: 'POST',
                body: new URLSearchParams({ id_alumno_tutor: idRelacion })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    alertify.success(data.msg);
                    cargarAlumnosActuales(idTutor);
                } else {
                    alertify.error(data.msg);
                }
            });
        },
        function () {}
    ).set('labels', { ok: 'Sí, desvincular', cancel: 'Cancelar' });
});

// Guardar asignación de alumno
document.getElementById('formAsignar').addEventListener('submit', function (e) {
    e.preventDefault();
    if (!document.getElementById('as_alumno').value) {
        alertify.error('Seleccione un alumno.');
        return;
    }
    var formData = new FormData(this);
    fetch('api/tutores_asignar.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.status) {
                alertify.success(data.msg);
                cargarAlumnosActuales(document.getElementById('as_id_tutor').value);
                this.reset();
                document.getElementById('as_id_tutor').value = document.getElementById('asignar_id_tutor').value;
            } else {
                alertify.error(data.msg);
            }
        })
        .catch(() => alertify.error('Error de comunicación.'));
});


// para mostrar los alumnos
document.addEventListener('click', function (e) {
    var boton = e.target.closest('.btnVerAlumnos');
    if (!boton) return;

    document.getElementById('ver_nombre_tutor').textContent = boton.dataset.nombre;
    document.getElementById('ver_alumnos_contenido').innerHTML =
        '<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>';

    new bootstrap.Modal(document.getElementById('modalVerAlumnos')).show();

    fetch('api/tutores_alumnos.php?id_tutor=' + boton.dataset.id)
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                document.getElementById('ver_alumnos_contenido').innerHTML =
                    '<p class="text-muted">Sin alumnos asignados.</p>';
                return;
            }
            var html = '<ul class="list-group">';
            data.forEach(function (a) {
                var badge = a.es_principal === 'Sí'
                    ? '<span class="badge bg-success ms-2">Principal</span>'
                    : '<span class="badge bg-secondary ms-2">Secundario</span>';
                html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>${escHtml(a.apellidos)}, ${escHtml(a.nombres)} <small class="text-muted">CI: ${escHtml(a.cedula)}</small></span>
                    ${badge}
                </li>`;
            });
            html += '</ul>';
            document.getElementById('ver_alumnos_contenido').innerHTML = html;
        });
});


// Modal eliminar
document.addEventListener('click', function (e) {
    var boton = e.target.closest('.btnEliminar');
    if (!boton) return;
    document.getElementById('del_id').value = boton.dataset.id;
    document.getElementById('del_nombre').value = boton.dataset.nombre;
    document.getElementById('del_cedula').value = boton.dataset.cedula;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
});

document.getElementById('formEliminar').addEventListener('submit', function (e) {
    e.preventDefault();
    var formData = new FormData(this);

    alertify.confirm(
        'Eliminar Tutor',
        '¿Está seguro que desea eliminar este tutor? Se desvincularán todos los alumnos asignados.',
        function () {
            fetch('api/tutores_eliminar.php', { method: 'POST', body: formData })
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


// Envio generico
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

// Escape HTML para evitar inyección en los modales
function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>