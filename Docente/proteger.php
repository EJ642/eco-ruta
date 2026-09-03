<?php
// Proteger página: solo acceso para docentes
require_once __DIR__ . '/../servicios/conexion.php';

if (!isset($_SESSION['active']) || $_SESSION['active'] !== true) {
    header('Location: index.php?error=sesion_expirada');
    exit;
}

// Verificar que sea rol Docente (idRol = 3)
if ($_SESSION['rol'] !== 'Docente') {
    header('Location: ../salir.php');
    exit;
}

// Timeout de sesión (30 minutos)
$timeout = 1800;
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $timeout) {
    header('Location: ../salir.php?timeout=1');
    exit;
}

// Actualizar último acceso
$_SESSION['ultimo_acceso'] = time();