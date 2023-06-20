<?php
namespace gamboamartin\xml_cfdi_4;
date_default_timezone_set('America/Mexico_City');

use DOMDocument;
use DOMException;
use DOMNode;
use gamboamartin\errores\errores;

use stdClass;
use Throwable;

class xml{
    public DOMDocument $dom;
    public stdClass $cfdi;
    public DOMNode  $xml;
    private validacion $valida;
    private errores $error;


    public function __construct(){
        $this->valida = new validacion();
        $this->error = new errores();
        $this->cfdi = new stdClass();
        $this->cfdi->comprobante = new stdClass();
        $this->cfdi->comprobante->xmlns_xsi = "http://www.w3.org/2001/XMLSchema-instance";
        $this->cfdi->comprobante->xmlns_pago20 = "http://www.sat.gob.mx/Pagos20";
        $this->cfdi->comprobante->xmlns_nomina12 = "http://www.sat.gob.mx/nomina12";
        $this->cfdi->comprobante->xmlns_cfdi = "http://www.sat.gob.mx/cfd/4";
        $this->cfdi->comprobante->moneda = "";
        $this->cfdi->comprobante->total = "0";
        $this->cfdi->comprobante->xsi_schemaLocation = "http://www.sat.gob.mx/cfd/4 ";
        $this->cfdi->comprobante->xsi_schemaLocation .= "http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd ";
        $this->cfdi->comprobante->exportacion = "";
        $this->cfdi->comprobante->tipo_de_comprobante = "";
        $this->cfdi->comprobante->sub_total = 0;
        $this->cfdi->comprobante->lugar_expedicion = "";
        $this->cfdi->comprobante->fecha = "";
        $this->cfdi->comprobante->folio = "";
        $this->cfdi->comprobante->forma_pago = "";
        $this->cfdi->comprobante->metodo_pago = "";
        $this->cfdi->comprobante->serie = "";
        $this->cfdi->comprobante->version = "4.0";
        $this->cfdi->comprobante->namespace = new stdClass();
        $this->cfdi->comprobante->namespace->w3 = 'http://www.w3.org/2000/xmlns/';

        $this->cfdi->emisor = new stdClass();
        $this->cfdi->receptor = new stdClass();
        $this->cfdi->conceptos = array();
        $this->cfdi->impuestos = new stdClass();
        $this->cfdi->relacionados = new stdClass();


        $this->dom = new DOMDocument('1.0', 'utf-8');

    }

    public function cfdi_comprobante(stdClass $comprobante): DOMDocument|array
    {

        $keys = array('tipo_de_comprobante','moneda','total', 'exportacion','sub_total','lugar_expedicion',
            'folio');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar comprobante', data: $valida);
        }

        $fecha_cfdi = (new fechas())->fecha_cfdi(comprobante: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular fecha', data: $fecha_cfdi);
        }

        $this->cfdi->comprobante->fecha = $fecha_cfdi;
        $comprobante->fecha = $fecha_cfdi;


        $comprobante_base = (new dom_xml())->comprobante(comprobante: $comprobante,xml:  $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar cfdi comprobante', data: $comprobante_base);
        }

        $complemento = (new complementos())->aplica_complemento_cfdi_comprobante(comprobante: $comprobante, xml: $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar complementos', data: $complemento);
        }

