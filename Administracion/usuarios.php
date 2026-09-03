<?php 
    $ruta = "../";
    include __DIR__ . '/includes/header.php';
    require_once __DIR__ . '/../servicios/conexion.php';

    $sql = "SELECT u.idUsuario, u.usuario, u.correo, u.idRol, r.rol, u.estado 
            FROM usuarios u 
            INNER JOIN rol r ON u.idRol = r.idRol
            ORDER BY u.idRol, u.usuario";
    $lista_usuarios = buscar_datos($sql);

    $sql_roles = "SELECT idRol, rol FROM rol WHERE activo = 'Sí'";
    $lista_roles = buscar_datos($sql_roles);

    // Docentes SIN cuenta — sin campo correo (columna eliminada de docente)
    $sql_docentes_sin_cuenta = "SELECT idDocente, cedula, nombres, apellidos 
                                FROM docente 
                                WHERE idUsuario IS NULL AND estado = 'Activo'
                                ORDER BY apellidos, nombres";
    $docentes_sin_cuenta = buscar_datos($sql_docentes_sin_cuenta);

    // Tutores SIN cuenta — sin campo correo (columna eliminada de tutor)
    $sql_tutores_sin_cuenta = "SELECT idTutor, cedula, nombres, apellidos, parentesco 
                               FROM tutor 
                               WHERE idUsuario IS NULL AND estado = 'Activo'
                               ORDER BY apellidos, nombres";
    $tutores_sin_cuenta = buscar_datos($sql_tutores_sin_cuenta);

    $sql_roles_admin = "SELECT idRol, rol FROM rol WHERE idRol IN (1,2,5) AND activo = 'Sí'";
    $roles_admin = buscar_datos($sql_roles_admin);
?>

<!-- ID de sesión expuesto de forma segura para la validación JS al eliminar -->
<script>
    var ID_USUARIO_SESION = <?php echo (int)($_SESSION['idUsuario'] ?? 0); ?>;
</script>

<link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css"/>

