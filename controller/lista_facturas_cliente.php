<?php
/**
 * Controlador de Ventas -> Facturas.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_facturas_cliente extends controller
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
    public $facturas_cliente;
    public $documentos;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Facturas', 'Ventas', true, true, false, 'bi bi-cart-dash-fill');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        $this->access_crear = $this->user->have_access_to('crear_venta');

        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_factura();
        } else if (isset($_GET['anular'])) {
            $this->anular_factura();
        } else if (isset($_REQUEST['aut_masivo'])) {
            $this->autorizacion_masiva();
        }

        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->clientes         = new clientes();
        $this->facturas_cliente = new facturascli();
        $this->documentos       = new documentos();
        $this->autorizar_sri    = new autorizar_sri();
        $this->enviar_correo    = new envio_correos();
    }

    private function init_filter()
    {
        $this->idempresa   = $this->user->idempresa;
        $this->cantidad    = 0;
        $this->url_recarga = false;
        $this->filtros     = '';

        $this->offset = 0;
        if (isset($_REQUEST['offset'])) {
            $this->offset = $_REQUEST['offset'];
        }

        $this->offset2 = 0;
        if (isset($_REQUEST['offset2'])) {
            $this->offset2 = $_REQUEST['offset2'];
        }

        $this->query = '';
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

        $this->desde_aut = '';
        if (isset($_REQUEST['desde_aut']) && $_REQUEST['desde_aut'] != '') {
            $this->desde_aut = $_REQUEST['desde_aut'];
        }

        $this->hasta_aut = '';
        if (isset($_REQUEST['hasta_aut']) && $_REQUEST['hasta_aut'] != '') {
            $this->hasta_aut = $_REQUEST['hasta_aut'];
        }

        $this->iddocumento = '';
        if (isset($_REQUEST['iddocumento']) && $_REQUEST['iddocumento'] != '') {
            $this->iddocumento = $_REQUEST['iddocumento'];
            $this->filtros .= '&iddocumento=' . $this->iddocumento;
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

        $this->anuladas = isset($_REQUEST['anuladas']);
        if (isset($_REQUEST['anuladas'])) {
            $this->filtros .= '&anuladas';
        }
        $this->saldosini = isset($_REQUEST['saldosini']);
        if (isset($_REQUEST['saldosini'])) {
            $this->filtros .= '&saldosini';
        }
        $this->autorizadas = isset($_REQUEST['autorizadas']);
        if (isset($_REQUEST['autorizadas'])) {
            $this->filtros .= '&autorizadas';
        }
        $this->sinautorizar = isset($_REQUEST['sinautorizar']);
        if (isset($_REQUEST['sinautorizar'])) {
            $this->filtros .= '&sinautorizar';
        }
    }

    private function eliminar_factura()
    {
        $factura = $this->facturas_cliente->get($_GET['delete']);
        if ($factura) {
            if ($factura->delete()) {
                $this->new_message("Factura eliminada correctamente.");
            } else {
                $this->new_error_msg("Existió un error al eliminar la factura");
            }
        } else {
            $this->new_advice("Factura No Encontrada");
        }
    }

    private function anular_factura()
    {
        $factura = $this->facturas_cliente->get($_GET['anular']);
        if ($factura) {
            if (!$factura->anulado) {
                if ($factura->anular()) {
                    $this->new_message("Documento Anulado correctamente.");
                } else {
                    $this->new_error_msg("Error al anular el documento.");
                }
            } else {
                $this->new_advice("El Documento ya se encuentra anulado.");
            }
        } else {
            $this->new_advice("Documento No Encontrado");
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->facturas_cliente->search_facturascli($this->idempresa, $this->query, $this->idcliente, $this->desde, $this->hasta, $this->iddocumento, false, $this->anuladas, $this->saldosini, $this->autorizadas, $this->sinautorizar, $this->offset);
        } else {
            $this->cantidad = $this->facturas_cliente->search_facturascli($this->idempresa, $this->query, $this->idcliente, $this->desde, $this->hasta, $this->iddocumento, false, $this->anuladas, $this->saldosini, $this->autorizadas, $this->sinautorizar, -1);
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

    private function autorizacion_masiva()
    {
        if ($this->empresa->activafacelec) {
            $facturas = $this->facturas_cliente->getNoAutorizados($this->idempresa, $this->desde_aut, $this->hasta_aut);
            if ($facturas) {
                foreach ($facturas as $key => $factura) {
                    $this->offset2++;
                    if ($factura) {
                        if ($factura->anulado) {
                            $this->new_advice("No se puede realizar la autorización, el documento se encuentra anulado.");
                        } else if ($factura->saldoinicial) {
                            $this->new_advice('El documento es un saldo inicial, no se puede realizar la autorización en el SRI.');
                        }
                        if ($factura->getlineas()) {
                            if ($factura->estado_sri != 'AUTORIZADO') {
                                if ($factura->coddocumento == '01') {
                                    $carpeta = 'facturas';
                                } else if ($factura->coddocumento == '04') {
                                    $carpeta = 'notasdecredito';
                                } else if ($factura->coddocumento == '05') {
                                    $carpeta = 'notasdedebito';
                                } else {
                                    $this->new_error_msg('Documento No encontrado.');
                                }
                                $rutaXmlFirmado = JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/documentosElectronicos/" . $carpeta . "/autorizados/";
                                $archivoFirmado = $rutaXmlFirmado . $factura->numero_documento . ".xml";
                                if (!file_exists($archivoFirmado)) {
                                    $result = $this->autorizar_sri->procesar_documento_sri($factura, $this->empresa);
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
                $this->new_advice("Procesando (" . $this->offset2 . ") documentos. <div class='spinner-border spinner-border-sm' role='status'></div>");
                $this->url_recarga = $this->url() . "&aut_masivo&desde_aut=" . $this->desde_aut . "&hasta_aut=" . $this->hasta_aut . "&offset2=" . $this->offset2;
            } else {
                $this->new_message("Proceso Finalizado correctamente (" . $this->offset2 . ").");
            }
        } else {
            $this->new_error_msg("La Facturación Electrónica se encuentra desactivada.");
        }
    }
}
