<?php
    session_name('DOCENTE_SESSION');
    session_start();
    if (!isset($ruta)) { $ruta = ""; }

    if (!function_exists('validarRutaSegura')) {
        function validarRutaSegura($ruta) {
            if (empty($ruta)) {
                return false;
            }
            $ruta = filter_var($ruta, FILTER_SANITIZE_URL);
            $ruta = strtok($ruta, '#');
            if (strpos($ruta, '..') !== false) {
                return false;
            }
            if (!preg_match('#^/[A-Za-z0-9_\-./?=&%]*$#', $ruta)) {
                return false;
            }
            return $ruta;
        }
    }

    if (!function_exists('guardarUltimaRuta')) {
        function guardarUltimaRuta() {
            $rutaActual = $_SERVER['REQUEST_URI'] ?? '';
            $rutaSegura = validarRutaSegura($rutaActual);
            if ($rutaSegura) {
                setcookie('ultima_ruta', $rutaSegura, time() + 30 * 24 * 60 * 60, '/', '', false, true);
            }
        }
    }

    // Seguridad: verificar sesión y token
    if (empty($_SESSION['active']) || empty($_SESSION['token'])) {
        header('location: '.$ruta.'Docente/index.php');
        exit;
    }

    // Verificar que sea rol Docente
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Docente') {
        header('location: '.$ruta.'salirDocente.php');
        exit;
    }

    // Guardar la última ruta visitada
    guardarUltimaRuta();

    // Verificar expiración de sesión por inactividad (30 minutos)
    $max_session_time = 30 * 60;
    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $max_session_time) {
        session_destroy();
        header('location: '.$ruta.'Docente/index.php?error=sesion_expirada');
        exit;
    }

    // Actualizar último acceso en cada petición válida
    $_SESSION['ultimo_acceso'] = time();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Docente - Santa Teresita</title>
<!--
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
-->  
    <link rel="stylesheet" href="<?php echo $ruta; ?>css/stylesDocente.css">
    <link href="<?php echo $ruta; ?>bt/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $ruta; ?>bt-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $ruta; ?>alertify/alertify.min.css"/>
    <link rel="stylesheet" href="<?php echo $ruta; ?>alertify/themes/default.min.css"/>
  
    <style>
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .bg-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .bg-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .bg-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <?php include __DIR__ . "/nav.php"; ?>

        <div class="footer">
            <ul class="menu">
                           <li class="menu-item menu-item-static<?php echo $isActive('configuracion.php'); ?>">
                <a href="<?php echo $ruta; ?>Docente/configuracion.php" class="menu-link">
                    <i class='bi bi-gear'></i>
                    <span>Configuración</span>
                </a>
            </li>
            </ul>
            <div class="user">
                <div class="user-img">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="user-data">
                    <span class="name"><?php echo $_SESSION['nombre_docente'] . ' ' . $_SESSION['apellido_docente']; ?></span>
                    <span class="rol"><?php echo $_SESSION['rol']; ?></span>
                </div>
                <div class="user-icon exit-btn" id="exit-btn">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
            </div>
        </div>
    </div>
