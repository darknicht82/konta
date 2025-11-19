<?php
/**
 * Controlador de Ventas -> Clientes.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_clientes extends controller
{
    //Filtros
    public $query;
    public $b_tipoid;
    public $b_regimen;
    public $idempresa;
    //modelos
    public $clientes;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Clientes', 'Ventas', true, true, false, 'bi bi-people-fill');
    }

    protected function private_core()
    {
        $this->init_filter();

        $this->clientes = new clientes();

        if (isset($_POST['identificacion'])) {
            $this->crear_cliente();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_cliente();
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

        $prov = $this->clientes->get_by_identificacion($this->idempresa, $_REQUEST['identificacion_b']);

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

    private function crear_cliente()
    {
        if (!$this->clientes->get_by_identificacion($this->idempresa, $_POST['identificacion'])) {
            $cliente = new clientes();
            $cliente->idempresa = $this->user->idempresa;
            $cliente->identificacion = $_POST['identificacion'];
            $cliente->tipoid = $_POST['tipoid'];
            $cliente->razonsocial = $_POST['razonsocial'];
            if ($_POST['nombrecomercial'] != '') {
                $cliente->nombrecomercial = $_POST['nombrecomercial'];
            } else {
                $cliente->nombrecomercial = $_POST['razonsocial'];
            }
            $cliente->telefono = $_POST['telefono'];
            if ($_POST['celular'] != '') {
                $cliente->celular = $_POST['celular'];
            }
            $cliente->email = $_POST['email'];
            $cliente->direccion = $_POST['direccion'];
            $cliente->regimen = $_POST['regimen'];
            $cliente->obligado = isset($_POST['obligado']);
            $cliente->agretencion = isset($_POST['agretencion']);
            $cliente->fec_creacion = date('Y-m-d');
            $cliente->nick_creacion = $this->user->nick;

            if ($cliente->save()) {
                header('location: ' . $cliente->url()); 
            } else {
                $this->new_error_msg("No se pudo crear el cliente, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El cliente con identificacion ".$_POST['identificacion']." ya se encuentra registrado. <b>(Revise en la parte inferior)</b>");
            $this->query = $_POST['identificacion'];
        }
    }

    private function eliminar_cliente()
    {
        $cliente = $this->clientes->get($_GET['delete']);
        if ($cliente) {
            if ($cliente->delete()) {
                $this->new_message("Cliente eliminado correctamente.");
            } else {
                $this->new_error_msg("No se puede eliminar el cliente, debe estar utilizado en una transacción de venta.");
            }
        } else {
            $this->new_advice("Error al eliminar, el cliente no se encuentra registrado o ya fue eliminado.");
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = buscar_clientes($this->idempresa, $this->query, $this->b_tipoid, $this->b_regimen, $this->offset);
        } else {
            $this->cantidad = buscar_clientes($this->idempresa, $this->query, $this->b_tipoid, $this->b_regimen, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }
}