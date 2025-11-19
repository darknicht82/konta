<?php
/**
 * Controlador de Movimientos -> Ver Movimientos de Stock.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_movimiento_stock extends controller
{
    //variables
    public $movimiento;
    public $allow_delete;
    public $allow_modify;
    public $impresion;
    //modelos
    public $movimientos;
    public $lineasmovimientos;
    public $articulos;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ver Movimiento', 'Ventas', false, false);
    }

    protected function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->impresion = $this->user->have_access_to('impresion_docs');

        $this->movimientos       = new movimientos();
        $this->lineasmovimientos = new lineasmovimientos();
        $this->articulos         = new articulos();

        $this->movimiento = false;
        if (isset($_GET['id'])) {
            $this->movimiento = $this->movimientos->get($_GET['id']);
            if (!$this->movimiento) {
                $this->new_advice("No se encuentra la movimiento seleccionado.");
                return;
            } else if ($this->movimiento->idempresa != $this->empresa->idempresa) {
                $this->movimiento = false;
                $this->new_advice("El documento no esta disponible para su empresa.");
                return;
            }
        }

        if (isset($_POST['nueva_linea'])) {
            $this->agregar_linea();
        } else if (isset($_POST['codigobarras'])) {
            $this->buscar_codigobarras();
        } else if (isset($_GET['buscar_articulo'])) {
            $this->buscar_articulos();
        } else if (isset($_POST['idarticulo'])) {
            $this->buscar_articulo();
        } else if (isset($_GET['actdetmov'])) {
            $this->buscar_detalle_movimiento();
        } else if (isset($_POST['eliminar_linea'])) {
            $this->eliminar_linea();
        } else if (isset($_POST['buscar_cobros'])) {
            $this->buscar_cobros();
        } else if (isset($_POST['buscar_fp'])) {
            $this->buscar_fp();
        } else if (isset($_POST['nuevo_cobro'])) {
            $this->agregar_cobro();
        } else if (isset($_POST['eliminar_cobro'])) {
            $this->eliminar_cobro();
        } else if (isset($_GET['autorizar'])) {
            $this->procesar_autorizacion();
        }
    }

    private function eliminar_linea()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Movimiento No Encontrada.');
        if ($this->movimiento) {
            $linea = $this->lineasmovimientos->get($_POST['eliminar_linea']);
            if ($linea) {
                if ($linea->delete()) {
                    $this->movimiento->total -= $linea->costototal;

                    if ($this->movimiento->save()) {
                        $result = array('error' => 'F', 'msj' => '');
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al actualizar los totales del Movimiento.');
                        $linea->save();
                    }
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al Eliminar la linea del movimiento.');
                    if ($linea->get_errors()) {
                        foreach ($linea->get_errors() as $key => $val) {
                            $result['msj'] .= "\n" . $val;
                        }
                    }
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Linea de Movimiento no Encontrada.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_detalle_movimiento()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No existe la Movimiento.');
        if ($this->movimiento) {
            $lineas = $this->lineasmovimientos->all_by_idmovimiento($this->movimiento->idmovimiento);
            $result = array('error' => 'T', 'msj' => 'Sin Detalle.');
            if ($lineas) {
                $result = array('error' => 'F', 'msj' => '', 'movimiento' => $this->movimiento, 'lineas' => $lineas);
            }
        }

        echo json_encode($result);
        exit;
    }

    private function buscar_codigobarras()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Artículo No encontrado.');
        $articulos      = new articulos(false, $_POST['establecimiento']);
        $articulo       = $articulos->get_by_codbarras($this->empresa->idempresa, $_POST['codigobarras']);
        if ($articulo) {
            if ($articulo->controlar_stock == 1 && $articulo->tipo == 1) {
                if ($articulo->stock_fisico > 1) {
                    $result = array('error' => 'F', 'msj' => '', 'art' => $articulo);
                } else {
                    $result = array('error' => 'T', 'msj' => 'Artículo Sin Stock.');
                }
            } else {
                $result = array('error' => 'F', 'msj' => '', 'art' => $articulo);
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_articulo()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Artículo No encontrado.');
        $articulos      = new articulos(false, $_POST['establecimiento']);
        $articulo       = $articulos->get($_POST['idarticulo']);
        if ($articulo) {
            $trans0 = new \trans_inventario();
            $costo = $trans0->getCostoArticulo($this->empresa->idempresa, $articulo->idarticulo, $this->movimiento->fec_emision, $this->movimiento->hora_emision);
            if ($this->movimiento->tipo == 'egre') {
                if ($articulo->controlar_stock == 1 && $articulo->tipo == 1) {
                    if ($articulo->stock_fisico > 0) {
                        $result = array('error' => 'F', 'msj' => '', 'art' => $articulo, 'costo' => $costo);
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Artículo Sin Stock.');
                    }
                } else {
                    $result = array('error' => 'F', 'msj' => '', 'art' => $articulo, 'costo' => $costo);
                }
            } else {
                $result = array('error' => 'F', 'msj' => '', 'art' => $articulo, 'costo' => $costo);
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_articulos()
    {
        $this->template = false;
        $articulos      = buscar_articulos($this->empresa->idempresa, $_GET['buscar_articulo'], '', '', 1, '', $_GET['establecimiento']);
        echo json_encode($articulos);
        exit;
    }

    private function agregar_linea()
    {
        $this->template = false;
        $paso           = true;
        $result         = array('error' => 'T', 'msj' => 'Movimiento No Encontrada.');
        if ($this->movimiento) {
            //busco el ariculo para ver si tengo q validar el stock
            $art = $this->articulos->get($_POST['idarticulo']);
            if ($art) {
                if ($this->movimiento->tipo == 'egre') {
                    if ($art->controlar_stock == 1) {
                        //Si es tipo 1 (articulo) y esta activo el control de stock valido el stock del almacen
                        $stock = new stocks();
                        $st    = $stock->get_by_idestab_idart($this->movimiento->idestablecimiento, $art->idarticulo);
                        if ($st) {
                            if ($_POST['cantidad'] > $st->stock) {
                                $result = array('error' => 'T', 'msj' => 'El articulo ' . $art->nombre . " no tiene el stock suficiente. Stock Actual: " . $st->stock);
                                $paso   = false;
                            }
                        } else {
                            $result = array('error' => 'T', 'msj' => 'El articulo ' . $art->nombre . " no tiene el stock suficiente. Stock Actual: 0");
                            $paso   = false;
                        }
                    }
                }

            } else {
                $result = array('error' => 'T', 'msj' => 'Articulo No Encontrado.');
                $paso   = false;
            }

            if ($paso) {
                $linea               = new lineasmovimientos();
                $linea->idmovimiento = $this->movimiento->idmovimiento;
                $linea->idarticulo   = $_POST['idarticulo'];
                $linea->codprincipal = $_POST['codprincipal'];
                $linea->descripcion  = $_POST['descripcion'];
                $linea->cantidad     = $_POST['cantidad'];
                $linea->costo        = $_POST['costo'];
                $linea->costototal   = $_POST['costototal'];
                $linea->fec_creacion  = date('Y-m-d');
                $linea->nick_creacion = $this->user->nick;

                if ($linea->save()) {
                    $this->movimiento->total += $linea->costototal;

                    if ($this->movimiento->save()) {
                        $result = array('error' => 'F', 'msj' => '');
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al actualizar los totales del Movimiento.');
                        $linea->delete();
                    }
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al Guardar la linea del movimiento.');
                    if ($linea->get_errors()) {
                        foreach ($linea->get_errors() as $key => $val) {
                            $result['msj'] .= "\n" . $val;
                        }
                    }
                }
            }
        }
        echo json_encode($result);
        exit;
    }
}
