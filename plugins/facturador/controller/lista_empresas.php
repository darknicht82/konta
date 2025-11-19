<?php
/**
 * Controlador de Administrador -> Empresas
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_empresas extends controller
{
    //Filtros
    public $query;
    public $b_grupo;
    public $b_marca;
    public $b_tipo;
    public $idempresa;
    //modelos
    public $empresas0;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Empresas', 'Administrador', true, true, false, 'bi bi-bank2');
    }

    protected function private_core()
    {
        $this->init_filter();

        $this->empresas0 = new empresa();
        $this->historial = new historial_planes();

        if (isset($_POST['idempresa'])) {
            $this->buscar_empresa();
        } else if (isset($_POST['idempresa_plan'])) {
            $this->actualizar_plan();
        } else if (isset($_POST['historial_p'])) {
            $this->historial_plan();
        }
        //Busqueda
        $this->buscar();
        $this->buscar(-1);
    }

    private function init_filter()
    {
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

        $this->vencimiento = isset($_REQUEST['vencimiento']);
        if (isset($_REQUEST['vencimiento'])) {
            $this->filtros .= '&vencimiento';
        }

    }

    private function buscar_empresa()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Empresa No Encontrada.');
        $empresa        = $this->empresas0->get($_POST['idempresa']);
        if ($empresa) {
            $result = array('error' => 'F', 'msj' => '', 'empresa' => $empresa);
        }
        echo json_encode($result);
        exit;
    }

    private function actualizar_plan()
    {
        $empresa = $this->empresas0->get($_POST['idempresa_plan']);
        if ($empresa) {
            $empresa->fec_inicio_plan    = $_POST['fec_inicio_plan'];
            $empresa->fec_caducidad_plan = $_POST['fec_caducidad_plan'];
            $empresa->numusers           = $_POST['numusers'];
            $empresa->numdocs            = $_POST['numdocs'];
            $empresa->plan_basico            = isset($_POST['plan_basico']);
            if ($empresa->save()) {
                // si se guarda genero el historial
                $historial                     = new historial_planes();
                $historial->idempresa          = $empresa->idempresa;
                $historial->fec_inicio_plan    = $empresa->fec_inicio_plan;
                $historial->fec_caducidad_plan = $empresa->fec_caducidad_plan;
                $historial->numusers           = $empresa->numusers;
                $historial->numdocs            = $empresa->numdocs;
                $historial->plan_basico        = $empresa->plan_basico;
                $historial->nick_creacion      = $this->user->nick;
                $historial->fec_creacion       = date('Y-m-d');
                if ($historial->save()) {
                    $this->new_message("Plan de la Empresa " . $empresa->razonsocial . " actualizado correctamente.");
                } else {
                    $this->new_advice("Plan actualizado en la empresa correctamente, pero se tuvo un error al guardar el historial del plan.");
                }
            } else {
                $this->new_error_msg("Error al actualizar el plan de la empresa.");
            }
        } else {
            $this->new_error_msg("Empresa No encontrada.");
        }
    }

    private function historial_plan()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Sin historial.');
        $historial      = $this->historial->all_by_idempresa($_POST['historial_p']);
        if ($historial) {
            $result = array('error' => 'F', 'msj' => '', 'historial' => $historial);
        }
        echo json_encode($result);
        exit;
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->empresas0->search_empresa($this->query, $this->desde, $this->hasta, $this->vencimiento, $this->offset);
        } else {
            $this->cantidad = $this->empresas0->search_empresa($this->query, $this->desde, $this->hasta, $this->vencimiento, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }
}
