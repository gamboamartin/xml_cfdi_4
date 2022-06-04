<?php
namespace gamboamartin\xml_cfdi_4;

use stdClass;

class validacion extends \gamboamartin\validacion\validacion{
    public function complemento_pago_comprobante(stdClass $comprobante, xml $xml): bool|array
    {
        if((float)$xml->cfdi->comprobante->total!==0.0){
            return $this->error->error(mensaje:'Error cuando tipo_de_comprobante sea P el total debe ser 0',
                data: $comprobante);
        }
        return true;
    }
}
