<?php
namespace tests\controllers;


use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
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


    public function test_consulta_estado_sat(): void
    {
        errores::$error = false;

        $timbra = new timbra();

        $rfc_emisor = 'EKU9003173C9';
        $rfc_receptor = 'XAXX010101000';
        $total = '1.16';
        $uuid = '4a5dc24d-e0a9-4172-9fdd-38b2dfbd4435';
        $resultado = $timbra->consulta_estado_sat($rfc_emisor, $rfc_receptor, $total, $uuid);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        errores::$error = false;
        //$this->assertNotEmpty($resultado->uuid);
    }

    public function test_datos_base(): void
    {
        errores::$error = false;

        $timbra = new timbra();
        $timbra = new liberator($timbra);

        $rfc_emisor = 'a';
        $rfc_receptor = 'b';
        $total = 'q';
        $uuid = 'c';
        $resultado = $timbra->datos_base($rfc_emisor, $rfc_receptor, $total, $uuid);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        errores::$error = false;

    }

    public function test_integra_datos_base(): void
    {
        errores::$error = false;

        $timbra = new timbra();
        $timbra = new liberator($timbra);

        $rfc_emisor = 'a';
        $rfc_receptor = 'b';
        $total = '4';
        $uuid = 'r';
        $resultado = $timbra->integra_datos_base($rfc_emisor, $rfc_receptor, $total, $uuid);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        errores::$error = false;
    }

    public function test_timbra(): void
    {
        errores::$error = false;

        $timbra = new timbra();

        $folio = mt_rand(0,999999999).mt_rand(0,999999999).mt_rand(0,999999999).mt_rand(0,999999999);

/*
        $contenido_xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd" Version="4.0" Fecha="2023-03-28T10:02:58" Serie="CFDI4.0" Folio="'.$folio.'" FormaPago="01" SubTotal="10500.00" Moneda="MXN" Total="12180.00" TipoDeComprobante="I" MetodoPago="PUE" LugarExpedicion="03000" Exportacion="01"> 
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



        $id_comprobante = '';

        $resultado = $timbra->timbra($contenido_xml, $id_comprobante, 'profact');


        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertNotEmpty($resultado->uuid);

        errores::$error = false;


        $folio = mt_rand(0,999999999).mt_rand(0,999999999).mt_rand(0,999999999).mt_rand(0,999999999);


        $contenido_xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd" Version="4.0" Fecha="2023-03-28T14:43:51" Serie="CFDI4.0" Folio="'.$folio.'" FormaPago="01" SubTotal="100.00" Moneda="MXN" Total="114.75" TipoDeComprobante="I" MetodoPago="PUE" LugarExpedicion="03000" Exportacion="01"> 
 <cfdi:Emisor Rfc="EKU9003173C9" Nombre="ESCUELA KEMPER URGATE" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="MOFY900516NL1" Nombre="YADIRA MAGALY MONTAÑEZ FELIX" DomicilioFiscalReceptor="91779" RegimenFiscalReceptor="612" UsoCFDI="G01"/>
  <cfdi:Conceptos>
    <cfdi:Concepto Cantidad="10.000000" Unidad="Caja" NoIdentificacion="400578" Descripcion="Compra de fresas" ValorUnitario="10.00" Importe="100.00" ClaveProdServ="70141902" ClaveUnidad="EA" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="100.00" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="16.00"/>
        </cfdi:Traslados>
        <cfdi:Retenciones>
          <cfdi:Retencion Base="100.00" Impuesto="001" TipoFactor="Tasa" TasaOCuota="0.012500" Importe="1.25" />
        </cfdi:Retenciones>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos TotalImpuestosTrasladados="16.00" TotalImpuestosRetenidos="1.25">
  <cfdi:Retenciones>
      <cfdi:Retencion Impuesto="001" Importe="1.25"/>
    </cfdi:Retenciones>
    <cfdi:Traslados>
      <cfdi:Traslado Base="100.00" Impuesto="002" Importe="16.00" TasaOCuota="0.160000" TipoFactor="Tasa"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>

</cfdi:Comprobante>';

        $resultado = $timbra->timbra($contenido_xml, $id_comprobante, 'profact');

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertNotEmpty($resultado->uuid);

        */

        errores::$error = false;

        $contenido_xml = '{
    "Comprobante":
    {
        "Version": "4.0",
        "Serie": "LC-P",
        "Folio": "1",
        "Fecha": "2023-05-31T10:59:59",
        "NoCertificado": "30001000000400002434",
        "SubTotal": "0",
        "Moneda": "XXX",
        "Total": "0",
        "TipoDeComprobante": "T",
        "Exportacion": "01",
        "LugarExpedicion": "55000",
        "Emisor":
        {
            "Rfc": "EKU9003173C9",
            "Nombre": "ESCUELA KEMPER URGATE",
            "RegimenFiscal": "601"
        },
        "Receptor":
        {
            "Rfc": "EKU9003173C9",
            "Nombre": "ESCUELA KEMPER URGATE",
            "DomicilioFiscalReceptor": "26015",
            "RegimenFiscalReceptor": "601",
            "UsoCFDI": "S01"
        },
        "Conceptos":
        [
            {
                "ClaveProdServ": "60101704",
                "NoIdentificacion": "000004",
                "Cantidad": "1",
                "ClaveUnidad": "E48",
                "Unidad": "Paquetes",
                "Descripcion": "LIBRO(S) TEXTO Y/O MAT. BIBLIOGRAFICO VL",
                "ValorUnitario": "0",
                "Importe": "0",
                "ObjetoImp": "01"
            }
        ]
    }
}';

        $ruta_key_pem = '/var/www/html/xml_cfdi_4/tests/files/CSD_EKU9003173C9_key.pem';
        $ruta_cer_pem = '/var/www/html/xml_cfdi_4/tests/files/CSD_EKU9003173C9_cer.pem';
        $id_comprobante = '';

        $resultado = $timbra->timbra(contenido_xml: $contenido_xml, id_comprobante: $id_comprobante,
            ruta_cer_pem: $ruta_cer_pem, ruta_key_pem: $ruta_key_pem, pac_prov: 'facturalo');



        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertNotEmpty($resultado->uuid);



        $contenido_xml = '{
    "Comprobante":
    {
        "Version": "4.0",
        "Serie": "LC-P",
        "Folio": "1005",
        "Fecha": "2023-05-31T11:37:08",
        "NoCertificado": "30001000000400002434",
        "SubTotal": "0",
        "Moneda": "XXX",
        "Total": "0",
        "TipoDeComprobante": "T",
        "Exportacion": "01",
        "LugarExpedicion": "55000",
        "CfdiRelacionados":[
        {
            "TipoRelacion":"04",
            "CfdiRelacionado":
                ["6c76a910-2115-4a2c-bf15-e67c1505dd21","0CE337CF-62BE-4ECC-9EBB-67F7EA1AF6C4"]
        },
        {
            "TipoRelacion":"07",
            "CfdiRelacionado":
                ["6c76a910-2115-4a2c-bf15-e67c1505dd21","0CE337CF-62BE-4ECC-9EBB-67F7EA1AF6C4"]
        }
        ],
        "Emisor":
        {
            "Rfc": "EKU9003173C9",
            "Nombre": "ESCUELA KEMPER URGATE",
            "RegimenFiscal": "601"
        },
        "Receptor":
        {
            "Rfc": "EKU9003173C9",
            "Nombre": "ESCUELA KEMPER URGATE",
            "DomicilioFiscalReceptor": "26015",
            "RegimenFiscalReceptor": "601",
            "UsoCFDI": "S01"
        },
        "Conceptos":
        [
            {
                "ClaveProdServ": "60101704",
                "NoIdentificacion": "000004",
                "Cantidad": "1",
                "ClaveUnidad": "E48",
                "Unidad": "Paquetes",
                "Descripcion": "LIBRO(S) TEXTO Y/O MAT. BIBLIOGRAFICO VL",
                "ValorUnitario": "0",
                "Importe": "0",
                "ObjetoImp": "01"
            }
        ]
    }
}';

        $ruta_key_pem = '/var/www/html/xml_cfdi_4/tests/files/CSD_EKU9003173C9_key.pem';
        $ruta_cer_pem = '/var/www/html/xml_cfdi_4/tests/files/CSD_EKU9003173C9_cer.pem';
        $id_comprobante = '';

        $resultado = $timbra->timbra(contenido_xml: $contenido_xml, id_comprobante: $id_comprobante,
            ruta_cer_pem: $ruta_cer_pem, ruta_key_pem: $ruta_key_pem, pac_prov: 'facturalo');



        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertNotEmpty($resultado->uuid);
        $this->assertStringContainsStringIgnoringCase('CfdiRelacionados TipoRelacion="04"><cfdi:CfdiRelacionado UUID="6c76',$resultado->xml_sellado);


        errores::$error = false;

        $contenido_xml_array = new stdClass();
        $contenido_xml_array->Comprobante = new stdClass();
        $contenido_xml_array->Comprobante->Version = '4.0';
        $contenido_xml_array->Comprobante->Serie = '4.0';

        $contenido_xml_array->Comprobante->Folio = '0000179826';
        $contenido_xml_array->Comprobante->Fecha = '2023-05-31T10:37:08';
        $contenido_xml_array->Comprobante->NoCertificado = '30001000000400002434';
        $contenido_xml_array->Comprobante->SubTotal = '0';
        $contenido_xml_array->Comprobante->Moneda = 'XXX';
        $contenido_xml_array->Comprobante->Total = '0';
        $contenido_xml_array->Comprobante->TipoDeComprobante = 'P';
        $contenido_xml_array->Comprobante->Exportacion = '01';
        $contenido_xml_array->Comprobante->LugarExpedicion = '55000';

        $contenido_xml_array->Comprobante->CfdiRelacionados[0] = new stdClass();

        $contenido_xml_array->Comprobante->CfdiRelacionados[0]->TipoRelacion = '04';
        $contenido_xml_array->Comprobante->CfdiRelacionados[0]->CfdiRelacionado[] = '6c76a910-2115-4a2c-bf15-e67c1505dd21';
        $contenido_xml_array->Comprobante->CfdiRelacionados[0]->CfdiRelacionado[] = '0CE337CF-62BE-4ECC-9EBB-67F7EA1AF6C4';

        $contenido_xml_array->Comprobante->CfdiRelacionados[1] = new stdClass();
        $contenido_xml_array->Comprobante->CfdiRelacionados[1]->TipoRelacion = '07';
        $contenido_xml_array->Comprobante->CfdiRelacionados[1]->CfdiRelacionado[] = '6c76a910-2115-4a2c-bf15-e67c1505dd21';
        $contenido_xml_array->Comprobante->CfdiRelacionados[1]->CfdiRelacionado[] = '0CE337CF-62BE-4ECC-9EBB-67F7EA1AF6C4';


        $contenido_xml_array->Comprobante->Emisor = new stdClass();

        $contenido_xml_array->Comprobante->Emisor->Rfc = 'EKU9003173C9';
        $contenido_xml_array->Comprobante->Emisor->Nombre = 'ESCUELA KEMPER URGATE';
        $contenido_xml_array->Comprobante->Emisor->RegimenFiscal = '601';

        $contenido_xml_array->Comprobante->Receptor = new stdClass();
        $contenido_xml_array->Comprobante->Receptor->Rfc = 'XAXX010101000';
        $contenido_xml_array->Comprobante->Receptor->Nombre = 'PUBLICO GENERAL';
        $contenido_xml_array->Comprobante->Receptor->DomicilioFiscalReceptor = '55000';
        $contenido_xml_array->Comprobante->Receptor->RegimenFiscalReceptor = '616';
        $contenido_xml_array->Comprobante->Receptor->UsoCFDI = 'CP01';

        $contenido_xml_array->Comprobante->Conceptos[0] = new stdClass();
        $contenido_xml_array->Comprobante->Conceptos[0]->ClaveProdServ = '84111506';
        $contenido_xml_array->Comprobante->Conceptos[0]->Cantidad = '1';
        $contenido_xml_array->Comprobante->Conceptos[0]->ClaveUnidad = 'ACT';
        $contenido_xml_array->Comprobante->Conceptos[0]->Descripcion = 'Pago';
        $contenido_xml_array->Comprobante->Conceptos[0]->ValorUnitario = '0';
        $contenido_xml_array->Comprobante->Conceptos[0]->ObjetoImp = '01';
        $contenido_xml_array->Comprobante->Conceptos[0]->Importe = '0';


        $contenido_xml_array->Comprobante->Complemento[0] = new stdClass();
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20 = new stdClass();

        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Version = '2.0';

        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Totales = new stdClass();

        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Totales->TotalTrasladosBaseIVA16 = '100';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Totales->TotalTrasladosImpuestoIVA16 = '16';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Totales->MontoTotalPagos = '116';

        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0] = new stdClass();
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->FechaPago = '2022-09-09T17:33:38';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->FormaDePagoP = '01';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->MonedaP = 'MXN';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->TipoCambioP = '1';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->Monto = '116';


        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0] = new stdClass();
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->IdDocumento = 'b7c8d2bf-cb4e-4f84-af89-c68b6731206a';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->Serie = 'FA';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->Folio = 'N0000216349';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->MonedaDR = 'MXN';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->EquivalenciaDR = '1';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->NumParcialidad = '2';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->ImpSaldoAnt = '116';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->ImpPagado = '116';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->ImpSaldoInsoluto = '0.00';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->ObjetoImpDR = '02';

        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->ImpuestosDR = new stdClass();
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->ImpuestosDR->TrasladosDR[0] = new stdClass();

        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->ImpuestosDR->TrasladosDR[0]->BaseDR = '100';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->ImpuestosDR->TrasladosDR[0]->ImpuestoDR = '002';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->ImpuestosDR->TrasladosDR[0]->TipoFactorDR = 'Tasa';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->ImpuestosDR->TrasladosDR[0]->TasaOCuotaDR = '0.160000';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->DoctoRelacionado[0]->ImpuestosDR->TrasladosDR[0]->ImporteDR = '16';

        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->ImpuestosP = new stdClass();
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->ImpuestosP->TrasladosP[0] = new stdClass();

        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->ImpuestosP->TrasladosP[0]->BaseP = '100';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->ImpuestosP->TrasladosP[0]->ImpuestoP = '002';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->ImpuestosP->TrasladosP[0]->TipoFactorP = 'Tasa';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->ImpuestosP->TrasladosP[0]->TasaOCuotaP = '0.160000';
        $contenido_xml_array->Comprobante->Complemento[0]->Pagos20->Pago[0]->ImpuestosP->TrasladosP[0]->ImporteP = '16';

        $contenido_json = json_encode($contenido_xml_array);
        //print_r($contenido_json);exit;

       // print_r($contenido_json);exit;

        $ruta_key_pem = '/var/www/html/xml_cfdi_4/tests/files/CSD_EKU9003173C9_key.pem';
        $ruta_cer_pem = '/var/www/html/xml_cfdi_4/tests/files/CSD_EKU9003173C9_cer.pem';
        $id_comprobante = '';

        $resultado = $timbra->timbra(contenido_xml: $contenido_json, id_comprobante: $id_comprobante,
            ruta_cer_pem: $ruta_cer_pem, ruta_key_pem: $ruta_key_pem, pac_prov: 'facturalo');

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertNotEmpty($resultado->uuid);
        $this->assertStringContainsStringIgnoringCase('CfdiRelacionados TipoRelacion="04"><cfdi:CfdiRelacionado UUID="6c76',$resultado->xml_sellado);




    }




}

