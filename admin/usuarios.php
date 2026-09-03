<?php
require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['administrador']);
require_once __DIR__ . '/../servicios/conexion.php';

$sql = "SELECT u.id_usuario, u.nombre_completo, u.email, u.activo,
               r.nombre_rol,
               c.id_comercio, c.razon_social, c.ruc, c.direccion_fiscal, c.rubro,
               rp.id_repartidor, rp.tipo_vehiculo, rp.placa_identificacion
        FROM usuarios u
        INNER JOIN roles r ON r.id_rol = u.id_rol
        LEFT JOIN comercios c ON c.id_usuario = u.id_usuario
        LEFT JOIN repartidores rp ON rp.id_usuario = u.id_usuario
        ORDER BY r.id_rol, u.nombre_completo";
$lista_usuarios = buscar_datos($sql);

include __DIR__ . '/../includes/header.php';
?>

<script>
    var ID_USUARIO_SESION = <?php echo (int) ($_SESSION['usuario_id'] ?? 0); ?>;
</script>

<link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css"/>

<!-- ENCABEZADO -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">
        <i class="bi bi-people-fill me-2 text-success"></i>Mantenimiento de Usuarios
    </h1>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdmin">
            <i class="bi bi-shield-lock-fill"></i> Nuevo Admin
        </button>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalComercio">
            <i class="bi bi-shop"></i> Nuevo Comercio
        </button>
        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalRepartidor">
            <i class="bi bi-bicycle"></i> Nuevo Repartidor
        </button>
    </div>
</div>

<!-- TABS -->
<ul class="nav nav-tabs mb-3" id="tabUsuarios" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-todos-btn" data-bs-toggle="tab" data-bs-target="#tab-todos" type="button" role="tab">
            <i class="bi bi-people"></i> Todos <span class="badge bg-secondary ms-1" id="cnt-todos">0</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-administrador-btn" data-bs-toggle="tab" data-bs-target="#tab-administrador" type="button" role="tab">
            <i class="bi bi-shield-lock"></i> Administradores <span class="badge bg-primary ms-1" id="cnt-administrador">0</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-comerciante-btn" data-bs-toggle="tab" data-bs-target="#tab-comerciante" type="button" role="tab">
            <i class="bi bi-shop"></i> Comercios <span class="badge bg-success ms-1" id="cnt-comerciante">0</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-repartidor-btn" data-bs-toggle="tab" data-bs-target="#tab-repartidor" type="button" role="tab">
            <i class="bi bi-bicycle"></i> Repartidores <span class="badge bg-warning text-dark ms-1" id="cnt-repartidor">0</span>
        </button>
    </li>
</ul>

