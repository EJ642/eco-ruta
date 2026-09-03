<?php
/**
 * Conexión Centralizada a Base de Datos - EcoRuta
 * ===================================================
 * Configuración segura con MySQLi, soporte UTF-8 completo (utf8mb4)
 * y funciones de consulta preparadas para mitigar inyecciones SQL.
 */

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'ecoruta_db');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

/**
 * 1. Obtener una nueva instancia de conexión MySQLi segura
 * @return mysqli
 */
function conectar_bd() {
    // Activar modo de reporte de errores sin filtrar credenciales
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conexion->set_charset(DB_CHARSET);
        return $conexion;
    } catch (mysqli_sql_exception $e) {
        error_log("Error de conexión a la BD: " . $e->getMessage());
        die("Error temporal en el servicio de base de datos. Por favor, intente más tarde.");
    }
}

/**
 * 2. Sanitización básica de cadenas (útil para texto plano / display)
 * Nota: Para consultas SQL SIEMPRE use sentencias preparadas (prepare/bind_param).
 */
function limpiar_cadena($str) {
    if ($str === null) return '';
    $conexion = conectar_bd();
    $str = trim((string) $str);
    $str = stripslashes($str);
    $str = mysqli_real_escape_string($conexion, $str);
    $conexion->close();
    return $str;
}

/**
 * 3. Ejecutar consultas SELECT preparadas de forma segura
 * @param string $sql Consulta SQL con placeholders '?'
 * @param string $tipos Cadena de tipos para bind_param (ej: 'si' para string, int)
 * @param array $params Array de parámetros
 * @return array|false Lista asociativa de registros o false si no hay resultados
 */
function buscar_datos_preparado($sql, $tipos = '', $params = []) {
    $conexion = conectar_bd();
    try {
        $stmt = $conexion->prepare($sql);
        if (!empty($params) && !empty($tipos)) {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        $datos = [];
        while ($row = $resultado->fetch_assoc()) {
            $datos[] = $row;
        }
        $stmt->close();
        $conexion->close();
        return !empty($datos) ? $datos : false;
    } catch (Exception $e) {
        error_log("Error en buscar_datos_preparado: " . $e->getMessage());
        $conexion->close();
        return false;
    }
}

/**
 * 4. Helper SELECT tradicional (mantiene compatibilidad, previene errores)
 */
function buscar_datos($sql) {
    $conexion = conectar_bd();
    try {
        $resultado = $conexion->query($sql);
        $datos = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {
                $datos[] = $row;
            }
            $conexion->close();
            return $datos;
        }
        $conexion->close();
        return false;
    } catch (Exception $e) {
        error_log("Error en buscar_datos: " . $e->getMessage());
        $conexion->close();
        return false;
    }
}

/**
 * 5. Helper INSERT tradicional
 */
function insertar_datos($sql) {
    $conexion = conectar_bd();
    try {
        if ($conexion->query($sql)) {
            $last_id = $conexion->insert_id;
            $conexion->close();
            return $last_id;
        }
        $conexion->close();
        return false;
    } catch (Exception $e) {
        error_log("Error en insertar_datos: " . $e->getMessage());
        $conexion->close();
        return false;
    }
}

/**
 * 6. Helper UPDATE tradicional
 */
function actualizar_datos($sql) {
    $conexion = conectar_bd();
    try {
        $res = $conexion->query($sql);
        $conexion->close();
        return (bool) $res;
    } catch (Exception $e) {
        error_log("Error en actualizar_datos: " . $e->getMessage());
        $conexion->close();
        return false;
    }
}

/**
 * 7. Helper DELETE tradicional
 */
function eliminar_datos($sql) {
    $conexion = conectar_bd();
    try {
        $res = $conexion->query($sql);
        $conexion->close();
        return (bool) $res;
    } catch (Exception $e) {
        error_log("Error en eliminar_datos: " . $e->getMessage());
        $conexion->close();
        return false;
    }
}