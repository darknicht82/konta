<?php
/**
 * Controlador de Compras -> Servicios.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_servicios extends controller
{
    //Filtros
    public $query;
    public $b_grupo;
    public $b_tipo;
    public $idempresa;
    //modelos
    public $articulos;
    public $impuestos;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Detalle de Servicios', 'Configuración', true, true, false, 'bi bi-list-check');
    }

    protected function private_core()
    {
        $this->init_filter();

        $this->articulos = new articulos();
        $this->impuestos = new impuestos();

        if (isset($_POST['codprincipal'])) {
            $this->crear_servicio();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_servicio();
        }

        $this->buscar();
        $this->buscar(-1);
    }

    private function init_filter()
    {
        $this->idempresa = $this->user->idempresa;
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

        $this->b_grupo = '';
        if (isset($_REQUEST['b_grupo']) && $_REQUEST['b_grupo'] != '') {
            $this->b_grupo = $_REQUEST['b_grupo'];
            $this->filtros .= '&b_grupo=' . $this->b_grupo;
        }

        $this->b_impuesto = '';
        if (isset($_REQUEST['b_impuesto']) && $_REQUEST['b_impuesto'] != '') {
            $this->b_impuesto = $_REQUEST['b_impuesto'];
            $this->filtros .= '&b_impuesto=' . $this->b_impuesto;
        }

        $this->b_tipo = 2;
    }

    private function crear_servicio()
    {
        if (!$this->articulos->get_by_codprincipal($this->idempresa, $_POST['codprincipal'])) {
            $articulo               = new articulos();
            $articulo->idempresa    = $this->user->idempresa;
            $articulo->codprincipal = $_POST['codprincipal'];
            $articulo->nombre       = $_POST['nombre'];
            if ($_POST['idgrupo'] != '') {
                $articulo->idgrupo = $_POST['idgrupo'];
            }
            $articulo->idimpuesto = $_POST['idimpuesto'];
            $articulo->tipo       = 2;

            //Busco el impuesto generado
            $impuesto = $this->impuestos->get($articulo->idimpuesto);
            if ($impuesto) {
                //calculo el precio
                $articulo->precio = floatval($_POST['precio']) / (1 + ($impuesto->porcentaje / 100));
            }

            $articulo->fec_creacion  = date('Y-m-d');
            $articulo->nick_creacion = $this->user->nick;

            if ($articulo->save()) {
                header('location: ' . $articulo->url());
            } else {
                $this->new_error_msg("No se pudo crear el servicio, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El servicio con Código Principal: " . $_POST['codprincipal'] . " ya se encuentra registrado. <b>(Revise en la parte inferior)</b>");
            $this->query = $_POST['codprincipal'];
        }
    }

    private function eliminar_servicio()
    {
        $articulo = $this->articulos->get($_GET['delete']);
        if ($articulo) {
            if ($articulo->delete()) {
                $this->new_message("Servicio eliminado correctamente.");
            } else {
                $this->new_error_msg("No se puede eliminar el servicio, debe estar utilizado en una transacción de compra o venta.");
            }
        } else {
            $this->new_advice("Error al eliminar, el servicio no se encuentra registrado o ya fue eliminado.");
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = buscar_articulos($this->idempresa, $this->query, $this->b_grupo, '', $this->b_tipo, $this->b_impuesto, '', '', '', '', $this->offset);
        } else {
            $this->cantidad = buscar_articulos($this->idempresa, $this->query, $this->b_grupo, '', $this->b_tipo, $this->b_impuesto, '', '', '', '', -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }
}