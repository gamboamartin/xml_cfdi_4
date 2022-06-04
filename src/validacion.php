<?php
namespace gamboamartin\xml_cfdi_4;

use stdClass;

class validacion extends \gamboamartin\validacion\validacion{
    /**
     * Valida parametros de un complemento de pago
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
        return true;
    }
}
