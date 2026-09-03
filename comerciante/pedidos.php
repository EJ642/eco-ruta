<?php
require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['comerciante']);

require_once __DIR__ . '/../servicios/conexion.php';

$conexion = conectar_bd();
$idUsuario = (int) $_SESSION['usuario_id'];

// Obtener pedidos del comercio logueado
$stmt = $conexion->prepare(
    'SELECT p.id_pedido, p.descripcion_paquete, p.peso_kg, p.distancia_km,
            p.tarifa_calculada, p.co2_estimado_ahorrado_kg, p.estado, p.fecha_creacion,
            te.tipo_vehiculo,
            d_dest.calle AS dest_calle, d_dest.numero AS dest_numero, d_dest.ciudad AS dest_ciudad, d_dest.referencia AS dest_ref,
            d_dest.latitud AS dest_lat, d_dest.longitud AS dest_lng,
            d_orig.calle AS orig_calle, d_orig.numero AS orig_numero, d_orig.ciudad AS orig_ciudad,
            d_orig.latitud AS orig_lat, d_orig.longitud AS orig_lng,
            u_rep.nombre_completo AS nombre_repartidor, u_rep.telefono AS tel_repartidor,
            ce.tipo_confirmacion, ce.nota_observacion, ce.fecha_confirmacion, ce.evidencia
     FROM pedidos p
     INNER JOIN comercios c ON c.id_comercio = p.id_comercio
     INNER JOIN direcciones d_dest ON d_dest.id_direccion = p.id_direccion_destino
     INNER JOIN direcciones d_orig ON d_orig.id_direccion = p.id_direccion_origen
     LEFT JOIN tarifas_ecologicas te ON te.id_tarifa = p.id_tarifa
     LEFT JOIN repartidores rp ON rp.id_repartidor = p.id_repartidor
     LEFT JOIN usuarios u_rep ON u_rep.id_usuario = rp.id_usuario
     LEFT JOIN confirmaciones_entrega ce ON ce.id_pedido = p.id_pedido
     WHERE c.id_usuario = ?
     ORDER BY p.id_pedido DESC'
);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conexion->close();

