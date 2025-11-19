<?php
/**
 * Controlador de Compras -> Anticipos -> Nueva Devolucion.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class crear_devolucion_cliente extends controller
{
    //Filtros
    public $idempresa;
    public $cantidad;
    public $filtros;
    public $offset;
    public $nom_cliente;
    public $idcliente;
    //Modelos
    public $trans_cobros;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Nueva Devolucion', 'Ventas', false, false, false, 'bi bi-cash-stack');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        $this->impresion = $this->user->have_access_to('impresion_ventas');

        $this->resultados = array();
        //Genero las funcionalidades
        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_POST['gen_devolucion'])) {
            $this->nueva_devolucion();
        } else if (isset($_POST['idfp'])) {
            $this->buscar_forma_pago();
        } else if (isset($_GET['imprimir'])) {
            $this->impresiones();
        }

        $this->buscar_resultados();
    }

    private function init_modelos()
    {
        $this->clientes               = new clientes();
        $this->formaspago             = new formaspago();
        $this->cab_devolucion_cliente = new cab_devolucion_cliente();
        $this->facturascli            = new facturascli();
        $this->anticiposcli           = new anticiposcli();
        $this->retencionescli         = new retencionescli();
        $this->trans_cobros           = new trans_cobros();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;

        $this->nom_cliente = '';
        $this->idcliente   = '';
        if (isset($_REQUEST['idcliente']) && $_REQUEST['idcliente'] != '') {
            $cliente = $this->clientes->get($_REQUEST['idcliente']);
            if ($cliente) {
                $this->idcliente   = $cliente->idcliente;
                $this->nom_cliente = $cliente->identificacion . " - " . $cliente->razonsocial;
            }
        }
    }

    private function buscar_cliente()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_clientes($this->idempresa, $_GET['buscar_cliente']);

        echo json_encode($result);
        exit;
    }

    public function buscar_resultados()
    {
        $this->resultados = $this->trans_cobros->getSaldosFavor($this->idempresa, $this->idcliente);
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

    public function nueva_devolucion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error al generar la Devolución.');

        //verifico si existe el cliente seleccionado
        $idcliente = null;
        if ($_REQUEST['clienteselect'] != '') {
            $cliente = $this->clientes->get($_REQUEST['clienteselect']);
            if ($cliente) {
                $idcliente = $cliente->idcliente;
            }
        }

        if (isset($_POST['num_doc'])) {
            if (trim($_POST['num_doc']) != '') {
                //valido si el numero de documento no se encuentra ya registrado
                $numDoc = $this->cab_devolucion_cliente->getNumDoc($this->idempresa, $_POST['fpselec'], $_POST['num_doc']);
                if ($numDoc) {
                    $result = array('error' => 'T', 'msj' => 'El Número de Documento ya se encuentra registrado.');
                    echo json_encode($result);
                    exit;
                }
            } else {
                //Valido si no es valido el numero de
                $result = array('error' => 'T', 'msj' => 'Número de Documento no Válido.');
                echo json_encode($result);
                exit;
            }
        }

        $devol              = new \cab_devolucion_cliente();
        $devol->idempresa   = $this->idempresa;
        $devol->idcliente   = $idcliente;
        $devol->idformapago = $_POST['fpselec'];
        $devol->fecha_trans = $_POST['fec_trans'];
        if (isset($_POST['num_doc'])) {
            $devol->num_doc = $_POST['num_doc'];
        }
        if (isset($_POST['observaciones']) && $_POST['observaciones'] != '') {
            $devol->observaciones = $_POST['observaciones'];
        }
        $devol->fec_creacion  = date('Y-m-d');
        $devol->nick_creacion = $this->user->nick;

        $totaldev = 0;
        $correcto = true;
        if ($devol->save()) {
            foreach ($_POST['iddocumentos'] as $key => $iddocumento) {
                if (isset($_POST['valor_' . $iddocumento])) {
                    $data = explode("-", $iddocumento);
                    $doc  = false;
                    if ($data[0] == 'F') {
                        $doc           = $this->facturascli->get($data[1]);
                        $idfactura     = $doc->idfacturacli;
                        $idanticipocli = null;
                        $idretencion   = null;
                        $idcliented    = $doc->idcliente;
                    } else if ($data[0] == 'A') {
                        $doc           = $this->anticiposcli->get($data[1]);
                        $idfactura     = null;
                        $idanticipocli = $doc->idanticipocli;
                        $idretencion   = null;
                        $idcliented    = $doc->idcliente;
                    } else if ($data[0] == 'R') {
                        $doc           = $this->retencionescli->get($data[1]);
                        $idfactura     = null;
                        $idanticipocli = null;
                        $idretencion   = $doc->idretencion;
                        $idcliented    = $doc->idcliente;
                    }
                    if ($doc) {
                        if (floatval($_POST['valor_' . $iddocumento]) > 0) {
                            $tcobro                  = new \trans_cobros();
                            $tcobro->idempresa       = $this->idempresa;
                            $tcobro->idcliente       = $idcliented;
                            $tcobro->idfacturacli    = $idfactura;
                            $tcobro->idanticipocli   = $idanticipocli;
                            $tcobro->idretencion     = $idretencion;
                            $tcobro->idformapago     = $devol->idformapago;
                            $tcobro->tipo            = 'Devolucion';
                            $tcobro->fecha_trans     = $devol->fecha_trans;
                            $tcobro->num_doc         = $devol->num_doc;
                            $tcobro->debito          = floatval($_POST['valor_' . $iddocumento]);
                            $tcobro->esabono         = true;
                            $tcobro->iddevolucioncli = $devol->iddevolucioncli;
                            $tcobro->fec_creacion    = date('Y-m-d');
                            $tcobro->nick_creacion   = $this->user->nick;
                            if (!$tcobro->save()) {
                                $mensaje = ' Error: ';
                                foreach ($devol->get_errors() as $key => $error) {
                                    $mensaje .= $error . " - ";
                                }
                                $msj = '';
                                if ($mensaje != 'Error: ') {
                                    $msj .= $mensaje;
                                }
                                $result   = array('error' => 'T', 'msj' => 'Error al generar la Devolución del Cliente.' . $msj);
                                $correcto = false;
                                break;
                            } else {
                                $totaldev += $tcobro->debito;
                            }
                        }
                    } else {
                        $result   = array('error' => 'T', 'msj' => 'Documento de Devolución no Encontrado, verifique los datos y vuelva a intentarlo.');
                        $correcto = false;
                        break;
                    }
                }
            }
            if ($correcto) {
                $devol->valor = $totaldev;
                if ($devol->save()) {
                    $result = array('error' => 'F', 'msj' => 'Devolución Generada correctamente.', 'url' => $this->url() . '&idcliente=' . $devol->idcliente . '&imprimir=' . $devol->iddevolucioncli);
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al actualizar el valor del Devolución.');
                }
            } else {
                if (!$devol->delete()) {
                    $mensaje = ' Error: ';
                    foreach ($devol->get_errors() as $key => $error) {
                        $mensaje .= $error . " - ";
                    }

                    if ($mensaje != 'Error: ') {
                        $result['msj'] .= $mensaje;
                    }
                }
            }
        } else {
            $mensaje = 'Error: ';
            foreach ($devol->get_errors() as $key => $error) {
                $mensaje .= $error . " - ";
            }
            $msj = '';
            if ($mensaje != 'Error: ') {
                $msj .= $mensaje;
            }
            $result = array('error' => 'T', 'msj' => 'Error al generar la Cabecera de la Devolución. ' . $msj);
            echo json_encode($result);
            exit;
        }

        echo json_encode($result);
        exit;
    }

    private function impresiones()
    {
        $mensaje = '';
        if ($this->impresion) {
            $mensaje = 'Imprima el comprobante presionando <a href="index.php?page=impresion_ventas&imprimir_devol=' . $_GET['imprimir'] . '" target="_blank">aquí</a>';
        }

        if ($mensaje != '') {
            $this->new_message($mensaje);
        }
    }
}
