<?php
namespace tests\controllers;
use gamboamartin\errores\errores;
use gamboamartin\test\test;

class indexTest extends test {
    public function test_complemento_pago(): void
    {
        errores::$error = false;

        $url = 'http://localhost/xml_cfdi_4/index.php?tipo_de_comprobante=P';

        $curl = curl_init();

        $data_comprobante['lugar_expedicion'] = '29960';
        $data_comprobante['folio'] = '922';

        $data_emisor['rfc'] = 'IIA040805DZ4';
        $data_emisor['nombre'] = 'INDISTRIA ILUMINADORA DE ALMACENES';
        $data_emisor['regimen_fiscal'] = '626';

        $data_receptor['rfc'] = 'EKU9003173C9';
        $data_receptor['nombre'] = 'ESCUELA KEMPER URGATE';
        $data_receptor['domicilio_fiscal_receptor'] = '26015';
        $data_receptor['regimen_fiscal_receptor'] = '603';


        $data_pagos['total_traslados_base_iva_16'] = 1500;
        $data_pagos['total_traslados_impuesto_iva_16'] = 240;
        $data_pagos['monto_total_pagos'] = 1740;

        $data_pagos['pagos'][0]['fecha_pago'] = '2022-04-20T11:47:03';
        $data_pagos['pagos'][0]['forma_de_pago_p'] = '03';
        $data_pagos['pagos'][0]['moneda_p'] = 'MXN';
        $data_pagos['pagos'][0]['tipo_cambio_p'] = '1';
        $data_pagos['pagos'][0]['monto'] = '1740';

        $data_pagos['pagos'][0]['docto_relacionado'][0]['id_documento'] = '16c7d351-d88b-4b7e-bb6b-f8c3d5cc6cde';
        $data_pagos['pagos'][0]['docto_relacionado'][0]['folio'] = '921';
        $data_pagos['pagos'][0]['docto_relacionado'][0]['moneda_dr'] = 'MXN';
        $data_pagos['pagos'][0]['docto_relacionado'][0]['equivalencia_dr'] = '1';
        $data_pagos['pagos'][0]['docto_relacionado'][0]['num_parcialidad'] = '1';
        $data_pagos['pagos'][0]['docto_relacionado'][0]['imp_saldo_ant'] = '1740';
        $data_pagos['pagos'][0]['docto_relacionado'][0]['imp_pagado'] = '1740';
        $data_pagos['pagos'][0]['docto_relacionado'][0]['imp_saldo_insoluto'] = '0';
        $data_pagos['pagos'][0]['docto_relacionado'][0]['objeto_imp_dr'] = '02';
        $data_pagos['pagos'][0]['docto_relacionado'][0]['impuestos_dr'][0]['traslados_dr'][0]['traslado_dr'][0]['base_dr'] = '1500' ;
        $data_pagos['pagos'][0]['docto_relacionado'][0]['impuestos_dr'][0]['traslados_dr'][0]['traslado_dr'][0]['impuesto_dr'] = '002' ;
        $data_pagos['pagos'][0]['docto_relacionado'][0]['impuestos_dr'][0]['traslados_dr'][0]['traslado_dr'][0]['tipo_factor_dr'] = 'Tasa' ;
        $data_pagos['pagos'][0]['docto_relacionado'][0]['impuestos_dr'][0]['traslados_dr'][0]['traslado_dr'][0]['tasa_o_cuota_dr'] = '0.160000' ;
        $data_pagos['pagos'][0]['docto_relacionado'][0]['impuestos_dr'][0]['traslados_dr'][0]['traslado_dr'][0]['importe_dr'] = '240' ;

        $data_pagos['pagos'][0]['impuestos_p'][0]['traslados_p'][0]['traslado_p'][0]['base_p'] = '1500';
        $data_pagos['pagos'][0]['impuestos_p'][0]['traslados_p'][0]['traslado_p'][0]['impuesto_p'] = '002';
        $data_pagos['pagos'][0]['impuestos_p'][0]['traslados_p'][0]['traslado_p'][0]['tipo_factor_p'] = 'Tasa';
        $data_pagos['pagos'][0]['impuestos_p'][0]['traslados_p'][0]['traslado_p'][0]['tasa_o_cuota_p'] = '0.160000';
        $data_pagos['pagos'][0]['impuestos_p'][0]['traslados_p'][0]['traslado_p'][0]['importe_p'] = '240';



        $fields['comprobante'] = $data_comprobante;
        $fields['pagos'] = $data_pagos;
        $fields['emisor'] = $data_emisor;
        $fields['receptor'] = $data_receptor;

        $fields_string = http_build_query($fields);


        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


        $data = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($data,true);

        $this->assertStringContainsStringIgnoringCase('<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" ',$data['xml']);



    }
}
