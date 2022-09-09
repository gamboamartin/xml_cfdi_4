<?php
namespace gamboamartin\xml_cfdi_4;
use DOMElement;
use DOMException;
use gamboamartin\errores\errores;
use stdClass;
use Throwable;

class nomina{
    private validacion $valida;
    private errores $error;
    public function __construct(){
        $this->valida = new validacion();
        $this->error = new errores();
    }

    public function nodo_nominas(DOMElement $nodo_complemento, stdClass $nomina, xml $xml): bool|DOMElement|array
    {

        try {
            $nodo_nominas = $xml->dom->createElementNS($xml->cfdi->comprobante->xmlns_nomina12, 'nomina12:Nomina');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento nomina12:Nomina', data: $e);
        }

        $nodo_complemento->appendChild($nodo_nominas);

        $keys = array('tipo_nomina','fecha_pago','fecha_inicial_pago','fecha_final_pago','num_dias_pagados',
            'total_percepciones','total_deducciones');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }


        $nodo_nominas->setAttribute('xmlns:nomina12', $xml->cfdi->comprobante->xmlns_nomina12);
        $nodo_nominas->setAttribute('Version', '1.2');
        $nodo_nominas->setAttribute('TipoNomina', $nomina->tipo_nomina);
        $nodo_nominas->setAttribute('FechaPago', $nomina->fecha_pago);
        $nodo_nominas->setAttribute('FechaInicialPago',$nomina->fecha_inicial_pago);
        $nodo_nominas->setAttribute('FechaFinalPago',$nomina->fecha_final_pago);
        $nodo_nominas->setAttribute('NumDiasPagados',$nomina->num_dias_pagados);
        $nodo_nominas->setAttribute('TotalPercepciones',$nomina->total_percepciones);
        $nodo_nominas->setAttribute('TotalDeducciones',$nomina->total_deducciones);



        return $nodo_nominas;
    }

    public function nodo_nominas_emisor(DOMElement $nodo_nominas, stdClass $nomina, xml $xml): bool|DOMElement|array
    {

        try {
            $nodo_nomina_emisor = $xml->dom->createElement('nomina12:Emisor');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento nomina12:Emisor', data: $e);
        }

        $keys = array('emisor');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $keys = array('registro_patronal','rfc_patron_origen');

        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina->emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $nodo_nominas->appendChild($nodo_nomina_emisor);

        $nodo_nomina_emisor->setAttribute('RegistroPatronal', $nomina->emisor->registro_patronal);
        $nodo_nomina_emisor->setAttribute('RfcPatronOrigen', $nomina->emisor->rfc_patron_origen);



        return $nodo_nomina_emisor;
    }


}
