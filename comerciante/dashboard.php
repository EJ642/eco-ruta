<?php
require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['comerciante']);

require_once __DIR__ . '/../servicios/conexion.php';

$conexion = conectar_bd();
$idUsuario = (int) $_SESSION['usuario_id'];

// Obtener id_comercio y razón social
$stmtComercio = $conexion->prepare(
    'SELECT id_comercio, razon_social, ruc, direccion_fiscal
     FROM comercios
     WHERE id_usuario = ? LIMIT 1'
);
$stmtComercio->bind_param('i', $idUsuario);
$stmtComercio->execute();
$comercio = $stmtComercio->get_result()->fetch_assoc();
$stmtComercio->close();

$idComercio = (int) ($comercio['id_comercio'] ?? 0);

$stats = [
    'pedidos_hoy' => 0,
    'pendientes' => 0,
    'en_camino' => 0,
    'entregados' => 0,
    'co2_total' => 0
];
$pedidosRecientes = [];

if ($idComercio > 0) {
    // Estadísticas
    $stmtStats = $conexion->prepare(
        'SELECT
            (SELECT COUNT(*) FROM pedidos WHERE id_comercio = ? AND DATE(fecha_creacion) = CURDATE()) AS pedidos_hoy,
            (SELECT COUNT(*) FROM pedidos WHERE id_comercio = ? AND estado = \'pendiente\') AS pendientes,
            (SELECT COUNT(*) FROM pedidos WHERE id_comercio = ? AND estado = \'en_camino\') AS en_camino,
            (SELECT COUNT(*) FROM pedidos WHERE id_comercio = ? AND estado = \'entregado\') AS entregados,
            (SELECT COALESCE(SUM(co2_estimado_ahorrado_kg), 0) FROM pedidos WHERE id_comercio = ?) AS co2_total'
    );
    $stmtStats->bind_param('iiiii', $idComercio, $idComercio, $idComercio, $idComercio, $idComercio);
    $stmtStats->execute();
    $stats = $stmtStats->get_result()->fetch_assoc();
    $stmtStats->close();

    // 5 Pedidos recientes
    $stmtRecientes = $conexion->prepare(
        'SELECT p.id_pedido, p.descripcion_paquete, p.tarifa_calculada, p.co2_estimado_ahorrado_kg,
                p.estado, p.fecha_creacion,
                d.calle, d.numero, d.ciudad
         FROM pedidos p
         INNER JOIN direcciones d ON d.id_direccion = p.id_direccion_destino
         WHERE p.id_comercio = ?
         ORDER BY p.id_pedido DESC LIMIT 5'
    );
    $stmtRecientes->bind_param('i', $idComercio);
    $stmtRecientes->execute();
    $pedidosRecientes = $stmtRecientes->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtRecientes->close();
}

$conexion->close();

$ruta = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="welcome mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <p class="eyebrow text-success mb-1">Panel del Comerciante</p>
        <h1 class="h2 mb-1">¡Hola, <?php echo htmlspecialchars($comercio['razon_social'] ?? $_SESSION['usuario_nombre'], ENT_QUOTES, 'UTF-8'); ?>!</h1>
        <p class="text-muted mb-0">Gestiona tus despachos sostenibles y monitorea tu contribución ambiental en tiempo real.</p>
    </div>
    <div>
        <a href="nuevo_pedido.php" class="btn btn-success py-2 px-3 fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Nueva Solicitud de Entrega
        </a>
    </div>
</div>

<!-- ESTADÍSTICAS -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary fs-3">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Pedidos Hoy</div>
                    <div class="fs-3 fw-bold"><?php echo (int) $stats['pedidos_hoy']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-warning bg-opacity-10 text-warning fs-3">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold">En Proceso</div>
                    <div class="fs-3 fw-bold"><?php echo (int) $stats['pendientes'] + (int) $stats['en_camino']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-success bg-opacity-10 text-success fs-3">
                    <i class="bi bi-check2-all"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Entregados</div>
                    <div class="fs-3 fw-bold"><?php echo (int) $stats['entregados']; ?></div>
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
                    <div class="text-muted small text-uppercase fw-bold">CO₂ Evitado</div>
                    <div class="fs-3 fw-bold text-success"><?php echo number_format((float) $stats['co2_total'], 2, ',', '.'); ?> kg</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PEDIDOS RECIENTES -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history text-success me-2"></i>Envíos Recientes</h5>
        <a href="pedidos.php" class="btn btn-sm btn-outline-success">Ver todos los pedidos <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"># Pedido</th>
                        <th>Fecha</th>
                        <th>Destino</th>
                        <th>Paquete</th>
                        <th>Tarifa</th>
                        <th>CO₂ Ahorrado</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($pedidosRecientes)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                            Aún no has registrado pedidos. ¡Crea tu primer envío ecológico!
                        </td>
                    </tr>
                <?php else: foreach ($pedidosRecientes as $pr):
                    $badges = [
                        'pendiente' => 'bg-warning text-dark',
                        'en_camino' => 'bg-primary',
                        'entregado' => 'bg-success',
                        'cancelado' => 'bg-danger'
                    ];
                    $estadoLabels = [
                        'pendiente' => 'Pendiente',
                        'en_camino' => 'En camino',
                        'entregado' => 'Entregado',
                        'cancelado' => 'Cancelado'
                    ];
                ?>
                    <tr>
                        <td class="ps-3 fw-bold">#<?php echo $pr['id_pedido']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($pr['fecha_creacion'])); ?></td>
                        <td><?php echo htmlspecialchars($pr['calle'] . ' ' . $pr['numero'] . ', ' . $pr['ciudad'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($pr['descripcion_paquete'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="fw-bold"><?php echo number_format((float) $pr['tarifa_calculada'], 0, ',', '.'); ?> Gs.</td>
                        <td class="text-success"><i class="bi bi-tree-fill"></i> <?php echo number_format((float) $pr['co2_estimado_ahorrado_kg'], 3, ',', '.'); ?> kg</td>
                        <td>
                            <span class="badge <?php echo $badges[$pr['estado']] ?? 'bg-secondary'; ?>">
                                <?php echo $estadoLabels[$pr['estado']] ?? $pr['estado']; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
