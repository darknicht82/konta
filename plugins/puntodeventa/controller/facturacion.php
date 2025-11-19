<?php
/**
 * Controlador de Administrador -> Empresas
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class facturacion extends controller
{
    //Filtros
    public $idempresa;
    public $modtotal;
    //modelos
    public $establecimiento;
    public $usuarios;
    public $cajas;
    public $formaspago;
    //variables
    public $arqueo;
    public $cliente_s;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Facturación', 'Punto de Venta', true, true, false, 'bi bi-cart4');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        $this->impresion = $this->user->have_access_to('impresion_ventas');

        if (isset($_POST['aperturar_caja'])) {
            $this->aperturar_caja();
        } else if (isset($_POST['m001'])) {
            $this->cerrar_caja();
        }

        $this->arqueo = false;
        //Busco si existe un cierre aperturado por el usuario loggeado
        $arq = $this->cierres->getCierreUsuario($this->idempresa, $this->user->nick);
        if ($arq) {
            $this->arqueo = $arq;
        }

        if ($this->arqueo) {
            //Realizo las ventas y demas movimientos de la caja
            //Busco el consumidor Final
            $cli = $this->clientes->get_ConsFinal($this->idempresa);
            if ($cli) {
                $this->cliente_s = $cli;
            }

            if (isset($_POST['numlineas'])) {
                $this->nueva_factura_venta();
            } else if (isset($_GET['buscar_cliente'])) {
                $this->buscar_clientes();
            } else if (isset($_POST['idcliente'])) {
                $this->buscar_cliente();
            } else if (isset($_REQUEST['tipoid_b'])) {
                $this->consulta_servidor();
            } else if (isset($_POST['identificacion'])) {
                $this->crear_cliente();
            } else if (isset($_POST['codigobarras'])) {
                $this->buscar_codigobarras();
            } else if (isset($_GET['buscar_articulo'])) {
                $this->buscar_articulos();
            } else if (isset($_POST['idarticulo'])) {
                $this->buscar_articulo();
            } else if (isset($_GET['articulos_v'])) {
                $this->validar_stock();
            } else if (isset($_POST['idfp'])) {
                $this->buscar_forma_pago();
            } else if (isset($_POST['idcierre'])) {
                $this->historial_cierre();
            } else if (isset($_POST['b_movs'])) {
                $this->historial_movs();
            } else if (isset($_GET['reimprimir'])) {
                $this->reimprimir_ticket();
            } else if (isset($_GET['reimprimir_mov'])) {
                $this->reimprimir_movimiento();
            } else if (isset($_POST['t_mov'])) {
                $this->nuevo_movimiento_caja();
            } else if (isset($_POST['presentacion'])) {
                $this->buscar_presentaciones();
            } else if (isset($_POST['b_grupos'])) {
                $this->buscar_grupos();
            } else if (isset($_POST['b_subgrupos'])) {
                $this->buscar_subgrupos();
            } else if (isset($_POST['b_artics'])) {
                $this->busca_arts();
            }
        } else {
            $this->new_advice('Ningún cierre de Caja encontrado, debe abrir una caja para poder Facturar.');
        }
    }

    private function init_modelos()
    {
        $this->cierres       = new cierres();
        $this->cajas         = new cajas();
        $this->clientes      = new clientes();
        $this->cliente_s     = new clientes();
        $this->formaspago    = new formaspago();
        $this->documentos    = new documentos();
        $this->articulos     = new articulos();
        $this->facturascli   = new facturascli();
        $this->mov_cajas     = new mov_cajas();
        $this->trans_cobros  = new trans_cobros();
        $this->parametros    = new \parametrizacion();
        $this->enviar_correo = new envio_correos();
        $this->grupos        = new grupos();
        $this->paso_um       = false;
        if (complemento_exists('unidadesmedida')) {
            $this->paso_um      = true;
            $this->unidades     = new unidades_medida();
            $this->art_unidades = new articulos_unidades();
        }
        if (complemento_exists('cuadre_producto')) {
            $this->articulos_cuadre = new articulos_cuadre();
        }
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
        $this->modtotal  = 0;
        $mtparam         = $this->parametros->all_by_codigo($this->idempresa, 'modtotal');
        if ($mtparam) {
            $this->modtotal = $mtparam->valor;
        }
        $this->reimpresion = 1;
        $rtparam           = $this->parametros->all_by_codigo($this->idempresa, 'reimpresion');
        if ($rtparam) {
            $this->reimpresion = $rtparam->valor;
        }
        $this->mtipodoc = 1;
        $mtdparam       = $this->parametros->all_by_codigo($this->idempresa, 'mtipodoc');
        if ($mtdparam) {
            $this->mtipodoc = $mtdparam->valor;
        }
        $this->autorizacion = 0;
        $autparam           = $this->parametros->all_by_codigo($this->idempresa, 'autorizacion');
        if ($autparam) {
            $this->autorizacion = $autparam->valor;
        }

        $this->mosmenu = 0;
        $menuparam     = $this->parametros->all_by_codigo($this->idempresa, 'mosmenu');
        if ($menuparam) {
            $this->mosmenu = $menuparam->valor;
        }
    }

    private function aperturar_caja()
    {
        $caja = $this->cierres->getCajaAbierta($_POST['aperturar_caja']);
        if (!$caja) {
            //realizo la apertura de la caja
            $cierre                    = new cierres();
            $cierre->idempresa         = $this->empresa->idempresa;
            $cierre->idestablecimiento = $_POST['idestablecimiento'];
            $cierre->idcaja            = $_POST['aperturar_caja'];
            $cierre->nick              = $this->user->nick;
            $cierre->inicial           = $_POST['inicial'];
            $cierre->apertura          = date('Y-m-d H:i:s');
            $cierre->fec_creacion      = date('Y-m-d');
            $cierre->nick_creacion     = $this->user->nick;
            if ($cierre->save()) {
                $this->new_message("Caja Aperturada correctamente.");
            } else {
                $this->new_error_msg("Error al aperturar la caja, verifique los datos y vuelva a intentar.");
            }
        } else {
            $this->new_advice("La caja no se encuentra disponible. Ya fue aperturada por otro usuario.");
        }
    }

    private function cerrar_caja()
    {
        $cierre = $this->cierres->get($_POST['idcierrecaja']);
        if ($cierre) {
            $cierre->cierre            = date('Y-m-d H:i:s');
            $cierre->m001              = $_POST['m001'];
            $cierre->m005              = $_POST['m005'];
            $cierre->m010              = $_POST['m010'];
            $cierre->m025              = $_POST['m025'];
            $cierre->m050              = $_POST['m050'];
            $cierre->m1                = $_POST['m1'];
            $cierre->b1                = $_POST['b1'];
            $cierre->b5                = $_POST['b5'];
            $cierre->b10               = $_POST['b10'];
            $cierre->b20               = $_POST['b20'];
            $cierre->b50               = $_POST['b50'];
            $cierre->b100              = $_POST['b100'];
            $cierre->totalemp          = $_POST['totalc'];
            $cierre->fec_modificacion  = date('Y-m-d');
            $cierre->nick_modificacion = $this->user->nick;

            if ($cierre->save()) {
                //si esta activo guardo el cuadre de producto
                if (complemento_exists('cuadre_producto')) {
                    foreach ($this->articulos->getArticulosCuadre($this->idempresa) as $key => $val) {
                        if (isset($_POST['cuadre_' . $val['idarticulo']])) {
                            $art_cuadre                = new articulos_cuadre();
                            $art_cuadre->idempresa     = $this->idempresa;
                            $art_cuadre->idcaja        = $cierre->idcaja;
                            $art_cuadre->idcierre      = $cierre->idcierre;
                            $art_cuadre->idarticulo    = $val['idarticulo'];
                            $art_cuadre->stockfinal    = floatval($_POST['cuadre_' . $val['idarticulo']]);
                            $art_cuadre->fec_creacion  = date('Y-m-d');
                            $art_cuadre->nick_creacion = $this->user->nick;
                            if (!$art_cuadre->save()) {
                                $this->new_error_msg('Error al Guardar el cuadre de Producto.');
                            }

                        }
                    }
                }
                $this->new_message("Caja Cerrada correctamente.");
                echo "<script>window.open('index.php?page=impresion_tickets&cierre=" . $cierre->idcierre . "', '', 'width=300, height=200');</script>";
                header('Refresh: 0; URL=' . $this->url());
            } else {
                $this->new_error_msg("Error al cerrar la caja, verifique los datos y vuelva a intentar.");
            }
        } else {
            $this->new_advice("Cierre de Caja no Encontrado.");
        }
    }

    private function buscar_forma_pago()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Forma de Pago No encontrado.');
        $fp             = $this->formaspago->get($_POST['idfp']);
        if ($fp) {
            $result = array('error' => 'F', 'msj' => '', 'fpago' => $fp);
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_clientes()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_clientes($this->idempresa, $_GET['buscar_cliente']);

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

    private function consulta_servidor()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error en la consulta');

        $prov = $this->clientes->get_by_identificacion($this->idempresa, $_REQUEST['identificacion_b']);

        if ($prov) {
            $result = array('error' => 'R', 'msj' => 'El cliente ya se encuentra registrado, utilice el buscador para registrarlo.');
        } else {
            if ($_REQUEST['tipoid_b'] == 'C') {
                $result = consultarCedula($_REQUEST['identificacion_b']);
            } else if ($_REQUEST['tipoid_b'] == 'R') {
                $result = consultarRucSri($_REQUEST['identificacion_b']);
            }
        }

        echo json_encode($result);
        exit;
    }

    private function crear_cliente()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error en la consulta');
        if (!$this->clientes->get_by_identificacion($this->idempresa, $_POST['identificacion'])) {
            $cliente                 = new clientes();
            $cliente->idempresa      = $this->user->idempresa;
            $cliente->identificacion = $_POST['identificacion'];
            $cliente->tipoid         = $_POST['tipoid'];
            $cliente->razonsocial    = $_POST['razonsocial'];
            if ($_POST['nombrecomercial'] != '') {
                $cliente->nombrecomercial = $_POST['nombrecomercial'];
            } else {
                $cliente->nombrecomercial = $_POST['razonsocial'];
            }
            $cliente->telefono = $_POST['telefono'];
            if ($_POST['celular'] != '') {
                $cliente->celular = $_POST['celular'];
            }
            $cliente->email         = $_POST['email'];
            $cliente->direccion     = $_POST['direccion'];
            $cliente->regimen       = $_POST['regimen'];
            $cliente->obligado      = isset($_POST['obligado']);
            $cliente->agretencion   = isset($_POST['agretencion']);
            $cliente->fec_creacion  = date('Y-m-d');
            $cliente->nick_creacion = $this->user->nick;

            if ($cliente->save()) {
                $result = array('error' => 'F', 'msj' => "Cliente creado correctamente.", 'cliente' => $cliente);
            } else {
                $result = array('error' => 'T', 'msj' => "No se pudo crear el cliente, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $result = array('error' => 'T', 'msj' => "El cliente con identificacion " . $_POST['identificacion'] . " ya se encuentra registrado.");
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

    private function buscar_articulos()
    {
        $this->template = false;
        $articulos      = buscar_articulos($this->empresa->idempresa, $_GET['buscar_articulo'], '', '', '', '', $_GET['establecimiento'], true, '', true);
        echo json_encode($articulos);
        exit;
    }

    private function buscar_articulo()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Artículo No encontrado.');
        $articulos      = new articulos(false, $_POST['establecimiento']);
        $articulo       = $articulos->get($_POST['idarticulo']);
        if ($articulo) {
            if ($articulo->tipo == 1 && $articulo->controlar_stock == 1) {
                //Verifico el stock
                if (floatval($articulo->stock_fisico) > 0) {
                    $result = array('error' => 'F', 'msj' => '', 'art' => $articulo);
                } else {
                    $result = array('error' => 'T', 'msj' => 'El articulo ' . $articulo->nombre . " no tiene el stock suficiente. Stock Actual: " . $articulo->stock_fisico);
                }
            } else {
                $result = array('error' => 'F', 'msj' => '', 'art' => $articulo);
            }
        }
        echo json_encode($result);
        exit;
    }

    private function validar_stock()
    {
        $this->template = false;
        $result         = array('error' => 'F', 'msj' => '');
        $articulos      = new articulos(false, $_GET['establecimiento']);
        $datos          = json_decode(stripslashes($_GET['articulos_v']));
        $msj            = "";
        foreach ($datos as $key => $d) {
            $articulo = $articulos->get($d[0]);
            if ($articulo) {
                if ($articulo->tipo == 1 && $articulo->controlar_stock == 1) {
                    if (floatval($d[1]) > floatval($articulo->stock_fisico)) {
                        $msj .= "El articulo " . $articulo->nombre . " no tiene el stock suficiente. Stock Actual: " . $articulo->stock_fisico . "\n";
                    }
                }
            }
        }
        if ($msj != "") {
            $result = array('error' => 'T', 'msj' => $msj);
        }
        echo json_encode($result);
        exit;
    }

    private function nueva_factura_venta()
    {
        $cli = $this->clientes->get($_POST['idcliente']);
        if ($cli) {
            $factura                      = new facturascli();
            $factura->idempresa           = $this->idempresa;
            $factura->idcliente           = $_POST['idcliente'];
            $factura->tipoid              = $cli->tipoid;
            $factura->identificacion      = $cli->identificacion;
            $factura->razonsocial         = $cli->razonsocial;
            $factura->email               = $_POST['email'];
            $factura->direccion           = $_POST['direccion'];
            $factura->regimen_empresa     = $this->empresa->regimen;
            $factura->obligado_empresa    = $this->empresa->obligado;
            $factura->agretencion_empresa = $this->empresa->agretencion;
            //Busco el id Documento para almacenar
            $paso = true;
            if (complemento_exists('manejo_notasventa')) {
                if ($factura->tipoid == 'F') {
                    $lineasfp = floatval($_POST['lineasfp']);
                    $esefec   = false;
                    $cantid   = 0;
                    for ($j = 0; $j < $lineasfp; $j++) {
                        if (isset($_POST['idformapago_' . $j])) {
                            $cantid++;
                            $fp = $this->formaspago->get($_POST['idformapago_' . $j]);
                            if ($fp) {
                                if ($fp->esefec) {
                                    $esefec = true;
                                } else {
                                    $esefec = false;
                                    break;
                                }
                            }
                        }
                    }

                    if ($esefec && $cantid == 1) {
                        $documento = $this->documentos->get_by_codigo('02');
                        if (!$documento) {
                            $this->new_error_msg('Documento 02 no encontrado, verifique en los tipos de documentos.');
                            return;
                        }
                        $paso = false;
                    }
                }
            }

            if ($paso) {
                if (isset($_POST['isnota'])) {
                    $documento = $this->documentos->get_by_codigo('02');
                    if (!$documento) {
                        $this->new_error_msg('Documento 02 no encontrado, verifique en los tipos de documentos.');
                        return;
                    }
                } else {
                    $documento = $this->documentos->get_by_codigo('01');
                    if (!$documento) {
                        $this->new_error_msg('Documento 01 no encontrado, verifique en los tipos de documentos.');
                        return;
                    }
                }
            }
            $factura->iddocumento       = $documento->iddocumento;
            $factura->coddocumento      = $documento->codigo;
            $factura->idestablecimiento = $this->arqueo->idestablecimiento;
            $factura->fec_emision       = date('Y-m-d');
            $factura->hora_emision      = date('H:i:s');
            $factura->fec_registro      = date('Y-m-d');
            $factura->diascredito       = 1;
            $factura->idcaja            = $this->arqueo->idcierre;
            $factura->fec_creacion      = date('Y-m-d');
            $factura->nick_creacion     = $this->user->nick;
            if ($_POST['observaciones'] != '') {
                $factura->observaciones = $_POST['observaciones'];
            }

            if ($factura->save()) {
                //Si se guarda la Factura almaceno el detalle
                $lineas = floatval($_POST['numlineas']);
                for ($i = 0; $i < $lineas; $i++) {
                    if (isset($_POST['idarticulo_' . $i])) {
                        $articulo = $this->articulos->get($_POST['idarticulo_' . $i]);
                        if ($articulo) {
                            $linea               = new lineasfacturascli();
                            $linea->idfacturacli = $factura->idfacturacli;
                            $linea->idarticulo   = $_POST['idarticulo_' . $i];
                            $linea->idimpuesto   = $_POST['idimpuesto_' . $i];
                            $linea->codprincipal = $articulo->codprincipal;
                            $linea->descripcion  = $_POST['descripcion_' . $i];
                            $linea->cantidad     = $_POST['cantidad_' . $i];
                            $linea->pvpunitario  = $_POST['pvpunitario_' . $i];
                            $linea->dto          = $_POST['dto_' . $i];
                            $linea->pvptotal     = $_POST['pvptotal_' . $i];
                            $linea->pvpsindto    = $linea->cantidad * $linea->pvpunitario;
                            if (complemento_exists('unidadesmedida')) {
                                if ($_POST['idunidad_' . $i] != 'null') {
                                    $linea->idunidad = $_POST['idunidad_' . $i];
                                }
                                $linea->factor = $_POST['factor_' . $i];
                            }
                            //$linea->valorice      = $_POST['valorice'];
                            $linea->valoriva      = $_POST['valoriva_' . $i];
                            $linea->fec_creacion  = date('Y-m-d');
                            $linea->nick_creacion = $this->user->nick;

                            if ($linea->save()) {
                                switch ($linea->get_impuesto()) {
                                    case 'IVANO':
                                        //Base no Objeto de IVA
                                        $factura->base_noi += $linea->pvptotal;
                                        break;
                                    case 'IVA0':
                                        //Base 0
                                        $factura->base_0 += $linea->pvptotal;
                                        break;
                                    case 'IVAEX':
                                        //Base Excento de IVA
                                        $factura->base_exc += $linea->pvptotal;
                                        break;
                                    default:
                                        //Base Gravada
                                        $factura->base_gra += $linea->pvptotal;
                                        break;
                                }
                                $factura->totaldescuento += ($linea->pvpsindto - $linea->pvptotal);
                                $factura->totalice += $linea->valorice;
                                $factura->totaliva += $linea->valoriva;
                            } else {
                                $this->new_advice('Error al Guardar la linea de la factura.');
                                $msj = '';
                                if ($linea->get_errors()) {
                                    foreach ($linea->get_errors() as $key => $val) {
                                        $msj .= "\n" . $val;
                                    }
                                }
                                $this->new_error_msg($msj);
                                $factura->delete();
                                return;
                            }
                        } else {
                            $this->new_advice("Articulo no Encontrado");
                            $factura->delete();
                            return;
                        }
                    }
                }

                if ($factura->save()) {
                    //Almaceno los cobros de la factura
                    $lineasfp = floatval($_POST['lineasfp']);
                    for ($j = 0; $j < $lineasfp; $j++) {
                        if (isset($_POST['idformapago_' . $j])) {
                            $fp = $this->formaspago->get($_POST['idformapago_' . $j]);
                            if ($fp) {
                                if (!$fp->escredito) {
                                    $cobro               = new \trans_cobros();
                                    $cobro->idempresa    = $this->idempresa;
                                    $cobro->idcliente    = $factura->idcliente;
                                    $cobro->idfacturacli = $factura->idfacturacli;
                                    $cobro->idformapago  = $_POST['idformapago_' . $j];
                                    $cobro->tipo         = 'Cobro';
                                    $cobro->fecha_trans  = $_POST['fec_trans_' . $j];
                                    if (isset($_POST['numdoc_' . $j]) && $_POST['numdoc_' . $j] != '') {
                                        $cobro->num_doc = $_POST['numdoc_' . $j];
                                    }
                                    $cobro->credito       = $_POST['valorreal_' . $j];
                                    $cobro->esabono       = false;
                                    $cobro->idcaja        = $this->arqueo->idcierre;
                                    $cobro->fec_creacion  = date('Y-m-d');
                                    $cobro->nick_creacion = $this->user->nick;
                                    $cobro->save();
                                }
                            }
                        }
                    }
                    //Actualizo los datos del cliente si se marca la opcion de actualizar los datos
                    if (isset($_POST['act_cliente'])) {
                        $cli->direccion         = $_POST['direccion'];
                        $cli->telefono          = $_POST['telefono'];
                        $cli->email             = $_POST['email'];
                        $cli->fec_modificacion  = date('Y-m-d');
                        $cli->nick_modificacion = $this->user->nick;
                        $cli->save();
                    }

                    if (JG_ECAUTE != 1) {
                        $this->new_message('Factura guardada correctamente.');
                        echo "<script>window.open('index.php?page=impresion_tickets&facturacli=" . $factura->idfacturacli . "', '', 'width=300, height=200');</script>";
                        header('Refresh: 0; URL=' . $this->url());
                    } else {
                        $this->new_message('Prefactura guardada correctamente.');
                        echo "<script>window.open('index.php?page=impresion_ventas&tipo=fact&id=" . $factura->idfacturacli . "', '');</script>";
                        header('Refresh: 0; URL=' . $this->url());
                    }

                    //Si esta activa la autorizacion lo realizo
                    if ($this->autorizacion == 1 && $factura->coddocumento != '02') {
                        $this->procesar_autorizacion($factura);
                    }
                } else {
                    $this->new_error_msg('Error al actualizar los totales de la Factura.');
                    $linea->delete();
                    $factura->delete();
                    return;
                }
            } else {
                $this->new_error_msg("No se pudo crear la cabecera de la factura, verifique los datos y vuelva a intentarlo.");
            }
        } else {
            $this->new_advice("No se encuentra el Cliente seleccionado.");
        }
    }

    private function historial_cierre()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Caja No encontrada.');
        if ($this->arqueo) {
            $facturas = $this->facturascli->getbycierre($this->idempresa, $this->arqueo->idcierre);
            if ($facturas) {
                $result = array('error' => 'F', 'msj' => '', 'facs' => $facturas);
            } else {
                $result = array('error' => 'T', 'msj' => 'Sin Historial.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function historial_movs()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => '');
        if ($this->arqueo) {
            $movs = $this->mov_cajas->get_by_idempresa_caja($this->idempresa, $this->arqueo->idcierre);
            if ($movs) {
                $result = array('error' => 'F', 'msj' => '', 'movs' => $movs);
            } else {
                $result = array('error' => 'T', 'msj' => '');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function reimprimir_ticket()
    {
        if ($this->reimpresion == 1) {
            $factura = $this->facturascli->get($_GET['reimprimir']);
            if ($factura) {
                if ($factura->idempresa == $this->idempresa) {
                    if ($factura->coddocumento == '02' && JG_ECAUTE == 1) {
                        echo "<script>window.open('index.php?page=impresion_ventas&tipo=fact&id=" . $factura->idfacturacli . "', '');</script>";
                        header('Refresh: 0; URL=' . $this->url());
                    } else {
                        echo "<script>window.open('index.php?page=impresion_tickets&reimprimir&facturacli=" . $factura->idfacturacli . "', '', 'width=300, height=200');</script>";
                        header('Refresh: 0; URL=' . $this->url());
                    }
                } else {
                    $this->new_advice('La factura no es válida para su empresa.');
                }
            } else {
                $this->new_advice('Factura no encontrada.');
            }
        } else {
            $this->new_advice('No tiene permiso para realizar la reimpresión.');
        }
    }

    private function reimprimir_movimiento()
    {
        $mov = $this->mov_cajas->get($_GET['reimprimir_mov']);
        if ($mov) {
            if ($mov->idempresa == $this->idempresa) {
                echo "<script>window.open('index.php?page=impresion_tickets&reimprimir&movimiento=" . $mov->idmovcaja . "', '', 'width=300, height=200');</script>";
                header('Refresh: 0; URL=' . $this->url());
            } else {
                $this->new_advice('El movimiento no es válida para su empresa.');
            }
        } else {
            $this->new_advice('Movimiento no encontrada.');
        }
    }

    private function nuevo_movimiento_caja()
    {
        $mov                = new mov_cajas();
        $mov->idempresa     = $this->idempresa;
        $mov->idcierre      = $this->arqueo->idcierre;
        $mov->nombre        = $_POST['n_mov'];
        $mov->tipo          = $_POST['t_mov'];
        $mov->valor         = $_POST['v_mov'];
        $mov->fec_creacion  = date('Y-m-d');
        $mov->nick_creacion = $this->user->nick;

        if ($mov->save()) {
            $this->new_message("Movimiento Guardado correctamente");
            echo "<script>window.open('index.php?page=impresion_tickets&movimiento=" . $mov->idmovcaja . "', '', 'width=300, height=200');</script>";
            header('Refresh: 0; URL=' . $this->url());
        } else {
            $this->new_advice("Error al guardar el Movimiento.");
        }
    }

    private function buscar_presentaciones()
    {
        $this->template = false;
        $lista          = array();
        $result         = array('error' => 'T', 'msj' => 'Artículo no encontrado.');
        $articulo       = $this->articulos->get($_POST['presentacion']);
        if ($articulo) {
            $presentaciones = $this->art_unidades->all_by_idarticulo($_POST['presentacion'], $this->idempresa);
            if ($presentaciones) {
                foreach ($presentaciones as $key => $pre) {
                    $unitario = round($pre->cantidad * $pre->precio, 6);
                    $pvpiva   = round($unitario * (1 + ($articulo->get_porcentaje_impuesto() / 100)), 2);
                    $lista[]  = array('idunidad' => $pre->idunidad, 'descripcion' => $pre->medida, 'factor' => $pre->cantidad, 'precio' => $unitario, 'total' => $pvpiva);
                }
                $lista[] = array('idunidad' => $articulo->idunidad, 'descripcion' => $articulo->medida, 'factor' => 1, 'precio' => $articulo->precio, 'total' => $articulo->get_precio_iva());
                $result  = array('error' => 'F', 'msj' => '', 'presentaciones' => $lista);
            } else {
                $result = array('error' => 'T', 'msj' => 'Unidades de Medida no encontradas. Artículo: ' . $articulo->nombre);
            }
        }
        echo json_encode($result);
        exit;
    }

    private function procesar_autorizacion($factura)
    {
        if ($this->empresa->activafacelec) {
            if ($factura) {
                if ($factura->anulado) {
                    $this->new_advice("No se puede realizar la autorización, el documento se encuentra anulado.");
                    return false;
                } else if ($factura->saldoinicial) {
                    $this->new_advice('El documento es un saldo inicial, no se puede realizar la autorización en el SRI.');
                    return;
                } else if ($factura->coddocumento == '02') {
                    return;
                }
                if ($factura->getlineas()) {
                    if ($factura->estado_sri != 'AUTORIZADO') {
                        $autorizar_sri = new autorizar_sri();
                        if ($factura->coddocumento == '01') {
                            $carpeta = 'facturas';
                        } else {
                            $this->new_error_msg('Documento No encontrado.');
                            return;
                        }
                        $rutaXmlFirmado = JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/documentosElectronicos/" . $carpeta . "/autorizados/";
                        $archivoFirmado = $rutaXmlFirmado . $factura->numero_documento . ".xml";
                        if (!file_exists($archivoFirmado)) {
                            $result = $autorizar_sri->procesar_documento_sri($factura, $this->empresa);
                            if ($result['error'] == 'F') {
                                if (file_exists($archivoFirmado)) {
                                    $archivoXml = simplexml_load_file($archivoFirmado);
                                    if ($archivoXml) {
                                        $factura->tipoemision      = 'E';
                                        $factura->estado_sri       = $archivoXml->estado;
                                        $factura->fec_autorizacion = substr($archivoXml->fechaAutorizacion, 0, 10);
                                        $factura->hor_autorizacion = substr($archivoXml->fechaAutorizacion, 11, 8);
                                        if ($factura->save()) {
                                            //Genero el envio de correo electronico
                                            $correo = $this->enviar_correo->correo_docs_ventas($factura, $this->empresa, false);
                                            if ($correo['error'] == 'T') {
                                                $this->new_advice("Comprobante Autorizado Correctamente, No se pudo enviar el correo electrónico. Detalle:" . $correo['msj']);
                                            } else {
                                                $this->new_message("Documento Autorizado Correctamente");
                                            }
                                        } else {
                                            $this->new_error_msg("Existió un inconveniente al autorizar el documento.");
                                        }
                                    }
                                } else {
                                    $this->new_error_msg("Documento firmado no encontrado");
                                }
                            } else {
                                $this->new_error_msg($result['msj']);
                            }
                        } else {
                            $archivoXml = simplexml_load_file($archivoFirmado);
                            if ($archivoXml) {
                                $factura->tipoemision      = 'E';
                                $factura->estado_sri       = $archivoXml->estado;
                                $factura->fec_autorizacion = substr($archivoXml->fechaAutorizacion, 0, 10);
                                $factura->hor_autorizacion = substr($archivoXml->fechaAutorizacion, 11, 8);
                                if ($factura->save()) {
                                    $this->new_advice("El documento ya se encuentra autorizado, verifique el estado.");
                                } else {
                                    $this->new_error_msg("Error al actualizar el estado del documento.!");
                                }
                            }
                        }
                    } else {
                        $this->new_advice("El documento ya se encuentra autorizado.!");
                    }
                } else {
                    $this->new_error_msg("El documento no tiene items, no se puede autorizar.!");
                }
            } else {
                $this->new_advice("Documento No Encontrada.");
            }
        }
    }

    private function buscar_grupos()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Menú no encontrado, puede configurarlo dentro de la configuración de los Artículos.');
        $grupos         = $this->grupos->getMenu($this->idempresa);
        if ($grupos) {
            $result = array('error' => 'F', 'msj' => '', 'grupos' => $grupos);
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_subgrupos()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'SubMenú no encontrado, verifique en la Configuracion de los Articulos.');
        $subgrupos      = $this->grupos->getSubMenu($this->idempresa, $_POST['b_subgrupos']);
        if ($subgrupos) {
            $grupo   = $this->grupos->get($_POST['b_subgrupos']);
            $idpadre = '';
            if ($grupo && $grupo->idpadre) {
                $idpadre = $grupo->idpadre;
            }
            $result = array('error' => 'F', 'msj' => '', 'grupos' => $subgrupos, 'idpadre' => $idpadre);
        }
        echo json_encode($result);
        exit;
    }

    private function busca_arts()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Articulos no encontrados, verifique la lista de los Articulos.');
        $articulos      = new articulos(false, $_POST['establecimiento']);
        $arts           = $articulos->getItemsVentaPorGrupo($this->idempresa, $_POST['b_artics']);
        if ($arts) {
            $grupo   = $this->grupos->get($_POST['b_artics']);
            $idpadre = '';
            if ($grupo && $grupo->idpadre) {
                $idpadre = $grupo->idpadre;
            }
            $result = array('error' => 'F', 'msj' => '', 'articulos' => $arts, 'idpadre' => $idpadre);
        }
        echo json_encode($result);
        exit;
    }
}
