<?php
/**
 * Controlador de Compras -> Facturas.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_facturas_proveedor extends controller
{
    //Filtros
    public $query;
    public $nom_proveedor;
    public $idproveedor;
    public $desde;
    public $hasta;    
    public $idempresa;
    //modelos
    public $facturasprov;
    public $proveedores;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Facturas', 'Compras', true, true, false, 'bi bi-cart-dash-fill');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();
        
        $this->access_crear = $this->user->have_access_to('crear_compra');
        
        if (isset($_GET['buscar_proveedor'])) {
            $this->buscar_proveedor();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_factura();
        }

        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->proveedores = new proveedores();
        $this->facturas_proveedor = new facturasprov();
        $this->documentos = new documentos();
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

        $this->iddocumento = '';
        if (isset($_REQUEST['iddocumento']) && $_REQUEST['iddocumento'] != '') {
            $this->iddocumento = $_REQUEST['iddocumento'];
            $this->filtros .= '&iddocumento=' . $this->iddocumento;
        }

        $this->nom_proveedor = '';
        $this->idproveedor = '';
        if (isset($_REQUEST['idproveedor']) && $_REQUEST['idproveedor'] != '') {
            $proveedor = $this->proveedores->get($_REQUEST['idproveedor']);
            if ($proveedor) {
                $this->idproveedor = $proveedor->idproveedor;                
                $this->nom_proveedor = $proveedor->identificacion." - ".$proveedor->razonsocial;
                $this->filtros .= '&idproveedor=' . $this->idproveedor;              
            }
        }

        $this->anuladas = isset($_REQUEST['anuladas']);
        if (isset($_REQUEST['anuladas'])) {
            $this->filtros .= '&anuladas';
        }
        $this->saldosini = isset($_REQUEST['saldosini']);
        if (isset($_REQUEST['saldosini'])) {
            $this->filtros .= '&saldosini';
        }
        $this->autorizadas = isset($_REQUEST['autorizadas']);
        if (isset($_REQUEST['autorizadas'])) {
            $this->filtros .= '&autorizadas';
        }
        $this->sinautorizar = isset($_REQUEST['sinautorizar']);
        if (isset($_REQUEST['sinautorizar'])) {
            $this->filtros .= '&sinautorizar';
        }
    }

    private function eliminar_factura()
    {
        $factura = $this->facturas_proveedor->get($_GET['delete']);
        if ($factura) {
            if ($factura->delete()) {
                $this->new_message("Documento eliminado correctamente.");
            } else {
                $this->new_error_msg("Existió un error al eliminar el Documento");
            }
        } else {
            $this->new_advice("Documento No Encontrado");
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->facturas_proveedor->search_facturasprov($this->idempresa, $this->query, $this->idproveedor, $this->desde, $this->hasta, $this->iddocumento, false, $this->anuladas, $this->saldosini, $this->autorizadas, $this->sinautorizar, $this->offset);
        } else {
            $this->cantidad = $this->facturas_proveedor->search_facturasprov($this->idempresa, $this->query, $this->idproveedor, $this->desde, $this->hasta, $this->iddocumento, false, $this->anuladas, $this->saldosini, $this->autorizadas, $this->sinautorizar, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }

    private function buscar_proveedor()
    {
        $this->template = false;
        $result = array();
        $result = buscar_proveedores($this->idempresa, $_GET['buscar_proveedor']);

        echo json_encode($result);
        exit;
    }
}