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

    /**
     * Funcion que ajusta la fecha de un cfdi a formato T
     * @param string $fecha Fecha en formato YYYY-mm-dd
     * @param string $hora Hora de emision del cfdi
     * @return array|string
     * @version 1.17.0
     */
    private function fecha_base(string $fecha, string $hora): array|string
    {
        $fecha = trim($fecha);
        if($fecha === ''){
            $fix = 'Es necesario enviar la fecha con el siguiente formato YYYY-mm-dd donde YYYY Es un año valido ';
            $fix .= 'a 4 digitos ej 2020 mm corresponde al mes a dos digitos 01 = enero 11 = noviembre';
            $fix .= ' dd corresponde al dia a 2 digitos ejemplo 01 igual al primero 0 dia 12 o 31';
            $fix .= ' entonces fecha puede ser 2020-01-01 donde se representa primero de enero del año 2020';
            $fix .= ' o 2022-06-08 donde se representa el 8 de junio de 2022';
            return $this->error->error(mensaje: 'Error fecha esta vacia', data: $fecha, fix: $fix);
        }

        $fecha_cfdi = $fecha;
        $es_fecha_base = $this->valida->valida_pattern(key:'fecha', txt: $fecha);
        if(errores::$error){
            $fix = 'Es necesario enviar la fecha con el siguiente formato YYYY-mm-dd donde YYYY Es un año valido ';
            $fix .= 'a 4 digitos ej 2020 mm corresponde al mes a dos digitos 01 = enero 11 = noviembre';
            $fix .= ' dd corresponde al dia a 2 digitos ejemplo 01 igual al primero 0 dia 12 o 31';
            $fix .= ' entonces fecha puede ser 2020-01-01 donde se representa primero de enero del año 2020';
            $fix .= ' o 2022-06-08 donde se representa el 8 de junio de 2022';
            return $this->error->error(mensaje: 'Error al validar fecha', data: $es_fecha_base, fix: $fix);
        }
        if($es_fecha_base) {
            $fecha_cfdi = $fecha . 'T' . $hora;
        }




        return $fecha_cfdi;
    }

    /**
     * Obtiene la fecha para un cfdi con el formato especifico
     * @param stdClass $comprobante Datos para nodo comprobante
     * @return array|string
     *
     */
    final public function fecha_cfdi(stdClass $comprobante): array|string
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

    /**
     * @param string $fecha Fecha a verificar
     * @param string $hora Hora a integrar
     * @return array|string
     * @version 2.49.0
     */
    PUBLIC function fecha_cfdi_con_datos(string $fecha, string $hora): array|string
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
     * @version 0.9.0
     * @return string
     */
    private function fecha_cfdi_vacia(): string
    {
        $hora  = date('H:i:s');
        $fecha_cfdi = date('Y-m-d');
        $fecha_cfdi .='T'.   $hora;
        return $fecha_cfdi;

    }

    /**
     * Integra una fecha con espacios con formato T
     * @param string $fecha Fecha en ejecucion
     * @return array|string
     * @version 1.47.0
     */
    private function fecha_hora_min_sec_esp(string $fecha): array|string
    {
        $fecha = trim($fecha);
        if($fecha === ''){
            return $this->error->error(mensaje: 'Error fecha vacia', data: $fecha);
        }
        $fecha_cfdi = $fecha;
        $es_fecha_hora_min_sec_esp = $this->valida->valida_pattern(key:'fecha_hora_min_sec_esp', txt: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha', data: $es_fecha_hora_min_sec_esp);
        }
        if($es_fecha_hora_min_sec_esp) {
            $hora_ex = explode(' ', $fecha);
            $fecha_cfdi = $hora_ex[0] . 'T' . $hora_ex[1];
        }
        return $fecha_cfdi;
    }

    /**
     * Integra la fecha en formato T
     * @param string $fecha Fecha a integrar
     * @return array|string
     * @version 2.11.0
     */
    private function fecha_hora_min_sec_t(string $fecha): array|string
    {
        $fecha = trim($fecha);
        if($fecha === ''){
            return $this->error->error(mensaje: 'Error fecha esta vacia', data: $fecha);
        }
        $fecha_cfdi = $fecha;
        $es_fecha_hora_min_sec_esp = $this->valida->valida_pattern(key:'fecha_hora_min_sec_esp', txt: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha', data: $es_fecha_hora_min_sec_esp);
        }
        if($es_fecha_hora_min_sec_esp) {
            $hora_ex = explode(' ', $fecha);
            $fecha_cfdi = $hora_ex[0] . 'T' . $hora_ex[1];
        }

        $es_fecha = $this->valida->valida_pattern(key:'fecha', txt: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha', data: $es_fecha_hora_min_sec_esp);
        }
        if($es_fecha) {

            $fecha_cfdi = $fecha . 'T' . date('H:i:s');
        }

        $es_fecha_hora_min_sec_t = $this->valida->valida_pattern(key:'fecha_hora_min_sec_t', txt: $fecha_cfdi);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha', data: $es_fecha_hora_min_sec_t);
        }
        if(!$es_fecha_hora_min_sec_t){
            return $this->error->error(mensaje: 'Error al validar fecha no tiene el formato especifico',
                data: $fecha);
        }
        return $fecha_cfdi;
    }
}
