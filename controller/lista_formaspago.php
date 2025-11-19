<?php
/**
 * Controlador de Configuracion -> Formas de Pago.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_formaspago extends controller
{
    //Filtros
    public $query;
    public $b_grupo;
    public $b_marca;
    public $b_tipo;
    public $idempresa;
    //modelos
    public $formaspago;
    public $formaspago_sri;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Formas de Pago', 'Configuración', true, true, false, 'bi bi-cash-coin');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        if (isset($_POST['idformapago'])) {
            $this->modificar_formapago();
        } else if (isset($_POST['codigosri'])) {
            $this->crear_formapago();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_formapago();
        } else if (isset($_POST['config'])) {
            $this->buscar_configuracion();
        } else if (isset($_GET['buscar_subcuenta'])) {
            $this->buscar_subcuenta();
        } else if (isset($_POST['idformapago_config'])) {
            $this->tratar_configuracion();
        } else if (isset($_GET['actdetsri'])) {
            $this->getDetalleSri();
        }

        $this->buscar();
    }

    private function init_modelos()
    {
        $this->formaspago     = new formaspago();
        $this->formaspago_sri = new formaspago_sri();
        if (complemento_exists('contabilidad')) {
            $this->ejercicios = new ejercicios();
            $this->parametros = new param_contable();
            $this->subcuentas = new plancuentas();
        }
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
    }

    private function crear_formapago()
    {
        $formapago            = new formaspago();
        $formapago->idempresa = $this->empresa->idempresa;
        $formapago->codigosri = $_POST['codigosri'];
        $formapago->nombre    = $_POST['nombre'];
        if ($this->user->admin) {
            $formapago->escredito = isset($_POST['escredito']);
            $formapago->esnc      = isset($_POST['esnc']);
            $formapago->esreten   = isset($_POST['esreten']);
        }
        $formapago->escompra      = isset($_POST['escompra']);
        $formapago->esventa       = isset($_POST['esventa']);
        $formapago->esefec        = isset($_POST['esefec']);
        $formapago->num_doc       = isset($_POST['num_doc']);
        $formapago->fec_creacion  = date('Y-m-d');
        $formapago->nick_creacion = $this->user->nick;
        if ($formapago->save()) {
            $this->new_message("Forma de Pago creada correctamente.");
        } else {
            $this->new_error_msg("No se pudo crear la forma de pago, verifique los datos y vuelva a intentarlo.");
        }
    }

    private function modificar_formapago()
    {
        $formapago = $this->formaspago->get($_POST['idformapago']);
        if ($formapago) {
            $formapago->codigosri = $_POST['codigosri'];
            $formapago->nombre    = $_POST['nombre'];
            if ($this->user->admin) {
                $formapago->escredito = isset($_POST['escredito']);
                $formapago->esnc      = isset($_POST['esnc']);
                $formapago->esreten   = isset($_POST['esreten']);
            }
            $formapago->escompra          = isset($_POST['escompra']);
            $formapago->esventa           = isset($_POST['esventa']);
            $formapago->num_doc           = isset($_POST['num_doc']);
            $formapago->esefec            = isset($_POST['esefec']);
            $formapago->fec_modificacion  = date('Y-m-d');
            $formapago->nick_modificacion = $this->user->nick;

            if ($formapago->save()) {
                $this->new_message("Forma de Pago modificada correctamente.");
            } else {
                $this->new_error_msg("No se pudo modificar la forma de pago, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("La forma de pago no se encuentra registrado.");
        }
    }

    private function eliminar_formapago()
    {
        $formapago = $this->formaspago->get($_GET['delete']);
        if ($formapago) {
            if ($formapago->delete()) {
                $this->new_message("Forma de Pago eliminada correctamente.");
            } else {
                $this->new_error_msg("No se puede eliminar la forma de pago, debe estar utilizado en una transacción de compra.");
            }
        } else {
            $this->new_advice("Error al eliminar, la forma de pago no se encuentra registrada o ya fue eliminada.");
        }
    }

    private function buscar()
    {
        $this->resultados = buscar_formaspago('', $this->empresa->idempresa);
    }

    private function buscar_configuracion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Forma de Pago No encontrada.');
        $formapago      = $this->formaspago->get($_POST['config']);
        if ($formapago) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByFormaPago($formapago->idformapago, $ejercicio->idejercicio);
                if ($param) {
                    $pagos = false;
                    if ($param->idsubcformapago) {
                        $pago0 = $this->subcuentas->get($param->idsubcformapago);
                        if ($pago0) {
                            $pagos = $pago0;
                        }
                    }
                    $result = array('error' => 'F', 'msj' => '', 'pagos' => $pagos);
                } else {
                    $pagos  = false;
                    $result = array('error' => 'F', 'msj' => '', 'pagos' => $pagos);
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Ejercicio No encontrado.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_subcuenta()
    {
        $this->template = false;
        $result         = array();
        $result         = $this->subcuentas->buscar_subcuenta($this->idempresa, $_GET['ejer'], $_GET['buscar_subcuenta']);

        echo json_encode($result);
        exit;
    }

    private function tratar_configuracion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Forma de Pago No encontrada.');

        $formapago = $this->formaspago->get($_POST['idformapago_config']);
        if ($formapago) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByFormaPago($formapago->idformapago, $ejercicio->idejercicio);
                if (!$param) {
                    $param                = new param_contable();
                    $param->idempresa     = $this->idempresa;
                    $param->idejercicio   = $ejercicio->idejercicio;
                    $param->idformapago   = $formapago->idformapago;
                    $param->fec_creacion  = date('Y-m-d');
                    $param->nick_creacion = $this->user->nick;
                } else {
                    $param->fec_modificacion  = date('Y-m-d');
                    $param->nick_modificacion = $this->user->nick;
                }

                $param->idsubcformapago = $_POST['idsubcformapago'];

                if ($param->save()) {
                    $result = array('error' => 'F', 'msj' => 'Parametrización guardada correctamente.');
                } else {
                    $msj = '';
                    foreach ($param->get_errors() as $key => $e) {
                        $msj .= $e . ". ";
                    }
                    $mensaje = '';
                    if ($msj) {
                        $mensaje = 'Error: ' . $msj;
                    }
                    $result = array('error' => 'T', 'msj' => 'No se pudo almacenar la Parametrización. ' . $mensaje);
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Ejercicio No encontrado.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function getDetalleSri()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => '');
        
        $lineas = $this->formaspago_sri->all();
        if ($lineas) {
            $result = array('error' => 'F', 'msj' => '', 'lineas' => $lineas);
        }

        echo json_encode($result);
        exit;
    }
}
