<?php
namespace gamboamartin\xml_cfdi_4;
use DOMElement;
use DOMException;
use gamboamartin\errores\errores;
use stdClass;
use Throwable;

class nomina{
    private validacion $valida;
    private errores $error;
    public function __construct(){
        $this->valida = new validacion();
        $this->error = new errores();
    }

    public function nodo_nominas(DOMElement $nodo_complemento, stdClass $nomina, xml $xml): bool|DOMElement|array
    {

        try {
            $nodo_nominas = $xml->dom->createElementNS($xml->cfdi->comprobante->xmlns_nomina12, 'nomina12:Nomina');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento nomina12:Nomina', data: $e);
        }

        $nodo_complemento->appendChild($nodo_nominas);

        $keys = array('tipo_nomina','fecha_pago','fecha_inicial_pago','fecha_final_pago','num_dias_pagados',
            'total_percepciones','total_deducciones');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }


        $nodo_nominas->setAttribute('xmlns:nomina12', $xml->cfdi->comprobante->xmlns_nomina12);
        $nodo_nominas->setAttribute('Version', '1.2');
        $nodo_nominas->setAttribute('TipoNomina', $nomina->tipo_nomina);
        $nodo_nominas->setAttribute('FechaPago', $nomina->fecha_pago);
        $nodo_nominas->setAttribute('FechaInicialPago',$nomina->fecha_inicial_pago);
        $nodo_nominas->setAttribute('FechaFinalPago',$nomina->fecha_final_pago);
        $nodo_nominas->setAttribute('NumDiasPagados',$nomina->num_dias_pagados);
        $nodo_nominas->setAttribute('TotalPercepciones',$nomina->total_percepciones);
        $nodo_nominas->setAttribute('TotalDeducciones',$nomina->total_deducciones);

        if(isset($nomina->total_otros_pagos)) {
            $nodo_nominas->setAttribute('TotalOtrosPagos', $nomina->total_otros_pagos);
        }



