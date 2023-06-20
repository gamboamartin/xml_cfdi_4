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

    private function add_concepto(stdClass $concepto, array $output){
        $output['Comprobante']['Conceptos']= array();
        $concepto_json = array();
        foreach ($concepto as $attr=>$value) {

            $concepto_json = $this->concepto_para_json(attr: $attr,concepto_json:  $concepto_json, value: $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar concepto_json',data:  $concepto_json);
            }

        }
        $output['Comprobante']['Conceptos'][] = $concepto_json;
        return $output;
    }

    /**
     * Genera el complemento de cuenta a terceros
     * @param stdClass|array $comprobante
     * @param stdClass|array $conceptos_a
     * @param stdClass|array $emisor
     * @param stdClass|array $impuestos
     * @param stdClass|array $receptor
     * @return bool|array|string
     */
    final public function complemento_a_cuenta_terceros(stdClass|array $comprobante, stdClass|array $conceptos_a,
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
                                       stdClass|array $receptor, stdClass|array $relacionados = array()): bool|array|string
    {
        $data = $this->init_base(comprobante: $comprobante,emisor:  $emisor, receptor: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $data);
        }

        $relacionados_ = $relacionados;

        if(is_array($relacionados_)){
            $relacionados_ = (object) $relacionados_;
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

        if(count($relacionados)> 0) {
            $dom = $xml->cfdi_relacionados(relacionados: $relacionados_);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar relacionados', data: $dom);
            }
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

        $keys = array('otro_pago');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina->otros_pagos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $nodo_nominas_otros_pagos = (new nomina())->nodo_nominas_otros_pagos(nodo_nominas: $nodo_nominas,
            nomina: $nomina_, xml: $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_nominas_otros_pagos);
        }

        foreach ($nomina_->otros_pagos->otro_pago as $op){
            if(is_array($op)){
                $op = (object)$op;
            }

            $keys = array('tipo_otro_pago','clave','concepto','importe','es_subsidio');
            $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $op);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar otro pago', data: $valida);
            }

            $nodo_otro_pago = (new otro_pago())->nodo_otro_pago(
                nodo_nominas_otros_pagos: $nodo_nominas_otros_pagos, otro_pago: $op, xml:  $xml);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar deduccion', data: $nodo_otro_pago);
            }
        }

        return $xml->dom->saveXML();
    }

    public function complemento_nomina_v33(stdClass|array $comprobante, stdClass|array $emisor, stdClass|array $nomina,
                                       stdClass|array $receptor, stdClass|array $relacionados = array()): bool|array|string
    {
        $data = $this->init_base(comprobante: $comprobante,emisor:  $emisor, receptor: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $data);
        }

        $relacionados_ = $relacionados;

        if(is_array($relacionados_)){
            $relacionados_ = (object) $relacionados_;
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

        $dom = $xml->cfdi_comprobante_v33(comprobante: $comprobante_nm);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        if(count($relacionados)> 0) {
            $dom = $xml->cfdi_relacionados(relacionados: $relacionados_);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar relacionados', data: $dom);
            }
        }


        $dom = $xml->cfdi_emisor(emisor:  $data->emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar emisor', data: $dom);
        }

        $receptor->uso_cfdi = 'CN01';
        $dom = $xml->cfdi_receptor_v33(receptor:  $data->receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }

        $dom = (new complementos())->conceptos_complemento_nomina_dom(descuento: $comprobante->descuento, xml: $xml,
            valor_unitario: $comprobante->sub_total);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }



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

        $keys = array('otro_pago');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $nomina->otros_pagos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $nodo_nominas_otros_pagos = (new nomina())->nodo_nominas_otros_pagos(nodo_nominas: $nodo_nominas,
            nomina: $nomina_, xml: $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_nominas_otros_pagos);
        }

        foreach ($nomina_->otros_pagos->otro_pago as $op){
            if(is_array($op)){
                $op = (object)$op;
            }

            $keys = array('tipo_otro_pago','clave','concepto','importe','es_subsidio');
            $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $op);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar otro pago', data: $valida);
            }

            $nodo_otro_pago = (new otro_pago())->nodo_otro_pago(
                nodo_nominas_otros_pagos: $nodo_nominas_otros_pagos, otro_pago: $op, xml:  $xml);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar deduccion', data: $nodo_otro_pago);
            }
        }

        return $xml->dom->saveXML();
    }

    public function complemento_nomina_haberes(stdClass|array $comprobante, stdClass|array $emisor, stdClass|array $nomina,
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

        if(isset($data->comprobante->descuento)){
            unset($data->comprobante->descuento);
        }

        if(isset($nomina_->receptor->tipo_contrato )){
            $nomina_->receptor->tipo_contrato = '99';
        }
        if(isset($nomina_->receptor->tipo_regimen )){
            $nomina_->receptor->tipo_regimen = '99';
        }

        if(isset($nomina_->receptor->periodicidad_pago )){
            $nomina_->receptor->periodicidad_pago = '99';
        }

        if(isset($nomina_->tipo_nomina )){
            $nomina_->tipo_nomina = 'E';
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

        $dom = (new complementos())->conceptos_complemento_nomina_dom_haberes(xml: $xml,
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

        $nodo_nominas = (new nomina())->nodo_nominas_haberes(nodo_complemento: $nodo_complemento, nomina: $nomina_, xml: $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_nominas);
        }

        $nodo_nominas_receptor = (new nomina())->nodo_nominas_receptor(nodo_nominas: $nodo_nominas, nomina: $nomina_, xml: $xml);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar nodo', data: $nodo_nominas_receptor);
        }

        $nodo_nominas_percepciones = (new nomina())->nodo_nominas_percepciones_haberes(nodo_nominas: $nodo_nominas,
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

            $nodo_jubilacion = (new percepcion())->nodo_jubilacion_pension_retiro(
                nodo_nominas_percepciones: $nodo_nominas_percepciones, jubilacion: $percep, xml:  $xml);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar jubilacion', data: $nodo_jubilacion);
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

    private function concepto_para_json(string $attr, array $concepto_json, string|array $value): array
    {
        $keys_data_concepto = $this->keys_data_concepto();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener keys_data_concepto',data:  $keys_data_concepto);
        }

        if(!is_array($value)) {
            $key_output = $this->key_output(atributo: $attr, keys_data: $keys_data_concepto);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener key_output',data:  $key_output);
            }
            $concepto_json[$key_output] = $value;
        }
        if(is_array($value)){
            $concepto_json = $this->data_impuesto_concepto(attr: $attr,concepto_json:  $concepto_json,impuestos:  $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar impuesto concepto_json',data:  $concepto_json);
            }
        }
        return $concepto_json;
    }

    private function data_impuesto_concepto(string $attr, array $concepto_json, array $impuestos){

        if($attr === 'impuestos') {
            foreach ($impuestos as $impuesto) {
                $concepto_json = $this->impuestos_concepto(attr: $attr, concepto_json: $concepto_json, impuesto: $impuesto);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al integrar impuesto concepto_json', data: $concepto_json);
                }
            }
        }
        return $concepto_json;
    }

    private function genera_impuestos(string $attr, array $concepto_json, array $imps, string $tipo_impuestos){
        foreach ($imps as $imp){
            $concepto_json = $this->integra_impuesto(attr:$attr, concepto_json: $concepto_json, imp: $imp,tipo_impuestos:  $tipo_impuestos);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar impuesto key_output',data:  $concepto_json);
            }
        }
        return $concepto_json;
    }

    private function imp_json(string $attr_imp, array $imp_json, string $value_imp){
        $keys_data_impuesto = $this->keys_data_impuesto();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener keys_data_impuesto',data:  $keys_data_impuesto);
        }

        $key_output = $this->key_output(atributo: $attr_imp, keys_data: $keys_data_impuesto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key_output',data:  $key_output);
        }
        $imp_json[$key_output] = $value_imp;
        return $imp_json;
    }

    private function imps_json(stdClass $imp){
        $imp_json = array();
        foreach ($imp as $attr_imp=>$value_imp){
            $imp_json = $this->imp_json(attr_imp: $attr_imp,imp_json:  $imp_json,value_imp:  $value_imp);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar value',data:  $imp_json);
            }
        }
        return $imp_json;
    }

    private function impuesto_concepto(string $attr, array $concepto_json, stdClass $impuesto, string $tipo_impuestos){
        $attr_imp = trim($tipo_impuestos);
        $attr_imp = strtolower($attr_imp);
        if(isset($impuesto->$attr_imp)){
            $concepto_json = $this->impuestos_json(attr: $attr,concepto_json:  $concepto_json,impuesto:  $impuesto, tipo_impuestos: $attr_imp);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar impuesto concepto_json',data:  $concepto_json);
            }
        }
        return $concepto_json;
    }

    private function impuestos(stdClass $impuestos_){
        $impuestos_ = $this->impuestos_trasladados(impuestos_: $impuestos_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar traslados', data: $impuestos_);
        }

        $impuestos_ = $this->impuestos_retenidos(impuestos_: $impuestos_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar retenciones', data: $impuestos_);
        }
        return $impuestos_;
    }

    private function impuestos_concepto(string $attr, array $concepto_json, stdClass $impuesto){
        $tipos_impuestos[] = 'traslados';
        $tipos_impuestos[] = 'retenciones';
        foreach ($tipos_impuestos as $tipo_impuesto) {
            $concepto_json = $this->impuesto_concepto(attr: $attr, concepto_json: $concepto_json, impuesto: $impuesto, tipo_impuestos: $tipo_impuesto);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar impuesto concepto_json', data: $concepto_json);
            }
        }
        return $concepto_json;
    }

    private function impuestos_json(string $attr, array $concepto_json, stdClass $impuesto, string $tipo_impuestos){
        $attr_imp = trim($tipo_impuestos);
        $attr_imp = strtolower($attr_imp);

        $values_attr = trim($tipo_impuestos);
        $values_attr = ucwords($values_attr);

        $imps = $impuesto->$attr_imp;
        $tipo_impuestos = $values_attr;

        $concepto_json = $this->genera_impuestos(attr: $attr,concepto_json:  $concepto_json,imps:  $imps,tipo_impuestos:  $tipo_impuestos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar impuesto key_output',data:  $concepto_json);
        }
        return $concepto_json;
    }

    private function impuestos_retenidos(stdClass $impuestos_){
        if(isset($impuestos_->total_impuestos_retenidos)){
            $impuestos_ = $this->reasigna_retenciones(impuestos_: $impuestos_);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar retenciones', data: $impuestos_);
            }
        }
        return $impuestos_;
    }

    private function impuestos_trasladados(stdClass $impuestos_){
        if(isset($impuestos_->total_impuestos_trasladados)){
            $impuestos_ = $this->reasigna_traslados(impuestos_: $impuestos_);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar traslados', data: $impuestos_);
            }
        }
        return $impuestos_;
    }

    public function ingreso(stdClass|array $comprobante, array $conceptos, stdClass|array $emisor,
                            array|stdClass $impuestos, stdClass|array $receptor, stdClass|array $relacionados = array(),
                            string $tipo = 'xml'): bool|array|string
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
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $dom);
        }

        $dom = $xml->cfdi_emisor(emisor: $data->emisor);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar emisor', data: $dom);
        }

        $dom = $xml->cfdi_receptor(receptor: $data->receptor);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }

        $dom = $xml->cfdi_conceptos(conceptos: $conceptos);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar conceptos', data: $dom);
        }

        $impuestos_ = $this->impuestos(impuestos_: $impuestos_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar traslados', data: $impuestos_);
        }


        $dom = $xml->cfdi_impuestos(impuestos: $impuestos_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar impuestos', data: $dom);
        }
        return $xml->dom->saveXML();

    }

    public function ingreso_json(stdClass|array $comprobante, array $conceptos, stdClass|array $emisor,
                            array|stdClass $impuestos, stdClass|array $receptor, array $complemento = array(),
                                 stdClass|array $relacionados = array()): bool|array|string
    {

        $data = $this->init_base(comprobante: $comprobante,emisor:  $emisor, receptor: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $data);
        }
        if(!isset($data->comprobante->tipo_de_comprobante)){
            $data->comprobante->tipo_de_comprobante = 'I';
        }


        $impuestos_ = $impuestos;

        if(is_array($impuestos_)){
            $impuestos_ = (object) $impuestos_;
        }


        $keys = array('tipo_de_comprobante','moneda','total', 'exportacion','sub_total','lugar_expedicion',
            'folio','no_certificado');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar comprobante', data: $valida);
        }


        $xml = new xml();
        $json = array();
        $json = $xml->cfdi_comprobante_json(comprobante: $data->comprobante, json: $json);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar comprobante', data: $json);
        }

        $json = $xml->cfdi_relacionados_json($relacionados, $json);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar relaciones', data: $json);
        }

        $json = $xml->cfdi_emisor_json(emisor: $data->emisor, json: $json);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar emisor', data: $json);
        }

        $json = $xml->cfdi_receptor_json(json: $json, receptor: $data->receptor);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar receptor', data: $json);
        }

        $json = $xml->cfdi_conceptos_json(conceptos: $conceptos, json: $json);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar conceptos', data: $json);
        }

        if(count($complemento) > 0) {
            $json['Comprobante']['Complemento'] = $complemento;
        }

        $impuestos_ = $this->impuestos(impuestos_: $impuestos_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar traslados', data: $impuestos_);
        }

        $json = $xml->cfdi_impuestos_json(impuestos: $impuestos_, json: $json);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar impuestos', data: $json);
        }
        return json_encode($json);

    }

    /**
     * Inicializa los elementos basicos de un xml
     * @param stdClass|array $comprobante Datos del comprobante version fecha etc
     * @param stdClass|array $emisor Datos del emisor del cfdi razon social rfc etc
     * @param stdClass|array $receptor Datos del receptor de cfdi rfc razon social etc
     * @param stdClass|array $relacionados
     * @return stdClass
     * @version 1.4.0
     */
    private function init_base(stdClass|array $comprobante, stdClass|array $emisor, stdClass|array $receptor,
                               stdClass|array $relacionados = array()): stdClass
    {
        $comprobante_ = $comprobante;
        $emisor_ = $emisor;
        $receptor_ = $receptor;
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
        if(is_array($relacionados_)){
            $relacionados_ = (object) $relacionados_;
        }

        $data = new stdClass();
        $data->emisor = $emisor_;
        $data->comprobante = $comprobante_;
        $data->receptor = $receptor_;
        $data->relacionados = $relacionados_;

        return $data;

    }

    private function integra_impuesto(string $attr, array $concepto_json, stdClass $imp, string $tipo_impuestos){
        $keys_data_concepto = $this->keys_data_concepto();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener keys_data_concepto',data:  $keys_data_concepto);
        }

        $imp_json = $this->imps_json(imp: $imp);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar value',data:  $imp_json);
        }

        $key_output = $this->key_output(atributo: $attr, keys_data: $keys_data_concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key_output',data:  $key_output);
        }

        $concepto_json[$key_output][$tipo_impuestos][] = $imp_json;
        return $concepto_json;
    }

    private function key_output(string $atributo, array $keys_data){
        return $keys_data[$atributo];
    }


    private function keys_data_comprobante(): array
    {
        $keys_data['serie'] = 'Serie';
        $keys_data['folio'] = 'Folio';
        $keys_data['forma_pago'] = 'FormaPago';
        $keys_data['sub_total'] = 'SubTotal';
        $keys_data['moneda'] = 'Moneda';
        $keys_data['total'] = 'Total';
        $keys_data['lugar_expedicion'] = 'LugarExpedicion';
        $keys_data['tipo_de_comprobante'] = 'TipoDeComprobante';
        $keys_data['exportacion'] = 'Exportacion';
        $keys_data['fecha'] = 'Fecha';
        $keys_data['descuento'] = 'Descuento';
        $keys_data['no_certificado'] = 'NoCertificado';
        $keys_data['metodo_pago'] = 'MetodoPago';
        return $keys_data;
    }

    private function keys_data_concepto(): array
    {
        $keys_data['clave_prod_serv'] = 'ClaveProdServ';
        $keys_data['cantidad'] = 'Cantidad';
        $keys_data['clave_unidad'] = 'Cantidad';
        $keys_data['descripcion'] = 'Descripcion';
        $keys_data['valor_unitario'] = 'ValorUnitario';
        $keys_data['importe'] = 'Importe';
        $keys_data['objeto_imp'] = 'ObjetoImp';
        $keys_data['no_identificacion'] = 'NoIdentificacion';
        $keys_data['unidad'] = 'Unidad';
        $keys_data['impuestos'] = 'Impuestos';



        return $keys_data;
    }

    private function keys_data_emisor(): array
    {
        $keys_data['rfc'] = 'Rfc';
        $keys_data['nombre'] = 'Nombre';
        $keys_data['regimen_fiscal'] = 'FormaPago';
        $keys_data['sub_total'] = 'RegimenFiscal';
        return $keys_data;
    }

    private function keys_data_impuesto(): array
    {
        $keys_data['base'] = 'Base';
        $keys_data['impuesto'] = 'Impuesto';
        $keys_data['tipo_factor'] = 'TipoFactor';
        $keys_data['tasa_o_cuota'] = 'TasaOCuota';
        $keys_data['importe'] = 'Importe';
        $keys_data['descuento'] = 'Descuento';

        return $keys_data;
    }
    private function keys_data_receptor(): array
    {
        $keys_data['rfc'] = 'Rfc';
        $keys_data['nombre'] = 'Nombre';
        $keys_data['uso_cfdi'] = 'UsoCFDI';
        $keys_data['domicilio_fiscal_receptor'] = 'DomicilioFiscalReceptor';
        $keys_data['regimen_fiscal_receptor'] = 'RegimenFiscalReceptor';

        return $keys_data;
    }

    private function reasigna_retenciones(stdClass $impuestos_){

        $total_impuestos_retenidos = $this->total_impuestos_retenidos(impuestos_: $impuestos_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar retenciones', data: $total_impuestos_retenidos);
        }

        $impuestos_->total_impuestos_retenidos = $total_impuestos_retenidos;
        return $impuestos_;
    }

    private function reasigna_traslados(stdClass $impuestos_){
        $total_impuestos_trasladados = $this->total_impuestos_trasladados(impuestos_: $impuestos_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar traslados', data: $total_impuestos_trasladados);
        }
        $impuestos_->total_impuestos_trasladados = $total_impuestos_trasladados;
        return $impuestos_;
    }

    private function total_impuestos_retenidos(stdClass $impuestos_): array|string
    {
        $total_impuestos_retenidos = trim($impuestos_->total_impuestos_retenidos);
        $total_impuestos_retenidos = str_replace(' ', '', $total_impuestos_retenidos);
        $total_impuestos_retenidos = str_replace('$', '', $total_impuestos_retenidos);
        return str_replace(',', '', $total_impuestos_retenidos);
    }

    private function total_impuestos_trasladados(stdClass $impuestos_): array|string
    {
        $total_impuestos_trasladados = trim($impuestos_->total_impuestos_trasladados);
        $total_impuestos_trasladados = str_replace(' ', '', $total_impuestos_trasladados);
        $total_impuestos_trasladados = str_replace('$', '', $total_impuestos_trasladados);
        return str_replace(',', '', $total_impuestos_trasladados);
    }
}
