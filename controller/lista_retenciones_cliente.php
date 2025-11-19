<?php
/**
 * Controlador de Ventas -> Retenciones.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_retenciones_cliente extends controller
{
    //Filtros
    public $query;
    public $nom_cliente;
    public $idcliente;
    public $desde;
    public $hasta;
    public $idempresa;
    //modelos
    public $clientes;
    public $retenciones_cliente;
    public $facturascli;
    public $lineasretencionescli;
    public $documentos;
    public $tiposretenciones;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Retenciones', 'Ventas', true, true, false, 'bi bi-clipboard2-minus');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_retencion();
        } else if (isset($_POST['nueva_retencion'])) {
            $this->nueva_retencion();
        }

        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->clientes             = new clientes();
        $this->retenciones_cliente  = new retencionescli();
        $this->facturascli          = new facturascli();
        $this->lineasretencionescli = new lineasretencionescli();
        $this->documentos           = new documentos();
        $this->tiposretenciones     = new tiposretenciones();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
        $this->cantidad  = 0;
        $this->filtros   = '';

        $this->offset = 0;
        if (isset($_REQUEST['offset'])) {
            $this->offset = $_REQUEST['offset'];
        }

        $this->query     = '';
        if (isset($_REQUEST['query']) && $_REQUEST['query'] != '') {
            $this->query = $_REQUEST['query'];
            $this->filtros .= '&query=' . $this->query;
        }

        $this->desde = '';
        if (isset($_REQUEST['desde']) && $_REQUEST['desde'] != '') {
            $this->desde = $_REQUEST['desde'];
            $this->filtros .= '&desde=' . $this->desde;
        }

        $this->hasta = '';
        if (isset($_REQUEST['hasta']) && $_REQUEST['hasta'] != '') {
            $this->hasta = $_REQUEST['hasta'];
            $this->filtros .= '&hasta=' . $this->hasta;
        }

        $this->nom_cliente = '';
        $this->idcliente   = '';
        if (isset($_REQUEST['idcliente']) && $_REQUEST['idcliente'] != '') {
            $cliente = $this->clientes->get($_REQUEST['idcliente']);
            if ($cliente) {
                $this->idcliente   = $cliente->idcliente;
                $this->nom_cliente = $cliente->identificacion . " - " . $cliente->razonsocial;
                $this->filtros .= '&idcliente=' . $this->idcliente;
            }
        }
    }

    private function eliminar_retencion()
    {
        $retencion = $this->retenciones_cliente->get($_GET['delete']);
        if ($retencion) {
            if ($retencion->delete()) {
                $this->new_message("Retencion de Cliente eliminada correctamente.");
            } else {
                $this->new_error_msg("Existió un error al eliminar la retencion");
            }
        } else {
            $this->new_advice("Retencion de Cliente No Encontrada");
        }
    }

    private function buscar($offset = 0)
    {
        if ($this->query == '') {
            $this->query = false;
        }
        if ($this->desde == '') {
            $this->desde = false;
        }
        if ($this->hasta == '') {
            $this->hasta = false;
        }
        if ($this->idcliente == '') {
            $this->idcliente = false;
        }
        if ($offset == 0) {
            $this->resultados = $this->retenciones_cliente->search_retenciones_cliente($this->idempresa, $this->query, $this->idcliente, $this->desde, $this->hasta, $this->offset);
        } else {
            $this->cantidad = $this->retenciones_cliente->search_retenciones_cliente($this->idempresa, $this->query, $this->idcliente, $this->desde, $this->hasta, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
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

    private function nueva_retencion()
    {
        $l_nro = strlen($_POST['nro_autorizacion']);
        if ($l_nro == 49) {
            //Es electronico
            $this->procesar_ret_electronica();
        } else {
            $this->crear_retencion();
        }
    }

    private function procesar_ret_electronica()
    {
        $reten = consultardocsri($_POST['nro_autorizacion']);
        if ($reten['encontrado']) {
            $comprobante = $reten['xml'];
            if ($comprobante->infoTributaria->codDoc == '07') {
                if ($comprobante->infoCompRetencion->identificacionSujetoRetenido == $this->empresa->ruc) {
                    //Busco el Cliente
                    $cliente = $this->clientes->get_by_identificacion($this->empresa->idempresa, $comprobante->infoTributaria->ruc);
                    if (!$cliente) {
                        //Si no existe lo creo
                        $cliente                 = new clientes();
                        $cliente->idempresa      = $this->empresa->idempresa;
                        $cliente->identificacion = $comprobante->infoTributaria->ruc;
                        $cliente->tipoid         = 'R';
                        $cliente->razonsocial    = $comprobante->infoTributaria->razonSocial;
                        if (isset($comprobante->infoTributaria->nombreComercial)) {
                            $cliente->nombrecomercial = $comprobante->infoTributaria->nombreComercial;
                        } else {
                            $cliente->nombrecomercial = $cliente->razonsocial;
                        }
                        $cliente->direccion   = $comprobante->infoTributaria->dirMatriz;
                        $cliente->agretencion = true;
                        //Obligado o No
                        if (isset($comprobante->infoCompRetencion->obligadoContabilidad)) {
                            if ($comprobante->infoCompRetencion->obligadoContabilidad == 'SI') {
                                $cliente->obligado = true;
                            }
                        }
                        $cliente->fec_creacion  = date('Y-m-d');
                        $cliente->nick_creacion = $this->user->nick;
                        if (!$cliente->save()) {
                            $this->new_advice("No se pudo crear el cliente.");
                            return;
                        }
                    }
                    $retencion                   = new retencionescli();
                    $retencion->idempresa        = $this->empresa->idempresa;
                    $retencion->idcliente        = $cliente->idcliente;
                    $fecha                       = explode("/", $comprobante->infoCompRetencion->fechaEmision);
                    $retencion->fec_emision      = $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];
                    $retencion->fec_registro     = $retencion->fec_emision;
                    $numero_documento            = str_pad($comprobante->infoTributaria->estab, 3, "0", STR_PAD_LEFT) . '-' . str_pad($comprobante->infoTributaria->ptoEmi, 3, "0", STR_PAD_LEFT) . '-' . str_pad($comprobante->infoTributaria->secuencial, 9, "0", STR_PAD_LEFT);
                    $retencion->numero_documento = $numero_documento;
                    $retencion->nro_autorizacion = $comprobante->infoTributaria->claveAcceso;
                    $retencion->fec_autorizacion = substr($reten['fec_aut'], 0, 10);
                    $retencion->hor_autorizacion = substr($reten['fec_aut'], 11, 8);
                    $retencion->fec_creacion     = date('Y-m-d');
                    $retencion->nick_creacion    = $this->user->nick;

                    if ($retencion->save()) {
                        //Si se guarda recorro el detalle
                        if ($comprobante['version'] == '1.0.0') {
                            //Version Anterior
                            foreach ($comprobante->impuestos->impuesto as $key => $impuesto) {
                                $numdoc  = substr($impuesto->numDocSustento, 0, 3) . "-" . substr($impuesto->numDocSustento, 3, 3) . "-" . substr($impuesto->numDocSustento, 6);
                                $factura = false;
                                if ($impuesto->codDocSustento == '01') {
                                    $factura = $this->facturascli->buscar_factura($this->empresa->idempresa, $numdoc);
                                }
                                $linea              = new lineasretencionescli();
                                $linea->idempresa   = $this->empresa->idempresa;
                                $linea->idretencion = $retencion->idretencion;
                                if ($impuesto->codigo == 1) {
                                    $linea->especie = 'renta';
                                } else {
                                    $linea->especie = 'iva';
                                }
                                $linea->codigo = $impuesto->codigoRetencion;
                                //Busco el idtiporetencion
                                $tiporet = $this->tiposretenciones->get_retencion_venta($this->empresa->idempresa, $linea->especie, $linea->codigo);
                                if ($tiporet) {
                                    $linea->idtiporetencion = $tiporet->idtiporetencion;
                                } else {
                                    $this->new_advice("Codigo de Retención no encontrado para el Modulo de Ventas, Codigo: " . $linea->codigo);
                                    $retencion->delete();
                                    return;
                                }
                                $linea->baseimponible    = floatval($impuesto->baseImponible);
                                $linea->porcentaje       = floatval($impuesto->porcentajeRetener);
                                $linea->total            = floatval($impuesto->valorRetenido);
                                $linea->coddocumento_mod = $impuesto->codDocSustento;
                                if ($factura) {
                                    $linea->idfactura_mod = $factura->idfacturacli;
                                }
                                $linea->numero_documento_mod = $numdoc;
                                $fechafact                   = explode("/", $impuesto->fechaEmisionDocSustento);
                                $linea->fec_emision_mod      = $fechafact[2] . "-" . $fechafact[1] . "-" . $fechafact[0];
                                $linea->fec_creacion         = date('Y-m-d');
                                $linea->nick_creacion        = $this->user->nick;
                                if (!$linea->save()) {
                                    $this->new_advice("Error al generar el detalle de la Retencion, Codigo: " . $linea->codigo);
                                    $retencion->delete();
                                    return;
                                } else {
                                    $retencion->total += floatval($linea->total);
                                }
                            }

                            if ($retencion->save()) {
                                header('location: ' . $retencion->url());
                            } else {
                                $this->new_error_msg("Error al guardar la Retencion");
                                $retencion->delete();
                                return;
                            }
                        } else {
                            //Version ATS
                            foreach ($comprobante->docsSustento->docSustento as $key => $docSustento) {
                                foreach ($docSustento->retenciones->retencion as $key => $ret) {
                                    $numdoc  = substr($docSustento->numDocSustento, 0, 3) . "-" . substr($docSustento->numDocSustento, 3, 3) . "-" . substr($docSustento->numDocSustento, 6);
                                    $factura = false;
                                    if ($docSustento->codDocSustento == '01') {
                                        $factura = $this->facturascli->buscar_factura($this->empresa->idempresa, $numdoc);
                                    }
                                    $factura            = $this->facturascli->buscar_factura($this->empresa->idempresa, $numdoc);
                                    $linea              = new lineasretencionescli();
                                    $linea->idempresa   = $this->empresa->idempresa;
                                    $linea->idretencion = $retencion->idretencion;
                                    if ($ret->codigo == 1) {
                                        $linea->especie = 'renta';
                                    } else {
                                        $linea->especie = 'iva';
                                    }
                                    $linea->codigo = $ret->codigoRetencion;
                                    //Busco el idtiporetencion
                                    $tiporet = $this->tiposretenciones->get_retencion_venta($this->empresa->idempresa, $linea->especie, $linea->codigo);
                                    if ($tiporet) {
                                        $linea->idtiporetencion = $tiporet->idtiporetencion;
                                    } else {
                                        $this->new_advice("Codigo de Retención no encontrado para el Modulo de Ventas, Codigo: " . $linea->codigo);
                                        $retencion->delete();
                                        return;
                                    }
                                    $linea->baseimponible    = floatval($ret->baseImponible);
                                    $linea->porcentaje       = floatval($ret->porcentajeRetener);
                                    $linea->total            = floatval($ret->valorRetenido);
                                    $linea->coddocumento_mod = $docSustento->codDocSustento;
                                    if ($factura) {
                                        $linea->idfactura_mod = $factura->idfacturacli;
                                    }
                                    $linea->numero_documento_mod = $numdoc;
                                    $fechafact                   = explode("/", $docSustento->fechaEmisionDocSustento);
                                    $linea->fec_emision_mod      = $fechafact[2] . "-" . $fechafact[1] . "-" . $fechafact[0];
                                    $linea->fec_creacion         = date('Y-m-d');
                                    $linea->nick_creacion        = $this->user->nick;
                                    if (!$linea->save()) {
                                        $this->new_advice("Error al generar el detalle de la Retencion, Codigo: " . $linea->codigo);
                                        $retencion->delete();
                                        return;
                                    } else {
                                        $retencion->total += floatval($linea->total);
                                    }
                                }
                            }

                            if ($retencion->save()) {
                                header('location: ' . $retencion->url());
                            } else {
                                $this->new_error_msg("Error al guardar la Retencion");
                                $retencion->delete();
                                return;
                            }
                        }
                    } else {
                        $this->new_advice('Error al guardar la Retención.');
                    }
                } else {
                    $this->new_advice('Retención: ' . $_POST['nro_autorizacion'] . ' no valida para la empresa.');
                }
            } else {
                $this->new_advice('El comprobante consultado no es una Retención.');
            }
        } else {
            $this->new_advice($reten['msj']);
        }
    }

    private function crear_retencion()
    {
        $retencion                   = new retencionescli();
        $retencion->idempresa        = $this->empresa->idempresa;
        $retencion->idcliente        = $_POST['idcliente_ret'];
        $retencion->fec_emision      = $_POST['fec_emision'];
        $retencion->fec_registro     = $_POST['fec_registro'];
        $numero_documento            = str_pad($_POST['estab'], 3, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['ptoemision'], 3, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['secuencial'], 9, "0", STR_PAD_LEFT);
        $retencion->numero_documento = $numero_documento;
        $retencion->nro_autorizacion = $_POST['nro_autorizacion'];
        $retencion->fec_creacion     = date('Y-m-d');
        $retencion->nick_creacion    = $this->user->nick;

        if ($retencion->save()) {
            header('location: ' . $retencion->url());
        } else {
            $this->new_error_msg("Error al guardar la Retencion");
            $retencion->delete();
            return;
        }
    }
}
