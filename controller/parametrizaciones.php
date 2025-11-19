<?php
/**
 * Controlador de Configuracion -> Parametrizaciones.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class parametrizaciones extends controller
{
    public $idempresa;
    public $parametrizacion;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Parametrizaciones', 'Configuración', true, true, false, 'bi bi-gear');
    }

    protected function private_core()
    {
        $this->init_filter();
        $this->init_models();

        if (isset($_POST['codigop'])) {
            $this->tratar_parametrizacion();
        }
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
    }

    private function init_models()
    {
        $this->parametrizacion = new parametrizacion();
    }

    public function getValor($codigo)
    {
        $param = $this->parametrizacion->all_by_codigo($this->idempresa, $codigo);
        if ($param) {
            return $param->valor;
        }

        return '';
    }

    private function tratar_parametrizacion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error al almacenar la Parametrización.');
        if ($_POST['valorp'] != '') {
            $param = $this->parametrizacion->all_by_codigo($this->idempresa, $_POST['codigop']);
            if (!$param) {
                $param                = new parametrizacion();
                $param->idempresa     = $this->idempresa;
                $param->fec_creacion  = date('Y-m-d');
                $param->nick_creacion = $this->user->nick;
            } else {
                $param->fec_modificacion  = date('Y-m-d');
                $param->nick_modificacion = $this->user->nick;
            }
            $param->codigo = $_POST['codigop'];
            $param->valor  = $_POST['valorp'];
            if ($param->save()) {
                $result = array('error' => 'F', 'msj' => '');
            }
        } else {
            $result = array('error' => 'T', 'msj' => 'Valor de Parametrización no Válido.');
        }

        echo json_encode($result);
        exit;
    }
}
