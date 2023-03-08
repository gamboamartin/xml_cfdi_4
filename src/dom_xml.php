<?php
namespace gamboamartin\xml_cfdi_4;

use DOMElement;
use DOMException;
use DOMNode;
use gamboamartin\errores\errores;

use stdClass;
use Throwable;

class dom_xml{
    private validacion $valida;
    private errores $error;
    public function __construct(){
        $this->valida = new validacion();
        $this->error = new errores();
    }

    public function anexa_impuestos(DOMElement $data_nodo, stdClass $impuestos,
                                    string $obj_impuestos, string $tipo_impuesto, xml $xml): array|DOMElement
    {
        if(!isset($impuestos->$obj_impuestos)){
            return $this->error->error(mensaje: "Error no existe $obj_impuestos en impuestos", data: $impuestos);
        }
        if(!is_array($impuestos->$obj_impuestos)){
            return $this->error->error(mensaje: 'Error obj_impuestos en impuestos debe ser un array', data: $impuestos);
        }
        if(count($impuestos->$obj_impuestos)>0){
            $obj_imp_xml = ucwords($obj_impuestos);
            $nodo_impuestos = $xml->dom->createElement("cfdi:$obj_imp_xml");
            $data_nodo->appendChild($nodo_impuestos);

            $nodo_impuestos = $this->carga_impuestos(impuestos: $impuestos,nodo_impuestos:  $nodo_impuestos,
                tipo_impuesto: $tipo_impuesto,obj_impuestos: $obj_impuestos, xml: $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar nodo', data: $nodo_impuestos);
            }

        }
        return $data_nodo;
    }



    private function attrs_concepto(stdClass $concepto, DOMElement $nodo_concepto): DOMElement|array
    {
        $valida = $this->valida->valida_data_concepto(concepto: $concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar concepto', data: $valida);
        }
        $nodo_concepto->setAttribute('ClaveProdServ', $concepto->clave_prod_serv);

        if(isset($concepto->no_identificacion) && $concepto->no_identificacion!==''){
            $nodo_concepto->setAttribute('NoIdentificacion', $concepto->no_identificacion);
        }

        if(isset($concepto->unidad) && $concepto->unidad!==''){
            $nodo_concepto->setAttribute('Unidad', $concepto->unidad);
        }
        if(isset($concepto->descuento) && $concepto->descuento!==''){
            $nodo_concepto->setAttribute('Descuento', $concepto->descuento);
        }

        $nodo_concepto->setAttribute('Cantidad', $concepto->cantidad);
        $nodo_concepto->setAttribute('ClaveUnidad', $concepto->clave_unidad);
        $nodo_concepto->setAttribute('Descripcion', $concepto->descripcion);
        $nodo_concepto->setAttribute('ValorUnitario', $concepto->valor_unitario);
        $nodo_concepto->setAttribute('Importe', $concepto->importe);
        $nodo_concepto->setAttribute('ObjetoImp', $concepto->objeto_imp);

        return $nodo_concepto;
    }

    private function attrs_concepto_retencion(DOMElement $nodo_retencion, stdClass $retencion): DOMElement|array
    {
        $keys = array('base','impuesto','tipo_factor','tasa_o_cuota','importe');
        $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $retencion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar retencion', data: $valida);
        }
        $keys = array('base','tasa_o_cuota','importe');
        $valida = $this->valida->valida_numerics(keys:$keys, row: $retencion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar traslado', data: $valida);
        }

