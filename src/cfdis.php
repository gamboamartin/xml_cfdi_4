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

        $comprobante_ = $comprobante;
        $emisor_ = $emisor;
        $receptor_ = $receptor;

        $impuestos_ = $impuestos;

        if(is_array($comprobante_)){
            $comprobante_ = (object) $comprobante_;
        }
        if(is_array($emisor_)){
            $emisor_ = (object) $emisor_;
        }
        if(is_array($receptor_)){
            $receptor_ = (object) $receptor_;
        }
        if(is_array($impuestos_)){
            $impuestos_ = (object) $impuestos_;
        }

        $xml = new xml();

        $comprobante_cp = (new complementos())->comprobante_a_cuenta_terceros(comprobante: $comprobante_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener comprobante', data: $comprobante_cp);
        }

        $dom = $xml->cfdi_comprobante(comprobante: $comprobante_cp);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        $dom = $xml->cfdi_emisor(emisor:  $emisor_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar emisor', data: $dom);
        }

        $dom = $xml->cfdi_receptor(receptor:  $receptor_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }

        $dom = $xml->cfdi_conceptos(conceptos: $conceptos_a);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar conceptos', data: $dom);
        }

        $dom = $xml->cfdi_impuestos(impuestos: $impuestos_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar impuestos', data: $dom);
        }

        return $xml->dom->saveXML();
    }

    public function complemento_pago(stdClass|array $comprobante, stdClass|array $emisor, stdClass|array $pagos,
                                     stdClass|array $receptor): bool|array|string
    {

        $comprobante_ = $comprobante;
        $emisor_ = $emisor;
        $receptor_ = $receptor;
        $pagos_ = $pagos;

        if(is_array($comprobante_)){
            $comprobante_ = (object) $comprobante_;
        }
        if(is_array($emisor_)){
            $emisor_ = (object) $emisor_;
        }
        if(is_array($receptor_)){
            $receptor_ = (object) $receptor_;
        }

        if(is_array($pagos_)){
            $pagos_ = (object) $pagos_;
        }


        $keys = array('lugar_expedicion', 'folio');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $comprobante_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar comprobante de pago', data: $valida);
        }

        $keys = array('rfc','nombre','regimen_fiscal');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $emisor_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $emisor', data: $valida);
        }

        $keys = array('rfc','nombre','domicilio_fiscal_receptor','regimen_fiscal_receptor');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $receptor_);
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


        $nodo_totales = (new pago())->nodo_totales(nodo_pagos: $nodo_pagos, pagos: $pagos_,xml:  $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_totales);
        }

        $valida = $this->valida->valida_tipo_dato_pago(pagos: $pagos_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar pagos', data: $valida);
        }

        foreach($pagos_->pagos as $pago){

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
        $comprobante_ = $comprobante;
        $emisor_ = $emisor;
        $receptor_ = $receptor;
        $impuestos_ = $impuestos;
        
        $relacionados_ = $relacionados;


        if(is_array($comprobante_)){
            $comprobante_ = (object) $comprobante_;
        }
        if(is_array($emisor_)){
            $emisor_ = (object) $emisor_;
        }
        if(is_array($receptor_)){
            $receptor_ = (object) $receptor_;
        }

        if(is_array($impuestos_)){
            $impuestos_ = (object) $impuestos_;
        }


        if(is_array($relacionados_)){
            $relacionados_ = (object) $relacionados_;
        }



        $xml = new xml();

        $comprobante_cp = (new complementos())->comprobante_nota_credito(comprobante: $comprobante_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener comprobante', data: $comprobante_cp);
        }

        $dom = $xml->cfdi_comprobante(comprobante: $comprobante_cp);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        $dom = $xml->cfdi_relacionados(relacionados:  $relacionados_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar relacionados', data: $dom);
        }

        $dom = $xml->cfdi_emisor(emisor:  $emisor_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar emisor', data: $dom);
        }

        $dom = $xml->cfdi_receptor(receptor:  $receptor_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }

        $dom = $xml->cfdi_conceptos(conceptos: $conceptos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar conceptos', data: $dom);
        }

        $dom = $xml->cfdi_impuestos(impuestos: $impuestos_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar impuestos', data: $dom);
        }

        return $xml->dom->saveXML();
    }

    public function ingreso(stdClass|array $comprobante, stdClass|array $emisor, stdClass|array $receptor){
        $comprobante_ = $comprobante;
        $emisor_ = $emisor;
        $receptor_ = $receptor;


        if(is_array($comprobante_)){
            $comprobante_ = (object) $comprobante_;
        }
        if(is_array($emisor_)){
            $emisor_ = (object) $emisor_;
        }
        if(is_array($receptor_)){
            $receptor_ = (object) $receptor_;
        }



    }
}
