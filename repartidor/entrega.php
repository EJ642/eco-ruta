<?php
require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['repartidor', 'administrador']);

require_once __DIR__ . '/../servicios/conexion.php';

$idPedido = (int) ($_GET['id'] ?? 0);

if ($idPedido <= 0) {
    header('Location: dashboard.php');
    exit;
}

$conexion = conectar_bd();
$idUsuario = (int) $_SESSION['usuario_id'];
$rol = $_SESSION['rol'];

$stmt = $conexion->prepare(
    'SELECT p.id_pedido, p.descripcion_paquete, p.peso_kg, p.distancia_km,
            p.tarifa_calculada, p.co2_estimado_ahorrado_kg, p.estado, p.fecha_creacion,
            c.razon_social AS comercio_nombre, c.ruc AS comercio_ruc, c.direccion_fiscal AS comercio_dir,
            u_com.telefono AS comercio_tel, u_com.email AS comercio_email,
            d_dest.calle AS dest_calle, d_dest.numero AS dest_numero, d_dest.ciudad AS dest_ciudad, d_dest.referencia AS dest_ref,
            d_dest.latitud AS dest_lat, d_dest.longitud AS dest_lng,
            d_orig.calle AS orig_calle, d_orig.numero AS orig_numero, d_orig.ciudad AS orig_ciudad,
            d_orig.latitud AS orig_lat, d_orig.longitud AS orig_lng,
            te.tipo_vehiculo,
            ce.tipo_confirmacion, ce.evidencia, ce.nota_observacion, ce.fecha_confirmacion,
            rp.id_usuario AS id_usuario_repartidor
     FROM pedidos p
     INNER JOIN comercios c ON c.id_comercio = p.id_comercio
     INNER JOIN usuarios u_com ON u_com.id_usuario = c.id_usuario
     INNER JOIN direcciones d_dest ON d_dest.id_direccion = p.id_direccion_destino
     INNER JOIN direcciones d_orig ON d_orig.id_direccion = p.id_direccion_origen
     LEFT JOIN tarifas_ecologicas te ON te.id_tarifa = p.id_tarifa
     LEFT JOIN confirmaciones_entrega ce ON ce.id_pedido = p.id_pedido
     LEFT JOIN repartidores rp ON rp.id_repartidor = p.id_repartidor
     WHERE p.id_pedido = ? LIMIT 1'
);
$stmt->bind_param('i', $idPedido);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conexion->close();

if (!$pedido) {
    header('Location: dashboard.php');
    exit;
}

