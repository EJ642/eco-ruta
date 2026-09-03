<?php
require_once "servicios/conexion.php";

// Agregar columnas si no existen
$sql_alter = "
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS correo VARCHAR(255) NOT NULL AFTER usuario,
ADD COLUMN IF NOT EXISTS nombre VARCHAR(255) NOT NULL AFTER correo,
ADD COLUMN IF NOT EXISTS estado ENUM('Activo','Inactivo') DEFAULT 'Activo' AFTER nombre,
ADD COLUMN IF NOT EXISTS token VARCHAR(64) DEFAULT NULL AFTER estado,
ADD COLUMN IF NOT EXISTS token_expira DATETIME DEFAULT NULL AFTER token;
";

if (actualizar_datos($sql_alter)) {
    echo "Columnas agregadas exitosamente.<br>";
} else {
    echo "Error al agregar columnas.<br>";
}

// Hashear la contraseña y actualizar usuario
$hash = password_hash('admin123', PASSWORD_BCRYPT);
$sql_update = "UPDATE usuarios SET password = '$hash', correo = 'sergio@example.com', nombre = 'Sergio', estado = 'Activo' WHERE idUsuario = 1";

if (actualizar_datos($sql_update)) {
    echo "Usuario actualizado exitosamente.";
} else {
    echo "Error al actualizar usuario.";
}
?>