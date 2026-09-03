<?php
    if (!isset($ruta)) { $ruta = ""; }

    $currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $isActive = function ($pages) use ($currentPage) {
        return in_array($currentPage, (array) $pages, true) ? ' active' : '';
    };
?>
    <div class="menu-btn" id="menu-btn">
        <i class='bi bi-chevron-left'></i>
    </div>

    <div class="brand">
        <div class="brand-mark">
            <i class='bi bi-mortarboard-fill'></i>
        </div>
        <div class="brand-text">
            <span class="brand-name">Santa Teresita</span>
            <small class="brand-role">Docente</small>
        </div>
    </div>

    <div class="menu-container">
        <ul class="menu">
            <li class="menu-item menu-item-static<?php echo $isActive('menu.php'); ?>">
                <a href="<?php echo $ruta; ?>Docente/menu.php" class="menu-link">
                    <i class='bi bi-house'></i>
                    <span>Inicio</span>
                </a>
            </li>

            <div class="menu-section-title mt-3">Gestión Diaria</div>
            
            <li class="menu-item menu-item-static<?php echo $isActive('reg_catedra.php'); ?>">
                <a href="<?php echo $ruta; ?>Docente/reg_catedra.php" class="menu-link">
                    <i class="bi bi-card-list"></i>
                    <span>Registro de cátedra</span>
                </a>
            </li>

            <li class="menu-item menu-item-static<?php echo $isActive('asistencia.php'); ?>">
                <a href="<?php echo $ruta; ?>Docente/asistencia.php" class="menu-link">
                    <i class='bi bi-calendar-check'></i>
                    <span>Asistencia</span>
                </a>
            </li>

            <div class="menu-section-title mt-3">Académico</div>

            <li class="menu-item menu-item-dropdown<?php echo $isActive('resumen_alumno.php', 'recuperatorios.php'); ?>">
                <button type="button" class="menu-link menu-toggle" aria-expanded="false">
                    <i class='bi bi-table'></i>
                    <span>Mi Planilla</span>
                    <i class='bi bi-chevron-down linki'></i>
                </button>
                <ul class="sub-menu">
                    <li>
                        <a href="<?php echo $ruta; ?>Docente/resumen_alumno.php" class="sub-menu-item<?php echo $currentPage === 'resumen_alumno.php' ? ' active' : ''; ?>">Ver proceso</a>
                    </li>
                    <li>
                        <a href="<?php echo $ruta; ?>Docente/recuperatorios.php" class="sub-menu-item<?php echo $currentPage === 'recuperatorios.php' ? ' active' : ''; ?>">Recuperatorios</a>
                    </li>
                </ul>
            </li>            

            <li class="menu-item menu-item-static<?php echo $isActive('calificaciones.php'); ?>">
                <a href="<?php echo $ruta; ?>Docente/calificaciones.php" class="menu-link">
                    <i class='bi bi-journal-check'></i>
                    <span>Registro de Proceso</span>
                </a>
            </li>

            <li class="menu-item menu-item-static<?php echo $isActive('evaluaciones.php'); ?>">
                <a href="<?php echo $ruta; ?>Docente/evaluaciones.php" class="menu-link">
                    <i class='bi bi-pencil'></i>
                    <span>Evaluaciones</span>
                </a>
            </li>


        </ul>
    </div>