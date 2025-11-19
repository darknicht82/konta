<?php
/**
 * Controlador de Ventas -> Cuentas por Cobrar
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class cuentasxcobrar extends controller
{
    //Filtros
    public $query;
    public $tipo;
    public $desde;
    public $hasta;
    public $nom_cliente;
    public $idcliente;
    public $idempresa;
    //modelos
    public $clientes;
    public $trans_cobros;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Cuentas por Cobrar', 'Ventas', 'Cobros', true, false, 'bi bi-coin');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        $this->access_crear = $this->user->have_access_to('crear_cobro');
        
        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        }

        //Busqueda
        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->clientes     = new clientes();
        $this->trans_cobros = new trans_cobros();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
        $this->cantidad = 0;
        $this->filtros  = '';

        $this->offset = 0;
        if (isset($_REQUEST['offset'])) {
            $this->offset = $_REQUEST['offset'];
        }

        $this->query = '';
        if (isset($_REQUEST['query']) && $_REQUEST['query'] != '') {
            $this->query = $_REQUEST['query'];
            $this->filtros .= '&query=' . $this->query;
        }

        $this->estado = 1;
        if (isset($_REQUEST['estado'])) {
            $this->estado = $_REQUEST['estado'];
            $this->filtros .= '&estado=' . $this->estado;
        }

        $this->desde = '';
        if (isset($_REQUEST['desde']) && $_REQUEST['desde'] != '') {
            $this->desde = $_REQUEST['desde'];
            $this->filtros .= '&desde=' . $this->desde;
        }

        $this->hasta = '';
        if (isset($_REQUEST['hasta']) && $_REQUEST['hasta'] != '') {
            $this->hasta = $_REQUEST['hasta'];
            $this->filtros .= '&hasta=' . $this->hasta;
        }

        $this->nom_cliente = '';
        $this->idcliente   = '';
        if (isset($_REQUEST['idcliente']) && $_REQUEST['idcliente'] != '') {
            $cliente = $this->clientes->get($_REQUEST['idcliente']);
            if ($cliente) {
                $this->idcliente   = $cliente->idcliente;
                $this->nom_cliente = $cliente->identificacion . " - " . $cliente->razonsocial;
                $this->filtros .= '&idcliente=' . $this->idcliente;
            }
        }

    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->trans_cobros->getSaldosFacturas($this->idempresa, $this->query, $this->idcliente, $this->estado, $this->desde, $this->hasta, $this->offset);
        } else {
            $this->cantidad = $this->trans_cobros->getSaldosFacturas($this->idempresa, $this->query, $this->idcliente, $this->estado, $this->desde, $this->hasta, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }

    private function buscar_cliente()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_clientes($this->idempresa, $_GET['buscar_cliente']);

        echo json_encode($result);
        exit;
    }
}
