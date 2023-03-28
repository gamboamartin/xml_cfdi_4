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

    final public function cancela(string $motivo_cancelacion, string $rfc_emisor, string $uuid, string $pac_prov='', $uuid_sustitucion = ''){
        $pac = new pac();
        $keys = array('ruta_pac','usuario_integrador');
        $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $pac);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar pac',data: $valida);
        }

        if($motivo_cancelacion === '01'){
            if($uuid_sustitucion === ''){
                return $this->error->error(mensaje: 'Error uuid_sustitucion debe existir',data: $uuid_sustitucion);
            }
        }

        $ws= $pac->ruta_pac;
        $usuario_int = $pac->usuario_integrador;
        $timbra_rs = 'CancelaCFDI40';
        $aplica_params = true;
        $tipo_entrada = 'xml';

        if($pac_prov!==''){
            $ws= $pac->pac->$pac_prov->ruta;
            $usuario_int = $pac->pac->$pac_prov->pass;
            $timbra_rs = $pac->pac->$pac_prov->timbra_rs;
            $aplica_params = $pac->pac->$pac_prov->aplica_params;
            $tipo_entrada = $pac->pac->$pac_prov->tipo_entrada;
        }
        $params = array();

        $params['usuarioIntegrador'] = $usuario_int;
        $params['rfcEmisor'] = $rfc_emisor;
        //$params['rfcReceptor'] = $rfc_receptor;
        $params['folioUUID'] = strtoupper(trim($uuid));
        $params['motivoCancelacion'] = $motivo_cancelacion;
        $params['folioUUIDSustitucion'] = $uuid_sustitucion;

        try {
            if($aplica_params) {
                $client = new SoapClient($ws, $params);
            }
            else{
                $client = new SoapClient($ws);
            }
        }
        catch (Throwable $e){
            return $this->error->error('Error al timbrar',array($e,$params));
        }

        if($aplica_params){
            $response = $client->__soapCall($timbra_rs, array('parameters' => $params));

        }

        $result = $response->CancelaCFDI40Result->anyType;


        $tipo_resultado = $result[0];
        $cod_mensaje = $result[1];
        $mensaje = $result[2];
        $cod_error = $result[6];
        $mensaje_error = $result[7];
        $salida = $result[8];

        if((int)$cod_error !==0){
            return $this->error->error(mensaje: 'Error al timbrar',data: $result);
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


        return $data;
    }

    public function timbra(string $contenido_xml, string $id_comprobante = '', string $pac_prov=''): array|stdClass
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
        $timbra_rs = $pac->timbra_rs;
        $aplica_params = true;
        $tipo_entrada = 'xml';

        if($pac_prov!==''){
            $ws= $pac->pac->$pac_prov->ruta;
            $usuario_int = $pac->pac->$pac_prov->pass;
            $timbra_rs = $pac->pac->$pac_prov->timbra_rs;
            $aplica_params = $pac->pac->$pac_prov->aplica_params;
            $tipo_entrada = $pac->pac->$pac_prov->tipo_entrada;
        }


        $base64Comprobante = base64_encode($contenido_xml);


        $params = array();

        $params['usuarioIntegrador'] = $usuario_int;
        $params['xmlComprobanteBase64'] = $base64Comprobante;
        $params['idComprobante'] = $id_comprobante;

        try {
            if($aplica_params) {
                $client = new SoapClient($ws, $params);
            }
            else{
                $client = new SoapClient($ws);
            }
        }
        catch (Throwable $e){
            return $this->error->error('Error al timbrar',array($e,htmlentities($contenido_xml)));
        }

        if($aplica_params){
            $response = $client->__soapCall($timbra_rs, array('parameters' => $params));
        }
        else{

            $keyPEM = file_get_contents('/var/www/html/xml_cfdi_4/CSD01_AAA010101AAA_key.pem');
            $cerPEM = file_get_contents('/var/www/html/xml_cfdi_4/CSD01_AAA010101AAA_cer.pem');
            $response = $client->timbrarJSON($usuario_int, $base64Comprobante, $keyPEM, $cerPEM);
        }

        //print_r($response);exit;


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
