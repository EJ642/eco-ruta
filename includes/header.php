<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$basePath = '/EcoRuta/';
$ruta = $basePath;

$navPorRol = [
    'administrador' => 'nav_admin.php',
    'comerciante'   => 'nav_comerciante.php',
    'repartidor'    => 'nav_repartidor.php',
];
$navFile = $navPorRol[$_SESSION['rol'] ?? ''] ?? 'nav_admin.php';
$paginaActual = basename($_SERVER['PHP_SELF'] ?? '');

$usuarioNombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$usuarioEmail = htmlspecialchars($_SESSION['usuario_email'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>EcoRuta · Logística Sostenible</title>

    <!-- Bootstrap 5, Bootstrap Icons & Alertify -->
    <link rel="stylesheet" href="/eco-ruta/bt-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/eco-ruta/alertify/alertify.min.css">
    <link rel="stylesheet" href="/eco-ruta/alertify/themes/default.min.css">
    <link rel="stylesheet" href="/eco-ruta/css/styles.css">
    <link rel="stylesheet" href="/eco-ruta/bt/bootstrap.min.css">
</head>
<body class="eco-admin-body">
    <div class="eco-admin-shell">
        <aside class="sidebar" id="sidebar" aria-label="Navegación lateral">
            <?php include __DIR__ . '/' . $navFile; ?>
        </aside>
        <div class="page-content">
            <header class="topbar">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Abrir menú de navegación" aria-expanded="false">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <div class="topbar-brand">
                        <span class="brand-dot"></span> EcoRuta
                    </div>
                </div>
                <div class="topbar-user">
                    <span class="user-badge">
                        <i class="bi bi-person-circle"></i>
                        <span class="text-truncate"><?php echo $usuarioEmail ?: $usuarioNombre; ?></span>
                    </span>
                </div>
            </header>
            <main class="main-content">
                <div class="container-fluid">