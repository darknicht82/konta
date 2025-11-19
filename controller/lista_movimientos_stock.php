<?php
/**
 * Controlador de Inventario -> Movimientos.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_movimientos_stock extends controller
{
    //Filtros
    public $query;
    public $desde;
    public $hasta;    
    public $idempresa;
    //modelos
    public $movimientos;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Movimientos', 'Inventarios', true, true, false, 'bi bi-arrow-down-up');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        $this->access_crear = $this->user->have_access_to('crear_movimiento');
        
        if (isset($_GET['delete'])) {
            $this->eliminar_movimientos();
        }

        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->movimientos = new movimientos();
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
            $this->filtros .= '&query=' . $this->query;
        }

        $this->hasta = '';
        if (isset($_REQUEST['hasta']) && $_REQUEST['hasta'] != '') {
            $this->hasta = $_REQUEST['hasta'];
            $this->filtros .= '&hasta=' . $this->hasta;
        }

        $this->tipo = '';
        if (isset($_REQUEST['tipo']) && $_REQUEST['tipo'] != '') {
            $this->tipo = $_REQUEST['tipo'];
            $this->filtros .= '&tipo=' . $this->tipo;
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->movimientos->search_movimientos($this->idempresa, $this->query, $this->tipo, $this->desde, $this->hasta, $this->offset);
        } else {
            $this->cantidad = $this->movimientos->search_movimientos($this->idempresa, $this->query, $this->tipo, $this->desde, $this->hasta, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }

    private function eliminar_movimientos()
    {
        $movimiento = $this->movimientos->get($_GET['delete']);
        if ($movimiento) {
            if ($movimiento->delete()) {
                $this->new_message("Movimiento eliminado correctamente.");
            } else {
                $this->new_error_msg("Existió un error al eliminar el Movimiento");
            }
        } else {
            $this->new_advice("Movimiento No Encontrado");
        }
    }
}