<div class="main-content">

    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="h3 mb-0 text-gray-800">
             <i class="bi bi-people-fill me-2 text-primary"></i>Mantenimiento de Usuarios
        </h1>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdmin">
                <i class="bi bi-shield-lock-fill"></i> Nuevo Admin
            </button>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalDocente">
                <i class="bi bi-person-workspace"></i> Nuevo Docente
            </button>
            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalTutor">
                <i class="bi bi-people-fill"></i> Nuevo Tutor
            </button>
        </div>
    </div>

    <!-- TABS -->
    <ul class="nav nav-tabs mb-3" id="tabUsuarios" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-todos-btn" data-bs-toggle="tab" data-bs-target="#tab-todos" type="button" role="tab">
                <i class="bi bi-people"></i> Todos
                <span class="badge bg-secondary ms-1" id="cnt-todos">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-admin-btn" data-bs-toggle="tab" data-bs-target="#tab-admin" type="button" role="tab">
                <i class="bi bi-shield-lock"></i> Administrativos
                <span class="badge bg-primary ms-1" id="cnt-admin">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-docente-btn" data-bs-toggle="tab" data-bs-target="#tab-docente" type="button" role="tab">
                <i class="bi bi-person-workspace"></i> Docentes
                <span class="badge bg-success ms-1" id="cnt-docente">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-tutor-btn" data-bs-toggle="tab" data-bs-target="#tab-tutor" type="button" role="tab">
                <i class="bi bi-people-fill"></i> Tutores
                <span class="badge bg-warning text-dark ms-1" id="cnt-tutor">0</span>
            </button>
        </li>
    </ul>

    <!-- Contenido de las tablas -->
    <div class="tab-content" id="tabUsuariosContent">
        <?php
        $tabs = [
            'todos' => ['roles' => [1,2,3,4,5], 'activo' => true],
            'admin' => ['roles' => [1,2,5], 'activo' => false],
            'docente' => ['roles' => [3], 'activo' => false],
            'tutor' => ['roles' => [4], 'activo' => false],
        ];
        foreach ($tabs as $tabKey => $tabInfo):
            $activo = $tabInfo['activo'] ? 'show active' : '';
        ?>
        <div class="tab-pane fade <?php echo $activo; ?>" id="tab-<?php echo $tabKey; ?>" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover tblUsuarios"
                               id="tbl_<?php echo $tabKey; ?>"
                               width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if ($lista_usuarios):
                                foreach ($lista_usuarios as $dato):
                                    if (!in_array((int)$dato['idRol'], $tabInfo['roles'])) continue;
                                    $estadoColor = ($dato['estado'] == 'Activo') ? 'success' : 'danger';
                                    $rolBadge = 'secondary';
                                    if (in_array((int)$dato['idRol'], [1,2,3])) $rolBadge = 'primary';
                                    elseif ((int)$dato['idRol'] === 4) $rolBadge = 'success';
                                    elseif ((int)$dato['idRol'] === 5) $rolBadge = 'warning';
                            ?>
                                <tr>
                                    <td><?php echo $dato['idUsuario']; ?></td>
                                    <td><?php echo htmlspecialchars($dato['usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($dato['correo']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $rolBadge; ?> <?php echo ($rolBadge=='warning')?'text-dark':''; ?>">
                                            <?php echo htmlspecialchars($dato['rol']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $estadoColor; ?>">
                                            <?php echo $dato['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm btnEditar"
                                            data-bs-toggle="modal" data-bs-target="#modalEditar"
                                            data-id="<?php echo $dato['idUsuario']; ?>"
                                            data-usuario="<?php echo htmlspecialchars($dato['usuario'], ENT_QUOTES); ?>"
                                            data-correo="<?php echo htmlspecialchars($dato['correo'], ENT_QUOTES); ?>"
                                            data-idrol="<?php echo $dato['idRol']; ?>"
                                            data-estado="<?php echo $dato['estado']; ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm btnEliminar"
                                            data-id="<?php echo $dato['idUsuario']; ?>"
                                            data-usuario="<?php echo htmlspecialchars($dato['usuario'], ENT_QUOTES); ?>"
                                            data-correo="<?php echo htmlspecialchars($dato['correo'], ENT_QUOTES); ?>"
                                            data-rol="<?php echo htmlspecialchars($dato['rol'], ENT_QUOTES); ?>"
                                            data-estado="<?php echo $dato['estado']; ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php
                                endforeach;
                            endif;
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>


<!-- Modal nuevo Usuario Amin --> 
<div class="modal fade" id="modalAdmin" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h1 class="modal-title fs-5"><i class="bi bi-shield-lock-fill me-2"></i>Nuevo Usuario Administrativo</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAdmin">

          <div class="mb-3">
            <label class="form-label">Nombre de usuario <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="usuario" id="admin_usuario"
                   placeholder="Mínimo 5 caracteres" minlength="5" required>
            <div class="form-text">Mínimo 5 caracteres, sin espacios.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="correo" id="admin_correo"
                   placeholder="correo@escuela.edu.py" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Contraseña <span class="text-danger">*</span></label>
              <input type="password" class="form-control" name="clave" id="admin_clave" required>
              <div class="form-text">Mín. 8 car., 1 mayúscula, 1 número, 1 especial.</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="admin_confirmar" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Rol <span class="text-danger">*</span></label>
            <select class="form-select" name="idRol" id="admin_idRol" required>
              <option value="" disabled selected>Seleccione...</option>
              <?php if($roles_admin): foreach($roles_admin as $r): ?>
              <option value="<?php echo $r['idRol']; ?>"><?php echo $r['rol']; ?></option>
              <?php endforeach; endif; ?>
            </select>
          </div>

          <input type="hidden" name="tipo_registro" value="admin">

          <div class="modal-footer px-0 pb-0">
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

<!-- <! Modal nuevo Usuario Docente !> -->
<div class="modal fade" id="modalDocente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h1 class="modal-title fs-5"><i class="bi bi-person-workspace me-2"></i>Nuevo Usuario Docente</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formDocente">

          <?php if($docentes_sin_cuenta): ?>
          <div class="alert alert-info py-2 small">
            <i class="bi bi-info-circle-fill me-1"></i>
            Seleccione un docente registrado para crear su cuenta de acceso.
          </div>

          <div class="mb-3">
            <label class="form-label">Docente <span class="text-danger">*</span></label>
            <select class="form-select" name="idDocente" id="doc_idDocente" required onchange="cargarDatosDocente(this)">
              <option value="" disabled selected>Seleccione un docente...</option>
              <?php foreach($docentes_sin_cuenta as $d): ?>
              <option value="<?php echo $d['idDocente']; ?>"
                      data-nombre="<?php echo htmlspecialchars($d['nombres'].' '.$d['apellidos'], ENT_QUOTES); ?>">
                <?php echo htmlspecialchars($d['apellidos'].', '.$d['nombres'].' — CI: '.$d['cedula']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

            <div class="mb-3">
                <label class="form-label">Nombre de usuario</label>
                <input type="text" class="form-control bg-light" name="usuario" id="doc_usuario" readonly
                    title="Generado automáticamente desde el nombre del docente">
                <div class="form-text">Generado automáticamente al seleccionar el docente.</div>
            </div>

            <div class="mb-3">
            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="correo" id="doc_correo"
                placeholder="correo@escuela.edu.py" required>
            </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Contraseña <span class="text-danger">*</span></label>
              <input type="password" class="form-control" name="clave" id="doc_clave" required>
              <div class="form-text">Mín. 8 car., 1 mayúscula, 1 número, 1 especial.</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="doc_confirmar" required>
            </div>
          </div>

          <input type="hidden" name="idRol" value="3">
          <input type="hidden" name="tipo_registro" value="docente">

          <?php else: ?>
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            No hay docentes registrados sin cuenta de usuario.
            Primero registre un docente en la sección <strong>Docentes</strong>.
          </div>
          <?php endif; ?>

          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle-fill"></i> Cancelar
            </button>
            <?php if($docentes_sin_cuenta): ?>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-save"></i> Guardar
            </button>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- MODAL: NUEVO USUARIO TUTOR -->
<div class="modal fade" id="modalTutor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h1 class="modal-title fs-5"><i class="bi bi-people-fill me-2"></i>Nuevo Usuario Tutor</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formTutor">

          <?php if($tutores_sin_cuenta): ?>
          <div class="alert alert-info py-2 small">
            <i class="bi bi-info-circle-fill me-1"></i>
            Seleccione un tutor registrado para crear su cuenta de acceso.
          </div>

          <div class="mb-3">
            <label class="form-label">Tutor <span class="text-danger">*</span></label>
            <select class="form-select" name="idTutor" id="tut_idTutor" required onchange="cargarDatosTutor(this)">
              <option value="" disabled selected>Seleccione un tutor</option>
              <?php foreach($tutores_sin_cuenta as $t): ?>
              <option value="<?php echo $t['idTutor']; ?>"
                      data-nombre="<?php echo htmlspecialchars($t['nombres'].' '.$t['apellidos'], ENT_QUOTES); ?>">
                <?php echo htmlspecialchars($t['apellidos'].', '.$t['nombres'].' — '.$t['parentesco']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Nombre del usuario</label>
            <input type="text" class="form-control bg-light" name="usuario" id="tut_usuario" readonly
                   title="Generado automáticamente desde el nombre del tutor">
            <div class="form-text">Generado automáticamente al seleccionar el tutor.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="correo" id="tut_correo"
                    placeholder="correo@escuela.edu.py" required>
            </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Contraseña <span class="text-danger">*</span></label>
              <input type="password" class="form-control" name="clave" id="tut_clave" required>
              <div class="form-text">Mín. 8 car., 1 mayúscula, 1 número, 1 especial.</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="tut_confirmar" required>
            </div>
          </div>

          <input type="hidden" name="idRol" value="4">
          <input type="hidden" name="tipo_registro" value="tutor">

          <?php else: ?>
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            No hay tutores registrados sin cuenta de usuario.
            Primero registre un tutor en la sección <strong>Tutores</strong>.
          </div>
          <?php endif; ?>

          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle-fill"></i> Cancelar
            </button>
            <?php if($tutores_sin_cuenta): ?>
            <button type="submit" class="btn btn-warning">
              <i class="bi bi-save"></i> Guardar
            </button>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- MODAL: EDITAR USUARIO -->
<div class="modal fade" id="modalEditar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h1 class="modal-title fs-5"><i class="bi bi-pencil-square me-2"></i>Editar Usuario</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditarUsuario">

          <input type="hidden" name="id_usuario" id="id_edit">

          <div class="mb-3">
            <label class="form-label">Nombre de usuario <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="usuario" id="usuario_edit" minlength="5" required>
            <div class="form-text">Mínimo 5 caracteres.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="correo" id="correo_edit" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Rol <span class="text-danger">*</span></label>
            <select class="form-select" name="idRol" id="idRol_edit" required>
              <option value="" disabled selected>Seleccione...</option>
              <?php if($lista_roles): foreach($lista_roles as $r): ?>
              <option value="<?php echo $r['idRol']; ?>"><?php echo htmlspecialchars($r['rol']); ?></option>
              <?php endforeach; endif; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" class="form-control" name="clave" id="clave_edit"
                   placeholder="Dejar vacío para no cambiarla">
            <div class="form-text text-muted">
              Solo completar si se desea cambiar. Mín. 8 car., 1 mayúscula, 1 número, 1 especial.
            </div>
          </div>
            
          <div class="mb-3">
            <label class="form-label">Confirmar nueva contraseña</label>
            <input type="password" class="form-control" id="confirmar_edit"
                   placeholder="Confirmar nueva contraseña">
          </div>

          <div class="mb-3">
            <label class="form-label">Estado <span class="text-danger">*</span></label>
            <select class="form-select" name="estado" id="estado_edit">
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
            </select>
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


<!-- ============================================================
     MODAL: ELIMINAR USUARIO
     ============================================================ -->
<div class="modal fade" id="modalEliminar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h1 class="modal-title fs-5"><i class="bi bi-trash-fill me-2"></i>Eliminar Usuario</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger py-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>¿Estás seguro?</strong> Esta acción no se puede deshacer.
                </div>
                <form id="formEliminarUsuario">
                    <input type="hidden" name="id_usuario" id="id_delete">
                    <div class="mb-3">
                        <label class="form-label">Nombre de usuario</label>
                        <input type="text" class="form-control bg-light" id="usuario_delete" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control bg-light" id="correo_delete" readonly>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Rol</label>
                            <input type="text" class="form-control bg-light" id="rol_delete" readonly>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Estado</label>
                            <input type="text" class="form-control bg-light" id="estado_delete" readonly>
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

    var dtConfig = {
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
                title: 'Lista de Usuarios',
                filename: 'Reporte_Usuarios',
                exportOptions: { columns: [0,1,2,3,4] }
            },
            {
                    extend:'pdfHtml5',

                    text:'<i class="bi bi-file-earmark-pdf"></i>',

                    className:'btn btn-danger',

                    title:'Lista de Usuarios',

                    filename:'Reporte_Usuarios',

                    exportOptions:{
                        columns:[0,1,2,3]
                    },

                    customize:function(doc){

                        encabezadoPDF(
                            doc,
                            'REPORTE DE USUARIOS',
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
                                                <div class="titulo-reporte">REPORTE DE USUARIOS</div>
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
        columnDefs: [{ orderable: false, targets: 5 }]
    };

    ['todos','admin','docente','tutor'].forEach(function(tab) {
        var dt = $('#tbl_' + tab).DataTable(dtConfig);
        $('#cnt-' + tab).text(dt.rows().count());
    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tab = $(e.target).attr('id').replace('tab-','').replace('-btn','');
        $('#tbl_' + tab).DataTable().columns.adjust().draw();
    });
});


// UTILIDADES
function generarUsuario(nombreCompleto) {
    return nombreCompleto;
}

// Valida política de contraseña segura. Retorna array de errores (vacío = válida)
function validarPassword(clave) {
    var errores = [];
    if (clave.length < 8) errores.push("mínimo 8 caracteres");
    if (!/[A-Z]/.test(clave)) errores.push("al menos una letra mayúscula");
    if (!/[0-9]/.test(clave)) errores.push("al menos un número");
    if (!/[^A-Za-z0-9]/.test(clave)) errores.push("al menos un carácter especial (!@#$%...)");
    return errores;
}

// Valida longitud mínima del nombre de usuario
function validarUsuario(usuario) {
    return usuario.trim().length >= 5;
}


// AUTO-COMPLETAR USUARIO DESDE EL NOMBRE DEL DOCENTE
function cargarDatosDocente(sel) {
    var nombre = sel.options[sel.selectedIndex].getAttribute('data-nombre') || '';
    document.getElementById('doc_usuario').value = generarUsuario(nombre);
}

// AUTO-COMPLETAR USUARIO DESDE EL NOMBRE DEL TUTOR
function cargarDatosTutor(sel) {
    var nombre = sel.options[sel.selectedIndex].getAttribute('data-nombre') || '';
    document.getElementById('tut_usuario').value = generarUsuario(nombre);
}


// LIMPIAR MODALES DE CREACIÓN AL ABRIRSE
['modalAdmin','modalDocente','modalTutor'].forEach(function(id) {
    document.getElementById(id).addEventListener('show.bs.modal', function() {
        this.querySelector('form').reset();
        // Limpiar campo readonly de usuario generado si existe
        var u = this.querySelector('[id$="_usuario"]');
        if (u && u.readOnly) u.value = '';
    });
});


// GUARDAR NUEVO ADMIN
document.getElementById('formAdmin').addEventListener('submit', function(e) {
    e.preventDefault();

    var usuario = document.getElementById('admin_usuario').value.trim();
    var clave = document.getElementById('admin_clave').value;
    var confirma = document.getElementById('admin_confirmar').value;

    if (!validarUsuario(usuario)) {
        alertify.error("El nombre de usuario debe tener al menos 5 caracteres.");
        return;
    }
    var errPass = validarPassword(clave);
    if (errPass.length > 0) {
        alertify.error("Contraseña insegura: " + errPass.join(', ') + ".");
        return;
    }
    if (clave !== confirma) {
        alertify.error("Las contraseñas no coinciden.");
        document.getElementById('admin_confirmar').value = '';
        return;
    }

    enviarFormulario('formAdmin', 'usuarios_guardar.php');
});


// GUARDAR NUEVO DOCENTE
document.getElementById('formDocente').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!document.getElementById('doc_idDocente').value) {
        alertify.error("Seleccione un docente primero.");
        return;
    }
    var clave = document.getElementById('doc_clave').value;
    var confirma = document.getElementById('doc_confirmar').value;

    var errPass = validarPassword(clave);
    if (errPass.length > 0) {
        alertify.error("Contraseña insegura: " + errPass.join(', ') + ".");
        return;
    }
    if (clave !== confirma) {
        alertify.error("Las contraseñas no coinciden.");
        document.getElementById('doc_confirmar').value = '';
        return;
    }

    enviarFormulario('formDocente', 'usuarios_guardar.php');
});


// GUARDAR NUEVO TUTOR
document.getElementById('formTutor').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!document.getElementById('tut_idTutor').value) {
        alertify.error("Seleccione un tutor primero.");
        return;
    }
    var clave = document.getElementById('tut_clave').value;
    var confirma = document.getElementById('tut_confirmar').value;

    var errPass = validarPassword(clave);
    if (errPass.length > 0) {
        alertify.error("Contraseña insegura: " + errPass.join(', ') + ".");
        return;
    }
    if (clave !== confirma) {
        alertify.error("Las contraseñas no coinciden.");
        document.getElementById('tut_confirmar').value = '';
        return;
    }

    enviarFormulario('formTutor', 'usuarios_guardar.php');
});


