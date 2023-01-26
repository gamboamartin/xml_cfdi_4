<?php
namespace tests\controllers;


use gamboamartin\errores\errores;
use gamboamartin\test\test;
use gamboamartin\xml_cfdi_4\percepcion;

use gamboamartin\xml_cfdi_4\timbra;
use gamboamartin\xml_cfdi_4\xml;
use stdClass;

class timbraTest extends test {
    public errores $errores;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();

    }



    public function test_timbra(): void
    {
        errores::$error = false;

        $timbra = new timbra();

        $folio = mt_rand(0,999999999).mt_rand(0,999999999).mt_rand(0,999999999).mt_rand(0,999999999);


        $contenido_xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd" Version="4.0" Fecha="2023-01-25T10:02:58" Serie="CFDI4.0" Folio="'.$folio.'" FormaPago="01" SubTotal="10500.00" Moneda="MXN" Total="12180.00" TipoDeComprobante="I" MetodoPago="PUE" LugarExpedicion="03000" Exportacion="01"> 
 <cfdi:Emisor Rfc="EKU9003173C9" Nombre="ESCUELA KEMPER URGATE" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="MOFY900516NL1" Nombre="YADIRA MAGALY MONTAÑEZ FELIX" DomicilioFiscalReceptor="91779" RegimenFiscalReceptor="612" UsoCFDI="G01"/>
  <cfdi:Conceptos>
    <cfdi:Concepto Cantidad="30.000000" Unidad="Caja" NoIdentificacion="400578" Descripcion="Compra de fresas" ValorUnitario="350.00" Importe="10500.00" ClaveProdServ="70141902" ClaveUnidad="EA" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="10500.00" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="1680.00"/>
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos TotalImpuestosTrasladados="1680.00">
    <cfdi:Traslados>
      <cfdi:Traslado Base="10500.00" Impuesto="002" Importe="1680.00" TasaOCuota="0.160000" TipoFactor="Tasa"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>

</cfdi:Comprobante>';

        /*$contenido_xml = '{
  "Comprobante": {
    "Serie": "A",
    "Folio": "1",
    "Fecha": "2023-01-26T11:11:11",
    "FormaPago": "01",
    "NoCertificado": "30001000000300023708",
    "CondicionesDePago": "NA",
    "SubTotal": "1.00",
    "Moneda": "MXN",
    "TipoCambio": "1",
    "Total": "1.16",
    "TipoDeComprobante": "I",
    "MetodoPago": "PUE",
    "LugarExpedicion": "45079",
    "Emisor": {
      "Rfc": "AAA010101AAA",
      "Nombre": "Soluciones Fiscales Facturalo S DE RL DE CV",
      "RegimenFiscal": "601"
    },
    "Receptor": {
      "Rfc": "XAXX010101000",
      "Nombre": "PUBLICO EN GENERAL",
      "UsoCFDI": "G01"
    },
    "Conceptos": [
      {
        "ClaveProdServ": "01010101",
        "NoIdentificacion": "00001",
        "Cantidad": "1",
        "ClaveUnidad": "F52",
        "Unidad": "TONELADA",
        "Descripcion": "ACERO",
        "ValorUnitario": "1.00",
        "Importe": "1.00",
        "Impuestos":
        {
          "Traslados": [
            {
              "Base": "1.00",
              "Impuesto": "002",
              "TipoFactor": "Tasa",
              "TasaOCuota": "0.160000",
              "Importe": "0.16"
            }
          ]
        }
      }
    ],
    "Impuestos": {
      "TotalImpuestosTrasladados": "0.16",
      "Traslados": [
        {
          "Impuesto": "002",
          "TipoFactor": "Tasa",
          "TasaOCuota": "0.160000",
          "Importe": "0.16"
        }
      ]
    }
  }
}';*/

        $id_comprobante = '';

        $resultado = $timbra->timbra($contenido_xml, $id_comprobante, 'profact');
       // print_r($resultado);exit;

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertNotEmpty($resultado->uuid);

        errores::$error = false;

    }




}

