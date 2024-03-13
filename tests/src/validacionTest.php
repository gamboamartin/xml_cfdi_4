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

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
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

    public function test_valida_nodo_impuesto(){
        errores::$error = false;

        $val = new validacion();
        //$val = new liberator($val);

        $traslado = new stdClass();

        $resultado = $val->valida_nodo_impuesto($traslado);

        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar obj_impuesto',$resultado['mensaje']);

        errores::$error = false;

        $traslado = new stdClass();
        $traslado->base = 1;
        $traslado->impuesto = 1;
        $traslado->tipo_factor = 1;
        $traslado->tasa_o_cuota = 1;
        $traslado->importe = 1;

        $resultado = $val->valida_nodo_impuesto($traslado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        $this->assertTrue($resultado);
        errores::$error = false;

    }
}

