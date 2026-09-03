<?php
/**
 * API: Guardar Nueva Solicitud de Pedido
 * ========================================
 * Registra las direcciones de origen y destino, calcula la tarifa ecológica,
 * la estimación de CO2 evitado y crea el pedido con estado 'pendiente'.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['comerciante', 'administrador']);

require_once __DIR__ . '/../servicios/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'msg' => 'Método no permitido.']);
    exit;
}

$conexion = conectar_bd();

try {
    // 1. Obtener id_comercio asociado
    $idUsuario = (int) $_SESSION['usuario_id'];
    $idComercio = 0;

    if ($_SESSION['rol'] === 'comerciante') {
        $stmtComercio = $conexion->prepare('SELECT id_comercio FROM comercios WHERE id_usuario = ? LIMIT 1');
        $stmtComercio->bind_param('i', $idUsuario);
        $stmtComercio->execute();
        $resCom = $stmtComercio->get_result()->fetch_assoc();
        $stmtComercio->close();

        if (!$resCom) {
            $conexion->close();
            echo json_encode(['status' => false, 'msg' => 'No se encontró el perfil de comercio asociado a tu cuenta.']);
            exit;
        }
        $idComercio = (int) $resCom['id_comercio'];
    } else {
        // Si es admin enviando la solicitud, puede especificar id_comercio
        $idComercio = (int) ($_POST['id_comercio'] ?? 1);
    }

    // 2. Datos del paquete
    $descripcionPaquete = trim($_POST['descripcion_paquete'] ?? '');
    $pesoKg = (float) ($_POST['peso_kg'] ?? 1.0);
    $tipoVehiculo = trim($_POST['tipo_vehiculo'] ?? 'bicicleta');
    $distanciaKm = (float) ($_POST['distancia_km'] ?? 0);

    if ($descripcionPaquete === '') {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'Debe ingresar la descripción del paquete.']);
        exit;
    }

    if ($distanciaKm <= 0) {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'La distancia calculada debe ser mayor a 0 km.']);
        exit;
    }

    if (!in_array($tipoVehiculo, ['bicicleta', 'vehiculo_electrico'], true)) {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'Seleccione un tipo de transporte ecológico válido.']);
        exit;
    }

    // 3. Datos de Origen
    $calleOrigen   = trim($_POST['calle_origen'] ?? 'Origen');
    $numeroOrigen  = trim($_POST['numero_origen'] ?? '');
    $ciudadOrigen  = trim($_POST['ciudad_origen'] ?? 'Asunción');
    $refOrigen     = trim($_POST['referencia_origen'] ?? '');
    $latOrigen     = !empty($_POST['latitud_origen']) ? (float) $_POST['latitud_origen'] : null;
    $lngOrigen     = !empty($_POST['longitud_origen']) ? (float) $_POST['longitud_origen'] : null;

    // 4. Datos de Destino
    $calleDestino  = trim($_POST['calle_destino'] ?? '');
    $numeroDestino = trim($_POST['numero_destino'] ?? '');
    $ciudadDestino = trim($_POST['ciudad_destino'] ?? 'Asunción');
    $refDestino    = trim($_POST['referencia_destino'] ?? '');
    $latDestino    = !empty($_POST['latitud_destino']) ? (float) $_POST['latitud_destino'] : null;
    $lngDestino    = !empty($_POST['longitud_destino']) ? (float) $_POST['longitud_destino'] : null;

    if ($calleDestino === '') {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'Debe especificar la calle de entrega (destino).']);
        exit;
    }

    // 5. Buscar tarifa ecológica y calcular costos
    $stmtTarifa = $conexion->prepare(
        'SELECT id_tarifa, precio_base, precio_por_km, factor_co2_kg_por_km
         FROM tarifas_ecologicas
         WHERE tipo_vehiculo = ?
           AND (? >= distancia_min_km AND ? <= distancia_max_km)
         ORDER BY distancia_max_km ASC LIMIT 1'
    );
    $stmtTarifa->bind_param('sdd', $tipoVehiculo, $distanciaKm, $distanciaKm);
    $stmtTarifa->execute();
    $tarifa = $stmtTarifa->get_result()->fetch_assoc();
    $stmtTarifa->close();

    if (!$tarifa) {
        $stmtMax = $conexion->prepare(
            'SELECT id_tarifa, precio_base, precio_por_km, factor_co2_kg_por_km
             FROM tarifas_ecologicas
             WHERE tipo_vehiculo = ?
             ORDER BY distancia_max_km DESC LIMIT 1'
        );
        $stmtMax->bind_param('s', $tipoVehiculo);
        $stmtMax->execute();
        $tarifa = $stmtMax->get_result()->fetch_assoc();
        $stmtMax->close();
    }

    if (!$tarifa) {
        $conexion->close();
        echo json_encode(['status' => false, 'msg' => 'No se encontró tarifa para la distancia indicada.']);
        exit;
    }

    $idTarifa = (int) $tarifa['id_tarifa'];
    $tarifaCalculada = (float) $tarifa['precio_base'] + ($distanciaKm * (float) $tarifa['precio_por_km']);
    $co2AhorradoKg = $distanciaKm * (float) $tarifa['factor_co2_kg_por_km'];

    // 6. Transacción para guardar direcciones y pedido
    $conexion->begin_transaction();

    // Insertar dirección de origen
    $stmtInsOrigen = $conexion->prepare(
        'INSERT INTO direcciones (calle, numero, ciudad, referencia, latitud, longitud)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmtInsOrigen->bind_param('ssssdd', $calleOrigen, $numeroOrigen, $ciudadOrigen, $refOrigen, $latOrigen, $lngOrigen);
    $stmtInsOrigen->execute();
    $idDirOrigen = $conexion->insert_id;
    $stmtInsOrigen->close();

    // Insertar dirección de destino
    $stmtInsDestino = $conexion->prepare(
        'INSERT INTO direcciones (calle, numero, ciudad, referencia, latitud, longitud)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmtInsDestino->bind_param('ssssdd', $calleDestino, $numeroDestino, $ciudadDestino, $refDestino, $latDestino, $lngDestino);
    $stmtInsDestino->execute();
    $idDirDestino = $conexion->insert_id;
    $stmtInsDestino->close();

    // Insertar pedido en estado 'pendiente'
    $stmtPedido = $conexion->prepare(
        'INSERT INTO pedidos (
            id_comercio, id_direccion_origen, id_direccion_destino,
            descripcion_paquete, peso_kg, id_tarifa, distancia_km,
            tarifa_calculada, co2_estimado_ahorrado_kg, estado
         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'pendiente\')'
    );
    $stmtPedido->bind_param(
        'iiisdiddd',
        $idComercio,
        $idDirOrigen,
        $idDirDestino,
        $descripcionPaquete,
        $pesoKg,
        $idTarifa,
        $distanciaKm,
        $tarifaCalculada,
        $co2AhorradoKg
    );

    if (!$stmtPedido->execute()) {
        throw new Exception('Error al insertar pedido: ' . $stmtPedido->error);
    }
    $idNuevoPedido = $conexion->insert_id;
    $stmtPedido->close();

    $conexion->commit();
    $conexion->close();

    echo json_encode([
        'status' => true,
        'msg' => '¡Solicitud de entrega creada con éxito! Pedido #' . $idNuevoPedido,
        'id_pedido' => $idNuevoPedido,
        'tarifa_calculada' => $tarifaCalculada,
        'co2_ahorrado' => $co2AhorradoKg
    ]);

} catch (Exception $e) {
    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->rollback();
        $conexion->close();
    }
    error_log('Error en pedidos_guardar: ' . $e->getMessage());
    echo json_encode([
        'status' => false,
        'msg' => 'Ocurrió un error al registrar el pedido: ' . $e->getMessage()
    ]);
}

