<?php
namespace gamboamartin\xml_cfdi_4;
class parser{

    public function concepto_importe(float $importe): float
    {
        return round($importe,4);
    }

    public function concepto_valor_unitario(float $valor_unitario): float
    {
        return round($valor_unitario,4);
    }
}
