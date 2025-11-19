<?php
/**
 * Controlador de Clientes -> Cliente.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_cliente extends controller
{
    //variables
    public $cliente;
    //modelos
    public $clientes;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ver Cliente', 'Ventas', false, false);
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->cliente = false;
        if (isset($_GET['id'])) {
            $this->cliente = $this->clientes->get($_GET['id']);
            if ($this->cliente) {
                if ($this->cliente->idempresa == $this->empresa->idempresa) {
                    if (isset($_POST['identificacion'])) {
                        $this->modificar_cliente();
                    } else if (isset($_POST['idmedidor'])) {
                        $this->editar_medidor();
                    } else if (isset($_POST['nuevo_medidor'])) {
                        $this->nuevo_medidor();
                    }
                } else {
                    $this->new_advice("El Cliente no esta disponible para su empresa.");
                    $this->cliente = false;
                    return;
                }
            } else {
                $this->new_advice("No se encuentra el cliente seleccionado.");
            }
        }

        if (isset($_POST['config'])) {
            $this->buscar_configuracion();
        } else if (isset($_GET['buscar_subcuenta'])) {
            $this->buscar_subcuenta();
        } else if (isset($_POST['idcliente_config'])) {
            $this->tratar_configuracion();
        }
    }

    private function init_modelos()
    {
        $this->clientes = new clientes();
        if (complemento_exists('juntadeagua')) {
            $this->medidores = new medidores_cliente();
        }
        if (complemento_exists('contabilidad')) {
            $this->ejercicios = new ejercicios();
            $this->parametros = new param_contable();
            $this->subcuentas = new plancuentas();
        }
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
        $this->mostrar   = 'datos';
        if (isset($_GET['mostrar'])) {
            $this->mostrar = $_GET['mostrar'];
        }
    }

    private function modificar_cliente()
    {
        $this->cliente->identificacion = $_POST['identificacion'];
        $this->cliente->tipoid         = $_POST['tipoid'];
        $this->cliente->razonsocial    = $_POST['razonsocial'];
        if ($_POST['nombrecomercial'] != '') {
            $this->cliente->nombrecomercial = $_POST['nombrecomercial'];
        } else {
            $this->cliente->nombrecomercial = $_POST['razonsocial'];
        }
        $this->cliente->telefono = $_POST['telefono'];
        if ($_POST['celular'] != '') {
            $this->cliente->celular = $_POST['celular'];
        }
        $this->cliente->email             = $_POST['email'];
        $this->cliente->direccion         = $_POST['direccion'];
        $this->cliente->regimen           = $_POST['regimen'];
        $this->cliente->obligado          = isset($_POST['obligado']);
        $this->cliente->agretencion       = isset($_POST['agretencion']);
        $this->cliente->fec_modificacion  = date('Y-m-d');
        $this->cliente->nick_modificacion = $this->user->nick;

        if ($this->cliente->save()) {
            $this->new_message("Cliente modificado correctamente.");
        } else {
            $this->new_error_msg("Error al modificar el Cliente.");
        }
    }

    private function editar_medidor()
    {
        $medidor = $this->medidores->get($_POST['idmedidor']);
        if ($medidor) {
            $medidor->numero            = $_POST['numero'];
            $medidor->fec_inicio        = $_POST['fec_inicio'];
            $medidor->consumo_ini       = $_POST['consumo_ini'];
            $medidor->sector            = $_POST['sector'];
            $medidor->activo            = isset($_POST['activo']);
            $medidor->fec_modificacion  = date('Y-m-d');
            $medidor->nick_modificacion = $this->user->nick;

            if ($medidor->save()) {
                $this->new_message("Medidor modificado correctamente.");
            } else {
                $this->new_error_msg("Error al modificar el Medidor del Cliente.");
            }

        } else {
            $this->new_error_msg("Medidor de Cliente no encontrado, imposible modificar.");
        }
    }

    private function nuevo_medidor()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Cliente No Encontrado.');

        $medidor                = new medidores_cliente();
        $medidor->idempresa     = $this->empresa->idempresa;
        $medidor->idcliente     = $this->cliente->idcliente;
        $medidor->numero        = $_POST['numero'];
        $medidor->fec_inicio    = $_POST['fec_inicio'];
        $medidor->consumo_ini   = $_POST['consumo_ini'];
        $medidor->sector        = $_POST['sector'];
        $medidor->activo        = isset($_POST['activo']);
        $medidor->fec_creacion  = date('Y-m-d');
        $medidor->nick_creacion = $this->user->nick;

        if ($medidor->save()) {
            $result = array('error' => 'F', 'msj' => 'Medidor creado correctamente.', 'url' => $this->cliente->url() . "&mostrar=medidores");
        } else {
            $result = array('error' => 'T', 'msj' => 'Error al crear el Medidor del Cliente.');
        }

        echo json_encode($result);
        exit;
    }

    private function buscar_configuracion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Cliente No encontrado.');
        if ($this->cliente) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByCliente($this->cliente->idcliente, $ejercicio->idejercicio);
                if ($param) {
                    $cliente = false;
                    if ($param->idsubccliente) {
                        $cli0 = $this->subcuentas->get($param->idsubccliente);
                        if ($cli0) {
                            $cliente = $cli0;
                        }
                    }
                    $anticipo = false;
                    if ($param->idsubcantcliente) {
                        $ant0 = $this->subcuentas->get($param->idsubcantcliente);
                        if ($ant0) {
                            $anticipo = $ant0;
                        }
                    }
                    $notas = false;
                    if ($param->idsubcntcliente) {
                        $not0 = $this->subcuentas->get($param->idsubcntcliente);
                        if ($not0) {
                            $notas = $not0;
                        }
                    }
                    $result = array('error' => 'F', 'msj' => '', 'cliente' => $cliente, 'anticipo' => $anticipo, 'notas' => $notas);
                } else {
                    $cliente  = false;
                    $anticipo = false;
                    $notas    = false;
                    $result   = array('error' => 'F', 'msj' => '', 'cliente' => $cliente, 'anticipo' => $anticipo, 'notas' => $notas);
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
        $result         = array('error' => 'T', 'msj' => 'Cliente No encontrado.');
        if ($this->cliente) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByCliente($this->cliente->idcliente, $ejercicio->idejercicio);
                if (!$param) {
                    $param                = new param_contable();
                    $param->idempresa     = $this->idempresa;
                    $param->idejercicio   = $ejercicio->idejercicio;
                    $param->idcliente     = $this->cliente->idcliente;
                    $param->fec_creacion  = date('Y-m-d');
                    $param->nick_creacion = $this->user->nick;
                } else {
                    $param->fec_modificacion  = date('Y-m-d');
                    $param->nick_modificacion = $this->user->nick;
                }
                $param->idsubccliente = null;
                if (isset($_POST['idsubccliente'])) {
                    $param->idsubccliente = $_POST['idsubccliente'];
                }
                $param->idsubcantcliente = null;
                if (isset($_POST['idsubcantcliente'])) {
                    $param->idsubcantcliente = $_POST['idsubcantcliente'];
                }
                $param->idsubcntcliente = null;
                if (isset($_POST['idsubcntcliente'])) {
                    $param->idsubcntcliente = $_POST['idsubcntcliente'];
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
