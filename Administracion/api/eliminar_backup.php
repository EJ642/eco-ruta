<?php

header('Content-Type: application/json');

$response=[
    "status"=>false,
    "msg"=>""
];

if(empty($_POST["archivo"])){

    $response["msg"]="Archivo no recibido.";

    echo json_encode($response);

    exit;

}

$archivo=basename($_POST["archivo"]);

$ruta=__DIR__."/../backups/".$archivo;

if(!file_exists($ruta)){

    $response["msg"]="El archivo no existe.";

    echo json_encode($response);

    exit;

}

if(unlink($ruta)){

    $response["status"]=true;

    $response["msg"]="Respaldo eliminado correctamente.";

}else{

    $response["msg"]="No fue posible eliminar el respaldo.";

}

echo json_encode($response);