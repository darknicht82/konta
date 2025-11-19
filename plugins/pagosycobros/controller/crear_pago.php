<?php
/**
 * Controlador de Compras -> Nuevo Pago.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class crear_pago extends controller
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
        parent::__construct(__CLASS__, 'Nuevo Pago', 'Compras', false, false, false, 'bi bi-cash-stack');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        $this->impresion = $this->user->have_access_to('impresion_compras');

        $this->resultados = array();
        if ($this->idproveedor != '') {
            $this->buscar_resultados();
        }
        //Genero las funcionalidades
        if (isset($_GET['buscar_proveedor'])) {
            $this->buscar_proveedor();
        } else if (isset($_POST['gen_pago'])) {
            $this->nuevo_pago();
        } else if (isset($_POST['idfp'])) {
            $this->buscar_forma_pago();
        } else if (isset($_GET['imprimir'])) {
            $this->impresiones();
        }
    }

    private function init_modelos()
    {
        $this->proveedores     = new proveedores();
        $this->trans_pagos = new trans_pagos();
        $this->formaspago   = new formaspago();
        $this->cab_pagos   = new cab_pagos();
        $this->facturasprov  = new facturasprov();
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
        $this->resultados = $this->trans_pagos->pendientesPago($this->idempresa, $this->idproveedor);
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

    public function nuevo_pago()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Proveedor no encontrado.');

        //verifico si existe el proveedor seleccionado
        $proveedor = $this->proveedores->get($_REQUEST['proveedorselect']);
        if ($proveedor) {
            if (isset($_POST['num_doc'])) {
                if (trim($_POST['num_doc']) != '') {
                    //valido si el numero de documento no se encuentra ya registrado
                    $numDoc = $this->trans_pagos->getNumDocProveedor($this->idempresa, $_POST['fpselec'], $_POST['num_doc']);
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

            $pago              = new \cab_pagos();
            $pago->idempresa   = $this->idempresa;
            $pago->idproveedor   = $proveedor->idproveedor;
            $pago->idformapago = $_POST['fpselec'];
            $pago->fecha_trans = $_POST['fec_trans'];
            if (isset($_POST['num_doc'])) {
                $pago->num_doc = $_POST['num_doc'];
            }
            if (isset($_POST['observaciones']) && $_POST['observaciones'] != '') {
                $pago->observaciones = $_POST['observaciones'];
            }
            $pago->fec_creacion  = date('Y-m-d');
            $pago->nick_creacion = $this->user->nick;

            $totalpago = 0;
            $correcto   = true;
            if ($pago->save()) {
                foreach ($_POST['idfacturas'] as $key => $idfactura) {
                    if (isset($_POST['valor_' . $idfactura])) {
                        $factura = $this->facturasprov->get($idfactura);
                        if ($factura) {
                            if (floatval($_POST['valor_' . $idfactura]) > 0) {
                                $tpago                = new \trans_pagos();
                                $tpago->idempresa     = $this->idempresa;
                                $tpago->idproveedor     = $pago->idproveedor;
                                $tpago->idfacturaprov  = $idfactura;
                                $tpago->idformapago   = $pago->idformapago;
                                $tpago->tipo          = 'Pago';
                                $tpago->fecha_trans   = $pago->fecha_trans;
                                $tpago->num_doc       = $pago->num_doc;
                                $tpago->debito       = floatval($_POST['valor_' . $idfactura]);
                                $tpago->esabono       = true;
                                $tpago->idpago       = $pago->idpago;
                                $tpago->fec_creacion  = date('Y-m-d');
                                $tpago->nick_creacion = $this->user->nick;
                                if (!$tpago->save()) {
                                    $result   = array('error' => 'T', 'msj' => 'Error al generar el Pago en la Factura.');
                                    $correcto = false;
                                    break;
                                } else {
                                    $totalpago += $tpago->debito;
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
                    $pago->valor = $totalpago;
                    if ($pago->save()) {
                        $result = array('error' => 'F', 'msj' => 'Pago Generado correctamente.', 'url' => $this->url() . '&idproveedor=' . $pago->idproveedor . '&imprimir=' . $pago->idpago);
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al actualizar el valor del Pago.');
                    }
                } else {
                    if (!$pago->delete()) {
                        $mensaje = 'Error: ';
                        foreach ($pago->get_errors() as $key => $error) {
                            $mensaje .= $error . " - ";
                        }

                        if ($mensaje != 'Error: ') {
                            $result['msj'] .= $mensaje;
                        }
                    }
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al generar la Cabecera del Pago.');
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
            $mensaje = 'Imprima el comprobante presionando <a href="index.php?page=impresion_compras&imprimir_pago=' . $_GET['imprimir'] . '" target="_blank">aquí</a>';
        }

        if ($mensaje != '') {
            $this->new_message($mensaje);
        }
    }
}