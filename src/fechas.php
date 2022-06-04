<?php
namespace gamboamartin\xml_cfdi_4;
use gamboamartin\errores\errores;
use stdClass;

class fechas{
    private validacion $valida;
    private errores $error;
    public function __construct(){
        $this->valida = new validacion();
        $this->error = new errores();
    }
    private function fecha_base(string $fecha, string $hora): array|string
    {
        $fecha_cfdi = $fecha;
        $es_fecha_base = $this->valida->valida_pattern(key:'fecha', txt: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha', data: $es_fecha_base);
        }
        if($es_fecha_base) {
            $fecha_cfdi = $fecha . 'T' . $hora;
        }
        return $fecha_cfdi;
    }

    public function fecha_cfdi(stdClass $comprobante): array|string
    {
        $hora  = date('H:i:s');
        if(!isset($comprobante->fecha) || trim($comprobante->fecha)===''){
            $fecha_cfdi = $this->fecha_cfdi_vacia();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al calcular fecha vacia', data: $fecha_cfdi);
            }
        }
        else{
            $fecha_cfdi = $this->fecha_cfdi_con_datos(fecha: $comprobante->fecha, hora: $hora);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al calcular fecha', data: $fecha_cfdi);
            }
        }
        return $fecha_cfdi;
    }

    private function fecha_cfdi_con_datos(string $fecha, string $hora): array|string
    {
        $fecha_cfdi = $this->fecha_base(fecha: $fecha, hora: $hora);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular fecha', data: $fecha_cfdi);
        }

        $fecha_cfdi = $this->fecha_hora_min_sec_esp(fecha: $fecha_cfdi);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular fecha', data: $fecha_cfdi);
        }

        $fecha_cfdi = $this->fecha_hora_min_sec_t(fecha: $fecha_cfdi);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular fecha', data: $fecha_cfdi);
        }
        return $fecha_cfdi;
    }

    /**
     * Obtiene la fecha en formato T actual
     * @return string
     */
    private function fecha_cfdi_vacia(): string
    {
        $hora  = date('H:i:s');
        $fecha_cfdi = date('Y-m-d');
        $fecha_cfdi .='T'.   $hora;
        return $fecha_cfdi;

    }

    private function fecha_hora_min_sec_esp(string $fecha): array|string
    {
        $fecha_cfdi = $fecha;
        $es_fecha_hora_min_sec_esp = $this->valida->valida_pattern(key:'fecha_hora_min_sec_esp', txt: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha', data: $es_fecha_hora_min_sec_esp);
        }
        if($es_fecha_hora_min_sec_esp) {
            $hora_ex = explode(' ', $fecha);
            $fecha_cfdi = $fecha . 'T' . $hora_ex[1];
        }
        return $fecha_cfdi;
    }
    private function fecha_hora_min_sec_t(string $fecha): array|string
    {
        $fecha_cfdi = $fecha;
        $es_fecha_hora_min_sec_t = $this->valida->valida_pattern(key:'fecha_hora_min_sec_t', txt: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha', data: $es_fecha_hora_min_sec_t);
        }
        if($es_fecha_hora_min_sec_t) {
            $hora_ex = explode('T', $fecha);
            $fecha_cfdi = $fecha . 'T' . $hora_ex[1];
        }
        return $fecha_cfdi;
    }
}
