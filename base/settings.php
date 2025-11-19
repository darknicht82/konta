<?php
/**
 * Class to manage settings.
 */
class settings
{
    /**
     * Timezones list with GMT offset
     * 
     * @return array
     * @link http://stackoverflow.com/a/9328760
     */
    public function get_timezone_list()
    {
        $zones_array = [];
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone'] = $zone;
            $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }

        return $zones_array;
    }

    public function new_codigo_options()
    {
        return array(
            'eneboo' => 'Compatible con Eneboo',
            'new' => 'TIPO + EJERCICIO + ' . strtoupper(JG_SERIE) . ' + NÚMERO',
            '0-NUM' => 'Número continuo (con 0s)',
            'NUM' => 'Número continuo',
            'SERIE-YY-0-NUM' => strtoupper(JG_SERIE) . ' + AÑO (2 díg.) + NÚMERO (con 0s)',
            'SERIE-YY-0-NUM-CORTO' => strtoupper(JG_SERIE) . ' + AÑO (2 díg.) + NÚMERO (mín. 4 car.)'
        );
    }

    /**
     * Lista de opciones para NF0
     * @return integer[]
     */
    public function nf0()
    {
        return array(0, 1, 2, 3, 4, 5);
    }

    /**
     * Lista de opciones para NF1
     * @return array
     */
    public function nf1()
    {
        return array(
            ',' => 'coma',
            '.' => 'punto',
            ' ' => '(espacio en blanco)'
        );
    }

    public function reset()
    {
        if (file_exists(JG_FOLDER . '/tmp/' . JG_TMP_NAME . 'config2.ini')) {
            return unlink(JG_FOLDER . '/tmp/' . JG_TMP_NAME . 'config2.ini');
        }

        return true;
    }

    public function save()
    {
        $file = fopen(JG_FOLDER . '/tmp/' . JG_TMP_NAME . 'config2.ini', 'w');
        if ($file) {
            foreach ($GLOBALS['config2'] as $i => $value) {
                $saveValue = is_numeric($value) ? $value : "'" . $value . "'";
                fwrite($file, $i . " = " . $saveValue . ";\n");
            }

            fclose($file);
            return true;
        }

        return false;
    }

    /**
     * Devuelve la lista de elementos a traducir
     * @return array
     */
    public function traducciones()
    {
        $clist = [];
        $include = array(
            'factura', 'facturas', 'factura_simplificada', 'factura_rectificativa',
            'albaran', 'albaranes', 'pedido', 'pedidos', 'presupuesto', 'presupuestos',
            'provincia', 'apartado', 'cifnif', 'iva', 'irpf', 'numero2', 'serie', 'series'
        );

        foreach ($GLOBALS['config2'] as $i => $value) {
            if (in_array($i, $include)) {
                $clist[] = array('nombre' => $i, 'valor' => $value);
            }
        }

        return $clist;
    }
}
