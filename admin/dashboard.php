<?php
require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['administrador']);

require_once __DIR__ . '/../servicios/conexion.php';

$conexion = conectar_bd();

// 1. Métricas Globales
$stmtStats = $conexion->query(
    'SELECT
        (SELECT COUNT(*) FROM usuarios WHERE activo = 1) AS total_usuarios,
        (SELECT COUNT(*) FROM comercios) AS total_comercios,
        (SELECT COUNT(*) FROM repartidores WHERE disponible = 1) AS repartidores_activos,
        (SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_creacion) = CURDATE()) AS entregas_hoy,
        (SELECT COUNT(*) FROM pedidos WHERE estado = \'pendiente\') AS pendientes_asignacion,
        (SELECT COALESCE(SUM(co2_estimado_ahorrado_kg), 0) FROM pedidos WHERE estado = \'entregado\') AS co2_total_ahorrado'
);
$stats = $stmtStats->fetch_assoc();
$stmtStats->close();

// 2. Métricas de la Vista `vw_metricas_diarias`
$stmtMetricas = $conexion->query(
    'SELECT fecha, total_entregas, co2_ahorrado_total_kg
     FROM vw_metricas_diarias
     ORDER BY fecha DESC LIMIT 7'
);
$metricasDiarias = $stmtMetricas ? $stmtMetricas->fetch_all(MYSQLI_ASSOC) : [];
if ($stmtMetricas) $stmtMetricas->close();

// 3. Lista de Repartidores para el Modal de Asignación
$stmtReps = $conexion->query(
    'SELECT rp.id_repartidor, u.nombre_completo, rp.tipo_vehiculo, rp.placa_identificacion
     FROM repartidores rp
     INNER JOIN usuarios u ON u.id_usuario = rp.id_usuario
     WHERE u.activo = 1
     ORDER BY u.nombre_completo ASC'
);
$listaRepartidores = $stmtReps ? $stmtReps->fetch_all(MYSQLI_ASSOC) : [];
if ($stmtReps) $stmtReps->close();

// 4. Últimos Pedidos Globales
$stmtPedidos = $conexion->query(
    'SELECT p.id_pedido, p.descripcion_paquete, p.distancia_km, p.tarifa_calculada,
            p.co2_estimado_ahorrado_kg, p.estado, p.fecha_creacion, p.id_repartidor,
            c.razon_social AS comercio,
            d.calle, d.numero, d.ciudad,
            u_rep.nombre_completo AS repartidor_nombre
     FROM pedidos p
     INNER JOIN comercios c ON c.id_comercio = p.id_comercio
     INNER JOIN direcciones d ON d.id_direccion = p.id_direccion_destino
     LEFT JOIN repartidores rp ON rp.id_repartidor = p.id_repartidor
     LEFT JOIN usuarios u_rep ON u_rep.id_usuario = rp.id_usuario
     ORDER BY p.id_pedido DESC LIMIT 10'
);
$pedidosRecientes = $stmtPedidos ? $stmtPedidos->fetch_all(MYSQLI_ASSOC) : [];
if ($stmtPedidos) $stmtPedidos->close();

$conexion->close();

$ruta = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="welcome mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <p class="eyebrow text-success mb-1">Panel de Control General</p>
        <h1 class="h2 mb-1">Operación EcoRuta</h1>
        <p class="text-muted mb-0">Monitoreo en tiempo real de la flota verde, comercios asociados y métricas de impacto ambiental.</p>
    </div>
    <div>
        <a href="usuarios.php" class="btn btn-outline-success">
            <i class="bi bi-people-fill me-1"></i> Administrar Usuarios
        </a>
    </div>
</div>

<!-- TARJETAS DE MÉTRICAS -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary fs-3">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Entregas Hoy</div>
                    <div class="fs-3 fw-bold"><?php echo (int) $stats['entregas_hoy']; ?></div>
                    <small class="text-muted"><?php echo (int) $stats['pendientes_asignacion']; ?> pendientes</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 p-3 h-100 border-start border-success border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-success text-white fs-3">
                    <i class="bi bi-tree-fill"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold">CO₂ Total Ahorrado</div>
                    <div class="fs-3 fw-bold text-success"><?php echo number_format((float) $stats['co2_total_ahorrado'], 2, ',', '.'); ?> kg</div>
                    <small class="text-success fw-bold">100% Cero Emisiones</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-success bg-opacity-10 text-success fs-3">
                    <i class="bi bi-shop"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Comercios</div>
                    <div class="fs-3 fw-bold"><?php echo (int) $stats['total_comercios']; ?></div>
                    <small class="text-muted">Asociados activos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-warning bg-opacity-10 text-warning fs-3">
                    <i class="bi bi-bicycle"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Flota Activa</div>
                    <div class="fs-3 fw-bold"><?php echo (int) $stats['repartidores_activos']; ?></div>
                    <small class="text-muted">Repartidores ecológicos</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABLA DE PEDIDOS RECIENTES Y ASIGNACIÓN -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0 fw-bold"><i class="bi bi-activity text-success me-2"></i>Monitoreo de Envíos en Tiempo Real</h5>
        <span class="badge bg-light text-dark border">Últimos pedidos registrados</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"># Pedido</th>
                        <th>Comercio</th>
                        <th>Destino</th>
                        <th>Paquete</th>
                        <th>Tarifa / CO₂</th>
                        <th>Repartidor</th>
                        <th>Estado</th>
                        <th class="text-end pe-3">Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($pedidosRecientes)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No hay pedidos registrados en el sistema.</td>
                    </tr>
                <?php else: foreach ($pedidosRecientes as $p):
                    $badges = [
                        'pendiente' => 'bg-warning text-dark',
                        'en_camino' => 'bg-primary text-white',
                        'entregado' => 'bg-success text-white',
                        'cancelado' => 'bg-danger text-white'
                    ];
                    $estadoLabels = [
                        'pendiente' => 'Pendiente',
                        'en_camino' => 'En camino',
                        'entregado' => 'Entregado',
                        'cancelado' => 'Cancelado'
                    ];
                ?>
                    <tr>
                        <td class="ps-3 fw-bold">#<?php echo $p['id_pedido']; ?></td>
                        <td><strong><?php echo htmlspecialchars($p['comercio'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td><?php echo htmlspecialchars($p['calle'] . ' ' . $p['numero'] . ', ' . $p['ciudad'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($p['descripcion_paquete'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <div><?php echo number_format((float)$p['tarifa_calculada'], 0, ',', '.'); ?> Gs.</div>
                            <small class="text-success"><i class="bi bi-tree-fill"></i> <?php echo $p['co2_estimado_ahorrado_kg']; ?> kg CO₂</small>
                        </td>
                        <td>
                            <?php if (!empty($p['repartidor_nombre'])): ?>
                                <i class="bi bi-person-badge text-success"></i> <?php echo htmlspecialchars($p['repartidor_nombre'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border">Sin Asignar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $badges[$p['estado']] ?? 'bg-secondary'; ?>">
                                <?php echo $estadoLabels[$p['estado']] ?? $p['estado']; ?>
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <?php if (empty($p['id_repartidor']) || $p['estado'] === 'pendiente'): ?>
                                <button type="button" class="btn btn-sm btn-outline-success btnAsignar"
                                    data-bs-toggle="modal" data-bs-target="#modalAsignar"
                                    data-id="<?php echo $p['id_pedido']; ?>"
                                    data-paquete="<?php echo htmlspecialchars($p['descripcion_paquete'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-comercio="<?php echo htmlspecialchars($p['comercio'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="bi bi-person-plus-fill"></i> Asignar
                                </button>
                            <?php else: ?>
                                <span class="text-muted small">Asignado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: ASIGNAR REPARTIDOR -->
<div class="modal fade" id="modalAsignar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-bicycle me-2"></i>Asignar Repartidor a Pedido <span id="lblAsignarPedidoId"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAsignarRepartidor">
            <input type="hidden" name="id_pedido" id="id_pedido_asignar">

            <div class="mb-3 p-2 bg-light rounded">
                <div class="small text-muted">Comercio: <strong id="lblAsignarComercio"></strong></div>
                <div class="small text-muted">Paquete: <strong id="lblAsignarPaquete"></strong></div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Seleccionar Repartidor Disponible:</label>
                <select class="form-select" name="id_repartidor" required>
                    <option value="" disabled selected>Seleccione un chofer de la flota...</option>
                    <?php foreach ($listaRepartidores as $rep): ?>
                        <option value="<?php echo $rep['id_repartidor']; ?>">
                            <?php echo htmlspecialchars($rep['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?> 
                            (<?php echo ucfirst(str_replace('_', ' ', $rep['tipo_vehiculo'])); ?><?php echo $rep['placa_identificacion'] ? ' · ' . $rep['placa_identificacion'] : ''; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success" id="btnGuardarAsignacion">
                    <i class="bi bi-check-circle"></i> Confirmar Asignación
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btnAsignar');
    if (!btn) return;

    var id = btn.getAttribute('data-id');
    document.getElementById('id_pedido_asignar').value = id;
    document.getElementById('lblAsignarPedidoId').textContent = '#' + id;
    document.getElementById('lblAsignarComercio').textContent = btn.getAttribute('data-comercio');
    document.getElementById('lblAsignarPaquete').textContent = btn.getAttribute('data-paquete');
});

document.getElementById('formAsignarRepartidor').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btnGuardarAsignacion');
    btn.disabled = true;
    btn.innerHTML = 'Asignando...';

    var formData = new FormData(this);

    fetch('../api/pedidos_asignar_repartidor.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status) {
            alertify.success(data.msg);
            setTimeout(() => window.location.reload(), 800);
        } else {
            alertify.error(data.msg);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Confirmar Asignación';
        }
    })
    .catch(() => {
        alertify.error('Error de comunicación con el servidor.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Confirmar Asignación';
    });
});
</script>