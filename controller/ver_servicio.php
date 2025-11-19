<?php
/**
 * Controlador de Articulos -> Articulo.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_servicio extends controller
{
    //variables
    public $servicio;
    //modelos
    public $articulos;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ver Articulo', 'Inventario', false, false);
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->servicio = false;
        if (isset($_GET['id'])) {
            $this->servicio = $this->articulos->get($_GET['id']);
            if ($this->servicio) {
                if ($this->servicio->idempresa == $this->empresa->idempresa) {
                    if (isset($_POST['codprincipal'])) {
                        $this->modificar_servicio();
                    }
                } else {
                    $this->new_advice("El Servicio no esta disponible para su empresa.");
                    $this->servicio = false;
                    return;
                }
            } else {
                $this->new_advice("No se encuentra el servicio seleccionado.");
            }
        }

        if (isset($_POST['config'])) {
            $this->buscar_configuracion();
        } else if (isset($_GET['buscar_subcuenta'])) {
            $this->buscar_subcuenta();
        } else if (isset($_POST['idservicio_config'])) {
            $this->tratar_configuracion();
        }
    }

    private function init_modelos()
    {
        $this->articulos = new articulos();
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

    private function modificar_servicio()
    {
        $this->servicio->codprincipal = $_POST['codprincipal'];
        if ($_POST['codauxiliar'] != '') {
            $this->servicio->codauxiliar = $_POST['codauxiliar'];
        } else {
            $this->servicio->codauxiliar = null;
        }
        $this->servicio->nombre     = $_POST['nombre'];
        $this->servicio->idimpuesto = $_POST['idimpuesto'];
        $this->servicio->precio     = $_POST['precio'];
        $this->servicio->dto        = 0;
        if ($_POST['idgrupo'] != '') {
            $this->servicio->idgrupo = $_POST['idgrupo'];
        } else {
            $this->servicio->idgrupo = null;
        }
        $this->servicio->sevende   = isset($_POST['sevende']);
        $this->servicio->secompra  = isset($_POST['secompra']);
        $this->servicio->bloqueado = isset($_POST['bloqueado']);

        $this->servicio->fec_modificacion  = date('Y-m-d');
        $this->servicio->nick_modificacion = $this->user->nick;

        if ($this->servicio->save()) {
            $this->new_message("Servicio modificado correctamente.");
        } else {
            $this->new_error_msg("Error al modificar el Servicio.");
        }
    }

    private function buscar_configuracion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Servicio No encontrado.');
        if ($this->servicio) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByArticulo($this->servicio->idarticulo, $ejercicio->idejercicio);
                if ($param) {
                    $compras = false;
                    if ($param->idsubccompras) {
                        $comp0 = $this->subcuentas->get($param->idsubccompras);
                        if ($comp0) {
                            $compras = $comp0;
                        }
                    }
                    $ventas = false;
                    if ($param->idsubcventas) {
                        $vent0 = $this->subcuentas->get($param->idsubcventas);
                        if ($vent0) {
                            $ventas = $vent0;
                        }
                    }
                    $notas = false;
                    if ($param->idsubcntventas) {
                        $not0 = $this->subcuentas->get($param->idsubcntventas);
                        if ($not0) {
                            $notas = $not0;
                        }
                    }
                    $result = array('error' => 'F', 'msj' => '', 'compras' => $compras, 'ventas' => $ventas, 'notas' => $notas);
                } else {
                    $compras = false;
                    $ventas  = false;
                    $notas   = false;
                    $result  = array('error' => 'F', 'msj' => '', 'compras' => $compras, 'ventas' => $ventas, 'notas' => $notas);
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
        $result         = array('error' => 'T', 'msj' => 'Servicio No encontrado.');
        if ($this->servicio) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByArticulo($this->servicio->idarticulo, $ejercicio->idejercicio);
                if (!$param) {
                    $param                = new param_contable();
                    $param->idempresa     = $this->idempresa;
                    $param->idejercicio   = $ejercicio->idejercicio;
                    $param->idarticulo    = $this->servicio->idarticulo;
                    $param->fec_creacion  = date('Y-m-d');
                    $param->nick_creacion = $this->user->nick;
                } else {
                    $param->fec_modificacion  = date('Y-m-d');
                    $param->nick_modificacion = $this->user->nick;
                }
                $paso                 = false;
                $param->idsubccompras = null;
                if (isset($_POST['idsubccompras'])) {
                    $param->idsubccompras = $_POST['idsubccompras'];
                    $paso                 = true;
                }
                $param->idsubcventas = null;
                if (isset($_POST['idsubcventas'])) {
                    $param->idsubcventas = $_POST['idsubcventas'];
                    $paso                = true;
                }
                $param->idsubcntventas = null;
                if (isset($_POST['idsubcntventas'])) {
                    $param->idsubcntventas = $_POST['idsubcntventas'];
                    $paso                  = true;
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
