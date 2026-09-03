<?php 
session_start();



// Mostrar todas las variables de sesión
echo "<pre>";
print_r($_SESSION); // Muestra el contenido de forma legible
echo "</pre>";

// También puedes usar var_dump para más detalle
// var_dump($_SESSION);


?>