        $nodo_retencion->setAttribute('Base', $retencion->base);
        $nodo_retencion->setAttribute('Impuesto', $retencion->impuesto);
        $nodo_retencion->setAttribute('TipoFactor', $retencion->tipo_factor);
        $nodo_retencion->setAttribute('TasaOCuota', $retencion->tasa_o_cuota);
        $nodo_retencion->setAttribute('Importe', $retencion->importe);
        return $nodo_retencion;
    }

    private function attrs_concepto_traslado(DOMElement $nodo_traslado, stdClass $traslado): DOMElement|array
    {
        $keys = array('base','impuesto','tipo_factor','tasa_o_cuota','importe');
        $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $traslado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar traslado', data: $valida);
        }
        $keys = array('base','tasa_o_cuota','importe');
        $valida = $this->valida->valida_numerics(keys:$keys, row: $traslado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar traslado', data: $valida);
        }

        $nodo_traslado->setAttribute('Base', $traslado->base);
        $nodo_traslado->setAttribute('Impuesto', $traslado->impuesto);
        $nodo_traslado->setAttribute('TipoFactor', $traslado->tipo_factor);
        $nodo_traslado->setAttribute('TasaOCuota', $traslado->tasa_o_cuota);
        $nodo_traslado->setAttribute('Importe', $traslado->importe);
        return $nodo_traslado;
    }


    public function carga_conceptos(array $conceptos, DOMElement $nodo_conceptos, xml $xml): xml|array
    {
        foreach ($conceptos as $concepto){
            if(is_array($concepto)){
                $concepto = (object)$concepto;
            }
            if(!is_object($concepto)){
                return $this->error->error(mensaje: 'Error el concepto debe ser un objeto', data: $concepto);
            }
            $elementos_concepto = (new dom_xml())->elementos_concepto(concepto: $concepto,
                nodo_conceptos: $nodo_conceptos,xml:  $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar atributos', data: $elementos_concepto);
            }
        }
        return $xml;
    }


    private function carga_nodo_concepto_impuestos(array $impuestos, DOMElement $nodo_impuestos, xml $xml): array|DOMElement
    {
        foreach ($impuestos as $impuesto){

            $valida = $this->valida->valida_data_impuestos(impuesto: $impuesto);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar $impuesto', data: $valida);
            }

            if(isset($impuesto->traslados) && count($impuesto->traslados)>0){

                $nodo_traslados = $this->concepto_traslados(nodo_impuestos: $nodo_impuestos,
                    traslados: $impuesto->traslados,xml: $xml);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al asignar atributos', data: $nodo_traslados);
                }
            }
            if(isset($impuesto->retenciones) && count($impuesto->retenciones)>0){

                $nodo_retenciones = $this->concepto_retenciones(nodo_impuestos: $nodo_impuestos,
                    retenciones: $impuesto->retenciones,xml: $xml);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al asignar atributos', data: $nodo_retenciones);
                }
            }

        }
        return $nodo_impuestos;
    }

    private function carga_nodo_retencion(DOMElement $nodo_retenciones, stdClass $retencion, xml $xml): array|DOMElement
    {
        try {
            $nodo_retencion = $xml->dom->createElement('cfdi:Retencion');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear elemento cfdi:Traslado', data: $e);
        }

        $nodo_retenciones->appendChild($nodo_retencion);
        $nodo_retencion = $this->attrs_concepto_retencion(nodo_retencion: $nodo_retencion,retencion: $retencion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar atributos', data: $nodo_retencion);
        }
        return $nodo_retencion;
    }


    private function carga_nodo_traslado(DOMElement $nodo_traslados, stdClass $traslado, xml $xml): array|DOMElement
    {
        try {
            $nodo_traslado = $xml->dom->createElement('cfdi:Traslado');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear elemento cfdi:Traslado', data: $e);
        }

        $nodo_traslados->appendChild($nodo_traslado);
        $nodo_traslado = $this->attrs_concepto_traslado(nodo_traslado: $nodo_traslado,traslado: $traslado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar atributos', data: $nodo_traslado);
        }
        return $nodo_traslado;
    }

    private function carga_nodo_impuesto_comprobante(DOMElement $nodo_impuestos, stdClass $obj_impuesto, string $tipo_impuesto, xml $xml): array|DOMElement
    {
        $nodo_impuesto= $this->crea_nodo_impuesto(nodo_impuestos: $nodo_impuestos,tipo_impuesto: $tipo_impuesto, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar nodo', data: $nodo_impuesto);
        }

        $nodo_impuesto = $this->nodo_impuesto(
            nodo_impuesto: $nodo_impuesto,obj_impuesto:  $obj_impuesto, tipo_impuesto: $tipo_impuesto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar nodo', data: $nodo_impuesto);
        }
        return $nodo_impuesto;
    }

    private function carga_nodos_retencion(DOMElement $nodo_retenciones, array $retenciones, XML $xml): array|DOMElement
    {
        foreach ($retenciones as $retencion){
            if(!is_object($retencion)){
                return $this->error->error(mensaje: 'Error retencion debe ser un objeto', data: $retencion);
            }
            $nodo_retencion = $this->carga_nodo_retencion(nodo_retenciones: $nodo_retenciones,retencion: $retencion,xml: $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar atributos', data: $nodo_retencion);
            }
        }
        return $nodo_retenciones;
    }


    private function carga_nodos_traslado(DOMElement $nodo_traslados, array $traslados, XML $xml): array|DOMElement
    {
        foreach ($traslados as $traslado){
            if(!is_object($traslado)){
                return $this->error->error(mensaje: 'Error $traslado debe ser un objeto', data: $traslado);
            }
            $nodo_traslado = $this->carga_nodo_traslado(nodo_traslados: $nodo_traslados,traslado: $traslado,xml: $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar atributos', data: $nodo_traslado);
            }
        }
        return $nodo_traslados;
    }

    private function carga_impuestos(stdClass $impuestos,DOMElement $nodo_impuestos, string $tipo_impuesto, string $obj_impuestos, xml $xml):  array|DOMElement{
        foreach ($impuestos->$obj_impuestos as $obj_impuesto){

            $valida = $this->valida->valida_nodo_impuesto(obj_impuesto: $obj_impuesto);
            if(errores::$error){
                return $this->error->error(mensaje: "Error al validar $tipo_impuesto", data: $valida);
            }

            $nodo_impuesto = $this->carga_nodo_impuesto_comprobante(nodo_impuestos: $nodo_impuestos,
                obj_impuesto:  $obj_impuesto, tipo_impuesto: $tipo_impuesto,xml:  $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar nodo', data: $nodo_impuesto);
            }

        }
        return $nodo_impuestos;
    }



    public function comprobante(stdClass $comprobante, xml $xml): array|stdClass
    {
        $nodo = $this->inicializa_comprobante(comprobante: $comprobante,xml:  $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar cfdi comprobante', data: $nodo);
        }

        $comprobante_base = $this->comprobante_base(nodo:$nodo,
            tipo_de_comprobante: $comprobante->tipo_de_comprobante, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar cfdi comprobante', data: $comprobante_base);
        }

        return $comprobante_base;
    }

    private function comprobante_base(DOMElement $nodo, string $tipo_de_comprobante, xml $xml): array|stdClass
    {


        $nodo->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        if(!isset($xml->xml)){
            return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
        }

        if($tipo_de_comprobante === 'P') {
            $nodo->setAttribute('xmlns:pago20', 'http://www.sat.gob.mx/Pagos20');
            $nodo->setAttribute('xmlns:cfdi', 'http://www.sat.gob.mx/cfd/4');
            $nodo->setAttribute('xsi:schemaLocation', 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/Pagos20 http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd');
        }
        if($tipo_de_comprobante === 'E') {
            $nodo->setAttribute('xmlns:cfdi', 'http://www.sat.gob.mx/cfd/4');
            $nodo->setAttribute('xsi:schemaLocation', 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd');
        }
        if($tipo_de_comprobante === 'I') {
            $nodo->setAttribute('xmlns:cfdi', 'http://www.sat.gob.mx/cfd/4');
            $nodo->setAttribute('xsi:schemaLocation', 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd');
        }
        if($tipo_de_comprobante === 'N') {
            $nodo->setAttribute('xmlns:nomina12', 'http://www.sat.gob.mx/nomina12');
            $nodo->setAttribute('xmlns:cfdi', 'http://www.sat.gob.mx/cfd/4');
            $nodo->setAttribute('xsi:schemaLocation', 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/nomina12 http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina12.xsd');
        }



        $data_nodo = $this->init_dom_cfdi_comprobante(nodo: $nodo,xml:  $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar cfdi comprobante', data: $data_nodo);
        }

        return $xml->cfdi;
    }

    /**
     * Se valida y se integra lo necesario en en nodo comprobante referente al complemento de pago
     * @version 0.3.0
     * @param stdClass $comprobante
     * @param xml $xml
     * @return bool|array
     */
    public function comprobante_pago(stdClass $comprobante, xml $xml): bool|array
    {
        if(!isset($xml->xml)){
            return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
        }

        $valida = $this->valida->complemento_pago_comprobante(comprobante: $comprobante,xml:  $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar complemento pago dom pago', data: $valida);
        }

        return $valida;
    }

    private function concepto_retenciones(DOMElement $nodo_impuestos, array $retenciones, xml $xml): array|DOMElement
    {

        try {
            $nodo_retenciones = $xml->dom->createElement('cfdi:Retenciones');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento cfdi:Retenciones', data: $e);
        }
        $nodo_impuestos->appendChild($nodo_retenciones);
        $nodo_retenciones= $this->carga_nodos_retencion(nodo_retenciones: $nodo_retenciones,retenciones: $retenciones, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar atributos', data: $nodo_retenciones);
        }
        return $nodo_retenciones;
    }


    private function concepto_traslados(DOMElement $nodo_impuestos, array $traslados, xml $xml): array|DOMElement
    {

        try {
            $nodo_traslados = $xml->dom->createElement('cfdi:Traslados');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento cfdi:Traslados', data: $e);
        }
        $nodo_impuestos->appendChild($nodo_traslados);
        $nodo_traslados = $this->carga_nodos_traslado(nodo_traslados: $nodo_traslados,traslados: $traslados, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar atributos', data: $nodo_traslados);
        }
        return $nodo_traslados;
    }

    private function crea_nodo_impuesto(DOMElement $nodo_impuestos, string $tipo_impuesto, xml $xml): bool|DOMElement
    {
        $nodo_impuesto = $xml->dom->createElement("cfdi:$tipo_impuesto");
        $nodo_impuestos->appendChild($nodo_impuesto);
        return $nodo_impuesto;
    }




    private function elemento_concepto(stdClass $concepto, DOMElement $nodo_conceptos, xml $xml): array|DOMElement
    {
        $valida = $this->valida->valida_data_concepto(concepto: $concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar concepto', data: $valida);
        }

        if(!isset($concepto->impuestos)){
            return $this->error->error(mensaje: 'Error debe existir impuestos en concepto', data: $concepto);
        }
        if(!is_array($concepto->impuestos)){
            return $this->error->error(mensaje: 'Error impuestos debe ser un array de objetos', data: $concepto);
        }
        try {
            $nodo_concepto = $xml->dom->createElement('cfdi:Concepto');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el atributo cfdi:Concepto', data: $e);
        }

        $nodo_conceptos->appendChild($nodo_concepto);

        $nodo_concepto = $this->attrs_concepto(concepto: $concepto, nodo_concepto: $nodo_concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar atributos', data: $nodo_concepto);
        }


        $nodo_impuestos = $this->genera_nodo_concepto_impuestos(impuestos: $concepto->impuestos,
            nodo_concepto: $nodo_concepto,xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al cargar nodo impuestos', data: $nodo_impuestos);
        }

        if(isset($concepto->a_cuanta_terceros)){
            $nodo_a_cuenta_terceros = $this->genera_nodo_a_cuenta_terceros(
                a_cuanta_terceros: $concepto->a_cuanta_terceros, nodo_concepto: $nodo_concepto, xml: $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al cargar nodo a cuenta terceros',
                    data: $nodo_a_cuenta_terceros);
            }
        }

        return $nodo_conceptos;
    }

    /**
     *
     */
    private function elementos_concepto(stdClass $concepto, DOMElement $nodo_conceptos, xml $xml): xml|array
    {

        /**
         * REFCATORIZAR
         */


        $attrs = array('valor_unitario','importe');
        foreach ($attrs as $attr){
            $concepto = $this->limpia_monto_attr(key: $attr, obj: $concepto);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al limpiar objeto', data: $concepto);
            }
        }

        if(isset($concepto->impuestos)){
            foreach ($concepto->impuestos as $impuesto){
                if(isset($impuesto->traslados)){
                    foreach ($impuesto->traslados as $indice=>$traslado){

                        $attrs = array('base','importe');
                        foreach ($attrs as $attr){
                            $traslado = $this->limpia_attr_existente(key: $attr,obj:  $traslado);
                            if(errores::$error){
                                return $this->error->error(mensaje: 'Error al limpiar objeto', data: $traslado);
                            }
                        }

                        $impuesto->traslados[$indice] = $traslado;

                    }
                }

                if(isset($impuesto->retenciones)){
                    foreach ($impuesto->retenciones as $indice=>$retencion){

                        $attrs = array('base','importe');
                        foreach ($attrs as $attr){
                            $retencion = $this->limpia_attr_existente(key: $attr,obj:  $retencion);
                            if(errores::$error){
                                return $this->error->error(mensaje: 'Error al limpiar objeto', data: $retencion);
                            }
                        }

                        $impuesto->retenciones[$indice] = $retencion;

                    }
                }

            }
        }

        $xml->cfdi->conceptos[] = new stdClass();
        $valida = $this->valida->valida_concepto(concepto: $concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $concepto', data: $valida);
        }
        $valida = $this->valida->valida_data_concepto(concepto: $concepto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $concepto', data: $valida);
        }

        $elemento_concepto = $this->elemento_concepto(concepto: $concepto, nodo_conceptos: $nodo_conceptos,xml:  $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar atributos', data: $elemento_concepto);
        }
        return $xml;
    }

    private function genera_attrs(array $keys, array $keys_especial, DOMElement $nodo, string $nodo_key,
                                  stdClass $object, xml $xml): array|DOMElement
    {
        if(!isset($xml->cfdi->$nodo_key)){
            return $this->error->error(mensaje: 'Error no esta inicializado $xml->cfdi->'.$nodo_key,
                data: $xml->cfdi);
        }

        $data_nodo = (new init())->asigna_datos_para_nodo(keys: $keys, nodo_key: $nodo_key,objetc:  $object,xml:  $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar '.$nodo_key, data: $data_nodo);
        }

        $setea = $this->setea_attr(keys: $keys, keys_especial: $keys_especial,nodo:  $nodo,
            nodo_key:  $nodo_key, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear '.$nodo_key, data: $setea);
        }
        return $setea;
    }

    private function genera_nodo_a_cuenta_terceros(array $a_cuanta_terceros, DOMElement $nodo_concepto, xml $xml): array|DOMElement
    {
        if(count($a_cuanta_terceros)>0){
            try {
                $nodo_a_cuanta_terceros = $xml->dom->createElement('cfdi:ACuentaTerceros');
            }
            catch (Throwable $e){
                return $this->error->error(mensaje: 'Error al crear el elemento cfdi:ACuentaTerceros', data: $e);
            }

            $nodo_concepto->appendChild($nodo_a_cuanta_terceros);

            foreach ($a_cuanta_terceros as $a_cuanta_tercero){
                $nodo_a_cuanta_terceros->setAttribute('RfcACuentaTerceros',
                    $a_cuanta_tercero->rfc_acuenta_terceros);

                $nodo_a_cuanta_terceros->setAttribute('NombreACuentaTerceros',
                    $a_cuanta_tercero->nombre_a_cuenta_terceros);

                $nodo_a_cuanta_terceros->setAttribute('RegimenFiscalACuentaTerceros',
                    $a_cuanta_tercero->regimen_fiscal_a_cuenta_terceros);

                $nodo_a_cuanta_terceros->setAttribute('DomicilioFiscalACuentaTerceros',
                    $a_cuanta_tercero->domicilio_fiscal_a_cuenta_terceros);
            }

        }

        return $nodo_concepto;
    }

    private function genera_nodo_concepto_impuestos(array $impuestos, DOMElement $nodo_concepto, xml $xml): array|DOMElement
    {

        /**
         * REFACTORIZAR
         */
        $aplica_nodo_impuestos = false;
        if(count($impuestos)>0){
            foreach ($impuestos as $impuesto){
                foreach ($impuesto as $tipo_imp){
                    if(count($tipo_imp)>0){
                        $aplica_nodo_impuestos = true;
                        break;
                    }
                }
                if($aplica_nodo_impuestos){
                    break;
                }

            }
        }

        if($aplica_nodo_impuestos){
            try {
                $nodo_impuestos = $xml->dom->createElement('cfdi:Impuestos');
            }
            catch (Throwable $e){
                return $this->error->error(mensaje: 'Error al crear el elemento cfdi:Impuestos', data: $e);
            }
            $nodo_concepto->appendChild($nodo_impuestos);

            $nodo_impuestos = $this->carga_nodo_concepto_impuestos(impuestos: $impuestos,
                nodo_impuestos: $nodo_impuestos,xml: $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al cargar nodo impuestos', data: $nodo_impuestos);
            }
        }
        return $nodo_concepto;
    }

    private function inicializa_comprobante(stdClass $comprobante, xml $xml): bool|array|DOMElement
    {
        $data_comprobante = (new init())->inicializa_valores_comprobante(comprobante: $comprobante, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar comprobante', data: $data_comprobante);
        }

        $nodo = $xml->dom->createElement('cfdi:Comprobante');
        $xml->xml = $xml->dom->appendChild($nodo);


        return $nodo;
    }

    private function init_dom_cfdi_comprobante(DOMElement $nodo, xml $xml): DOMElement
    {
        $nodo->setAttribute('Moneda', $xml->cfdi->comprobante->moneda);
        $nodo->setAttribute('Total', $xml->cfdi->comprobante->total);
        $nodo->setAttribute('Exportacion', $xml->cfdi->comprobante->exportacion);
        $nodo->setAttribute('TipoDeComprobante', $xml->cfdi->comprobante->tipo_de_comprobante);
        $nodo->setAttribute('SubTotal', $xml->cfdi->comprobante->sub_total);
        $nodo->setAttribute('LugarExpedicion', $xml->cfdi->comprobante->lugar_expedicion);
        $nodo->setAttribute('Fecha', $xml->cfdi->comprobante->fecha);
        $nodo->setAttribute('Folio', $xml->cfdi->comprobante->folio);
        $nodo->setAttribute('Version', $xml->cfdi->comprobante->version);
        if(isset($xml->cfdi->comprobante->serie) && (string)$xml->cfdi->comprobante->serie !== ''){
            $nodo->setAttribute('Serie', $xml->cfdi->comprobante->serie);
        }
        if(isset($xml->cfdi->comprobante->forma_pago) && (string)$xml->cfdi->comprobante->forma_pago !== ''){
            $nodo->setAttribute('FormaPago', $xml->cfdi->comprobante->forma_pago);
        }
        if(isset($xml->cfdi->comprobante->metodo_pago) && (string)$xml->cfdi->comprobante->metodo_pago !== ''){
            $nodo->setAttribute('MetodoPago', $xml->cfdi->comprobante->metodo_pago);
        }
        if(isset($xml->cfdi->comprobante->descuento) && (string)$xml->cfdi->comprobante->descuento !== ''){
            $nodo->setAttribute('Descuento', $xml->cfdi->comprobante->descuento);
        }
        if(isset($xml->cfdi->comprobante->tipo_cambio) && (string)$xml->cfdi->comprobante->tipo_cambio !== ''){
            $nodo->setAttribute('TipoCambio', $xml->cfdi->comprobante->tipo_cambio);
        }

        return $nodo;
    }

    private function key_especial_attr(string $key, string $key_nodo_xml,  array $keys_especial){
        foreach ($keys_especial as $key_val=>$key_especial){
            if($key_val === $key) {
                $key_nodo_xml = $key_especial;
                break;
            }
        }
        return $key_nodo_xml;
    }

    private function key_nodo_base(string $key): array|string
    {
        $key_nodo_xml = str_replace('_', ' ', $key);
        $key_nodo_xml = ucwords($key_nodo_xml);
        return str_replace(' ', '', $key_nodo_xml);
    }

    private function key_nodo_xml(string $key, array $keys_especial){
        $key_nodo_xml = $this->key_nodo_base(key: $key);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear $key_nodo_xml'.$key, data: $key_nodo_xml);
        }

        $key_nodo_xml = $this->key_especial_attr(key: $key,key_nodo_xml: $key_nodo_xml,
            keys_especial: $keys_especial);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear $key_nodo_xml'.$key, data: $key_nodo_xml);
        }
        return $key_nodo_xml;
    }

    public function nodo(array $keys, array $keys_especial, string $local_name, string $nodo_key,
                         stdClass $object, xml $xml): array|DOMElement
    {

        if(!isset($xml->cfdi->$nodo_key)){
            return $this->error->error(mensaje: 'Error no esta inicializado $xml->cfdi->'.$nodo_key,
                data: $xml->cfdi);
        }
        try {
            $nodo = $xml->dom->createElement($local_name);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al cargar elemento '.$local_name, data: $e);
        }
        $xml->xml->appendChild($nodo);

        $setea = $this->genera_attrs(keys: $keys, keys_especial: $keys_especial,nodo:  $nodo,nodo_key:  $nodo_key,
            object: $object, xml: $xml);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear '.$nodo_key, data: $setea);
        }
        return $nodo;
    }

    /**
     * @param DOMElement $nodo_impuesto
     * @param stdClass $obj_impuesto
     * @param string $tipo_impuesto
     * @return DOMElement
     */
    private function nodo_impuesto(DOMElement $nodo_impuesto, stdClass $obj_impuesto,string  $tipo_impuesto = 'Traslado'): DOMElement
    {
        if($tipo_impuesto === 'Traslado') {
            $nodo_impuesto->setAttribute('Base', $obj_impuesto->base);
        }
        $nodo_impuesto->setAttribute('Impuesto', $obj_impuesto->impuesto);

        if($tipo_impuesto === 'Traslado') {
            $nodo_impuesto->setAttribute('TipoFactor', $obj_impuesto->tipo_factor);
            $nodo_impuesto->setAttribute('TasaOCuota', $obj_impuesto->tasa_o_cuota);
        }
        $nodo_impuesto->setAttribute('Importe', $obj_impuesto->importe);
        return $nodo_impuesto;
    }

    /**
     * Limpia un monto
     * @param string|int|float $monto monto a limpiar
     * @return array|string
     */
    private function limpia_monto(string|int|float $monto): array|string
    {
        $monto = trim($monto);
        $monto = str_replace('$', '', $monto);
        return str_replace(',', '', $monto);
    }

    private function limpia_monto_attr(string $key, stdClass $obj){
        $monto = $this->limpia_monto(monto: $obj->$key);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar monto', data: $monto);
        }
        $obj->$key = $monto;
        return $obj;
    }

    private function limpia_attr_existente(string $key, stdClass $obj){
        if(isset($obj->$key)){

            $obj = $this->limpia_monto_attr(key: $key, obj: $obj);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al limpiar objeto', data: $obj);
            }
        }
        return $obj;
    }



    private function setea_attr(array $keys, array $keys_especial, DOMElement $nodo,
                                string $nodo_key, xml $xml): DOMElement
    {
        foreach ($keys as $key){

            $key_nodo_xml = $this->key_nodo_xml(key: $key,keys_especial: $keys_especial);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al setear $key_nodo_xml'.$key, data: $key_nodo_xml);
            }

            $nodo->setAttribute($key_nodo_xml, $xml->cfdi->$nodo_key->$key);
        }

        return $nodo;
    }
}