$ruta = '../';
include __DIR__ . '/../includes/header.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    .entrega-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .card-entrega {
        background: #ffffff;
        border: 1px solid #dce5d8;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(22, 36, 31, 0.05);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .badge-estado-lg {
        font-size: 0.95rem;
        padding: 0.5em 1em;
        border-radius: 8px;
        font-weight: 700;
    }
    .badge-pendiente { background-color: #fef3c7; color: #92400e; }
    .badge-en_camino { background-color: #dbeafe; color: #1e40af; }
    .badge-entregado { background-color: #dcfce7; color: #166534; }
    .badge-cancelado { background-color: #fee2e2; color: #991b1b; }
    
    #mapEntrega {
        height: 280px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid #dce5d8;
        margin-bottom: 1rem;
    }
    .signature-pad-container {
        border: 2px dashed #b8ceb5;
        border-radius: 12px;
        background: #fbfdfa;
        position: relative;
        text-align: center;
        padding: 8px;
    }
    canvas#signatureCanvas {
        width: 100%;
        height: 180px;
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #dce5d8;
        touch-action: none;
        cursor: crosshair;
    }
    .qr-box {
        background: #f8faf9;
        border: 2px dashed #16724d;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
    }
</style>

<div class="entrega-header">
    <div>
        <span class="badge badge-estado-lg badge-<?php echo $pedido['estado']; ?> mb-2">
            <?php 
            $labels = ['pendiente' => 'Pendiente de inicio', 'en_camino' => 'En Camino al destino', 'entregado' => 'Entrega Completada'];
            echo $labels[$pedido['estado']] ?? ucfirst($pedido['estado']);
            ?>
        </span>
        <h1 class="h3 mb-0">Entrega #<?php echo $pedido['id_pedido']; ?></h1>
        <p class="text-muted mb-0">Orden solicitada por <strong><?php echo htmlspecialchars($pedido['comercio_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al panel
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Columna Izquierda: Información de Entrega & Mapa -->
    <div class="col-lg-7">
        
        <!-- Tarjeta de Direcciones & Contacto -->
        <div class="card-entrega">
            <h5 class="fw-bold mb-3 text-success"><i class="bi bi-geo-alt-fill me-2"></i>Ruta de Reparto</h5>
            
            <!-- Origen -->
            <div class="d-flex gap-3 mb-3 p-3 bg-light rounded-3">
                <div class="fs-3 text-success"><i class="bi bi-shop"></i></div>
                <div>
                    <strong class="d-block text-muted small text-uppercase">Punto de Retiro (Origen)</strong>
                    <div class="fw-bold"><?php echo htmlspecialchars($pedido['comercio_nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div><?php echo htmlspecialchars($pedido['orig_calle'] . ' ' . $pedido['orig_numero'] . ', ' . $pedido['orig_ciudad'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php if ($pedido['comercio_tel']): ?>
                        <small class="text-muted"><i class="bi bi-telephone"></i> Tel: <?php echo htmlspecialchars($pedido['comercio_tel'], ENT_QUOTES, 'UTF-8'); ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Destino -->
            <div class="d-flex gap-3 mb-3 p-3 rounded-3" style="background: #eef7f2; border-left: 4px solid #16724d;">
                <div class="fs-3 text-danger"><i class="bi bi-geo-fill"></i></div>
                <div>
                    <strong class="d-block text-success small text-uppercase fw-bold">Punto de Entrega (Destino)</strong>
                    <div class="fw-bold fs-5"><?php echo htmlspecialchars($pedido['dest_calle'] . ' ' . $pedido['dest_numero'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div><?php echo htmlspecialchars($pedido['dest_ciudad'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php if ($pedido['dest_ref']): ?>
                        <div class="mt-1 badge bg-white text-dark border">
                            <i class="bi bi-info-circle text-primary"></i> Ref: <?php echo htmlspecialchars($pedido['dest_ref'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mapa GPS -->
            <div id="mapEntrega"></div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <span class="badge bg-secondary"><i class="bi bi-pin-map"></i> <?php echo $pedido['distancia_km']; ?> km</span>
                    <span class="badge bg-success"><i class="bi bi-tree-fill"></i> <?php echo $pedido['co2_estimado_ahorrado_kg']; ?> kg CO₂ evitado</span>
                </div>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $pedido['dest_lat']; ?>,<?php echo $pedido['dest_lng']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-cursor-fill me-1"></i> Abrir en Google Maps GPS
                </a>
            </div>
        </div>

        <!-- Tarjeta de Paquete -->
        <div class="card-entrega">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-box-seam me-2 text-warning"></i>Detalle del Paquete</h5>
            <p class="fs-5 mb-1"><?php echo htmlspecialchars($pedido['descripcion_paquete'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-muted mb-0"><strong>Peso:</strong> <?php echo $pedido['peso_kg']; ?> kg | <strong>Transporte:</strong> <?php echo ucfirst(str_replace('_', ' ', $pedido['tipo_vehiculo'] ?? 'bicicleta')); ?></p>
        </div>

    </div>

    <!-- Columna Derecha: Acciones & Confirmación -->
    <div class="col-lg-5">
        
        <?php if ($pedido['estado'] === 'pendiente'): ?>
            <!-- PASO 1: Iniciar Recorrido -->
            <div class="card-entrega text-center py-4 border-primary">
                <div class="display-4 text-primary mb-3"><i class="bi bi-bicycle"></i></div>
                <h4 class="fw-bold">¿Listo para salir?</h4>
                <p class="text-muted">Retira el paquete del comercio y presiona el botón para avisar que vas en camino.</p>
                <button type="button" class="btn btn-primary btn-lg w-100 py-3 fw-bold" id="btnIniciarCamino">
                    <i class="bi bi-play-fill fs-4 align-middle"></i> Iniciar Entrega (En Camino)
                </button>
            </div>

        <?php elseif ($pedido['estado'] === 'en_camino'): ?>
            <!-- PASO 2: Confirmar Entrega con Firma o QR -->
            <div class="card-entrega">
                <h4 class="fw-bold mb-2 text-success"><i class="bi bi-check-circle-fill me-2"></i>Confirmar Entrega</h4>
                <p class="text-muted small mb-3">Solicita la firma o código QR al cliente como constancia de recepción.</p>

                <!-- Selector de método -->
                <ul class="nav nav-pills nav-fill mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active btn-sm" id="tab-firma-btn" data-bs-toggle="pill" data-bs-target="#tab-firma" type="button">
                            <i class="bi bi-pen"></i> Firma Digital
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn-sm" id="tab-qr-btn" data-bs-toggle="pill" data-bs-target="#tab-qr" type="button">
                            <i class="bi bi-qr-code"></i> Código QR
                        </button>
                    </li>
                </ul>

                <form id="formConfirmarEntrega">
                    <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                    <input type="hidden" name="nuevo_estado" value="entregado">
                    <input type="hidden" name="tipo_confirmacion" id="tipo_confirmacion_input" value="firma_digital">
                    <input type="hidden" name="evidencia" id="evidencia_input" value="">

                    <div class="tab-content mb-3">
                        <!-- Pestaña Firma Digital -->
                        <div class="tab-pane fade show active" id="tab-firma">
                            <div class="signature-pad-container">
                                <p class="small text-muted mb-1">Dibuja la firma del cliente en el recuadro:</p>
                                <canvas id="signatureCanvas"></canvas>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnLimpiarFirma">
                                    <i class="bi bi-eraser"></i> Limpiar firma
                                </button>
                            </div>
                        </div>

                        <!-- Pestaña Código QR -->
                        <div class="tab-pane fade" id="tab-qr">
                            <div class="qr-box">
                                <i class="bi bi-qr-code-scan fs-1 text-success d-block mb-2"></i>
                                <p class="small text-muted mb-2">Ingresa o escanea el código del comprobante del paquete:</p>
                                <input type="text" class="form-control text-center fw-bold" id="qr_input_manual" placeholder="Ej: QR-ECORUTA-<?php echo $pedido['id_pedido']; ?>" value="QR-ECORUTA-<?php echo $pedido['id_pedido']; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nota u Observación de Entrega (Opcional)</label>
                        <textarea class="form-control" name="nota_observacion" id="nota_observacion" rows="2" placeholder="Ej: Recibido por Juan Pérez en portería"></textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 py-3 fw-bold" id="btnConfirmarFinal">
                        <i class="bi bi-check2-all fs-4 align-middle"></i> Confirmar Entrega Realizada
                    </button>
                </form>
            </div>

        <?php elseif ($pedido['estado'] === 'entregado'): ?>
            <!-- RESUMEN DE ENTREGA COMPLETADA -->
            <div class="card-entrega bg-success bg-opacity-10 border-success">
                <div class="text-center py-3">
                    <i class="bi bi-patch-check-fill display-4 text-success mb-2 d-block"></i>
                    <h4 class="fw-bold text-success">¡Paquete Entregado!</h4>
                    <p class="text-muted mb-3">Esta entrega fue finalizada con éxito.</p>
                </div>
                
                <table class="table table-sm table-borderless small mb-0">
                    <tr><td class="text-muted">Fecha y hora:</td><td class="fw-bold"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_confirmacion'] ?? $pedido['fecha_creacion'])); ?></td></tr>
                    <tr><td class="text-muted">Método:</td><td><span class="badge bg-success"><?php echo $pedido['tipo_confirmacion'] === 'firma_digital' ? 'Firma Digital' : 'Código QR'; ?></span></td></tr>
                    <tr><td class="text-muted">Nota:</td><td><?php echo htmlspecialchars($pedido['nota_observacion'] ?: 'Sin observaciones', ENT_QUOTES, 'UTF-8'); ?></td></tr>
                </table>

                <?php if ($pedido['tipo_confirmacion'] === 'firma_digital' && !empty($pedido['evidencia'])): ?>
                    <div class="mt-3 p-2 bg-white rounded border text-center">
                        <small class="text-muted d-block mb-1">Constancia de Firma:</small>
                        <?php if (strpos($pedido['evidencia'], 'img/') === 0): ?>
                            <img src="<?php echo $ruta . $pedido['evidencia']; ?>" alt="Firma digital" style="max-height: 80px; max-width: 100%;">
                        <?php else: ?>
                            <div class="text-muted small"><?php echo htmlspecialchars($pedido['evidencia'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Coordenadas de la ruta
    var latO = <?php echo (float) ($pedido['orig_lat'] ?? -25.29); ?>;
    var lngO = <?php echo (float) ($pedido['orig_lng'] ?? -57.58); ?>;
    var latD = <?php echo (float) ($pedido['dest_lat'] ?? -25.282); ?>;
    var lngD = <?php echo (float) ($pedido['dest_lng'] ?? -57.635); ?>;

    var map = L.map('mapEntrega').setView([latO, lngO], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    var markerO = L.marker([latO, lngO]).addTo(map).bindPopup("<b>🏬 Retiro (Comercio)</b>");
    var markerD = L.marker([latD, lngD]).addTo(map).bindPopup("<b>📍 Entrega (Cliente)</b>");
    var routeLine = L.polyline([[latO, lngO], [latD, lngD]], { color: '#16724d', weight: 4 }).addTo(map);
    map.fitBounds(routeLine.getBounds(), { padding: [30, 30] });

    // 2. Acción: Iniciar Recorrido (En camino)
    var btnIniciar = document.getElementById('btnIniciarCamino');
    if (btnIniciar) {
        btnIniciar.addEventListener('click', function() {
            var formData = new FormData();
            formData.append('id_pedido', <?php echo $pedido['id_pedido']; ?>);
            formData.append('nuevo_estado', 'en_camino');

            btnIniciar.disabled = true;
            btnIniciar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Actualizando...';

            fetch('../api/pedidos_actualizar_estado.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.status) {
                    alertify.success(res.msg);
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    alertify.error(res.msg);
                    btnIniciar.disabled = false;
                }
            })
            .catch(() => {
                alertify.error('Error de comunicación.');
                btnIniciar.disabled = false;
            });
        });
    }

    // 3. Firma Digital (HTML5 Canvas)
    var canvas = document.getElementById('signatureCanvas');
    if (canvas) {
        var ctx = canvas.getContext('2d');
        var drawing = false;
        var hasDrawn = false;

        // Ajustar resolución del canvas
        function resizeCanvas() {
            var rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = 180;
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#16241f';
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            if (e.touches && e.touches[0]) {
                return {
                    x: e.touches[0].clientX - rect.left,
                    y: e.touches[0].clientY - rect.top
                };
            }
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }

        function startDrawing(e) {
            e.preventDefault();
            drawing = true;
            hasDrawn = true;
            var pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!drawing) return;
            e.preventDefault();
            var pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDrawing(e) {
            if (drawing) {
                ctx.closePath();
                drawing = false;
            }
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDrawing);

        document.getElementById('btnLimpiarFirma').addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
        });

        // Alternancia entre Firma y QR
        document.getElementById('tab-firma-btn').addEventListener('click', function() {
            document.getElementById('tipo_confirmacion_input').value = 'firma_digital';
        });
        document.getElementById('tab-qr-btn').addEventListener('click', function() {
            document.getElementById('tipo_confirmacion_input').value = 'codigo_qr';
        });

        // Envío de confirmación de entrega
        document.getElementById('formConfirmarEntrega').addEventListener('submit', function(e) {
            e.preventDefault();
            var tipo = document.getElementById('tipo_confirmacion_input').value;
            var evidencia = '';

            if (tipo === 'firma_digital') {
                if (!hasDrawn) {
                    alertify.error('Por favor, solicite al cliente que firme en el recuadro.');
                    return;
                }
                evidencia = canvas.toDataURL('image/png');
            } else {
                evidencia = document.getElementById('qr_input_manual').value.trim();
                if (!evidencia) {
                    alertify.error('Ingrese el código QR del comprobante.');
                    return;
                }
            }

            document.getElementById('evidencia_input').value = evidencia;

            var btn = document.getElementById('btnConfirmarFinal');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Registrando entrega...';

            var formData = new FormData(this);

            fetch('../api/pedidos_actualizar_estado.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.status) {
                    alertify.success(res.msg);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    alertify.error(res.msg);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check2-all fs-4 align-middle"></i> Confirmar Entrega Realizada';
                }
            })
            .catch(() => {
                alertify.error('Error al guardar confirmación.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check2-all fs-4 align-middle"></i> Confirmar Entrega Realizada';
            });
        });
    }
});
</script>

