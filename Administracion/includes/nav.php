<?php
    if (!isset($ruta)) { $ruta = ""; }
?>
        <div class="menu-btn" id="menu-btn">
            <i class='bx bx-chevron-left'></i>
        </div>
        <div class="brand">
            <span>Santa Teresita</span>
        </div>

        <div class="menu-container">
            <ul class="menu">
                <li class="menu-item menu-item-static active"><a href="<?php echo $baseAdminUrl; ?>menu.php" class="menu-link"><i class='bx bx-home'></i> Inicio</a></li>
                <li class="menu-item menu-item-dropdown"><a href="#" class="menu-link"><i class='bx bx-grid-alt'></i> Mantenimientos<i class='bx bx-chevron-down linki'></i></a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo $baseAdminUrl; ?>usuarios.php" class="sub-menu-item">Usuarios</a></li>
                        <li><a href="<?php echo $baseAdminUrl; ?>docentes.php" class="sub-menu-item">Profesores</a></li>
                        <li><a href="<?php echo $baseAdminUrl; ?>alumnos.php" class="sub-menu-item">Alumnos</a></li>
                        <li><a href="<?php echo $baseAdminUrl; ?>especialidades.php" class="sub-menu-item">Especialidades</a></li>
                        <li><a href="<?php echo $baseAdminUrl; ?>cursos.php" class="sub-menu-item">Cursos</a></li>
                        <li><a href="<?php echo $baseAdminUrl; ?>materias.php" class="sub-menu-item">Materias</a></li>
                        <li><a href="<?php echo $baseAdminUrl; ?>turnos.php" class="sub-menu-item">Turnos</a></li>
                        <li><a href="<?php echo $baseAdminUrl; ?>tutores.php" class="sub-menu-item">Tutores</a></li>
                    </ul>
                </li>
                <li class="menu-item menu-item-static"><a href="<?php echo $baseAdminUrl; ?>resumen_alumno.php" class="menu-link"><i class='bx bx-book-open'></i> Resumen Calificaciones</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $baseAdminUrl; ?>matriculaciones.php" class="menu-link"><i class='bx bx-user-plus'></i> Matriculaciones</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $baseAdminUrl; ?>asignaciones.php" class="menu-link"><i class="bi bi-card-list"></i> Docente-Asignaciones</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $baseAdminUrl; ?>asignacionesMateria.php" class="menu-link"><i class="bi bi-card-list"></i> Materia-Asignaciones</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $baseAdminUrl; ?>asignacionesTutores.php" class="menu-link"><i class="bi bi-person-lines-fill"></i> Tutor-Asignaciones</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $baseAdminUrl; ?>cierre_academico.php" class="menu-link"><i class="bi bi-card-list"></i> Cierres</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $baseAdminUrl; ?>auditoria_notas.php" class="menu-link"><i class="bi bi-card-list"></i> Auditoría</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $baseAdminUrl; ?>backup.php" class="menu-link"><i class="bi bi-download"></i> Respaldos</a></li>
            </ul>
        </div>
