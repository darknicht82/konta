<?php
/**
 * Controlador de Inventario -> Articulos.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_unidades_medida extends controller
{
    //Filtros
    public $idempresa;
    //modelos
    public $unidades;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Unidades Med.', 'Inventarios', true, true, false, 'bi bi-speedometer2');
    }

    protected function private_core()
    {
        $this->unidades = new unidades_medida();
        if (isset($_POST['idunidad'])) {
            $this->modificar_unidad();
        } else if (isset($_POST['codigo'])) {
            $this->crear_unidad();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_unidad();
        }

        $this->buscar();
    }

    private function crear_unidad()
    {
        if (!$this->unidades->get_by_codigo($_POST['codigo'], $this->empresa->idempresa)) {
            $unidad                = new unidades_medida();
            $unidad->idempresa     = $this->empresa->idempresa;
            $unidad->codigo        = $_POST['codigo'];
            $unidad->nombre        = $_POST['nombre'];
            $unidad->fec_creacion  = date('Y-m-d');
            $unidad->nick_creacion = $this->user->nick;
            if ($unidad->save()) {
                $this->new_message("Unidad de Medida creada correctamente.");
            } else {
                $this->new_error_msg("No se pudo crear la unidad de medida, verifique los datos y vuelva a intentarlo.");
            }
        } else {
            $this->new_error_msg("El codigo ya se encuentra creado.");
        }
    }

    private function modificar_unidad()
    {
        $unidad = $this->unidades->get($_POST['idunidad']);
        if ($unidad) {
            $unidad->codigo = $_POST['codigo'];
            $unidad->nombre = $_POST['nombre'];
            $unidad->fec_modificacion  = date('Y-m-d');
            $unidad->nick_modificacion = $this->user->nick;

            if ($unidad->save()) {
                $this->new_message("Unidad de Medida modificada correctamente.");
            } else {
                $this->new_error_msg("No se pudo modificar la unidad de medida, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("La unidad de medida no se encuentra registrado.");
        }
    }

    private function eliminar_unidad()
    {
        $unidad = $this->unidades->get($_GET['delete']);
        if ($unidad) {
            if ($unidad->delete()) {
                $this->new_message("Unidad de Medida eliminada correctamente.");
            } else {
                $this->new_error_msg("No se puede eliminar la unidad de medida, debe estar utilizado en una transacción de compra.");
            }
        } else {
            $this->new_advice("Error al eliminar, la unidad de medida no se encuentra registrada o ya fue eliminada.");
        }
    }

    private function buscar()
    {
        $this->resultados = $this->unidades->all_by_idempresa($this->empresa->idempresa);
    }
}
