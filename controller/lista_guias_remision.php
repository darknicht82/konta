<?php
/**
 * Controlador de Ventas -> Guias.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_guias_remision extends controller
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
    public $guias_cliente;
    public $documentos;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Guias de Remisión', 'Ventas', true, true, false, 'bi bi-truck');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        $this->access_crear = $this->user->have_access_to('crear_guia');
        
        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_guia();
        } else if (isset($_GET['anular'])) {
            $this->anular_guia();
        }

        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->clientes = new clientes();
        $this->guias    = new guiascli();
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
        $this->autorizadas = isset($_REQUEST['autorizadas']);
        if (isset($_REQUEST['autorizadas'])) {
            $this->filtros .= '&autorizadas';
        }
        $this->sinautorizar = isset($_REQUEST['sinautorizar']);
        if (isset($_REQUEST['sinautorizar'])) {
            $this->filtros .= '&sinautorizar';
        }
    }

    private function eliminar_guia()
    {
        $guia = $this->guias->get($_GET['delete']);
        if ($guia) {
            if ($guia->delete()) {
                $this->new_message("Factura eliminada correctamente.");
            } else {
                $this->new_error_msg("Existió un error al eliminar la guia");
            }
        } else {
            $this->new_advice("Factura No Encontrada");
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->guias->search_guiascli($this->idempresa, $this->query, $this->idcliente, $this->desde, $this->hasta, $this->anuladas, $this->autorizadas, $this->sinautorizar, $this->offset);
        } else {
            $this->cantidad = $this->guias->search_guiascli($this->idempresa, $this->query, $this->idcliente, $this->desde, $this->hasta, $this->anuladas, $this->autorizadas, $this->sinautorizar, -1);
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

    private function anular_guia()
    {
        $guia = $this->guias->get($_GET['anular']);
        if ($guia) {
            if (!$guia->anulado) {
                if ($guia->anular()) {
                    $this->new_message("Guía de Remisión anulada correctamente.");
                } else {
                    $this->new_error_msg("Error al anular la Guía de Remisión.");
                }
            } else {
                $this->new_advice("La Guía de Remisión ya se encuentra anulada.");
            }
        } else {
            $this->new_advice("Documento No Encontrado");
        }
    }
}