// FUNCIÓN GENÉRICA DE ENVÍO AJAX
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
        .catch(() => alertify.error("Error de comunicación con el servidor."));
}


// LLENAR MODAL DE EDICIÓN
document.addEventListener('click', function(e) {
    var boton = e.target.closest('.btnEditar');
    if (!boton) return;

    document.getElementById('id_edit').value = boton.getAttribute('data-id');
    document.getElementById('usuario_edit').value = boton.getAttribute('data-usuario');
    document.getElementById('correo_edit').value = boton.getAttribute('data-correo');
    document.getElementById('clave_edit').value = '';

    var idRol = boton.getAttribute('data-idrol');
    var estado = boton.getAttribute('data-estado');

    var selRol = document.getElementById('idRol_edit');
    if (selRol) {
        for (var i = 0; i < selRol.options.length; i++) {
            selRol.options[i].selected = (selRol.options[i].value == idRol);
        }
    }

    var selEstado = document.getElementById('estado_edit');
    if (selEstado) {
        for (var j = 0; j < selEstado.options.length; j++) {
            selEstado.options[j].selected = (selEstado.options[j].value == estado);
        }
    }
});


// GUARDAR EDICIÓN
document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
    e.preventDefault();

    var usuario = document.getElementById('usuario_edit').value.trim();
    var clave = document.getElementById('clave_edit').value;

    if (!validarUsuario(usuario)) {
        alertify.error("El nombre de usuario debe tener al menos 5 caracteres.");
        return;
    }
    // Solo validar contraseña si se quiere cambiar
    if (clave !== '') {
        var errPass = validarPassword(clave);
        if (errPass.length > 0) {
            alertify.error("Contraseña insegura: " + errPass.join(', ') + ".");
            return;
        }
    }

    var formData = new FormData(this);
    fetch('api/usuarios_actualizar.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.status) {
                alertify.success(data.msg);
                setTimeout(() => window.location.reload(), 600);
            } else {
                alertify.error(data.msg);
            }
        })
        .catch(() => alertify.error("Error de comunicación con el servidor."));
});


