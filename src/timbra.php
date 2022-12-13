<?php
namespace gamboamartin\xml_cfdi_4;
use config\pac;
use gamboamartin\errores\errores;
use SoapClient;

use stdClass;
use Throwable;

class timbra{
    private errores $error;
    private validacion $valida;

    public function __construct(){
        $this->error = new errores();
        $this->valida = new validacion();

    }

    public function timbra(string $contenido_xml, string $id_comprobante = ''): array|stdClass
    {

        $contenido_xml = trim($contenido_xml);
        if($contenido_xml === ''){
            return $this->error->error('xml no puede venir vacio',$contenido_xml);
        }

        $pac = new pac();
        if($id_comprobante === ''){
            $id_comprobante = (string)time();
        }

        $keys = array('ruta_pac','usuario_integrador');
        $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $pac);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar pac',data: $valida);
        }


        $ws= $pac->ruta_pac;
        $usuario_int = $pac->usuario_integrador;
        $base64Comprobante = base64_encode($contenido_xml);

        $params = array();
        $params['usuarioIntegrador'] = $usuario_int;
        $params['xmlComprobanteBase64'] = $base64Comprobante;
        $params['idComprobante'] = $id_comprobante;

        try {
            $client = new SoapClient($ws,$params);
        }
        catch (Throwable $e){
            return $this->error->error('Error al timbrar',array($e,htmlentities($contenido_xml)));
        }

        $response = $client->__soapCall('TimbraCFDI', array('parameters' => $params));
        $result = $response->TimbraCFDIResult->anyType;
        $tipo_resultado = $result[0];
        $cod_mensaje = $result[1];
        $mensaje = $result[2];
        $cod_error = $result[6];
        $mensaje_error = $result[7];
        $salida = $result[8];
        $xml_sellado = $result[3];
        $qr_code = $result[4];
        $txt = $result[5];
        $data_uuid = $result[8];

        if((int)$cod_error !==0){
            return $this->error->error(mensaje: 'Error al timbrar',data: $result);
        }

        $uuid = '';
        $data_uuid_json = json_decode($data_uuid);
        if(isset($data_uuid_json[0]->Key)){
            if($data_uuid_json[0]->Key === 'UUID'){
                $uuid = $data_uuid_json[0]->Value;
            }
        }

        $data = new stdClass();
        $data->response = $response;
        $data->result = $result;
        $data->tipo_resultado = $tipo_resultado;
        $data->cod_mensaje = $cod_mensaje;
        $data->mensaje = $mensaje;
        $data->cod_error = $cod_error;
        $data->mensaje_error = $mensaje_error;
        $data->salida = $salida;
        $data->qr_code = $qr_code;
        $data->txt = $txt;
        $data->data_uuid = $data_uuid;
        $data->uuid = $uuid;
        $data->data_uuid_json = $data_uuid_json;
        $data->xml_sellado = $xml_sellado;


        return $data;


    }
}