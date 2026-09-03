<?php

    $carpeta="../backups/";

    if(isset($_GET["resumen"])){

    $archivos=glob($carpeta."*.sql");

    $total=0;

    $ultimo="-";

    foreach($archivos as $a){

        $total+=filesize($a);

    }

    if(count($archivos)>0){

        usort($archivos,function($a,$b){

            return filemtime($b)-filemtime($a);

        });

        $ultimo=date("d/m/Y H:i",filemtime($archivos[0]));

    }

    echo json_encode([

        "ultimo"=>$ultimo,

        "cantidad"=>count($archivos),

        "espacio"=>number_format($total/1024/1024,2)." MB"

    ]);

    exit;

    }

    $data=[];

    $archivos=glob($carpeta."*.sql");

    usort($archivos,function($a,$b){

        return filemtime($b)-filemtime($a);

    });

    $i=1;

    foreach($archivos as $archivo){

    $nombre=basename($archivo);

    $data[]=[

        "id"=>$i++,

        "archivo"=>$nombre,

        "fecha"=>date("d/m/Y H:i",filemtime($archivo)),

        "tamano"=>number_format(filesize($archivo)/1024/1024,2)." MB",

        "acciones"=>' 
            <a href="../backups/'.$nombre.'" download class="btn btn-primary btn-sm">
                <i class="bi bi-download"></i>
            </a>

            <button
                class="btn btn-danger btn-sm btnEliminarBackup"
                data-archivo="'.$nombre.'">

                <i class="bi bi-trash"></i>

            </button>'

        ];

    }

    

    echo json_encode([

        "data"=>$data

    ]);