<?php
/**
 * Controlador de Compras -> Cuentas por Pagar
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class cuentasxpagar extends controller
{
    //Filtros
    public $query;
    public $tipo;
    public $desde;
    public $hasta;
    public $nom_proveedor;
    public $idproveedor;
    public $idempresa;
    //modelos
    public $proveedores;
    public $trans_pagos;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Cuentas por Pagar', 'Compras', 'Pagos', true, false, 'bi bi-coin');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        if (isset($_GET['buscar_proveedor'])) {
            $this->buscar_proveedor();
        }

        //Busqueda
        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->proveedores     = new proveedores();
        $this->trans_pagos = new trans_pagos();
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

        $this->nom_proveedor = '';
        $this->idproveedor   = '';
        if (isset($_REQUEST['idproveedor']) && $_REQUEST['idproveedor'] != '') {
            $cliente = $this->proveedores->get($_REQUEST['idproveedor']);
            if ($cliente) {
                $this->idproveedor   = $cliente->idproveedor;
                $this->nom_proveedor = $cliente->identificacion . " - " . $cliente->razonsocial;
                $this->filtros .= '&idproveedor=' . $this->idproveedor;
            }
        }

    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->trans_pagos->getSaldosFacturas($this->idempresa, $this->query, $this->idproveedor, $this->estado, $this->desde, $this->hasta, $this->offset);
        } else {
            $this->cantidad = $this->trans_pagos->getSaldosFacturas($this->idempresa, $this->query, $this->idproveedor, $this->estado, $this->desde, $this->hasta, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }

    private function buscar_proveedor()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_proveedores($this->idempresa, $_GET['buscar_proveedor']);

        echo json_encode($result);
        exit;
    }
}
