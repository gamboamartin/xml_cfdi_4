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

    public function valida_data_concepto(mixed $concepto): bool|array
    {
        $keys = array('clave_prod_serv','cantidad','importe','clave_unidad','descripcion','no_identificacion',
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

    /**
     * Verifica que los datos enviados de un traslado sean correctos en tipo de dato
     * @version 0.5.0
     * @param mixed $traslado Obj de tipo traslado
     * @return bool|array
     */
    public function valida_nodo_traslado(mixed $traslado): bool|array
    {
        if(!is_object($traslado)){
            return $this->error->error(mensaje: 'Error traslado en $traslado debe ser un objeto', data: $traslado);
        }
        $keys = array('base','impuesto','tipo_factor','tasa_o_cuota','importe');
        $valida = $this->valida_existencia_keys(keys: $keys,registro:  $traslado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar traspaso', data: $valida);
        }
        $keys = array('base','tasa_o_cuota','importe');
        $valida = $this->valida_numerics(keys: $keys,row:  $traslado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar traspaso', data: $valida);
        }
        return true;
    }
}
