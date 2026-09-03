<?php
/**
 * API: Cotizador Ecológico de Tarifas y CO2
 * ==========================================
 * Calcula la tarifa y la estimación de emisiones de CO2 ahorradas
 * en función de la distancia y el tipo de vehículo ecológico.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../servicios/verificar_sesion.php';
verificar_rol(['comerciante', 'administrador']);

require_once __DIR__ . '/../servicios/conexion.php';

$distanciaKm = (float) ($_GET['distancia_km'] ?? $_POST['distancia_km'] ?? 0);
$tipoVehiculo = trim($_GET['tipo_vehiculo'] ?? $_POST['tipo_vehiculo'] ?? '');

if ($distanciaKm <= 0) {
    echo json_encode([
        'status' => false,
        'msg' => 'La distancia debe ser mayor a 0 km.'
    ]);
    exit;
}

$conexion = conectar_bd();

try {
    if ($tipoVehiculo !== '' && in_array($tipoVehiculo, ['bicicleta', 'vehiculo_electrico'], true)) {
        // Cotización para un vehículo específico
        $stmt = $conexion->prepare(
            'SELECT id_tarifa, tipo_vehiculo, distancia_min_km, distancia_max_km,
                    precio_base, precio_por_km, factor_co2_kg_por_km
             FROM tarifas_ecologicas
             WHERE tipo_vehiculo = ?
               AND (? >= distancia_min_km AND ? <= distancia_max_km)
             ORDER BY distancia_max_km ASC LIMIT 1'
        );
        $stmt->bind_param('sdd', $tipoVehiculo, $distanciaKm, $distanciaKm);
        $stmt->execute();
        $tarifa = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Si excede el rango máximo, tomar la tarifa máxima del vehículo
        if (!$tarifa) {
            $stmtMax = $conexion->prepare(
                'SELECT id_tarifa, tipo_vehiculo, distancia_min_km, distancia_max_km,
                        precio_base, precio_por_km, factor_co2_kg_por_km
                 FROM tarifas_ecologicas
                 WHERE tipo_vehiculo = ?
                 ORDER BY distancia_max_km DESC LIMIT 1'
            );
            $stmtMax->bind_param('s', $tipoVehiculo);
            $stmtMax->execute();
            $tarifa = $stmtMax->get_result()->fetch_assoc();
            $stmtMax->close();
        }

        if ($tarifa) {
            $precioBase = (float) $tarifa['precio_base'];
            $precioKm = (float) $tarifa['precio_por_km'];
            $factorCO2 = (float) $tarifa['factor_co2_kg_por_km'];

            $costoTotal = $precioBase + ($distanciaKm * $precioKm);
            $co2Ahorrado = $distanciaKm * $factorCO2;

            echo json_encode([
                'status' => true,
                'data' => [
                    'id_tarifa'                => (int) $tarifa['id_tarifa'],
                    'tipo_vehiculo'            => $tarifa['tipo_vehiculo'],
                    'distancia_km'             => round($distanciaKm, 2),
                    'precio_base'              => $precioBase,
                    'precio_por_km'            => $precioKm,
                    'tarifa_calculada'         => round($costoTotal, 0),
                    'tarifa_formateada'        => number_format($costoTotal, 0, ',', '.') . ' Gs.',
                    'co2_estimado_ahorrado_kg' => round($co2Ahorrado, 3),
                    'co2_formateado'           => number_format($co2Ahorrado, 3, ',', '.') . ' kg de CO₂'
                ]
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'msg' => 'No se encontró una tarifa ecológica aplicable para esta distancia.'
            ]);
        }
    } else {
        // Comparador: Devuelve cotización para Bicicleta y Vehículo Eléctrico
        $stmtTodos = $conexion->query(
            'SELECT id_tarifa, tipo_vehiculo, distancia_min_km, distancia_max_km,
                    precio_base, precio_por_km, factor_co2_kg_por_km
             FROM tarifas_ecologicas
             ORDER BY tipo_vehiculo, distancia_min_km ASC'
        );
        $todasTarifas = $stmtTodos->fetch_all(MYSQLI_ASSOC);
        $stmtTodos->close();

        $opciones = [];
        $vehiculos = ['bicicleta', 'vehiculo_electrico'];

        foreach ($vehiculos as $v) {
            $tarifaSeleccionada = null;
            $tarifaMax = null;

            foreach ($todasTarifas as $t) {
                if ($t['tipo_vehiculo'] === $v) {
                    $tarifaMax = $t;
                    if ($distanciaKm >= (float) $t['distancia_min_km'] && $distanciaKm <= (float) $t['distancia_max_km']) {
                        $tarifaSeleccionada = $t;
                        break;
                    }
                }
            }

            $tarifaFinal = $tarifaSeleccionada ?: $tarifaMax;
            if ($tarifaFinal) {
                $precioBase = (float) $tarifaFinal['precio_base'];
                $precioKm = (float) $tarifaFinal['precio_por_km'];
                $factorCO2 = (float) $tarifaFinal['factor_co2_kg_por_km'];

                $costoTotal = $precioBase + ($distanciaKm * $precioKm);
                $co2Ahorrado = $distanciaKm * $factorCO2;

                $opciones[$v] = [
                    'id_tarifa'                => (int) $tarifaFinal['id_tarifa'],
                    'tipo_vehiculo'            => $v,
                    'distancia_km'             => round($distanciaKm, 2),
                    'precio_base'              => $precioBase,
                    'precio_por_km'            => $precioKm,
                    'tarifa_calculada'         => round($costoTotal, 0),
                    'tarifa_formateada'        => number_format($costoTotal, 0, ',', '.') . ' Gs.',
                    'co2_estimado_ahorrado_kg' => round($co2Ahorrado, 3),
                    'co2_formateado'           => number_format($co2Ahorrado, 3, ',', '.') . ' kg de CO₂'
                ];
            }
        }

        echo json_encode([
            'status' => true,
            'data' => $opciones
        ]);
    }

    $conexion->close();

} catch (Exception $e) {
    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->close();
    }
    error_log("Error en pedidos_calcular_tarifa: " . $e->getMessage());
    echo json_encode([
        'status' => false,
        'msg' => 'Error al calcular la tarifa ecológica.'
    ]);
}

