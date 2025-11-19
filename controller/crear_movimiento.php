<?php
/**
 * Controlador de Movimientos -> Nuevo.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class crear_movimiento extends controller
{
    public $idempresa;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Nuevo Movimiento', 'Inventario', false, false, false, 'bi bi-cart-dash-fill');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        if (isset($_POST['tipo'])) {
            $this->nuevo_movimiento_stock();
        }
    }

    private function init_modelos()
    {
        $this->movimientos     = new movimientos();
        $this->establecimiento = new establecimiento();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
    }

    private function nuevo_movimiento_stock()
    {
        $mov                    = new movimientos();
        $mov->idempresa         = $this->empresa->idempresa;
        $mov->idestablecimiento = $_POST['idestablecimiento'];
        $mov->tipo              = $_POST['tipo'];
        $mov->fec_emision       = $_POST['fec_emision'];
        $mov->hora_emision      = date('H:i:s');
        if ($_POST['observaciones'] != '') {
            $mov->observaciones = $_POST['observaciones'];
        }
        $mov->fec_creacion  = date('Y-m-d');
        $mov->nick_creacion = $this->user->nick;

        if ($mov->save()) {
            header('location: ' . $mov->url());
        } else {
            $this->new_error_msg("Error al crear el Movimiento de Stock, verifique los datos y vuelva a intentarlo.");
        }
    }
}
