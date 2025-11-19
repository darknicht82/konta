<?php
/**
 * Controlador de Ventas -> Notas de Venta.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_notas_de_venta extends controller
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
        if (JG_ECAUTE == 1) {
            parent::__construct(__CLASS__, 'Prefacturas', 'Ventas', true, true, false, 'bi bi-cart3');
        } else {
            if (complemento_exists('manejo_notasventa')) {
                parent::__construct(__CLASS__, 'Lista N. de Venta', 'Ventas', 'Notas de Venta', true, false, 'bi bi-cart3');
            } else {
                parent::__construct(__CLASS__, 'Notas de Venta', 'Ventas', true, true, false, 'bi bi-cart3');
            }
        }
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_notaventa();
        } else if (isset($_GET['anular'])) {
            $this->anular_notaventa();
        }

        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->clientes         = new clientes();
        $this->facturas_cliente = new facturascli();
        $this->documentos       = new documentos();
    }

    private function init_filter()
    {
        $this->idempresa = $this->user->idempresa;
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

        $this->iddocumento = $this->documentos->get_by_codigo('02')->iddocumento;
        $this->filtros .= '&iddocumento=' . $this->iddocumento;

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

        $this->anuladas = isset($_REQUEST['anuladas']);
        if (isset($_REQUEST['anuladas'])) {
            $this->filtros .= '&anuladas';
        }

        $this->vigentes = isset($_REQUEST['vigentes']);
        if (isset($_REQUEST['vigentes'])) {
            $this->filtros .= '&vigentes';
        }
    }

    private function eliminar_notaventa()
    {
        $factura = $this->facturas_cliente->get($_GET['delete']);
        if ($factura) {
            if ($factura->delete()) {
                $this->new_message("Nota de Venta eliminada correctamente.");
            } else {
                $this->new_error_msg("Existió un error al eliminar la nota de Venta");
            }
        } else {
            $this->new_advice("Nota de Venta No Encontrada");
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->facturas_cliente->search_facturascli($this->idempresa, $this->query, $this->idcliente, $this->desde, $this->hasta, $this->iddocumento, $this->vigentes, $this->anuladas, false, false, false, $this->offset);
        } else {
            $this->cantidad = $this->facturas_cliente->search_facturascli($this->idempresa, $this->query, $this->idcliente, $this->desde, $this->hasta, $this->iddocumento, $this->vigentes, $this->anuladas, false, false, false, -1);
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

    private function anular_notaventa()
    {
        $factura = $this->facturas_cliente->get($_GET['anular']);
        if ($factura) {
            if (!$factura->anulado) {
                if ($factura->anular()) {
                    $this->new_message("Nota de Venta anulada correctamente.");
                } else {
                    $this->new_error_msg("Error al anular la Nota de Venta.");
                }
            } else {
                $this->new_advice("La Nota de Venta ya se encuentra anulada.");
            }
        } else {
            $this->new_advice("Nota de Venta No Encontrada");
        }
    }
}
