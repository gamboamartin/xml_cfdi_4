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

    public function test_fecha_base(){
        errores::$error = false;

        $fechas = new fechas();
        $fechas = new liberator($fechas);

        $fecha = '2001-01-01';
        $hora = '00:00:00';
        $resultado = $fechas->fecha_base($fecha, $hora);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('2001-01-01T00:00:00',$resultado);
        errores::$error = false;
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

