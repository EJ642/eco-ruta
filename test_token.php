<?php
session_start();
require_once 'sesion.php'; // Verificar sesión

echo "<h1>Información de Sesión y Token</h1>";
echo "<pre>";
echo "Usuario: " . ($_SESSION['usuario'] ?? 'No definido') . "\n";
echo "Rol: " . ($_SESSION['rol'] ?? 'No definido') . "\n";
echo "Token: " . ($_SESSION['token'] ?? 'No definido') . "\n";
echo "Hora de login: " . (isset($_SESSION['login_time']) ? date('H:i:s', $_SESSION['login_time']) : 'No definido') . "\n";
echo "Tiempo activo: " . (isset($_SESSION['login_time']) ? (time() - $_SESSION['login_time']) . ' segundos' : 'No definido') . "\n";
echo "Sesión activa: " . (isset($_SESSION['active']) ? 'Sí' : 'No') . "\n";
echo "</pre>";

echo "<h2>Acciones de prueba:</h2>";
echo "<ul>";
echo "<li><a href='?action=refresh'>Refrescar token manualmente</a></li>";
echo "<li><a href='salir.php'>Cerrar sesión</a></li>";
echo "</ul>";

// Refrescar token manualmente si se solicita
if (isset($_GET['action']) && $_GET['action'] === 'refresh') {
    $nuevo_token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $nuevo_token;
    echo "<p style='color: green;'>Token refrescado: $nuevo_token</p>";
    echo "<script>location.href='test_token.php';</script>";
}
?>