$ruta = '../';
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    .badge-estado {
        font-size: 0.8rem;
        padding: 0.4em 0.75em;
        border-radius: 6px;
        font-weight: 600;
    }
    .badge-pendiente { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-en_camino { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .badge-entregado { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-cancelado { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .co2-tag {
        color: #16724d;
        font-weight: 700;
        font-size: 0.85rem;
    }
    #mapDetalle {
        height: 250px;
        width: 100%;
        border-radius: 8px;
        border: 1px solid #dce5d8;
    }
</style>

<!-- ENCABEZADO -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0">
            <i class="bi bi-box-seam-fill me-2 text-success"></i>Mis Solicitudes de Entrega
        </h1>
        <p class="text-muted mb-0">Gestiona y rastrea el estado de tus envíos ecológicos en tiempo real.</p>
    </div>
    <div>
        <a href="nuevo_pedido.php" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> Nueva Solicitud de Entrega
        </a>
    </div>
</div>

<!-- TABS DE FILTRO -->
<ul class="nav nav-tabs mb-3" id="tabPedidos" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="tab-todos-btn" data-bs-toggle="tab" data-bs-target="#tab-todos" type="button">
            <i class="bi bi-list-ul"></i> Todos <span class="badge bg-secondary ms-1" id="cnt-todos">0</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="tab-pendiente-btn" data-bs-toggle="tab" data-bs-target="#tab-pendiente" type="button">
            <i class="bi bi-clock-history"></i> Pendientes <span class="badge bg-warning text-dark ms-1" id="cnt-pendiente">0</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="tab-en_camino-btn" data-bs-toggle="tab" data-bs-target="#tab-en_camino" type="button">
            <i class="bi bi-bicycle"></i> En camino <span class="badge bg-primary ms-1" id="cnt-en_camino">0</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="tab-entregado-btn" data-bs-toggle="tab" data-bs-target="#tab-entregado" type="button">
            <i class="bi bi-check2-circle"></i> Entregados <span class="badge bg-success ms-1" id="cnt-entregado">0</span>
        </button>
    </li>
</ul>

<!-- CONTENIDO DE TABS -->
<div class="tab-content">
    <?php
    $tabs = [
        'todos'      => ['estados' => ['pendiente', 'en_camino', 'entregado', 'cancelado'], 'activo' => true],
        'pendiente'  => ['estados' => ['pendiente'], 'activo' => false],
        'en_camino'  => ['estados' => ['en_camino'], 'activo' => false],
        'entregado'  => ['estados' => ['entregado'], 'activo' => false],
    ];

    foreach ($tabs as $tabKey => $tabInfo):
        $activoClase = $tabInfo['activo'] ? 'show active' : '';
    ?>
    <div class="tab-pane fade <?php echo $activoClase; ?>" id="tab-<?php echo $tabKey; ?>" role="tabpanel">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover tblPedidos" id="tbl_<?php echo $tabKey; ?>" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th># Pedido</th>
                                <th>Fecha</th>
                                <th>Destino</th>
                                <th>Paquete</th>
                                <th>Vehículo / Tarifa</th>
                                <th>CO₂ Evitado</th>
                                <th>Repartidor</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($pedidos): foreach ($pedidos as $p):
                            if (!in_array($p['estado'], $tabInfo['estados'], true)) continue;

                            $estadoLabels = [
                                'pendiente'  => 'Pendiente',
                                'en_camino'  => 'En camino',
                                'entregado'  => 'Entregado',
                                'cancelado'  => 'Cancelado'
                            ];
                            $vehiculoLabels = [
                                'bicicleta'          => '<i class="bi bi-bicycle text-success"></i> Bici',
                                'vehiculo_electrico' => '<i class="bi bi-ev-front-fill text-primary"></i> Eléctrico'
                            ];
                        ?>
                            <tr>
                                <td class="fw-bold">#<?php echo $p['id_pedido']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($p['fecha_creacion'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($p['dest_calle'] . ' ' . $p['dest_numero'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($p['dest_ciudad'], ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($p['descripcion_paquete'], ENT_QUOTES, 'UTF-8'); ?><br>
                                    <small class="text-muted"><?php echo $p['peso_kg']; ?> kg</small>
                                </td>
                                <td>
                                    <?php echo $vehiculoLabels[$p['tipo_vehiculo'] ?? 'bicicleta'] ?? 'Ecológico'; ?><br>
                                    <strong class="text-dark"><?php echo number_format((float)$p['tarifa_calculada'], 0, ',', '.'); ?> Gs.</strong>
                                </td>
                                <td>
                                    <span class="co2-tag">
                                        <i class="bi bi-tree-fill"></i> <?php echo number_format((float)$p['co2_estimado_ahorrado_kg'], 3, ',', '.'); ?> kg
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($p['nombre_repartidor'])): ?>
                                        <i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($p['nombre_repartidor'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Por asignar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-estado badge-<?php echo $p['estado']; ?>">
                                        <?php echo $estadoLabels[$p['estado']] ?? $p['estado']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-success btn-sm btnDetalle"
                                        data-bs-toggle="modal" data-bs-target="#modalDetallePedido"
                                        data-id="<?php echo $p['id_pedido']; ?>"
                                        data-paquete="<?php echo htmlspecialchars($p['descripcion_paquete'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-peso="<?php echo $p['peso_kg']; ?>"
                                        data-distancia="<?php echo $p['distancia_km']; ?>"
                                        data-tarifa="<?php echo number_format((float)$p['tarifa_calculada'], 0, ',', '.'); ?>"
                                        data-co2="<?php echo number_format((float)$p['co2_estimado_ahorrado_kg'], 3, ',', '.'); ?>"
                                        data-estado="<?php echo $p['estado']; ?>"
                                        data-origen="<?php echo htmlspecialchars($p['orig_calle'] . ' ' . $p['orig_numero'] . ', ' . $p['orig_ciudad'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-destino="<?php echo htmlspecialchars($p['dest_calle'] . ' ' . $p['dest_numero'] . ', ' . $p['dest_ciudad'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-ref-destino="<?php echo htmlspecialchars($p['dest_ref'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-lat-orig="<?php echo $p['orig_lat'] ?? -25.29; ?>"
                                        data-lng-orig="<?php echo $p['orig_lng'] ?? -57.58; ?>"
                                        data-lat-dest="<?php echo $p['dest_lat'] ?? -25.282; ?>"
                                        data-lng-dest="<?php echo $p['dest_lng'] ?? -57.635; ?>"
                                        data-repartidor="<?php echo htmlspecialchars($p['nombre_repartidor'] ?? 'No asignado aún', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-tipo-conf="<?php echo htmlspecialchars($p['tipo_confirmacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-nota-conf="<?php echo htmlspecialchars($p['nota_observacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-fecha-conf="<?php echo !empty($p['fecha_confirmacion']) ? date('d/m/Y H:i', strtotime($p['fecha_confirmacion'])) : ''; ?>">
                                        <i class="bi bi-eye"></i> Detalle
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

<!-- MODAL: DETALLE DEL PEDIDO Y RUTA -->
<div class="modal fade" id="modalDetallePedido" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Detalle de Entrega <span id="lblModalPedidoId"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-7">
            <h6><i class="bi bi-info-circle text-success me-1"></i> Información del Envío</h6>
            <table class="table table-sm table-borderless">
                <tr><td class="text-muted" width="35%">Paquete:</td><td class="fw-bold" id="detPaquete"></td></tr>
                <tr><td class="text-muted">Origen:</td><td id="detOrigen"></td></tr>
                <tr><td class="text-muted">Destino:</td><td id="detDestino"></td></tr>
                <tr><td class="text-muted">Distancia:</td><td><span id="detDistancia"></span> km</td></tr>
                <tr><td class="text-muted">Tarifa:</td><td class="fw-bold text-success"><span id="detTarifa"></span> Gs.</td></tr>
                <tr><td class="text-muted">CO₂ Ahorrado:</td><td class="text-success"><i class="bi bi-tree-fill"></i> <span id="detCO2"></span> kg</td></tr>
                <tr><td class="text-muted">Repartidor:</td><td id="detRepartidor"></td></tr>
                <tr><td class="text-muted">Estado actual:</td><td><span id="detEstadoBadge"></span></td></tr>
            </table>

            <div id="seccionConfirmacion" class="alert alert-success mt-3 d-none">
                <h6 class="alert-heading mb-1"><i class="bi bi-check-circle-fill"></i> Constancia de Entrega</h6>
                <p class="mb-1 small"><strong>Método:</strong> <span id="detTipoConf"></span> | <strong>Fecha:</strong> <span id="detFechaConf"></span></p>
                <p class="mb-0 small"><strong>Nota / Observación:</strong> <span id="detNotaConf"></span></p>
            </div>
          </div>
          <div class="col-md-5">
            <h6><i class="bi bi-map text-primary me-1"></i> Mapa de Ruta</h6>
            <div id="mapDetalle"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- DataTables & Leaflet -->
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
$(document).ready(function () {
    var dtConfig = {
        language: { url: "<?php echo $ruta; ?>dt/es-ES.json" },
        responsive: true,
        dom: 'Bfrtip',
        pageLength: 10,
        order: [[0, 'desc']],
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Reporte de Pedidos - EcoRuta',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Reporte de Pedidos - EcoRuta',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer"></i> Imprimir',
                className: 'btn btn-info btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            }
        ]
    };

    ['todos', 'pendiente', 'en_camino', 'entregado'].forEach(function (tab) {
        var dt = $('#tbl_' + tab).DataTable(dtConfig);
        $('#cnt-' + tab).text(dt.rows().count());
    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tab = $(e.target).attr('id').replace('tab-', '').replace('-btn', '');
        $('#tbl_' + tab).DataTable().columns.adjust().draw();
    });
});

// MAPA DEL MODAL DETALLE
var mapDetalle = null;
var markerO = null;
var markerD = null;
var routeLine = null;

document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btnDetalle');
    if (!btn) return;

    var id = btn.getAttribute('data-id');
    document.getElementById('lblModalPedidoId').textContent = '#' + id;
    document.getElementById('detPaquete').textContent = btn.getAttribute('data-paquete') + ' (' + btn.getAttribute('data-peso') + ' kg)';
    document.getElementById('detOrigen').textContent = btn.getAttribute('data-origen');
    document.getElementById('detDestino').textContent = btn.getAttribute('data-destino');
    document.getElementById('detDistancia').textContent = btn.getAttribute('data-distancia');
    document.getElementById('detTarifa').textContent = btn.getAttribute('data-tarifa');
    document.getElementById('detCO2').textContent = btn.getAttribute('data-co2');
    document.getElementById('detRepartidor').textContent = btn.getAttribute('data-repartidor');

    var estado = btn.getAttribute('data-estado');
    document.getElementById('detEstadoBadge').className = 'badge badge-estado badge-' + estado;
    document.getElementById('detEstadoBadge').textContent = estado.toUpperCase();

    // Confirmación si está entregado
    var tipoConf = btn.getAttribute('data-tipo-conf');
    var secConf = document.getElementById('seccionConfirmacion');
    if (tipoConf) {
        secConf.classList.remove('d-none');
        document.getElementById('detTipoConf').textContent = tipoConf === 'firma_digital' ? 'Firma Digital' : 'Código QR';
        document.getElementById('detFechaConf').textContent = btn.getAttribute('data-fecha-conf') || '-';
        document.getElementById('detNotaConf').textContent = btn.getAttribute('data-nota-conf') || 'Sin observaciones';
    } else {
        secConf.classList.add('d-none');
    }

    // Coordenadas
    var latO = parseFloat(btn.getAttribute('data-lat-orig')) || -25.29;
    var lngO = parseFloat(btn.getAttribute('data-lng-orig')) || -57.58;
    var latD = parseFloat(btn.getAttribute('data-lat-dest')) || -25.282;
    var lngD = parseFloat(btn.getAttribute('data-lng-dest')) || -57.635;

    var modalEl = document.getElementById('modalDetallePedido');
    modalEl.addEventListener('shown.bs.modal', function initMapOnce() {
        modalEl.removeEventListener('shown.bs.modal', initMapOnce);

        if (!mapDetalle) {
            mapDetalle = L.map('mapDetalle').setView([latO, lngO], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(mapDetalle);
        }

        if (markerO) mapDetalle.removeLayer(markerO);
        if (markerD) mapDetalle.removeLayer(markerD);
        if (routeLine) mapDetalle.removeLayer(routeLine);

        markerO = L.marker([latO, lngO]).addTo(mapDetalle).bindPopup("Origen");
        markerD = L.marker([latD, lngD]).addTo(mapDetalle).bindPopup("Destino");
        routeLine = L.polyline([[latO, lngO], [latD, lngD]], { color: '#16724d', weight: 4 }).addTo(mapDetalle);

        mapDetalle.invalidateSize();
        mapDetalle.fitBounds(routeLine.getBounds(), { padding: [20, 20] });
    });
});
</script>

