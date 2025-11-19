<?php
/**
 * Controlador de Articulos -> Articulo.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_articulo extends controller
{
    //variables
    public $articulo;
    public $tab;
    //modelos
    public $articulos;
    public $stocks;
    public $trans_inventario;
    public $unidades;
    public $art_unidades;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ver Articulo', 'Inventario', false, false);
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->articulo = false;
        if (isset($_GET['id'])) {
            $this->articulo = $this->articulos->get($_GET['id']);
            if ($this->articulo) {
                if ($this->articulo->idempresa == $this->empresa->idempresa) {
                    if (isset($_POST['codprincipal'])) {
                        $this->modificar_articulo();
                    } else if (isset($_POST['idstock'])) {
                        $this->modificar_stock();
                    } else if (isset($_POST['buscar_kardex'])) {
                        $this->kardex_articulo();
                    } else if (isset($_GET['recalcular'])) {
                        $this->recalcular_stock();
                    } else if (isset($_POST['new_pres'])) {
                        $this->nueva_presentacion();
                    } else if (isset($_POST['idartunidad'])) {
                        $this->editar_presentacion();
                    } else if (isset($_GET['delum'])) {
                        $this->delete_presentacion();
                    } else if (isset($_POST['new_ins'])) {
                        $this->nuevo_insumo();
                    } else if (isset($_POST['idinsumo'])) {
                        $this->editar_insumo();
                    } else if (isset($_GET['delins'])) {
                        $this->delete_insumo();
                    } else if (isset($_POST['imagen'])) {
                        $this->cargar_imagen_articulo();
                    } else if (isset($_GET['delete_imagen'])) {
                        $this->borrar_imagen_articulo();
                    }
                } else {
                    $this->new_advice("El Articulo no esta disponible para su empresa.");
                    $this->articulo = false;
                    return;
                }
            } else {
                $this->new_advice("No se encuentra el articulo seleccionado.");
            }
        }

        if (isset($_POST['config'])) {
            $this->buscar_configuracion();
        } else if (isset($_GET['buscar_subcuenta'])) {
            $this->buscar_subcuenta();
        } else if (isset($_POST['idarticulo_config'])) {
            $this->tratar_configuracion();
        } else if (isset($_GET['buscar_articulo'])) {
            $this->buscar_articulos();
        } else if (isset($_POST['idarticulo'])) {
            $this->buscar_articulo();
        }
    }

    private function init_modelos()
    {
        $this->articulos        = new articulos();
        $this->stocks           = new stocks();
        $this->trans_inventario = new trans_inventario();
        $this->grupos           = new grupos();
        $this->marcas           = new marcas();
        $this->paso_um          = false;
        if (complemento_exists('unidadesmedida')) {
            $this->paso_um      = true;
            $this->unidades     = new unidades_medida();
            $this->art_unidades = new articulos_unidades();
        }
        if (complemento_exists('contabilidad')) {
            $this->ejercicios = new ejercicios();
            $this->parametros = new param_contable();
            $this->subcuentas = new plancuentas();
        }
        if (complemento_exists('articulos_compuestos')) {
            $this->insumos = new insumos_art();
        }
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
        $this->tab       = 'datos';
        if (isset($_GET['tab'])) {
            $this->tab = $_GET['tab'];
        }
    }

    private function modificar_articulo()
    {
        $this->articulo->codprincipal = $_POST['codprincipal'];
        if ($_POST['codauxiliar'] != '') {
            $this->articulo->codauxiliar = $_POST['codauxiliar'];
        } else {
            $this->articulo->codauxiliar = null;
        }
        if ($_POST['codbarras'] != '') {
            $this->articulo->codbarras = $_POST['codbarras'];
        } else {
            $this->articulo->codbarras = null;
        }
        $this->articulo->nombre = $_POST['nombre'];
        if ($_POST['detalle'] != '') {
            $this->articulo->detalle = $_POST['detalle'];
        } else {
            $this->articulo->detalle = null;
        }
        $this->articulo->idimpuesto = $_POST['idimpuesto'];
        $this->articulo->precio     = $_POST['precio'];
        $this->articulo->dto        = 0;
        if ($_POST['idmarca'] != '') {
            $this->articulo->idmarca = $_POST['idmarca'];
        } else {
            $this->articulo->idmarca = null;
        }
        if ($_POST['idgrupo'] != '') {
            $this->articulo->idgrupo = $_POST['idgrupo'];
        } else {
            $this->articulo->idgrupo = null;
        }
        $this->articulo->controlar_stock = $_POST['controlar_stock'];
        if (isset($_POST['idunidad'])) {
            $this->articulo->idunidad = $_POST['idunidad'];
        }
        $this->articulo->sevende   = isset($_POST['sevende']);
        $this->articulo->secompra  = isset($_POST['secompra']);
        $this->articulo->bloqueado = isset($_POST['bloqueado']);
        $this->articulo->compuesto = isset($_POST['compuesto']);
        $this->articulo->gencuadre = isset($_POST['gencuadre']);

        $this->articulo->fec_modificacion  = date('Y-m-d');
        $this->articulo->nick_modificacion = $this->user->nick;

        if ($this->articulo->save()) {
            $this->new_message("Artículo modificado correctamente.");
        } else {
            $this->new_error_msg("Error al modificar el Artículo.");
        }
    }

    private function modificar_stock()
    {
        $stock = $this->stocks->get($_POST['idstock']);
        if ($stock) {
            if ($_POST['ubicacion'] != '') {
                $stock->ubicacion = $_POST['ubicacion'];
            } else {
                $stock->ubicacion = null;
            }

            $stock->fec_modificacion  = date('Y-m-d');
            $stock->nick_modificacion = $this->user->nick;

            if ($stock->save()) {
                $this->new_message("Stock Actualizado correctamente");
            } else {
                $this->new_error_msg("Existió un error al actualizar el stock.");
            }
        } else {
            $this->new_advice("Stock no encontrado.");
        }
    }

    private function kardex_articulo()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No se encuentra movimientos del Artículo.');

        $trans = new trans_inventario();
        $datos = $trans->get_by_articulo($this->articulo->idarticulo, $_POST['buscar_kardex']);
        if ($datos) {
            $result = array('error' => 'F', 'msj' => '', 'kardex' => $datos);
        }

        echo json_encode($result);
        exit;
    }

    private function recalcular_stock()
    {
        $stock = new stocks();
        $st    = $stock->get_by_idestab_idart($_GET['recalcular'], $this->articulo->idarticulo);
        if ($st) {
            $trans     = new trans_inventario();
            $stock     = $trans->getstock_by_articulo($this->articulo->idarticulo, $_GET['recalcular']);
            $st->stock = $stock;
            if ($st->save()) {
                $this->new_message("Stock del establecimiento " . $st->get_establecimiento() . " recalculado correctamente.");
            } else {
                $this->new_error_msg("Existió un inconveniente al realizar el recalculo de stock.");
            }
        } else {
            $this->new_advice("Stock de Artículo no encontrado, no se puede realizar el proceso de recalculo.");
        }
    }

    private function nueva_presentacion()
    {
        $presentacion             = new articulos_unidades();
        $presentacion->idempresa  = $this->empresa->idempresa;
        $presentacion->idunidad   = $_POST['idunidad'];
        $presentacion->idarticulo = $this->articulo->idarticulo;
        $presentacion->cantidad   = $_POST['cantidad'];
        $presentacion->precio     = $_POST['precio'];
        //Auditoria
        $presentacion->fec_creacion  = date('Y-m-d');
        $presentacion->nick_creacion = $this->user->nick;

        if ($presentacion->save()) {
            $this->new_message('Unidad de Medida del Artículo, registrada correctamente.');
        } else {
            $this->new_error_msg('Error al registrar la Unidad de Medida del Artículo.');
        }
    }

    private function editar_presentacion()
    {
        $presentacion = $this->art_unidades->get($_POST['idartunidad']);
        if ($presentacion) {
            $presentacion->cantidad = $_POST['cantidad'];
            $presentacion->precio   = $_POST['precio'];
            //Auditoria
            $presentacion->fec_modificacion  = date('Y-m-d');
            $presentacion->nick_modificacion = $this->user->nick;
            if ($presentacion->save()) {
                $this->new_message('Unidad de Medida del Artículo, modificada correctamente.');
            } else {
                $this->new_error_msg('Error al modificar la Unidad de Medida del Artículo.');
            }
        } else {
            $this->new_error_msg("Presentación del artículo no encontrada.");
        }
    }

    private function delete_presentacion()
    {
        $presentacion = $this->art_unidades->get($_GET['delum']);
        if ($presentacion) {
            if ($presentacion->delete()) {
                $this->new_message('Unidad de Medida del Artículo, eliminada correctamente.');
            } else {
                $this->new_error_msg('Error al eliminar la Unidad de Medida del Artículo.');
            }
        } else {
            $this->new_error_msg("Presentación del artículo no encontrada.");
        }
    }

    private function delete_insumo()
    {
        $insumo = $this->insumos->get($_GET['delins']);
        if ($insumo) {
            if ($insumo->delete()) {
                $this->new_message('Insumo eliminado correctamente.');
            } else {
                $this->new_error_msg('Error al eliminar el insumo del Artículo.');
            }
        } else {
            $this->new_error_msg("Insumo no encontrado.");
        }
    }

    public function getTipoCosto()
    {
        $costoinv = 'costopromedio';
        $param0   = new \parametrizacion();
        $param    = $param0->all_by_codigo($this->empresa->idempresa, 'costoinv');
        if ($param) {
            $costoinv = $param->valor;
        }

        return $costoinv;
    }

    private function buscar_configuracion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Artículo No encontrado.');
        if ($this->articulo) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByArticulo($this->articulo->idarticulo, $ejercicio->idejercicio);
                if ($param) {
                    $compras = false;
                    if ($param->idsubccompras) {
                        $comp0 = $this->subcuentas->get($param->idsubccompras);
                        if ($comp0) {
                            $compras = $comp0;
                        }
                    }
                    $ventas = false;
                    if ($param->idsubcventas) {
                        $vent0 = $this->subcuentas->get($param->idsubcventas);
                        if ($vent0) {
                            $ventas = $vent0;
                        }
                    }
                    $costos = false;
                    if ($param->idsubccostos) {
                        $cost0 = $this->subcuentas->get($param->idsubccostos);
                        if ($cost0) {
                            $costos = $cost0;
                        }
                    }
                    $notas = false;
                    if ($param->idsubcntventas) {
                        $not0 = $this->subcuentas->get($param->idsubcntventas);
                        if ($not0) {
                            $notas = $not0;
                        }
                    }
                    $notasct = false;
                    if ($param->idsubcntcostos) {
                        $notct0 = $this->subcuentas->get($param->idsubcntcostos);
                        if ($notct0) {
                            $notasct = $notct0;
                        }
                    }
                    $result = array('error' => 'F', 'msj' => '', 'compras' => $compras, 'ventas' => $ventas, 'costos' => $costos, 'notas' => $notas, 'notasct' => $notasct);
                } else {
                    $compras = false;
                    $ventas  = false;
                    $costos  = false;
                    $notas   = false;
                    $notasct = false;
                    $result  = array('error' => 'F', 'msj' => '', 'compras' => $compras, 'ventas' => $ventas, 'costos' => $costos, 'notas' => $notas, 'notasct' => $notasct);
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Ejercicio No encontrado.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_subcuenta()
    {
        $this->template = false;
        $result         = array();
        $result         = $this->subcuentas->buscar_subcuenta($this->idempresa, $_GET['ejer'], $_GET['buscar_subcuenta']);

        echo json_encode($result);
        exit;
    }

    private function tratar_configuracion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Artículo No encontrado.');
        if ($this->articulo) {
            $ejercicio = $this->ejercicios->get($_POST['idejercicio']);
            if ($ejercicio) {
                $param = $this->parametros->getByArticulo($this->articulo->idarticulo, $ejercicio->idejercicio);
                if (!$param) {
                    $param                = new param_contable();
                    $param->idempresa     = $this->idempresa;
                    $param->idejercicio   = $ejercicio->idejercicio;
                    $param->idarticulo    = $this->articulo->idarticulo;
                    $param->fec_creacion  = date('Y-m-d');
                    $param->nick_creacion = $this->user->nick;
                } else {
                    $param->fec_modificacion  = date('Y-m-d');
                    $param->nick_modificacion = $this->user->nick;
                }
                $param->idsubccompras = null;
                if (isset($_POST['idsubccompras'])) {
                    $param->idsubccompras = $_POST['idsubccompras'];
                }
                $param->idsubcventas = null;
                if (isset($_POST['idsubcventas'])) {
                    $param->idsubcventas = $_POST['idsubcventas'];
                }
                $param->idsubccostos = null;
                if (isset($_POST['idsubccostos'])) {
                    $param->idsubccostos = $_POST['idsubccostos'];
                }
                $param->idsubcntventas = null;
                if (isset($_POST['idsubcntventas'])) {
                    $param->idsubcntventas = $_POST['idsubcntventas'];
                }
                $param->idsubcntcostos = null;
                if (isset($_POST['idsubcntcostos'])) {
                    $param->idsubcntcostos = $_POST['idsubcntcostos'];
                }

                if ($param->save()) {
                    $result = array('error' => 'F', 'msj' => 'Parametrización guardada correctamente.');
                } else {
                    $msj = '';
                    foreach ($param->get_errors() as $key => $e) {
                        $msj .= $e . ". ";
                    }
                    $mensaje = '';
                    if ($msj) {
                        $mensaje = 'Error: ' . $msj;
                    }
                    $result = array('error' => 'T', 'msj' => 'No se pudo almacenar la Parametrización. ' . $mensaje);
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Ejercicio No encontrado.');
            }
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
            $result = array('error' => 'F', 'msj' => '', 'art' => $articulo, 'costo' => $articulo->getCostoArt());
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

    private function nuevo_insumo()
    {
        $insumo                 = new insumos_art();
        $insumo->idempresa      = $this->empresa->idempresa;
        $insumo->idarticulocomp = $this->articulo->idarticulo;
        $insumo->idarticulo     = $_POST['idarticulo_ins'];
        $insumo->cantidad       = $_POST['cantidad_ins'];
        //Auditoria
        $insumo->fec_creacion  = date('Y-m-d');
        $insumo->nick_creacion = $this->user->nick;

        if ($insumo->save()) {
            $this->new_message('Insumo, registrado correctamente.');
        } else {
            $this->new_error_msg('Error al registrar el insumo del Artículo.');
        }
    }

    private function editar_insumo()
    {
        $insumo = $this->insumos->get($_POST['idinsumo']);
        if ($insumo) {
            $insumo->cantidad = $_POST['cantidad'];
            //Auditoria
            $insumo->fec_modificacion  = date('Y-m-d');
            $insumo->nick_modificacion = $this->user->nick;
            if ($insumo->save()) {
                $this->new_message('Insumo del Artículo, modificada correctamente.');
            } else {
                $this->new_error_msg('Error al modificar la Insumo del Artículo.');
            }
        } else {
            $this->new_error_msg("Insumo del artículo no encontrado.");
        }
    }

    private function cargar_imagen_articulo()
    {
        //carga de logo
        if (is_uploaded_file($_FILES['imagen_art']['tmp_name'])) {
            if (!file_exists(JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/articulos")) {
                @mkdir(JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/articulos", 0777, true);
            }

            $rutalogo = "";
            if (substr(strtolower($_FILES['imagen_art']['name']), -3) == 'png') {
                $rutalogo = JG_MYDOCS . "datosEmpresas/" . $this->empresa->idempresa . "/articulos/" . $this->articulo->idarticulo . ".png";
            } else if (substr(strtolower($_FILES['imagen_art']['name']), -3) == 'jpg') {
                $rutalogo = JG_MYDOCS . "datosEmpresas/" . $this->empresa->idempresa . "/articulos/" . $this->articulo->idarticulo . ".jpg";
            }
            // Image temp source
            $imageTemp = $_FILES["imagen_art"]["tmp_name"];
            // Comprimos el fichero
            $compressedImage = compressImage($imageTemp, $rutalogo);

            if ($compressedImage) {
                $this->articulo->imagen = $rutalogo;
                if ($this->articulo->save()) {
                    $this->new_message('Imagen guardada correctamente.');
                } else {
                    $this->new_error_msg('Error al guardar la imagen del Artículo.');
                }
            } else {
                $this->new_error_msg("Error al comprimir la imagen");
            }
        }
    }

    private function borrar_imagen_articulo()
    {
        if (file_exists($this->articulo->imagen)) {
            unlink($this->articulo->imagen);
        }
        $this->articulo->imagen = null;
        if ($this->articulo->save()) {
            $this->new_message('Imagen eliminada correctamente.');
        } else {
            $this->new_error_msg('Error al eliminar la Imagen del Articulo.');
        }
    }
}
