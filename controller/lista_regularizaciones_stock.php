<?php
/**
 * Controlador de Inventario -> Regularizaciones.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_regularizaciones_stock extends controller
{
    //Filtros
    public $query;
    public $desde;
    public $hasta;
    public $idestablecimiento;
    public $idempresa;
    //modelos
    public $regularizaciones;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Regularizaciones', 'Inventarios', true, true, false, 'bi bi-file-diff');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        $this->access_crear = $this->user->have_access_to('crear_regularizacion');
        
        if (isset($_GET['delete'])) {
            $this->eliminar_regularizacion();
        }
        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->regularizaciones = new regularizaciones();
        $this->establecimiento  = new establecimiento();
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

        $this->query     = '';
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

        $this->idestablecimiento = '';
        if (isset($_REQUEST['idestablecimiento']) && $_REQUEST['idestablecimiento'] != '') {
            $this->idestablecimiento = $_REQUEST['idestablecimiento'];
            $this->filtros .= '&idestablecimiento=' . $this->idestablecimiento;
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->regularizaciones->search_regularizaciones($this->idempresa, $this->query, $this->idestablecimiento, $this->desde, $this->hasta, $this->offset);
        } else {
            $this->cantidad = $this->regularizaciones->search_regularizaciones($this->idempresa, $this->query, $this->idestablecimiento, $this->desde, $this->hasta, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }

    private function eliminar_regularizacion()
    {
        $regularizacion = $this->regularizaciones->get($_GET['delete']);
        if ($regularizacion) {
            if ($regularizacion->delete()) {
                $this->new_message("Regularización eliminada correctamente.");
            } else {
                $this->new_error_msg("Existió un error al eliminar la Regularización");
            }
        } else {
            $this->new_advice("Regularización No Encontrada");
        }
    }
}
