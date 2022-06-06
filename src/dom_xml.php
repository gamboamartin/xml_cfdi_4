<?php
namespace gamboamartin\xml_cfdi_4;

use DOMElement;
use DOMException;
use DOMNode;
use gamboamartin\errores\errores;
use PhpParser\Builder\Function_;
use stdClass;

class dom_xml{
    private validacion $valida;
    private errores $error;
    public function __construct(){
        $this->valida = new validacion();
        $this->error = new errores();
    }

    /**
     * Asigna los atributos xsi para complemento de pago
     * @version 0.2.0
     * @param xml $xml Objeto de ejecucion de xml
     * @return DOMNode|array
     */
    private function asigna_cfdi_comprobante_pago(xml $xml): DOMNode|array
    {

        if(!isset($xml->xml)){
            return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
        }

        $xml->xml->setAttributeNS($xml->cfdi->comprobante->namespace->w3, 'xmlns:pago20',
            $xml->cfdi->comprobante->xmlns_pago20);
        $xml->cfdi->comprobante->xsi_schemaLocation.=" ".$xml->cfdi->comprobante->xmlns_pago20;
        $xml->cfdi->comprobante->xsi_schemaLocation.=" http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd";

        return $xml->xml;
    }

    private function attr_ns(xml $xml): xml
    {
        $xml->xml->setAttributeNS($xml->cfdi->comprobante->namespace->w3, 'xmlns:xsi',
            $xml->cfdi->comprobante->xmlns_xsi);

        $xml->xml->setAttributeNS($xml->cfdi->comprobante->xmlns_xsi, 'xsi:schemaLocation',
            $xml->cfdi->comprobante->xsi_schemaLocation);

        return $xml;
    }

    private function attrs_concepto(stdClass $concepto, DOMElement $elemento_concepto): DOMElement
    {
        $elemento_concepto->setAttribute('ClaveProdServ', $concepto->clave_prod_serv);
        $elemento_concepto->setAttribute('NoIdentificacion', $concepto->no_identificacion);
        $elemento_concepto->setAttribute('Cantidad', $concepto->cantidad);
        $elemento_concepto->setAttribute('ClaveUnidad', $concepto->clave_unidad);
        $elemento_concepto->setAttribute('Descripcion', $concepto->descripcion);
        $elemento_concepto->setAttribute('ValorUnitario', $concepto->valor_unitario);
        $elemento_concepto->setAttribute('Importe', $concepto->importe);
        $elemento_concepto->setAttribute('ObjetoImp', $concepto->objeto_imp);

        return $elemento_concepto;
    }

    /**
     * @throws DOMException
     */
    public function carga_conceptos(array $conceptos, DOMElement $nodo, xml $xml): xml|array
    {
        foreach ($conceptos as $concepto){

            $elementos_concepto = (new dom_xml())->elementos_concepto(concepto: $concepto, nodo: $nodo,xml:  $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar atributos', data: $elementos_concepto);
            }
        }
        return $xml;
    }

    public function comprobante(stdClass $comprobante, xml $xml): array|stdClass
    {
        $nodo = $this->inicializa_comprobante(comprobante: $comprobante,xml:  $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar cfdi comprobante', data: $nodo);
        }

        $comprobante_base = $this->comprobante_base(nodo:$nodo, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar cfdi comprobante', data: $comprobante_base);
        }

        return $comprobante_base;
    }

    private function comprobante_base(DOMElement $nodo, xml $xml): array|stdClass
    {


        $attr_ns = $this->attr_ns(xml:$xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar dom attr', data: $attr_ns);
        }

        $data_nodo = $this->init_dom_cfdi_comprobante(nodo: $nodo,xml:  $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar cfdi comprobante', data: $data_nodo);
        }

        return $xml->cfdi;
    }

    public function comprobante_pago(stdClass $comprobante, xml $xml): DOMNode|array
    {
        $valida = $this->valida->complemento_pago_comprobante(comprobante: $comprobante,xml:  $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar complemento pago dom pago', data: $valida);
        }
        $cfdi_comprobante_pago = $this->asigna_cfdi_comprobante_pago(xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar dom pago', data: $cfdi_comprobante_pago);
        }
        return $cfdi_comprobante_pago;
    }

