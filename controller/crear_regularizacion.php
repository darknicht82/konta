<?php
/**
 * Controlador de Nueva Regularizacion -> Regularizaciones.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class crear_regularizacion extends controller
{
    //Filtros
    public $query;
    public $b_grupo;
    public $b_marca;
    public $b_tipo;
    public $idempresa;
    public $idestablecimiento;
    //modelos
    public $articulos;
    public $impuestos;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Nueva Regularizacion', 'Regul.', false, false, false, 'bi bi-file-earmark-plus');
    }

    protected function private_core()
    {
        $this->init_filter();

        $this->articulos              = new articulos();
        $this->establecimiento        = new establecimiento();
        $this->lineasregularizaciones = new lineasregularizaciones();

        if (isset($_POST['establecimiento'])) {
            $this->nueva_regularizacion();
        }

        $this->buscar();
    }

    private function init_filter()
    {
        $this->idempresa = $this->user->idempresa;
        $this->query     = '';
        if (isset($_REQUEST['query']) && $_REQUEST['query'] != '') {
            $this->query = $_REQUEST['query'];
        }

        $this->b_grupo = '';
        if (isset($_REQUEST['b_grupo']) && $_REQUEST['b_grupo'] != '') {
            $this->b_grupo = $_REQUEST['b_grupo'];
        }

        $this->b_marca = '';
        if (isset($_REQUEST['b_marca']) && $_REQUEST['b_marca'] != '') {
            $this->b_marca = $_REQUEST['b_marca'];
        }

        $this->idestablecimiento = '';
        if (isset($_REQUEST['idestablecimiento']) && $_REQUEST['idestablecimiento'] != '') {
            $this->idestablecimiento = $_REQUEST['idestablecimiento'];
        }

        $this->b_tipo = 1;
    }

    private function buscar()
    {
        if ($this->idestablecimiento != '') {
            $this->resultados = buscar_articulos($this->idempresa, $this->query, $this->b_grupo, $this->b_marca, $this->b_tipo, '', $this->idestablecimiento);
        } else {
            $this->resultados = array();
        }
    }

    private function nueva_regularizacion()
    {
        if (isset($_POST['nstock'])) {
            //valido si todos tienen valor para la regularizacion
            $exists_data = false;
            foreach ($_POST['nstock'] as $key => $st) {
                if ($st != '') {
                    $exists_data = true;
                    break;
                }
            }

            if ($exists_data) {
                $regularizacion                    = new regularizaciones();
                $regularizacion->idempresa         = $this->idempresa;
                $regularizacion->idestablecimiento = $_POST['establecimiento'];
                $regularizacion->fec_emision       = date('Y-m-d');
                $regularizacion->hora_emision      = date('H:i:s');
                if (isset($_POST['observaciones']) && $_POST['observaciones'] != '') {
                    $regularizacion->observaciones = $_POST['observaciones'];
                }
                $regularizacion->fec_creacion  = date('Y-m-d');
                $regularizacion->nick_creacion = $this->user->nick;

                if ($regularizacion->save()) {
                    $art0 = new articulos(false, $regularizacion->idestablecimiento);
                    foreach ($_POST['nstock'] as $key => $st) {
                        if ($st != '') {
                            $art = $art0->get($key);
                            if ($art) {
                                $linea = new lineasregularizaciones();
                                $linea->idregularizacion = $regularizacion->idregularizacion;
                                $linea->idarticulo = $art->idarticulo;
                                $linea->codprincipal = $art->codprincipal;
                                $linea->descripcion = $art->nombre;
                                $linea->cantidad = $art->stock_fisico;
                                $linea->costo = $art->getCostoArt();
                                $linea->nueva_cantidad = floatval($st);
                                $linea->fec_creacion  = date('Y-m-d');
                                $linea->nick_creacion = $this->user->nick;

                                if ($linea->save()) {
                                    $regularizacion->total += $linea->costototal;
                                } else {
                                    $this->new_advice("Error al generar la linea de la regularización.");
                                    $regularizacion->delete();
                                    return;
                                }
                            } else {
                                $this->new_advice("No se encuentra el articulo, verifique y vuelva a intentar.");
                                $regularizacion->delete();
                                return;
                            }
                        }
                    }

                    if ($regularizacion->save()) {
                        header('location: ' . $regularizacion->url());
                    } else {
                        $this->new_advice("Error al generar la regularización de stock.");
                        $regularizacion->delete();
                        return;
                    }
                } else {
                    $this->new_error_msg("Error al generar la regularización de stock, verifique los datos y vuelva a intentarlo.");
                }
            } else {
                $this->new_advice("No tiene datos para realizar la regularización");
            }
        } else {
            $this->new_error_msg("No encuentra datos para realizar la regularización.");
        }
    }
}
