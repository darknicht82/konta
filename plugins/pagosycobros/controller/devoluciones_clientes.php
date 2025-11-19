<?php
/**
 * Controlador de Ventas -> Anticipos -> Devoluciones
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class devoluciones_clientes extends controller
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
    public $trans_pagos;
    public $formaspago;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Devoluciones', 'Ventas', 'Anticipos', true, false, 'bi bi-cash-coin');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();
        //Tiene accesos para crear
        $this->access_crear = $this->user->have_access_to('crear_devolucion_cliente');
        //Tiene accesos para imprimir
        $this->impresion = $this->user->have_access_to('impresion_ventas');
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_devolucion();
        }

        //Busqueda
        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->clientes               = new clientes();
        $this->trans_pagos            = new trans_pagos();
        $this->formaspago             = new formaspago();
        $this->cab_devolucion_cliente = new cab_devolucion_cliente();
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

        $this->idformapago = '';
        if (isset($_REQUEST['idformapago'])) {
            $this->idformapago = $_REQUEST['idformapago'];
            $this->filtros .= '&idformapago=' . $this->idformapago;
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

        $this->anulados = isset($_REQUEST['anulados']);
        if (isset($_REQUEST['anulados'])) {
            $this->filtros .= '&anulados';
        }

        $this->scliente = isset($_REQUEST['scliente']);
        if (isset($_REQUEST['scliente'])) {
            $this->filtros .= '&scliente';
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->cab_devolucion_cliente->buscarDevolucionCli($this->idempresa, $this->query, $this->idcliente, $this->idformapago, $this->desde, $this->hasta, $this->scliente, $this->anulados, $this->offset);
        } else {
            $this->cantidad = $this->cab_devolucion_cliente->buscarDevolucionCli($this->idempresa, $this->query, $this->idcliente, $this->idformapago, $this->desde, $this->hasta, $this->scliente, $this->anulados, -1);
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

    private function eliminar_devolucion()
    {
        $pago = $this->cab_devolucion_cliente->get($_GET['delete']);
        if ($pago) {
            if ($pago->idempresa != $this->idempresa) {
                $this->new_advice('La Devolución no es valida para su empresa.');
                return;
            }
            if (!$this->allow_delete) {
                $this->new_advice('El usuario no tiene permiso para eliminar.');
                return;
            }

            if ($pago->delete()) {
                $this->new_message("Devolución eliminada correctamente.");
            }
        } else {
            $this->new_error_msg('Devolución no encontrada.');
        }
    }
}
