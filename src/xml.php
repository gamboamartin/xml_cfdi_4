<?php
namespace gamboamartin\xml_cfdi_4;
date_default_timezone_set('America/Mexico_City');

use DOMDocument;
use DOMException;
use DOMNode;
use gamboamartin\errores\errores;

use phpDocumentor\Reflection\Types\This;
use stdClass;

class xml{
    public DOMDocument $dom;
    public stdClass $cfdi;
    public DOMNode  $xml;
    private validacion $valida;
    private errores $error;

    /**
     * @throws DOMException
     */
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
        $this->cfdi->comprobante->sub_total = "0";
        $this->cfdi->comprobante->lugar_expedicion = "";
        $this->cfdi->comprobante->fecha = "";
        $this->cfdi->comprobante->folio = "";
        $this->cfdi->comprobante->version = "4.0";
        $this->cfdi->comprobante->namespace = new stdClass();
        $this->cfdi->comprobante->namespace->w3 = 'http://www.w3.org/2000/xmlns/';



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
}