<div class="tab-content" id="tabUsuariosContent">
    <?php
    $tabs = [
        'todos'         => ['roles' => ['administrador', 'comerciante', 'repartidor'], 'activo' => true],
        'administrador' => ['roles' => ['administrador'], 'activo' => false],
        'comerciante'   => ['roles' => ['comerciante'], 'activo' => false],
        'repartidor'    => ['roles' => ['repartidor'], 'activo' => false],
    ];
    foreach ($tabs as $tabKey => $tabInfo):
        $activoClase = $tabInfo['activo'] ? 'show active' : '';
    ?>
    <div class="tab-pane fade <?php echo $activoClase; ?>" id="tab-<?php echo $tabKey; ?>" role="tabpanel">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover tblUsuarios" id="tbl_<?php echo $tabKey; ?>" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($lista_usuarios): foreach ($lista_usuarios as $dato):
                            if (!in_array($dato['nombre_rol'], $tabInfo['roles'], true)) continue;

                            $rolBadge = ['administrador' => 'primary', 'comerciante' => 'success', 'repartidor' => 'warning'][$dato['nombre_rol']] ?? 'secondary';
                            $estadoColor = $dato['activo'] ? 'success' : 'danger';
                            $estadoTexto = $dato['activo'] ? 'Activo' : 'Inactivo';
                        ?>
                            <tr>
                                <td><?php echo $dato['id_usuario']; ?></td>
                                <td><?php echo htmlspecialchars($dato['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($dato['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $rolBadge; ?> <?php echo $rolBadge === 'warning' ? 'text-dark' : ''; ?>">
                                        <?php echo ucfirst($dato['nombre_rol']); ?>
                                    </span>
                                </td>
                                <td><span class="badge bg-<?php echo $estadoColor; ?>"><?php echo $estadoTexto; ?></span></td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm btnEditar"
                                        data-bs-toggle="modal" data-bs-target="#modalEditar"
                                        data-id="<?php echo $dato['id_usuario']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($dato['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-email="<?php echo htmlspecialchars($dato['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-rol="<?php echo $dato['nombre_rol']; ?>"
                                        data-activo="<?php echo (int) $dato['activo']; ?>"
                                        data-razon-social="<?php echo htmlspecialchars($dato['razon_social'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-ruc="<?php echo htmlspecialchars($dato['ruc'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-direccion-fiscal="<?php echo htmlspecialchars($dato['direccion_fiscal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-rubro="<?php echo htmlspecialchars($dato['rubro'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-tipo-vehiculo="<?php echo htmlspecialchars($dato['tipo_vehiculo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-placa="<?php echo htmlspecialchars($dato['placa_identificacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btnEliminar"
                                        data-id="<?php echo $dato['id_usuario']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($dato['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-email="<?php echo htmlspecialchars($dato['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-rol="<?php echo ucfirst($dato['nombre_rol']); ?>">
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
    <?php endforeach; ?>
</div>

<!-- MODAL: NUEVO ADMIN -->
<div class="modal fade" id="modalAdmin" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h1 class="modal-title fs-5"><i class="bi bi-shield-lock-fill me-2"></i>Nuevo Administrador</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAdmin">
          <div class="mb-3">
            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre_completo" id="admin_nombre" minlength="5" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" id="admin_email" required>
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
          <input type="hidden" name="tipo_registro" value="administrador">
          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: NUEVO COMERCIO -->
<div class="modal fade" id="modalComercio" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h1 class="modal-title fs-5"><i class="bi bi-shop me-2"></i>Nuevo Comercio</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formComercio">
          <div class="mb-3">
            <label class="form-label">Nombre de contacto <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre_completo" minlength="5" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Contraseña <span class="text-danger">*</span></label>
              <input type="password" class="form-control" name="clave" id="comercio_clave" required>
              <div class="form-text">Mín. 8 car., 1 mayúscula, 1 número, 1 especial.</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="comercio_confirmar" required>
            </div>
          </div>
          <hr>
          <div class="mb-3">
            <label class="form-label">Razón social <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="razon_social" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">RUC <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="ruc" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Rubro</label>
              <input type="text" class="form-control" name="rubro">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Dirección fiscal</label>
            <input type="text" class="form-control" name="direccion_fiscal">
          </div>
          <input type="hidden" name="tipo_registro" value="comerciante">
          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: NUEVO REPARTIDOR -->
<div class="modal fade" id="modalRepartidor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h1 class="modal-title fs-5"><i class="bi bi-bicycle me-2"></i>Nuevo Repartidor</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formRepartidor">
          <div class="mb-3">
            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre_completo" minlength="5" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Contraseña <span class="text-danger">*</span></label>
              <input type="password" class="form-control" name="clave" id="repartidor_clave" required>
              <div class="form-text">Mín. 8 car., 1 mayúscula, 1 número, 1 especial.</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="repartidor_confirmar" required>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Tipo de vehículo <span class="text-danger">*</span></label>
              <select class="form-select" name="tipo_vehiculo" required>
                <option value="" disabled selected>Seleccione...</option>
                <option value="bicicleta">Bicicleta</option>
                <option value="vehiculo_electrico">Vehículo eléctrico</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Placa / identificación</label>
              <input type="text" class="form-control" name="placa_identificacion">
            </div>
          </div>
          <input type="hidden" name="tipo_registro" value="repartidor">
          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: EDITAR (campos comunes + condicionales según rol) -->
<div class="modal fade" id="modalEditar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h1 class="modal-title fs-5"><i class="bi bi-pencil-square me-2"></i>Editar Usuario</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditar">
          <input type="hidden" name="id_usuario" id="id_edit">
          <input type="hidden" name="rol" id="rol_edit">

          <div class="mb-3">
            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre_completo" id="nombre_edit" minlength="5" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" id="email_edit" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" class="form-control" name="clave" id="clave_edit" placeholder="Dejar vacío para no cambiarla">
            <div class="form-text">Solo completar si se desea cambiar. Mín. 8 car., 1 mayúscula, 1 número, 1 especial.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Estado <span class="text-danger">*</span></label>
            <select class="form-select" name="activo" id="activo_edit" required>
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>

          <div id="camposComercioEdit" class="d-none">
            <hr>
            <div class="mb-3">
              <label class="form-label">Razón social</label>
              <input type="text" class="form-control" name="razon_social" id="razon_social_edit">
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">RUC</label>
                <input type="text" class="form-control" name="ruc" id="ruc_edit">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Rubro</label>
                <input type="text" class="form-control" name="rubro" id="rubro_edit">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Dirección fiscal</label>
              <input type="text" class="form-control" name="direccion_fiscal" id="direccion_fiscal_edit">
            </div>
          </div>

          <div id="camposRepartidorEdit" class="d-none">
            <hr>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Tipo de vehículo</label>
                <select class="form-select" name="tipo_vehiculo" id="tipo_vehiculo_edit">
                  <option value="bicicleta">Bicicleta</option>
                  <option value="vehiculo_electrico">Vehículo eléctrico</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Placa / identificación</label>
                <input type="text" class="form-control" name="placa_identificacion" id="placa_edit">
              </div>
            </div>
          </div>

          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Actualizar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: ELIMINAR -->
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
        <form id="formEliminar">
          <input type="hidden" name="id_usuario" id="id_delete">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control bg-light" id="nombre_delete" readonly>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label">Email</label>
              <input type="text" class="form-control bg-light" id="email_delete" readonly>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Rol</label>
              <input type="text" class="form-control bg-light" id="rol_delete" readonly>
            </div>
          </div>
          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger"><i class="bi bi-trash-fill"></i> Eliminar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

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

<script>
$(document).ready(function () {
    var dtConfig = {
        language: { url: "<?php echo $ruta; ?>dt/es-ES.json" },
        responsive: true,
        dom: 'Bfrtip',
        pageLength: 10,
        columnDefs: [{ orderable: false, targets: 5 }],
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i>',
                titleAttr: 'Exportar a Excel',
                className: 'btn btn-success btn-sm',
                title: 'Reporte de Usuarios - EcoRuta',
                filename: 'Reporte_Usuarios_EcoRuta',
                exportOptions: { columns: [0, 1, 2, 3, 4] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf"></i>',
                titleAttr: 'Exportar a PDF',
                className: 'btn btn-danger btn-sm',
                filename: 'Reporte_Usuarios_EcoRuta',
                exportOptions: { columns: [0, 1, 2, 3, 4] },
                customize: function (doc) {
                    doc.content.splice(0, 0, {
                        text: 'ECORUTA', style: 'header', alignment: 'center'
                    }, {
                        text: 'Plataforma de Gestión Logística Verde', alignment: 'center', margin: [0, 0, 0, 4], fontSize: 9
                    }, {
                        text: 'Reporte de Usuarios — ' + new Date().toLocaleDateString(),
                        alignment: 'center', margin: [0, 0, 0, 12], fontSize: 9, color: '#5f6f67'
                    });
                    doc.styles.header = { fontSize: 16, bold: true, color: '#16724d' };
                }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer"></i>',
                titleAttr: 'Imprimir',
                className: 'btn btn-info btn-sm',
                title: '',
                exportOptions: { columns: [0, 1, 2, 3, 4] },
                customize: function (win) {
                    var fecha = new Date().toLocaleDateString();
                    var tablaHTML = $(win.document.body).find('table').prop('outerHTML');
                    win.document.body.innerHTML = `
                        <div style="font-family: Arial, sans-serif; padding: 10px;">
                            <h2 style="color:#16724d; margin-bottom:2px;">ECORUTA</h2>
                            <p style="margin:0 0 4px; font-size:12px;">Plataforma de Gestión Logística Verde</p>
                            <p style="margin:0 0 12px; font-size:11px; color:#5f6f67;">Reporte de Usuarios — ${fecha}</p>
                            ${tablaHTML}
                        </div>`;
                    $(win.document.body).find('table').css({ width: '100%', borderCollapse: 'collapse' });
                    $(win.document.body).find('th, td').css({ border: '1px solid #333', padding: '4px 6px', fontSize: '11px' });
                }
            }
        ]
    };

    ['todos', 'administrador', 'comerciante', 'repartidor'].forEach(function (tab) {
        var dt = $('#tbl_' + tab).DataTable(dtConfig);
        $('#cnt-' + tab).text(dt.rows().count());
    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tab = $(e.target).attr('id').replace('tab-', '').replace('-btn', '');
        $('#tbl_' + tab).DataTable().columns.adjust().draw();
    });
});

function validarPassword(clave) {
    var errores = [];
    if (clave.length < 8) errores.push("mínimo 8 caracteres");
    if (!/[A-Z]/.test(clave)) errores.push("al menos una mayúscula");
    if (!/[0-9]/.test(clave)) errores.push("al menos un número");
    if (!/[^A-Za-z0-9]/.test(clave)) errores.push("al menos un carácter especial");
    return errores;
}

function enviarFormulario(formId, url, callback) {
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

['modalAdmin', 'modalComercio', 'modalRepartidor'].forEach(function (id) {
    document.getElementById(id).addEventListener('show.bs.modal', function () {
        this.querySelector('form').reset();
    });
});

document.getElementById('formAdmin').addEventListener('submit', function (e) {
    e.preventDefault();
    var clave = document.getElementById('admin_clave').value;
    var confirma = document.getElementById('admin_confirmar').value;
    var errPass = validarPassword(clave);
    if (errPass.length) { alertify.error("Contraseña insegura: " + errPass.join(', ') + "."); return; }
    if (clave !== confirma) { alertify.error("Las contraseñas no coinciden."); return; }
    enviarFormulario('formAdmin', '<?php echo $ruta; ?>api/usuarios_guardar.php');
});

document.getElementById('formComercio').addEventListener('submit', function (e) {
    e.preventDefault();
    var clave = document.getElementById('comercio_clave').value;
    var confirma = document.getElementById('comercio_confirmar').value;
    var errPass = validarPassword(clave);
    if (errPass.length) { alertify.error("Contraseña insegura: " + errPass.join(', ') + "."); return; }
    if (clave !== confirma) { alertify.error("Las contraseñas no coinciden."); return; }
    enviarFormulario('formComercio', '<?php echo $ruta; ?>api/usuarios_guardar.php');
});

document.getElementById('formRepartidor').addEventListener('submit', function (e) {
    e.preventDefault();
    var clave = document.getElementById('repartidor_clave').value;
    var confirma = document.getElementById('repartidor_confirmar').value;
    var errPass = validarPassword(clave);
    if (errPass.length) { alertify.error("Contraseña insegura: " + errPass.join(', ') + "."); return; }
    if (clave !== confirma) { alertify.error("Las contraseñas no coinciden."); return; }
    enviarFormulario('formRepartidor', '<?php echo $ruta; ?>api/usuarios_guardar.php');
});

// LLENAR MODAL DE EDICIÓN (mostrando/ocultando campos según el rol)
document.addEventListener('click', function (e) {
    var boton = e.target.closest('.btnEditar');
    if (!boton) return;

    document.getElementById('id_edit').value = boton.getAttribute('data-id');
    document.getElementById('nombre_edit').value = boton.getAttribute('data-nombre');
    document.getElementById('email_edit').value = boton.getAttribute('data-email');
    document.getElementById('clave_edit').value = '';
    document.getElementById('activo_edit').value = boton.getAttribute('data-activo');

    var rol = boton.getAttribute('data-rol');
    document.getElementById('rol_edit').value = rol;

    var camposComercio = document.getElementById('camposComercioEdit');
    var camposRepartidor = document.getElementById('camposRepartidorEdit');
    camposComercio.classList.toggle('d-none', rol !== 'comerciante');
    camposRepartidor.classList.toggle('d-none', rol !== 'repartidor');

    if (rol === 'comerciante') {
        document.getElementById('razon_social_edit').value = boton.getAttribute('data-razon-social');
        document.getElementById('ruc_edit').value = boton.getAttribute('data-ruc');
        document.getElementById('rubro_edit').value = boton.getAttribute('data-rubro');
        document.getElementById('direccion_fiscal_edit').value = boton.getAttribute('data-direccion-fiscal');
    }
    if (rol === 'repartidor') {
        document.getElementById('tipo_vehiculo_edit').value = boton.getAttribute('data-tipo-vehiculo');
        document.getElementById('placa_edit').value = boton.getAttribute('data-placa');
    }
});

document.getElementById('formEditar').addEventListener('submit', function (e) {
    e.preventDefault();
    var clave = document.getElementById('clave_edit').value;
    if (clave !== '') {
        var errPass = validarPassword(clave);
        if (errPass.length) { alertify.error("Contraseña insegura: " + errPass.join(', ') + "."); return; }
    }
    enviarFormulario('formEditar', '<?php echo $ruta; ?>api/usuarios_actualizar.php');
});

// LLENAR Y CONFIRMAR ELIMINACIÓN
document.addEventListener('click', function (e) {
    var boton = e.target.closest('.btnEliminar');
    if (!boton) return;

    var idAEliminar = parseInt(boton.getAttribute('data-id'));
    if (idAEliminar === parseInt(ID_USUARIO_SESION)) {
        alertify.error("No podés eliminar tu propio usuario mientras estás conectado.");
        return;
    }

    document.getElementById('id_delete').value = idAEliminar;
    document.getElementById('nombre_delete').value = boton.getAttribute('data-nombre');
    document.getElementById('email_delete').value = boton.getAttribute('data-email');
    document.getElementById('rol_delete').value = boton.getAttribute('data-rol');

    var modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
    modal.show();
});

document.getElementById('formEliminar').addEventListener('submit', function (e) {
    e.preventDefault();
    var formData = new FormData(this);

    alertify.confirm("Eliminar Usuario", "¿Está seguro que desea eliminar este usuario permanentemente?",
        function () {
            fetch('<?php echo $ruta; ?>api/usuarios_eliminar.php', { method: 'POST', body: formData })
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
        function () { alertify.error('Cancelado'); }
    ).set('labels', { ok: 'Sí, Eliminar', cancel: 'Cancelar' });
});
</script>
