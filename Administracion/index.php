<?php
    $ruta = '../';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>

    
    <link rel="stylesheet" href="<?php echo $ruta; ?>css/tailwind-lite.css">
    <link rel="stylesheet" href="<?php echo $ruta; ?>bt-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $ruta; ?>bt/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $ruta; ?>alertify/alertify.min.css">
    <link rel="stylesheet" href="<?php echo $ruta; ?>alertify/themes/default.min.css">
    <script src="<?php echo $ruta; ?>bt/bootstrap.min.js"></script>

    <style>
        @keyframes shine {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .button-bg {
            background: conic-gradient(from 0deg, #00F5FF, #FF00C7, #FFD700, #00FF85, #8A2BE2, #00F5FF);
            background-size: 300% 300%;
            animation: shine 4s ease-out infinite;
        }

        body.BKG {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            position: relative;
            padding: 1rem;
        }

        .desvanecer {
            position: absolute;
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 50;
            transition: opacity .6s ease, transform .6s ease;
            opacity: 1;
        }

        .error-msg {
            background-color: #b91c1c; /* rojo oscuro */
            color: #ffffff;
            border: 1px solid #7f1d1d;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            width: 20rem;
            max-width: 90vw;
            text-align: center;
        }

        .desvanecer.fade-out {
            opacity: 0;
            transform: translateX(-50%) translateY(-10px);
        }
    </style>
</head>

<body class="BKG">
    <?php 
        $mensaje_error = '';
        if (isset($_GET['error'])) {
            if ($_GET['error'] === 'sesion_expirada') {
                $mensaje_error = 'Su sesión ha expirado por inactividad. Inicie sesión nuevamente.';
            } else {
                $mensaje_error = 'Credenciales incorrectas. Intente de nuevo.';
            }
        } elseif (isset($_GET['logout'])) {
            $mensaje_error = 'Ha cerrado sesión correctamente.';
        }
    ?>
    <?php if ($mensaje_error): ?>
        <div id="error-msg" class="error-msg desvanecer">
            <?php echo $mensaje_error; ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $ruta; ?>Administracion/login.php" method="POST"
        class="bg-white rounded-lg shadow-xl text-sm text-gray-500 border border-gray-200 p-8 py-12 w-80 sm:w-[352px]">
        <p class="text-2xl font-medium text-center">
            <span class="text-indigo-500">Iniciar</span> Sesión
            <h6 align="center">Santa Teresita - Administración</h6>
        </p>

        <div class="mt-4">
            <label class="block"><i class="bi bi-person"></i> Correo</label>
            <input type="email" name="correo" placeholder="escriba su correo" required
                class="border border-gray-200 rounded w-full p-2 mt-1 outline-indigo-500">
        </div>

        <div class="mt-4">
            <label class="block"><i class="bi bi-lock"></i> Contraseña</label>
            <input type="password" name="password" placeholder="escriba su contraseña" required
                class="border border-gray-200 rounded w-full p-2 mt-1 outline-indigo-500">
        </div>

        <p class="mt-4">
            ¿Olvidó su contraseña?
            <a href="../recuperar_clave.php?origen=administracion" class="text-indigo-500">Haga clic aquí</a>
        </p>

        <div class="button-bg rounded-full p-0.5 hover:scale-105 transition duration-300 active:scale-100">
            <button type="submit" class="px-8 text-sm py-2.5 text-black rounded-full font-medium bg-white w-full">
                Ingresar
            </button>
        </div>
    </form>

    <script>
    function showError(text) {
        let msg = document.getElementById('error-msg');
        if (!msg) {
            msg = document.createElement('div');
            msg.id = 'error-msg';
            msg.className = 'error-msg desvanecer';
            document.body.appendChild(msg);
        }
        msg.textContent = text;
        msg.classList.remove('fade-out');
        if (msg._timeout) clearTimeout(msg._timeout);
        msg._timeout = setTimeout(function() {
            msg.classList.add('fade-out');
            setTimeout(function() { if (msg.parentNode) msg.parentNode.removeChild(msg); }, 700);
        }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const data = new FormData(form);
            try {
                const resp = await fetch('<?php echo $ruta; ?>Administracion/login.php', {
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    body: data
                });
                const json = await resp.json();
                if (json.success) {
                    window.location = json.redirect;
                } else {
                    showError(json.message || '!Credenciales incorrectas!');
                }
            } catch (err) {
                showError('Error de comunicación.');
            }
        });

        //11:05
        if (window.location.search.includes('error=1')) {
            history.replaceState(null, '', window.location.pathname);
        }

        if (window.location.search.includes('error=sesion_expirada')) {
            if (window.alertify) {
                alertify.error('Sesión expirada, inicie nuevamente');
            } else {
                showError('Su sesión ha expirado por inactividad. Inicie sesión nuevamente.');
            }
            history.replaceState(null, '', window.location.pathname);
        }
    });
    </script>
    <script src="<?php echo $ruta; ?>alertify/alertify.min.js"></script>
</body>

</html>