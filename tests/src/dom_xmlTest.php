<?php
namespace tests\controllers;

use DOMException;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use gamboamartin\xml_cfdi_4\dom_xml;
use gamboamartin\xml_cfdi_4\xml;
use stdClass;

class dom_xmlTest extends test {
    public errores $errores;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();

    }

    public function test_asigna_cfdi_comprobante_pago(): void
    {
        errores::$error = false;

        $dom = new dom_xml();
        $dom = new liberator($dom);

        $xml = new xml();
        $comprobante = new stdClass();
        $comprobante->tipo_de_comprobante = 'P';
        $comprobante->moneda = 'XXX';
        $comprobante->exportacion = '01';
        $comprobante->total = 0;
        $comprobante->sub_total = 0;
        $comprobante->lugar_expedicion = 44110;
        $comprobante->fecha = '2021-01-01';
        $comprobante->folio = '01';
        $comprobante = $xml->cfdi_comprobante($comprobante);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al generar comprobante', data: $comprobante);
            print_r($error);
            exit;
        }

        $resultado = $dom->asigna_cfdi_comprobante_pago($xml);


        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('http://www.sat.gob.mx/cfd/4', $resultado->namespaceURI);
        $this->assertEquals('Comprobante', $resultado->localName);
        $this->assertEquals('cfdi:Comprobante', $resultado->nodeName);
        $this->assertEquals('cfdi:Comprobante', $resultado->tagName);
        $this->assertEquals('cfdi', $resultado->prefix);


        errores::$error = false;
    }

    public function test_attr_ns(): void
    {
        errores::$error = false;

        $dom = new dom_xml();
        //$dom = new liberator($dom);

        $xml = new xml();

        $comprobante = new stdClass();
        $comprobante->tipo_de_comprobante = 'I';
        $comprobante->moneda = 'MXN';
        $comprobante->exportacion = '01';
        $comprobante->total = 0;
        $comprobante->sub_total = 0;
        $comprobante->lugar_expedicion = 44110;
        $comprobante->fecha = '2021-01-01';
        $comprobante->folio = '01';
        $comprobante = $xml->cfdi_comprobante($comprobante);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al generar comprobante', data: $comprobante);
            print_r($error);
            exit;
        }

        $resultado = $dom->attr_ns($xml);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('utf-8', $resultado->dom->actualEncoding);
        $this->assertEquals('utf-8', $resultado->dom->encoding);
        $this->assertEquals('utf-8', $resultado->dom->xmlEncoding);
        $this->assertEquals('1.0', $resultado->dom->version);
        $this->assertEquals('1.0', $resultado->dom->xmlVersion);
        $this->assertEquals('#document', $resultado->dom->nodeName);
        $this->assertEquals('9', $resultado->dom->nodeType);
        $this->assertEquals('cfdi:Comprobante', $resultado->xml->nodeName);
        $this->assertEquals('cfdi:Comprobante', $resultado->xml->nodeName);
        $this->assertEquals('http://www.sat.gob.mx/cfd/4', $resultado->xml->namespaceURI);
        $this->assertEquals('cfdi', $resultado->xml->prefix);
        $this->assertEquals('Comprobante', $resultado->xml->localName);
    }

    /**
     * @throws DOMException
     */
    public function test_comprobante_pago(): void
    {
        errores::$error = false;

        $dom = new dom_xml();
        //$dom = new liberator($dom);

        $xml = new xml();

        $comprobante = new stdClass();
        $comprobante->tipo_de_comprobante = 'P';
        $comprobante->moneda = 'XXX';
        $comprobante->exportacion = '01';
        $comprobante->total = 0;
        $comprobante->sub_total = 0;
        $comprobante->lugar_expedicion = 44110;
        $comprobante->fecha = '2021-01-01';
        $comprobante->folio = '01';

        $comprobante = $xml->cfdi_comprobante($comprobante);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al generar comprobante', data: $comprobante);
            print_r($error);
            exit;
        }


        $comprobante = new stdClass();
        $comprobante->tipo_de_comprobante = 'P';
        $comprobante->moneda = 'XXX';
        $comprobante->exportacion = '01';
        $comprobante->total = 0;
        $comprobante->sub_total = 0;
        $comprobante->lugar_expedicion = 44110;
        $comprobante->fecha = '2021-01-01';
        $comprobante->folio = '01';

        $resultado = $dom->comprobante_pago($comprobante, $xml);

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('cfdi:Comprobante', $resultado->tagName);
        $this->assertEquals('cfdi:Comprobante', $resultado->nodeName);
        $this->assertEquals('http://www.sat.gob.mx/cfd/4', $resultado->namespaceURI);
        $this->assertEquals('cfdi', $resultado->prefix);
        $this->assertEquals('Comprobante', $resultado->localName);
        errores::$error = false;

    }

    /**
     * @throws \DOMException
     */
    public function test_elemento_concepto(): void
    {
        errores::$error = false;

        $dom = new dom_xml();
        //$dom = new liberator($dom);

        $xml = new xml();

        $comprobante = new stdClass();
        $comprobante->tipo_de_comprobante = 'I';
        $comprobante->moneda = 'MXN';
        $comprobante->exportacion = '01';
        $comprobante->total = 0;
        $comprobante->sub_total = 0;
        $comprobante->lugar_expedicion = 44110;
        $comprobante->fecha = '2021-01-01';
        $comprobante->folio = '01';
        $comprobante = $xml->cfdi_comprobante($comprobante);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al generar comprobante', data: $comprobante);
            print_r($error);
            exit;
        }

        $concepto = new stdClass();
        $nodo = $xml->dom->createElement('cfdi:Conceptos');
        $xml->xml->appendChild($nodo);

        $concepto->impuestos = array();
        $concepto->clave_prod_serv = '1';
        $concepto->cantidad = '1';
        $concepto->clave_unidad = 'a';
        $concepto->descripcion = 'a';
        $concepto->valor_unitario = '1';
        $concepto->no_identificacion = 'a';
        $concepto->objeto_imp = 'a';
        $concepto->importe = '1';

        $resultado = $dom->elemento_concepto($concepto, $nodo, $xml);

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('cfdi:Conceptos', $resultado->tagName);
        $this->assertEquals('cfdi:Conceptos', $resultado->localName);
        errores::$error = false;

    }


}

