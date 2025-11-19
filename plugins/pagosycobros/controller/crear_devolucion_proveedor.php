<?php
/**
 * Controlador de Compras -> Anticipos -> Nueva Devolucion.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class crear_devolucion_proveedor extends controller
{
    //Filtros
    public $idempresa;
    public $cantidad;
    public $filtros;
    public $offset;
    public $nom_proveedor;
    public $idproveedor;
    //Modelos
    public $trans_pagos;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Nueva Devolucion', 'Compras', false, false, false, 'bi bi-cash-stack');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        $this->impresion = $this->user->have_access_to('impresion_compras');

        $this->resultados = array();
        //Genero las funcionalidades
        if (isset($_GET['buscar_proveedor'])) {
            $this->buscar_proveedor();
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
        $this->proveedores              = new proveedores();
        $this->trans_pagos              = new trans_pagos();
        $this->formaspago               = new formaspago();
        $this->cab_devolucion_proveedor = new cab_devolucion_proveedor();
        $this->facturasprov             = new facturasprov();
        $this->anticiposprov            = new anticiposprov();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;

        $this->nom_proveedor = '';
        $this->idproveedor   = '';
        if (isset($_REQUEST['idproveedor']) && $_REQUEST['idproveedor'] != '') {
            $proveedor = $this->proveedores->get($_REQUEST['idproveedor']);
            if ($proveedor) {
                $this->idproveedor   = $proveedor->idproveedor;
                $this->nom_proveedor = $proveedor->identificacion . " - " . $proveedor->razonsocial;
            }
        }
    }

    private function buscar_proveedor()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_proveedores($this->idempresa, $_GET['buscar_proveedor']);

        echo json_encode($result);
        exit;
    }

    public function buscar_resultados()
    {
        $this->resultados = $this->trans_pagos->getSaldosFavor($this->idempresa, $this->idproveedor);
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

        //verifico si existe el proveedor seleccionado
        $idproveedor = null;
        if ($_REQUEST['proveedorselect'] != '') {
            $proveedor = $this->proveedores->get($_REQUEST['proveedorselect']);
            if ($proveedor) {
                $idproveedor = $proveedor->idproveedor;
            }
        }

        if (isset($_POST['num_doc'])) {
            if (trim($_POST['num_doc']) != '') {
                //valido si el numero de documento no se encuentra ya registrado
                $numDoc = $this->cab_devolucion_proveedor->getNumDoc($this->idempresa, $_POST['fpselec'], $_POST['num_doc']);
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

        $devol              = new \cab_devolucion_proveedor();
        $devol->idempresa   = $this->idempresa;
        $devol->idproveedor = $idproveedor;
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
                        $doc            = $this->facturasprov->get($data[1]);
                        $idfactura      = $doc->idfacturaprov;
                        $idanticipoprov = null;
                        $idproveedord = $doc->idproveedor;
                    } else if ($data[0] == 'A') {
                        $doc            = $this->anticiposprov->get($data[1]);
                        $idfactura      = null;
                        $idanticipoprov = $doc->idanticipoprov;
                        $idproveedord = $doc->idproveedor;
                    }
                    if ($doc) {
                        if (floatval($_POST['valor_' . $iddocumento]) > 0) {
                            $tpago                   = new \trans_pagos();
                            $tpago->idempresa        = $this->idempresa;
                            $tpago->idproveedor      = $idproveedord;
                            $tpago->idfacturaprov    = $idfactura;
                            $tpago->idanticipoprov   = $idanticipoprov;
                            $tpago->idformapago      = $devol->idformapago;
                            $tpago->tipo             = 'Devolucion';
                            $tpago->fecha_trans      = $devol->fecha_trans;
                            $tpago->num_doc          = $devol->num_doc;
                            $tpago->credito          = floatval($_POST['valor_' . $iddocumento]);
                            $tpago->esabono          = true;
                            $tpago->iddevolucionprov = $devol->iddevolucionprov;
                            $tpago->fec_creacion     = date('Y-m-d');
                            $tpago->nick_creacion    = $this->user->nick;
                            if (!$tpago->save()) {
                                $mensaje = ' Error: ';
                                foreach ($devol->get_errors() as $key => $error) {
                                    $mensaje .= $error . " - ";
                                }
                                $msj = '';
                                if ($mensaje != 'Error: ') {
                                    $msj .= $mensaje;
                                }
                                $result   = array('error' => 'T', 'msj' => 'Error al generar la Devolución del Proveedor.'. $msj);
                                $correcto = false;
                                break;
                            } else {
                                $totaldev += $tpago->credito;
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
                    $result = array('error' => 'F', 'msj' => 'Devolución Generada correctamente.', 'url' => $this->url() . '&idproveedor=' . $devol->idproveedor . '&imprimir=' . $devol->iddevolucionprov);
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
            $mensaje = 'Imprima el comprobante presionando <a href="index.php?page=impresion_compras&imprimir_devol=' . $_GET['imprimir'] . '" target="_blank">aquí</a>';
        }

        if ($mensaje != '') {
            $this->new_message($mensaje);
        }
    }
}
