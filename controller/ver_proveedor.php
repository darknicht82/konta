<?php
/**
 * Controlador de Proveedores -> Proveedor.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_proveedor extends controller
{
    //variables
    public $proveedor;
    public $allow_delete;
    public $allow_modify;
    //modelos
    public $proveedores;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Proveedor', 'Proveedores', false, false);
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->proveedor = false;
        if (isset($_GET['id'])) {
            $this->proveedor = $this->proveedores->get($_GET['id']);
            if ($this->proveedor) {
                if ($this->proveedor->idempresa == $this->empresa->idempresa) {
                    if (isset($_POST['identificacion'])) {
                        $this->modificar_proveedor();
                    }
                } else {
                    $this->new_advice("El Proveedor no esta disponible para su empresa.");
                    $this->proveedor = false;
                    return;
                }
            } else {
                $this->new_advice("No se encuentra el proveedor seleccionado.");
            }
        }

        if (isset($_POST['config'])) {
            $this->buscar_configuracion();
        } else if (isset($_GET['buscar_subcuenta'])) {
            $this->buscar_subcuenta();
        } else if (isset($_POST['idproveedor_config'])) {
            $this->tratar_configuracion();
        }
    }

    private function init_modelos()
    {
        $this->proveedores = new proveedores();
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

    private function modificar_proveedor()
    {
        $this->proveedor->identificacion = $_POST['identificacion'];
        $this->proveedor->tipoid         = $_POST['tipoid'];
        $this->proveedor->razonsocial    = $_POST['razonsocial'];
        if ($_POST['nombrecomercial'] != '') {
            $this->proveedor->nombrecomercial = $_POST['nombrecomercial'];
        } else {
            $this->proveedor->nombrecomercial = $_POST['razonsocial'];
        }
        $this->proveedor->telefono = $_POST['telefono'];
        if ($_POST['celular'] != '') {
            $this->proveedor->celular = $_POST['celular'];
        }
        $this->proveedor->email             = $_POST['email'];
        $this->proveedor->direccion         = $_POST['direccion'];
        $this->proveedor->regimen           = $_POST['regimen'];
        $this->proveedor->obligado          = isset($_POST['obligado']);
        $this->proveedor->agretencion       = isset($_POST['agretencion']);
        $this->proveedor->fec_modificacion  = date('Y-m-d');
        $this->proveedor->nick_modificacion = $this->user->nick;

        if ($this->proveedor->save()) {
            $this->new_message("Proveedor modificado correctamente.");
        } else {
            $this->new_error_msg("Error al modificar el proveedor.");
        }
    }

    private function buscar_configuracion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Servicio No encontrado.');
        if ($this->proveedor) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByProveedor($this->proveedor->idproveedor, $ejercicio->idejercicio);
                if ($param) {
                    $proveedor = false;
                    if ($param->idsubcproveedor) {
                        $cli0 = $this->subcuentas->get($param->idsubcproveedor);
                        if ($cli0) {
                            $proveedor = $cli0;
                        }
                    }
                    $anticipo = false;
                    if ($param->idsubcantproveedor) {
                        $ant0 = $this->subcuentas->get($param->idsubcantproveedor);
                        if ($ant0) {
                            $anticipo = $ant0;
                        }
                    }
                    $result = array('error' => 'F', 'msj' => '', 'proveedor' => $proveedor, 'anticipo' => $anticipo);
                } else {
                    $proveedor  = false;
                    $anticipo = false;
                    $result   = array('error' => 'F', 'msj' => '', 'proveedor' => $proveedor, 'anticipo' => $anticipo);
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
        $result         = array('error' => 'T', 'msj' => 'Proveedor No encontrado.');
        if ($this->proveedor) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByProveedor($this->proveedor->idproveedor, $ejercicio->idejercicio);
                if (!$param) {
                    $param                = new param_contable();
                    $param->idempresa     = $this->idempresa;
                    $param->idejercicio   = $ejercicio->idejercicio;
                    $param->idproveedor     = $this->proveedor->idproveedor;
                    $param->fec_creacion  = date('Y-m-d');
                    $param->nick_creacion = $this->user->nick;
                } else {
                    $param->fec_modificacion  = date('Y-m-d');
                    $param->nick_modificacion = $this->user->nick;
                }
                $param->idsubcproveedor = null;
                if (isset($_POST['idsubcproveedor'])) {
                    $param->idsubcproveedor = $_POST['idsubcproveedor'];
                }
                $param->idsubcantproveedor = null;
                if (isset($_POST['idsubcantproveedor'])) {
                    $param->idsubcantproveedor = $_POST['idsubcantproveedor'];
                }

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
}
