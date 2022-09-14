<?php
namespace gamboamartin\xml_cfdi_4;
use DOMElement;
use DOMException;
use gamboamartin\errores\errores;
use stdClass;
use Throwable;

class percepcion{
    private validacion $valida;
    private errores $error;
    public function __construct(){
        $this->valida = new validacion();
        $this->error = new errores();
    }


    public function nodo_percepcion(DOMElement $nodo_nominas_percepciones, stdClass $percepcion, xml $xml): array|DOMElement
    {
        try {
            $nodo_percepcion = $xml->dom->createElement('nomina12:Percepcion');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento percepcion20:percepcion', data: $e);
        }

        $nodo_nominas_percepciones->appendChild($nodo_percepcion);

        $nodo_percepcion->setAttribute('TipoPercepcion', $percepcion->tipo_percepcion);
        $nodo_percepcion->setAttribute('Clave', $percepcion->clave);
        $nodo_percepcion->setAttribute('Concepto', $percepcion->concepto);
        $nodo_percepcion->setAttribute('ImporteGravado', $percepcion->importe_gravado);
        $nodo_percepcion->setAttribute('ImporteExento', $percepcion->importe_exento);

        return $nodo_nominas_percepciones;
    }

}
