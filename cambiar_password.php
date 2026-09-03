<?php
require_once "servicios/conexion.php";
$alert = '';
$mostrar_form = false;
$origen = isset($_GET['origen']) ? strtolower(limpiar_cadena($_GET['origen'])) : 'administracion';
// Soportar orígenes: administracion, docente, tutor, forms
if (!in_array($origen, ['administracion', 'docente', 'tutor', 'forms'], true)) {
    $origen = 'administracion';
}

// 1. VALIDAR SI LLEGAN EL ID Y EL TOKEN POR LA URL (GET)
$redirect_map = [
    'docente' => 'Docente/index.php',
    'tutor'   => 'tutor/index.php',
    'forms'   => 'Administracion/index.php',
    'administracion' => 'Administracion/index.php'
];

if (empty($_GET['id']) || empty($_GET['token'])) {
    $dest = isset($redirect_map[$origen]) ? $redirect_map[$origen] : $redirect_map['administracion'];
    header('Location: ' . $dest);
    exit;
}

$id_user = (int) limpiar_cadena($_GET['id']);
$token   = limpiar_cadena($_GET['token']);

// 2. VERIFICAR SI EL TOKEN ES VÁLIDO EN LA BD
// Buscamos usuario con ese ID, ese TOKEN y que la fecha de expiración sea MAYOR a la actual (NOW)
$sql = "SELECT idUsuario FROM usuarios WHERE idUsuario = $id_user AND token = '$token' AND token_expira > NOW()";
$query_user = buscar_datos($sql);

if ($query_user) {
    $mostrar_form = true; // El token es correcto, mostramos el formulario
} else {
        $recuperar_url = 'recuperar_clave.php?origen=' . $origen;
        $alert = '<div class="alert alert-danger text-center">'
                     . '<h4><i class="bi bi-x-circle"></i> Enlace no válido</h4>'
                     . '<p>El enlace ha caducado o es incorrecto.</p>'
                     . '<a href="' . $recuperar_url . '" class="btn btn-danger">Solicitar nuevo</a>'
                     . '</div>';
}

// 3. PROCESAR EL CAMBIO DE CONTRASEÑA (POST)
if (!empty($_POST)) {
    $pass_nuevo = $_POST['clave'];
    $pass_conf  = $_POST['confirmar_clave'];

    if (empty($pass_nuevo) || empty($pass_conf)) {
        $alert = '<div class="alert alert-warning">Todos los campos son obligatorios.</div>';
    } elseif ($pass_nuevo != $pass_conf) {
        $alert = '<div class="alert alert-warning">Las contraseñas no coinciden.</div>';
    } else {
        // Todo OK: Encriptamos y actualizamos
        $pass_hash = password_hash($pass_nuevo, PASSWORD_BCRYPT);
        
        // Actualizamos la clave Y BORRAMOS EL TOKEN para que este enlace no se pueda usar dos veces
        $sql_update = "UPDATE usuarios SET password = '$pass_hash', token = NULL, token_expira = NULL WHERE idUsuario = $id_user";
        
        if (actualizar_datos($sql_update)) {
            $login_map = [
                'docente' => 'Docente/index.php',
                'tutor' => 'tutor/index.php',
                'forms' => 'Administracion/index.php',
                'administracion' => 'Administracion/index.php'
            ];
            $login_url = isset($login_map[$origen]) ? $login_map[$origen] : $login_map['administracion'];

            $alert = '<div class="alert alert-success text-center">'
                   . '<h4><i class="bi bi-check-circle"></i> ¡Contraseña Restablecida!</h4>'
                   . '<p>Su contraseña ha sido actualizada correctamente.</p>'
                   . '<a href="' . $login_url . '" class="btn btn-primary w-100">Iniciar Sesión</a>'
                   . '</div>';
            $mostrar_form = false; // Ocultamos el form para que solo vea el botón de login
        } else {
            $alert = '<div class="alert alert-danger">Error al actualizar la contraseña.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña</title>
    <link rel="icon" href="img/recuperar.png">
    <link href="bt/bootstrap.min.css" rel="stylesheet">
    <link href="bt-icons/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-pass { 
            width: 100%; 
            max-width: 420px; 
            border: none; 
            border-radius: 12px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .card-header { 
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white; 
            padding: 2rem 1.5rem; 
            text-align: center; 
        }
        .card-header h4 { 
            font-weight: 600;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
        }
        .card-body { 
            padding: 2rem; 
            background: white;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            font-weight: 600;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .btn-danger {
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-danger:hover {
            transform: translateY(-2px);
        }
        .btn-primary {
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        .alert { 
            border: none;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

    <div class="card card-pass">
        <div class="card-header">
            <h4 class="mb-0"><i class="bi bi-lock-fill me-2"></i>Restablecer Contraseña</h4>
        </div>
        <div class="card-body">
            
            <?php echo $alert; ?>

            <?php if ($mostrar_form): ?>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="clave" class="form-label">
                        <i class="bi bi-shield-lock me-2"></i>Nueva Contraseña
                    </label>
                    <input type="password" class="form-control" name="clave" id="clave" placeholder="Ingrese su nueva contraseña" required>
                </div>

                <div class="mb-4">
                    <label for="confirmar_clave" class="form-label">
                        <i class="bi bi-shield-check me-2"></i>Confirmar Contraseña
                    </label>
                    <input type="password" class="form-control" name="confirmar_clave" id="confirmar_clave" placeholder="Confirme su contraseña" required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Guardar Contraseña
                    </button>
                </div>
            </form>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>