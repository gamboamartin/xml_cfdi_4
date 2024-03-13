<?php
namespace tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\test\test;
use gamboamartin\xml_cfdi_4\complementos;
use gamboamartin\xml_cfdi_4\xml;
use stdClass;

class complementosTest extends test {
    public errores $errores;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();

    }

    public function test_aplica_complemento_cfdi_comprobante(): void
    {
        errores::$error = false;

        $com = new complementos();
        //$com = new liberator($com);


        $comprobante = new stdClass();
        $xml = new xml();
        $resultado = $com->aplica_complemento_cfdi_comprobante($comprobante,$xml);


        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;

        $com = new complementos();
        //$com = new liberator($com);
        $comprobante = new stdClass();
        $xml = new xml();
        $xml->cfdi->comprobante->tipo_de_comprobante = 'P';
        $resultado = $com->aplica_complemento_cfdi_comprobante($comprobante,$xml);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar dom pago',$resultado['mensaje']);

        errores::$error = false;

        $com = new complementos();
        //$com = new liberator($com);
        $comprobante = new stdClass();
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
        $xml->cfdi->comprobante->tipo_de_comprobante = 'P';
        $xml->cfdi->comprobante->moneda = 'XXX';
        $resultado = $com->aplica_complemento_cfdi_comprobante($comprobante,$xml);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);


        errores::$error = false;
    }

    public function test_comprobante_a_cuenta_terceros(): void
    {
        errores::$error = false;

        $com = new complementos();
        //$com = new liberator($com);


        $comprobante = new stdClass();

        $resultado = $com->comprobante_a_cuenta_terceros($comprobante);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('PUE',$resultado->metodo_pago);
        $this->assertEquals('I',$resultado->tipo_de_comprobante);
        $this->assertEquals('01',$resultado->exportacion);
        errores::$error = false;
    }

    public function test_comprobante_complemento_nomina(): void
    {
        errores::$error = false;

        $com = new complementos();
        //$com = new liberator($com);


        $comprobante = new stdClass();

        $resultado = $com->comprobante_complemento_nomina($comprobante);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('N',$resultado->tipo_de_comprobante);
        $this->assertEquals('MXN',$resultado->moneda);
        $this->assertEquals('01',$resultado->exportacion);
        errores::$error = false;
    }

    public function test_comprobante_complemento_pago(): void
    {
        errores::$error = false;

        $com = new complementos();
        //$com = new liberator($com);


        $comprobante = new stdClass();

        $resultado = $com->comprobante_complemento_pago($comprobante);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('P',$resultado->tipo_de_comprobante);
        $this->assertEquals('XXX',$resultado->moneda);
        $this->assertEquals('0',$resultado->total);
        $this->assertEquals('01',$resultado->exportacion);
        $this->assertEquals('0',$resultado->sub_total);
        errores::$error = false;
    }




}

