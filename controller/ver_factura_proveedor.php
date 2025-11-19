<?php
/**
 * Controlador de Factura -> Ver Factura Proveedor.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_factura_proveedor extends controller
{
    //variables
    public $factura;
    //modelos
    public $facturasprov;
    public $lineasfacturasprov;
    public $articulos;
    public $trans_pagos;
    public $formaspago;
    public $tiposretenciones;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ver Factura Proveedor', 'Compras', false, false);
    }

    protected function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->impresion = $this->user->have_access_to('impresion_compras');

        $this->facturasprov       = new facturasprov();
        $this->lineasfacturasprov = new lineasfacturasprov();
        $this->articulos          = new articulos();
        $this->trans_pagos        = new trans_pagos();
        $this->formaspago         = new formaspago();
        $this->tiposretenciones   = new tiposretenciones();
        $this->enviar_correo      = new envio_correos();
        $this->proveedores        = new proveedores();
        $this->sustentos          = new sustentos();

        $this->factura = false;
        if (isset($_GET['id'])) {
            $this->factura = $this->facturasprov->get($_GET['id']);
            if (!$this->factura) {
                $this->new_advice("No se encuentra la factura seleccionada.");
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
        } else if (isset($_POST['buscar_pagos'])) {
            $this->buscar_pagos();
        } else if (isset($_POST['buscar_fp'])) {
            $this->buscar_fp();
        } else if (isset($_POST['nuevo_pago'])) {
            $this->agregar_pago();
        } else if (isset($_POST['eliminar_pago'])) {
            $this->eliminar_pago();
        } else if (isset($_GET['autorizar'])) {
            $this->procesar_autorizacion();
        } else if (isset($_GET['autorizar_ret'])) {
            $this->procesar_autorizacion_ret();
        } else if (isset($_POST['validar_edicion'])) {
            $this->validar_edicion();
        } else if (isset($_POST['validar_edicion_retencion_masivo'])) {
            $this->validar_edicion_retencion_masivo();
        } else if (isset($_POST['validar_edicion_retencion'])) {
            $this->validar_edicion_retencion();
        } else if (isset($_POST['editar_doc'])) {
            $this->editar_documento();
        } else if (isset($_GET['buscar_proveedor'])) {
            $this->buscar_proveedores();
        } else if (isset($_POST['idproveedor'])) {
            $this->buscar_proveedor();
        } else if (isset($_POST['validar_anulacion'])) {
            $this->validar_anulacion();
        } else if (isset($_POST['anular_doc'])) {
            $this->tratar_anulacion();
        } else if (isset($_POST['editar_ret_masiva'])) {
            $this->editar_ret_masiva();
        } else if (isset($_POST['editar_ret'])) {
            $this->editar_retencion();
        } else if (isset($_POST['reenviar_correo'])) {
            $this->reenviar_correo();
        }
    }

    private function eliminar_linea()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Factura No Encontrada.');
        if ($this->factura) {
            $linea = $this->lineasfacturasprov->get($_POST['eliminar_linea']);
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
                    $this->factura->totalirbp -= $linea->valorirbp;

                    if (!$this->factura->getlineas()) {
                        $this->factura->base_gra       = 0;
                        $this->factura->base_noi       = 0;
                        $this->factura->base_0         = 0;
                        $this->factura->base_exc       = 0;
                        $this->factura->totaldescuento = 0;
                        $this->factura->totalice       = 0;
                        $this->factura->totaliva       = 0;
                        $this->factura->totalirbp      = 0;
                    }

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
                $result = array('error' => 'T', 'msj' => 'Error al Eliminar la linea de la factura.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function eliminar_pago()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Factura No Encontrada.');
        if ($this->factura) {
            $pago0 = new \trans_pagos();
            $pago  = $pago0->get($_POST['eliminar_pago']);
            if ($pago) {
                if ($pago->idpago) {
                    $result = array('error' => 'T', 'msj' => 'El Pago se encuentra asociado a Pago Masivo. Verifique y vuelva a intentarlo.');
                } else if ($pago->delete()) {
                    $result = array('error' => 'F', 'msj' => 'Pago Eliminado Correctamente.');
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al eliminar el pago de la Factura.');
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Pago No Encontrado.');
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
            $lineas = $this->lineasfacturasprov->all_by_idfacturaprov($this->factura->idfacturaprov);
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
        $articulo       = $this->articulos->get_by_codbarras($this->empresa->idempresa, $_POST['codigobarras']);
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
        $articulo       = $this->articulos->get($_POST['idarticulo']);
        if ($articulo) {
            $result = array('error' => 'F', 'msj' => '', 'art' => $articulo);
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_articulos()
    {
        $this->template = false;
        $articulos      = buscar_articulos($this->empresa->idempresa, $_GET['buscar_articulo'], '', '', '', '', '', '', true, true);
        echo json_encode($articulos);
        exit;
    }

    private function agregar_linea()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Factura No Encontrada.');
        if ($this->factura) {
            $linea                = new lineasfacturasprov();
            $linea->idfacturaprov = $this->factura->idfacturaprov;
            $linea->idarticulo    = $_POST['idarticulo'];
            $linea->idimpuesto    = $_POST['idimpuesto'];
            $linea->codprincipal  = $_POST['codprincipal'];
            $linea->descripcion   = $_POST['descripcion'];
            $linea->cantidad      = $_POST['cantidad'];
            $linea->pvpunitario   = $_POST['pvpunitario'];
            $linea->dto           = $_POST['dto'];
            $linea->pvptotal      = $_POST['pvptotal'];
            $linea->pvpsindto     = $linea->cantidad * $linea->pvpunitario;
            $linea->valorice      = $_POST['valorice'];
            $linea->valoriva      = $_POST['valoriva'];
            $linea->valorirbp     = $_POST['valorirbp'];
            //Retenciones
            if (isset($_POST['idretencion_renta'])) {
                $linea->idretencion_renta = $_POST['idretencion_renta'];
            }
            if (isset($_POST['idretencion_iva'])) {
                $linea->idretencion_iva = $_POST['idretencion_iva'];
            }
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
                $this->factura->totalirbp += $linea->valorirbp;

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
        echo json_encode($result);
        exit;
    }

    private function buscar_pagos()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Factura no Encontrada');
        if ($this->factura) {
            if ($this->factura->total > 0) {
                $pagos  = $this->trans_pagos->all_by_idfacturaprov($this->factura->idfacturaprov);
                $result = array('error' => 'F', 'msj' => '', 'total' => $this->factura->total, 'pagos' => $pagos);
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

    private function agregar_pago()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Factura no Encontrada');
        if ($this->factura) {
            $pago                = new \trans_pagos();
            $pago->idempresa     = $this->empresa->idempresa;
            $pago->idproveedor   = $this->factura->idproveedor;
            $pago->idfacturaprov = $this->factura->idfacturaprov;
            $pago->idformapago   = $_POST['idformapago'];
            $pago->tipo          = 'Pago';
            $pago->fecha_trans   = $_POST['fecha_trans'];
            if ($_POST['num_doc'] != '') {
                $pago->num_doc = $_POST['num_doc'];
            }
            $pago->debito        = $_POST['valor'];
            $pago->esabono       = true;
            $pago->fec_creacion  = date('Y-m-d');
            $pago->nick_creacion = $this->user->nick;
            if ($pago->save()) {
                $result = array('error' => 'F', 'msj' => 'Pago Registrado Correctamente.');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al generar el Pago.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function procesar_autorizacion()
    {
        if ($this->empresa->activafacelec) {
            if ($this->factura) {
                if ($this->factura->saldoinicial) {
                    $this->new_advice('El documento es un saldo inicial, no se puede realizar la autorización en el SRI.');
                    return;
                }
                if ($this->factura->getlineas()) {
                    if ($this->factura->estado_sri != 'AUTORIZADO') {
                        $autorizar_sri = new autorizar_sri();
                        if ($this->factura->coddocumento == '03') {
                            $carpeta = 'liquidacionescompra';
                        } else {
                            $this->new_error_msg('Documento No encontrado.');
                            return;
                        }
                        $rutaXmlFirmado = JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/documentosElectronicos/" . $carpeta . "/autorizados/";
                        $archivoFirmado = $rutaXmlFirmado . $this->factura->numero_documento . ".xml";
                        if (!file_exists($archivoFirmado)) {
                            $result = $autorizar_sri->procesar_documento_sri($this->factura, $this->empresa, false);
                            if ($result['error'] == 'F') {
                                if (file_exists($archivoFirmado)) {
                                    $archivoXml = simplexml_load_file($archivoFirmado);
                                    if ($archivoXml) {
                                        $this->factura->estado_sri       = $archivoXml->estado;
                                        $this->factura->fec_autorizacion = substr($archivoXml->fechaAutorizacion, 0, 10);
                                        $this->factura->hor_autorizacion = substr($archivoXml->fechaAutorizacion, 11, 8);
                                        if ($this->factura->save()) {
                                            $correo = $this->enviar_correo->correo_docs_ventas($this->factura, $this->empresa, false);
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
                                $this->factura->estado_sri       = $archivoXml->estado;
                                $this->factura->fec_autorizacion = substr($archivoXml->fechaAutorizacion, 0, 10);
                                $this->factura->hor_autorizacion = substr($archivoXml->fechaAutorizacion, 11, 8);
                                if ($this->factura->save()) {
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

    private function procesar_autorizacion_ret()
    {
        if ($this->empresa->activafacelec) {
            if ($this->factura) {
                if ($this->factura->saldoinicial) {
                    $this->new_advice('El documento es un saldo inicial, no se puede realizar la autorización en el SRI.');
                    return;
                }
                if ($this->factura->getlineas()) {
                    if ($this->factura->estado_sri_ret != 'AUTORIZADO') {
                        $autorizar_sri  = new autorizar_sri();
                        $carpeta        = 'retencionescompra';
                        $rutaXmlFirmado = JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/documentosElectronicos/" . $carpeta . "/autorizados/";
                        $archivoFirmado = $rutaXmlFirmado . $this->factura->numero_retencion . ".xml";
                        if (!file_exists($archivoFirmado)) {
                            $result = $autorizar_sri->procesar_documento_sri($this->factura, $this->empresa, true);
                            if ($result['error'] == 'F') {
                                if (file_exists($archivoFirmado)) {
                                    $archivoXml = simplexml_load_file($archivoFirmado);
                                    if ($archivoXml) {
                                        $this->factura->estado_sri_ret       = $archivoXml->estado;
                                        $this->factura->fec_autorizacion_ret = substr($archivoXml->fechaAutorizacion, 0, 10);
                                        $this->factura->hor_autorizacion_ret = substr($archivoXml->fechaAutorizacion, 11, 8);
                                        if ($this->factura->save()) {
                                            $correo = $this->enviar_correo->correo_docs_ventas($this->factura, $this->empresa, true);
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
                                $this->factura->estado_sri_ret       = $archivoXml->estado;
                                $this->factura->fec_autorizacion_ret = substr($archivoXml->fechaAutorizacion, 0, 10);
                                $this->factura->hor_autorizacion_ret = substr($archivoXml->fechaAutorizacion, 11, 8);
                                if ($this->factura->save()) {
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
        $result         = array('error' => 'T', 'msj' => 'Documento no Encontrado');
        if ($this->factura) {
            if (!$this->allow_modify) {
                $result = array('error' => 'T', 'msj' => 'El usuario no tiene permiso para editar el documento.');
            } else if ($this->factura->anulado) {
                $result = array('error' => 'T', 'msj' => 'La Factura se encuentra anulada, no se puede editar.');
            } else if ($this->factura->estado_sri == 'AUTORIZADO') {
                $result = array('error' => 'T', 'msj' => $this->factura->get_tipodoc() . ' Autorizada, no se puede editar.');
            } else if ($this->factura->estado_sri_ret == 'AUTORIZADO') {
                $result = array('error' => 'T', 'msj' => 'La Retención se encuentra Autorizada, no se puede editar.');
            } else {
                $pagos = $this->trans_pagos->all_by_idfacturaprov($this->factura->idfacturaprov, true);
                if (count($pagos) > 1) {
                    $result = array('error' => 'T', 'msj' => 'La Factura tiene pagos asociados, no se puede editar.');
                } else {
                    $result = array('error' => 'F', 'msj' => '', 'factura' => $this->factura, 'proveedor' => $this->factura->get_proveedor());
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    public function validar_edicion_retencion_masivo()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Documento no Encontrado');
        if ($this->factura) {
            if (!$this->allow_modify) {
                $result = array('error' => 'T', 'msj' => 'El usuario no tiene permiso para editar el documento.');
            } else if ($this->factura->anulado) {
                $result = array('error' => 'T', 'msj' => 'La Factura se encuentra anulada, no se puede editar.');
            } else if ($this->factura->estado_sri_ret == 'AUTORIZADO') {
                $result = array('error' => 'T', 'msj' => 'La Retención se encuentra Autorizada, no se puede editar.');
            } else {
                $pagos = $this->trans_pagos->all_by_idfacturaprov($this->factura->idfacturaprov, true);
                if (count($pagos) > 1) {
                    $result = array('error' => 'T', 'msj' => 'La Factura tiene pagos asociados, no se puede editar.');
                } else {
                    $result = array('error' => 'F', 'msj' => '', 'factura' => $this->factura);
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    public function validar_edicion_retencion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Documento no Encontrado');
        if ($this->factura) {
            if (!$this->allow_modify) {
                $result = array('error' => 'T', 'msj' => 'El usuario no tiene permiso para editar el documento.');
            } else if ($this->factura->anulado) {
                $result = array('error' => 'T', 'msj' => 'La Factura se encuentra anulada, no se puede editar.');
            } else if ($this->factura->estado_sri_ret == 'AUTORIZADO') {
                $result = array('error' => 'T', 'msj' => 'La Retención se encuentra Autorizada, no se puede editar.');
            } else {
                $pagos = $this->trans_pagos->all_by_idfacturaprov($this->factura->idfacturaprov, true);
                if (count($pagos) > 1) {
                    $result = array('error' => 'T', 'msj' => 'La Factura tiene pagos asociados, no se puede editar.');
                } else {
                    $linea  = $this->lineasfacturasprov->get($_POST['validar_edicion_retencion']);
                    $result = array('error' => 'F', 'msj' => '', 'linea' => $linea);
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_proveedores()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_proveedores($this->empresa->idempresa, $_GET['buscar_proveedor']);

        echo json_encode($result);
        exit;
    }

    private function buscar_proveedor()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Proveedor No encontrado.');
        $proveedor      = $this->proveedores->get($_POST['idproveedor']);
        if ($proveedor) {
            $result = array('error' => 'F', 'msj' => '', 'prov' => $proveedor);
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
                $this->new_advice("La Factura se encuentra anulada, no se puede editar.");
                return;
            } else if ($this->factura->estado_sri == 'AUTORIZADO') {
                $this->new_advice("El Documento se encuentra Autorizado, no se puede editar.");
                return;
            } else if ($this->factura->estado_sri_ret == 'AUTORIZADO') {
                $this->new_advice("La Retención se encuentra Autorizada, no se puede editar.");
                return;
            } else {
                $pagos = $this->trans_pagos->all_by_idfacturaprov($this->factura->idfacturaprov, true);
                if (count($pagos) > 1) {
                    $this->new_advice("La Factura tiene pagos asociados, no se puede editar.");
                    return;
                }
            }

            if ($this->factura->coddocumento == '04' && $this->factura->idfactura_mod) {
            } else if ($this->factura->coddocumento == '05' && $this->factura->idfactura_mod) {
            } else {
                $this->factura->idproveedor    = $_POST['idproveedor'];
                $this->factura->tipoid         = $_POST['tipidenproveedor'];
                $this->factura->identificacion = $_POST['identproveedor'];
                $this->factura->razonsocial    = $_POST['razonsproveedor'];
            }

            $this->factura->idsustento           = $_POST['idsustento'];
            $this->factura->fec_emision          = $_POST['fec_emision'];
            $this->factura->fec_registro         = $_POST['fec_registro'];
            $this->factura->diascredito          = $_POST['diascredito'];
            $this->factura->direccion            = $_POST['direccion'];
            $this->factura->email                = $_POST['email'];
            $this->factura->observaciones        = null;
            if ($_POST['observaciones'] != '') {
                $this->factura->observaciones = $_POST['observaciones'];
            }

            $this->factura->nro_autorizacion = $_POST['nro_autorizacion'];

            $this->factura->nick_modificacion = $this->user->nick;
            $this->factura->fec_modificacion  = date('Y-m-d');

            if ($this->factura->save()) {
                $trinv = new trans_inventario();
                $trinv->actualizar_proveedor($this->empresa->idempresa, $this->factura->idfacturaprov, $this->factura->idproveedor);
                $this->new_message("Documento modificado correctamente.");
            } else {
                $this->new_error_msg("Error al editar el Documento, verifique los datos y vuelva a intentarlo");
            }
        } else {
            $this->new_error_msg("Documento no Encontrado");
        }
    }

    private function validar_anulacion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Documento no Encontrado');
        if ($this->factura) {
            if (!$this->allow_delete) {
                $result = array('error' => 'T', 'msj' => 'El usuario no tiene permiso para anular el documento.');
            } else if ($this->factura->anulado) {
                $result = array('error' => 'T', 'msj' => 'El documento ya se encuentra anulado.');
            } else {
                $pagos = $this->trans_pagos->all_by_idfacturaprov($this->factura->idfacturaprov, true);
                if (count($pagos) > 1) {
                    $result = array('error' => 'T', 'msj' => 'La Factura tiene pagos asociados, no se puede anular.');
                } else {
                    $result = array('error' => 'F', 'msj' => '', 'factura' => $this->factura);
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    private function tratar_anulacion()
    {
        if ($this->factura) {
            if (!$this->allow_delete) {
                $this->new_error_msg('El usuario no tiene permiso para anular el documento.');
                return;
            }
            if ($this->factura->anulado) {
                $this->new_error_msg('El documento ya se encuentra anulado.');
                return;
            }

            if (isset($_POST['tipo'])) {
                if ($_POST['tipo'] == 'doc') {
                    if (!$this->factura->anular_documento($this->user->nick)) {
                        $this->new_advice("Error al anular el documento.");
                        return;
                    }
                } else if ($_POST['tipo'] == 'ret') {
                    if (!$this->factura->anular_retencion($this->user->nick)) {
                        $this->new_advice("Error al anular la retención.");
                        return;
                    }
                }

                $this->new_message("Anulación generada correctamente");
                $this->factura = $this->facturasprov->get($_GET['id']);
                if (!$this->factura) {
                    $this->new_advice("No se encuentra la factura seleccionada.");
                } else if ($this->factura->idempresa != $this->empresa->idempresa) {
                    $this->factura = false;
                    $this->new_advice("El documento no esta disponible para su empresa.");
                    return;
                }
            }
        } else {
            $this->new_error_msg("Documento no Encontrado");
            return;
        }
    }

    private function editar_ret_masiva()
    {
        if ($this->factura) {
            if (!$this->allow_modify) {
                $this->new_error_msg('El usuario no tiene permiso para editar el documento.');
                return;
            }
            if ($this->factura->anulado) {
                $this->new_error_msg('El documento se encuentra anulado, no es posible editar.');
                return;
            }
            if ($this->factura->estado_sri_ret == 'AUTORIZADO') {
                $this->new_error_msg('La retención se encuentra Autorizada, no es posible editar.');
                return;
            }

            if ($this->lineasfacturasprov->editar_retencion_masiva($_POST['idretencion_renta_masiva'], $_POST['idretencion_iva_masiva'], $this->factura->idfacturaprov)) {
                $this->new_message("Retención Aplicada correctamente.");
                $this->factura->save();
            } else {
                $this->new_advice("Existió un error al aplicar la retención, verifique los datos y vuelva a intentarlo.");
            }
        } else {
            $this->new_error_msg("Documento no Encontrado");
            return;
        }
    }

    private function editar_retencion()
    {
        $this->template = false;
        $result = array('error' => 'T', 'msj' => 'Documento no Encontrado');
        if ($this->factura) {
            if (!$this->allow_modify) {
                $result = array('error' => 'T', 'msj' => 'El usuario no tiene permiso para editar el documento.');
            } else if ($this->factura->anulado) {
                $result = array('error' => 'T', 'msj' => 'El documento se encuentra anulado, no es posible editar.');
            } else if ($this->factura->estado_sri_ret == 'AUTORIZADO') {
                $result = array('error' => 'T', 'msj' => 'La retención se encuentra Autorizada, no es posible editar.');
            } else {
                $linea                    = $this->lineasfacturasprov->get($_POST['editar_ret']);
                $linea->idretencion_renta = $_POST['idretencion_renta_ind'];
                $linea->idretencion_iva   = $_POST['idretencion_iva_ind'];
                $linea->nick_modificacion = $this->user->nick;
                $linea->fec_modificacion  = date('Y-m-d');

                if ($linea->save()) {
                    $result = array('error' => 'F', 'msj' => 'Retención actualizada correctamente.');
                    $this->factura->save();
                } else {
                    $result = array('error' => 'T', 'msj' => 'Existió un error al aplicar la retención, verifique los datos y vuelva a intentarlo.');
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    private function reenviar_correo()
    {
        if ($this->factura) {
            $isretencion = false;
            if ($this->factura->anulado) {
                $this->new_advice("El Documento se encuentra anulado, no se puede reenviar.");
                return;
            } else {
                if ($_POST['reenviar_correo'] == 'documento') {
                    if ($this->factura->estado_sri != 'AUTORIZADO') {
                        $this->new_advice("El Documento no se encuentra Autorizado, no se puede reenviar.");
                        return;
                    }
                } else {
                    $isretencion = true;
                    if ($this->factura->estado_sri_ret != 'AUTORIZADO') {
                        $this->new_advice("La Retención no se encuentra Autorizada, no se puede reenviar.");
                        return;
                    }
                }
            }

            $envio = new \envio_correos();
            $reenvio = $envio->correo_docs_ventas($this->factura, $this->empresa, $isretencion, true, $_POST['copias']);
            if ($reenvio['error'] == 'F') {
                $this->new_message($reenvio['msj']);
            } else {
                $this->new_advice($reenvio['msj']);
            }
        } else {
            $this->new_error_msg("Factura no Encontrada");
        }
    }
}
