<?php
require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['comerciante']);

require_once __DIR__ . '/../servicios/conexion.php';

$conexion = conectar_bd();
$idUsuario = (int) $_SESSION['usuario_id'];

// Obtener datos del comercio logueado
$stmt = $conexion->prepare(
    'SELECT c.id_comercio, c.razon_social, c.ruc, c.direccion_fiscal, c.rubro
     FROM comercios c
     WHERE c.id_usuario = ? LIMIT 1'
);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$comercio = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conexion->close();

$razonSocial = $comercio['razon_social'] ?? $_SESSION['usuario_nombre'];
$direccionFiscal = $comercio['direccion_fiscal'] ?? 'Av. Mariscal López 1234, Asunción';

$ruta = '../';
include __DIR__ . '/../includes/header.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    .order-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .card-eco-form {
        background: #ffffff;
        border: 1px solid #dce5d8;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(22, 36, 31, 0.05);
        padding: 1.75rem;
        margin-bottom: 1.5rem;
    }
    .section-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 1.15rem;
        font-weight: 700;
        color: #16241f;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .vehicle-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .vehicle-card {
        border: 2px solid #dce5d8;
        border-radius: 12px;
        padding: 1rem;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
        background: #fdfefe;
    }
    .vehicle-card:hover {
        border-color: #16724d;
        background: #f0f7f2;
    }
    .vehicle-card.active {
        border-color: #16724d;
        background: #e3f3ec;
        box-shadow: 0 0 0 2px rgba(22, 114, 77, 0.2);
    }
    .vehicle-card i {
        font-size: 2rem;
        color: #16724d;
        display: block;
        margin-bottom: 0.4rem;
    }
    .vehicle-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #16241f;
    }
    .vehicle-desc {
        font-size: 0.75rem;
        color: #6d7d76;
        margin-top: 2px;
    }
    #map {
        height: 380px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid #dce5d8;
        z-index: 1;
    }
    .quote-box {
        background: linear-gradient(145deg, #16724d, #0e5036);
        color: #ffffff;
        border-radius: 14px;
        padding: 1.5rem;
        position: sticky;
        top: 20px;
        box-shadow: 0 10px 30px rgba(22, 114, 77, 0.25);
    }
    .quote-title {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #c7e86b;
        font-weight: 700;
    }
    .quote-price {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 2.3rem;
        font-weight: 700;
        margin: 0.4rem 0;
    }
    .co2-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(199, 232, 107, 0.2);
        border: 1px solid rgba(199, 232, 107, 0.35);
        color: #e5f7a6;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    .quote-details {
        border-top: 1px solid rgba(255, 255, 255, 0.15);
        padding-top: 0.85rem;
        font-size: 0.85rem;
        color: #cae0d2;
    }
    .quote-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.4rem;
    }
</style>

<div class="order-page-header">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-plus-circle-fill text-success me-2"></i>Nueva Solicitud de Entrega</h1>
        <p class="text-muted mb-0">Programa un despacho ecológico sin emisiones para tus clientes.</p>
    </div>
    <div>
        <a href="pedidos.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Pedidos
        </a>
    </div>
</div>

