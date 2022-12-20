<?php
namespace gamboamartin\xml_cfdi_4;
use DOMElement;
use DOMException;
use gamboamartin\errores\errores;
use stdClass;
use Throwable;

class deduccion{
    private validacion $valida;
    private errores $error;
    public function __construct(){
        $this->valida = new validacion();
        $this->error = new errores();
    }


    public function nodo_deduccion(DOMElement $nodo_nominas_deducciones, stdClass $deduccion, xml $xml): array|DOMElement
    {
        try {
            $nodo_deduccion = $xml->dom->createElement('nomina12:Deduccion');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento deduccion20:deduccion', data: $e);
        }

        $nodo_nominas_deducciones->appendChild($nodo_deduccion);

        $keys = array('tipo_deduccion','clave','concepto','importe');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $deduccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $nodo_deduccion->setAttribute('TipoDeduccion', $deduccion->tipo_deduccion);
        $nodo_deduccion->setAttribute('Clave', $deduccion->clave);
        $nodo_deduccion->setAttribute('Concepto', $deduccion->concepto);
        $nodo_deduccion->setAttribute('Importe', $deduccion->importe);

        return $nodo_nominas_deducciones;
    }

}
