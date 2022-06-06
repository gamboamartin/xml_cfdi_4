<?php
namespace tests\controllers;

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


}

