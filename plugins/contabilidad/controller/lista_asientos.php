<?php
/**
 * Controlador de Contabilidad -> Asientos.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_asientos extends controller
{
    //Filtros
    public $idempresa;
    //modelos
    public $asientos;
    //variables
    public $resultados;
    public $cantidad;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Asientos', 'Contabilidad', true, true, false, 'bi bi-list-columns-reverse');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        if (isset($_POST['new_nombre'])) {
            $this->crear_ejercicio();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_ejercicio();
        }

        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->asientos   = new asientos();
        $this->ejercicios = new ejercicios();
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

        $this->tipo = '';
        if (isset($_REQUEST['tipo']) && $_REQUEST['tipo'] != '') {
            $this->tipo = $_REQUEST['tipo'];
            $this->filtros .= '&tipo=' . $this->tipo;
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

        $this->idejercicio = '';
        if (isset($_REQUEST['idejercicio']) && $_REQUEST['idejercicio'] != '') {
            $this->idejercicio = $_REQUEST['idejercicio'];
            $this->filtros .= '&idejercicio=' . $this->idejercicio;
        }
    }

    private function crear_ejercicio()
    {
        if (!$this->asientos->get_by_codigo($this->idempresa, $_POST['new_nombre'])) {
            $ejercicio                = new asientos();
            $ejercicio->idempresa     = $this->idempresa;
            $ejercicio->nombre        = $_POST['new_nombre'];
            $ejercicio->fec_inicio    = $_POST['new_nombre'] . "-01-01";
            $ejercicio->fec_fin       = $_POST['new_nombre'] . "-12-31";
            $ejercicio->fec_creacion  = date('Y-m-d');
            $ejercicio->nick_creacion = $this->user->nick;

            if ($ejercicio->save()) {
                $this->new_message("Ejercicio creado correctamente.");
            } else {
                $this->new_error_msg("No se pudo crear el ejercicio, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El ejercicio " . $_POST['new_nombre'] . " ya se encuentra registrado.");
        }
    }

    private function eliminar_ejercicio()
    {
        $ejercicio = $this->asientos->get($_GET['delete']);
        if ($ejercicio) {
            if ($ejercicio->delete()) {
                $this->new_message("Ejercicio eliminado correctamente.");
            } else {
                $this->new_error_msg("No se puede eliminar el ejercicio.");
            }
        } else {
            $this->new_advice("Error al eliminar, el ejercicio no se encuentra registrado o ya fue eliminado.");
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->asientos->search_asientos($this->idempresa, $this->query, $this->tipo, $this->desde, $this->hasta, $this->idejercicio, $this->offset);
        } else {
            $this->cantidad = $this->asientos->search_asientos($this->idempresa, $this->query, $this->tipo, $this->desde, $this->hasta, $this->idejercicio, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }
}
