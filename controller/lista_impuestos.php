<?php
/**
 * Controlador de Inventario -> Articulos.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_impuestos extends controller
{
    //Filtros
    public $query;
    public $b_grupo;
    public $b_marca;
    public $b_tipo;
    public $idempresa;
    //modelos
    public $impuestos;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Impuestos', 'Configuración', true, true, false, 'bi bi-percent');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        if (isset($_POST['idimpuesto'])) {
            $this->modificar_impuesto();
        } else if (isset($_POST['codigo'])) {
            $this->crear_impuesto();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_impuesto();
        } else if (isset($_POST['config'])) {
            $this->buscar_configuracion();
        } else if (isset($_GET['buscar_subcuenta'])) {
            $this->buscar_subcuenta();
        } else if (isset($_POST['idimpuesto_config'])) {
            $this->tratar_configuracion();
        }

        $this->buscar();
    }

    private function init_modelos()
    {
        $this->impuestos = new impuestos();
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

    private function crear_impuesto()
    {
        if (!$this->impuestos->get_by_codigo($_POST['codigo'])) {
            $impuesto                = new impuestos();
            $impuesto->codigo        = $_POST['codigo'];
            $impuesto->nombre        = $_POST['nombre'];
            $impuesto->porcentaje    = $_POST['porcentaje'];
            $impuesto->fec_creacion  = date('Y-m-d');
            $impuesto->nick_creacion = $this->user->nick;

            if ($impuesto->save()) {
                $this->new_message("Impuesto creado correctamente.");
            } else {
                $this->new_error_msg("No se pudo crear el impuesto, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El impuesto con codigo " . $_POST['codigo'] . " ya se encuentra registrado.");
        }
    }

    private function modificar_impuesto()
    {
        $impuesto = $this->impuestos->get($_POST['idimpuesto']);
        if ($impuesto) {
            $impuesto->codigo            = $_POST['codigo'];
            $impuesto->nombre            = $_POST['nombre'];
            $impuesto->porcentaje        = $_POST['porcentaje'];
            $impuesto->fec_modificacion  = date('Y-m-d');
            $impuesto->nick_modificacion = $this->user->nick;

            if ($impuesto->save()) {
                $this->new_message("Impuesto modificado correctamente.");
            } else {
                $this->new_error_msg("No se pudo modificar el impuesto, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El impuesto no se encuentra registrado.");
        }
    }

    private function eliminar_impuesto()
    {
        $impuesto = $this->impuestos->get($_GET['delete']);
        if ($impuesto) {
            if ($impuesto->delete()) {
                $this->new_message("Impuesto eliminado correctamente.");
            } else {
                $this->new_error_msg("No se puede eliminar el impuesto, debe estar utilizado en una transacción de compra o venta.");
            }
        } else {
            $this->new_advice("Error al eliminar, el impuesto no se encuentra registrado o ya fue eliminado.");
        }
    }

    private function buscar()
    {
        $this->resultados = $this->impuestos->all();
    }

    private function buscar_configuracion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Impuesto No encontrado.');
        $impuesto       = $this->impuestos->get($_POST['config']);
        if ($impuesto) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByImpuestos($impuesto->idimpuesto, $ejercicio->idejercicio);
                if ($param) {
                    $compras = false;
                    if ($param->idsubcivacompras) {
                        $comp0 = $this->subcuentas->get($param->idsubcivacompras);
                        if ($comp0) {
                            $compras = $comp0;
                        }
                    }
                    $ventas = false;
                    if ($param->idsubcivaventas) {
                        $vent0 = $this->subcuentas->get($param->idsubcivaventas);
                        if ($vent0) {
                            $ventas = $vent0;
                        }
                    }
                    $notas = false;
                    if ($param->idsubcivanotasventa) {
                        $not0 = $this->subcuentas->get($param->idsubcivanotasventa);
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
        $result         = array('error' => 'T', 'msj' => 'Impuesto No encontrado.');

        $impuesto = $this->impuestos->get($_POST['idimpuesto_config']);
        if ($impuesto) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByImpuestos($impuesto->idimpuesto, $ejercicio->idejercicio);
                if (!$param) {
                    $param                = new param_contable();
                    $param->idempresa     = $this->idempresa;
                    $param->idejercicio   = $ejercicio->idejercicio;
                    $param->idimpuesto    = $impuesto->idimpuesto;
                    $param->fec_creacion  = date('Y-m-d');
                    $param->nick_creacion = $this->user->nick;
                } else {
                    $param->fec_modificacion  = date('Y-m-d');
                    $param->nick_modificacion = $this->user->nick;
                }

                $param->idsubcivacompras = $_POST['idsubcivacompras'];
                $param->idsubcivaventas  = $_POST['idsubcivaventas'];
                if (isset($_POST['idsubcivanotasventa'])) {
                    $param->idsubcivanotasventa = $_POST['idsubcivanotasventa'];
                }

                if ($param->save()) {
                    $result = array('error' => 'F', 'msj' => 'Parametrizaciones guardadas correctamente.');
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
