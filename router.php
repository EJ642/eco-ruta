<?php
session_start();

if (!isset($_SESSION['usuario_id'], $_SESSION['rol'])) {
    header('Location: index.php');
    exit;
}

$destinoPorRol = [
    'administrador' => 'admin/dashboard.php',
    'comerciante'   => 'comerciante/dashboard.php',
    'repartidor'    => 'repartidor/dashboard.php',
];

$rol = $_SESSION['rol'];
$destino = $destinoPorRol[$rol] ?? 'index.php';

header('Location: ' . $destino);
exit;

