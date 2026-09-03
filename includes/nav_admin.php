<?php
if (!isset($ruta)) { $ruta = ''; }
$usuarioNombreSidebar = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8');
$usuarioRolSidebar = htmlspecialchars(ucfirst($_SESSION['rol'] ?? 'administrador'), ENT_QUOTES, 'UTF-8');
$paginaActual = basename($_SERVER['PHP_SELF'] ?? '');
?>
<div class="sidebar-toolbar">
    <div class="brand-mark">
        <span class="brand-badge"><i class="bi bi-tree-fill"></i></span>
        <span class="brand-text">EcoRuta</span>
    </div>
    <button type="button" class="menu-btn" id="menu-btn" aria-label="Comprimir barra lateral" aria-pressed="false">
        <i class="bi bi-layout-sidebar-inset"></i>
    </button>
</div>

<div class="menu-container">
    <nav class="menu" aria-label="Menú principal">
        <div class="menu-section-title">Panel Principal</div>

        <div class="menu-item <?php echo $paginaActual === 'dashboard.php' ? 'active' : ''; ?>">
            <a href="<?php echo $ruta; ?>admin/dashboard.php" class="menu-link" title="Inicio">
                <span class="menu-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                <span class="menu-text">Panel de Control</span>
            </a>
        </div>

        <div class="menu-section-title mt-3">Gestión</div>

        <div class="menu-item <?php echo $paginaActual === 'usuarios.php' ? 'active' : ''; ?>">
            <a href="<?php echo $ruta; ?>admin/usuarios.php" class="menu-link" title="Gestión de Usuarios">
                <span class="menu-icon"><i class="bi bi-people-fill"></i></span>
                <span class="menu-text">Usuarios y Cuentas</span>
            </a>
        </div>
    </nav>
</div>

<div class="sidebar-footer">
    <div class="user-profile">
        <div class="user-avatar"><?php echo strtoupper(substr($usuarioNombreSidebar, 0, 1)); ?></div>
        <div class="user-data">
            <span class="name"><?php echo $usuarioNombreSidebar; ?></span>
            <span class="rol"><?php echo $usuarioRolSidebar; ?></span>
        </div>
    </div>
    <button type="button" id="exit-btn" class="exit-btn" title="Cerrar sesión" aria-label="Cerrar sesión">
        <i class="bi bi-box-arrow-right"></i>
    </button>
</div>
