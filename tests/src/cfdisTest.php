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

    public function test_complemento_a_cuenta_terceros(): void
    {
        errores::$error = false;

        $cfdis = new cfdis();
        //$com = new liberator($com);

        $comprobante = new stdClass();
        $comprobante->folio  = 922;
        $comprobante->forma_pago  = '01';
        $comprobante->sub_total  = 1050.00;
        $comprobante->moneda  = 'MXN';
        $comprobante->total  = 1218.00;
        $comprobante->lugar_expedicion  = 29960;

        $emisor = new stdClass();

        $emisor->rfc = 'IIA040805DZ4';
        $emisor->nombre = 'INDISTRIA ILUMINADORA DE ALMACENES';
        $emisor->regimen_fiscal = '626';

        $receptor = new stdClass();
        $receptor->rfc = 'EKU9003173C9';
        $receptor->nombre = 'ESCUELA KEMPER URGATE';
        $receptor->domicilio_fiscal_receptor = '26015';
        $receptor->regimen_fiscal_receptor = '603';
        $receptor->uso_cfdi = 'G03';

        $conceptos = array();
        $conceptos[0] = new stdClass();
        $conceptos[0]->clave_prod_serv = '84111506';
        $conceptos[0]->cantidad = '1';
        $conceptos[0]->clave_unidad = 'ACT';
        $conceptos[0]->descripcion = 'Pago';
        $conceptos[0]->valor_unitario = '0';
        $conceptos[0]->importe = '0';
        $conceptos[0]->objeto_imp = '01';
        $conceptos[0]->no_identificacion = '400578';
        $conceptos[0]->impuestos = array();
        $conceptos[0]->impuestos[0]= new stdClass();
        $conceptos[0]->impuestos[0]->traslados = array();
        $conceptos[0]->impuestos[0]->traslados[0] = new stdClass();
        $conceptos[0]->impuestos[0]->traslados[0]->base = '1';
        $conceptos[0]->impuestos[0]->traslados[0]->impuesto = 'a';
        $conceptos[0]->impuestos[0]->traslados[0]->tipo_factor = 'a';
        $conceptos[0]->impuestos[0]->traslados[0]->tasa_o_cuota = '1';
        $conceptos[0]->impuestos[0]->traslados[0]->importe = '1';

        $conceptos[0]->a_cuanta_terceros = array();
        $conceptos[0]->a_cuanta_terceros[0] = new stdClass();
        $conceptos[0]->a_cuanta_terceros[0]->rfc_acuenta_terceros = 'JUFA7608212V6';
        $conceptos[0]->a_cuanta_terceros[0]->nombre_a_cuenta_terceros = 'ADRIANA JUAREZ FERNANDEZ';
        $conceptos[0]->a_cuanta_terceros[0]->regimen_fiscal_a_cuenta_terceros = '612';
        $conceptos[0]->a_cuanta_terceros[0]->domicilio_fiscal_a_cuenta_terceros = '29133';

        $impuestos = new stdClass();
        $impuestos->total_impuestos_trasladados = '240.00';

        $impuestos->traslados = array();
        $impuestos->traslados[0] = new stdClass();
        $impuestos->traslados[0]->base = '1500.00';
        $impuestos->traslados[0]->impuesto = '002';
        $impuestos->traslados[0]->tipo_factor = 'Tasa';
        $impuestos->traslados[0]->tasa_o_cuota = '0.160000';
        $impuestos->traslados[0]->importe = '240.00';

        $resultado = $cfdis->complemento_a_cuenta_terceros(comprobante: $comprobante,conceptos_a: $conceptos,
            emisor:  $emisor, impuestos: $impuestos, receptor: $receptor);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase('<cfdi:Comprobante xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',$resultado);
        $this->assertStringContainsStringIgnoringCase('<cfdi:Conceptos><cfdi:Concepto ClaveProdServ="84111506" NoIdentificacion="400578"',$resultado);
        $this->assertStringContainsStringIgnoringCase('</cfdi:Impuestos><cfdi:ACuentaTerceros RfcACuentaTerceros="JUFA7608212V6"',$resultado);
        $this->assertStringContainsStringIgnoringCase('RegimenFiscalACuentaTerceros="612" DomicilioFiscalACuentaTerceros="29133"/>',$resultado);

        errores::$error = false;
    }

    public function test_complemento_nomina(): void
    {
        errores::$error = false;

        $cfdis = new cfdis();
        //$com = new liberator($com);
        $comprobante = new stdClass();
        $comprobante->lugar_expedicion = 20000;
        $comprobante->folio = 'Folio';
        $comprobante->total = '4700';
        $comprobante->sub_total = '5000';
        $comprobante->serie = 'Serie';
        $comprobante->descuento = '300';
        $comprobante->tipo_cambio = '1';
        $comprobante->metodo_pago = 'PPD';

        $emisor = new stdClass();

        $emisor->rfc = 'EKU9003173C9';
        $emisor->nombre = 'ESCUELA KEMPER URGATE';
        $emisor->regimen_fiscal = '601';

        $receptor = new stdClass();
        $receptor->rfc = 'XOJI740919U48';
        $receptor->nombre = 'INGRID XODAR JIMENEZ';
        $receptor->domicilio_fiscal_receptor = '88965';
        $receptor->regimen_fiscal_receptor = '605';


        $nomina = new stdClass();
        $nomina->tipo_nomina = 'O';
        $nomina->fecha_pago = '2021-12-24';
        $nomina->fecha_inicial_pago = '2021-12-09';
        $nomina->fecha_final_pago = '2021-12-24';
        $nomina->num_dias_pagados = '15';
        $nomina->total_percepciones = '5000';
        $nomina->total_deducciones = '300';
        $nomina->emisor = new stdClass();
        $nomina->emisor->registro_patronal = 'B5510768108';
        $nomina->emisor->rfc_patron_origen = 'URE180429TM6';

        $nomina->receptor = new stdClass();
        $nomina->receptor->curp = 'XEXX010101HNEXXXA4';
        $nomina->receptor->num_seguridad_social = '000000';
        $nomina->receptor->fecha_inicio_rel_laboral = '2015-01-01';
        $nomina->receptor->antiguedad = '2015-01-01';
        $nomina->receptor->tipo_contrato = '01';
        $nomina->receptor->tipo_jornada = '01';
        $nomina->receptor->tipo_regimen = '03';
        $nomina->receptor->num_empleado = '120';
        $nomina->receptor->departamento = 'Desarrollo';
        $nomina->receptor->puesto = 'Ingeniero de Software';
        $nomina->receptor->riesgo_puesto = '1';
        $nomina->receptor->periodicidad_pago = '04';
        $nomina->receptor->cuenta_bancaria = '1111111111';
        $nomina->receptor->banco = '002';
        $nomina->receptor->salario_base_cot_apor = '490.22';
        $nomina->receptor->salario_diario_integrado = '146.47';
        $nomina->receptor->clave_ent_fed = 'JAL';
        
        $nomina->percepciones = new stdClass();
        $nomina->percepciones->total_sueldos = '02';
        $nomina->percepciones->total_gravado = '02';
        $nomina->percepciones->total_exento = '02';
        /*$nomina->percepciones->percepcion = array();
        $nomina->percepciones->percepcion[0] = new stdClass();
        $nomina->percepciones->percepcion[0]->tipo_percepcion = '';
        $nomina->percepciones->percepcion[0]->clave = '';
        $nomina->percepciones->percepcion[0]->concepto = '';
        $nomina->percepciones->percepcion[0]->importe_gravado = '';
        $nomina->percepciones->percepcion[0]->importe_exento = '';
        
        $nomina->deducciones = new stdClass();
        $nomina->deducciones->totalo_otras_deducciones = '02';
        $nomina->deducciones->total_impuestos_retenidos = '02';

        $nomina->deducciones->deduccion = array();
        $nomina->deducciones->deduccion[0] = new stdClass();
        $nomina->deducciones->deduccion[0]->tipo_deduccion = '';
        $nomina->deducciones->deduccion[0]->clave = '';
        $nomina->deducciones->deduccion[0]->concepto = '';
        $nomina->deducciones->deduccion[0]->importe = '';

        $nomina->deducciones->deduccion[1] = new stdClass();
        $nomina->deducciones->deduccion[1]->tipo_deduccion = '';
        $nomina->deducciones->deduccion[1]->clave = '';
        $nomina->deducciones->deduccion[1]->concepto = '';
        $nomina->deducciones->deduccion[1]->importe = '';*/
        

        $resultado = $cfdis->complemento_nomina(comprobante: $comprobante,emisor:  $emisor, nomina: $nomina,receptor:  $receptor);
       print_r($resultado);exit;

        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);

        //print_r($resultado);exit;
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
        $this->assertStringContainsStringIgnoringCase('<cfdi:Comprobante xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',$resultado);
        $this->assertStringContainsStringIgnoringCase('xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xsi:schemaLocation',$resultado);
        $this->assertStringContainsStringIgnoringCase('mlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',$resultado);
        $this->assertStringContainsStringIgnoringCase(' xmlns:pago20="http://www.sat.gob.mx/Pagos20"',$resultado);
        $this->assertStringContainsStringIgnoringCase('xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/Pagos20',$resultado);
        $this->assertStringContainsStringIgnoringCase(' Moneda="XXX" Total="0" Exportacion="01" TipoDeComprobante="P" SubTotal="0"',$resultado);
        $this->assertStringContainsStringIgnoringCase('="0" LugarExpedicion="29960"',$resultado);
        $this->assertStringContainsStringIgnoringCase('sor Rfc="IIA040805DZ4" Nombre="INDISTRIA ILUMINADORA DE ALMACENES" RegimenFiscal="626"/><',$resultado);
        $this->assertStringContainsStringIgnoringCase('cal="626"/><cfdi:Receptor Rfc="EKU9003173C9" Nombre="ESCUELA KEMPER URGATE" Domicil',$resultado);
        $this->assertStringContainsStringIgnoringCase('icilioFiscalReceptor="26015" RegimenFiscalReceptor="603" UsoCfdi="CP01"/><cfdi:Con',$resultado);
        $this->assertStringContainsStringIgnoringCase('otalTrasladosImpuestoIVA16="240.00" MontoTotalPagos="1740.00"/><pago20:Pag',$resultado);

        errores::$error  = false;


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

        $pagos->pagos[0]->docto_relacionado[1] = new stdClass();
        $pagos->pagos[0]->docto_relacionado[1]->id_documento = 'a';
        $pagos->pagos[0]->docto_relacionado[1]->folio = 'a';
        $pagos->pagos[0]->docto_relacionado[1]->moneda_dr = 'a';
        $pagos->pagos[0]->docto_relacionado[1]->equivalencia_dr = '1';
        $pagos->pagos[0]->docto_relacionado[1]->num_parcialidad = '1.054';
        $pagos->pagos[0]->docto_relacionado[1]->imp_saldo_ant = '1';
        $pagos->pagos[0]->docto_relacionado[1]->imp_pagado = '1';
        $pagos->pagos[0]->docto_relacionado[1]->imp_saldo_insoluto = '1';
        $pagos->pagos[0]->docto_relacionado[1]->objeto_imp_dr = 'a';
        $pagos->pagos[0]->docto_relacionado[1]->impuestos_dr = array();
        $pagos->pagos[0]->docto_relacionado[1]->impuestos_dr[0] = new stdClass();

        $pagos->pagos[0]->docto_relacionado[1]->impuestos_dr[0]->traslados_dr[0] = new stdClass();
        $pagos->pagos[0]->docto_relacionado[1]->impuestos_dr[0]->traslados_dr[0]->traslado_dr = array();
        $pagos->pagos[0]->docto_relacionado[1]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0] = new stdClass();
        $pagos->pagos[0]->docto_relacionado[1]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0]->base_dr = '1';
        $pagos->pagos[0]->docto_relacionado[1]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0]->impuesto_dr = '1';
        $pagos->pagos[0]->docto_relacionado[1]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0]->tipo_factor_dr = 'a';
        $pagos->pagos[0]->docto_relacionado[1]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0]->tasa_o_cuota_dr = '1';
        $pagos->pagos[0]->docto_relacionado[1]->impuestos_dr[0]->traslados_dr[0]->traslado_dr[0]->importe_dr = '55';


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
        $this->assertStringContainsStringIgnoringCase('<cfdi:Comprobante xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',$resultado);
        $this->assertStringContainsStringIgnoringCase('TasaOCuotaDR="1.000000" ImporteDR="55.00"',$resultado);
        $this->assertStringContainsStringIgnoringCase('TasaOCuotaDR="1.000000" ImporteDR="1.00"',$resultado);



        errores::$error = false;
    }


    public function test_completo_nota_credito(): void
    {
        errores::$error = false;

        $cfdis = new cfdis();
        //$com = new liberator($com);
        $comprobante = new stdClass();
        $comprobante->serie  = 'NCV4.0';
        $comprobante->folio  = 922;
        $comprobante->forma_pago  = '01';
        $comprobante->sub_total  = 1050.00;
        $comprobante->moneda  = 'MXN';
        $comprobante->total  = 1218.00;
        $comprobante->lugar_expedicion  = 29960;

        $emisor = new stdClass();
        $emisor->rfc = 'IIA040805DZ4';
        $emisor->nombre = 'INDISTRIA ILUMINADORA DE ALMACENES';
        $emisor->regimen_fiscal = '626';

        $receptor = new stdClass();
        $receptor->rfc = 'EKU9003173C9';
        $receptor->nombre = 'ESCUELA KEMPER URGATE';
        $receptor->domicilio_fiscal_receptor = '26015';
        $receptor->regimen_fiscal_receptor = '603';
        $receptor->uso_cfdi = 'G01';


        $relacionados = new stdClass();
        $relacionados->tipo_relacion = '02';

        $relacionados->relaciones = array();
        $relacionados->relaciones[0] = new stdClass();
        $relacionados->relaciones[0]->uuid = '7945A043-3073-4295-BC0B-C17AFB6697A5';

        $conceptos = array();
        $conceptos[0] = new stdClass();
        $conceptos[0]->clave_prod_serv = '84111506';
        $conceptos[0]->cantidad = '1';
        $conceptos[0]->clave_unidad = 'ACT';
        $conceptos[0]->descripcion = 'Pago';
        $conceptos[0]->valor_unitario = '0';
        $conceptos[0]->importe = '0';
        $conceptos[0]->objeto_imp = '01';
        $conceptos[0]->no_identificacion = '400578';
        $conceptos[0]->unidad = 'Caja';
        $conceptos[0]->impuestos = array();
        $conceptos[0]->impuestos[0]= new stdClass();
        $conceptos[0]->impuestos[0]->traslados = array();
        $conceptos[0]->impuestos[0]->traslados[0] = new stdClass();
        $conceptos[0]->impuestos[0]->traslados[0]->base = '1';
        $conceptos[0]->impuestos[0]->traslados[0]->impuesto = 'a';
        $conceptos[0]->impuestos[0]->traslados[0]->tipo_factor = 'a';
        $conceptos[0]->impuestos[0]->traslados[0]->tasa_o_cuota = '1';
        $conceptos[0]->impuestos[0]->traslados[0]->importe = '1';

        $impuestos = new stdClass();
        $impuestos->total_impuestos_trasladados = '168.00';

        $impuestos->traslados = array();
        $impuestos->traslados[0] = new stdClass();
        $impuestos->traslados[0]->base = '1';
        $impuestos->traslados[0]->impuesto = 'a';
        $impuestos->traslados[0]->tipo_factor = 'a';
        $impuestos->traslados[0]->tasa_o_cuota = '1';
        $impuestos->traslados[0]->importe = '1';

        $resultado = $cfdis->completo_nota_credito(comprobante: $comprobante, conceptos: $conceptos,
            emisor:  $emisor, impuestos: $impuestos,receptor: $receptor, relacionados: $relacionados);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase('<cfdi:Comprobante xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',$resultado);
        $this->assertStringContainsStringIgnoringCase('Moneda="MXN" Total="1218" Exportacion="01" TipoDeComprobante="E"',$resultado);
        $this->assertStringContainsStringIgnoringCase(' Serie="NCV4.0" FormaPago="01" MetodoPago="PUE"><cfdi:CfdiRelacionados',$resultado);
        $this->assertStringContainsStringIgnoringCase('UUID="7945A043-3073-4295-BC0B-C17AFB6697A5"/></cfdi:CfdiRelacion',$resultado);

        errores::$error = false;
    }

    public function test_init_base(): void
    {
        errores::$error = false;

        $cfdis = new cfdis();
        $cfdis = new liberator($cfdis);
        $comprobante = new stdClass();
        $comprobante->serie  = 'NCV4.0';
        $comprobante->folio  = 922;
        $comprobante->forma_pago  = '01';
        $comprobante->sub_total  = 1050.00;
        $comprobante->moneda  = 'MXN';
        $comprobante->total  = 1218.00;
        $comprobante->lugar_expedicion  = 29960;

        $emisor = new stdClass();
        $emisor->rfc = 'IIA040805DZ4';
        $emisor->nombre = 'INDISTRIA ILUMINADORA DE ALMACENES';
        $emisor->regimen_fiscal = '626';

        $receptor = new stdClass();
        $receptor->rfc = 'EKU9003173C9';
        $receptor->nombre = 'ESCUELA KEMPER URGATE';
        $receptor->domicilio_fiscal_receptor = '26015';
        $receptor->regimen_fiscal_receptor = '603';
        $receptor->uso_cfdi = 'G01';


        $resultado = $cfdis->init_base($comprobante, $emisor, $receptor);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('IIA040805DZ4',$resultado->emisor->rfc);
        $this->assertEquals('NCV4.0',$resultado->comprobante->serie);
        $this->assertEquals('603',$resultado->receptor->regimen_fiscal_receptor);
        errores::$error = false;
    }

    public function test_ingreso(){
        errores::$error = false;

        $cfdis = new cfdis();
        //$com = new liberator($com);
        $comprobante = new stdClass();
        $comprobante->serie  = 'NCV4.0';
        $comprobante->folio  = 922;
        $comprobante->forma_pago  = '01';
        $comprobante->sub_total  = 1050.00;
        $comprobante->moneda  = 'MXN';
        $comprobante->total  = 1218.00;
        $comprobante->lugar_expedicion  = 29960;
        $comprobante->tipo_de_comprobante  = 'I';
        $comprobante->exportacion  = '01';

        $emisor = new stdClass();
        $emisor->rfc = 'IIA040805DZ4';
        $emisor->nombre = 'INDISTRIA ILUMINADORA DE ALMACENES';
        $emisor->regimen_fiscal = '626';

        $receptor = new stdClass();
        $receptor->rfc = 'EKU9003173C9';
        $receptor->nombre = 'ESCUELA KEMPER URGATE';
        $receptor->domicilio_fiscal_receptor = '26015';
        $receptor->regimen_fiscal_receptor = '603';
        $receptor->uso_cfdi = 'G01';

        $conceptos = array();
        $conceptos[0] = new stdClass();
        $conceptos[0]->clave_prod_serv = '84111506';
        $conceptos[0]->cantidad = '1';
        $conceptos[0]->clave_unidad = 'ACT';
        $conceptos[0]->descripcion = 'Pago';
        $conceptos[0]->valor_unitario = '0';
        $conceptos[0]->importe = '0';
        $conceptos[0]->objeto_imp = '01';
        $conceptos[0]->no_identificacion = '400578';
        $conceptos[0]->unidad = 'Caja';
        $conceptos[0]->impuestos = array();
        $conceptos[0]->impuestos[0]= new stdClass();
        $conceptos[0]->impuestos[0]->traslados = array();
        $conceptos[0]->impuestos[0]->traslados[0] = new stdClass();
        $conceptos[0]->impuestos[0]->traslados[0]->base = '1';
        $conceptos[0]->impuestos[0]->traslados[0]->impuesto = 'a';
        $conceptos[0]->impuestos[0]->traslados[0]->tipo_factor = 'a';
        $conceptos[0]->impuestos[0]->traslados[0]->tasa_o_cuota = '1';
        $conceptos[0]->impuestos[0]->traslados[0]->importe = '1';

        $impuestos = new stdClass();
        $impuestos->total_impuestos_trasladados = '168.00';

        $impuestos->traslados = array();
        $impuestos->traslados[0] = new stdClass();
        $impuestos->traslados[0]->base = '1';
        $impuestos->traslados[0]->impuesto = 'a';
        $impuestos->traslados[0]->tipo_factor = 'a';
        $impuestos->traslados[0]->tasa_o_cuota = '1';
        $impuestos->traslados[0]->importe = '1';

        $resultado = $cfdis->ingreso(comprobante: $comprobante, conceptos: $conceptos,
            emisor:  $emisor, impuestos: $impuestos,receptor: $receptor);

        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase('<cfdi:Comprobante xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',$resultado);
        $this->assertStringContainsStringIgnoringCase('Moneda="MXN" Total="1218" Exportacion="01" TipoDeComprobante="I"',$resultado);



        errores::$error = false;
    }

}

