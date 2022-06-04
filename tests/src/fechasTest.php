<?php
namespace tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use gamboamartin\xml_cfdi_4\fechas;
use gamboamartin\xml_cfdi_4\xml;
use stdClass;

class fechasTest extends test {
    public errores $errores;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();

    }

    public function test_fecha_cfdi_vacia(){
        errores::$error = false;

        $fechas = new fechas();
        $fechas = new liberator($fechas);

        $resultado = $fechas->fecha_cfdi_vacia();
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);

        errores::$error = false;
    }
}

