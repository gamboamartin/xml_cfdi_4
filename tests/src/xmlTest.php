<?php
namespace tests\controllers;

use DOMException;
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

    public function test_cfdi_comprobante(): void
    {
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

    /**
     */
    public function test_cfdi_emisor(): void
    {
        errores::$error = false;

        $xml = new xml();
        //$modelo = new liberator($modelo);


        $emisor = new stdClass();
        $resultado = $xml->cfdi_emisor(emisor: $emisor);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar $emisor',$resultado['mensaje']);

        errores::$error = false;

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

        $emisor = new stdClass();

        $resultado = $xml->cfdi_emisor(emisor: $emisor);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar $emisor',$resultado['mensaje']);

        errores::$error = false;


        $emisor = new stdClass();
        $emisor->rfc = 'a';
        $emisor->nombre = 'a';
        $emisor->regimen_fiscal = 'a';

        $resultado = $xml->cfdi_emisor(emisor: $emisor);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase('<?xml version="1.0" encoding="utf-8"?>',$resultado);
        $this->assertStringContainsStringIgnoringCase('<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"',$resultado);
        $this->assertStringContainsStringIgnoringCase('xsi:schemaLocation="http://www.sat.gob.mx/cfd/4',$resultado);
        $this->assertStringContainsStringIgnoringCase('cfdi:Emisor Rfc="a" Nombre="a" Regim',$resultado);
        $this->assertStringContainsStringIgnoringCase('a" RegimenFiscal="a"/></cfdi:Comprobante>',$resultado);
    }

    /**
     */
    public function test_cfdi_receptor(): void
    {
        errores::$error = false;

        $xml = new xml();
        //$modelo = new liberator($modelo);

        $comprobante = new stdClass();
        $comprobante->tipo_de_comprobante = 'I';
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

        $receptor = new stdClass();
        $receptor->rfc = 'a';
        $receptor->nombre = 'b';
        $receptor->domicilio_fiscal_receptor = 'c';
        $receptor->regimen_fiscal_receptor = 'd';
        $receptor->uso_cfdi = 'f';
        $resultado = $xml->cfdi_receptor(receptor: $receptor);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase('xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd "',$resultado);
        $this->assertStringContainsStringIgnoringCase('Exportacion="01" TipoDeComprobante="I"',$resultado);
        $this->assertStringContainsStringIgnoringCase('<cfdi:Receptor Rfc="a" Nombre="b"',$resultado);
        $this->assertStringContainsStringIgnoringCase('DomicilioFiscalReceptor="c" RegimenFis',$resultado);
        $this->assertStringContainsStringIgnoringCase('FiscalReceptor="d" UsoCfdi="f"/>',$resultado);
    }
}

