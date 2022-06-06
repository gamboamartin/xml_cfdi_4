<?php
namespace gamboamartin\xml_cfdi_4;
use gamboamartin\errores\errores;
use stdClass;

class init{
    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }
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

        return $xml->cfdi->comprobante;
    }

}
