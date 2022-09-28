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

    public function complemento_a_cuenta_terceros(stdClass|array $comprobante, stdClass|array $conceptos_a,
                                                  stdClass|array $emisor, stdClass|array $impuestos,
                                                  stdClass|array $receptor): bool|array|string
    {

        $data = $this->init_base(comprobante: $comprobante,emisor:  $emisor, receptor: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $data);
        }

        $impuestos_ = $impuestos;
        if(is_array($impuestos_)){
            $impuestos_ = (object) $impuestos_;
        }

        $xml = new xml();

        $comprobante_ct = (new complementos())->comprobante_a_cuenta_terceros(comprobante: $data->comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener comprobante', data: $comprobante_ct);
        }

        $dom = $xml->cfdi_comprobante(comprobante: $comprobante_ct);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        $dom = $xml->cfdi_emisor(emisor:  $data->emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar emisor', data: $dom);
        }

        $dom = $xml->cfdi_receptor(receptor:  $data->receptor);
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

        $data = $this->init_base(comprobante: $comprobante,emisor:  $emisor, receptor: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $data);
        }

        $pagos_ = $pagos;

        if(is_array($pagos_)){
            $pagos_ = (object) $pagos_;
        }


        $keys = array('lugar_expedicion', 'folio');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $data->comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar comprobante de pago', data: $valida);
        }

        $keys = array('rfc','nombre','regimen_fiscal');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $data->emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $emisor', data: $valida);
        }

        $keys = array('rfc','nombre','domicilio_fiscal_receptor','regimen_fiscal_receptor');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $data->receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $receptor', data: $valida);
        }

        $xml = new xml();

        $comprobante_cp = (new complementos())->comprobante_complemento_pago(comprobante: $data->comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener comprobante', data: $comprobante_cp);
        }

        $dom = $xml->cfdi_comprobante(comprobante: $comprobante_cp);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        $dom = $xml->cfdi_emisor(emisor:  $data->emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar emisor', data: $dom);
        }

        $receptor->uso_cfdi = 'CP01';
        $dom = $xml->cfdi_receptor(receptor:  $data->receptor);
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

    public function complemento_nomina(stdClass|array $comprobante, stdClass|array $emisor, stdClass|array $nomina,
                                       stdClass|array $receptor): bool|array|string
    {
        $data = $this->init_base(comprobante: $comprobante,emisor:  $emisor, receptor: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $data);
        }

        $nomina_ = $nomina;
        if(is_array($nomina_)){
            $nomina_ = (object)$nomina_;
        }

        $keys = array('lugar_expedicion', 'folio','descuento');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $data->comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar comprobante de pago', data: $valida);
        }

        $keys = array('rfc','nombre','regimen_fiscal');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $data->emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $emisor', data: $valida);
        }

        $keys = array('rfc','nombre','domicilio_fiscal_receptor','regimen_fiscal_receptor');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $data->receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $receptor', data: $valida);
        }

        $xml = new xml();
        $comprobante_nm = (new complementos())->comprobante_complemento_nomina(comprobante: $data->comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener comprobante', data: $comprobante_nm);
        }

        $dom = $xml->cfdi_comprobante(comprobante: $comprobante_nm);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        $dom = $xml->cfdi_emisor(emisor:  $data->emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar emisor', data: $dom);
        }

        $receptor->uso_cfdi = 'CN01';
        $dom = $xml->cfdi_receptor(receptor:  $data->receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }

        $dom = (new complementos())->conceptos_complemento_nomina_dom(descuento: $comprobante->descuento, xml: $xml,
            valor_unitario: $comprobante->sub_total);
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

        $nodo_nominas = (new nomina())->nodo_nominas(nodo_complemento: $nodo_complemento, nomina: $nomina_, xml: $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_nominas);
        }

        $nodo_nominas_emisor = (new nomina())->nodo_nominas_emisor(nodo_nominas: $nodo_nominas, nomina: $nomina_, xml: $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_nominas_emisor);
        }

        $nodo_nominas_receptor = (new nomina())->nodo_nominas_receptor(nodo_nominas: $nodo_nominas, nomina: $nomina_, xml: $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_nominas_receptor);
        }
        
        $nodo_nominas_percepciones = (new nomina())->nodo_nominas_percepciones(nodo_nominas: $nodo_nominas,
            nomina: $nomina_, xml: $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_nominas_percepciones);
        }

        $keys = array('percepcion');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina->percepciones);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        foreach ($nomina_->percepciones->percepcion as $percep){

            if(!is_array($percep) && !is_object($percep)){
                return $this->error->error(mensaje: 'Error la percepcion debe ser un array o un objeto',
                    data: $percep);
            }

            if(is_array($percep)){
                $percep = (object)$percep;
            }

            $nodo_percepcion = (new percepcion())->nodo_percepcion(
                nodo_nominas_percepciones: $nodo_nominas_percepciones, percepcion: $percep, xml:  $xml);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar percepcion', data: $nodo_percepcion);
            }
        }

        $nodo_nominas_deducciones = (new nomina())->nodo_nominas_deducciones(nodo_nominas: $nodo_nominas,
            nomina: $nomina_, xml: $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_nominas_deducciones);
        }

        $keys = array('deduccion');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina->deducciones);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        foreach ($nomina_->deducciones->deduccion as $deduc){
            if(is_array($deduc)){
                $deduc = (object)$deduc;
            }

            $nodo_deduccion = (new deduccion())->nodo_deduccion(
                nodo_nominas_deducciones: $nodo_nominas_deducciones, deduccion: $deduc, xml:  $xml);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar deduccion', data: $nodo_deduccion);
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

        $data = $this->init_base(comprobante: $comprobante,emisor:  $emisor, receptor: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $data);
        }
        $impuestos_ = $impuestos;

        $relacionados_ = $relacionados;
        if(is_array($impuestos_)){
            $impuestos_ = (object) $impuestos_;
        }
        if(is_array($relacionados_)){
            $relacionados_ = (object) $relacionados_;
        }


        $xml = new xml();

        $comprobante_nc = (new complementos())->comprobante_nota_credito(comprobante: $data->comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener comprobante', data: $comprobante_nc);
        }

        $dom = $xml->cfdi_comprobante(comprobante: $comprobante_nc);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        $dom = $xml->cfdi_relacionados(relacionados:  $relacionados_);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar relacionados', data: $dom);
        }

        $dom = $xml->cfdi_emisor(emisor:  $data->emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar emisor', data: $dom);
        }

        $dom = $xml->cfdi_receptor(receptor:  $data->receptor);
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

    public function ingreso(stdClass|array $comprobante, array $conceptos, stdClass|array $emisor,
                            array|stdClass $impuestos, stdClass|array $receptor): bool|array|string
    {

        $data = $this->init_base(comprobante: $comprobante,emisor:  $emisor, receptor: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $data);
        }

        $impuestos_ = $impuestos;

        if(is_array($impuestos_)){
            $impuestos_ = (object) $impuestos_;
        }


        $keys = array('tipo_de_comprobante','moneda','total', 'exportacion','sub_total','lugar_expedicion',
            'folio');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar comprobante', data: $valida);
        }

        $xml = new xml();
        $dom = $xml->cfdi_comprobante(comprobante: $data->comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        $dom = $xml->cfdi_receptor(receptor:  $data->receptor);
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

    /**
     * Inicializa los elementos basicos de un xml
     * @param stdClass|array $comprobante Datos del comprobante version fecha etc
     * @param stdClass|array $emisor Datos del emisor del cfdi razon social rfc etc
     * @param stdClass|array $receptor Datos del receptor de cfdi rfc razon social etc
     * @return stdClass
     * @version 1.4.0
     */
    private function init_base(stdClass|array $comprobante, stdClass|array $emisor, stdClass|array $receptor): stdClass
    {
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

        $data = new stdClass();
        $data->emisor = $emisor_;
        $data->comprobante = $comprobante_;
        $data->receptor = $receptor_;

        return $data;

    }
}
