<?php
namespace tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use gamboamartin\xml_cfdi_4\fechas;
use gamboamartin\xml_cfdi_4\validacion;
use gamboamartin\xml_cfdi_4\xml;
use stdClass;

class validacionTest extends test {
    public errores $errores;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();

    }

    public function test_complemento_pago_comprobante(){
        errores::$error = false;

        $val = new validacion();
        //$val = new liberator($val);

        $comprobante = new stdClass();
        $xml = new xml();
        $xml->cfdi->comprobante->moneda = 'XXX';
        $resultado = $val->complemento_pago_comprobante($comprobante, $xml);
        print_r($resultado);

        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        $this->assertIsBool($resultado);

        errores::$error = false;


        $comprobante = new stdClass();
        $xml = new xml();
        $xml->cfdi->comprobante->total = 10;
        $resultado = $val->complemento_pago_comprobante($comprobante, $xml);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);


        errores::$error = false;
    }
}

