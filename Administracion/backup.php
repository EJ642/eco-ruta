<?php
    $ruta = "../";
    include __DIR__ . '/includes/header.php';
    require_once __DIR__ . '/../servicios/conexion.php';

    $esDirector = !empty($_SESSION['active']) && ($_SESSION['rol'] ?? '') === 'Director';
    $mensajeNoAutorizado = 'No autorizado. Solo el Director puede administrar los respaldos.';
    $usuario_id = (int)($_SESSION['usuario_id'] ?? 0);
?>

<link rel="stylesheet" href="<?= $ruta ?>dt/dataTables.bootstrap5.min.css">



<?php if (!$esDirector): ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($mensajeNoAutorizado, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </div>

    <script src="<?php echo $ruta; ?>alertify/alertify.min.js"></script>

    <script>
        alertify.error(<?= json_encode($mensajeNoAutorizado, JSON_UNESCAPED_UNICODE) ?>);
        // setTimeout(function () {
        //     window.location.href = "<?= $ruta ?>Administracion/menu.php";
        // }, 1200);
    </script>

    <?php include __DIR__ . '/includes/footer.php'; exit; ?>
<?php endif; ?>

<style>
    .page-title{
        font-weight:700;
        color:#2c3e50;
    }

    .card-info{
        border:none;
        border-radius:15px;
        overflow:hidden;
        color:white;
        transition:.25s;
    }

    .card-info:hover{
        transform:translateY(-4px);
        box-shadow:0 10px 20px rgba(0,0,0,.15);
    }

    .bg1{
        background:linear-gradient(135deg,#2563eb,#1d4ed8);
    }

    .bg2{
        background:linear-gradient(135deg,#10b981,#059669);
    }

    .bg3{
        background:linear-gradient(135deg,#f59e0b,#d97706);
    }

    .card-info i{
        font-size:40px;
        opacity:.25;
        position:absolute;
        right:20px;
        bottom:15px;
    }

    .card-header-custom{
        background:linear-gradient(90deg,#0d6efd,#3b82f6);
        color:white;
        font-weight:bold;
    }

    .btn-backup{
        border-radius:10px;
    }

    .table td{
        vertical-align:middle;
    }
</style>

    <div class="main-content">

    <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

    <h3 class="page-title">

    <i class="bi bi-database-fill"></i>

        Respaldos de Base de Datos

    </h3>

    <button class="btn btn-success btn-backup" id="btnBackup">

        <i class="bi bi-cloud-arrow-down-fill"></i>

            Crear Respaldo

    </button>

    </div>

    <div class="row mb-4">

        <div class="col-md-4">

            <div class="card card-info bg1 position-relative">

                <div class="card-body">

                    <h6>Último respaldo</h6>

                    <h4 id="ultimoBackup">-</h4>

                    <i class="bi bi-clock-history"></i>

                </div>

        </div>

    </div>

    <div class="col-md-4">

    <div class="card card-info bg2 position-relative">

    <div class="card-body">

    <h6>Total de respaldos</h6>

    <h4 id="cantidadBackup">0</h4>

    <i class="bi bi-hdd-stack-fill"></i>

    </div>

    </div>

    </div>

    <div class="col-md-4">

    <div class="card card-info bg3 position-relative">

    <div class="card-body">

    <h6>Espacio utilizado</h6>

    <h4 id="espacioBackup">0 MB</h4>

    <i class="bi bi-device-hdd-fill"></i>

    </div>

    </div>

    </div>

    </div>

    <div class="card shadow">

    <div class="card-header card-header-custom">

    <i class="bi bi-folder-fill"></i>

    Historial de Respaldos

    </div>

    <div class="card-body">

    <table class="table table-bordered table-hover" id="tablaBackup">

        <thead>

            <tr>

                <th>#</th>

                <th>Archivo</th>

                <th>Fecha</th>

                <th>Tamaño</th>

                <th>Acciones</th>

            </tr>

        </thead>

        <tbody>

        </tbody>

    </table>

    </div>

    </div>

    </div>

    </div>

    <script src="<?php echo $ruta; ?>dt/jquery-3.7.0.js"></script>
    <script src="<?php echo $ruta; ?>dt/jquery.dataTables.min.js"></script>
    <script src="<?php echo $ruta; ?>dt/dataTables.bootstrap5.min.js"></script>
    <script src="<?php echo $ruta; ?>alertify/alertify.min.js"></script>

    <script>
        

        let tabla;

        $(function(){

            tabla=$("#tablaBackup").DataTable({

                ajax:"listar_respaldos.php",

                columns:[

                    {data:"id",
                        className:"text-center"},

                    {data:"archivo"},

                    {data:"fecha",
                        className:"text-center"},

                    {data:"tamano",
                        className:"text-center"},

                    {data:"acciones",
                        className:"text-center"
                    }

            ],

            language:{
                url:"<?= $ruta ?>dt/es-ES.json"
            },

            pageLength:10

        });

    
    $("#btnBackup").click(function () {

        alertify.confirm(
            "¿Crear un respaldo?",
            "Se generará una copia de seguridad de la base de datos.",
            function () {
                alertify.success("Generando respaldo...");

                $.ajax({
                    url: "../servicios/BDbackup.php",
                    type: "POST",
                    dataType: "json",
                    success: function (respuesta) {
                        if (respuesta.status) {
                            alertify.success(respuesta.msg || "Respaldo creado");
                            $("#tablaBackup").DataTable().ajax.reload();
                            actualizarResumen();
                            setTimeout(function () {
                                window.location.reload();
                            }, 1000);
                        } else {
                            alertify.error(respuesta.msg || "No se pudo crear el respaldo");
                        }
                    },
                    error: function () {
                        alertify.error("No fue posible comunicarse con el servidor.");
                    }
                });
            },
            function () {
                alertify.error("Cancelado");
            }
        ).set('labels', { ok: 'Sí, crear', cancel: 'Cancelar' });

    });


    function actualizarResumen(){

        $.getJSON("listar_respaldos.php?resumen=1", function(r){

            $("#ultimoBackup").text(r.ultimo);

            $("#cantidadBackup").text(r.cantidad);

            $("#espacioBackup").text(r.espacio);

        });

    }

    actualizarResumen();

    });

    $(document).on("click",".btnEliminarBackup",function(){

    let archivo=$(this).data("archivo");

    alertify.confirm(

        "Eliminar respaldo",

        "¿Desea eliminar este respaldo?<br><br><b>"+archivo+"</b>",

            function(){

                $.ajax({

                    url:"eliminar_backup.php",

                    type:"POST",

                    dataType:"json",

                    data:{
                        archivo:archivo
                    },

                    success:function(r){

                        if(r.status){

                            alertify.success(r.msg);

                            tabla.ajax.reload(null,false);

                            actualizarResumen();

                        }else{

                            alertify.error(r.msg);

                        }

                    },

                    error:function(){

                        alertify.error("No fue posible eliminar el respaldo.");

                    }

                });

            },

            function(){

                alertify.message("Operación cancelada");

            }

        ).set({

            labels:{
                ok:"Eliminar",
                cancel:"Cancelar"
            }

        });

    });

    </script>

    <?php
        include __DIR__ . '/includes/footer.php';
    ?>