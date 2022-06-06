<?php
namespace gamboamartin\xml_cfdi_4;
date_default_timezone_set('America/Mexico_City');

use DOMDocument;
use DOMException;
use DOMNode;
use gamboamartin\errores\errores;

use stdClass;

class xml{
    public DOMDocument $dom;
    public stdClass $cfdi;
    public DOMNode  $xml;
    private validacion $valida;
    private errores $error;


    public function __construct(){
        $this->valida = new validacion();
        $this->error = new errores();
        $this->cfdi = new stdClass();
        $this->cfdi->comprobante = new stdClass();
        $this->cfdi->comprobante->xmlns_xsi = "http://www.w3.org/2001/XMLSchema-instance";
        $this->cfdi->comprobante->xmlns_pago20 = "http://www.sat.gob.mx/Pagos20";
        $this->cfdi->comprobante->xmlns_cfdi = "http://www.sat.gob.mx/cfd/4";
        $this->cfdi->comprobante->moneda = "";
        $this->cfdi->comprobante->total = "0";
        $this->cfdi->comprobante->xsi_schemaLocation = "http://www.sat.gob.mx/cfd/4 ";
        $this->cfdi->comprobante->xsi_schemaLocation .= "http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd ";
        $this->cfdi->comprobante->exportacion = "";
        $this->cfdi->comprobante->tipo_de_comprobante = "";
        $this->cfdi->comprobante->sub_total = 0;
        $this->cfdi->comprobante->lugar_expedicion = "";
        $this->cfdi->comprobante->fecha = "";
        $this->cfdi->comprobante->folio = "";
        $this->cfdi->comprobante->version = "4.0";
        $this->cfdi->comprobante->namespace = new stdClass();
        $this->cfdi->comprobante->namespace->w3 = 'http://www.w3.org/2000/xmlns/';

        $this->cfdi->emisor = new stdClass();
        $this->cfdi->receptor = new stdClass();
        $this->cfdi->conceptos = array();


        $this->dom = new DOMDocument('1.0', 'utf-8');

    }

    public function cfdi_comprobante(stdClass $comprobante): bool|string|array
    {


        $keys = array('tipo_de_comprobante','moneda','total', 'exportacion','sub_total','lugar_expedicion',
            'folio');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar comprobante', data: $valida);
        }

        $fecha_cfdi = (new fechas())->fecha_cfdi(comprobante: $comprobante);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular fecha', data: $fecha_cfdi);
        }

        $this->cfdi->comprobante->fecha = $fecha_cfdi;
        $comprobante->fecha = $fecha_cfdi;


        $comprobante_base = (new dom_xml())->comprobante(comprobante: $comprobante,xml:  $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar cfdi comprobante', data: $comprobante_base);
        }

        $complemento = (new complementos())->aplica_complemento_cfdi_comprobante(comprobante: $comprobante, xml: $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar complementos', data: $complemento);
        }

        return $this->dom->saveXML();
    }

    public function cfdi_conceptos(array $conceptos): bool|string|array
    {
        if(!isset($this->xml)){
            return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
        }
        if(count($conceptos) === 0){
            return $this->error->error(mensaje: 'Error los conceptos no pueden ir vacios', data: $conceptos);
        }

        $nodo = $this->dom->createElement('cfdi:Conceptos');
        $this->xml->appendChild($nodo);

        foreach ($conceptos as $concepto){
            $this->cfdi->conceptos[] = new stdClass();
            $valida = $this->valida->valida_concepto(concepto: $concepto);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar $concepto', data: $valida);
            }
            $valida = $this->valida->valida_data_concepto(concepto: $concepto);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar $concepto', data: $valida);
            }

            $elemento_concepto = $this->dom->createElement('cfdi:Concepto');
            $nodo->appendChild($elemento_concepto);

            $elemento_concepto->setAttribute('ClaveProdServ', $concepto->clave_prod_serv);
            $elemento_concepto->setAttribute('NoIdentificacion', $concepto->no_identificacion);
            $elemento_concepto->setAttribute('Cantidad', $concepto->cantidad);
            $elemento_concepto->setAttribute('ClaveUnidad', $concepto->clave_unidad);
            $elemento_concepto->setAttribute('Descripcion', $concepto->descripcion);
            $elemento_concepto->setAttribute('ValorUnitario', $concepto->valor_unitario);
            $elemento_concepto->setAttribute('Importe', $concepto->importe);
            $elemento_concepto->setAttribute('ObjetoImp', $concepto->objeto_imp);



        }


        return $this->dom->saveXML();
    }

    /**
     */
    public function cfdi_emisor(stdClass $emisor): bool|string|array
    {
        $keys = array('rfc','nombre','regimen_fiscal');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $emisor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $emisor', data: $valida);
        }

        if(!isset($this->xml)){
            return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
        }

        $data_nodo = (new dom_xml())->nodo(keys: $keys, local_name: 'cfdi:Emisor', nodo_key: 'emisor',
            object:  $emisor,xml:  $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear $emisor', data: $data_nodo);
        }


        return $this->dom->saveXML();
    }

    public function cfdi_receptor(stdClass $receptor): bool|string|array
    {
        $keys = array('rfc','nombre','domicilio_fiscal_receptor','regimen_fiscal_receptor','uso_cfdi');
        $valida = $this->valida->valida_existencia_keys(keys: $keys, registro: $receptor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $receptor', data: $valida);
        }

        if(!isset($this->xml)){
            return $this->error->error(mensaje: 'Error no esta inicializado el xml', data: $this);
        }

        $data_nodo = (new dom_xml())->nodo(keys: $keys, local_name: 'cfdi:Receptor', nodo_key: 'receptor',object:  $receptor,xml:  $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al setear $receptor', data: $data_nodo);
        }

        return $this->dom->saveXML();
    }
}
