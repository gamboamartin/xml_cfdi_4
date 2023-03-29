<?php
namespace gamboamartin\xml_cfdi_4;

use gamboamartin\errores\errores;
use stdClass;

class validacion extends \gamboamartin\validacion\validacion{
    /**
     * Valida parametros de un complemento de pago
     * @version 0.1.0
     * @param stdClass $comprobante objeto con los datos del comprobante
     * @param xml $xml Objeto donde se genera el cfdi
     * @return bool|array
     */
    public function complemento_pago_comprobante(stdClass $comprobante, xml $xml): bool|array
    {
        if((float)$xml->cfdi->comprobante->total!==0.0){
            return $this->error->error(mensaje:'Error cuando tipo_de_comprobante sea P el total debe ser 0',
                data: $comprobante);
        }
        if((string)$xml->cfdi->comprobante->moneda !== 'XXX'){
            return $this->error->error(mensaje:'Error cuando tipo_de_comprobante sea P la moneda  debe ser XXX',
                data: $comprobante);
        }
        return true;
    }

    public function valida_concepto(mixed $concepto): bool|array
    {
        if(!is_object($concepto)){
            return $this->error->error(mensaje: 'Error el concepto debe ser un objeto', data: $concepto);
        }
        if(empty($concepto)){
            return $this->error->error(mensaje: 'Error el concepto puede venir vacio', data: $concepto);
        }
        return true;
    }

    /**
     * Valida que existan los elementos de un concepto
     * @param mixed $concepto Concepto a validar
     * @return bool|array
     */
    final public function valida_data_concepto(mixed $concepto): bool|array
    {
        $keys = array('clave_prod_serv','cantidad','importe','clave_unidad','descripcion',
            'valor_unitario', 'objeto_imp');
        $valida = $this->valida_existencia_keys(keys: $keys, registro: $concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $concepto', data: $valida);
        }
        $keys_ids = array('clave_prod_serv');
        $valida = $this->valida_ids(keys: $keys_ids, registro: $concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $concepto', data: $valida);
        }
        $keys_numerics = array('clave_prod_serv','cantidad','valor_unitario','importe');
        $valida = $this->valida_numerics(keys: $keys_numerics, row: $concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $concepto', data: $valida);
        }
        return  true;
    }

    public function valida_data_importe_concepto(mixed $concepto): bool|array
    {
        $keys = array('cantidad','valor_unitario');
        $valida = $this->valida_existencia_keys(keys: $keys, registro: $concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $concepto', data: $valida);
        }

        $keys_numerics = array('cantidad','valor_unitario');
        $valida = $this->valida_numerics(keys: $keys_numerics, row: $concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $concepto', data: $valida);
        }
        return true;
    }

    public function valida_data_impuestos(mixed $impuesto): bool|array
    {
        if(!is_object($impuesto)){
            return $this->error->error(mensaje: 'Error $impuesto debe ser un objeto', data: $impuesto);
        }
        if(!isset($impuesto->traslados)){
            return $this->error->error(mensaje: 'Error $impuesto->traslados debe existir', data: $impuesto);
        }
        if(!is_array($impuesto->traslados)){
            return $this->error->error(mensaje: 'Error $impuesto->traslados debe ser un array', data: $impuesto);
        }
        return true;
    }

    public function valida_data_pago(mixed $pago): bool|array
    {
        if(!is_object($pago)){
            return $this->error->error(mensaje: 'Error  el pago debe se run objeto', data: $pago);
        }

        $keys = array('fecha_pago','forma_de_pago_p','moneda_p','tipo_cambio_p','monto');
        $valida = $this->valida_existencia_keys(keys: $keys, registro: $pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar pago', data: $valida);
        }

        $keys = array('fecha_pago');
        $valida = $this->fechas_in_array(data: $pago, keys: $keys,tipo_val: 'fecha_hora_min_sec_t');
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar pago', data: $valida);
        }

        $keys = array('tipo_cambio_p','monto');
        $valida = $this->valida_numerics(keys: $keys, row: $pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar pago', data: $valida);
        }

        return true;
    }

    public function valida_docto_relacionado(mixed $docto_relacionado): bool|array
    {
        if(!is_object($docto_relacionado)){
            return $this->error->error(mensaje: 'Error docto_relacionado debe ser un obj', data: $docto_relacionado);
        }
        $keys = array('id_documento','folio','moneda_dr','equivalencia_dr','num_parcialidad','imp_saldo_ant',
            'imp_pagado','imp_saldo_insoluto','objeto_imp_dr');
        $valida = $this->valida_existencia_keys(keys: $keys, registro: $docto_relacionado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $docto_relacionado', data: $valida);
        }

        $keys = array('equivalencia_dr','num_parcialidad','imp_saldo_ant', 'imp_pagado','imp_saldo_insoluto');
        $valida = $this->valida_numerics(keys: $keys, row: $docto_relacionado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $docto_relacionado', data: $valida);
        }
        return true;
    }

    public function valida_nodo_impuesto(mixed $obj_impuesto): bool|array
    {
        if(!is_object($obj_impuesto)){
            return $this->error->error(mensaje: 'Error obj_impuesto  debe ser un objeto', data: $obj_impuesto);
        }
        $keys = array('base','impuesto','tipo_factor','tasa_o_cuota','importe');
        $valida = $this->valida_existencia_keys(keys: $keys,registro:  $obj_impuesto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar obj_impuesto', data: $valida);
        }
        $keys = array('base','tasa_o_cuota','importe');
        $valida = $this->valida_numerics(keys: $keys,row:  $obj_impuesto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar obj_impuesto', data: $valida);
        }
        return true;
    }



    public function valida_tipo_dato_pago(stdClass $pagos): bool|array
    {
        if(!isset($pagos->pagos)){
            return $this->error->error(mensaje: 'Error debe existir pagos en pagos', data: $pagos);
        }
        if(empty($pagos->pagos)){
            return $this->error->error(mensaje: 'Error  pagos en pagos esta vacio', data: $pagos);
        }
        return true;
    }

    public function valida_traslado(mixed $traslado_dr): bool|array
    {
        $keys = array('base_dr', 'impuesto_dr','tipo_factor_dr','tasa_o_cuota_dr','importe_dr');
        $valida = $this->valida_existencia_keys(keys: $keys, registro: $traslado_dr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $traslado_dr', data: $valida);
        }

        $keys = array('base_dr','tasa_o_cuota_dr','importe_dr');
        $valida = $this->valida_numerics(keys: $keys, row: $traslado_dr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $traslado_dr', data: $valida);
        }
        return true;
    }

    public function valida_traslado_p(mixed $traslado_p): bool|array
    {
        $keys = array('base_p','impuesto_p','tipo_factor_p','tasa_o_cuota_p','importe_p');
        $valida = $this->valida_existencia_keys(keys: $keys, registro: $traslado_p);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $traslado_p', data: $valida);
        }

        $keys = array('base_p','tasa_o_cuota_p','importe_p');
        $valida = $this->valida_numerics(keys: $keys, row: $traslado_p);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $traslado_p', data: $valida);
        }
        return true;
    }


}
