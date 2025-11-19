<?php
/**
 * Controlador de Guia -> Ver Guia Remision.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_guia_remision extends controller
{
    //variables
    public $guia;
    public $allow_delete;
    public $allow_modify;
    public $impresion;
    //modelos
    public $guiascli;
    public $lineasguiascli;
    public $articulos;
    public $trans_cobros;
    public $formaspago;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ver Guia Remision', 'Ventas', false, false);
    }

    protected function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->impresion = $this->user->have_access_to('impresion_ventas');

        $this->guiascli       = new guiascli();
        $this->lineasguiascli = new lineasguiascli();
        $this->articulos      = new articulos();
        $this->trans_cobros   = new trans_cobros();
        $this->formaspago     = new formaspago();
        $this->enviar_correo  = new envio_correos();

        $this->guia = false;
        if (isset($_GET['id'])) {
            $this->guia = $this->guiascli->get($_GET['id']);
            if (!$this->guia) {
                $this->new_advice("No se encuentra la guia seleccionada.");
                return;
            } else if ($this->guia->idempresa != $this->empresa->idempresa) {
                $this->guia = false;
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
        } else if (isset($_GET['actdetguia'])) {
            $this->buscar_detalle_guia();
        } else if (isset($_POST['eliminar_linea'])) {
            $this->eliminar_linea();
        } else if (isset($_GET['autorizar'])) {
            $this->procesar_autorizacion();
        } else if (isset($_POST['validar_edicion'])) {
            $this->validar_edicion();
        } else if (isset($_POST['editar_doc'])) {
            $this->editar_documento();
        } else if (isset($_POST['reenviar_correo'])) {
            $this->reenviar_correo();
        } else if (isset($_GET['buscar_trans'])) {
            $this->buscar_transportista();
        } else if (isset($_POST['trasnp'])) {
            $this->get_transportista();
        } else if (isset($_REQUEST['buscar_factura'])) {
            $this->buscar_factura();
        }
    }

    private function eliminar_linea()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Guia de Remision No Encontrada.');
        if ($this->guia) {
            $linea = $this->lineasguiascli->get($_POST['eliminar_linea']);
            if ($linea) {
                if ($linea->delete()) {
                    switch ($linea->get_impuesto()) {
                        case 'IVANO':
                            //Base no Objeto de IVA
                            $this->guia->base_noi -= $linea->pvptotal;
                            break;
                        case 'IVA0':
                            //Base 0
                            $this->guia->base_0 -= $linea->pvptotal;
                            break;
                        case 'IVAEX':
                            //Base Excento de IVA
                            $this->guia->base_exc -= $linea->pvptotal;
                            break;
                        default:
                            //Base Gravada
                            $this->guia->base_gra -= $linea->pvptotal;
                            break;
                    }
                    $this->guia->totaldescuento -= ($linea->pvpsindto - $linea->pvptotal);
                    $this->guia->totalice -= $linea->valorice;
                    $this->guia->totaliva -= $linea->valoriva;

                    if (!$this->guia->getlineas()) {
                        $this->guia->base_noi       = 0;
                        $this->guia->base_0         = 0;
                        $this->guia->base_exc       = 0;
                        $this->guia->base_gra       = 0;
                        $this->guia->totaldescuento = 0;
                        $this->guia->totalice       = 0;
                        $this->guia->totaliva       = 0;

                    }

                    if ($this->guia->save()) {
                        $result = array('error' => 'F', 'msj' => '');
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al actualizar los totales de la Guia de Remision.');
                        $linea->save();
                    }
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al Eliminar la linea de la guia.');
                    if ($linea->get_errors()) {
                        foreach ($linea->get_errors() as $key => $val) {
                            $result['msj'] .= "\n" . $val;
                        }
                    }
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Linea de Guia de Remision no Encontrada.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_detalle_guia()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No existe la Guia de Remision.');
        if ($this->guia) {
            $lineas = $this->lineasguiascli->all_by_idguiacli($this->guia->idguiacli);
            $result = array('error' => 'T', 'msj' => 'Sin Detalle.');
            if ($lineas) {
                $result = array('error' => 'F', 'msj' => '', 'guia' => $this->guia, 'lineas' => $lineas);
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
            $result = array('error' => 'F', 'msj' => '', 'art' => $articulo);
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
        $articulos      = buscar_articulos($this->empresa->idempresa, $_GET['buscar_articulo'], '', '', '', '', $_GET['establecimiento']);
        echo json_encode($articulos);
        exit;
    }

    private function agregar_linea()
    {
        $this->template = false;
        $paso           = true;
        $result         = array('error' => 'T', 'msj' => 'Guia de Remision No Encontrada.');
        if ($this->guia) {
            $linea               = new lineasguiascli();
            $linea->idguiacli    = $this->guia->idguiacli;
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
                        $this->guia->base_noi += $linea->pvptotal;
                        break;
                    case 'IVA0':
                        //Base 0
                        $this->guia->base_0 += $linea->pvptotal;
                        break;
                    case 'IVAEX':
                        //Base Excento de IVA
                        $this->guia->base_exc += $linea->pvptotal;
                        break;
                    default:
                        //Base Gravada
                        $this->guia->base_gra += $linea->pvptotal;
                        break;
                }
                $this->guia->totaldescuento += ($linea->pvpsindto - $linea->pvptotal);
                $this->guia->totalice += $linea->valorice;
                $this->guia->totaliva += $linea->valoriva;

                if ($this->guia->save()) {
                    $result = array('error' => 'F', 'msj' => '');
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al actualizar los totales de la Guia de Remision.');
                    $linea->delete();
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al Guardar la linea de la guia.');
                if ($linea->get_errors()) {
                    foreach ($linea->get_errors() as $key => $val) {
                        $result['msj'] .= "\n" . $val;
                    }
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    private function procesar_autorizacion()
    {
        if ($this->empresa->activafacelec) {
            if ($this->guia) {
                if ($this->guia->getlineas()) {
                    if ($this->guia->estado_sri != 'AUTORIZADO') {
                        $autorizar_sri = new autorizar_sri();
                        if ($this->guia->coddocumento == '06') {
                            $carpeta = 'guiasremision';
                        } else {
                            $this->new_error_msg('Documento No encontrado.');
                            return;
                        }
                        $rutaXmlFirmado = JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/documentosElectronicos/" . $carpeta . "/autorizados/";
                        $archivoFirmado = $rutaXmlFirmado . $this->guia->numero_documento . ".xml";
                        if (!file_exists($archivoFirmado)) {
                            $result = $autorizar_sri->procesar_documento_sri($this->guia, $this->empresa);
                            if ($result['error'] == 'F') {
                                if (file_exists($archivoFirmado)) {
                                    $archivoXml = simplexml_load_file($archivoFirmado);
                                    if ($archivoXml) {
                                        $this->guia->tipoemision      = 'E';
                                        $this->guia->estado_sri       = $archivoXml->estado;
                                        $this->guia->fec_autorizacion = substr($archivoXml->fechaAutorizacion, 0, 10);
                                        $this->guia->hor_autorizacion = substr($archivoXml->fechaAutorizacion, 11, 8);
                                        if ($this->guia->save()) {
                                            //Genero el envio de correo electronico
                                            $correo = $this->enviar_correo->correo_docs_ventas($this->guia, $this->empresa, false);
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
                                $this->guia->tipoemision      = 'E';
                                $this->guia->estado_sri       = $archivoXml->estado;
                                $this->guia->fec_autorizacion = substr($archivoXml->fechaAutorizacion, 0, 10);
                                $this->guia->hor_autorizacion = substr($archivoXml->fechaAutorizacion, 11, 8);
                                if ($this->guia->save()) {
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
        } else {
            $this->new_error_msg("La Facturación Electrónica se encuentra desactivada.");
        }
    }

    public function validar_edicion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Guía de Remisión no Encontrada');
        if ($this->guia) {
            if (!$this->allow_modify) {
                $result = array('error' => 'T', 'msj' => 'El usuario no tiene permiso para editar el documento.');
            } else if ($this->guia->anulado) {
                $result = array('error' => 'T', 'msj' => 'La Guía de Remisión se encuentra anulada, no se puede editar.');
            } else if ($this->guia->estado_sri == 'AUTORIZADO') {
                $result = array('error' => 'T', 'msj' => 'La Guía de Remisión se encuentra Autorizada, no se puede editar.');
            } else {
                $result = array('error' => 'F', 'msj' => '', 'guia' => $this->guia, 'cliente' => $this->guia->get_cliente());
            }
        }
        echo json_encode($result);
        exit;
    }

    private function editar_documento()
    {
        if ($this->guia) {
            if (!$this->allow_modify) {
                $this->new_advice("El usuario no tiene permiso para editar el documento.");
                return;
            } else if ($this->guia->anulado) {
                $this->new_advice("La Guía de Remisión se encuentra anulada, no se puede editar.");
                return;
            } else if ($this->guia->estado_sri == 'AUTORIZADO') {
                $this->new_advice("La Guía de Remisión se encuentra Autorizada, no se puede editar.");
                return;
            }

            $this->guia->idcliente      = $_POST['idcliente'];
            $this->guia->tipoid         = $_POST['tipidencliente'];
            $this->guia->identificacion = $_POST['identcliente'];
            $this->guia->razonsocial    = $_POST['razonscliente'];
            $this->guia->direccion      = $_POST['direccion'];
            $this->guia->email          = $_POST['email'];
            $this->guia->motivo         = $_POST['motivo'];

            $this->guia->codestablecimiento = null;
            if ($_POST['codestablecimiento'] != '') {
                $this->guia->codestablecimiento = $_POST['codestablecimiento'];
            }
            $this->guia->ruta = null;
            if ($_POST['ruta'] != '') {
                $this->guia->ruta = $_POST['ruta'];
            }
            $this->guia->tipoid_trans         = $_POST['tipoid_trans'];
            $this->guia->identificacion_trans = $_POST['identificacion_trans'];
            $this->guia->razonsocial_trans    = $_POST['razonsocial_trans'];
            $this->guia->placa                = $_POST['placa'];
            $this->guia->dirpartida           = $_POST['dirpartida'];
            $this->guia->tipo_guia            = $_POST['tipo_guia'];
            $this->guia->idfactura_mod        = null;
            $this->guia->numero_documento_mod = null;
            $this->guia->nro_autorizacion_mod = null;
            $this->guia->fec_emision_mod      = null;
            $this->guia->iddocumento_mod      = null;
            if (isset($_POST['idfactura_mod'])) {
                if ($_POST['idfactura_mod'] != "") {
                    $facturascli = new facturascli();
                    //Busco los datos de la factura utilizada para almacenar los datos
                    $factura_mod = $facturascli->get($_POST['idfactura_mod']);
                    if ($factura_mod) {
                        $this->guia->idfactura_mod        = $factura_mod->idfacturacli;
                        $this->guia->numero_documento_mod = $factura_mod->numero_documento;
                        $this->guia->nro_autorizacion_mod = $factura_mod->nro_autorizacion;
                        $this->guia->fec_emision_mod      = $factura_mod->fec_emision;
                        $this->guia->coddocumento_mod     = $factura_mod->coddocumento;
                        $this->guia->iddocumento_mod      = $factura_mod->iddocumento;
                    } else {
                        $this->new_error_msg("Documento Modificado no encontrado.");
                    }
                }
            }

            $this->guia->observaciones = null;
            if ($_POST['observaciones'] != '') {
                $this->guia->observaciones = $_POST['observaciones'];
            }

            $this->guia->nick_modificacion = $this->user->nick;
            $this->guia->fec_modificacion  = date('Y-m-d');

            if ($this->guia->save()) {
                $this->new_message("Guía de Remisión modificada correctamente.");
            } else {
                $this->new_error_msg("Error al editar la Guía de Remisión, verifique los datos y vuelva a intentarlo");
            }
        } else {
            $this->new_error_msg("Guía de Remisión no Encontrada");
        }
    }

    private function reenviar_correo()
    {
        if ($this->guia) {
            if ($this->guia->anulado) {
                $this->new_advice("El Documento se encuentra anulado, no se puede reenviar.");
                return;
            } else if ($this->guia->estado_sri != 'AUTORIZADO') {
                $this->new_advice("El Documento no se encuentra Autorizado, no se puede reenviar.");
                return;
            }
            
            $reenvio = $this->enviar_correo->correo_docs_ventas($this->guia, $this->empresa, false, true, $_POST['copias']);
            if ($reenvio['error'] == 'F') {
                $this->new_message($reenvio['msj']);
            } else {
                $this->new_advice($reenvio['msj']);
            }
        } else {
            $this->new_error_msg("Documento no Encontrado");
        }
    }

    private function buscar_transportista()
    {
        $this->template = false;
        $response       = array();
        $transportistas = $this->guiascli->getTransportistas($_GET['buscar_trans']);
        foreach ($transportistas as $key => $trans) {
            $response[] = array("value" => $trans['identificacion_trans'], "label" => $trans['razonsocial_trans'] . " - " . $trans['placa']);
        }
        echo json_encode($response);
        exit;
    }

    private function get_transportista()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Transportista No encontrado.');
        $transportista  = $this->guiascli->get_transp($_POST['trasnp']);
        if ($transportista) {
            $result = array('error' => 'F', 'msj' => '', 'trans' => $transportista);
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
