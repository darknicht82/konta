<?php
/**
 * Controlador de Retencion -> Ver Retencion Cliente.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_retencion_cliente extends controller
{
    //variables
    public $retencion;
    public $allow_delete;
    public $allow_modify;
    public $impresion;
    //modelos
    public $retencionescli;
    public $facturascli;
    public $lineasretencionescli;
    public $tiposretenciones;
    public $trans_cobros;
    public $formaspago;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ver Retencion Cliente', 'Retenciones', false, false);
    }

    protected function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->retencionescli       = new retencionescli();
        $this->facturascli          = new facturascli();
        $this->lineasretencionescli = new lineasretencionescli();
        $this->tiposretenciones     = new tiposretenciones();
        $this->trans_cobros         = new trans_cobros();
        $this->formaspago           = new formaspago();

        $this->retencion = false;
        if (isset($_GET['id'])) {
            $this->retencion = $this->retencionescli->get($_GET['id']);
            if (!$this->retencion) {
                $this->new_advice("No se encuentra la retención seleccionada.");
                return;
            } else if ($this->retencion->idempresa != $this->empresa->idempresa) {
                $this->retencion = false;
                $this->new_advice("La retención no esta disponible para su empresa.");
                return;
            }
        }
        if (isset($_POST['nueva_linea'])) {
            $this->agregar_linea();
        } else if (isset($_GET['buscar_tiporetencion'])) {
            $this->buscar_tiposretenciones();
        } else if (isset($_POST['idtiporetencion'])) {
            $this->buscar_tiporetencion();
        } else if (isset($_GET['actdetret'])) {
            $this->buscar_detalle_retencion();
        } else if (isset($_POST['eliminar_linea'])) {
            $this->eliminar_linea();
        } else if (isset($_REQUEST['buscar_factura'])) {
            $this->buscar_factura();
        }
    }

    private function eliminar_linea()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Retención No Encontrada.');
        if ($this->retencion) {
            $linea = $this->lineasretencionescli->get($_POST['eliminar_linea']);
            if ($linea) {
                if ($linea->delete()) {
                    $this->retencion->total -= $linea->total;

                    if ($this->retencion->save()) {
                        $result = array('error' => 'F', 'msj' => '');
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al actualizar los totales de la Retención.');
                        $linea->save();
                    }
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al Eliminar la linea de la retencion.');
                    if ($linea->get_errors()) {
                        foreach ($linea->get_errors() as $key => $val) {
                            $result['msj'] .= "\n" . $val;
                        }
                    }
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Linea de Retención no Encontrada.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_detalle_retencion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No existe la Retención.');
        if ($this->retencion) {
            $lineas = $this->lineasretencionescli->all_by_idretencion($this->retencion->idretencion);
            $result = array('error' => 'T', 'msj' => 'Sin Detalle.');
            if ($lineas) {
                $result = array('error' => 'F', 'msj' => '', 'retencion' => $this->retencion, 'lineas' => $lineas);
            }
        }

        echo json_encode($result);
        exit;
    }

    private function buscar_tiporetencion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Tipo de Retención No encontrada.');
        $base           = 0;
        $iva            = 0;
        if ($_POST['idfactura_ret'] != -1) {
            $factura = $this->facturascli->get($_POST['idfactura_ret']);
            if ($factura) {
                $base = round($factura->base_0 + $factura->base_gra, 2);
                $iva  = round($factura->totaliva, 2);
            }
        }
        $tiporet = $this->tiposretenciones->get($_POST['idtiporetencion']);
        if ($tiporet) {
            if ($tiporet->especie == 'iva') {
                $baseImp = $iva;
            } else {
                $baseImp = $base;
            }
            $result = array('error' => 'F', 'msj' => '', 'tipret' => $tiporet, 'base' => $baseImp);
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_tiposretenciones()
    {
        $this->template   = false;
        $tiposretenciones = $this->tiposretenciones->all_retenciones_venta($this->empresa->idempresa, $_GET['buscar_tiporetencion']);
        echo json_encode($tiposretenciones);
        exit;
    }

    private function agregar_linea()
    {
        $this->template = false;
        $paso           = true;
        $result         = array('error' => 'T', 'msj' => 'Retención No Encontrada.');
        if ($this->retencion) {
            //busco el ariculo para ver si tengo q validar el stock
            $ret = $this->tiposretenciones->get($_POST['idtiporetencion']);
            if (!$ret) {
                $result = array('error' => 'T', 'msj' => 'Tipo de Retención no encontrado.');
                $paso   = false;
            }

            if ($paso) {
                $linea                  = new lineasretencionescli();
                $linea->idempresa       = $this->empresa->idempresa;
                $linea->idretencion     = $this->retencion->idretencion;
                $linea->especie         = $_POST['especie'];
                $linea->codigo          = $_POST['codigo'];
                $linea->idtiporetencion = $_POST['idtiporetencion'];
                $linea->baseimponible   = floatval($_POST['baseimponible']);
                $linea->porcentaje      = floatval($_POST['porcentaje']);
                $linea->total           = floatval($_POST['total']);
                if (isset($_POST['nofactura'])) {
                    $linea->coddocumento_mod     = '01';
                    $numdoc                      = str_pad($_POST['doc_estab'], 3, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['doc_pto'], 3, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['doc_secuen'], 9, "0", STR_PAD_LEFT);
                    $linea->numero_documento_mod = $numdoc;
                    $linea->fec_emision_mod      = $_POST['doc_fecemi'];
                } else {
                    $factura = $this->facturascli->get($_POST['idfactura']);
                    if ($factura) {
                        $linea->coddocumento_mod     = $factura->coddocumento;
                        $linea->iddocumento_mod      = $factura->idfacturacli;
                        $linea->numero_documento_mod = $factura->numero_documento;
                        $linea->fec_emision_mod      = $factura->fec_emision;
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Factura No Encontrada.');
                        echo json_encode($result);
                        exit;
                    }
                }
                $linea->fec_creacion  = date('Y-m-d');
                $linea->nick_creacion = $this->user->nick;
                if ($linea->save()) {
                    $this->retencion->total += $linea->total;

                    if ($this->retencion->save()) {
                        $result = array('error' => 'F', 'msj' => '');
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al actualizar los totales de la Retención.');
                        $linea->delete();
                    }
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al Guardar la linea de la retencion.');
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

    private function buscar_factura()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_factura_cli($this->empresa->idempresa, $_GET['buscar_factura'], $_GET['idcliente']);

        echo json_encode($result);
        exit;
    }
}
