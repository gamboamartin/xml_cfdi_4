<?php
namespace gamboamartin\xml_cfdi_4;
use DOMElement;
use DOMException;
use gamboamartin\errores\errores;
use stdClass;
use Throwable;

class otro_pago{
    private validacion $valida;
    private errores $error;
    public function __construct(){
        $this->valida = new validacion();
        $this->error = new errores();
    }


    public function nodo_otro_pago(DOMElement $nodo_nominas_otros_pagos, stdClass $otro_pago, xml $xml): array|DOMElement
    {
        try {
            $nodo_otro_pago = $xml->dom->createElement('nomina12:OtroPago');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento nomina12:OtrosPagos', data: $e);
        }

        $nodo_nominas_otros_pagos->appendChild($nodo_otro_pago);

        $keys = array('subsidio_causado');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $otro_pago);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar otro pago subsidio', data: $valida);
        }

        $nodo_otro_pago->setAttribute('TipoOtroPago', $otro_pago->tipo_otro_pago);
        $nodo_otro_pago->setAttribute('Clave', $otro_pago->clave);
        $nodo_otro_pago->setAttribute('Concepto', $otro_pago->concepto);
        $nodo_otro_pago->setAttribute('Importe', $otro_pago->importe);

        if($otro_pago->es_subsidio){



            try {
                $nodo_otro_pago_sub = $xml->dom->createElement('nomina12:SubsidioAlEmpleo');
            }
            catch (Throwable $e){
                return $this->error->error(mensaje: 'Error al crear el elemento nomina12:SubsidioAlEmpleo', data: $e);
            }
            $nodo_otro_pago->appendChild($nodo_otro_pago_sub);
            $nodo_otro_pago_sub->setAttribute('SubsidioCausado',$otro_pago->subsidio_causado );
        }

        return $nodo_nominas_otros_pagos;
    }

}
