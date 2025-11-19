<?php
/**
 * Controlador de Administrador -> Informacion Plan
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class informacion_plan extends controller
{
    //Filtros
    public $idempresa;
    //modelos
    //variables
    public $mostrar;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Información Del Plan', 'Configuración', true, false, false, 'bi bi-info-circle-fill');
    }

    protected function private_core()
    {
        $this->init_filter();
        $this->init_modelos();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
        $this->mostrar = 'datos';
        if (isset($_GET['mostrar'])) {
            $this->mostrar = $_GET['mostrar'];
        }
    }

    private function init_modelos()
    {

    }
}