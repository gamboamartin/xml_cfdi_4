<?php
namespace gamboamartin\xml_cfdi_4;
use DOMElement;
use DOMException;
use gamboamartin\errores\errores;
use stdClass;
use Throwable;

class complementos{
    private errores  $error;
    private validacion $valida;
    public function __construct(){
        $this->error = new errores();
        $this->valida = new validacion();
    }

    /**
     * Ajusta elementos del xml para un complemento de pago
     * @version 0.4.0
     * @param stdClass $comprobante Objeto con datos del comprobante
     * @param xml $xml Objeto del cfdi en ejecucion
     * @return xml|array
     */
    public function aplica_complemento_cfdi_comprobante(stdClass $comprobante, xml $xml): xml|array
    {
        if($xml->cfdi->comprobante->tipo_de_comprobante === 'P'){
            $cfdi_comprobante_pago = (new dom_xml())->comprobante_pago(comprobante: $comprobante,xml:  $xml);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar dom pago', data: $cfdi_comprobante_pago);
            }
        }
        return $xml;
    }

    /**
     * Integra los elementos base de a cuenta a terceros
     * @param stdClass $comprobante datos del cfdi
     * @return stdClass
     * @version 1.38.0
     */
    final public function comprobante_a_cuenta_terceros(stdClass $comprobante): stdClass
    {
        $comprobante->metodo_pago = 'PUE';
        $comprobante->tipo_de_comprobante = 'I';
        $comprobante->exportacion = '01';
        return $comprobante;
    }

    /**
     * Genera la base del complemento del nodo comprobante de nomina
     * @param stdClass $comprobante Comprobante base nomina
     * @return stdClass
     * @version 1.8.0
     *
     */
    public function comprobante_complemento_nomina(stdClass $comprobante): stdClass
    {
        $comprobante->tipo_de_comprobante = 'N';
        $comprobante->moneda = 'MXN';
        $comprobante->exportacion = '01';
        return $comprobante;
    }


    /**
     * Se precarga la info base de un complemento de pago
     * @version 0.9.0
     * @param stdClass $comprobante
     * @return stdClass
     */
    public function comprobante_complemento_pago(stdClass $comprobante): stdClass
    {
        $comprobante->tipo_de_comprobante = 'P';
        $comprobante->moneda = 'XXX';
        $comprobante->total = '0';
        $comprobante->exportacion = '01';
        $comprobante->sub_total = '0';
        return $comprobante;
    }

    public function comprobante_nota_credito(stdClass $comprobante): stdClass
    {
        $comprobante->metodo_pago = 'PUE';
        $comprobante->tipo_de_comprobante = 'E';
        $comprobante->exportacion = '01';
        return $comprobante;
    }

    private function conceptos_complemento_nomina(float $descuento, float $valor_unitario): array
    {
        $conceptos = array();
        $conceptos[0] = new stdClass();
        $conceptos[0]->clave_prod_serv = '84111505';
        $conceptos[0]->cantidad = '1';
        $conceptos[0]->clave_unidad = 'ACT';
        $conceptos[0]->descripcion = 'Pago de nómina';
        $conceptos[0]->valor_unitario = number_format((float)$valor_unitario, 2, '.', '');
        $conceptos[0]->importe = number_format((float)$valor_unitario, 2, '.', '');
        $conceptos[0]->objeto_imp = '01';
        $conceptos[0]->descuento = number_format((float)$descuento, 2, '.', '');;
        $conceptos[0]->impuestos = array();
        
        return $conceptos;
    }

    private function conceptos_complemento_nomina_haberes(float $valor_unitario): array
    {
        $conceptos = array();
        $conceptos[0] = new stdClass();
        $conceptos[0]->clave_prod_serv = '84111505';
        $conceptos[0]->cantidad = '1';
        $conceptos[0]->clave_unidad = 'ACT';
        $conceptos[0]->descripcion = 'Pago de nómina';
        $conceptos[0]->valor_unitario = number_format((float)$valor_unitario, 2, '.', '');
        $conceptos[0]->importe = number_format((float)$valor_unitario, 2, '.', '');
        $conceptos[0]->objeto_imp = '01';
        $conceptos[0]->impuestos = array();

        return $conceptos;
    }


    private function conceptos_complemento_pago(): array
    {
        $conceptos = array();
        $conceptos[0] = new stdClass();
        $conceptos[0]->clave_prod_serv = '84111506';
        $conceptos[0]->cantidad = '1';
        $conceptos[0]->clave_unidad = 'ACT';
        $conceptos[0]->descripcion = 'Pago';
        $conceptos[0]->valor_unitario = '0';
        $conceptos[0]->importe = '0';
        $conceptos[0]->objeto_imp = '01';
        $conceptos[0]->impuestos = array();
        return $conceptos;
    }

    public function conceptos_complemento_nomina_dom(
        float $descuento, xml $xml, float $valor_unitario): bool|array|string
    {
        $conceptos = $this->conceptos_complemento_nomina(descuento:  $descuento,valor_unitario: $valor_unitario);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener conceptos', data: $conceptos);
        }

        $dom = $xml->cfdi_conceptos(conceptos: $conceptos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }
        return $dom;
    }

    public function conceptos_complemento_nomina_dom_haberes(xml $xml, float $valor_unitario): bool|array|string
    {
        $conceptos = $this->conceptos_complemento_nomina_haberes(valor_unitario: $valor_unitario);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener conceptos', data: $conceptos);
        }

        $dom = $xml->cfdi_conceptos(conceptos: $conceptos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }
        return $dom;
    }

    public function conceptos_complemento_pago_dom(xml $xml): bool|array|string
    {
        $conceptos = $this->conceptos_complemento_pago();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener conceptos', data: $conceptos);
        }

        $dom = $xml->cfdi_conceptos(conceptos: $conceptos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar receptor', data: $dom);
        }
        return $dom;
    }


    public function nodo_complemento(xml $xml): bool|DOMElement|array
    {
        try {
            $nodo_complemento = $xml->dom->createElement('cfdi:Complemento');
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al crear el elemento cfdi:Complemento', data: $e);
        }
        $xml->xml->appendChild($nodo_complemento);
        return $nodo_complemento;
    }




}
