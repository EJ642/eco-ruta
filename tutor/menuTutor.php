<?php
    $ruta = "../";
    include $ruta . "tutor/includes/header.php";

    // ── Conexión usando conexion.php del proyecto ─────────────────────────────
    include_once $ruta . "servicios/conexion.php";

    // ── Resolver idTutor ──────────────────────────────────────────────────────
    // Prioridad 1: $_SESSION['idTutor'] seteado directo en el login
    // Prioridad 2: buscar en tutor por idUsuario de la sesión
    $idTutor = 0;
    $tutor   = [];

    // El login guarda el ID en $_SESSION['usuario_id']
    $idUsuarioSesion = intval($_SESSION['usuario_id'] ?? $_SESSION['idUsuario'] ?? 0);

    if (!empty($_SESSION['idTutor'])) {
        $idTutor  = intval($_SESSION['idTutor']);
        $resTutor = buscar_datos("SELECT idTutor, nombres, apellidos, parentesco FROM tutor WHERE idTutor = $idTutor AND estado = 'Activo' LIMIT 1");
        $tutor    = $resTutor ? $resTutor[0] : [];

    } elseif ($idUsuarioSesion > 0) {
        $resTutor = buscar_datos("SELECT idTutor, nombres, apellidos, parentesco FROM tutor WHERE idUsuario = $idUsuarioSesion AND estado = 'Activo' LIMIT 1");
        if ($resTutor) {
            $tutor   = $resTutor[0];
            $idTutor = intval($tutor['idTutor']);
        }
    }

    // Fallback de nombre para el encabezado si no se encontró el tutor
    if (empty($tutor)) {
        $tutor = [
            'idTutor'    => 0,
            'nombres'    => $_SESSION['usuario'] ?? 'Tutor',
            'apellidos'  => '',
            'parentesco' => '',
        ];
    }

    // ── Alumnos asignados al tutor con su matrícula activa ────────────────────
    $alumnos = [];

    if ($idTutor > 0) {
        // JOIN completo: alumno_tutor → alumno → matricula → aula → anio_lectivo activo → curso → enfasis → turno
        $resAlumnos = buscar_datos("
            SELECT
                a.idAlumno,
                a.cedula,
                a.nombres,
                a.apellidos,
                a.fecha_nac,
                a.sexo,
                a.estado                 AS estado_alumno,
                at2.es_principal,
                m.idMatricula,
                m.idAula,
                m.estado                 AS estado_matricula,
                c.nombre                 AS nombre_curso,
                c.numero                 AS numero_curso,
                e.nombre                 AS nombre_enfasis,
                t.turno                  AS turno,
                t.descripcion            AS desc_turno,
                al2.anio                 AS anio_lectivo
            FROM alumno_tutor at2
            INNER JOIN alumno        a   ON a.idAlumno  = at2.idAlumno
            INNER JOIN matricula     m   ON m.idAlumno  = a.idAlumno
            INNER JOIN aula          au  ON au.idAula   = m.idAula
            INNER JOIN anio_lectivo  al2 ON al2.idAnio  = au.idAnio
                                        AND al2.activo  = 'Sí'
            INNER JOIN curso         c   ON c.idCurso   = au.idCurso
            INNER JOIN enfasis       e   ON e.idEnfasis = au.idEnfasis
            INNER JOIN turno         t   ON t.idTurno   = c.idTurno
            WHERE at2.idTutor = $idTutor
              AND a.estado    = 'Activo'
              AND m.estado    = 'Vigente'
            ORDER BY at2.es_principal DESC, a.apellidos, a.nombres
        ");

        // buscar_datos() devuelve false si no hay filas; normalizar a array
        $alumnos = $resAlumnos ?: [];
    }

    $totalAlumnos = count($alumnos);

    // Contar cursos únicos
    $cursosUnicos = count(array_unique(array_column($alumnos, 'nombre_curso')));

    // Helper: iniciales del nombre
    function iniciales(string $nombres, string $apellidos): string {
        $partes = array_filter(explode(' ', trim($nombres . ' ' . $apellidos)));
        return strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), array_slice($partes, 0, 2))));
    }

    // Helper: turno legible
    function labelTurno(string $turno): string {
        return match($turno) {
            'M'  => 'Mañana',
            'T'  => 'Tarde',
            'MT' => 'Mañana y Tarde',
            default => $turno,
        };
    }

    // Helper: edad desde fecha_nac
    function edad(string $fecha_nac): string {
        if (!$fecha_nac || $fecha_nac === '0000-00-00') return '—';
        $diff = (new DateTime())->diff(new DateTime($fecha_nac));
        return $diff->y . ' años';
    }
?>

