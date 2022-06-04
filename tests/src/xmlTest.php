<?php
namespace tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\test\test;
use gamboamartin\xml_cfdi_4\xml;
use stdClass;

class xmlTest extends test {
    public errores $errores;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();

    }

    public function test_cfdi_comprobante(){
        errores::$error = false;

        $xml = new xml();
        //$modelo = new liberator($modelo);

        $comprobante = new stdClass();
        $comprobante->tipo_de_comprobante = 'P';
        $comprobante->moneda = 'XXX';
        $comprobante->exportacion = '01';
        $comprobante->total = 0;
        $comprobante->sub_total = 0;
        $comprobante->lugar_expedicion = 44110;
        $comprobante->fecha = '2021-01-01';
        $comprobante->folio = '01';

        $resultado = $xml->cfdi_comprobante($comprobante);

        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase('<?xml version="1.0" encoding="utf-8"?>', $resultado);
        $this->assertStringContainsStringIgnoringCase('<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', $resultado);
        $this->assertStringContainsStringIgnoringCase('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:pago20', $resultado);
        $this->assertStringContainsStringIgnoringCase('go20="http://www.sat.gob.mx/Pagos20" xsi:schemaLoc', $resultado);
        $this->assertStringContainsStringIgnoringCase('on="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd " Moneda="XXX"', $resultado);
        $this->assertStringContainsStringIgnoringCase('eda="XXX" Total="0" Exportacion="01" TipoDeComprobante="P" SubTotal="0" ', $resultado);
        $this->assertStringContainsStringIgnoringCase('" Folio="01" Version="4.0"/>', $resultado);

        errores::$error = false;
    }
}

