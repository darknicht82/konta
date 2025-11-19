<?php
/**
 * Controlador de Administrador -> Empresas
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_cierres_caja extends controller
{
    //Filtros
    public $idempresa;
    public $desde;
    public $hasta;
    public $idestablecimiento;
    public $idcaja;
    public $nick;
    //modelos
    public $establecimiento;
    public $usuarios;
    public $cajas;
    public $cierres;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Cierres de Caja', 'Punto de Venta', true, true, false, 'bi bi-folder-fill');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();
        if (isset($_GET['reimprimir_cierre'])) {
            $this->reimprimir_cierre();
        }

        $this->ver_cierre = $this->user->have_access_to('ver_cierre_caja');
        $this->impresion = $this->user->have_access_to('impresion_tickets');
        $this->impresion_pdf = $this->user->have_access_to('impresion_puntoventa');
        //Busqueda
        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->establecimiento = new establecimiento();
        $this->usuarios        = new users();
        $this->cajas           = new cajas();
        $this->cierres         = new cierres();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
        $this->cantidad  = 0;
        $this->filtros   = '';

        $this->offset = 0;
        if (isset($_REQUEST['offset'])) {
            $this->offset = $_REQUEST['offset'];
        }

        $this->desde     = '';
        if (isset($_REQUEST['desde']) && $_REQUEST['desde'] != '') {
            $this->desde = $_REQUEST['desde'];
            $this->filtros .= '&desde=' . $this->desde;
        }

        $this->hasta = '';
        if (isset($_REQUEST['hasta']) && $_REQUEST['hasta'] != '') {
            $this->hasta = $_REQUEST['hasta'];
            $this->filtros .= '&hasta=' . $this->hasta;
        }

        $this->idestablecimiento = '';
        if (isset($_REQUEST['idestablecimiento']) && $_REQUEST['idestablecimiento'] != '') {
            $this->idestablecimiento = $_REQUEST['idestablecimiento'];
            $this->filtros .= '&idestablecimiento=' . $this->idestablecimiento;
        }

        $this->idcaja = '';
        if (isset($_REQUEST['idcaja']) && $_REQUEST['idcaja'] != '') {
            $this->idcaja = $_REQUEST['idcaja'];
            $this->filtros .= '&idcaja=' . $this->idcaja;
        }

        $this->nick = '';
        if (isset($_REQUEST['nick']) && $_REQUEST['nick'] != '') {
            $this->nick = $_REQUEST['nick'];
            $this->filtros .= '&nick=' . $this->nick;
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->cierres->search($this->idempresa, $this->desde, $this->hasta, $this->idestablecimiento, $this->idcaja, $this->nick, $this->offset);
        } else {
            $this->cantidad = $this->cierres->search($this->idempresa, $this->desde, $this->hasta, $this->idestablecimiento, $this->idcaja, $this->nick, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }

        }
    }

    private function reimprimir_cierre()
    {
        echo "<script>window.open('index.php?page=impresion_tickets&reimprimir&cierre=" . $_GET['reimprimir_cierre'] . "', '', 'width=300, height=200');</script>";
        header('Refresh: 0; URL=' . $this->url());
    }
}