<div class="main-content">
    <div class="card tutor-page">

        <!-- ══ DIAGNÓSTICO TEMPORAL — borrar en producción ══ -->
        <?php if (false): ?>
        <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:.5rem;padding:1rem;margin-bottom:1.5rem;font-size:.75rem;font-family:monospace;line-height:1.8;">
            <strong>&#x1F527; DIAGNÓSTICO (poner false para ocultar)</strong><br><br>
            <b>Variables de sesión:</b><br>
            <?php foreach ($_SESSION as $k => $v): ?>
            &nbsp;&nbsp;$_SESSION['<?php echo htmlspecialchars($k); ?>'] = <em><?php echo htmlspecialchars((string)$v); ?></em><br>
            <?php endforeach; ?>
            <br>
            <b>idTutor resuelto:</b> <?php echo $idTutor; ?><br><br>
            <?php
                $dbgAnio = buscar_datos("SELECT * FROM anio_lectivo WHERE activo = 'Si'");
                if (!$dbgAnio) $dbgAnio = buscar_datos("SELECT * FROM anio_lectivo WHERE activo = 'S\xc3\xad'");
                echo "<b>Año lectivo activo:</b> " . ($dbgAnio ? json_encode($dbgAnio[0]) : '<span style=color:red>NINGUNO</span>') . "<br>";
                if ($idTutor > 0) {
                    $dbgAt = buscar_datos("SELECT * FROM alumno_tutor WHERE idTutor = $idTutor");
                    echo "<b>alumno_tutor para idTutor=$idTutor:</b> " . ($dbgAt ? json_encode($dbgAt) : '<span style=color:red>NINGUNA FILA</span>') . "<br>";
                    if ($dbgAt) {
                        $ids = implode(',', array_column($dbgAt, 'idAlumno'));
                        $dbgMat = buscar_datos("SELECT idMatricula,idAlumno,idAula,estado FROM matricula WHERE idAlumno IN ($ids)");
                        echo "<b>Matrículas:</b> " . ($dbgMat ? json_encode($dbgMat) : '<span style=color:red>NINGUNA</span>') . "<br>";
                    }
                } else {
                    echo "<b style=color:red>idTutor=0: no se encontró el tutor. Revisar SESSION y campo tutor.idUsuario en BD.</b>";
                }
            ?>
        </div>
        <?php endif; ?>
        <!-- ══ FIN DIAGNÓSTICO ══ -->

        <!-- Encabezado  -->
        <div class="tutor-header">
            <p class="tutor-greeting">Bienvenido/a,</p>
            <h1 class="tutor-name"><?php echo htmlspecialchars($tutor['nombres'] . ' ' . $tutor['apellidos']); ?></h1>
        </div>

        <?php if ($totalAlumnos > 0): ?>

        <!-- Barra de búsqueda / filtro -->
        <div class="alumnos-toolbar">
            <div class="search-wrapper">
                <i class='bx bx-search'></i>
                <input type="text" id="buscar-alumno" placeholder="Buscar alumno por nombre o cédula…" oninput="filtrarAlumnos()">
            </div>
            <select id="filtro-curso" onchange="filtrarAlumnos()">
                <option value="">Todos los cursos</option>
                <?php
                $cursosOpciones = array_unique(array_column($alumnos, 'nombre_curso'));
                foreach ($cursosOpciones as $c): ?>
                <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="filtro-principal" onchange="filtrarAlumnos()">
                <option value="">Todos</option>
                <option value="Sí">Tutor principal</option>
                <option value="No">Tutor secundario</option>
            </select>
        </div>

        <!--  Grid de tarjetas -->
        <div class="alumnos-grid" id="alumnos-grid">
            <?php foreach ($alumnos as $al):
                $ini = iniciales($al['nombres'], $al['apellidos']);
                $esPrincipal = $al['es_principal'] === 'Sí';
                $turnoLabel = labelTurno($al['turno']);
                $sexoIcon = $al['sexo'] === 'F' ? 'bx-female' : 'bx-male';
                $sexoColor = $al['sexo'] === 'F' ? 'avatar-f' : 'avatar-m';
                $cursoCorto = $al['numero_curso'] . '° · ' . mb_substr($al['nombre_enfasis'], 0, 14);
            ?>
            <div class="alumno-card"
                 data-nombre="<?php echo strtolower($al['nombres'] . ' ' . $al['apellidos']); ?>"
                 data-cedula="<?php echo $al['cedula']; ?>"
                 data-curso="<?php echo htmlspecialchars($al['nombre_curso']); ?>"
                 data-principal="<?php echo $al['es_principal']; ?>">

                <!-- Columna izquierda: avatar + badges -->
                <div class="card-left">
                    <div class="alumno-avatar <?php echo $sexoColor; ?>">
                        <span><?php echo $ini; ?></span>
                    </div>
                    <?php if ($esPrincipal): ?>
                    <span class="badge-principal" title="Tutor principal"><i class='bx bx-star'></i> Principal</span>
                    <?php endif; ?>
                    <span class="badge-sexo"><i class="bx <?php echo $sexoIcon; ?>"></i></span>
                </div>

                <!-- Columna central: datos del alumno -->
                <div class="card-body-alumno">
                    <h3 class="alumno-nombre">
                        <?php echo htmlspecialchars($al['apellidos'] . ', ' . $al['nombres']); ?>
                    </h3>

                    <div class="alumno-meta-grid">
                        <div class="meta-item">
                            <i class='bx bx-id-card'></i>
                            <span><?php echo $al['cedula'] ?: '—'; ?></span>
                        </div>
                        <div class="meta-item">
                            <i class='bx bx-calendar'></i>
                            <span><?php echo edad($al['fecha_nac']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class='bx bx-book-open'></i>
                            <span><?php echo htmlspecialchars($al['nombre_curso']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class='bx bx-bulb'></i>
                            <span><?php echo htmlspecialchars($al['nombre_enfasis']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class='bx bx-time'></i>
                            <span><?php echo $turnoLabel; ?></span>
                        </div>
                        <div class="meta-item">
                            <i class='bx bx-check-shield'></i>
                            <span class="estado-mat estado-<?php echo strtolower($al['estado_matricula']); ?>">
                                <?php echo $al['estado_matricula']; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: botón calificaciones -->
                <div class="card-right">
                    <a href="<?php echo $ruta; ?>tutor/resumen_alumno_tutor.php?idAlumno=<?php echo $al['idAlumno']; ?>&idAula=<?php echo $al['idAula']; ?>&nombre=<?php echo urlencode($al['apellidos'].', '.$al['nombres']); ?>"
                       class="btn-ver-notas">
                        <i class='bx bx-bar-chart-alt-2'></i> Ver calificaciones
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div><!-- /alumnos-grid -->

        <!-- Estado vacío de búsqueda -->
        <div class="empty-state" id="empty-state" style="display:none;">
            <i class='bx bx-search-alt'></i>
            <p>No se encontraron alumnos con ese criterio.</p>
        </div>

        <?php else: ?>
        <!-- Sin alumnos asignados -->
        <div class="empty-state empty-state-full">
            <i class='bx bx-group'></i>
            <p>No tenés alumnos asignados en el año lectivo activo.</p>
            <small>Comunicate con la administración para verificar tu asignación.</small>
        </div>
        <?php endif; ?>

    </div><!-- /card -->
</div><!-- /main-content -->

<style>
/* ═══════════════════════════════════════════════════
   TUTOR PAGE — estilos embebidos (no modifica styles.css)
   ═══════════════════════════════════════════════════ */

/* Sidebar minimizado */
body.sidebar-minimized .main-content {
    margin-left: 5.42rem;
    width: calc(100% - 5.42rem);
}

/* La página ocupa toda la altura visible bajo el header del layout */
.tutor-page {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 40px); /* ajustar según padding del main-content */
}

/* ── Header simple ────────────────────────────────── */
.tutor-header {
    flex-shrink: 0;
    margin-bottom: 1.1rem;
    padding-bottom: 0.9rem;
    border-bottom: 1px solid var(--color-border);
}
.tutor-greeting {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
    margin-bottom: 0.1rem;
}
.tutor-name {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--color-text-primary);
    line-height: 1.2;
}

/* ── Toolbar ──────────────────────────────────────── */
.alumnos-toolbar {
    display: flex;
    gap: 0.6rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    align-items: center;
    flex-shrink: 0;
}
.search-wrapper {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid var(--color-border);
    border-radius: 0.5rem;
    padding: 0.42rem 0.75rem;
    gap: 0.4rem;
    flex: 1;
    min-width: 180px;
}
.search-wrapper i { color: var(--color-text-secondary); font-size: 1rem; }
.search-wrapper input {
    border: none; outline: none;
    font-size: 0.8rem;
    width: 100%;
    font-family: "Poppins", sans-serif;
    color: var(--color-text-primary);
    background: transparent;
}
.alumnos-toolbar select {
    border: 1px solid var(--color-border);
    border-radius: 0.5rem;
    padding: 0.42rem 0.65rem;
    font-size: 0.78rem;
    font-family: "Poppins", sans-serif;
    color: var(--color-text-secondary);
    background: #fff;
    cursor: pointer;
    outline: none;
}

/* ── Grid: tarjetas rectangulares, ocupan la altura restante ───── */
.alumnos-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.85rem;
    flex: 1;
    min-height: 0;          /* permite que el grid se achique y scrollee */
    overflow-y: auto;
    padding-right: 4px;     /* espacio para la scrollbar */
}

