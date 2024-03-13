<?php
namespace tests\controllers;


use DOMElement;
use gamboamartin\errores\errores;
use gamboamartin\test\test;
use gamboamartin\xml_cfdi_4\complementos;
use gamboamartin\xml_cfdi_4\dom_xml;
use gamboamartin\xml_cfdi_4\nomina;
use gamboamartin\xml_cfdi_4\percepcion;

use gamboamartin\xml_cfdi_4\xml;
use stdClass;

class percepcionTest extends test {
    public errores $errores;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();

    }



    public function test_nodo_percepcion(): void
    {
        errores::$error = false;

        $obj_percepcion = new percepcion();


        $xml = new xml();
        $nodo_nominas_percepciones = $xml->dom->createElement('nomina12:Percepciones');

        $percepcion= new stdClass();
        $percepcion->tipo_percepcion = '010';
        $percepcion->clave = 'a';
        $percepcion->concepto = 'a';
        $percepcion->importe_gravado = '0';
        $percepcion->importe_exento = '0.01';

        $resultado = $obj_percepcion->nodo_percepcion($nodo_nominas_percepciones, $percepcion, $xml);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;

    }




}

