<?php
namespace gamboamartin\xml_cfdi_4;

use DOMElement;
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
    private function asigna_cfdi_comprobante_pago(xml $xml): DOMNode
    {

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
}
