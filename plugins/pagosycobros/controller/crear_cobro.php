<?php
/**
 * Controlador de Ventas -> Nuevo Cobro.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class crear_cobro extends controller
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
        parent::__construct(__CLASS__, 'Nuevo Cobro', 'Ventas', false, false, false, 'bi bi-cash-stack');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        $this->impresion = $this->user->have_access_to('impresion_ventas');

        $this->resultados = array();
        if ($this->idcliente != '') {
            $this->buscar_resultados();
        }
        //Genero las funcionalidades
        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_POST['gen_cobro'])) {
            $this->nuevo_cobro();
        } else if (isset($_POST['idfp'])) {
            $this->buscar_forma_pago();
        } else if (isset($_GET['imprimir'])) {
            $this->impresiones();
        }
    }

    private function init_modelos()
    {
        $this->clientes     = new clientes();
        $this->trans_cobros = new trans_cobros();
        $this->formaspago   = new formaspago();
        $this->cab_cobros   = new cab_cobros();
        $this->facturascli  = new facturascli();
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
        $this->resultados = $this->trans_cobros->pendientesCobro($this->idempresa, $this->idcliente);
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

    public function nuevo_cobro()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Cliente no encontrado.');

        //verifico si existe el cliente seleccionado
        $cliente = $this->clientes->get($_REQUEST['clienteselect']);
        if ($cliente) {
            if (isset($_POST['num_doc'])) {
                if (trim($_POST['num_doc']) != '') {
                    //valido si el numero de documento no se encuentra ya registrado
                    $numDoc = $this->trans_cobros->getNumDocCliente($this->idempresa, $cliente->idcliente, $_POST['fpselec'], $_POST['num_doc']);
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

            $cobro              = new \cab_cobros();
            $cobro->idempresa   = $this->idempresa;
            $cobro->idcliente   = $cliente->idcliente;
            $cobro->idformapago = $_POST['fpselec'];
            $cobro->fecha_trans = $_POST['fec_trans'];
            if (isset($_POST['num_doc'])) {
                $cobro->num_doc = $_POST['num_doc'];
            }
            if (isset($_POST['observaciones']) && $_POST['observaciones'] != '') {
                $cobro->observaciones = $_POST['observaciones'];
            }
            $cobro->fec_creacion  = date('Y-m-d');
            $cobro->nick_creacion = $this->user->nick;

            $totalcobro = 0;
            $correcto   = true;
            if ($cobro->save()) {
                foreach ($_POST['idfacturas'] as $key => $idfactura) {
                    if (isset($_POST['valor_' . $idfactura])) {
                        $factura = $this->facturascli->get($idfactura);
                        if ($factura) {
                            if (floatval($_POST['valor_' . $idfactura]) > 0) {
                                $tcobro                = new \trans_cobros();
                                $tcobro->idempresa     = $this->idempresa;
                                $tcobro->idcliente     = $cobro->idcliente;
                                $tcobro->idfacturacli  = $idfactura;
                                $tcobro->idformapago   = $cobro->idformapago;
                                $tcobro->tipo          = 'Cobro';
                                $tcobro->fecha_trans   = $cobro->fecha_trans;
                                $tcobro->num_doc       = $cobro->num_doc;
                                $tcobro->credito       = floatval($_POST['valor_' . $idfactura]);
                                $tcobro->esabono       = true;
                                $tcobro->idcobro       = $cobro->idcobro;
                                $tcobro->fec_creacion  = date('Y-m-d');
                                $tcobro->nick_creacion = $this->user->nick;
                                if (!$tcobro->save()) {
                                    $result   = array('error' => 'T', 'msj' => 'Error al generar el Cobro en la Factura.');
                                    $correcto = false;
                                    break;
                                } else {
                                    $totalcobro += $tcobro->credito;
                                }
                            }
                        } else {
                            $result   = array('error' => 'T', 'msj' => 'Factura no Encontrada, verifique los datos y vuelva a intentarlo.');
                            $correcto = false;
                            break;
                        }
                    }
                }
                if ($correcto) {
                    $cobro->valor = $totalcobro;
                    if ($cobro->save()) {
                        $result = array('error' => 'F', 'msj' => 'Cobro Generado correctamente.', 'url' => $this->url() . '&idcliente=' . $cobro->idcliente . '&imprimir=' . $cobro->idcobro);
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al actualizar el valor del Cobro.');
                    }
                } else {
                    if (!$cobro->delete()) {
                        $mensaje = 'Error: ';
                        foreach ($cobro->get_errors() as $key => $error) {
                            $mensaje .= $error . ". ";
                        }

                        if ($mensaje != 'Error: ') {
                            $result['msj'] .= $mensaje;
                        }
                    }
                }
            } else {
                $mensaje = ' Error: ';
                foreach ($cobro->get_errors() as $key => $error) {
                    $mensaje .= $error . ". ";
                }
                $msj = '';
                if ($mensaje != ' Error: ') {
                    $msj .= $mensaje;
                }
                $result = array('error' => 'T', 'msj' => 'Error al generar la Cabecera del Cobro.'.$msj);
                echo json_encode($result);
                exit;
            }
        }

        echo json_encode($result);
        exit;
    }

    private function impresiones()
    {
        $mensaje = '';
        if ($this->impresion) {
            $mensaje = 'Imprima el comprobante presionando <a href="index.php?page=impresion_ventas&imprimir_cobro=' . $_GET['imprimir'] . '" target="_blank">aquí</a>';
        }

        if ($mensaje != '') {
            $this->new_message($mensaje);
        }
    }
}
