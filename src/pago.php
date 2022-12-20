<?php
namespace gamboamartin\xml_cfdi_4;
use DOMElement;
use DOMException;
use gamboamartin\errores\errores;
use stdClass;
use Throwable;

class pago{
    private validacion $valida;
    private errores $error;
    public function __construct(){
        $this->valida = new validacion();
        $this->error = new errores();
    }

    /**
     * @param stdClass|array $docto_relacionado
     * @param DOMElement $nodo_docto_relacionado
     * @return DOMElement|array
     */
    private function attr_docto_relacionado(stdClass|array $docto_relacionado,
                                            DOMElement $nodo_docto_relacionado): DOMElement|array
    {
        $docto_relacionado_ = $docto_relacionado;
        if(is_array($docto_relacionado)){
            $docto_relacionado_ = (object)$docto_relacionado;
        }

        if((float)$docto_relacionado_->equivalencia_dr === 1.0){
            $docto_relacionado_ = $this->equivalencia_dr_1(docto_relacionado: $docto_relacionado_);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al ajustar equivalencia_dr ', data: $docto_relacionado_);
            }

        }

        $nodo_docto_relacionado->setAttribute('IdDocumento', $docto_relacionado_->id_documento);
        $nodo_docto_relacionado->setAttribute('Folio', $docto_relacionado_->folio);
        $nodo_docto_relacionado->setAttribute('MonedaDR', $docto_relacionado_->moneda_dr);
        $nodo_docto_relacionado->setAttribute('EquivalenciaDR', $docto_relacionado_->equivalencia_dr);
        $nodo_docto_relacionado->setAttribute('NumParcialidad', $docto_relacionado_->num_parcialidad);
        $nodo_docto_relacionado->setAttribute('ImpSaldoAnt', $docto_relacionado_->imp_saldo_ant);
        $nodo_docto_relacionado->setAttribute('ImpPagado', $docto_relacionado_->imp_pagado);
        $nodo_docto_relacionado->setAttribute('ImpSaldoInsoluto', $docto_relacionado_->imp_saldo_insoluto);
        $nodo_docto_relacionado->setAttribute('ObjetoImpDR', $docto_relacionado_->objeto_imp_dr);
        return $nodo_docto_relacionado;
    }

    private function attr_pago(DOMElement $nodo_pago, stdClass $pago): DOMElement
    {
        $nodo_pago->setAttribute('FechaPago', $pago->fecha_pago);
        $nodo_pago->setAttribute('FormaDePagoP', $pago->forma_de_pago_p);
        $nodo_pago->setAttribute('MonedaP', $pago->moneda_p);
        $nodo_pago->setAttribute('TipoCambioP', $pago->tipo_cambio_p);
        $nodo_pago->setAttribute('Monto', $pago->monto);

        return $nodo_pago;
    }

    /**
     * Si la equivalencia dr es 1.0 o 1.00 asigna a 1
     * @param stdClass|array $docto_relacionado Documento relacionado de pago
     * @return stdClass|array
     */
    private function equivalencia_dr_1(stdClass|array $docto_relacionado): stdClass|array
    {
        $docto_relacionado_ = $docto_relacionado;
        if(is_array($docto_relacionado)){
            $docto_relacionado_ = (object)$docto_relacionado;
        }

        $keys = array('equivalencia_dr');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $docto_relacionado);
        if(errores::$error){
            $fix = 'Favor de integrar equivalencia_dr al objeto $docto_relacionado ej';
            $fix.= ' $docto_relacionado->equivalencia_dr = 1 o $docto_relacionado[equivalencia_dr] = 1 ';
            $fix.= ' donde equivalencia_dr debe ser un numero en 1 o  1.0 o 1.000000';
            return $this->error->error(mensaje: 'Error al validar $docto_relacionado', data: $valida, fix: $fix);
        }

        if((float)$docto_relacionado_->equivalencia_dr!==1.0){
            $fix = 'Favor de integrar equivalencia_dr al objeto $docto_relacionado ej';
            $fix.= ' $docto_relacionado->equivalencia_dr = 1 o $docto_relacionado[equivalencia_dr] = 1 ';
            $fix.= ' donde equivalencia_dr debe ser un numero en 1 o  1.0 o 1.000000';
            return $this->error->error(mensaje: 'Error equivalencia_dr debe ser un 1 como flotante o entero',
                data: $valida, fix: $fix);
        }

        $docto_relacionado_->equivalencia_dr = round($docto_relacionado_->equivalencia_dr);
        $docto_relacionado_->equivalencia_dr = (int)$docto_relacionado_->equivalencia_dr;
        $docto_relacionado_->equivalencia_dr = number_format($docto_relacionado_->equivalencia_dr);
        return $docto_relacionado_;
    }

    private function integra_traslados_p(DOMElement $nodo_traslados_p,  stdClass $traslados_p, xml $xml): array|DOMElement
    {
        if(!isset($traslados_p->traslado_p)){
            return $this->error->error(mensaje: 'Error debe existir traslado_p en pago', data: $traslados_p);
        }
        if(empty($traslados_p->traslado_p)){
            return $this->error->error(mensaje: 'Error  traslado_p en pago esta vacio', data: $traslados_p);
        }

        foreach ($traslados_p->traslado_p as $traslado_p) {

            if(is_array($traslado_p)){
                $traslado_p = (object)$traslado_p;
            }

            $nodo_traslado_p = $this->nodo_traslado_p(nodo_traslados_p: $nodo_traslados_p,
                traslado_p:  $traslado_p,xml:  $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al ajustar $traslado_p', data: $nodo_traslado_p);
            }

        }
        return $nodo_traslados_p;
    }


    private function nodo_docto_relacionado(stdClass $docto_relacionado, DOMElement $nodo_pago, xml $xml): array|DOMElement
    {
        $valida = $this->valida->valida_docto_relacionado(docto_relacionado: $docto_relacionado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $docto_relacionado', data: $valida);
        }

        $docto_relacionado_r = (new init())->ajusta_montos_doc_rel(docto_relacionado: $docto_relacionado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar $docto_relacionado', data: $docto_relacionado_r);
        }

        try {
            $nodo_docto_relacionado = $xml->dom->createElement('pago20:DoctoRelacionado');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento pago20:DoctoRelacionado', data: $e);
        }
        $nodo_pago->appendChild($nodo_docto_relacionado);

        $nodo_docto_relacionado = $this->attr_docto_relacionado(docto_relacionado: $docto_relacionado_r,
            nodo_docto_relacionado:  $nodo_docto_relacionado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar $docto_relacionado', data: $nodo_docto_relacionado);
        }
        return $nodo_docto_relacionado;
    }


    public function nodo_doctos_rel(DOMElement $nodo_pago, stdClass $pago, xml $xml): array|DOMElement
    {
        if(!isset($pago->docto_relacionado)){
            return $this->error->error(mensaje: 'Error debe existir docto_relacionado en pago', data: $pago);
        }
        if(empty($pago->docto_relacionado)){
            return $this->error->error(mensaje: 'Error  docto_relacionado en pago esta vacio', data: $pago);
        }
        foreach ($pago->docto_relacionado as $docto_relacionado){

            if(is_array($docto_relacionado)){
                $docto_relacionado = (object)$docto_relacionado;
            }

            $nodo_docto_relacionado = $this->nodo_docto_relacionado(docto_relacionado: $docto_relacionado, nodo_pago: $nodo_pago,xml:  $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar $docto_relacionado', data: $nodo_docto_relacionado);
            }

            if(!isset($docto_relacionado->impuestos_dr)){
                return $this->error->error(mensaje: 'Error debe existir impuestos_dr en docto rel', data: $docto_relacionado);
            }
            if(empty($docto_relacionado->impuestos_dr)){
                return $this->error->error(mensaje: 'Error  impuestos_dr en pago esta docto rel', data: $docto_relacionado);
            }

            $nodo_impuestos_dr = $this->nodo_impuestos_dr(docto_relacionado:$docto_relacionado,
                nodo_docto_relacionado: $nodo_docto_relacionado,xml:  $xml);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar nodo', data: $nodo_impuestos_dr);
            }

        }
        return $nodo_pago;
    }


    private function nodo_impuestos_dr(stdClass $docto_relacionado, DOMElement $nodo_docto_relacionado, xml $xml): array|DOMElement
    {
        foreach ($docto_relacionado->impuestos_dr as $impuesto_dr){
            try {
                $nodo_impuestos_dr = $xml->dom->createElement('pago20:ImpuestosDR');
            }
            catch (Throwable $e){
                return $this->error->error(mensaje: 'Error al crear el elemento pago20:ImpuestosDR', data: $e);
            }
            $nodo_docto_relacionado->appendChild($nodo_impuestos_dr);

            if(is_array($impuesto_dr)){
                $impuesto_dr = (object)$impuesto_dr;
            }

            if(!isset($impuesto_dr->traslados_dr)){
                return $this->error->error(mensaje: 'Error debe existir traslados_dr en docto rel', data: $impuesto_dr);
            }
            if(empty($impuesto_dr->traslados_dr)){
                return $this->error->error(mensaje: 'Error  traslados_dr en pago esta docto rel', data: $impuesto_dr);
            }
            foreach ($impuesto_dr->traslados_dr as $traslados_dr){

                if(is_array($traslados_dr)){
                    $traslados_dr = (object)$traslados_dr;
                }

                $nodo_traslados_dr = $this->nodo_traslados_dr(nodo_impuestos_dr: $nodo_impuestos_dr,
                    traslados_dr: $traslados_dr,xml:  $xml);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al validar $nodo_traslados_dr', data: $nodo_traslados_dr);
                }

            }
        }
        return $nodo_docto_relacionado;
    }


    public function nodo_impuestos_p(DOMElement $nodo_pago, stdClass $pago, xml $xml): array|DOMElement
    {
        if(!isset($pago->impuestos_p)){
            return $this->error->error(mensaje: 'Error debe existir impuestos_p en pago', data: $pago);
        }
        if(empty($pago->impuestos_p)){
            return $this->error->error(mensaje: 'Error  impuestos_p en pago esta vacio', data: $pago);
        }

        foreach ($pago->impuestos_p as $impuesto_p){
            try {
                $nodo_impuestos_p = $xml->dom->createElement('pago20:ImpuestosP');
            }
            catch (Throwable $e){
                return $this->error->error(mensaje: 'Error al crear el elemento pago20:ImpuestosP', data: $e);
            }
            $nodo_pago->appendChild($nodo_impuestos_p);

            if(is_array($impuesto_p)){
                $impuesto_p = (object)$impuesto_p;
            }

            if(!isset($impuesto_p->traslados_p)){
                return $this->error->error(mensaje: 'Error debe existir traslados_p en pago', data: $pago);
            }
            if(empty($impuesto_p->traslados_p)){
                return $this->error->error(mensaje: 'Error  traslados_p en pago esta vacio', data: $pago);
            }

            foreach ($impuesto_p->traslados_p as $traslados_p) {
                if(is_array($traslados_p)){
                    $traslados_p = (object)$traslados_p;
                }
                $nodo_traslados_p = $this->nodos_traslados_p(nodo_impuestos_p: $nodo_impuestos_p,
                    traslados_p:  $traslados_p,xml:  $xml);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al ajustar $nodo_traslados_p', data: $nodo_traslados_p);
                }

            }

        }
        return $nodo_pago;
    }


    public function nodo_pago(DOMElement $nodo_pagos, stdClass $pago, xml $xml): array|DOMElement
    {
        $valida = $this->valida->valida_data_pago(pago: $pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar pago', data: $valida);
        }

        $keys = array('monto');
        $pago_r = (new init())->montos_dos_decimals(keys: $keys, obj: $pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ajustar pago', data: $pago_r);
        }

        try {
            $nodo_pago  = $xml->dom->createElement('pago20:Pago');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento pago20:Pago', data: $e);
        }

        $nodo_pagos->appendChild($nodo_pago);

        $nodo_pago = $this->attr_pago(nodo_pago: $nodo_pago, pago: $pago_r);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar pago', data: $nodo_pago);
        }
        return $nodo_pago;
    }


    public function nodo_pagos(DOMElement $nodo_complemento, xml $xml): bool|DOMElement|array
    {
        try {
            $nodo_pagos = $xml->dom->createElementNS($xml->cfdi->comprobante->xmlns_pago20, 'pago20:Pagos');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento pago20:Pagos', data: $e);
        }

        $nodo_complemento->appendChild($nodo_pagos);

        $nodo_pagos->setAttribute('xmlns:pago20', $xml->cfdi->comprobante->xmlns_pago20);
        $nodo_pagos->setAttribute('Version', '2.0');
        return $nodo_pagos;
    }

    public function nodo_totales(DOMElement $nodo_pagos, stdClass $pagos, xml $xml): array|DOMElement
    {
        try {
            $nodo_totales = $xml->dom->createElement('pago20:Totales');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento pago20:Totales', data: $e);
        }
        $nodo_pagos->appendChild($nodo_totales);

        $keys = array('total_traslados_base_iva_16','total_traslados_impuesto_iva_16','monto_total_pagos');
        $valida = $this->valida->valida_numerics(keys: $keys, row: $pagos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar pagos', data: $valida);
        }

        $pagos_parser = (new init())->montos_dos_decimals(keys: $keys,obj:  $pagos);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar valores', data: $pagos_parser);
        }

        $nodo_totales->setAttribute('TotalTrasladosBaseIVA16', $pagos_parser->total_traslados_base_iva_16);
        $nodo_totales->setAttribute('TotalTrasladosImpuestoIVA16', $pagos_parser->total_traslados_impuesto_iva_16);
        $nodo_totales->setAttribute('MontoTotalPagos', $pagos_parser->monto_total_pagos);
        return $nodo_totales;
    }


    private function nodo_traslado_dr(DOMElement $nodo_traslados_dr, stdClass $traslado_dr, xml $xml): bool|array|DOMElement
    {
        $valida = $this->valida->valida_traslado(traslado_dr: $traslado_dr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $traslado_dr', data: $valida);
        }

        $traslado_dr_r = (new init())->ajusta_montos_traslado(traslado_dr: $traslado_dr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $traslado_dr', data: $traslado_dr_r);
        }

        try {
            $nodo_traslado_dr = $xml->dom->createElement('pago20:TrasladoDR');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento pago20:TrasladoDR', data: $e);
        }
        $nodo_traslados_dr->appendChild($nodo_traslado_dr);

        $nodo_traslado_dr->setAttribute('BaseDR', $traslado_dr_r->base_dr);
        $nodo_traslado_dr->setAttribute('ImpuestoDR', $traslado_dr_r->impuesto_dr);
        $nodo_traslado_dr->setAttribute('TipoFactorDR', $traslado_dr_r->tipo_factor_dr);
        $nodo_traslado_dr->setAttribute('TasaOCuotaDR', $traslado_dr_r->tasa_o_cuota_dr);
        $nodo_traslado_dr->setAttribute('ImporteDR', $traslado_dr_r->importe_dr);

        return $nodo_traslado_dr;
    }

    private function nodo_traslado_p(DOMElement $nodo_traslados_p, stdClass $traslado_p, xml $xml): bool|array|DOMElement
    {
        $nodo_traslado_p = $xml->dom->createElement('pago20:TrasladoP');
        $nodo_traslados_p->appendChild($nodo_traslado_p);


        $valida = $this->valida->valida_traslado_p(traslado_p: $traslado_p);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $traslado_p', data: $valida);
        }

        $traslado_p_r = (new init())->ajusta_traslado_p(traslado_p: $traslado_p);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar $traslado_p', data: $traslado_p_r);
        }

        $nodo_traslado_p->setAttribute('BaseP', $traslado_p_r->base_p);
        $nodo_traslado_p->setAttribute('ImpuestoP', $traslado_p_r->impuesto_p);
        $nodo_traslado_p->setAttribute('TipoFactorP', $traslado_p_r->tipo_factor_p);
        $nodo_traslado_p->setAttribute('TasaOCuotaP', $traslado_p_r->tasa_o_cuota_p);
        $nodo_traslado_p->setAttribute('ImporteP', $traslado_p_r->importe_p);
        return $nodo_traslado_p;
    }


    private function nodo_traslados_dr(DOMElement $nodo_impuestos_dr, stdClass $traslados_dr, xml $xml): array|DOMElement
    {
        try {
            $nodo_traslados_dr = $xml->dom->createElement('pago20:TrasladosDR');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento pago20:TrasladosDR', data: $e);
        }
        $nodo_impuestos_dr->appendChild($nodo_traslados_dr);



        if(!isset($traslados_dr->traslado_dr)){
            return $this->error->error(mensaje: 'Error debe existir $traslado_dr en docto rel', data: $traslados_dr);
        }
        if(empty($traslados_dr->traslado_dr)){
            return $this->error->error(mensaje: 'Error  $traslado_dr en pago esta docto rel', data: $traslados_dr);
        }

        $nodos_traslado_dr = $this->nodos_traslados_dr(nodo_traslados_dr: $nodo_traslados_dr,
            traslados_dr: $traslados_dr, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $traslado_dr', data: $nodos_traslado_dr);
        }
        return $nodos_traslado_dr;
    }


    private function nodos_traslados_dr(DOMElement $nodo_traslados_dr, stdClass $traslados_dr, xml $xml): array|DOMElement
    {
        foreach ($traslados_dr->traslado_dr as $traslado_dr) {
            if(is_array($traslado_dr)){
                $traslado_dr = (object)$traslado_dr;
            }
            $nodo_traslado_dr = $this->nodo_traslado_dr(nodo_traslados_dr: $nodo_traslados_dr,
                traslado_dr:  $traslado_dr, xml: $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar $traslado_dr', data: $nodo_traslado_dr);
            }

        }
        return $nodo_traslados_dr;
    }


    private function nodos_traslados_p(DOMElement $nodo_impuestos_p, stdClass $traslados_p, xml $xml): bool|array|DOMElement
    {
        try {
            $nodo_traslados_p = $xml->dom->createElement('pago20:TrasladosP');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento pago20:TrasladosP', data: $e);
        }
        $nodo_impuestos_p->appendChild($nodo_traslados_p);

        $nodo_traslado_p = $this->integra_traslados_p(nodo_traslados_p: $nodo_traslados_p,
            traslados_p:  $traslados_p, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar $traslado_p', data: $nodo_traslado_p);
        }
        return $nodo_traslados_p;
    }
}