        return $nodo_nominas;
    }

    public function nodo_nominas_haberes(DOMElement $nodo_complemento, stdClass $nomina, xml $xml): bool|DOMElement|array
    {

        try {
            $nodo_nominas = $xml->dom->createElementNS($xml->cfdi->comprobante->xmlns_nomina12, 'nomina12:Nomina');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento nomina12:Nomina', data: $e);
        }

        $nodo_complemento->appendChild($nodo_nominas);

        $keys = array('tipo_nomina','fecha_pago','fecha_inicial_pago','fecha_final_pago','num_dias_pagados',
            'total_percepciones');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $nodo_nominas->setAttribute('xmlns:nomina12', $xml->cfdi->comprobante->xmlns_nomina12);
        $nodo_nominas->setAttribute('Version', '1.2');
        $nodo_nominas->setAttribute('TipoNomina', $nomina->tipo_nomina);
        $nodo_nominas->setAttribute('FechaPago', $nomina->fecha_pago);
        $nodo_nominas->setAttribute('FechaInicialPago',$nomina->fecha_inicial_pago);
        $nodo_nominas->setAttribute('FechaFinalPago',$nomina->fecha_final_pago);
        $nodo_nominas->setAttribute('NumDiasPagados',$nomina->num_dias_pagados);
        $nodo_nominas->setAttribute('TotalPercepciones',$nomina->total_percepciones);

        return $nodo_nominas;
    }

    public function nodo_nominas_emisor(DOMElement $nodo_nominas, stdClass $nomina, xml $xml): bool|DOMElement|array
    {

        try {
            $nodo_nomina_emisor = $xml->dom->createElement('nomina12:Emisor');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento nomina12:Emisor', data: $e);
        }

        $keys = array('emisor');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $keys = array('registro_patronal','rfc_patron_origen');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina->emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $nodo_nominas->appendChild($nodo_nomina_emisor);

        $nodo_nomina_emisor->setAttribute('RegistroPatronal', $nomina->emisor->registro_patronal);
        $nodo_nomina_emisor->setAttribute('RfcPatronOrigen', $nomina->emisor->rfc_patron_origen);



        return $nodo_nomina_emisor;
    }

    public function nodo_nominas_receptor(DOMElement $nodo_nominas, stdClass $nomina, xml $xml): bool|DOMElement|array
    {

        try {
            $nodo_nomina_receptor = $xml->dom->createElement('nomina12:Receptor');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento nomina12:Emisor', data: $e);
        }

        $keys = array('receptor');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $keys = array('curp','num_seguridad_social','fecha_inicio_rel_laboral','antiguedad','tipo_contrato',
            'tipo_jornada','tipo_regimen','num_empleado','departamento','puesto','riesgo_puesto','periodicidad_pago',
            'cuenta_bancaria','banco','salario_base_cot_apor','salario_diario_integrado','clave_ent_fed');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina->receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $nodo_nominas->appendChild($nodo_nomina_receptor);

        $nodo_nomina_receptor->setAttribute('Curp', $nomina->receptor->curp);
        $nodo_nomina_receptor->setAttribute('NumSeguridadSocial', $nomina->receptor->num_seguridad_social);
        $nodo_nomina_receptor->setAttribute('FechaInicioRelLaboral', $nomina->receptor->fecha_inicio_rel_laboral);
        $nodo_nomina_receptor->setAttribute('Antigüedad', $nomina->receptor->antiguedad);
        $nodo_nomina_receptor->setAttribute('TipoContrato', $nomina->receptor->tipo_contrato);
        $nodo_nomina_receptor->setAttribute('TipoJornada', $nomina->receptor->tipo_jornada);
        $nodo_nomina_receptor->setAttribute('TipoRegimen', $nomina->receptor->tipo_regimen);
        $nodo_nomina_receptor->setAttribute('NumEmpleado', $nomina->receptor->num_empleado);
        $nodo_nomina_receptor->setAttribute('Departamento', $nomina->receptor->departamento);
        $nodo_nomina_receptor->setAttribute('Puesto', $nomina->receptor->puesto);
        $nodo_nomina_receptor->setAttribute('RiesgoPuesto', $nomina->receptor->riesgo_puesto);
        $nodo_nomina_receptor->setAttribute('PeriodicidadPago', $nomina->receptor->periodicidad_pago);
        $nodo_nomina_receptor->setAttribute('CuentaBancaria', $nomina->receptor->cuenta_bancaria);
        if(!isset($nomina->receptor->cuenta_bancaria)){
            $nodo_nomina_receptor->setAttribute('Banco', $nomina->receptor->banco);
        }
        $nodo_nomina_receptor->setAttribute('SalarioBaseCotApor', $nomina->receptor->salario_base_cot_apor);
        $nodo_nomina_receptor->setAttribute('SalarioDiarioIntegrado', $nomina->receptor->salario_diario_integrado);
        $nodo_nomina_receptor->setAttribute('ClaveEntFed', $nomina->receptor->clave_ent_fed);

        return $nodo_nomina_receptor;
    }

    public function nodo_nominas_percepciones(DOMElement $nodo_nominas, stdClass $nomina, xml $xml): bool|DOMElement|array
    {

        try {
            $nodo_nomina_percepciones = $xml->dom->createElement('nomina12:Percepciones');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento nomina12:Emisor', data: $e);
        }

        $keys = array('percepciones');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $keys = array('total_sueldos','total_gravado','total_exento');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina->percepciones);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $nodo_nominas->appendChild($nodo_nomina_percepciones);

        $nodo_nomina_percepciones->setAttribute('TotalSueldos', $nomina->percepciones->total_sueldos);
        $nodo_nomina_percepciones->setAttribute('TotalGravado', $nomina->percepciones->total_gravado);
        $nodo_nomina_percepciones->setAttribute('TotalExento', $nomina->percepciones->total_exento);

        return $nodo_nomina_percepciones;
    }

    public function nodo_nominas_deducciones(DOMElement $nodo_nominas, stdClass $nomina, xml $xml): bool|DOMElement|array
    {
        try {
            $nodo_nomina_deducciones = $xml->dom->createElement('nomina12:Deducciones');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento nomina12:Emisor', data: $e);
        }

        $keys = array('deducciones');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $keys = array('total_otras_deducciones','total_impuestos_retenidos');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina->deducciones);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $nodo_nominas->appendChild($nodo_nomina_deducciones);

        $nodo_nomina_deducciones->setAttribute('TotalOtrasDeducciones',
            $nomina->deducciones->total_otras_deducciones);
        $nodo_nomina_deducciones->setAttribute('TotalImpuestosRetenidos',
            $nomina->deducciones->total_impuestos_retenidos);

        return $nodo_nomina_deducciones;
    }

    public function nodo_nominas_otros_pagos(DOMElement $nodo_nominas, stdClass $nomina, xml $xml): bool|DOMElement|array
    {
        try {
            $nodo_nomina_otros_pagos = $xml->dom->createElement('nomina12:OtrosPagos');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento nomina12:OtrosPagos', data: $e);
        }

        $keys = array('otros_pagos');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }


        $nodo_nominas->appendChild($nodo_nomina_otros_pagos);


        return $nodo_nomina_otros_pagos;
    }

}
