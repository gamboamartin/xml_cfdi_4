<?php

use gamboamartin\errores\errores;
use gamboamartin\xml_cfdi_4\cfdis;

error_reporting(E_ALL);
ini_set('display_errors', '1');
setlocale(LC_ALL, 'es_MX.utf8');
date_default_timezone_set('America/Mexico_City');
set_time_limit(60000);
ini_set('memory_limit', '-1');
ini_set('upload_max_filesize', '2048M');
ini_set('post_max_size', '2048M');
include 'vendor/autoload.php';


$tipo_de_comprobante = $_GET['tipo_de_comprobante'];

if(!isset($_GET['tipo_de_comprobante'])){
    $fix = 'Debe existir en tu llamada a la aplicacion por GET el tipo de comprobante';
    $fix .= ' Ej https://xml-cfdi-4.ivitec.com.mx/index.php?tipo_de_comprobante=P';
    $error = (new errores())->error(mensaje:'Error no existe el tipo de comprobante', data: $_GET, fix: $fix);
    ob_clean();
    header('Content-Type: application/json');
    try {
        $data_error =  json_encode($error, JSON_THROW_ON_ERROR);
    }
    catch (Throwable $e){
        $error = (new errores())->error(mensaje:'Error al generar json', data: $e, fix: $fix);
        die('Error');
    }
    echo $data_error;

    exit;
}

if(!isset($_POST['comprobante'])){
    $error = (new errores())->error(mensaje:'Error no existe comprobante en POST', data: $_POST);
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($error, JSON_THROW_ON_ERROR);
    exit;
}

if($tipo_de_comprobante === 'P') {

    if (!isset($_POST['pagos'])) {
        $error = (new errores())->error(mensaje: 'Error no existe pagos en POST', data: $_POST);
        ob_clean();
        header('Content-Type: application/json');
        try {
            echo json_encode($error, JSON_THROW_ON_ERROR);
        }
        catch (Throwable $e){
            echo 'Error';
        }
        exit;
    }
}
if(!isset($_POST['emisor'])){
    $error = (new errores())->error(mensaje:'Error no existe emisor en POST', data: $_POST);
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($error, JSON_THROW_ON_ERROR);
    exit;
}
if(!isset($_POST['receptor'])){
    $error = (new errores())->error(mensaje:'Error no existe receptor en POST', data: $_POST);
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($error, JSON_THROW_ON_ERROR);
    exit;
}



$tipos_de_comprobante = array('P','I');
if(!in_array($tipo_de_comprobante, $tipos_de_comprobante, true)){
    $error = (new errores())->error(mensaje:'Error tipo_de_comprobante_invalido',
        data: $tipos_de_comprobante);
    header('Content-Type: application/json');
    echo json_encode($error, JSON_THROW_ON_ERROR);
    exit;
}

$comprobante = $_POST['comprobante'];
$emisor = $_POST['emisor'];
$receptor = $_POST['receptor'];


if($tipo_de_comprobante === 'P'){

    $pagos = $_POST['pagos'];

    $cfdi = (new cfdis())->complemento_pago($comprobante, $emisor, $pagos, $receptor);
    if(errores::$error){
        $error = (new errores())->error(mensaje:'Error al generar cfdi', data: $cfdi);
        ob_clean();
        header('Content-Type: application/json');
        try {
            $data_json =     json_encode($error, JSON_THROW_ON_ERROR);
        }
        catch (Throwable $e){
            $error = (new errores())->error(mensaje:'Error al generar json', data: $e);
            print_r($error);
            die('Error');
        }
        echo $data_json;
        exit;
    }
    ob_clean();
    header('Content-Type: application/json');
    try {
        $data_json = json_encode(array('xml' => $cfdi), JSON_THROW_ON_ERROR);
    }
    catch (Throwable $e){
        $error = (new errores())->error(mensaje:'Error al generar json', data: $e);
        print_r($error);
        die('Error');
    }
    echo $data_json;
    exit;

}


