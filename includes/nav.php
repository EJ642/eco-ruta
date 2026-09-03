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
                <li class="menu-item menu-item-static active"><a href="<?php echo $ruta; ?>Administracion/menu.php" class="menu-link"><i
                            class='bx bx-home'></i>
                        Inicio</a></li>
                <li class="menu-item menu-item-dropdown"><a href="#" class="menu-link"><i class='bx bx-grid-alt'></i>
                        Mantenimientos<i class='bx bx-chevron-down linki'></i></a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo $ruta; ?>Administracion/usuarios.php" class="sub-menu-item">Usuarios</a></li>
                        <li><a href="<?php echo $ruta; ?>Administracion/docentes.php" class="sub-menu-item">Profesores</a></li>
                        <li><a href="<?php echo $ruta; ?>Administracion/alumnos.php" class="sub-menu-item">Alumnos</a></li>
                        <li><a href="<?php echo $ruta; ?>Administracion/especialidades.php" class="sub-menu-item">Especialidades</a></li>
                        <li><a href="<?php echo $ruta; ?>Administracion/cursos.php" class="sub-menu-item">Cursos</a></li>
                        <li><a href="<?php echo $ruta; ?>Administracion/materias.php" class="sub-menu-item">Materias</a></li>
                        <li><a href="<?php echo $ruta; ?>Administracion/turnos.php" class="sub-menu-item">Turnos</a></li>
                        <li><a href="<?php echo $ruta; ?>Administracion/tutores.php" class="sub-menu-item">Tutores</a></li>
                    </ul>
                </li>
                <li class="menu-item menu-item-static"><a href="<?php echo $ruta; ?>Administracion/resumen_alumno.php" class="menu-link"><i
                            class='bx bx-book-open'></i>
                        Resumen Calificaciones</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $ruta; ?>Administracion/matriculaciones.php" class="menu-link"><i
                            class='bx bx-user-plus'></i>
                        Matriculaciones</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $ruta; ?>Administracion/asignaciones.php" class="menu-link"><i class="bi bi-card-list"></i>
                        Docente-Asignaciones</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $ruta; ?>Administracion/asignacionesMateria.php" class="menu-link"><i class="bi bi-card-list"></i>
                        Materia-Asignaciones</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $ruta; ?>Administracion/asignacionesTutores.php" class="menu-link"><i class="bi bi-person-lines-fill"></i>
                        Tutor-Asignaciones</a></li>
                <li class="menu-item menu-item-static"><a href="<?php echo $ruta; ?>Administracion/cierre_academico.php" class="menu-link"><i class="bi bi-card-list"></i>
                        Cierres</a>
                </li>
                <li class="menu-item menu-item-static"><a href="<?php echo $ruta; ?>Administracion/auditoria_notas.php" class="menu-link"><i class="bi bi-card-list"></i>
                        Auditoría</a>
                </li>
                <li class="menu-item menu-item-static"><a href="<?php echo $ruta; ?>Administracion/backup.php" class="menu-link"><i class="bi bi-download"></i>
                        Respaldos</a>
                </li>
                
            </ul>
        </div>