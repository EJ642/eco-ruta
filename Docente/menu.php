<?php
    $ruta = '../';
    include __DIR__ . '/includes/header.php';
    
    // Obtener estadísticas del docente
    require_once __DIR__ . '/../servicios/conexion.php';
    
    $docente_id = $_SESSION['docente_id'] ?? null;
    $stats = [
        'materias' => 0,
        'aulas' => 0,
        'alumnos' => 0,
        'asistencias_hoy' => 0
    ];
    
    if ($docente_id) {
        // Contar materias asignadas
        $sql = "SELECT COUNT(DISTINCT am.idMateria) as total 
                FROM docente_aula_materia dam
                JOIN aula_materia am ON dam.idAulaMateria = am.idAulaMateria
                WHERE dam.idDocente = $docente_id AND dam.activo = 1";
        $res = buscar_datos($sql);
        $stats['materias'] = $res[0]['total'] ?? 0;
        
        // Contar aulas asignadas
        $sql = "SELECT COUNT(DISTINCT am.idAula) as total 
                FROM docente_aula_materia dam
                JOIN aula_materia am ON dam.idAulaMateria = am.idAulaMateria
                WHERE dam.idDocente = $docente_id AND dam.activo = 1";
        $res = buscar_datos($sql);
        $stats['aulas'] = $res[0]['total'] ?? 0;
        
        // Contar total de alumnos en sus aulas
        $sql = "SELECT COUNT(DISTINCT m.idAlumno) as total
                FROM docente_aula_materia dam
                JOIN aula_materia am ON dam.idAulaMateria = am.idAulaMateria
                JOIN matricula m ON m.idAula = am.idAula AND m.estado = 'Vigente'
                WHERE dam.idDocente = $docente_id AND dam.activo = 1";
        $res = buscar_datos($sql);
        $stats['alumnos'] = $res[0]['total'] ?? 0;
        
        // Asistencia de hoy
        $sql = "SELECT COUNT(*) as total 
                FROM asistencia_sesion a
                JOIN docente_aula_materia dam ON a.idAulaMateria = dam.idAulaMateria
                WHERE dam.idDocente = $docente_id 
                AND DATE(a.fecha) = CURDATE()";
        $res = buscar_datos($sql);
        $stats['asistencias_hoy'] = $res[0]['total'] ?? 0;
    }
?>

    <!-- Dashboard Stats -->
    <div class="main-content">
        <div class="card">
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-book"></i>
                                    </div>
                                    <div class="ms-3">
                                        <p class="text-muted mb-0">Materias</p>
                                        <h4 class="mb-0"><?php echo $stats['materias']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-door-open"></i>
                                    </div>
                                    <div class="ms-3">
                                        <p class="text-muted mb-0">Aulas</p>
                                        <h4 class="mb-0"><?php echo $stats['aulas']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-warning">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="ms-3">
                                        <p class="text-muted mb-0">Alumnos</p>
                                        <h4 class="mb-0"><?php echo $stats['alumnos']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-info">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div class="ms-3">
                                        <p class="text-muted mb-0">Asist. Hoy</p>
                                        <h4 class="mb-0"><?php echo $stats['asistencias_hoy']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones principales -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-pencil-square fs-1 text-primary"></i>
                                <h5 class="card-title mt-3">Crear Evaluaciones</h5>
                                <p class="card-text text-muted">Registrar Evaluaciones por aula</p>
                                <a href="<?php echo $ruta; ?>Docente/evaluaciones.php" class="btn btn-primary">Evaluaciones</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-calendar-check fs-1 text-success"></i>
                                <h5 class="card-title mt-3">Registrar Asistencia</h5>
                                <p class="card-text text-muted">Controlar asistencia diaria</p>
                                <a href="<?php echo $ruta; ?>Docente/asistencia.php" class="btn btn-success">Tomar Asistencia</a>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-table fs-1 text-info"></i>
                                <h5 class="card-title mt-3">Planilla</h5>
                                <p class="card-text text-muted">Ver el proceso de alumnos</p>
                                <a href="<?php echo $ruta; ?>Docente/resumen_alumno.php" class="btn btn-info">Ver Resumen</a>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>