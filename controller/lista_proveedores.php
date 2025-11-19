<?php
/**
 * Controlador de Compras -> Proveedores.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_proveedores extends controller
{
    //Filtros
    public $query;
    public $b_tipoid;
    public $b_regimen;
    public $idempresa;
    //modelos
    public $proveedores;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Proveedores', 'Compras', true, true, false, 'bi bi-people-fill');
    }

    protected function private_core()
    {
        $this->init_filter();

        $this->proveedores = new proveedores();

        if (isset($_POST['identificacion'])) {
            $this->crear_proveedor();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_proveedor();
        } else if (isset($_REQUEST['tipoid_b'])) {
            $this->consulta_servidor();
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

        $this->query = '';
        if (isset($_REQUEST['query']) && $_REQUEST['query'] != '') {
            $this->query = $_REQUEST['query'];
            $this->filtros .= '&query=' . $this->query;
        }

        $this->b_tipoid = '';
        if (isset($_REQUEST['b_tipoid']) && $_REQUEST['b_tipoid'] != '') {
            $this->b_tipoid = $_REQUEST['b_tipoid'];
            $this->filtros .= '&b_tipoid=' . $this->b_tipoid;
        }

        $this->b_regimen = '';
        if (isset($_REQUEST['b_regimen']) && $_REQUEST['b_regimen'] != '') {
            $this->b_regimen = $_REQUEST['b_regimen'];
            $this->filtros .= '&b_regimen=' . $this->b_regimen;
        }
    }

    private function consulta_servidor()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error en la consulta');

        $prov = $this->proveedores->get_by_identificacion($this->idempresa, $_REQUEST['identificacion_b']);

        if ($prov) {
            $result = array('error' => 'R', 'msj' => 'El proveedor ya se encuentra registrado, utilice el buscador para registrarlo.');
        } else {
            if ($_REQUEST['tipoid_b'] == 'C') {
                $result = consultarCedula($_REQUEST['identificacion_b']);
            } else if ($_REQUEST['tipoid_b'] == 'R') {
                $result = consultarRucSri($_REQUEST['identificacion_b']);
            }
        }

        echo json_encode($result);
        exit;
    }

    private function crear_proveedor()
    {
        if (!$this->proveedores->get_by_identificacion($this->idempresa, $_POST['identificacion'])) {
            $proveedor = new proveedores();
            $proveedor->idempresa = $this->user->idempresa;
            $proveedor->identificacion = $_POST['identificacion'];
            $proveedor->tipoid = $_POST['tipoid'];
            $proveedor->razonsocial = $_POST['razonsocial'];
            if ($_POST['nombrecomercial'] != '') {
                $proveedor->nombrecomercial = $_POST['nombrecomercial'];
            } else {
                $proveedor->nombrecomercial = $_POST['razonsocial'];
            }
            $proveedor->telefono = $_POST['telefono'];
            if ($_POST['celular'] != '') {
                $proveedor->celular = $_POST['celular'];
            }
            $proveedor->email = $_POST['email'];
            $proveedor->direccion = $_POST['direccion'];
            $proveedor->regimen = $_POST['regimen'];
            $proveedor->obligado = isset($_POST['obligado']);
            $proveedor->agretencion = isset($_POST['agretencion']);
            $proveedor->fec_creacion = date('Y-m-d');
            $proveedor->nick_creacion = $this->user->nick;

            if ($proveedor->save()) {
                header('location: ' . $proveedor->url());
            } else {
                $this->new_error_msg("No se pudo crear el proveedor, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El proveedor con identificacion ".$_POST['identificacion']." ya se encuentra registrado. <b>(Revise en la parte inferior)</b>");
            $this->query = $_POST['identificacion'];
        }
    }

    private function eliminar_proveedor()
    {
        $proveedor = $this->proveedores->get($_GET['delete']);
        if ($proveedor) {
            if ($proveedor->delete()) {
                $this->new_message("Proveedor eliminado correctamente.");
            } else {
                $this->new_error_msg("No se puede eliminar el proveedor, debe estar utilizado en una transacción de compra.");
            }
        } else {
            $this->new_advice("Error al eliminar, el proveedor no se encuentra registrado o ya fue eliminado.");
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = buscar_proveedores($this->idempresa, $this->query, $this->b_tipoid, $this->b_regimen, $this->offset);
        } else {
            $this->cantidad = buscar_proveedores($this->idempresa, $this->query, $this->b_tipoid, $this->b_regimen, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }
}
