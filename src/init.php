<?php
namespace gamboamartin\xml_cfdi_4;
use stdClass;

class init{
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
