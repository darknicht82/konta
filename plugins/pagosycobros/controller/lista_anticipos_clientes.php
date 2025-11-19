<?php
/**
 * Controlador de Cobros -> Anticipos
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_anticipos_clientes extends controller
{
    //Filtros
    public $query;
    public $tipo;
    public $desde;
    public $hasta;
    public $nom_cliente;
    public $idcliente;
    public $idempresa;
    //modelos
    public $clientes;
    public $trans_cobros;
    public $formaspago;
    public $anticiposcli;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Lista Anticipos', 'Ventas', 'Anticipos', true, false, 'bi bi-cash');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();
        //Tiene accesos para imprimir
        $this->impresion = $this->user->have_access_to('impresion_ventas');
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en la ficha de Cliente?
        $this->allow_modify_cli = $this->user->allow_modify_on('ver_cliente');

        if (isset($_REQUEST['tipoid_b'])) {
            $this->consulta_servidor();
        } else if (isset($_POST['identificacion'])) {
            $this->crear_cliente();
        } else if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_POST['idfp'])) {
            $this->buscar_forma_pago();
        } else if (isset($_POST['idcliente'])) {
            $this->guardar_anticipo();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_anticipo();
        } else if (isset($_GET['imprimir'])) {
            $this->impresiones();
        } else if (isset($_POST['historial'])) {
            $this->historial();
        }

        //Busqueda
        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->clientes     = new clientes();
        $this->trans_cobros = new trans_cobros();
        $this->formaspago   = new formaspago();
        $this->anticiposcli = new anticiposcli();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
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

        $this->idformapago = '';
        if (isset($_REQUEST['idformapago'])) {
            $this->idformapago = $_REQUEST['idformapago'];
            $this->filtros .= '&idformapago=' . $this->idformapago;
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

        $this->nom_cliente = '';
        $this->idcliente   = '';
        if (isset($_REQUEST['idcliente2']) && $_REQUEST['idcliente2'] != '') {
            $cliente = $this->clientes->get($_REQUEST['idcliente2']);
            if ($cliente) {
                $this->idcliente   = $cliente->idcliente;
                $this->nom_cliente = $cliente->identificacion . " - " . $cliente->razonsocial;
                $this->filtros .= '&idcliente=' . $this->idcliente;
            }
        }

        $this->anulados = isset($_REQUEST['anulados']);
        if (isset($_REQUEST['anulados'])) {
            $this->filtros .= '&anulados';
        }
    }

    private function buscar($offset = 0)
    {
        if ($offset == 0) {
            $this->resultados = $this->anticiposcli->buscarAnticipos($this->idempresa, $this->query, $this->idcliente, $this->idformapago, $this->desde, $this->hasta, false, $this->anulados, $this->offset);
        } else {
            $this->cantidad = $this->anticiposcli->buscarAnticipos($this->idempresa, $this->query, $this->idcliente, $this->idformapago, $this->desde, $this->hasta, false, $this->anulados, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }

    private function consulta_servidor()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error en la consulta');

        $cli = $this->clientes->get_by_identificacion($this->idempresa, $_REQUEST['identificacion_b']);

        if ($cli) {
            $result = array('error' => 'R', 'msj' => 'El cliente ya se encuentra registrado, puede crear el Anticipo con el botón Nuevo.');
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

    private function buscar_cliente()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_clientes($this->idempresa, $_GET['buscar_cliente']);

        echo json_encode($result);
        exit;
    }

    private function eliminar_anticipo()
    {
        $anticipo = $this->anticiposcli->get($_GET['delete']);
        if ($anticipo) {
            if ($anticipo->idempresa != $this->idempresa) {
                $this->new_advice('El Anticipo no es valido para su empresa.');
                return;
            }
            if (!$this->allow_delete) {
                $this->new_advice('El usuario no tiene permiso para eliminar.');
                return;
            }

            if ($anticipo->delete()) {
                $this->new_message("Anticipo eliminado correctamente.");
            }
        } else {
            $this->new_error_msg('Anticipo no encontrado.');
        }
    }

    private function crear_cliente()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error en la consulta');
        if (!$this->clientes->get_by_identificacion($this->idempresa, $_POST['identificacion'])) {
            $cliente                 = new clientes();
            $cliente->idempresa      = $this->idempresa;
            $cliente->identificacion = $_POST['identificacion'];
            $cliente->tipoid         = $_POST['tipoid'];
            $cliente->razonsocial    = $_POST['razonsocial'];
            if ($_POST['nombrecomercial'] != '') {
                $cliente->nombrecomercial = $_POST['nombrecomercial'];
            } else {
                $cliente->nombrecomercial = $_POST['razonsocial'];
            }
            $cliente->telefono = $_POST['telefono'];
            if ($_POST['celular'] != '') {
                $cliente->celular = $_POST['celular'];
            }
            $cliente->email         = $_POST['email'];
            $cliente->direccion     = $_POST['direccion'];
            $cliente->regimen       = $_POST['regimen'];
            $cliente->obligado      = isset($_POST['obligado']);
            $cliente->agretencion   = isset($_POST['agretencion']);
            $cliente->fec_creacion  = date('Y-m-d');
            $cliente->nick_creacion = $this->user->nick;

            if ($cliente->save()) {
                $result = array('error' => 'F', 'msj' => "Cliente creado correctamente.", 'cliente' => $cliente);
            } else {
                $result = array('error' => 'T', 'msj' => "No se pudo crear el cliente, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $result = array('error' => 'T', 'msj' => "El cliente con identificacion " . $_POST['identificacion'] . " ya se encuentra registrado.");
        }

        echo json_encode($result);
        exit;
    }

    private function buscar_forma_pago()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Forma de Pago No encontrado.');
        $fp             = $this->formaspago->get($_POST['idfp']);
        if ($fp) {
            $result = array('error' => 'F', 'msj' => '', 'fpago' => $fp);
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_anticipo()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Cliente no encontrado.');

        //verifico si existe el cliente seleccionado
        $cliente = $this->clientes->get($_POST['idcliente']);
        if ($cliente) {
            if (isset($_POST['num_doc'])) {
                if (trim($_POST['num_doc']) != '') {
                    //valido si el numero de documento no se encuentra ya registrado
                    $numDoc = $this->trans_cobros->getNumDocCliente($this->idempresa, $cliente->idcliente, $_POST['fpselec'], $_POST['num_doc']);
                    if ($numDoc) {
                        $result = array('error' => 'T', 'msj' => 'El Número de Documento ya se encuentra registrado.');
                        echo json_encode($result);
                        exit;
                    }
                } else {
                    //Valido si no es valido el numero de
                    $result = array('error' => 'T', 'msj' => 'Número de Documento no Válido.');
                    echo json_encode($result);
                    exit;
                }
            }

            $anticipo              = new \anticiposcli();
            $anticipo->idempresa   = $this->idempresa;
            $anticipo->idcliente   = $cliente->idcliente;
            $anticipo->idformapago = $_POST['fpselec'];
            $anticipo->fec_emision = $_POST['fec_emision'];
            $anticipo->fecha_trans = $_POST['fec_trans'];
            $anticipo->valor       = $_POST['valor'];
            if (isset($_POST['num_doc'])) {
                $anticipo->num_doc = $_POST['num_doc'];
            }
            if (isset($_POST['observaciones']) && $_POST['observaciones'] != '') {
                $anticipo->observaciones = $_POST['observaciones'];
            }
            $anticipo->fec_creacion  = date('Y-m-d');
            $anticipo->nick_creacion = $this->user->nick;
            if ($anticipo->save()) {
                //Si se guarda correctamente genero la transaccion en el trans_cobros
                $tcobro                = new \trans_cobros();
                $tcobro->idempresa     = $this->idempresa;
                $tcobro->idcliente     = $anticipo->idcliente;
                $tcobro->idanticipocli = $anticipo->idanticipocli;
                $tcobro->idformapago   = $anticipo->idformapago;
                $tcobro->tipo          = 'Anticipo';
                $tcobro->fecha_trans   = $anticipo->fecha_trans;
                $tcobro->num_doc       = $anticipo->num_doc;
                $tcobro->credito       = floatval($anticipo->valor);
                $tcobro->esabono       = false;
                $tcobro->fec_creacion  = date('Y-m-d');
                $tcobro->nick_creacion = $this->user->nick;
                if (!$tcobro->save()) {
                    $result = array('error' => 'T', 'msj' => 'Error al generar el Cobro en el Anticipo.');
                    $anticipo->delete();
                } else {
                    $result = array('error' => 'F', 'msj' => 'Anticipo de Cliente Generado correctamente.', 'url' => $this->url() . '&imprimir=' . $anticipo->idanticipocli);
                }
            } else {
                $msjs = '';
                foreach ($anticipo->get_errors() as $key => $error) {
                    $msjs .= $error . ". ";
                }
                $msj = '';
                if ($msjs != '') {
                    $msj = ' Error: ' . $msjs;
                }
                $result = array('error' => 'T', 'msj' => 'Error al generar el Anticipo del cliente: ' . $cliente->razonsocial . '.' . $msj);
                echo json_encode($result);
                exit;
            }
        }
        echo json_encode($result);
        exit;
    }

    private function impresiones()
    {
        $mensaje = '';
        if ($this->impresion) {
            $mensaje = 'Imprima el comprobante presionando <a href="index.php?page=impresion_ventas&imprimir_anticipo=' . $_GET['imprimir'] . '" target="_blank">aquí</a>';
        }

        if ($mensaje != '') {
            $this->new_message($mensaje);
        }
    }

    private function historial()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Historial no encontrado.');

        $anticipo = $this->anticiposcli->get($_POST['historial']);
        if ($anticipo) {
            $historial = $this->trans_cobros->all_by_anticipocli($this->idempresa, $anticipo->idanticipocli);
            if ($historial) {
                $result = array('error' => 'F', 'msj' => '', 'historial' => $historial, 'total' => $anticipo->valor);
            }
        } else {
            $result = array('error' => 'T', 'msj' => 'Anticipo de Cliente no encontrado.');
        }

        echo json_encode($result);
        exit;
    }
}
