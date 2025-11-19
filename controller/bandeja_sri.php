<?php
/**
 * Controlador de SRI -> Bandeja.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class bandeja_sri extends controller
{
    //Filtros
    public $query;
    public $desde;
    public $hasta;
    //Modelos
    public $impuestos;
    public $articulos;
    public $documentos;
    public $proveedores;
    public $clientes;
    public $facturasprov;
    public $facturascli;
    public $retencionescli;
    public $bandeja;
    public $lineasbandejasri;
    public $lineasretbandejasri;
    public $tiposretenciones;
    //Contadores
    public $num_facproveedor;
    public $num_ncpproveedor;
    public $num_ncdproveedor;
    public $num_retcliente;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Bandeja SRI', 'SRI', true, true, true, 'bi bi-inbox');
    }

    protected function private_core()
    {
        $this->init_filter();
        $this->init_models();

        if (isset($_POST['nro_aut']) && $_POST['nro_aut'] != '') {
            $this->procesar_documento_electronico($_POST['nro_aut']);
        } else if (isset($_FILES['archivotxt']) && $_FILES['archivotxt']['name'] != '') {
            $this->procesar_archivo_txt();
        }

        if (isset($_GET['coddoc'])) {
            $this->buscar_datos();
        } else if (isset($_GET['noprocesar'])) {
            $this->noprocesardoc();
        } else if (isset($_GET['pendiente'])) {
            $this->pendientedoc();
        } else if (isset($_POST['procesar'])) {
            $this->procesar_documento();
        } else if (isset($_POST['procesar_ret'])) {
            $this->procesar_retencion();
        }

        $this->buscar();
    }

    private function init_filter()
    {
        $this->query = '';
        if (isset($_POST['query']) && $_POST['query'] != '') {
            $this->query = $_POST['query'];
        }

        $this->desde = '';
        if (isset($_POST['desde']) && $_POST['desde'] != '') {
            $this->desde = $_POST['desde'];
        }

        $this->hasta = '';
        if (isset($_POST['hasta']) && $_POST['hasta'] != '') {
            $this->hasta = $_POST['hasta'];
        }

        $this->estado = 0;
        if (isset($_POST['estado'])) {
            $this->estado = $_POST['estado'];
        }

        $this->num_facproveedor = 0;
        $this->num_ncpproveedor = 0;
        $this->num_ncdproveedor = 0;
        $this->num_retcliente   = 0;
    }

    private function init_models()
    {
        $this->impuestos           = new impuestos();
        $this->articulos           = new articulos();
        $this->documentos          = new documentos();
        $this->proveedores         = new proveedores();
        $this->clientes            = new clientes();
        $this->facturasprov        = new facturasprov();
        $this->facturascli         = new facturascli();
        $this->retencionescli      = new retencionescli();
        $this->bandeja             = new cab_bandejasri();
        $this->lineasbandejasri    = new lineasbandejasri();
        $this->lineasretbandejasri = new lineasretbandejasri();
        $this->tiposretenciones    = new tiposretenciones();
    }

    public function buscar()
    {
        $this->num_facproveedor = $this->bandeja->getNumDocs($this->empresa->idempresa, $this->query, '01', $this->desde, $this->hasta, $this->estado);
        $this->num_ncpproveedor = $this->bandeja->getNumDocs($this->empresa->idempresa, $this->query, '04', $this->desde, $this->hasta, $this->estado);
        $this->num_ncdproveedor = $this->bandeja->getNumDocs($this->empresa->idempresa, $this->query, '05', $this->desde, $this->hasta, $this->estado);
        $this->num_retcliente   = $this->bandeja->getNumDocs($this->empresa->idempresa, $this->query, '07', $this->desde, $this->hasta, $this->estado);
    }

    public function buscar_datos()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No existe Información.');
        $lineas         = $this->bandeja->allDocs($this->empresa->idempresa, $this->query, $_GET['coddoc'], $this->desde, $this->hasta, $this->estado);
        if ($lineas) {
            $result = array('error' => 'F', 'msj' => '', 'lineas' => $lineas);
        }

        echo json_encode($result);
        exit;
    }

    private function procesar_archivo_txt()
    {
        if (is_uploaded_file($_FILES['archivotxt']['tmp_name'])) {
            $archivo = fopen($_FILES['archivotxt']['tmp_name'], 'r');
            $paso    = true;
            //Recorro el archivo
            while (!feof($archivo)) {
                $linea = fgets($archivo);
                $lin   = explode("\t", $linea);
                foreach ($lin as $key => $l) {
                    if (strlen($l) == 49 && is_numeric($l)) {
                        if (!$this->procesar_documento_electronico($l)) {
                            $paso = false;
                        }
                        break;
                    }
                }
            }

            if ($paso) {
                $this->new_message("Archivo procesado correctamente.");
            }
        } else {
            $this->new_error_msg("se encontró un error al cargar el archivo.");
        }
    }

    private function procesar_documento_electronico($nroaut)
    {
        $respuesta = consultardocsri($nroaut);
        if ($respuesta['encontrado']) {
            $comprobante = $respuesta['xml'];
            $fec_aut     = $respuesta['fec_aut'];
            if ($comprobante->infoTributaria->codDoc == '01') {
                //Es Factura
                if ($comprobante->infoFactura->identificacionComprador == $this->empresa->ruc) {
                    $this->procesar_fac_proveedor($comprobante, $fec_aut);
                } else {
                    $this->new_advice('Factura: ' . $nroaut . ' no valida para la empresa.');
                    return false;
                }
            } else if ($comprobante->infoTributaria->codDoc == '04') {
                //Es una Nota de Credito
                if ($comprobante->infoNotaCredito->identificacionComprador == $this->empresa->ruc) {
                    $this->procesar_ncp_proveedor($comprobante, $fec_aut);
                } else {
                    $this->new_advice('Nota de Crédito: ' . $nroaut . ' no valida para la empresa.');
                    return false;
                }
            } else if ($comprobante->infoTributaria->codDoc == '05') {
                //Es una Nota de Debito
                if ($comprobante->infoNotaDebito->identificacionComprador == $this->empresa->ruc) {
                    $this->procesar_ncd_proveedor($comprobante, $fec_aut);
                } else {
                    $this->new_advice('Nota de Débito: ' . $nroaut . ' no valida para la empresa.');
                    return false;
                }
            } else if ($comprobante->infoTributaria->codDoc == '07') {
                //Es una retencion de Cliente
                if ($comprobante->infoCompRetencion->identificacionSujetoRetenido == $this->empresa->ruc) {
                    $this->procesar_ret_cliente($comprobante, $fec_aut);
                } else {
                    $this->new_advice('Retención: ' . $nroaut . ' no valida para la empresa.');
                    return false;
                }
            }
        } else {
            $this->new_advice($respuesta['msj'] . ". Nro. Aut: " . $nroaut);
            return false;
        }
        return true;
    }

    private function procesar_fac_proveedor($fac, $fec_aut)
    {
        $numdoc = $fac->infoTributaria->estab . "-" . $fac->infoTributaria->ptoEmi . "-" . $fac->infoTributaria->secuencial;
        //Valido que no exista ya creada la factura en el sistema
        if (!$this->facturasprov->get_by_nro_aut($this->empresa->idempresa, $fac->infoTributaria->claveAcceso)) {
            $bandeja            = new cab_bandejasri();
            $bandeja->idempresa = $this->empresa->idempresa;
            //Busco el proveedor
            $prov = $this->proveedores->get_by_identificacion($bandeja->idempresa, $fac->infoTributaria->ruc);
            if ($prov) {
                $bandeja->idproveedor = $prov->idproveedor;
            }
            $bandeja->coddocumento = $fac->infoTributaria->codDoc;
            //Busco el documento
            $documento = $this->documentos->get_by_codigo($bandeja->coddocumento);
            if ($documento) {
                $bandeja->iddocumento = $documento->iddocumento;
            } else {
                $this->new_error_msg("Documento Factura no encontrado.");
                return false;
            }
            $bandeja->tipoid         = 'R';
            $bandeja->identificacion = $fac->infoTributaria->ruc;
            $bandeja->razonsocial    = $fac->infoTributaria->razonSocial;
            if (isset($fac->infoTributaria->nombreComercial)) {
                $bandeja->nombrecomercial = $fac->infoTributaria->nombreComercial;
            } else {
                $bandeja->nombrecomercial = $bandeja->razonsocial;
            }
            $bandeja->numero_documento = $numdoc;
            $bandeja->nro_autorizacion = $fac->infoTributaria->claveAcceso;
            $bandeja->fec_autorizacion = substr($fec_aut, 0, 10);
            $bandeja->hor_autorizacion = substr($fec_aut, 11, 8);
            $fecha                     = explode("/", $fac->infoFactura->fechaEmision);
            $bandeja->fec_emision      = $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];
            $bandeja->hora_emision     = $bandeja->hor_autorizacion;
            $bandeja->fec_caducidad    = $bandeja->fec_emision;
            $bandeja->fec_registro     = $bandeja->fec_emision;
            $bandeja->fec_creacion     = date('Y-m-d');
            $bandeja->nick_creacion    = $this->user->nick;

            if ($bandeja->save()) {
                $paso = true;
                //Si se guarda almaceno el detalle
                foreach ($fac->detalles->detalle as $key => $det) {
                    $lin               = new lineasbandejasri();
                    $lin->idbandejasri = $bandeja->idbandejasri;
                    $lin->codprincipal = $det->codigoPrincipal;
                    if (isset($det->codigoAuxiliar)) {
                        $lin->codprincipal = $det->codigoAuxiliar;
                    }
                    //Busco si ya tiene homologacion si existe el idproveedor
                    if ($bandeja->idproveedor) {
                        $l = $this->lineasbandejasri->getHomologacionArt($this->empresa->idempresa, $bandeja->idproveedor, $lin->codprincipal);
                        if ($l) {
                            $lin->idarticulo = $l->idarticulo;
                        }
                    }
                    $lin->descripcion = $det->descripcion;
                    $lin->cantidad    = floatval($det->cantidad);
                    $lin->pvptotal    = floatval($det->precioTotalSinImpuesto);
                    $lin->pvpunitario = round($lin->pvptotal / $lin->cantidad, 6);

                    foreach ($det->impuestos->impuesto as $key => $imp) {
                        //IVA
                        if ($imp->codigo == 2) {
                            if ($imp->codigoPorcentaje == 0) {
                                // IVA 0%
                                $codimp = $this->impuestos->get_by_codigo('IVA0');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_0 += $lin->pvptotal;
                            } else if ($imp->codigoPorcentaje == 2) {
                                // IVA 12%
                                $codimp = $this->impuestos->get_by_codigo('IVA12');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_gra += $lin->pvptotal;
                            } else if ($imp->codigoPorcentaje == 3) {
                                // IVA 14%
                                $codimp = $this->impuestos->get_by_codigo('IVA14');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_gra += $lin->pvptotal;
                            } else if ($imp->codigoPorcentaje == 6) {
                                // IVA No Objeto
                                $codimp = $this->impuestos->get_by_codigo('IVANO');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_noi += $lin->pvptotal;
                            } else if ($imp->codigoPorcentaje == 7) {
                                // IVA Excento IVA
                                $codimp = $this->impuestos->get_by_codigo('IVAEX');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_exc += $lin->pvptotal;
                            } else if ($imp->codigoPorcentaje == 8) {
                                // IVA Diferenciado
                                $codimp = $this->impuestos->get_by_codigo('IVA8');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_gra += $lin->pvptotal;
                            }
                            $lin->valoriva = floatval($imp->valor);
                        }
                        //ICE
                        if ($imp->codigo == 3) {
                            $lin->valorice = floatval($imp->valor);
                        }
                        //IRBPNR
                        if ($imp->codigo == 5) {
                            $lin->valorirbp = floatval($imp->valor);
                        }

                    }

                    $bandeja->totalice += $lin->valorice;
                    $bandeja->totaliva += $lin->valoriva;
                    $bandeja->totalirbp += $lin->valorirbp;

                    $lin->fec_creacion  = date('Y-m-d');
                    $lin->nick_creacion = $this->user->nick;

                    if (!$lin->save()) {
                        $paso = false;
                    } else {
                        if (!$bandeja->save()) {
                            $paso = false;
                        }
                    }
                }

                if ($paso) {
                    $this->new_message("Factura: " . $numdoc . " con Nro. Aut: " . $fac->infoTributaria->claveAcceso . " procesada correctamente.");
                } else {
                    $this->new_error_msg("Error al capturar el detalle de la Factura: " . $numdoc . " con Nro. Aut: " . $fac->infoTributaria->claveAcceso);
                    $bandeja->delete();
                    return false;
                }
            } else {
                $this->new_error_msg("Error al capturar la Factura: " . $numdoc . " con Nro. Aut: " . $fac->infoTributaria->claveAcceso);
                return false;
            }
        } else {
            $this->new_advice("La Factura: " . $numdoc . " con Nro. Aut: " . $fac->infoTributaria->claveAcceso . " ya se encuentra registrada");
            return false;
        }
        return true;
    }

    private function procesar_ncp_proveedor($ncp, $fec_aut)
    {
        $numdoc = $ncp->infoTributaria->estab . "-" . $ncp->infoTributaria->ptoEmi . "-" . $ncp->infoTributaria->secuencial;
        //Valido que no exista ya creada la Nota de credito en el sistema
        if (!$this->facturasprov->get_by_nro_aut($this->empresa->idempresa, $ncp->infoTributaria->claveAcceso)) {
            $bandeja            = new cab_bandejasri();
            $bandeja->idempresa = $this->empresa->idempresa;
            //Busco el proveedor
            $prov = $this->proveedores->get_by_identificacion($bandeja->idempresa, $ncp->infoTributaria->ruc);
            if ($prov) {
                $bandeja->idproveedor = $prov->idproveedor;
            }
            $bandeja->coddocumento = $ncp->infoTributaria->codDoc;
            //Busco el documento
            $documento = $this->documentos->get_by_codigo($bandeja->coddocumento);
            if ($documento) {
                $bandeja->iddocumento = $documento->iddocumento;
            } else {
                $this->new_error_msg("Documento Nota de Crédito no encontrado.");
                return false;
            }
            $bandeja->tipoid         = 'R';
            $bandeja->identificacion = $ncp->infoTributaria->ruc;
            $bandeja->razonsocial    = $ncp->infoTributaria->razonSocial;
            if (isset($ncp->infoTributaria->nombreComercial)) {
                $bandeja->nombrecomercial = $ncp->infoTributaria->nombreComercial;
            } else {
                $bandeja->nombrecomercial = $bandeja->razonsocial;
            }
            $bandeja->numero_documento = $numdoc;
            $bandeja->nro_autorizacion = $ncp->infoTributaria->claveAcceso;
            $bandeja->fec_autorizacion = substr($fec_aut, 0, 10);
            $bandeja->hor_autorizacion = substr($fec_aut, 11, 8);
            $fecha                     = explode("/", $ncp->infoNotaCredito->fechaEmision);
            $bandeja->fec_emision      = $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];
            $bandeja->hora_emision     = $bandeja->hor_autorizacion;
            $bandeja->fec_caducidad    = $bandeja->fec_emision;
            $bandeja->fec_registro     = date('Y-m-d');
            //Capturo el documento al que esta asociado
            $bandeja->coddocumento_mod = $ncp->infoNotaCredito->codDocModificado;
            $doc2                      = $this->documentos->get_by_codigo($bandeja->coddocumento_mod);
            if ($doc2) {
                $bandeja->iddocumento_mod = $doc2->iddocumento;
            }
            $bandeja->numero_documento_mod = $ncp->infoNotaCredito->numDocModificado;
            $fecha_mod                     = explode("/", $ncp->infoNotaCredito->fechaEmisionDocSustento);
            $bandeja->fecdoc_modificado    = $fecha_mod[2] . "-" . $fecha_mod[1] . "-" . $fecha_mod[0];

            //Valido si el documento esta creado en las compras
            $doc = $this->facturasprov->getDocumentoProveedor($this->empresa->idempresa, $bandeja->identificacion, $bandeja->coddocumento_mod, $bandeja->numero_documento_mod);
            if ($doc) {
                $bandeja->idfactura_mod        = $doc->idfacturaprov;
                $bandeja->nro_autorizacion_mod = $doc->nro_autorizacion_mod;
            }
            $bandeja->fec_creacion  = date('Y-m-d');
            $bandeja->nick_creacion = $this->user->nick;

            if ($bandeja->save()) {
                $paso = true;
                //Si se guarda almaceno el detalle
                foreach ($ncp->detalles->detalle as $key => $det) {
                    $lin               = new lineasbandejasri();
                    $lin->idbandejasri = $bandeja->idbandejasri;
                    $lin->codprincipal = $det->codigoInterno;
                    //Busco si ya tiene homologacion si existe el idproveedor
                    if ($bandeja->idproveedor) {
                        $l = $this->lineasbandejasri->getHomologacionArt($this->empresa->idempresa, $bandeja->idproveedor, $lin->codprincipal);
                        if ($l) {
                            $lin->idarticulo = $l->idarticulo;
                        }
                    }
                    $lin->descripcion = $det->descripcion;
                    $lin->cantidad    = floatval($det->cantidad);
                    $lin->pvptotal    = floatval($det->precioTotalSinImpuesto);
                    $lin->pvpunitario = round($lin->pvptotal / $lin->cantidad, 6);

                    foreach ($det->impuestos->impuesto as $key => $imp) {
                        //IVA
                        if ($imp->codigo == 2) {
                            if ($imp->codigoPorcentaje == 0) {
                                // IVA 0%
                                $codimp = $this->impuestos->get_by_codigo('IVA0');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_0 += $lin->pvptotal;
                            } else if ($imp->codigoPorcentaje == 2) {
                                // IVA 12%
                                $codimp = $this->impuestos->get_by_codigo('IVA12');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_gra += $lin->pvptotal;
                            } else if ($imp->codigoPorcentaje == 3) {
                                // IVA 14%
                                $codimp = $this->impuestos->get_by_codigo('IVA14');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_gra += $lin->pvptotal;
                            } else if ($imp->codigoPorcentaje == 6) {
                                // IVA No Objeto
                                $codimp = $this->impuestos->get_by_codigo('IVANO');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_noi += $lin->pvptotal;
                            } else if ($imp->codigoPorcentaje == 7) {
                                // IVA Excento IVA
                                $codimp = $this->impuestos->get_by_codigo('IVAEX');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_exc += $lin->pvptotal;
                            } else if ($imp->codigoPorcentaje == 8) {
                                // IVA Diferenciado
                                $codimp = $this->impuestos->get_by_codigo('IVA8');
                                if ($codimp) {
                                    $lin->idimpuesto = $codimp->idimpuesto;
                                }
                                $bandeja->base_gra += $lin->pvptotal;
                            }
                            $lin->valoriva = floatval($imp->valor);
                        }
                        //ICE
                        if ($imp->codigo == 3) {
                            $lin->valorice = floatval($imp->valor);
                        }
                        //IRBPNR
                        if ($imp->codigo == 5) {
                            $lin->valorirbp = floatval($imp->valor);
                        }

                        $bandeja->totalice += $lin->valorice;
                        $bandeja->totaliva += $lin->valoriva;
                        $bandeja->totalirbp += $lin->valorirbp;
                    }

                    $lin->fec_creacion  = date('Y-m-d');
                    $lin->nick_creacion = $this->user->nick;

                    if (!$lin->save()) {
                        $paso = false;
                    } else {
                        if (!$bandeja->save()) {
                            $paso = false;
                        }
                    }
                }

                if ($paso) {
                    $this->new_message("Nota de Crédito: " . $numdoc . " con Nro. Aut: " . $ncp->infoTributaria->claveAcceso . " procesada correctamente.");
                } else {
                    $this->new_error_msg("Error al capturar el detalle de la Nota de Crédito: " . $numdoc . " con Nro. Aut: " . $ncp->infoTributaria->claveAcceso);
                    $bandeja->delete();
                    return false;
                }
            } else {
                $this->new_error_msg("Error al capturar la Nota de Crédito: " . $numdoc . " con Nro. Aut: " . $ncp->infoTributaria->claveAcceso);
                return false;
            }
        } else {
            $this->new_advice("La Nota de Crédito: " . $numdoc . " con Nro. Aut: " . $ncp->infoTributaria->claveAcceso . " ya se encuentra registrada");
            return false;
        }
        return true;
    }

    private function procesar_ncd_proveedor($ncd, $fec_aut)
    {
        $numdoc = $ncd->infoTributaria->estab . "-" . $ncd->infoTributaria->ptoEmi . "-" . $ncd->infoTributaria->secuencial;
        //Valido que no exista ya creada la Nota de Debito en el sistema
        if (!$this->facturasprov->get_by_nro_aut($this->empresa->idempresa, $ncd->infoTributaria->claveAcceso)) {
            $bandeja            = new cab_bandejasri();
            $bandeja->idempresa = $this->empresa->idempresa;
            //Busco el proveedor
            $prov = $this->proveedores->get_by_identificacion($bandeja->idempresa, $ncd->infoTributaria->ruc);
            if ($prov) {
                $bandeja->idproveedor = $prov->idproveedor;
            }
            $bandeja->coddocumento = $ncd->infoTributaria->codDoc;
            //Busco el documento
            $documento = $this->documentos->get_by_codigo($bandeja->coddocumento);
            if ($documento) {
                $bandeja->iddocumento = $documento->iddocumento;
            } else {
                $this->new_error_msg("Documento Nota de Débito no encontrado.");
                return false;
            }
            $bandeja->tipoid         = 'R';
            $bandeja->identificacion = $ncd->infoTributaria->ruc;
            $bandeja->razonsocial    = $ncd->infoTributaria->razonSocial;
            if (isset($ncd->infoTributaria->nombreComercial)) {
                $bandeja->nombrecomercial = $ncd->infoTributaria->nombreComercial;
            } else {
                $bandeja->nombrecomercial = $bandeja->razonsocial;
            }
            $bandeja->numero_documento = $numdoc;
            $bandeja->nro_autorizacion = $ncd->infoTributaria->claveAcceso;
            $bandeja->fec_autorizacion = substr($fec_aut, 0, 10);
            $bandeja->hor_autorizacion = substr($fec_aut, 11, 8);
            $fecha                     = explode("/", $ncd->infoNotaDebito->fechaEmision);
            $bandeja->fec_emision      = $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];
            $bandeja->hora_emision     = $bandeja->hor_autorizacion;
            $bandeja->fec_caducidad    = $bandeja->fec_emision;
            $bandeja->fec_registro     = date('Y-m-d');
            //Capturo el documento al que esta asociado
            $bandeja->coddocumento_mod = $ncd->infoNotaDebito->codDocModificado;
            $doc2                      = $this->documentos->get_by_codigo($bandeja->coddocumento_mod);
            if ($doc2) {
                $bandeja->iddocumento_mod = $doc2->iddocumento;
            }
            $bandeja->numero_documento_mod = $ncd->infoNotaDebito->numDocModificado;
            $fecha_mod                     = explode("/", $ncd->infoNotaDebito->fechaEmisionDocSustento);
            $bandeja->fecdoc_modificado    = $fecha_mod[2] . "-" . $fecha_mod[1] . "-" . $fecha_mod[0];

            //Valido si el documento esta creado en las compras
            $doc = $this->facturasprov->getDocumentoProveedor($this->empresa->idempresa, $bandeja->identificacion, $bandeja->coddocumento_mod, $bandeja->numero_documento_mod);
            if ($doc) {
                $bandeja->idfactura_mod        = $doc->idfacturaprov;
                $bandeja->nro_autorizacion_mod = $doc->nro_autorizacion_mod;
            }

            $bandeja->fec_creacion  = date('Y-m-d');
            $bandeja->nick_creacion = $this->user->nick;

            if ($bandeja->save()) {
                $paso = true;
                //Busco el Motivo
                $descripcion = 'Nota de Debito';
                $referencia  = ' NDC';
                if (isset($ncd->motivos->motivo->razon)) {
                    $descripcion = $ncd->motivos->motivo->razon;
                    $rf          = explode(" ", $descripcion);
                    $referencia  = $rf[0];
                }
                //Si se guarda almaceno el detalle
                foreach ($ncd->infoNotaDebito->impuestos->impuesto as $key => $imp) {
                    $lin               = new lineasbandejasri();
                    $lin->idbandejasri = $bandeja->idbandejasri;
                    $lin->codprincipal = $referencia;
                    //Busco si ya tiene homologacion si existe el idproveedor
                    if ($bandeja->idproveedor) {
                        $l = $this->lineasbandejasri->getHomologacionArt($this->empresa->idempresa, $bandeja->idproveedor, $lin->codprincipal);
                        if ($l) {
                            $lin->idarticulo = $l->idarticulo;
                        }
                    }
                    $lin->descripcion = $descripcion;
                    $lin->cantidad    = floatval(1);
                    $lin->pvptotal    = floatval($imp->baseImponible);
                    $lin->pvpunitario = round($lin->pvptotal / $lin->cantidad, 6);
                    //IVA
                    if ($imp->codigo == 2) {
                        if ($imp->codigoPorcentaje == 0) {
                            // IVA 0%
                            $codimp = $this->impuestos->get_by_codigo('IVA0');
                            if ($codimp) {
                                $lin->idimpuesto = $codimp->idimpuesto;
                            }
                            $bandeja->base_0 += $lin->pvptotal;
                        } else if ($imp->codigoPorcentaje == 2) {
                            // IVA 12%
                            $codimp = $this->impuestos->get_by_codigo('IVA12');
                            if ($codimp) {
                                $lin->idimpuesto = $codimp->idimpuesto;
                            }
                            $bandeja->base_gra += $lin->pvptotal;
                        } else if ($imp->codigoPorcentaje == 3) {
                            // IVA 14%
                            $codimp = $this->impuestos->get_by_codigo('IVA14');
                            if ($codimp) {
                                $lin->idimpuesto = $codimp->idimpuesto;
                            }
                            $bandeja->base_gra += $lin->pvptotal;
                        } else if ($imp->codigoPorcentaje == 6) {
                            // IVA No Objeto
                            $codimp = $this->impuestos->get_by_codigo('IVANO');
                            if ($codimp) {
                                $lin->idimpuesto = $codimp->idimpuesto;
                            }
                            $bandeja->base_noi += $lin->pvptotal;
                        } else if ($imp->codigoPorcentaje == 7) {
                            // IVA Excento IVA
                            $codimp = $this->impuestos->get_by_codigo('IVAEX');
                            if ($codimp) {
                                $lin->idimpuesto = $codimp->idimpuesto;
                            }
                            $bandeja->base_exc += $lin->pvptotal;
                        } else if ($imp->codigoPorcentaje == 8) {
                            // IVA Diferenciado
                            $codimp = $this->impuestos->get_by_codigo('IVA8');
                            if ($codimp) {
                                $lin->idimpuesto = $codimp->idimpuesto;
                            }
                            $bandeja->base_gra += $lin->pvptotal;
                        }
                        $lin->valoriva = floatval($imp->valor);
                    }
                    //ICE
                    if ($imp->codigo == 3) {
                        $lin->valorice = floatval($imp->valor);
                    }
                    //IRBPNR
                    if ($imp->codigo == 5) {
                        $lin->valorirbp = floatval($imp->valor);
                    }

                    $bandeja->totalice += $lin->valorice;
                    $bandeja->totaliva += $lin->valoriva;
                    $bandeja->totalirbp += $lin->valorirbp;

                    $lin->fec_creacion  = date('Y-m-d');
                    $lin->nick_creacion = $this->user->nick;

                    if (!$lin->save()) {
                        $paso = false;
                    }
                }

                if (!$bandeja->save()) {
                    $paso = false;
                }

                if ($paso) {
                    $this->new_message("Nota de Débito: " . $numdoc . " con Nro. Aut: " . $ncd->infoTributaria->claveAcceso . " procesada correctamente.");
                } else {
                    $this->new_error_msg("Error al capturar el detalle de la Nota de Débito: " . $numdoc . " con Nro. Aut: " . $ncd->infoTributaria->claveAcceso);
                    $bandeja->delete();
                    return false;
                }
            } else {
                $this->new_error_msg("Error al capturar la Nota de Débito: " . $numdoc . " con Nro. Aut: " . $ncd->infoTributaria->claveAcceso);
                return false;
            }
        } else {
            $this->new_advice("La Nota de Débito: " . $numdoc . " con Nro. Aut: " . $ncd->infoTributaria->claveAcceso . " ya se encuentra registrada");
            return false;
        }

        return true;
    }

    private function procesar_ret_cliente($ret, $fec_aut)
    {
        $numdoc = $ret->infoTributaria->estab . "-" . $ret->infoTributaria->ptoEmi . "-" . $ret->infoTributaria->secuencial;
        //Valido que no exista ya creada la Retencion en el sistema
        if (!$this->retencionescli->get_by_nro_aut($this->empresa->idempresa, $ret->infoTributaria->claveAcceso)) {
            $bandeja            = new cab_bandejasri();
            $bandeja->idempresa = $this->empresa->idempresa;
            //Busco el Cliente
            $cliente = $this->clientes->get_by_identificacion($this->empresa->idempresa, $ret->infoTributaria->ruc);
            if ($cliente) {
                $bandeja->idcliente = $cliente->idcliente;
            }
            $bandeja->coddocumento   = $ret->infoTributaria->codDoc;
            $bandeja->tipoid         = 'R';
            $bandeja->identificacion = $ret->infoTributaria->ruc;
            $bandeja->razonsocial    = $ret->infoTributaria->razonSocial;
            if (isset($ret->infoTributaria->nombreComercial)) {
                $bandeja->nombrecomercial = $ret->infoTributaria->nombreComercial;
            } else {
                $bandeja->nombrecomercial = $bandeja->razonsocial;
            }
            $bandeja->numero_documento = $numdoc;
            $bandeja->nro_autorizacion = $ret->infoTributaria->claveAcceso;
            $bandeja->fec_autorizacion = substr($fec_aut, 0, 10);
            $bandeja->hor_autorizacion = substr($fec_aut, 11, 8);
            $fecha                     = explode("/", $ret->infoCompRetencion->fechaEmision);
            $bandeja->fec_emision      = $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];
            $bandeja->hora_emision     = $bandeja->hor_autorizacion;
            $bandeja->fec_caducidad    = $bandeja->fec_emision;
            $bandeja->fec_registro     = date('Y-m-d');
            $bandeja->fec_creacion     = date('Y-m-d');
            $bandeja->nick_creacion    = $this->user->nick;

            if ($bandeja->save()) {
                $paso = true;
                //Si se guarda recorro el detalle
                if ($ret['version'] == '1.0.0') {
                    //Version Anterior
                    foreach ($ret->impuestos->impuesto as $key => $impuesto) {
                        $numdocfac = null;
                        if ($impuesto->codDocSustento != '22') {
                            $numdocfac = substr($impuesto->numDocSustento, 0, 3) . "-" . substr($impuesto->numDocSustento, 3, 3) . "-" . substr($impuesto->numDocSustento, 6);
                        }
                        $factura = false;
                        if ($impuesto->codDocSustento == '01') {
                            $factura = $this->facturascli->buscar_factura($this->empresa->idempresa, $numdocfac);
                        }
                        $lin               = new lineasretbandejasri();
                        $lin->idempresa    = $this->empresa->idempresa;
                        $lin->idbandejasri = $bandeja->idbandejasri;
                        if ($impuesto->codigo == 1) {
                            $lin->especie = 'renta';
                        } else {
                            $lin->especie = 'iva';
                        }
                        $lin->codigo = $impuesto->codigoRetencion;
                        //Busco el idtiporetencion
                        $tiporet = $this->tiposretenciones->get_retencion_venta($this->empresa->idempresa, $lin->especie, $lin->codigo);
                        if ($tiporet) {
                            $lin->idtiporetencion = $tiporet->idtiporetencion;
                        }
                        $lin->baseimponible    = floatval($impuesto->baseImponible);
                        $lin->porcentaje       = floatval($impuesto->porcentajeRetener);
                        $lin->total            = floatval($impuesto->valorRetenido);
                        $lin->coddocumento_mod = $impuesto->codDocSustento;
                        if ($factura) {
                            $lin->iddocumento_mod = $factura->idfacturacli;
                        }
                        if ($impuesto->codDocSustento != '22') {
                            $lin->numero_documento_mod = $numdocfac;
                            $fechafact                 = explode("/", $impuesto->fechaEmisionDocSustento);
                            $lin->fec_emision_mod      = $fechafact[2] . "-" . $fechafact[1] . "-" . $fechafact[0];
                        }
                        $lin->fec_creacion  = date('Y-m-d');
                        $lin->nick_creacion = $this->user->nick;
                        if (!$lin->save()) {
                            $this->new_advice("Error al capturar el detalle de la Retención: " . $numdoc . " con Nro. Aut: " . $ret->infoTributaria->claveAcceso . ", Codigo: " . $lin->codigo);
                            $bandeja->delete();
                            $paso = false;
                        } else {
                            $bandeja->total += floatval($lin->total);
                        }
                    }
                    if ($paso) {
                        if (!$bandeja->save()) {
                            $paso = false;
                        }
                    }
                } else {
                    //Version ATS
                    foreach ($ret->docsSustento->docSustento as $key => $docSustento) {
                        foreach ($docSustento->retenciones->retencion as $key => $ret2) {
                            $numdocfac = null;
                            if ($docSustento->codDocSustento != '22') {
                                $numdocfac = substr($docSustento->numDocSustento, 0, 3) . "-" . substr($docSustento->numDocSustento, 3, 3) . "-" . substr($docSustento->numDocSustento, 6);
                            }
                            $factura = false;
                            if ($docSustento->codDocSustento == '01') {
                                $factura = $this->facturascli->buscar_factura($this->empresa->idempresa, $numdocfac);
                            }
                            $lin               = new lineasretbandejasri();
                            $lin->idempresa    = $this->empresa->idempresa;
                            $lin->idbandejasri = $bandeja->idbandejasri;
                            if ($ret2->codigo == 1) {
                                $lin->especie = 'renta';
                            } else {
                                $lin->especie = 'iva';
                            }
                            $lin->codigo = $ret2->codigoRetencion;
                            //Busco el idtiporetencion
                            $tiporet = $this->tiposretenciones->get_retencion_venta($this->empresa->idempresa, $lin->especie, $lin->codigo);
                            if ($tiporet) {
                                $lin->idtiporetencion = $tiporet->idtiporetencion;
                            }
                            $lin->baseimponible    = floatval($ret2->baseImponible);
                            $lin->porcentaje       = floatval($ret2->porcentajeRetener);
                            $lin->total            = floatval($ret2->valorRetenido);
                            $lin->coddocumento_mod = $docSustento->codDocSustento;
                            if ($docSustento->codDocSustento != '22') {
                                if ($factura) {
                                    $lin->iddocumento_mod = $factura->idfacturacli;
                                }
                                $lin->numero_documento_mod = $numdocfac;
                                $fechafact                 = explode("/", $docSustento->fechaEmisionDocSustento);
                                $lin->fec_emision_mod      = $fechafact[2] . "-" . $fechafact[1] . "-" . $fechafact[0];
                            }
                            $lin->fec_creacion  = date('Y-m-d');
                            $lin->nick_creacion = $this->user->nick;
                            if (!$lin->save()) {
                                $this->new_advice("Error al capturar el detalle de la Retención: " . $numdoc . " con Nro. Aut: " . $ret->infoTributaria->claveAcceso . ", Codigo: " . $lin->codigo);
                                $bandeja->delete();
                                return;
                            } else {
                                $bandeja->total += floatval($lin->total);
                            }
                        }
                    }

                    if ($paso) {
                        if (!$bandeja->save()) {
                            $paso = false;
                        }
                    }
                }

                if ($paso) {
                    $this->new_message("Retención: " . $numdoc . " con Nro. Aut: " . $ret->infoTributaria->claveAcceso . " procesada correctamente.");
                } else {
                    $this->new_error_msg("Error al capturar el detalle de la Retención: " . $numdoc . " con Nro. Aut: " . $ret->infoTributaria->claveAcceso);
                    $bandeja->delete();
                    return false;
                }
            } else {
                $this->new_error_msg("Error al capturar la Retención: " . $numdoc . " con Nro. Aut: " . $ret->infoTributaria->claveAcceso);
                return false;
            }
        } else {
            $this->new_advice("La Retención: " . $numdoc . " con Nro. Aut: " . $ret->infoTributaria->claveAcceso . " ya se encuentra registrada");
            return false;
        }
        return true;
    }

    private function noprocesardoc()
    {
        $bandeja = $this->bandeja->get($_GET['noprocesar']);
        if ($bandeja) {
            if ($bandeja->estado != 1) {
                $bandeja->estado = 1;
                if ($bandeja->save()) {
                    $this->new_message("Estado Actualizado correctamente.");
                } else {
                    $this->new_error_msg("Error al cambiar el estado del documento.");
                }
            } else {
                $this->new_advice("El documento ya se encuentra dentro de los No Procesados");
            }
        } else {
            $this->new_advice('Documento no Encontrado');
        }
    }

    private function pendientedoc()
    {
        $bandeja = $this->bandeja->get($_GET['pendiente']);
        if ($bandeja) {
            if ($bandeja->estado != 0) {
                $bandeja->estado = 0;
                if ($bandeja->save()) {
                    $this->new_message("Estado Actualizado correctamente.");
                } else {
                    $this->new_error_msg("Error al cambiar el estado del documento.");
                }
            } else {
                $this->new_advice("El documento ya se encuentra dentro de los Pendientes por Procesar");
            }
        } else {
            $this->new_advice('Documento no Encontrado');
        }
    }

    private function procesar_documento()
    {
        $bandeja = $this->bandeja->get($_POST['procesar']);
        if ($bandeja) {
            if ($bandeja->estado == 1) {
                $this->new_advice("El documento ya se encuentra procesado.");
                return;
            }
            if ($bandeja->idempresa == $this->empresa->idempresa) {
                $prov = $this->proveedores->get_by_identificacion($bandeja->idempresa, $bandeja->identificacion);
                if (!$prov) {
                    //si el proveedor no existe lo creo
                    $prov                  = new proveedores();
                    $prov->idempresa       = $bandeja->idempresa;
                    $prov->identificacion  = $bandeja->identificacion;
                    $prov->tipoid          = $bandeja->tipoid;
                    $prov->razonsocial     = $bandeja->razonsocial;
                    $prov->nombrecomercial = $bandeja->nombrecomercial;
                    $prov->fec_creacion    = date('Y-m-d');
                    $prov->nick_creacion   = $this->user->nick;
                } else {
                    $prov->fec_modificacion  = date('Y-m-d');
                    $prov->nick_modificacion = $this->user->nick;
                }
                $prov->telefono  = $_POST['telefono'];
                $prov->email     = $_POST['email'];
                $prov->direccion = $_POST['direccion'];
                if (!$prov->save()) {
                    $this->new_error_msg("Error al guardar los datos del proveedor.!");
                    return;
                } else {
                    //actualizo la bandeja electronica con las cedulas
                    if (!$this->bandeja->actualizarProveedor($bandeja->idempresa, $prov->identificacion, $prov->idproveedor)) {
                        $this->new_error_msg("Error al actualizar el Identificador en el proveedor.");
                        return;
                    }
                }

                $factura                      = new facturasprov();
                $factura->idempresa           = $this->empresa->idempresa;
                $factura->idestablecimiento   = $_POST['idestablecimiento'];
                $factura->idproveedor         = $prov->idproveedor;
                $factura->coddocumento        = $bandeja->coddocumento;
                $factura->iddocumento         = $bandeja->iddocumento;
                $factura->idsustento          = $_POST['idsustento'];
                $factura->tipoemision         = 'E';
                $factura->tipoid              = $bandeja->tipoid;
                $factura->identificacion      = $bandeja->identificacion;
                $factura->razonsocial         = $bandeja->razonsocial;
                $factura->email               = $_POST['email'];
                $factura->direccion           = $_POST['direccion'];
                $factura->regimen_empresa     = $this->empresa->regimen;
                $factura->obligado_empresa    = $this->empresa->obligado;
                $factura->agretencion_empresa = $this->empresa->agretencion;
                $factura->numero_documento    = $bandeja->numero_documento;
                $factura->nro_autorizacion    = $bandeja->nro_autorizacion;
                $factura->diascredito         = $_POST['diascredito'];
                $factura->fec_emision         = $bandeja->fec_emision;
                $factura->hora_emision        = $bandeja->hora_emision;
                $factura->fec_caducidad       = $bandeja->fec_caducidad;
                $factura->fec_registro        = $bandeja->fec_emision;
                $factura->base_noi            = $bandeja->base_noi;
                $factura->base_0              = $bandeja->base_0;
                $factura->base_gra            = $bandeja->base_gra;
                $factura->base_exc            = $bandeja->base_exc;
                $factura->totaldescuento      = $bandeja->totaldescuento;
                $factura->totalice            = $bandeja->totalice;
                $factura->totaliva            = $bandeja->totaliva;
                $factura->totalirbp           = $bandeja->totalirbp;
                $factura->total               = $bandeja->total;
                if ($_POST['observaciones'] != '') {
                    $factura->observaciones = $_POST['observaciones'];
                }
                //si el documento tiene un documento modificado
                if ($bandeja->coddocumento == '04' || $bandeja->coddocumento == '05') {
                    //Valido si el documento esta creado en las compras
                    $doc = $this->facturasprov->getDocumentoProveedor($this->empresa->idempresa, $bandeja->identificacion, $bandeja->coddocumento_mod, $bandeja->numero_documento_mod);
                    if ($doc) {
                        $factura->idfactura_mod        = $doc->idfacturaprov;
                        $factura->nro_autorizacion_mod = $doc->nro_autorizacion;
                    }

                    $factura->iddocumento_mod      = $bandeja->iddocumento_mod;
                    $factura->coddocumento_mod     = $bandeja->coddocumento_mod;
                    $factura->numero_documento_mod = $bandeja->numero_documento_mod;
                    $factura->fecdoc_modificado    = $bandeja->fecdoc_modificado;
                }

                $factura->fec_creacion  = date('Y-m-d');
                $factura->nick_creacion = $this->user->nick;

                if ($factura->save()) {
                    $paso = true;
                    foreach ($bandeja->getlineas() as $key => $l) {
                        if ($l->cantidad > 0) {
                            $linea                    = new lineasfacturasprov();
                            $linea->idfacturaprov     = $factura->idfacturaprov;
                            $linea->idarticulo        = $l->idarticulo;
                            $art                      = $this->articulos->get($linea->idarticulo);
                            $linea->idimpuesto        = $l->idimpuesto;
                            $linea->codprincipal      = $art->codprincipal;
                            $linea->descripcion       = $art->nombre;
                            $linea->cantidad          = $l->cantidad;
                            $linea->pvpunitario       = $l->pvpunitario;
                            $linea->dto               = $l->dto;
                            $linea->pvptotal          = $l->pvptotal;
                            $linea->pvpsindto         = $l->pvpsindto;
                            $linea->valorice          = $l->valorice;
                            $linea->valoriva          = $l->valoriva;
                            $linea->valorirbp         = $l->valorirbp;
                            $linea->idretencion_renta = $l->idretencion_renta;
                            $linea->idretencion_iva   = $l->idretencion_iva;
                            $linea->fec_creacion      = $l->fec_creacion;
                            $linea->nick_creacion     = $l->nick_creacion;
                            if (!$linea->save()) {
                                $this->new_error_msg("Error al guardar el detalle del la factura. Item: " . $l->descripcion);
                                $paso = false;
                                break;
                            }
                        }
                    }

                    if ($paso) {
                        $bandeja->estado    = 2;
                        $bandeja->idfactura = $factura->idfacturaprov;
                        if ($bandeja->save()) {
                            $this->new_message("Documento Procesado correctamente. <a href='" . $factura->url() . "' target='_blank'>" . $factura->numero_documento . "</a>");
                        }
                    } else {
                        if ($factura->delete()) {
                            $this->new_advice("Factura No procesada.");
                        } else {
                            $this->new_advice("Error al Eliminar la factura");
                        }
                    }
                } else {
                    $this->new_error_msg("Error al generar la factura");
                }
            } else {
                $this->new_advice("El documento no esta disponible para su empresa.");
            }
        } else {
            $this->new_advice('Documento no Encontrado');
        }
    }

    private function procesar_retencion()
    {
        $bandeja = $this->bandeja->get($_POST['procesar_ret']);
        if ($bandeja) {
            if ($bandeja->estado == 1) {
                $this->new_advice("El documento ya se encuentra procesado.");
                return;
            }
            if ($bandeja->idempresa == $this->empresa->idempresa) {
                $cli = $this->clientes->get_by_identificacion($bandeja->idempresa, $bandeja->identificacion);
                if (!$cli) {
                    //si el cliente no existe lo creo
                    $cli                  = new clientes();
                    $cli->idempresa       = $bandeja->idempresa;
                    $cli->identificacion  = $bandeja->identificacion;
                    $cli->tipoid          = $bandeja->tipoid;
                    $cli->razonsocial     = $bandeja->razonsocial;
                    $cli->nombrecomercial = $bandeja->nombrecomercial;
                    $cli->fec_creacion    = date('Y-m-d');
                    $cli->nick_creacion   = $this->user->nick;
                } else {
                    $cli->fec_modificacion  = date('Y-m-d');
                    $cli->nick_modificacion = $this->user->nick;
                }
                $cli->telefono  = $_POST['telefono'];
                $cli->email     = $_POST['email'];
                $cli->direccion = $_POST['direccion'];
                if (!$cli->save()) {
                    $this->new_error_msg("Error al guardar los datos del cliente.!");
                    return;
                } else {
                    //actualizo la bandeja electronica con las cedulas
                    if (!$this->bandeja->actualizarCliente($bandeja->idempresa, $cli->identificacion, $cli->idcliente)) {
                        $this->new_error_msg("Error al actualizar el Identificador en el cliente.");
                        return;
                    }
                }

                $retencion                   = new retencionescli();
                $retencion->idempresa        = $this->empresa->idempresa;
                $retencion->idcliente        = $cli->idcliente;
                $retencion->fec_emision      = $bandeja->fec_emision;
                $retencion->fec_registro     = $bandeja->fec_registro;
                $retencion->numero_documento = $bandeja->numero_documento;
                $retencion->nro_autorizacion = $bandeja->nro_autorizacion;
                $retencion->fec_autorizacion = $bandeja->fec_autorizacion;
                $retencion->hor_autorizacion = $bandeja->hor_autorizacion;
                $retencion->total            = $bandeja->total;
                $retencion->fec_creacion     = date('Y-m-d');
                $retencion->nick_creacion    = $this->user->nick;

                if ($retencion->save()) {
                    $paso = true;
                    foreach ($bandeja->getlineasret() as $key => $l) {
                        $linea                  = new lineasretencionescli();
                        $linea->idempresa       = $this->empresa->idempresa;
                        $linea->idretencion     = $retencion->idretencion;
                        $linea->especie         = $l->especie;
                        $linea->codigo          = $l->codigo;
                        $linea->idtiporetencion = $l->idtiporetencion;
                        $linea->baseimponible   = floatval($l->baseimponible);
                        $linea->porcentaje      = floatval($l->porcentaje);
                        $linea->total           = floatval($l->total);
                        if ($l->coddocumento_mod == '01') {
                            $factura = $this->facturascli->buscar_factura($this->empresa->idempresa, $l->numero_documento_mod);
                            if ($factura) {
                                $linea->iddocumento_mod = $factura->idfacturacli;
                            }
                        }
                        $linea->numero_documento_mod = $l->numero_documento_mod;
                        $linea->fec_emision_mod      = $l->fec_emision_mod;
                        $linea->coddocumento_mod     = $l->coddocumento_mod;
                        $linea->fec_creacion         = date('Y-m-d');
                        $linea->nick_creacion        = $this->user->nick;
                        if (!$linea->save()) {
                            $this->new_error_msg("Error al guardar el detalle del la Retención. Item: " . $l->codigo);
                            $paso = true;
                            break;
                        }
                    }
                    if ($paso) {
                        $bandeja->estado      = 2;
                        $bandeja->idretencion = $retencion->idretencion;
                        if ($bandeja->save()) {
                            $retencion->save();
                            $this->new_message("Documento Procesado correctamente. <a href='" . $retencion->url() . "' target='_blank'>" . $retencion->numero_documento . "</a>");
                        }
                    } else {
                        if ($retencion->delete()) {
                            $this->new_advice("Retención No procesada.");
                        } else {
                            $this->new_advice("Error al Eliminar la retencion");
                        }
                    }
                } else {
                    $this->new_error_msg("Error al guardar la Retencion");
                }
            } else {
                $this->new_advice("El documento no esta disponible para su empresa.");
            }
        } else {
            $this->new_advice('Documento no Encontrado');
        }
    }
}
