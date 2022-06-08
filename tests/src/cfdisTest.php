<?php
namespace tests\controllers;


use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use gamboamartin\xml_cfdi_4\cfdis;
use gamboamartin\xml_cfdi_4\complementos;
use gamboamartin\xml_cfdi_4\xml;
use stdClass;

class cfdisTest extends test {
    public errores $errores;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();

    }

    public function test_complemento_pago(): void
    {
        errores::$error = false;

        $cfdis = new cfdis();
        //$com = new liberator($com);

        $comprobante = new stdClass();
        $comprobante->lugar_expedicion  = 29960;
        $comprobante->folio  = 922;

        $emisor = new stdClass();

        $emisor->rfc = 'IIA040805DZ4';
        $emisor->nombre = 'INDISTRIA ILUMINADORA DE ALMACENES';
        $emisor->regimen_fiscal = '626';

        $receptor = new stdClass();
        $receptor->rfc = 'EKU9003173C9';
        $receptor->nombre = 'ESCUELA KEMPER URGATE';
        $receptor->domicilio_fiscal_receptor = '26015';
        $receptor->regimen_fiscal_receptor = '603';


        $pagos = new stdClass();
        $pagos->total_traslados_base_iva_16 = '1500';
        $pagos->total_traslados_impuesto_iva_16 = '240';
        $pagos->monto_total_pagos = '1740';

        $pagos->pagos = array();
        $pagos->pagos[0] = new stdClass();

        $pagos->pagos[0]->fecha_pago = '2022-04-20T11:47:03';
        $pagos->pagos[0]->forma_de_pago_p = 'a';
        $pagos->pagos[0]->moneda_p = 'a';
        $pagos->pagos[0]->tipo_cambio_p = '1';
        $pagos->pagos[0]->monto = '1';
        $pagos->pagos[0]->docto_relacionado = array();
        $pagos->pagos[0]->docto_relacionado[0] = new stdClass();
        $pagos->pagos[0]->docto_relacionado[0]->id_documento = 'a';
        $pagos->pagos[0]->docto_relacionado[0]->folio = 'a';
        $pagos->pagos[0]->docto_relacionado[0]->moneda_dr = 'a';
        $pagos->pagos[0]->docto_relacionado[0]->equivalencia_dr = '1';
        $pagos->pagos[0]->docto_relacionado[0]->num_parcialidad = '1.054';
        $pagos->pagos[0]->docto_relacionado[0]->imp_saldo_ant = '1';
        $pagos->pagos[0]->docto_relacionado[0]->imp_pagado = '1';
        $pagos->pagos[0]->docto_relacionado[0]->imp_saldo_insoluto = '1';
        $pagos->pagos[0]->docto_relacionado[0]->objeto_imp_dr = 'a';
        $pagos->pagos[0]->docto_relacionado[0]->impuestos_dr = array();
        $pagos->pagos[0]->docto_relacionado[0]->impuestos_dr[0] = new stdClass();

        $pagos->pagos[0]->docto_relacionado[0]->impuestos_dr[0]->traslados_dr[0] = new stdClass();
        $pagos->pagos[0]->docto_relacionado[0]->impuestos_dr[0]->traslados_dr[0]->traslado_dr = array();
        $pagos->pagos[0]->docto_relacionado[0]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0] = new stdClass();
        $pagos->pagos[0]->docto_relacionado[0]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0]->base_dr = '1';
        $pagos->pagos[0]->docto_relacionado[0]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0]->impuesto_dr = '1';
        $pagos->pagos[0]->docto_relacionado[0]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0]->tipo_factor_dr = 'a';
        $pagos->pagos[0]->docto_relacionado[0]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0]->tasa_o_cuota_dr = '1';
        $pagos->pagos[0]->docto_relacionado[0]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0]->importe_dr = '1';

        $pagos->pagos[0]->impuestos_p= array();
        $pagos->pagos[0]->impuestos_p[0]= new stdClass();
        $pagos->pagos[0]->impuestos_p[0]->traslados_p = array();
        $pagos->pagos[0]->impuestos_p[0]->traslados_p[0] = new stdClass();
        $pagos->pagos[0]->impuestos_p[0]->traslados_p[0]->traslado_p = array();
        $pagos->pagos[0]->impuestos_p[0]->traslados_p[0]->traslado_p[0] = new stdClass() ;
        $pagos->pagos[0]->impuestos_p[0]->traslados_p[0]->traslado_p[0]->base_p = '1';
        $pagos->pagos[0]->impuestos_p[0]->traslados_p[0]->traslado_p[0]->impuesto_p = 'a';
        $pagos->pagos[0]->impuestos_p[0]->traslados_p[0]->traslado_p[0]->tipo_factor_p = 'a';
        $pagos->pagos[0]->impuestos_p[0]->traslados_p[0]->traslado_p[0]->tasa_o_cuota_p = '1';
        $pagos->pagos[0]->impuestos_p[0]->traslados_p[0]->traslado_p[0]->importe_p = '1';


        $resultado = $cfdis->complemento_pago(comprobante: $comprobante,emisor:  $emisor, pagos: $pagos, receptor: $receptor);



        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase('<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"',$resultado);
        $this->assertStringContainsStringIgnoringCase('"http://www.sat.gob.mx/cfd/4" xm',$resultado);
        $this->assertStringContainsStringIgnoringCase('mlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',$resultado);
        $this->assertStringContainsStringIgnoringCase(' xmlns:pago20="http://www.sat.gob.mx/Pagos20"',$resultado);
        $this->assertStringContainsStringIgnoringCase('xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd "',$resultado);
        $this->assertStringContainsStringIgnoringCase(' Moneda="XXX" Total="0" Exportacion="01" TipoDeComprobante="P" SubTotal="0"',$resultado);
        $this->assertStringContainsStringIgnoringCase('="0" LugarExpedicion="29960"',$resultado);
        $this->assertStringContainsStringIgnoringCase('sor Rfc="IIA040805DZ4" Nombre="INDISTRIA ILUMINADORA DE ALMACENES" RegimenFiscal="626"/><',$resultado);
        $this->assertStringContainsStringIgnoringCase('cal="626"/><cfdi:Receptor Rfc="EKU9003173C9" Nombre="ESCUELA KEMPER URGATE" Domicil',$resultado);
        $this->assertStringContainsStringIgnoringCase('icilioFiscalReceptor="26015" RegimenFiscalReceptor="603" UsoCfdi="CP01"/><cfdi:Con',$resultado);
        $this->assertStringContainsStringIgnoringCase('otalTrasladosImpuestoIVA16="240.00" MontoTotalPagos="1740.00"/><pago20:Pag',$resultado);


        errores::$error = false;
    }




}