<form id="formNuevoPedido" autocomplete="off">
    <input type="hidden" name="tipo_vehiculo" id="tipo_vehiculo_input" value="bicicleta">
    <input type="hidden" name="latitud_origen" id="latitud_origen" value="-25.2900000">
    <input type="hidden" name="longitud_origen" id="longitud_origen" value="-57.5800000">
    <input type="hidden" name="latitud_destino" id="latitud_destino" value="-25.2820000">
    <input type="hidden" name="longitud_destino" id="longitud_destino" value="-57.6350000">

    <div class="row g-4">
        <!-- Columna Izquierda: Formulario -->
        <div class="col-lg-7">
            
            <!-- 1. ORIGEN (COMERCIO) -->
            <div class="card-eco-form">
                <div class="section-title">
                    <i class="bi bi-shop text-success"></i> 1. Punto de Retiro (Origen)
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Comercio Emisor</label>
                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($razonSocial, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>
                <div class="row g-2">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Calle / Avenida <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="calle_origen" id="calle_origen" value="<?php echo htmlspecialchars($direccionFiscal, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Número / Altura</label>
                        <input type="text" class="form-control" name="numero_origen" value="1234">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Ciudad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ciudad_origen" value="Asunción" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Referencia del local</label>
                        <input type="text" class="form-control" name="referencia_origen" placeholder="Ej: Frente a la plaza">
                    </div>
                </div>
            </div>

            <!-- 2. DESTINO (CLIENTE) -->
            <div class="card-eco-form">
                <div class="section-title">
                    <i class="bi bi-geo-alt-fill text-danger"></i> 2. Punto de Entrega (Destino)
                </div>
                <div class="row g-2">
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Calle y Número <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="calle_destino" id="calle_destino" placeholder="Ej: Calle Palma 567" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Número / Depto</label>
                        <input type="text" class="form-control" name="numero_destino" placeholder="Ej: 567 / Piso 3">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Ciudad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ciudad_destino" value="Asunción" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Referencia / Indicaciones</label>
                        <input type="text" class="form-control" name="referencia_destino" placeholder="Ej: Casa con rejas verdes">
                    </div>
                </div>
            </div>

            <!-- 3. DETALLES DEL PAQUETE Y TRANSPORTE -->
            <div class="card-eco-form">
                <div class="section-title">
                    <i class="bi bi-box-seam text-warning"></i> 3. Detalles del Paquete & Transporte
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción del Paquete <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="descripcion_paquete" placeholder="Ej: Caja de pasteles - 20 unidades, frágil" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Peso estimado (kg)</label>
                        <input type="number" step="0.1" min="0.1" max="50" class="form-control" name="peso_kg" id="peso_kg" value="2.5" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Distancia estimada (km) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" min="0.1" max="50" class="form-control" name="distancia_km" id="distancia_km" value="4.2" required>
                        <div class="form-text">Calculado automáticamente desde el mapa.</div>
                    </div>
                </div>

                <label class="form-label fw-bold mb-2">Seleccione Tipo de Transporte Ecológico:</label>
                <div class="vehicle-selector">
                    <div class="vehicle-card active" data-vehicle="bicicleta" id="card-bici">
                        <i class="bi bi-bicycle"></i>
                        <div class="vehicle-title">Bicicleta</div>
                        <div class="vehicle-desc">Ideal hasta 12 km · Cero emisión</div>
                    </div>
                    <div class="vehicle-card" data-vehicle="vehiculo_electrico" id="card-auto">
                        <i class="bi bi-ev-front-fill"></i>
                        <div class="vehicle-title">Vehículo Eléctrico</div>
                        <div class="vehicle-desc">Hasta 20 km · Cargas mayores</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Columna Derecha: Mapa & Cotizador -->
        <div class="col-lg-5">
            <div class="card-eco-form">
                <div class="section-title">
                    <i class="bi bi-map text-primary"></i> Mapa Interactivo GPS
                </div>
                <p class="small text-muted mb-2">Haz clic en el mapa para posicionar el <strong>Destino (Punto Verde)</strong> o arrastra los marcadores.</p>
                <div id="map"></div>
            </div>

            <!-- Cotizador en Vivo -->
            <div class="quote-box">
                <div class="quote-title">🌱 Cotización Ecológica</div>
                <div class="quote-price" id="lblTarifa">12.200 Gs.</div>
                
                <div class="co2-badge" id="lblCO2">
                    <i class="bi bi-tree-fill"></i> 0.756 kg de CO₂ evitado
                </div>

                <div class="quote-details">
                    <div class="quote-row">
                        <span>Transporte:</span>
                        <strong id="lblTipoVehiculo">Bicicleta</strong>
                    </div>
                    <div class="quote-row">
                        <span>Distancia:</span>
                        <strong id="lblDistancia">4.2 km</strong>
                    </div>
                    <div class="quote-row">
                        <span>Precio Base:</span>
                        <span id="lblPrecioBase">8.000 Gs.</span>
                    </div>
                    <div class="quote-row">
                        <span>Precio por Km:</span>
                        <span id="lblPrecioKm">1.000 Gs./km</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-light w-100 mt-4 fw-bold py-2" id="btnCrearPedido">
                    <i class="bi bi-check-circle-fill text-success me-1"></i> Confirmar y Crear Pedido
                </button>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Coordenadas iniciales (Asunción centro por defecto)
    var latOrigen = -25.2900000;
    var lngOrigen = -57.5800000;
    var latDestino = -25.2820000;
    var lngDestino = -57.6350000;

    // 2. Inicializar Mapa con OpenStreetMap
    var map = L.map('map').setView([latOrigen, lngOrigen], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Iconos personalizados
    var origenIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    var destinoIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    var markerOrigen = L.marker([latOrigen, lngOrigen], { draggable: true, icon: origenIcon }).addTo(map)
        .bindPopup("<b>🏬 Origen (Comercio)</b>");

    var markerDestino = L.marker([latDestino, lngDestino], { draggable: true, icon: destinoIcon }).addTo(map)
        .bindPopup("<b>📍 Destino (Cliente)</b>");

    var polyline = L.polyline([[latOrigen, lngOrigen], [latDestino, lngDestino]], { color: '#16724d', weight: 4, dashArray: '8, 8' }).addTo(map);

    // Fórmula Haversine para calcular distancia en km entre dos coordenadas
    function calcularDistancia(lat1, lon1, lat2, lon2) {
        var R = 6371; // Radio de la tierra en km
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLon = (lon2 - lon1) * Math.PI / 180;
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function actualizarRutaYDistancia() {
        var posO = markerOrigen.getLatLng();
        var posD = markerDestino.getLatLng();

        document.getElementById('latitud_origen').value = posO.lat.toFixed(7);
        document.getElementById('longitud_origen').value = posO.lng.toFixed(7);
        document.getElementById('latitud_destino').value = posD.lat.toFixed(7);
        document.getElementById('longitud_destino').value = posD.lng.toFixed(7);

        polyline.setLatLngs([posO, posD]);

        var distKm = calcularDistancia(posO.lat, posO.lng, posD.lat, posD.lng);
        // Factor de ruta vial aproximada (1.3x de línea recta)
        var distEstimada = Math.max(0.5, Math.round(distKm * 1.25 * 10) / 10);
        document.getElementById('distancia_km').value = distEstimada;

        actualizarCotizacion();
    }

    markerOrigen.on('dragend', actualizarRutaYDistancia);
    markerDestino.on('dragend', actualizarRutaYDistancia);

    map.on('click', function(e) {
        markerDestino.setLatLng(e.latlng);
        actualizarRutaYDistancia();
    });

    // 3. Selección de Vehículo
    var vehicleCards = document.querySelectorAll('.vehicle-card');
    vehicleCards.forEach(function(card) {
        card.addEventListener('click', function() {
            vehicleCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            var vehiculo = this.getAttribute('data-vehicle');
            document.getElementById('tipo_vehiculo_input').value = vehiculo;
            actualizarCotizacion();
        });
    });

    // 4. Actualizar Cotización en Vivo
    function actualizarCotizacion() {
        var distancia = parseFloat(document.getElementById('distancia_km').value) || 0;
        var vehiculo = document.getElementById('tipo_vehiculo_input').value;

        if (distancia <= 0) return;

        fetch(`../api/pedidos_calcular_tarifa.php?distancia_km=${distancia}&tipo_vehiculo=${vehiculo}`)
            .then(r => r.json())
            .then(res => {
                if (res.status && res.data) {
                    var d = res.data;
                    document.getElementById('lblTarifa').textContent = d.tarifa_formateada;
                    document.getElementById('lblCO2').innerHTML = `<i class="bi bi-tree-fill"></i> ${d.co2_formateado} evitado`;
                    document.getElementById('lblTipoVehiculo').textContent = d.tipo_vehiculo === 'bicicleta' ? 'Bicicleta' : 'Vehículo Eléctrico';
                    document.getElementById('lblDistancia').textContent = d.distancia_km + ' km';
                    document.getElementById('lblPrecioBase').textContent = Number(d.precio_base).toLocaleString('es-PY') + ' Gs.';
                    document.getElementById('lblPrecioKm').textContent = Number(d.precio_por_km).toLocaleString('es-PY') + ' Gs./km';
                }
            })
            .catch(() => {});
    }

    document.getElementById('distancia_km').addEventListener('input', function() {
        actualizarCotizacion();
    });

    actualizarRutaYDistancia();

    // 5. Envío del Formulario
    document.getElementById('formNuevoPedido').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('btnCrearPedido');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Registrando pedido...';

        var formData = new FormData(this);

        fetch('../api/pedidos_guardar.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.status) {
                alertify.success(data.msg);
                setTimeout(() => {
                    window.location.href = 'pedidos.php';
                }, 1000);
            } else {
                alertify.error(data.msg);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> Confirmar y Crear Pedido';
            }
        })
        .catch(() => {
            alertify.error('Error de comunicación con el servidor.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> Confirmar y Crear Pedido';
        });
    });
});
</script>

