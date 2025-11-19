<?php
/**
 * Controlador de Bandeja SRI -> Procesar Bandeja SRI.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class procesar_bandejasri extends controller
{
    //variables
    public $factura;
    public $retencion;
    //modelos
    public $bandeja;
    public $articulos;
    public $lineasbandejasri;
    public $lineasretbandejasri;
    public $tiposretenciones;
    public $impuestos;
    public $sustentos;
    public $establecimiento;
    public $proveedores;
    public $clientes;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Procesar Bandeja SRI', 'Bandeja SRI', false, false);
    }

    protected function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->articulos           = new articulos();
        $this->bandeja             = new cab_bandejasri();
        $this->lineasbandejasri    = new lineasbandejasri();
        $this->lineasretbandejasri = new lineasretbandejasri();
        $this->tiposretenciones    = new tiposretenciones();
        $this->impuestos           = new impuestos();
        $this->sustentos           = new sustentos();
        $this->establecimiento     = new establecimiento();
        $this->proveedores         = new proveedores();
        $this->clientes            = new clientes();

        $this->factura   = false;
        $this->retencion = false;
        if (isset($_GET['id'])) {
            $bandeja = $this->bandeja->get($_GET['id']);
            if ($bandeja) {
                if ($bandeja->idempresa == $this->empresa->idempresa) {
                    if ($bandeja->estado == 0) {
                        if ($bandeja->coddocumento == '07') {
                            $this->retencion = $bandeja;
                        } else if ($bandeja->coddocumento == '01' or $bandeja->coddocumento == '04' or $bandeja->coddocumento == '05') {
                            $this->factura = $bandeja;
                        } else {
                            $this->new_advice("Tipo de Documento no Válido.");
                        }
                    } else {
                        $this->new_advice("El documento no esta habilitado para ser Procesado.");
                    }
                } else {
                    $this->new_advice("El documento no esta disponible para su empresa.");
                }
            } else {
                $this->new_advice("No se encuentra el Documento Seleccionado.");
            }
        }

        if (isset($_GET['actdetdoc'])) {
            $this->buscar_detalle_doc();
        } else if (isset($_GET['buscar_articulo'])) {
            $this->buscar_articulos();
        } else if (isset($_POST['hom_linea'])) {
            $this->realizar_homologacion();
        } else if (isset($_POST['buscar_linea'])) {
            $this->buscar_linea();
        } else if (isset($_POST['codart'])) {
            $this->buscar_articulo();
        } else if (isset($_POST['procesar_doc'])) {
            $this->procesar_documento();
        } else if (isset($_GET['actdetret'])) {
            $this->buscar_detalle_ret();
        } else if (isset($_POST['buscar_linea_ret'])) {
            $this->buscar_linea_ret();
        } else if (isset($_GET['buscar_retencion'])) {
            $this->buscar_retenciones();
        } else if (isset($_POST['codret'])) {
            $this->buscar_retencion();
        } else if (isset($_POST['hom_linea_ret'])) {
            $this->realizar_homologacion_ret();
        } else if (isset($_POST['editar_linea'])) {
            $this->editar_cantidad();
        } else if (isset($_POST['val_hom_masiva'])) {
            $this->validar_homologacion_masiva();
        } else if (isset($_POST['homologar_masivo'])) {
            $this->homologar_masivo();
        }
    }

    private function editar_cantidad()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No existe el Documento.');
        if ($this->factura) {
            if ($this->factura->estado == 1) {
                $result = array('error' => 'T', 'msj' => 'El Documento que esta intentando modificar se encuentra dentro de los No Procesados.');
            } else if ($this->factura->estado == 2) {
                $result = array('error' => 'T', 'msj' => 'El Documento que esta intentando modificar ya fue Procesado.');
            } else {
                $linea = $this->lineasbandejasri->get($_POST['editar_linea']);
                if ($linea) {
                    $new_cantidad = floatval($_POST['cant_new']);
                    if ($new_cantidad > 0) {
                        $linea->cantidad = $new_cantidad;
                        $linea->pvpunitario = round($linea->pvptotal / $linea->cantidad, 6);
                        if ($linea->save()) {
                            $result = array('error' => 'F', 'msj' => '');
                        } else {
                            $result = array('error' => 'T', 'msj' => 'Error al actualizar la cantidad del detalle del documento.');
                        }
                    } else if ($new_cantidad == 0) {
                        if ($linea->pvptotal > 0) {
                            $result = array('error' => 'T', 'msj' => 'Cantidad no Válida, el Artículo tiene precio por lo cual no puede poner la cantidad en 0');    
                        } else {
                            $linea->cantidad = $new_cantidad;
                            $linea->pvpunitario = round(0, 6);
                            if ($linea->save()) {
                                $result = array('error' => 'F', 'msj' => '');
                            } else {
                                $result = array('error' => 'T', 'msj' => 'Error al actualizar la cantidad del detalle del documento.');
                            }
                        }
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Cantidad no Válida.');
                    }
                } else {
                    $result = array('error' => 'T', 'msj' => 'Item no encontrado para realizar la edición.');
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_detalle_doc()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No existe el Documento.');
        if ($this->factura) {
            $lineas = $this->lineasbandejasri->all_by_idbandejasri($this->factura->idbandejasri);
            $result = array('error' => 'T', 'msj' => 'Sin Detalle.');
            if ($lineas) {
                $result = array('error' => 'F', 'msj' => '', 'factura' => $this->factura, 'lineas' => $lineas);
            }
        }

        echo json_encode($result);
        exit;
    }

    private function buscar_detalle_ret()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No existe el Documento.');
        if ($this->retencion) {
            $lineas = $this->lineasretbandejasri->all_by_idbandejasri($this->retencion->idbandejasri);
            $result = array('error' => 'T', 'msj' => 'Sin Detalle.');
            if ($lineas) {
                $result = array('error' => 'F', 'msj' => '', 'retencion' => $this->retencion, 'lineas' => $lineas);
            }
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

    private function buscar_retenciones()
    {
        $this->template = false;
        $retenciones    = $this->tiposretenciones->all_retenciones_venta($this->empresa->idempresa, $_GET['buscar_retencion']);
        echo json_encode($retenciones);
        exit;
    }

    private function buscar_articulo()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'El código del producto ya existe, puede realizar la homologación.');
        $art            = $this->articulos->get_by_codprincipal($this->empresa->idempresa, $_POST['codart']);
        if (!$art) {
            $result = array('error' => 'F', 'msj' => '');
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_retencion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'El código de la retención ya existe, puede realizar la homologación.');
        $ret            = $this->tiposretenciones->get_retencion_venta($this->empresa->idempresa, $_POST['especie'], $_POST['codret']);
        if (!$ret) {
            $result = array('error' => 'F', 'msj' => '');
        }
        echo json_encode($result);
        exit;
    }

    private function realizar_homologacion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Item no encontrado para realizar la homologación.');
        $linea          = $this->lineasbandejasri->get($_POST['hom_linea']);
        if ($this->factura) {
            if ($linea) {
                $idarticulo = false;
                if (isset($_POST['crearart'])) {
                    $art = $this->articulos->get_by_codprincipal($this->empresa->idempresa, $_POST['ref_proveedor']);
                    if ($art) {
                        $linea->idarticulo = $art->idarticulo;
                        if (isset($_POST['idretencion_renta'])) {
                            $linea->idretencion_renta = $_POST['idretencion_renta'];
                        }
                        if (isset($_POST['idretencion_iva'])) {
                            $linea->idretencion_iva = $_POST['idretencion_iva'];
                        }
                        if (!$linea->save()) {
                            $result     = array('error' => 'T', 'msj' => 'Error al realizar la homologación del Artículo.');
                            $idarticulo = false;
                        } else {
                            $idarticulo = $linea->idarticulo;
                        }
                    } else {
                        $art               = new articulos();
                        $art->idempresa    = $this->empresa->idempresa;
                        $art->codprincipal = $_POST['ref_proveedor'];
                        if ($_POST['codbarras'] != '') {
                            $art->codbarras = $_POST['codbarras'];
                        }
                        $art->nombre     = $_POST['desc_proveedor'];
                        $art->idimpuesto = $_POST['idimpuesto'];
                        //Busco el impuesto generado
                        $impuesto = $this->impuestos->get($art->idimpuesto);
                        if ($impuesto) {
                            //calculo el precio
                            $art->precio = floatval($_POST['precio']) / (1 + ($impuesto->porcentaje / 100));
                        }
                        if ($_POST['idmarca'] != '') {
                            $art->idmarca = $_POST['idmarca'];
                        }
                        if ($_POST['idgrupo'] != '') {
                            $art->idgrupo = $_POST['idgrupo'];
                        }
                        $art->tipo          = $_POST['tipo'];
                        $art->fec_creacion  = date('Y-m-d');
                        $art->nick_creacion = $this->user->nick;

                        if ($art->save()) {
                            $linea->idarticulo = $art->idarticulo;
                            if (isset($_POST['idretencion_renta'])) {
                                $linea->idretencion_renta = $_POST['idretencion_renta'];
                            }
                            if (isset($_POST['idretencion_iva'])) {
                                $linea->idretencion_iva = $_POST['idretencion_iva'];
                            }
                            if (!$linea->save()) {
                                $result     = array('error' => 'T', 'msj' => 'Error al realizar la homologación del Artículo.');
                                $idarticulo = false;
                            } else {
                                $idarticulo = $linea->idarticulo;
                            }
                        } else {
                            $result = array('error' => 'T', 'msj' => 'Error al crear el Artículo.');
                        }
                    }
                } else {
                    $linea->idarticulo = $_POST['idarticulo'];
                    if (isset($_POST['idretencion_renta'])) {
                        $linea->idretencion_renta = $_POST['idretencion_renta'];
                    }
                    if (isset($_POST['idretencion_iva'])) {
                        $linea->idretencion_iva = $_POST['idretencion_iva'];
                    }
                    if (!$linea->save()) {
                        $result     = array('error' => 'T', 'msj' => 'Error al realizar la homologación del Artículo.');
                        $idarticulo = false;
                    } else {
                        $idarticulo = $linea->idarticulo;
                    }
                }

                if ($idarticulo) {
                    if ($this->lineasbandejasri->homologar_items($this->empresa->idempresa, $this->factura->identificacion, $linea)) {
                        $result = array('error' => 'F', 'msj' => '');
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al realizar la homologación del Artículo, en los documentos del Proveedor.');
                    }
                }
            }
        } else {
            $result = array('error' => 'T', 'msj' => 'Documento no encontrado.');
        }
        echo json_encode($result);
        exit;
    }

    private function realizar_homologacion_ret()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Item no encontrado para realizar la homologación.');
        $linea          = $this->lineasretbandejasri->get($_POST['hom_linea_ret']);
        if ($this->retencion) {
            if ($linea) {
                $idtiporetencion = false;
                if (isset($_POST['crearret'])) {
                    $ret = $this->tiposretenciones->get_retencion_venta($this->empresa->idempresa, $linea->especie, $_POST['codigobase']);
                    if ($ret) {
                        $linea->idtiporetencion = $ret->idtiporetencion;
                        if (!$linea->save()) {
                            $result          = array('error' => 'T', 'msj' => 'Error al realizar la homologación de la Retención.');
                            $idtiporetencion = false;
                        } else {
                            $idtiporetencion = $linea->idtiporetencion;
                        }
                    } else {

                        $tiporetencion                = new tiposretenciones();
                        $tiporetencion->idempresa     = $this->empresa->idempresa;
                        $tiporetencion->codigobase    = $_POST['codigobase'];
                        $tiporetencion->codigo        = $_POST['codigo'];
                        $tiporetencion->especie       = $_POST['especie_ret'];
                        $tiporetencion->nombre        = $_POST['nombre'];
                        $tiporetencion->porcentaje    = floatval($_POST['porcentaje_ret']);
                        $tiporetencion->esventa       = true;
                        $tiporetencion->escompra      = false;
                        $tiporetencion->fec_creacion  = date('Y-m-d');
                        $tiporetencion->nick_creacion = $this->user->nick;

                        if ($tiporetencion->save()) {
                            $linea->idtiporetencion = $tiporetencion->idtiporetencion;
                            if (!$linea->save()) {
                                $result          = array('error' => 'T', 'msj' => 'Error al realizar la homologación de la Retención.');
                                $idtiporetencion = false;
                            } else {
                                $idtiporetencion = $linea->idtiporetencion;
                            }
                        } else {
                            $result = array('error' => 'T', 'msj' => 'Error al crear la Retención.');
                        }
                    }
                } else {
                    $linea->idtiporetencion = $_POST['idtiporetencion'];
                    if (!$linea->save()) {
                        $result          = array('error' => 'T', 'msj' => 'Error al realizar la homologación de la Retención.');
                        $idtiporetencion = false;
                    } else {
                        $idtiporetencion = $linea->idtiporetencion;
                    }
                }

                if ($idtiporetencion) {
                    if ($this->lineasretbandejasri->homologar_items($this->empresa->idempresa, $this->retencion->identificacion, $linea)) {
                        $result = array('error' => 'F', 'msj' => '');
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al realizar la homologación de la Retención, en los documentos del Cliente.');
                    }
                }
            }
        } else {
            $result = array('error' => 'T', 'msj' => 'Documento no encontrado.');
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_linea()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Item no encontrado.');
        $linea          = $this->lineasbandejasri->get($_POST['buscar_linea']);
        if ($linea) {
            $result = array('error' => 'F', 'msj' => '', 'linea' => $linea);
            if ($linea->idarticulo) {
                $art = $this->articulos->get($linea->idarticulo);
                if ($art) {
                    $result = array('error' => 'F', 'msj' => '', 'linea' => $linea, 'art' => $art);
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_linea_ret()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Item no encontrado.');
        $linea          = $this->lineasretbandejasri->get($_POST['buscar_linea_ret']);
        if ($linea) {
            $result = array('error' => 'F', 'msj' => '', 'linea' => $linea);
            if ($linea->idtiporetencion) {
                $ret = $this->tiposretenciones->get($linea->idtiporetencion);
                if ($ret) {
                    $result = array('error' => 'F', 'msj' => '', 'linea' => $linea, 'ret' => $ret);
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    private function procesar_documento()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Documento No encontrado');
        if ($this->factura) {
            $result = array('error' => 'F', 'msj' => '');
            $agret = false;
            if ($this->factura->coddocumento != '04') {
                if ($this->empresa->agretencion) {
                    $agret = true;
                }
            }
            $lineas = $this->lineasbandejasri->all_by_idbandejasri_sh($this->factura->idbandejasri, $agret);
            if ($lineas) {
                $result = array('error' => 'T', 'msj' => 'No tiene todo el detalle del documento homologado, realice las homologaciones y vuelva a intentar.');
            } else if ($this->factura->estado == 1) {
                $result = array('error' => 'T', 'msj' => 'El Documento que esta intentando procesar se encuentra dentro de los No Procesados.');
            } else if ($this->factura->estado == 2) {
                $result = array('error' => 'T', 'msj' => 'El Documento que esta intentando procesar ya fue Procesado.');
            } else {
                if ($this->factura->coddocumento != '01') {
                    if ($this->factura->numero_documento_mod) {
                        if ($this->factura->coddocumento_mod == '01') {
                            $ban = $this->bandeja->getNroDocProvPendiente($this->empresa->idempresa, $this->factura->identificacion, $this->factura->numero_documento_mod);
                            if ($ban) {
                                $result = array('error' => 'T', 'msj' => 'El Documento Modificado se encuentra en Pendiente dentro de la Bandeja Electrónica, realice el proceso respectivo y vuelva a intentar.');
                            }
                        }
                    }
                }
            }
            if ($result['error'] == 'F') {
                $email     = '';
                $direccion = '';
                $telefono  = '';
                //Si no tiene error agrego los datos del proveedor
                $prov = $this->proveedores->get_by_identificacion($this->factura->idempresa, $this->factura->identificacion);
                if ($prov) {
                    $email     = $prov->email;
                    $direccion = $prov->direccion;
                    $telefono  = $prov->telefono;
                }

                $result['email']     = $email;
                $result['direccion'] = $direccion;
                $result['telefono']  = $telefono;
            }
        } else if ($this->retencion) {
            $result = array('error' => 'F', 'msj' => '');
            $lineas = $this->lineasretbandejasri->all_by_idbandejasri_sh($this->retencion->idbandejasri);
            if ($lineas) {
                $result = array('error' => 'T', 'msj' => 'No tiene todo el detalle de la Retención homologado, realice las homologaciones y vuelva a intentar.');
            } else if ($this->retencion->estado == 1) {
                $result = array('error' => 'T', 'msj' => 'La Retención que esta intentando procesar se encuentra dentro de los No Procesados.');
            } else if ($this->retencion->estado == 2) {
                $result = array('error' => 'T', 'msj' => 'La Retención que esta intentando procesar ya fue Procesada.');
            }

            if ($result['error'] == 'F') {
                $email     = '';
                $direccion = '';
                $telefono  = '';
                //Si no tiene error agrego los datos del cliente
                $cli = $this->clientes->get_by_identificacion($this->retencion->idempresa, $this->retencion->identificacion);
                if ($cli) {
                    $email     = $cli->email;
                    $direccion = $cli->direccion;
                    $telefono  = $cli->telefono;
                }

                $result['email']     = $email;
                $result['direccion'] = $direccion;
                $result['telefono']  = $telefono;
            }
        }

        echo json_encode($result);
        exit;
    }

    private function validar_homologacion_masiva()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Documento No encontrado');
        if ($this->factura) {
            $result = array('error' => 'F', 'msj' => '');
            $agret = false;
            if ($this->factura->coddocumento != '04') {
                if ($this->empresa->agretencion) {
                    $agret = true;
                }
            }
            $lineas = $this->lineasbandejasri->all_by_idbandejasri_sh($this->factura->idbandejasri, $agret);
            if (!$lineas) {
                $result = array('error' => 'T', 'msj' => 'Todo el Documento se encuentra homologado, puede procesar el documento.');
            } else if ($this->factura->estado == 1) {
                $result = array('error' => 'T', 'msj' => 'El Documento que esta intentando procesar se encuentra dentro de los No Procesados.');
            } else if ($this->factura->estado == 2) {
                $result = array('error' => 'T', 'msj' => 'El Documento que esta intentando procesar ya fue Procesado.');
            }
        }

        echo json_encode($result);
        exit;
    }

    private function homologar_masivo()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Documento No encontrado');
        if ($this->factura) {
            $agret = false;
            if ($this->factura->coddocumento != '04') {
                if ($this->empresa->agretencion) {
                    $agret = true;
                }
            }
            $lineas = $this->lineasbandejasri->all_by_idbandejasri_sh($this->factura->idbandejasri, $agret);
            if (!$lineas) {
                $result = array('error' => 'T', 'msj' => 'Todo el Documento se encuentra homologado, puede procesar el documento.');
            } else if ($this->factura->estado == 1) {
                $result = array('error' => 'T', 'msj' => 'El Documento que esta intentando procesar se encuentra dentro de los No Procesados.');
            } else if ($this->factura->estado == 2) {
                $result = array('error' => 'T', 'msj' => 'El Documento que esta intentando procesar ya fue Procesado.');
            } else {
                $idarticulo = false;
                $idretencion_renta = NULL;
                $idretencion_iva = NULL;
                if (isset($_POST['crearart'])) {
                    $art = $this->articulos->get_by_codprincipal($this->empresa->idempresa, $_POST['ref_proveedor']);
                    if ($art) {
                        $idarticulo = $art->idarticulo;
                        if (isset($_POST['idretencion_renta'])) {
                            $idretencion_renta = $_POST['idretencion_renta'];
                        }
                        if (isset($_POST['idretencion_iva'])) {
                            $idretencion_iva = $_POST['idretencion_iva'];
                        }
                    } else {
                        $art               = new articulos();
                        $art->idempresa    = $this->empresa->idempresa;
                        $art->codprincipal = $_POST['ref_proveedor'];
                        if ($_POST['codbarras'] != '') {
                            $art->codbarras = $_POST['codbarras'];
                        }
                        $art->nombre     = $_POST['desc_proveedor'];
                        $art->idimpuesto = $_POST['idimpuesto'];
                        //Busco el impuesto generado
                        $impuesto = $this->impuestos->get($art->idimpuesto);
                        if ($impuesto) {
                            //calculo el precio
                            $art->precio = floatval($_POST['precio']) / (1 + ($impuesto->porcentaje / 100));
                        }
                        if ($_POST['idmarca'] != '') {
                            $art->idmarca = $_POST['idmarca'];
                        }
                        if ($_POST['idgrupo'] != '') {
                            $art->idgrupo = $_POST['idgrupo'];
                        }
                        $art->tipo          = $_POST['tipo'];
                        $art->fec_creacion  = date('Y-m-d');
                        $art->nick_creacion = $this->user->nick;

                        if ($art->save()) {
                            $idarticulo = $art->idarticulo;
                            if (isset($_POST['idretencion_renta'])) {
                                $idretencion_renta = $_POST['idretencion_renta'];
                            }
                            if (isset($_POST['idretencion_iva'])) {
                                $idretencion_iva = $_POST['idretencion_iva'];
                            }
                        } else {
                            $result = array('error' => 'T', 'msj' => 'Error al crear el Artículo.');
                        }
                    }
                } else {
                    $idarticulo = $_POST['idarticulo'];
                    if (isset($_POST['idretencion_renta'])) {
                        $idretencion_renta = $_POST['idretencion_renta'];
                    }
                    if (isset($_POST['idretencion_iva'])) {
                        $idretencion_iva = $_POST['idretencion_iva'];
                    }
                }

                if ($idarticulo) {
                    if ($this->lineasbandejasri->homologar_masivo($this->empresa->idempresa, $this->factura->identificacion, $this->factura->idbandejasri, $idarticulo, $idretencion_iva, $idretencion_renta)) {
                        $result = array('error' => 'F', 'msj' => '');
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al realizar la homologación del Artículo, en los documentos del Proveedor.');
                    }
                }
            }
        }

        echo json_encode($result);
        exit;
    }
}
