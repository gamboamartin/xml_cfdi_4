<?php
namespace gamboamartin\xml_cfdi_4;
use gamboamartin\errores\errores;
use stdClass;

class complementos{
    private errores  $error;
    public function __construct(){
        $this->error = new errores();
    }
    public function aplica_complemento_cfdi_comprobante(stdClass $comprobante, xml $xml): xml|array
    {
        if($xml->cfdi->comprobante->tipo_de_comprobante === 'P'){
            $cfdi_comprobante_pago = (new dom_xml())->comprobante_pago(comprobante: $comprobante,xml:  $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar dom pago', data: $cfdi_comprobante_pago);
            }
        }
        return $xml;
    }
}