        return $this->dom;
    }

    public function cfdi_comprobante_v33(stdClass $comprobante): DOMDocument|array
    {

        $keys = array('tipo_de_comprobante','moneda','total','sub_total','lugar_expedicion',
            'folio');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar comprobante', data: $valida);
        }

        $fecha_cfdi = (new fechas())->fecha_cfdi(comprobante: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular fecha', data: $fecha_cfdi);
        }

        $this->cfdi->comprobante->fecha = $fecha_cfdi;
        $comprobante->fecha = $fecha_cfdi;


        $comprobante_base = (new dom_xml())->comprobante_v33(comprobante: $comprobante,xml:  $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar cfdi comprobante', data: $comprobante_base);
        }

        $complemento = (new complementos())->aplica_complemento_cfdi_comprobante(comprobante: $comprobante, xml: $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar complementos', data: $complemento);
        }

        return $this->dom;
    }

    public function cfdi_comprobante_json(stdClass $comprobante, array $json): array
    {

        $keys = array('tipo_de_comprobante','moneda','total', 'exportacion','sub_total','lugar_expedicion',
            'folio','no_certificado');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar comprobante', data: $valida);
        }

        $fecha_cfdi = (new fechas())->fecha_cfdi(comprobante: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular fecha', data: $fecha_cfdi);
        }

        $this->cfdi->comprobante->fecha = $fecha_cfdi;
        $comprobante->fecha = $fecha_cfdi;


        $json = (new dom_xml())->comprobante_json(comprobante: $comprobante, json: $json, xml: $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar cfdi comprobante', data: $json);
        }



        return $json;
    }


    /**
     * Genera los conceptos de un cfdi
     * @param array $conceptos
     * @return bool|string|array
     */
    final public function cfdi_conceptos(array $conceptos): bool|string|array
    {
        if(!isset($this->xml)){
            return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
        }
        if(count($conceptos) === 0){
            return $this->error->error(mensaje: 'Error los conceptos no pueden ir vacios', data: $conceptos);
        }
        try {
            $nodo_conceptos = $this->dom->createElement('cfdi:Conceptos');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento cfdi:Conceptos', data: $e);
        }
        $this->xml->appendChild($nodo_conceptos);

        $elementos_concepto = (new dom_xml())->carga_conceptos(conceptos: $conceptos,
            nodo_conceptos: $nodo_conceptos,xml:  $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar atributos', data: $elementos_concepto);
        }

        return $this->dom->saveXML();
    }

    final public function cfdi_conceptos_json(array $conceptos, array $json):array
    {
        if(count($conceptos) === 0){
            return $this->error->error(mensaje: 'Error los conceptos no pueden ir vacios', data: $conceptos);
        }

        $nodo_conceptos = (new dom_xml())->carga_conceptos_json(conceptos: $conceptos, xml:  $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar atributos', data: $nodo_conceptos);
        }

        $json['Comprobante']['Conceptos'] = $nodo_conceptos;

        return $json;
    }

    /**
     */
    public function cfdi_emisor(stdClass $emisor): DOMDocument|array
    {
        $keys = array('rfc','nombre','regimen_fiscal');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $emisor', data: $valida);
        }

        if(!isset($this->xml)){
            return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
        }

        $data_nodo = (new dom_xml())->nodo(keys: $keys, keys_especial: array(), local_name: 'cfdi:Emisor',
            nodo_key: 'emisor', object:  $emisor,xml:  $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear $emisor', data: $data_nodo);
        }


        return $this->dom;
    }

    public function cfdi_emisor_json(stdClass $emisor, array $json): array
    {
        $keys = array('rfc','nombre','regimen_fiscal');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $emisor', data: $valida);
        }

        $json = (new dom_xml())->nodo_json(json: $json, keys: $keys, keys_especial: array(), local_name: 'Emisor',
            nodo_key: 'emisor', object: $emisor, xml: $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear $emisor', data: $json);
        }

        return $json;
    }

    /**
     */
    public function cfdi_impuestos(stdClass $impuestos): bool|array|string
    {

        $aplica_impuestos = false;
        $aplica_impuestos_trasladados = false;
        $keys = array();
        if(isset($impuestos->traslados)){
            if(count($impuestos->traslados ) > 0){

                $aplica_impuestos_trasladados = true;
                $aplica_impuestos = true;
                $keys[] = 'total_impuestos_trasladados';

            }
        }
        $aplica_impuestos_retenidos = false;
        if(isset($impuestos->retenciones)){
            if(count($impuestos->retenciones ) > 0){
                $aplica_impuestos_retenidos = true;
                $aplica_impuestos = true;
                $keys[] = 'total_impuestos_retenidos';
            }

        }

        if($aplica_impuestos) {

            $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $impuestos);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar $receptor', data: $valida);
            }
            if(!isset($this->xml)){
                return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
            }

            $data_nodo = (new dom_xml())->nodo(keys: $keys, keys_especial: array(),
                local_name: 'cfdi:Impuestos', nodo_key: 'impuestos', object: $impuestos, xml: $this);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al setear $emisor', data: $data_nodo);
            }
            if($aplica_impuestos_retenidos){
                $retenciones = (new dom_xml())->anexa_impuestos(data_nodo: $data_nodo,impuestos:  $impuestos,
                    obj_impuestos: 'retenciones',tipo_impuesto: 'Retencion',xml:  $this);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al generar nodo', data: $retenciones);
                }
            }
            if($aplica_impuestos_trasladados){
                $traslados = (new dom_xml())->anexa_impuestos(data_nodo: $data_nodo,
                    impuestos: $impuestos, obj_impuestos: 'traslados', tipo_impuesto: 'Traslado', xml: $this);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al generar nodo', data: $traslados);
                }
            }

        }

        return $this->dom->saveXML();
    }

    public function cfdi_impuestos_json(stdClass $impuestos, array $json): array
    {

        $aplica_impuestos = false;
        $aplica_impuestos_trasladados = false;
        $keys = array();
        if(isset($impuestos->traslados)){
            if(count($impuestos->traslados ) > 0){

                $aplica_impuestos_trasladados = true;
                $aplica_impuestos = true;
                $keys[] = 'total_impuestos_trasladados';

            }
        }
        $aplica_impuestos_retenidos = false;
        if(isset($impuestos->retenciones)){
            if(count($impuestos->retenciones ) > 0){
                $aplica_impuestos_retenidos = true;
                $aplica_impuestos = true;
                $keys[] = 'total_impuestos_retenidos';
            }

        }

        if($aplica_impuestos) {

            $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $impuestos);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar $receptor', data: $valida);
            }

            $json = (new dom_xml())->nodo_json(json: $json, keys: $keys,
                keys_especial: array(), local_name: 'Impuestos', nodo_key: 'impuestos', object: $impuestos, xml: $this);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al setear impuestos', data: $json);
            }
            if($aplica_impuestos_retenidos){
                $retenciones = (new dom_xml())->anexa_impuestos_json(impuestos:  $impuestos,
                    obj_impuestos: 'retenciones',tipo_impuesto: 'Retencion',xml:  $this);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al generar nodo', data: $retenciones);
                }
                $json['Comprobante']['Impuestos']['Retenciones'] = $retenciones;
            }
            if($aplica_impuestos_trasladados){
                $traslados = (new dom_xml())->anexa_impuestos_json(impuestos: $impuestos, obj_impuestos: 'traslados',
                    tipo_impuesto: 'Traslado', xml: $this);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al generar nodo', data: $traslados);
                }
                $json['Comprobante']['Impuestos']['Traslados'] = $traslados;
            }

        }

        return $json;
    }

    public function cfdi_receptor(stdClass $receptor): bool|string|array
    {
        $keys = array('rfc','nombre','domicilio_fiscal_receptor','regimen_fiscal_receptor','uso_cfdi');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $receptor', data: $valida);
        }

        if(!isset($this->xml)){
            return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
        }

        $keys_especial = array('uso_cfdi'=>'UsoCFDI');

        $data_nodo = (new dom_xml())->nodo(keys: $keys, keys_especial: $keys_especial,
            local_name: 'cfdi:Receptor', nodo_key: 'receptor', object:  $receptor,xml:  $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear $receptor', data: $data_nodo);
        }

        return $this->dom->saveXML();
    }

    public function cfdi_receptor_v33(stdClass $receptor): bool|string|array
    {
        $keys = array('rfc','nombre','uso_cfdi');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $receptor', data: $valida);
        }

        if(!isset($this->xml)){
            return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
        }

        $keys_especial = array('uso_cfdi'=>'UsoCFDI');

        $data_nodo = (new dom_xml())->nodo(keys: $keys, keys_especial: $keys_especial,
            local_name: 'cfdi:Receptor', nodo_key: 'receptor', object:  $receptor,xml:  $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear $receptor', data: $data_nodo);
        }

        return $this->dom->saveXML();
    }

    public function cfdi_receptor_json(array $json, stdClass $receptor): array
    {
        $keys = array('rfc','nombre','domicilio_fiscal_receptor','regimen_fiscal_receptor','uso_cfdi');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $receptor', data: $valida);
        }


        $keys_especial = array('uso_cfdi'=>'UsoCFDI');

        $json = (new dom_xml())->nodo_json(json: $json, keys: $keys, keys_especial: $keys_especial,
            local_name: 'Receptor', nodo_key: 'receptor', object: $receptor, xml: $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear $receptor', data: $json);
        }

        return $json;
    }

    final public function cfdi_relacionados_json(array $relacionados, array $json): array
    {

        $json = (new dom_xml())->relacionados_json(relacionados: $relacionados, json: $json);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar cfdi comprobante', data: $json);
        }

        return $json;
    }

    /**
     * @param stdClass $relacionados
     * @return bool|array|string
     * @throws DOMException
     */
    public function cfdi_relacionados(stdClass $relacionados): bool|array|string
    {
        $keys = array('tipo_relacion');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $relacionados);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $relacionados', data: $valida);
        }

        if(!isset($this->xml)){
            return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
        }

        $data_nodo = (new dom_xml())->nodo(keys: $keys, keys_especial: array(), local_name: 'cfdi:CfdiRelacionados',
            nodo_key: 'relacionados', object:  $relacionados, xml:  $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear $relacionados', data: $data_nodo);
        }

        if(!isset($relacionados->relaciones)){
            return $this->error->error(mensaje: 'Error no existe relaciones', data: $relacionados);
        }
        if(!is_array($relacionados->relaciones)){
            return $this->error->error(mensaje: 'Error traslados en impuestos debe ser un array', data: $relacionados);
        }
        if(count($relacionados->relaciones)>0){
            foreach ($relacionados->relaciones  as $relacion){

                $keys = array('uuid');
                $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $relacion);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al validar $relacionados', data: $valida);
                }

                $nodo_relacionado = $this->dom->createElement('cfdi:CfdiRelacionado');
                $data_nodo->appendChild($nodo_relacionado);
                $nodo_relacionado->setAttribute('UUID', $relacion->uuid);
            }
        }

        return $this->dom->saveXML();
    }

    final public function get_datos_xml(string $xml_data = ""): array
    {
        $xml = simplexml_load_string($xml_data);

        $ns = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('c', $ns['cfdi']);
        $xml->registerXPathNamespace('t', $ns['tfd']);

        $xml_data = array();
        $xml_data['cfdi_comprobante'] = array();
        $xml_data['cfdi_emisor'] = array();
        $xml_data['cfdi_receptor'] = array();
        $xml_data['cfdi_conceptos'] = array();
        $xml_data['tfd'] = array();

        $nodos = array();
        $nodos[] = '//cfdi:Comprobante';
        $nodos[] = '//cfdi:Comprobante//cfdi:Emisor';
        $nodos[] = '//cfdi:Comprobante//cfdi:Receptor';
        $nodos[] = '//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto';
        $nodos[] = '//t:TimbreFiscalDigital';

        foreach ($nodos as $key => $nodo) {
            foreach ($xml->xpath($nodo) as $value) {
                $data = (array)$value->attributes();
                $data = $data['@attributes'];
                $xml_data[array_keys($xml_data)[$key]] = $data;
            }
        }
        return $xml_data;
    }
}
