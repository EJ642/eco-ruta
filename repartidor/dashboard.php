<?php
require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['repartidor']);

require_once __DIR__ . '/../servicios/conexion.php';
$conexion = conectar_bd();

// Obtener id_repartidor a partir del usuario logueado
$consultaRepartidor = $conexion->prepare(
    'SELECT rp.id_repartidor, rp.tipo_vehiculo, rp.placa_identificacion, rp.disponible
     FROM repartidores rp
     WHERE rp.id_usuario = ? LIMIT 1'
);
$consultaRepartidor->bind_param('i', $_SESSION['usuario_id']);
$consultaRepartidor->execute();
$repartidor = $consultaRepartidor->get_result()->fetch_assoc();
$consultaRepartidor->close();

$stats = ['pendientes' => 0, 'en_camino' => 0, 'km_activos' => 0, 'co2_hoy' => 0, 'entregados_hoy' => 0];
$pedidos = [];
$turnoHoy = null;

if ($repartidor) {
    $idRepartidor = (int) $repartidor['id_repartidor'];

    // Turno de hoy
    $stmtTurno = $conexion->prepare(
        'SELECT id_turno, fecha, hora_inicio, hora_fin
         FROM turnos
         WHERE id_repartidor = ? AND fecha = CURDATE() LIMIT 1'
    );
    $stmtTurno->bind_param('i', $idRepartidor);
    $stmtTurno->execute();
    $turnoHoy = $stmtTurno->get_result()->fetch_assoc();
    $stmtTurno->close();

    // Tarjetas de resumen
    $consultaStats = $conexion->prepare(
        'SELECT
            (SELECT COUNT(*) FROM pedidos WHERE id_repartidor = ? AND estado = \'pendiente\') AS pendientes,
            (SELECT COUNT(*) FROM pedidos WHERE id_repartidor = ? AND estado = \'en_camino\') AS en_camino,
            (SELECT COUNT(*) FROM pedidos WHERE id_repartidor = ? AND estado = \'entregado\' AND DATE(fecha_actualizacion) = CURDATE()) AS entregados_hoy,
            (SELECT COALESCE(SUM(distancia_km), 0) FROM pedidos WHERE id_repartidor = ? AND estado IN (\'pendiente\',\'en_camino\')) AS km_activos,
            (SELECT COALESCE(SUM(co2_estimado_ahorrado_kg), 0) FROM pedidos WHERE id_repartidor = ? AND estado = \'entregado\' AND DATE(fecha_actualizacion) = CURDATE()) AS co2_hoy'
    );
    $consultaStats->bind_param('iiiii', $idRepartidor, $idRepartidor, $idRepartidor, $idRepartidor, $idRepartidor);
    $consultaStats->execute();
    $stats = $consultaStats->get_result()->fetch_assoc();
    $consultaStats->close();

    // Lista de pedidos asignados activos e históricos de hoy
    $consultaPedidos = $conexion->prepare(
        'SELECT p.id_pedido, p.descripcion_paquete, p.estado, p.distancia_km, p.peso_kg,
                p.tarifa_calculada, p.co2_estimado_ahorrado_kg, p.fecha_creacion,
                c.razon_social AS comercio,
                d.calle, d.numero, d.ciudad, d.referencia
         FROM pedidos p
         INNER JOIN comercios c ON c.id_comercio = p.id_comercio
         INNER JOIN direcciones d ON d.id_direccion = p.id_direccion_destino
         WHERE p.id_repartidor = ?
           AND (p.estado IN (\'pendiente\', \'en_camino\') OR (p.estado = \'entregado\' AND DATE(p.fecha_actualizacion) = CURDATE()))
         ORDER BY FIELD(p.estado, \'en_camino\', \'pendiente\', \'entregado\'), p.fecha_creacion ASC'
    );
    $consultaPedidos->bind_param('i', $idRepartidor);
    $consultaPedidos->execute();
    $pedidos = $consultaPedidos->get_result()->fetch_all(MYSQLI_ASSOC);
    $consultaPedidos->close();
}
$conexion->close();

$ruta = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="welcome mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <p class="eyebrow text-success mb-1">Panel del Repartidor</p>
        <h1 class="h2 mb-1">¡Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre'], ENT_QUOTES, 'UTF-8'); ?>!</h1>
        <p class="text-muted mb-0">
            Vehículo: <strong><?php echo ucfirst(str_replace('_', ' ', $repartidor['tipo_vehiculo'] ?? 'Bicicleta')); ?></strong>
            <?php if (!empty($repartidor['placa_identificacion'])): ?>
                (<?php echo htmlspecialchars($repartidor['placa_identificacion'], ENT_QUOTES, 'UTF-8'); ?>)
            <?php endif; ?>
            <?php if ($turnoHoy): ?>
                · Turno: <span class="badge bg-light text-dark border"><?php echo substr($turnoHoy['hora_inicio'], 0, 5); ?> - <?php echo substr($turnoHoy['hora_fin'], 0, 5); ?> hs</span>
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- TARJETAS DE RESUMEN -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-warning bg-opacity-10 text-warning fs-3">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Pendientes</div>
                    <div class="fs-3 fw-bold"><?php echo (int) $stats['pendientes']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary fs-3">
                    <i class="bi bi-bicycle"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold">En Camino</div>
                    <div class="fs-3 fw-bold"><?php echo (int) $stats['en_camino']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-info bg-opacity-10 text-info fs-3">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Km en Curso</div>
                    <div class="fs-3 fw-bold"><?php echo number_format((float) $stats['km_activos'], 1); ?> km</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 p-3 h-100 border-start border-success border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-success text-white fs-3">
                    <i class="bi bi-tree-fill"></i>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold">CO₂ Evitado Hoy</div>
                    <div class="fs-3 fw-bold text-success"><?php echo number_format((float) $stats['co2_hoy'], 2); ?> kg</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LISTA DE PEDIDOS ASIGNADOS -->
<h4 class="h5 fw-bold mb-3"><i class="bi bi-list-task text-success me-2"></i>Mis Entregas Asignadas</h4>

<div class="row g-3">
<?php if (empty($pedidos)): ?>
    <div class="col-12">
        <div class="card shadow-sm border-0 py-5 text-center">
            <div class="text-muted">
                <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                <h5>¡Todo al día!</h5>
                <p class="mb-0">No tienes entregas pendientes asignadas por el momento.</p>
            </div>
        </div>
    </div>
<?php else: foreach ($pedidos as $pedido):
    $badges = [
        'pendiente' => 'bg-warning text-dark',
        'en_camino' => 'bg-primary text-white',
        'entregado' => 'bg-success text-white'
    ];
    $labels = [
        'pendiente' => 'Pendiente',
        'en_camino' => 'En camino',
        'entregado' => 'Entregado'
    ];
?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card shadow-sm border-0 h-100 <?php echo $pedido['estado'] === 'en_camino' ? 'border-primary border-2' : ''; ?>">
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge <?php echo $badges[$pedido['estado']] ?? 'bg-secondary'; ?> px-2 py-1">
                            <?php echo $labels[$pedido['estado']] ?? $pedido['estado']; ?>
                        </span>
                        <span class="text-muted small fw-bold">#<?php echo $pedido['id_pedido']; ?></span>
                    </div>

                    <h5 class="card-title fw-bold text-dark mb-1">
                        <?php echo htmlspecialchars($pedido['descripcion_paquete'], ENT_QUOTES, 'UTF-8'); ?>
                    </h5>
                    <p class="text-muted small mb-2">Peso: <?php echo $pedido['peso_kg']; ?> kg · <?php echo $pedido['distancia_km']; ?> km</p>

                    <div class="p-2 bg-light rounded-3 mb-2 small">
                        <div class="text-muted"><i class="bi bi-shop text-success me-1"></i> <?php echo htmlspecialchars($pedido['comercio'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="fw-bold mt-1 text-truncate"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($pedido['calle'] . ' ' . $pedido['numero'] . ', ' . $pedido['ciudad'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php if ($pedido['referencia']): ?>
                            <div class="text-muted small mt-1"><i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($pedido['referencia'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="entrega.php?id=<?php echo $pedido['id_pedido']; ?>" class="btn <?php echo $pedido['estado'] === 'en_camino' ? 'btn-primary' : ($pedido['estado'] === 'entregado' ? 'btn-outline-success' : 'btn-success'); ?> w-100 fw-bold">
                        <?php if ($pedido['estado'] === 'pendiente'): ?>
                            <i class="bi bi-play-circle me-1"></i> Iniciar Entrega
                        <?php elseif ($pedido['estado'] === 'en_camino'): ?>
                            <i class="bi bi-check2-circle me-1"></i> Finalizar y Firmar
                        <?php else: ?>
                            <i class="bi bi-eye me-1"></i> Ver Constancia
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>