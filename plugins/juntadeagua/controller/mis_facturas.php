<?php
/**
 * Controlador de Ventas -> Facturas.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class mis_facturas extends controller
{
    //Filtros
    public $query;
    public $nom_cliente;
    public $idcliente;
    public $desde;
    public $hasta;
    public $idempresa;
    //modelos
    public $clientes;
    public $facturas_cliente;
    public $documentos;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Mis Facturas', 'Ventas', true, false, false, 'bi bi-list-columns-reverse');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        $this->impresion = $this->user->have_access_to('impresion_ventas');

        if ($this->idcliente != '') {
            //Busqueda
            $this->buscar();
            $this->buscar(-1);
        } else {
            $this->new_error_msg("El usuario no tiene Asociado un cliente, no se puede mostrar los resultados.");
        }
    }

    private function init_modelos()
    {
        $this->clientes     = new clientes();
        $this->trans_cobros = new trans_cobros();
        $this->medidores    = new medidores_cliente();
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
        if ($this->user->idcliente) {
            $cliente = $this->clientes->get($this->user->idcliente);
            if ($cliente) {
                $this->idcliente   = $cliente->idcliente;
                $this->nom_cliente = $cliente->identificacion . " - " . $cliente->razonsocial;
                $this->filtros .= '&idcliente=' . $this->idcliente;
            }
        }

        $this->idmedidor = '';
        if (isset($_REQUEST['idmedidor']) && $_REQUEST['idmedidor'] != '') {
            $this->idmedidor = $_REQUEST['idmedidor'];
            $this->filtros .= '&idmedidor=' . $this->idmedidor;
        }

    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->medidores->saldosClientesMedidor($this->idempresa, $this->query, $this->idcliente, $this->idmedidor, $this->estado, $this->desde, $this->hasta, $this->offset);
        } else {
            $this->cantidad = $this->medidores->saldosClientesMedidor($this->idempresa, $this->query, $this->idcliente, $this->idmedidor, $this->estado, $this->desde, $this->hasta, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }
}