    /**
     * @throws DOMException
     */
    private function elemento_concepto(stdClass $concepto, DOMElement $nodo, xml $xml): array|DOMElement
    {
        $elemento_concepto = $xml->dom->createElement('cfdi:Concepto');
        $nodo->appendChild($elemento_concepto);

        $elemento_concepto = $this->attrs_concepto(concepto: $concepto, elemento_concepto: $elemento_concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar atributos', data: $elemento_concepto);
        }
        return $elemento_concepto;
    }

    /**
     * @throws DOMException
     */
    private function elementos_concepto(stdClass $concepto, DOMElement $nodo, xml $xml): xml|array
    {
        $xml->cfdi->conceptos[] = new stdClass();
        $valida = $this->valida->valida_concepto(concepto: $concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $concepto', data: $valida);
        }
        $valida = $this->valida->valida_data_concepto(concepto: $concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $concepto', data: $valida);
        }

        $elemento_concepto = $this->elemento_concepto(concepto: $concepto, nodo: $nodo,xml:  $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar atributos', data: $elemento_concepto);
        }
        return $xml;
    }

    private function genera_attrs(array $keys, DOMElement $nodo, string $nodo_key, stdClass $object, xml $xml): array|DOMElement
    {
        $data_nodo = (new init())->asigna_datos_para_nodo(keys: $keys, nodo_key: $nodo_key,objetc:  $object,xml:  $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar '.$nodo_key, data: $data_nodo);
        }

        $setea = $this->setea_attr(keys: $keys,nodo:  $nodo,nodo_key:  $nodo_key, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear '.$nodo_key, data: $setea);
        }
        return $setea;
    }

    private function inicializa_comprobante(stdClass $comprobante, xml $xml): bool|array|DOMElement
    {
        $data_comprobante = (new init())->inicializa_valores_comprobante(comprobante: $comprobante, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar comprobante', data: $data_comprobante);
        }
        $nodo = $xml->dom->createElementNS($xml->cfdi->comprobante->xmlns_cfdi, 'cfdi:Comprobante');
        $xml->xml = $xml->dom->appendChild($nodo);

        return $nodo;
    }

    private function init_dom_cfdi_comprobante(DOMElement $nodo, xml $xml): DOMElement
    {
        $nodo->setAttribute('Moneda', $xml->cfdi->comprobante->moneda);
        $nodo->setAttribute('Total', $xml->cfdi->comprobante->total);
        $nodo->setAttribute('Exportacion', $xml->cfdi->comprobante->exportacion);
        $nodo->setAttribute('TipoDeComprobante', $xml->cfdi->comprobante->tipo_de_comprobante);
        $nodo->setAttribute('SubTotal', $xml->cfdi->comprobante->sub_total);
        $nodo->setAttribute('LugarExpedicion', $xml->cfdi->comprobante->lugar_expedicion);
        $nodo->setAttribute('Fecha', $xml->cfdi->comprobante->fecha);
        $nodo->setAttribute('Folio', $xml->cfdi->comprobante->folio);
        $nodo->setAttribute('Version', $xml->cfdi->comprobante->version);
        return $nodo;
    }

    public function nodo(array $keys, string $local_name, string $nodo_key, stdClass $object, xml $xml): array|DOMElement
    {

        $nodo = $xml->dom->createElement($local_name);
        $xml->xml->appendChild($nodo);

        $setea = $this->genera_attrs(keys: $keys,nodo:  $nodo,nodo_key:  $nodo_key, object: $object, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear '.$nodo_key, data: $setea);
        }
        return $setea;
    }

    private function setea_attr(array $keys, DOMElement $nodo, string $nodo_key, xml $xml): DOMElement
    {
        foreach ($keys as $key){
            $key_nodo_xml = str_replace('_', ' ', $key);
            $key_nodo_xml = ucwords($key_nodo_xml);
            $key_nodo_xml = str_replace(' ', '', $key_nodo_xml);
            $nodo->setAttribute($key_nodo_xml, $xml->cfdi->$nodo_key->$key);
        }

        return $nodo;
    }
}
