<?php

date_default_timezone_set('America/Sao_Paulo');

header('Content-Type: application/json');

$response = [
    "status" => false,
    "msg" => ""
];

//======================================
// DATOS DE CONEXIÓN
//======================================

$host      = "localhost";
$usuario   = "root";
$password  = "";
$basedatos = "ecoruta_db";

//======================================

$conn = new mysqli($host, $usuario, $password, $basedatos);

if ($conn->connect_error) {

    $response["msg"] = "Error de conexión: " . $conn->connect_error;

    echo json_encode($response);

    exit;
}

$conn->set_charset("utf8");

//======================================
// CARPETA BACKUPS
//======================================

$carpeta = __DIR__ . "/../backups/";

if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

//======================================

$fecha = date("d-m-Y_H-i-s");

$archivo = $carpeta . "ecoruta_" . $fecha . ".sql";

//======================================

$sql = "-- =====================================\n";
$sql .= "-- RESPALDO AUTOMÁTICO\n";
$sql .= "-- EcoRuta\n";
$sql .= "-- Fecha: " . date("d/m/Y H:i:s") . "\n";
$sql .= "-- =====================================\n\n";

$tablas = [];

$result = $conn->query("SHOW TABLES");

while ($row = $result->fetch_array()) {
    $tablas[] = $row[0];
}

foreach ($tablas as $tabla) {

    // Estructura

    $create = $conn->query("SHOW CREATE TABLE `$tabla`")->fetch_assoc();

    $sql .= "\n\nDROP TABLE IF EXISTS `$tabla`;\n";

    $sql .= $create['Create Table'] . ";\n\n";

    // Datos

    $datos = $conn->query("SELECT * FROM `$tabla`");

    while ($fila = $datos->fetch_assoc()) {

        $columnas = array_keys($fila);

        $valores = [];

        foreach ($fila as $valor) {

            if (is_null($valor)) {

                $valores[] = "NULL";

            } else {

                $valores[] = "'" . $conn->real_escape_string($valor) . "'";

            }

        }

        $sql .= "INSERT INTO `$tabla` (`"
            . implode("`,`", $columnas)
            . "`) VALUES ("
            . implode(",", $valores)
            . ");\n";

    }

    $sql .= "\n";

}

//======================================

if (file_put_contents($archivo, $sql)) {

    //Eliminar respaldos de más de 30 días

    foreach (glob($carpeta . "*.sql") as $file) {

        if (filemtime($file) < strtotime("-30 days")) {

            unlink($file);

        }

    }

    $response["status"] = true;
    $response["msg"] = "El respaldo fue creado correctamente.";

} else {

    $response["msg"] = "No fue posible guardar el archivo.";

}

$conn->close();

echo json_encode($response);