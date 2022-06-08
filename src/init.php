<?php
namespace gamboamartin\xml_cfdi_4;
use gamboamartin\errores\errores;
use stdClass;

class init{
    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }

    public function ajusta_montos_doc_rel(stdClass $docto_relacionado): array|stdClass
    {
        $keys = array('equivalencia_dr','imp_saldo_ant', 'imp_pagado','imp_saldo_insoluto');
        $docto_relacionado_r = $this->montos_dos_decimals(keys: $keys,obj:  $docto_relacionado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar $docto_relacionado', data: $docto_relacionado_r);
        }

        $keys = array('num_parcialidad');
        $docto_relacionado_r = $this->montos_enteros(keys: $keys,obj:  $docto_relacionado_r);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar $docto_relacionado', data: $docto_relacionado_r);
        }
        return $docto_relacionado_r;
    }

    public function ajusta_montos_traslado(stdClass $traslado_dr): array|stdClass
    {
        $keys = array('base_dr','importe_dr');
        $traslado_dr_r = (new init())->montos_dos_decimals(keys: $keys, obj: $traslado_dr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $traslado_dr', data: $traslado_dr_r);
        }

        $keys = array('tasa_o_cuota_dr');
        $traslado_dr_r = (new init())->montos_seis_decimals(keys: $keys, obj: $traslado_dr_r);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $traslado_dr', data: $traslado_dr_r);
        }
        return $traslado_dr_r;
    }

    public function ajusta_traslado_p(stdClass $traslado_p): array|stdClass
    {
        $keys = array('base_p','importe_p');
        $traslado_p_r = $this->montos_dos_decimals(keys:$keys, obj: $traslado_p);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar $traslado_p', data: $traslado_p_r);
        }

        $keys = array('tasa_o_cuota_p');
        $traslado_p_r = $this->montos_seis_decimals(keys:$keys, obj: $traslado_p_r);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar $traslado_p', data: $traslado_p_r);
        }
        return $traslado_p_r;
    }

    /**
     * Asigna los elementos al xml->cfdi
     * @param array $keys Conjunto de elementos para asignar sus valores
     * @param string $nodo_key Nodo al que se le asignara el valor de xml
     * @param stdClass $objetc Objeto de xml
     * @param xml $xml XML en ejecucion
     * @return array|stdClass
     */
    public function asigna_datos_para_nodo(array $keys, string $nodo_key, stdClass $objetc, xml $xml): array|stdClass
    {
        foreach ($keys as $key){
            if(!isset($xml->cfdi->$nodo_key)){
                return $this->error->error(mensaje: 'Error no esta inicializado $xml->cfdi->'.$nodo_key,
                    data: $xml->cfdi);
            }
            $xml->cfdi->$nodo_key->$key = $objetc->$key;
        }
        return $xml->cfdi->$nodo_key;
    }

    private function asigna_valor_unitario_concepto(stdClass $concepto): array|stdClass
    {
        $valor_unitario = (new parser())->concepto_valor_unitario(valor_unitario: $concepto->valor_unitario);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar $valor_unitario', data: $valor_unitario);
        }

        $concepto->valor_unitario = $valor_unitario;
        return $concepto;
    }

    public function inicializa_valores_comprobante(stdClass $comprobante, xml $xml){
        $xml->cfdi->comprobante->tipo_de_comprobante = $comprobante->tipo_de_comprobante;
        $xml->cfdi->comprobante->moneda = $comprobante->moneda;
        $xml->cfdi->comprobante->total = $comprobante->total;
        $xml->cfdi->comprobante->exportacion = $comprobante->exportacion;
        $xml->cfdi->comprobante->sub_total = $comprobante->sub_total;
        $xml->cfdi->comprobante->lugar_expedicion = $comprobante->lugar_expedicion;
        $xml->cfdi->comprobante->fecha = $comprobante->fecha;
        $xml->cfdi->comprobante->folio = $comprobante->folio;
        if(isset($comprobante->serie) && (string)$comprobante->serie !== ''){
            $xml->cfdi->comprobante->serie = $comprobante->serie;
        }

        return $xml->cfdi->comprobante;
    }

    private function monto_dos_decimals(float|int $value): string
    {
        $value = round($value,2);
        return number_format($value,2,'.','');
    }

    private function monto_seis_decimals(float|int $value): string
    {
        $value = round($value,6);
        return number_format($value,6,'.','');
    }

    public function montos_dos_decimals(array $keys, stdClass $obj): array|stdClass
    {
        foreach ($obj as $attr=>$value){
            if(in_array($attr, $keys, true)) {
                $obj->$attr = $this->monto_dos_decimals(value: $value);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al asignar valor', data: $obj->$attr);
                }
            }
        }
        return $obj;
    }

    public function montos_seis_decimals(array $keys, stdClass $obj): array|stdClass
    {
        foreach ($obj as $attr=>$value){
            if(in_array($attr, $keys, true)) {
                $obj->$attr = $this->monto_seis_decimals(value: $value);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al asignar valor', data: $obj->$attr);
                }
            }
        }
        return $obj;
    }

    private function monto_entero(float|int $value): string
    {
        $value = round($value);
        return number_format($value,0,'','');
    }

    public function montos_enteros(array $keys, stdClass $obj): array|stdClass
    {
        foreach ($obj as $attr=>$value){
            if(in_array($attr, $keys, true)) {
                $obj->$attr = $this->monto_entero(value: $value);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al asignar valor', data: $obj->$attr);
                }
            }
        }
        return $obj;
    }

}
