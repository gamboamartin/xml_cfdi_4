<?php
namespace gamboamartin\xml_cfdi_4;
use DOMException;
use gamboamartin\errores\errores;
use stdClass;

class cfdis{
    private errores $error;
    private validacion $valida;

    public function __construct(){
        $this->error = new errores();
        $this->valida = new validacion();

    }

    /**
     * @throws DOMException
     */
    public function complemento_a_cuenta_terceros(stdClass|array $comprobante, stdClass|array $conceptos_a,
                                                  stdClass|array $emisor, stdClass|array $impuestos,
                                                  stdClass|array $receptor)
    {
        if(is_array($comprobante)){
            $comprobante = (object) $comprobante;
        }
        if(is_array($emisor)){
            $emisor = (object) $emisor;
        }
        if(is_array($impuestos)){
            $impuestos = (object) $impuestos;
        }
        if(is_array($receptor)){
            $receptor = (object) $receptor;
        }

        $xml = new xml();

        $comprobante_cp = (new complementos())->comprobante_a_cuenta_terceros(comprobante: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener comprobante', data: $comprobante_cp);
        }

        $dom = $xml->cfdi_comprobante(comprobante: $comprobante_cp);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        $dom = $xml->cfdi_emisor(emisor:  $emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar emisor', data: $dom);
        }

        $dom = $xml->cfdi_receptor(receptor:  $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }

        $dom = $xml->cfdi_conceptos(conceptos: $conceptos_a);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar conceptos', data: $dom);
        }

        $dom = $xml->cfdi_impuestos(impuestos: $impuestos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar impuestos', data: $dom);
        }

        return $xml->dom->saveXML();
    }

    public function complemento_pago(stdClass|array $comprobante, stdClass|array $emisor, stdClass|array $pagos,
                                     stdClass|array $receptor): bool|array|string
    {

        $comprobante_ = $comprobante;

        if(is_array($comprobante_)){
            $comprobante_ = (object) $comprobante_;
        }
        if(is_array($emisor)){
            $emisor = (object) $emisor;
        }
        if(is_array($receptor)){
            $receptor = (object) $receptor;
        }

        if(is_array($pagos)){
            $pagos = (object) $pagos;
        }


        $keys = array('lugar_expedicion', 'folio');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $comprobante_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar comprobante de pago', data: $valida);
        }

        $keys = array('rfc','nombre','regimen_fiscal');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $emisor', data: $valida);
        }

        $keys = array('rfc','nombre','domicilio_fiscal_receptor','regimen_fiscal_receptor');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $receptor', data: $valida);
        }

        $xml = new xml();

        $comprobante_cp = (new complementos())->comprobante_complemento_pago(comprobante: $comprobante_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener comprobante', data: $comprobante_cp);
        }

        $dom = $xml->cfdi_comprobante(comprobante: $comprobante_cp);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        $dom = $xml->cfdi_emisor(emisor:  $emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar emisor', data: $dom);
        }

        $receptor->uso_cfdi = 'CP01';
        $dom = $xml->cfdi_receptor(receptor:  $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }


        $dom = (new complementos())->conceptos_complemento_pago_dom(xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }

        /**
         * COMPLEMENTO
         */

        $nodo_complemento = (new complementos())->nodo_complemento(xml: $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_complemento);
        }


        $nodo_pagos = (new pago())->nodo_pagos(nodo_complemento: $nodo_complemento, xml: $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_pagos);
        }


        $nodo_totales = (new pago())->nodo_totales(nodo_pagos: $nodo_pagos, pagos: $pagos,xml:  $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_totales);
        }

        $valida = $this->valida->valida_tipo_dato_pago(pagos: $pagos);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar pagos', data: $valida);
        }

        foreach($pagos->pagos as $pago){

            if(is_array($pago)){
                $pago = (object)$pago;
            }

            $nodo_pago = (new pago())->nodo_pago(nodo_pagos: $nodo_pagos, pago: $pago,xml:  $xml);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar pago', data: $nodo_pago);
            }

            $nodo_docto_relacionado = (new pago())->nodo_doctos_rel(nodo_pago: $nodo_pago,pago:  $pago,xml:  $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar $docto_relacionado', data: $nodo_docto_relacionado);
            }

            $nodo_impuestos_p = (new pago())->nodo_impuestos_p(nodo_pago: $nodo_pago, pago: $pago,xml:  $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al ajustar $nodo_traslados_p', data: $nodo_impuestos_p);
            }


        }

        return $xml->dom->saveXML();
    }

    /**
     * @throws DOMException
     */
    public function  completo_nota_credito(stdClass|array $comprobante, stdClass|array $conceptos,
                                           stdClass|array $emisor, stdClass|array $impuestos, stdClass|array $receptor,
                                           stdClass|array $relacionados): bool|array|string
    {
        if(is_array($comprobante)){
            $comprobante = (object) $comprobante;
        }
        if(is_array($emisor)){
            $emisor = (object) $emisor;
        }
        if(is_array($impuestos)){
            $impuestos = (object) $impuestos;
        }
        if(is_array($receptor)){
            $receptor = (object) $receptor;
        }
        if(is_array($relacionados)){
            $relacionados = (object) $relacionados;
        }

        $xml = new xml();

        $comprobante_cp = (new complementos())->comprobante_nota_credito(comprobante: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener comprobante', data: $comprobante_cp);
        }

        $dom = $xml->cfdi_comprobante(comprobante: $comprobante_cp);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        $dom = $xml->cfdi_relacionados(relacionados:  $relacionados);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar relacionados', data: $dom);
        }

        $dom = $xml->cfdi_emisor(emisor:  $emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar emisor', data: $dom);
        }

        $dom = $xml->cfdi_receptor(receptor:  $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }

        $dom = $xml->cfdi_conceptos(conceptos: $conceptos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar conceptos', data: $dom);
        }

        $dom = $xml->cfdi_impuestos(impuestos: $impuestos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar impuestos', data: $dom);
        }

        return $xml->dom->saveXML();
    }
}
