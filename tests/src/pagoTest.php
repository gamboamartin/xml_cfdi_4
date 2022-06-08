<?php
namespace tests\controllers;

use DOMException;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use gamboamartin\xml_cfdi_4\dom_xml;
use gamboamartin\xml_cfdi_4\pago;
use gamboamartin\xml_cfdi_4\xml;
use stdClass;

class pagoTest extends test {
    public errores $errores;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();

    }



    /**
     * @throws DOMException
     */
    public function test_equivalencia_dr_1(): void
    {
        errores::$error = false;

        $pago = new pago();
        $pago = new liberator($pago);

        $docto_relacionado = new stdClass();
        $resultado = $pago->equivalencia_dr_1($docto_relacionado);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar $docto_relacionado',$resultado['mensaje']);

        errores::$error = false;


        $docto_relacionado = new stdClass();
        $docto_relacionado->equivalencia_dr = 1.00;
        $resultado = $pago->equivalencia_dr_1($docto_relacionado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('1',$resultado->equivalencia_dr);

        errores::$error = false;


        $docto_relacionado = new stdClass();
        $docto_relacionado->equivalencia_dr = 1.000000;
        $resultado = $pago->equivalencia_dr_1($docto_relacionado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('1',$resultado->equivalencia_dr);

        errores::$error = false;


        $docto_relacionado = new stdClass();
        $docto_relacionado->equivalencia_dr = 1.2;
        $resultado = $pago->equivalencia_dr_1($docto_relacionado);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error equivalencia_dr debe ser un 1 como flotante o entero',$resultado['mensaje']);
        errores::$error = false;

    }




}

