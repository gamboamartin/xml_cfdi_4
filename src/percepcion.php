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


    /**
     * Genera el nodo percepcion de un xml
     * @param DOMElement $nodo_nominas_percepciones  Nodo de nomina inicializado
     * @param stdClass $percepcion Percepcion a integrar
     * @param xml $xml Xml inicializado
     * @return array|DOMElement
     * @version 1.20.0
     */
    public function nodo_percepcion(DOMElement $nodo_nominas_percepciones, stdClass $percepcion, xml $xml): array|DOMElement
    {
        try {
            $nodo_percepcion = $xml->dom->createElement('nomina12:Percepcion');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento percepcion20:percepcion', data: $e);
        }

        $nodo_nominas_percepciones->appendChild($nodo_percepcion);

        $keys = array('tipo_percepcion','clave','concepto','importe_gravado','importe_exento');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $percepcion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $keys = array('tipo_percepcion');
        $valida = $this->valida->valida_codigos_int_0_3_numbers(keys: $keys,registro: $percepcion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar percepcion', data: $valida);
        }

        $keys = array('importe_gravado','importe_exento');
        $valida = $this->valida->valida_double_mayores_igual_0(keys: $keys,registro: $percepcion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar percepcion', data: $valida);
        }

        $total_percepciones = round($percepcion->importe_gravado, 2) +  round($percepcion->importe_exento, 2);
        $total_percepciones = round($total_percepciones,2);
        if($total_percepciones<=0.0){
            return $this->error->error(mensaje: 'Error algun importe debe ser mayor a 0', data: $valida);
        }

        $nodo_percepcion->setAttribute('TipoPercepcion', $percepcion->tipo_percepcion);
        $nodo_percepcion->setAttribute('Clave', $percepcion->clave);
        $nodo_percepcion->setAttribute('Concepto', $percepcion->concepto);
        $nodo_percepcion->setAttribute('ImporteGravado', $percepcion->importe_gravado);
        $nodo_percepcion->setAttribute('ImporteExento', $percepcion->importe_exento);

        return $nodo_nominas_percepciones;
    }

    public function nodo_jubilacion_pension_retiro(DOMElement $nodo_nominas_percepciones, stdClass $jubilacion, xml $xml): array|DOMElement
    {
        try {
            $nodo_jubilacion = $xml->dom->createElement('nomina12:JubilacionPensionRetiro');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento percepcion20:percepcion', data: $e);
        }

        $nodo_nominas_percepciones->appendChild($nodo_jubilacion);

        $total_percepciones = round($jubilacion->importe_gravado, 2) +  round($jubilacion->importe_exento, 2);
        $total_percepciones = round($total_percepciones,2);
        if($total_percepciones<=0.0){
            return $this->error->error(mensaje: 'Error algun importe debe ser mayor a 0', data: $total_percepciones);
        }

        $nodo_jubilacion->setAttribute('IngresoNoAcumulable',$total_percepciones);
        $nodo_jubilacion->setAttribute('IngresoAcumulable', '0.00');
        $nodo_jubilacion->setAttribute('MontoDiario', $total_percepciones);
        $nodo_jubilacion->setAttribute('TotalParcialidad', $total_percepciones);

        return $nodo_nominas_percepciones;
    }

}