// LLENAR MODAL DE ELIMINACIÓN
document.addEventListener('click', function(e) {
    var boton = e.target.closest('.btnEliminar');
    if (!boton) return;

    var idAEliminar = parseInt(boton.getAttribute('data-id'));

    // Bloquear si es el usuario de la sesión activa
    if (idAEliminar === parseInt(ID_USUARIO_SESION)) {
        alertify.error("No podés eliminar tu propio usuario mientras estás conectado.");
        return;
    }

    document.getElementById('id_delete').value = idAEliminar;
    document.getElementById('usuario_delete').value = boton.getAttribute('data-usuario');
    document.getElementById('correo_delete').value = boton.getAttribute('data-correo');
    document.getElementById('rol_delete').value = boton.getAttribute('data-rol');
    document.getElementById('estado_delete').value = boton.getAttribute('data-estado');

    var modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
    modal.show();
});


// CONFIRMAR ELIMINACIÓN
document.getElementById('formEliminarUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);

    alertify.confirm(
        "Eliminar Usuario",
        "¿Está seguro que desea eliminar este usuario permanentemente?",
        function() {
            fetch('api/usuarios_eliminar.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status) {
                        alertify.success(data.msg);
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        alertify.error(data.msg);
                    }
                })
                .catch(() => alertify.error("Error en el servidor."));
        },
        function() { alertify.error('Cancelado'); }
    ).set('labels', { ok: 'Sí, Eliminar', cancel: 'Cancelar' });
});
</script>