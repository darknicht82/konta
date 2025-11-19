<?php
/**
 * Controlador de Contabilidad -> Ver Ejercicio.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_ejercicio extends controller
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
        parent::__construct(__CLASS__, 'Ver Ejercicio', 'Contabilidad', false, false);
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->ejercicio = false;
        if (isset($_GET['id'])) {
            $this->ejercicio = $this->ejercicios->get($_GET['id']);
            if (!$this->ejercicio) {
                $this->new_advice("No se encuentra el Ejercicio seleccionado.");
                return;
            } else if ($this->ejercicio->idempresa != $this->empresa->idempresa) {
                $this->ejercicio = false;
                $this->new_advice("El Ejercicio no esta disponible para su empresa.");
                return;
            }
        }
    }

    private function init_modelos()
    {
        $this->plancuentas = new plancuentas();
        $this->ejercicios  = new ejercicios();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
    }
}