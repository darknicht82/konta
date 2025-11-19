<?php
/**
 * Controlador de Punto de Venta -> Ver Cierre de Caja.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_cierre_caja extends controller
{
    //variables
    public $cierre;
    public $allow_delete;
    public $allow_modify;
    //models
    public $cierres;
    public $trans_cobros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ver Cierre de Caja', 'Punto de Venta', false, false);
    }

    protected function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->ver_articulo_access = $this->user->have_access_to('ver_articulo');

        $this->init_modelos();
        $this->init_filter();

        $this->cierre = false;
        if (isset($_GET['idcierre'])) {
            $this->cierre = $this->cierres->get($_GET['idcierre']);
            if (!$this->cierre) {
                $this->new_advice("No se encuentra el cierre seleccionado.");
                return;
            } else if ($this->cierre->idempresa != $this->idempresa) {
                $this->cierre = false;
                $this->new_advice("El cierre no esta disponible para su empresa.");
                return;
            }
        }
    }

    private function init_modelos()
    {
        $this->cierres      = new cierres();
        $this->trans_cobros = new trans_cobros();
        $this->mov_cajas    = new mov_cajas();
        if (complemento_exists('cuadre_producto')) {
            $this->articulos_cuadre = new articulos_cuadre();
        }
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;

        $this->mostrar = 'cuadre';
        if (isset($_GET['mostrar'])) {
            $this->mostrar = $_GET['mostrar'];
        }

    }

    public function getCobros()
    {
        $i            = 0;
        $credito      = 0;
        $cobros       = 0;
        $trans_cobros = $this->trans_cobros->getFormasCaja($this->idempresa, $this->cierre->idcierre);
        foreach ($trans_cobros as $key => $c) {
            if ($this->cierres->str2bool($c['escredito'])) {
                $credito += floatval($c['total']);
                $i = $key;
            } else {
                $cobros += floatval($c['cobros']);
            }
        }

        $cred = round($credito - $cobros, 2);
        if ($cred > 0) {
            $trans_cobros[$i]['cobros'] = $cred;
        }

        return $trans_cobros;
    }
}
