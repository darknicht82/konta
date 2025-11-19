<?php
/**
 * Controlador de Nota de Venta -> Ver Nota de Venta.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_nota_venta extends controller
{
    //variables
    public $factura;
    public $allow_delete;
    public $allow_modify;
    public $impresion;
    //modelos
    public $facturascli;
    public $lineasfacturascli;
    public $articulos;
    public $trans_cobros;
    public $formaspago;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ver Nota de Venta', 'Ventas', false, false);
    }

    protected function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->impresion = $this->user->have_access_to('impresion_ventas');

        $this->facturascli       = new facturascli();
        $this->lineasfacturascli = new lineasfacturascli();
        $this->articulos         = new articulos();
        $this->trans_cobros      = new trans_cobros();
        $this->formaspago        = new formaspago();
        $this->clientes          = new clientes();

        $this->factura = false;
        if (isset($_GET['id'])) {
            $this->factura = $this->facturascli->get($_GET['id']);
            if (!$this->factura) {
                $this->new_advice("No se encuentra la factura seleccionada.");
                return;
            } else if ($this->factura->idempresa != $this->empresa->idempresa) {
                $this->factura = false;
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
        } else if (isset($_GET['actdetfact'])) {
            $this->buscar_detalle_factura();
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
        } else if (isset($_POST['validar_edicion'])) {
            $this->validar_edicion();
        } else if (isset($_POST['editar_doc'])) {
            $this->editar_documento();
        } else if (isset($_GET['buscar_cliente'])) {
            $this->buscar_clientes();
        } else if (isset($_POST['idcliente'])) {
            $this->buscar_cliente();
        }
    }

    private function buscar_clientes()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_clientes($this->empresa->idempresa, $_GET['buscar_cliente']);

        echo json_encode($result);
        exit;
    }

    private function buscar_cliente()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Cliente No encontrado.');
        $cliente        = $this->clientes->get($_POST['idcliente']);
        if ($cliente) {
            $result = array('error' => 'F', 'msj' => '', 'cli' => $cliente);
        }
        echo json_encode($result);
        exit;
    }

    private function eliminar_linea()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Factura No Encontrada.');
        if ($this->factura) {
            $linea = $this->lineasfacturascli->get($_POST['eliminar_linea']);
            if ($linea) {
                if ($linea->delete()) {
                    switch ($linea->get_impuesto()) {
                        case 'IVANO':
                            //Base no Objeto de IVA
                            $this->factura->base_noi -= $linea->pvptotal;
                            break;
                        case 'IVA0':
                            //Base 0
                            $this->factura->base_0 -= $linea->pvptotal;
                            break;
                        case 'IVAEX':
                            //Base Excento de IVA
                            $this->factura->base_exc -= $linea->pvptotal;
                            break;
                        default:
                            //Base Gravada
                            $this->factura->base_gra -= $linea->pvptotal;
                            break;
                    }
                    $this->factura->totaldescuento -= ($linea->pvpsindto - $linea->pvptotal);
                    $this->factura->totalice -= $linea->valorice;
                    $this->factura->totaliva -= $linea->valoriva;

                    if ($this->factura->save()) {
                        $result = array('error' => 'F', 'msj' => '');
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al actualizar los totales de la Factura.');
                        $linea->save();
                    }
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al Eliminar la linea de la factura.');
                    if ($linea->get_errors()) {
                        foreach ($linea->get_errors() as $key => $val) {
                            $result['msj'] .= "\n" . $val;
                        }
                    }
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Linea de Factura no Encontrada.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function eliminar_cobro()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Factura No Encontrada.');
        if ($this->factura) {
            $cobro0 = new \trans_cobros();
            $cobro  = $cobro0->get($_POST['eliminar_cobro']);
            if ($cobro) {
                if ($cobro->delete()) {
                    $result = array('error' => 'F', 'msj' => 'Cobro Eliminado Correctamente.');
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al eliminar el cobro de la Factura.');
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Cobro No Encontrado.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_detalle_factura()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No existe la Factura.');
        if ($this->factura) {
            $lineas = $this->lineasfacturascli->all_by_idfacturacli($this->factura->idfacturacli);
            $result = array('error' => 'T', 'msj' => 'Sin Detalle.');
            if ($lineas) {
                $result = array('error' => 'F', 'msj' => '', 'factura' => $this->factura, 'lineas' => $lineas);
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
            $result = array('error' => 'F', 'msj' => '', 'art' => $articulo);
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_articulos()
    {
        $this->template = false;
        $articulos      = buscar_articulos($this->empresa->idempresa, $_GET['buscar_articulo'], '', '', '', '', $_GET['establecimiento'], true, '', true);
        echo json_encode($articulos);
        exit;
    }

    private function agregar_linea()
    {
        $this->template = false;
        $paso           = true;
        $result         = array('error' => 'T', 'msj' => 'Factura No Encontrada.');
        if ($this->factura) {
            //busco el ariculo para ver si tengo q validar el stock
            $art = $this->articulos->get($_POST['idarticulo']);
            if ($art) {
                if ($art->tipo == 1 && $art->controlar_stock == 1) {
                    //Si es tipo 1 (articulo) y esta activo el control de stock valido el stock del almacen
                    $stock = new stocks();
                    $st    = $stock->get_by_idestab_idart($this->factura->idestablecimiento, $art->idarticulo);
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

            } else {
                $result = array('error' => 'T', 'msj' => 'Articulo No Encontrado.');
                $paso   = false;
            }

            if ($paso) {
                $linea               = new lineasfacturascli();
                $linea->idfacturacli = $this->factura->idfacturacli;
                $linea->idarticulo   = $_POST['idarticulo'];
                $linea->idimpuesto   = $_POST['idimpuesto'];
                $linea->codprincipal = $_POST['codprincipal'];
                $linea->descripcion  = $_POST['descripcion'];
                $linea->cantidad     = $_POST['cantidad'];
                $linea->pvpunitario  = $_POST['pvpunitario'];
                $linea->dto          = $_POST['dto'];
                $linea->pvptotal     = $_POST['pvptotal'];
                $linea->pvpsindto    = $linea->cantidad * $linea->pvpunitario;
                //$linea->valorice      = $_POST['valorice'];
                $linea->valoriva      = $_POST['valoriva'];
                $linea->fec_creacion  = date('Y-m-d');
                $linea->nick_creacion = $this->user->nick;

                if ($linea->save()) {
                    switch ($linea->get_impuesto()) {
                        case 'IVANO':
                            //Base no Objeto de IVA
                            $this->factura->base_noi += $linea->pvptotal;
                            break;
                        case 'IVA0':
                            //Base 0
                            $this->factura->base_0 += $linea->pvptotal;
                            break;
                        case 'IVAEX':
                            //Base Excento de IVA
                            $this->factura->base_exc += $linea->pvptotal;
                            break;
                        default:
                            //Base Gravada
                            $this->factura->base_gra += $linea->pvptotal;
                            break;
                    }
                    $this->factura->totaldescuento += ($linea->pvpsindto - $linea->pvptotal);
                    $this->factura->totalice += $linea->valorice;
                    $this->factura->totaliva += $linea->valoriva;

                    if ($this->factura->save()) {
                        $result = array('error' => 'F', 'msj' => '');
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al actualizar los totales de la Factura.');
                        $linea->delete();
                    }
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al Guardar la linea de la factura.');
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

    private function buscar_cobros()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Factura no Encontrada');
        if ($this->factura) {
            if ($this->factura->total > 0) {
                $cobros = $this->trans_cobros->all_by_idfacturacli($this->factura->idfacturacli);
                $result = array('error' => 'F', 'msj' => '', 'total' => $this->factura->total, 'cobros' => $cobros);
            } else {
                $result = array('error' => 'T', 'msj' => 'La factura no tiene un valor a pagar, ingrese el detalle de la factura y vuelva a intentar.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_fp()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Factura no Encontrada');
        if ($this->factura) {
            $fpago = $this->formaspago->get($_POST['buscar_fp']);
            if ($fpago) {
                $result = array('error' => 'F', 'msj' => '', 'fp' => $fpago);
            } else {
                $result = array('error' => 'T', 'msj' => 'Forma de Pago no Encontrada');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function agregar_cobro()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Factura no Encontrada');
        if ($this->factura) {
            $cobro               = new \trans_cobros();
            $cobro->idempresa    = $this->empresa->idempresa;
            $cobro->idcliente    = $this->factura->idcliente;
            $cobro->idfacturacli = $this->factura->idfacturacli;
            $cobro->idformapago  = $_POST['idformapago'];
            $cobro->tipo         = 'Cobro';
            $cobro->fecha_trans  = $_POST['fecha_trans'];
            if ($_POST['num_doc'] != '') {
                $cobro->num_doc = $_POST['num_doc'];
            }
            $cobro->credito       = $_POST['valor'];
            $cobro->esabono       = true;
            $cobro->fec_creacion  = date('Y-m-d');
            $cobro->nick_creacion = $this->user->nick;
            if ($cobro->save()) {
                $result = array('error' => 'F', 'msj' => 'Cobro Registrado Correctamente.');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al generar el Cobro.');
            }
        }
        echo json_encode($result);
        exit;
    }

    public function validar_edicion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Nota de Venta no Encontrada');
        if ($this->factura) {
            if (!$this->allow_modify) {
                $result = array('error' => 'T', 'msj' => 'El usuario no tiene permiso para editar el documento.');
            } else if ($this->factura->anulado) {
                $result = array('error' => 'T', 'msj' => 'La Nota de venta se encuentra anulada, no se puede editar.');
            } else if ($this->factura->estado_sri == 'AUTORIZADO') {
                $result = array('error' => 'T', 'msj' => 'La Nota de venta se encuentra Autorizada, no se puede editar.');
            } else {
                $cobros = $this->trans_cobros->all_by_idfacturacli($this->factura->idfacturacli);
                if (count($cobros) > 1) {
                    $result = array('error' => 'T', 'msj' => 'La Nota de venta tiene cobros asociados, no se puede editar.');
                } else {
                    $result = array('error' => 'F', 'msj' => '', 'factura' => $this->factura, 'cliente' => $this->factura->get_cliente());
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    private function editar_documento()
    {
        if ($this->factura) {
            if (!$this->allow_modify) {
                $this->new_advice("El usuario no tiene permiso para editar el documento.");
                return;
            } else if ($this->factura->anulado) {
                $this->new_advice("La Nota de venta se encuentra anulada, no se puede editar.");
                return;
            } else {
                $cobros = $this->trans_cobros->all_by_idfacturacli($this->factura->idfacturacli);
                if (count($cobros) > 1) {
                    $this->new_advice("La Nota de venta tiene cobros asociados, no se puede editar.");
                    return;
                }
            }

            $this->factura->idcliente      = $_POST['idcliente'];
            $this->factura->tipoid         = $_POST['tipidencliente'];
            $this->factura->identificacion = $_POST['identcliente'];
            $this->factura->razonsocial    = $_POST['razonscliente'];            

            $this->factura->direccion     = $_POST['direccion'];
            $this->factura->email         = $_POST['email'];
            $this->factura->observaciones = null;
            if ($_POST['observaciones'] != '') {
                $this->factura->observaciones = $_POST['observaciones'];
            }

            $this->factura->nick_modificacion = $this->user->nick;
            $this->factura->fec_modificacion  = date('Y-m-d');

            if ($this->factura->save()) {
                $trinv = new trans_inventario();
                $trinv->actualizar_cliente($this->empresa->idempresa, $this->factura->idfacturacli, $this->factura->idcliente);
                $this->new_message("Nota de venta modificada correctamente.");
            } else {
                $this->new_error_msg("Error al editar la Nota de venta, verifique los datos y vuelva a intentarlo");
            }
        } else {
            $this->new_error_msg("Nota de venta no Encontrada");
        }
    }
}
