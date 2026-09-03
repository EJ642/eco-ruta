</div></div><!-- /.container-fluid /.main-content -->
    </div><!-- /.page-content -->
</div><!-- /.eco-admin-shell -->

<script src="<?php echo $basePath ?? '/EcoRuta/'; ?>bt/bootstrap.bundle.min.js"></script>
<script src="<?php echo $basePath ?? '/EcoRuta/'; ?>alertify/alertify.min.js"></script>
<script>
    const rolActual = <?php echo json_encode($_SESSION['rol'] ?? ''); ?>;
    const tokenActual = <?php echo json_encode($_SESSION['token'] ?? ''); ?>;
    const timeoutMs = 30 * 60 * 1000;
    let lastActivity = Date.now();
    let currentToken = tokenActual;

    function registrarActividad() {
        lastActivity = Date.now();
    }

    function cerrarSesionExpirada() {
        alertify.error('Sesión expirada, inicie nuevamente');
        setTimeout(() => {
            window.location.href = '/EcoRuta/index.php?error=sesion_expirada';
        }, 800);
    }

    function refrescarToken() {
        if (Date.now() - lastActivity > timeoutMs) {
            cerrarSesionExpirada();
            return;
        }

        fetch('/EcoRuta/refresh_token.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                usuario_id: <?php echo json_encode($_SESSION['usuario_id'] ?? 0); ?>,
                token: currentToken,
                rol: rolActual
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.new_token) {
                currentToken = data.new_token;
                return;
            }
            cerrarSesionExpirada();
        })
        .catch(() => {
            cerrarSesionExpirada();
        });
    }

    document.addEventListener('mousemove', registrarActividad);
    document.addEventListener('keypress', registrarActividad);
    document.addEventListener('click', registrarActividad);

    const sidebar = document.getElementById('sidebar');
    const menuButton = document.getElementById('menu-btn');
    const mobileMenuButton = document.getElementById('mobile-menu-btn');
    const isMobile = () => window.matchMedia('(max-width: 920px)').matches;

    function cambiarMenuMovil(abrir) {
        sidebar.classList.toggle('mobile-nav-open', abrir);
        mobileMenuButton?.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    }

    if (menuButton && sidebar) {
        menuButton.addEventListener('click', () => {
            if (isMobile()) {
                cambiarMenuMovil(!sidebar.classList.contains('mobile-nav-open'));
                return;
            }

            document.body.classList.toggle('sidebar-minimized');
            menuButton.setAttribute(
                'aria-label',
                document.body.classList.contains('sidebar-minimized')
                    ? 'Expandir barra lateral'
                    : 'Comprimir barra lateral'
            );
            menuButton.setAttribute(
                'aria-pressed',
                document.body.classList.contains('sidebar-minimized') ? 'true' : 'false'
            );
        });
    }

    mobileMenuButton?.addEventListener('click', () => {
        cambiarMenuMovil(!sidebar.classList.contains('mobile-nav-open'));
    });

    document.addEventListener('click', (event) => {
        if (!isMobile() || !sidebar?.classList.contains('mobile-nav-open')) {
            return;
        }

        if (!sidebar.contains(event.target) && !mobileMenuButton?.contains(event.target)) {
            cambiarMenuMovil(false);
        }
    });

    sidebar?.querySelectorAll('.menu-link, .sub-menu-item').forEach((link) => {
        link.addEventListener('click', () => {
            if (isMobile() && !link.classList.contains('menu-link')) {
                cambiarMenuMovil(false);
            }
        });
    });

    document.querySelectorAll('.exit-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            alertify.confirm('Cerrar sesión', '¿Está seguro que desea salir del sistema?',
                function () {
                    window.location.href = '/EcoRuta/salir.php';
                },
                function () {
                    alertify.error('Cancelado');
                }
            ).set('labels', { ok: 'Sí, salir', cancel: 'Cancelar' });
        });
    });

    document.querySelectorAll('.menu-item-dropdown > .menu-link').forEach((menuLink) => {
        menuLink.addEventListener('click', (event) => {
            event.preventDefault();
            const menuItem = menuLink.parentElement;
            const isOpen = menuItem.classList.toggle('is-open');
            menuLink.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    setInterval(refrescarToken, 5 * 60 * 1000);
    setInterval(() => {
        if (Date.now() - lastActivity > timeoutMs) {
            cerrarSesionExpirada();
        }
    }, 1000);
</script>
</body>
</html>
