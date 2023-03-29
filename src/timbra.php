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

    private function get_data_pem(string $ruta_cer_pem, string $ruta_key_pem){
        $valida = $this->valida_ruta(file: $ruta_key_pem);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar ruta_key_pem',data:  $valida);
        }
        $valida = $this->valida_ruta(file: $ruta_cer_pem);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar ruta_cer_pem',data:  $valida);
        }

        $pems = $this->pems(ruta_cer_pem: $ruta_cer_pem, ruta_key_pem: $ruta_key_pem);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar pems',data:  $pems);
        }
        return $pems;
    }

    private function pems(string $ruta_cer_pem, string $ruta_key_pem): stdClass
    {
        $key_pem = file_get_contents($ruta_key_pem);
        $cer_pem = file_get_contents($ruta_cer_pem);

        $data = new stdClass();
        $data->key = $key_pem;
        $data->cer = $cer_pem;
        return $data;

    }

    public function timbra(string $contenido_xml, string $id_comprobante = '', string $ruta_cer_pem = '',
                           string $ruta_key_pem = '', string $pac_prov=''): array|stdClass
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


        try {
            if($aplica_params) {
                $params = array();
                $params['usuarioIntegrador'] = $usuario_int;
                $params['xmlComprobanteBase64'] = $base64Comprobante;
                $params['idComprobante'] = $id_comprobante;
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
            $result = $response->TimbraCFDIResult->anyType;
        }
        else{

            $pems = $this->get_data_pem(ruta_cer_pem: $ruta_cer_pem,ruta_key_pem:  $ruta_key_pem);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar pems',data:  $pems);
            }


            $response = $client->timbrarJSON3($usuario_int, $base64Comprobante, $pems->key, $pems->cer);


            $result = (array)$response;

            $cod_error = 0;
            if((int)$result['code'] !== 200){
                $cod_error = $result['code'];

            }
            if((int)$cod_error === 307){
                $cod_error = 0;

            }

            if((int)$cod_error === 0){
                $data_json = json_decode($response->data);

                $result[0] = 'Exito';
                $result[1] = 'Exito';
                $result[2] = 'Exito';
                $result[6] = $cod_error;
                $result[7] = '';
                $result[8] = '';
                $result[4] = $data_json->CodigoQR;
                $result[5] = $data_json->CadenaOriginalSAT;
                $result[3] = $data_json->XML;

            }


        }

        $xml_sellado = $result[3];

        $lee_xml = (new xml())->get_datos_xml(xml_data: $xml_sellado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos',data:  $lee_xml);
        }


        $tipo_resultado = $result[0];
        $cod_mensaje = $result[1];
        $mensaje = $result[2];
        $cod_error = $result[6];
        $mensaje_error = $result[7];
        $salida = $result[8];
        $qr_code = $result[4];
        $txt = $result[5];


        if((int)$cod_error !==0){
            return $this->error->error(mensaje: 'Error al timbrar',data: $result);
        }


        $uuid = $lee_xml['tfd']['UUID'];

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
        $data->uuid = $uuid;
        $data->xml_sellado = $xml_sellado;


        return $data;


    }

    private function valida_ruta(string $file): bool|array
    {
        $file = trim($file);
        if($file === ''){
            return $this->error->error(mensaje: 'Error file esta vacio',data: $file);
        }
        if(!file_exists($file)){
            return $this->error->error(mensaje: 'Error file no existe',data: $file);
        }
        return true;
    }
}