/* ── Tarjeta rectangular horizontal ──────────────────────────── */
.alumno-card {
    background: #fff;
    border-radius: 0.85rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 1.25rem;
    padding: 1.1rem 1.5rem;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    border: 1px solid transparent;
    min-height: 90px;
}
.alumno-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    border-color: #748ffc30;
}

/* ── Columna izquierda: avatar + badges ──────────────────────── */
.card-left {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    flex-shrink: 0;
}
.alumno-avatar {
    width: 3.4rem;
    height: 3.4rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.03em;
}
.avatar-f { background: linear-gradient(135deg, #e64980, #f783ac); }
.avatar-m { background: linear-gradient(135deg, #3b5bdb, #748ffc); }

.badge-principal {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
    background: #fff3cd;
    color: #856404;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    white-space: nowrap;
}
.badge-principal i { font-size: 0.65rem; }
.badge-sexo {
    font-size: 1rem;
    color: var(--color-border);
}

/* ── Columna central: datos del alumno ───────────────────────── */
.card-body-alumno {
    min-width: 0;
}
.alumno-nombre {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.alumno-meta-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(140px, 1fr));
    gap: 0.3rem 1rem;
}
.meta-item {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.72rem;
    color: var(--color-text-secondary);
    overflow: hidden;
}
.meta-item i { font-size: 0.9rem; flex-shrink: 0; color: #748ffc; }
.meta-item span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.estado-mat {
    font-size: 0.65rem;
    font-weight: 600;
    padding: 0.1rem 0.4rem;
    border-radius: 999px;
}
.estado-vigente    { background: #d3f9d8; color: #2b8a3e; }
.estado-retirado   { background: #fff0f6; color: #a61e4d; }
.estado-trasladado { background: #fff3cd; color: #856404; }
.estado-promovido  { background: #e7f5ff; color: #1864ab; }
.estado-reprobado  { background: #ffe3e3; color: #c92a2a; }

/* ── Columna derecha: botón calificaciones ───────────────────── */
.card-right {
    flex-shrink: 0;
}
.btn-ver-notas {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.65rem 1.25rem;
    border-radius: 0.5rem;
    background: #3b5bdb;
    color: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.2s;
}
.btn-ver-notas:hover { background: #2f4ac0; color: #fff; }
.btn-ver-notas i { font-size: 1rem; }

/* ── Estados vacíos ───────────────────────────────── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1;
    color: var(--color-text-secondary);
    text-align: center;
}
.empty-state i { font-size: 2.8rem; margin-bottom: 0.6rem; color: var(--color-border); }
.empty-state p  { font-size: 0.84rem; }
.empty-state small { font-size: 0.72rem; margin-top: 0.25rem; }

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 900px) {
    .alumno-meta-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0 !important;
        width: 100% !important;
        padding: 12px;
    }
    .sidebar { display: none; }
    .tutor-page { height: calc(100vh - 24px); }
    .alumnos-toolbar { flex-direction: column; }
    .alumnos-toolbar select,
    .search-wrapper { width: 100%; }

    .alumno-card {
        grid-template-columns: 1fr;
        justify-items: stretch;
        text-align: left;
        gap: 0.75rem;
        padding: 1rem;
    }
    .card-left {
        flex-direction: row;
        justify-content: flex-start;
        gap: 0.6rem;
    }
    .alumno-meta-grid { grid-template-columns: 1fr 1fr; }
    .card-right { width: 100%; }
    .btn-ver-notas { width: 100%; }
}

@media (max-width: 480px) {
    .alumno-meta-grid { grid-template-columns: 1fr; }
}
</style>

<script>
function filtrarAlumnos() {
    const texto     = document.getElementById('buscar-alumno').value.toLowerCase().trim();
    const curso     = document.getElementById('filtro-curso').value;
    const principal = document.getElementById('filtro-principal').value;
    const cards     = document.querySelectorAll('.alumno-card');
    let visibles    = 0;

    cards.forEach(card => {
        const nombre    = card.dataset.nombre  || '';
        const cedula    = card.dataset.cedula  || '';
        const cursoCrd  = card.dataset.curso   || '';
        const esPrinc   = card.dataset.principal || '';

        const coincideTexto = !texto    || nombre.includes(texto) || cedula.includes(texto);
        const coincideCurso = !curso    || cursoCrd === curso;
        const coincidePrinc = !principal || esPrinc === principal;

        const ok = coincideTexto && coincideCurso && coincidePrinc;
        card.style.display = ok ? '' : 'none';
        if (ok) visibles++;
    });

    const empty = document.getElementById('empty-state');
    if (empty) empty.style.display = visibles === 0 ? 'flex' : 'none';
}
</script>

<?php include $ruta . "tutor/includes/footer.php"; ?>
