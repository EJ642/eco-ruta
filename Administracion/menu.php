<?php
    $ruta = "../";
    include __DIR__ . '/includes/header.php';

    require_once __DIR__ . '/../servicios/conexion.php';

    $sqlMatriculados = " SELECT COUNT(*) AS total
        FROM matricula
        WHERE estado = 'Vigente'";

    $matriculados = buscar_datos($sqlMatriculados);
    $totalMatriculados = $matriculados[0]['total'] ?? 0;

?>

<style>

.dashboard-card{
    border:none;
    border-radius:20px;
    overflow:hidden;
    transition:all .3s ease;
    cursor:pointer;
    color:white;
    min-height:190px;
}

.dashboard-card:hover{
    transform:translateY(-8px);
    box-shadow:0 15px 35px rgba(0,0,0,.18);
}

.dashboard-icon{
    font-size:3.5rem;
    opacity:.9;
}

.dashboard-number{
    font-size:2rem;
    font-weight:700;
}

.dashboard-title{
    font-size:1.2rem;
    font-weight:600;
}

.dashboard-subtitle{
    font-size:.9rem;
    opacity:.9;
}

.bg-matricula{
    background:linear-gradient(135deg,#4e73df,#224abe);
}

.bg-docente{
    background:linear-gradient(135deg,#1cc88a,#13855c);
}

.bg-materia{
    background:linear-gradient(135deg,#f6c23e,#dda20a);
}

.bg-tutor{
    background:linear-gradient(135deg,#36b9cc,#258391);
}

.bg-aula{
    background:linear-gradient(135deg,#e74a3b,#be2617);
}

.bg-mantenimiento{
    background:linear-gradient(135deg,#858796,#5a5c69);
}

.dashboard-link{
    text-decoration:none;
}
</style>

<div class="main-content">

    <div class="mb-4">

        <h2 class="fw-bold">
            Administración Académica
        </h2>

        <p class="text-muted">
            Gestión integral de alumnos, docentes, tutores y configuraciones del sistema.
        </p>

    </div>

    <div class="alert alert-primary shadow-sm border-0">

        <i class="bi bi-info-circle-fill"></i>

        Actualmente existen
        <strong><?php echo $totalMatriculados; ?></strong>
        alumnos matriculados en el sistema.

    </div>

    <div class="row g-4">

        <!-- MATRICULACIONES -->
        <div class="col-lg-4 col-md-6">

        <a href="<?php echo $ruta; ?>Administracion/matriculaciones.php"
        class="dashboard-link">

            <div class="card dashboard-card bg-matricula">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="dashboard-number">
                                <?php echo $totalMatriculados; ?>
                            </div>

                            <div class="dashboard-title">
                                Matriculaciones
                            </div>

                            <div class="dashboard-subtitle">
                                Alumnos matriculados
                            </div>

                        </div>

                        <div>
                            <i class="bi bi-person-vcard-fill dashboard-icon"></i>
                        </div>

                    </div>

                </div>

            </div>

        </a>

    </div>

        <!-- ASIGNACIONES DOCENTES -->
        <div class="col-lg-4 col-md-6">

        <a href="<?php echo $ruta; ?>Administracion/asignaciones.php"
        class="dashboard-link">

            <div class="card dashboard-card bg-docente">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="dashboard-number">
                                <i class="bi bi-arrow-right-circle"></i>
                            </div>

                            <div class="dashboard-title">
                                Asignaciones
                            </div>

                            <div class="dashboard-subtitle">
                                Docentes y materias
                            </div>

                        </div>

                        <i class="bi bi-person-workspace dashboard-icon"></i>

                    </div>

                </div>

            </div>

        </a>

    </div>

        <!-- ASIGNACION MATERIAS -->
        <div class="col-lg-4 col-md-6">
                <a href="<?php echo $ruta; ?>Administracion/asignacionesMateria.php"
               class="dashboard-link">

                <div class="card dashboard-card bg-materia">

                    <div class="card-body">

                        <i class="bi bi-journal-bookmark-fill dashboard-icon"></i>

                        <h5 class="card-title">
                            Materias
                        </h5>

                        <span class="card-subtitle">
                            Materias por aula
                        </span>

                    </div>

                </div>

            </a>
        </div>

        <!-- TUTORES -->
        <div class="col-lg-4 col-md-6">
                <a href="<?php echo $ruta; ?>Administracion/asignacionesTutores.php"
               class="dashboard-link">

                <div class="card dashboard-card bg-tutor">

                    <div class="card-body">

                        <i class="bi bi-people-fill dashboard-icon"></i>

                        <h5 class="card-title">
                            Tutores
                        </h5>

                        <span class="card-subtitle">
                            Asignación de tutores
                        </span>

                    </div>

                </div>

            </a>
        </div>

        <!-- CURSOS -->
        <div class="col-lg-4 col-md-6">
                <a href="<?php echo $ruta; ?>Administracion/cursos.php"
               class="dashboard-link">

                <div class="card dashboard-card bg-aula">

                    <div class="card-body">

                        <i class="bi bi-building-fill dashboard-icon"></i>

                        <h5 class="card-title">
                            Cursos
                        </h5>

                        <span class="card-subtitle">
                            Gestión de cursos
                        </span>

                    </div>

                </div>

            </a>
        </div>

        <!-- AUDITORIA -->
        <div class="col-lg-4 col-md-6">
                <a href="<?php echo $ruta; ?>Administracion/auditoria_notas.php"
               class="dashboard-link">

                <div class="card dashboard-card bg-mantenimiento">

                    <div class="card-body">

                        <i class="bi bi-gear-fill dashboard-icon"></i>

                        <h5 class="card-title">
                            Auditoria
                        </h5>

                        <span class="card-subtitle">
                            Auditoria de notas
                        </span>

                    </div>

                </div>

            </a>
        </div>

    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>