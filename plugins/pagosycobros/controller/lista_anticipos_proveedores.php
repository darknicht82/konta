<?php
/**
 * Controlador de Pagos -> Anticipo
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_anticipos_proveedores extends controller
{
    //Filtros
    public $query;
    public $tipo;
    public $desde;
    public $hasta;
    public $nom_proveedor;
    public $idproveedor;
    public $idempresa;
    //modelos
    public $proveedores;
    public $trans_pagos;
    public $formaspago;
    public $anticiposprov;
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Lista Anticipos', 'Compras', 'Anticipos', true, false, 'bi bi-cash');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();
        //Tiene accesos para imprimir
        $this->impresion = $this->user->have_access_to('impresion_compras');
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en la ficha de Proveedor?
        $this->allow_modify_prov = $this->user->allow_modify_on('ver_proveedor');

        if (isset($_REQUEST['tipoid_b'])) {
            $this->consulta_servidor();
        } else if (isset($_POST['identificacion'])) {
            $this->crear_proveedor();
        } else if (isset($_GET['buscar_proveedor'])) {
            $this->buscar_proveedor();
        } else if (isset($_POST['idfp'])) {
            $this->buscar_forma_pago();
        } else if (isset($_POST['idproveedor'])) {
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
        $this->proveedores   = new proveedores();
        $this->trans_pagos   = new trans_pagos();
        $this->formaspago    = new formaspago();
        $this->anticiposprov = new anticiposprov();
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

        $this->nom_proveedor = '';
        $this->idproveedor   = '';
        if (isset($_REQUEST['idproveedor2']) && $_REQUEST['idproveedor2'] != '') {
            $proveedor = $this->proveedores->get($_REQUEST['idproveedor2']);
            if ($proveedor) {
                $this->idproveedor   = $proveedor->idproveedor;
                $this->nom_proveedor = $proveedor->identificacion . " - " . $proveedor->razonsocial;
                $this->filtros .= '&idproveedor=' . $this->idproveedor;
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
            $this->resultados = $this->anticiposprov->buscarAnticipos($this->idempresa, $this->query, $this->idproveedor, $this->idformapago, $this->desde, $this->hasta, false, $this->anulados, $this->offset);
        } else {
            $this->cantidad = $this->anticiposprov->buscarAnticipos($this->idempresa, $this->query, $this->idproveedor, $this->idformapago, $this->desde, $this->hasta, false, $this->anulados, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }

    private function consulta_servidor()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error en la consulta');

        $cli = $this->proveedores->get_by_identificacion($this->idempresa, $_REQUEST['identificacion_b']);

        if ($cli) {
            $result = array('error' => 'R', 'msj' => 'El proveedor ya se encuentra registrado, puede crear el Anticipo con el botón Nuevo.');
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

    private function buscar_proveedor()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_proveedores($this->idempresa, $_GET['buscar_proveedor']);

        echo json_encode($result);
        exit;
    }

    private function eliminar_anticipo()
    {
        $anticipo = $this->anticiposprov->get($_GET['delete']);
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

    private function crear_proveedor()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error en la consulta');
        if (!$this->proveedores->get_by_identificacion($this->idempresa, $_POST['identificacion'])) {
            $proveedor                 = new proveedores();
            $proveedor->idempresa      = $this->idempresa;
            $proveedor->identificacion = $_POST['identificacion'];
            $proveedor->tipoid         = $_POST['tipoid'];
            $proveedor->razonsocial    = $_POST['razonsocial'];
            if ($_POST['nombrecomercial'] != '') {
                $proveedor->nombrecomercial = $_POST['nombrecomercial'];
            } else {
                $proveedor->nombrecomercial = $_POST['razonsocial'];
            }
            $proveedor->telefono = $_POST['telefono'];
            if ($_POST['celular'] != '') {
                $proveedor->celular = $_POST['celular'];
            }
            $proveedor->email         = $_POST['email'];
            $proveedor->direccion     = $_POST['direccion'];
            $proveedor->regimen       = $_POST['regimen'];
            $proveedor->obligado      = isset($_POST['obligado']);
            $proveedor->agretencion   = isset($_POST['agretencion']);
            $proveedor->fec_creacion  = date('Y-m-d');
            $proveedor->nick_creacion = $this->user->nick;

            if ($proveedor->save()) {
                $result = array('error' => 'F', 'msj' => "Proveedor creado correctamente.", 'proveedor' => $proveedor);
            } else {
                $result = array('error' => 'T', 'msj' => "No se pudo crear el proveedor, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $result = array('error' => 'T', 'msj' => "El proveedor con identificacion " . $_POST['identificacion'] . " ya se encuentra registrado.");
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
        $result         = array('error' => 'T', 'msj' => 'Proveedor no encontrado.');

        //verifico si existe el proveedor seleccionado
        $proveedor = $this->proveedores->get($_POST['idproveedor']);
        if ($proveedor) {
            if (isset($_POST['num_doc'])) {
                if (trim($_POST['num_doc']) != '') {
                    //valido si el numero de documento no se encuentra ya registrado
                    $numDoc = $this->trans_pagos->getNumDocProveedor($this->idempresa, $_POST['fpselec'], $_POST['num_doc']);
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

            $anticipo              = new \anticiposprov();
            $anticipo->idempresa   = $this->idempresa;
            $anticipo->idproveedor = $proveedor->idproveedor;
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
                //Si se guarda correctamente genero la transaccion en el trans_pagos
                $tpago                 = new \trans_pagos();
                $tpago->idempresa      = $this->idempresa;
                $tpago->idproveedor    = $anticipo->idproveedor;
                $tpago->idanticipoprov = $anticipo->idanticipoprov;
                $tpago->idformapago    = $anticipo->idformapago;
                $tpago->tipo           = 'Anticipo';
                $tpago->fecha_trans    = $anticipo->fecha_trans;
                $tpago->num_doc        = $anticipo->num_doc;
                $tpago->debito         = floatval($anticipo->valor);
                $tpago->esabono        = false;
                $tpago->fec_creacion   = date('Y-m-d');
                $tpago->nick_creacion  = $this->user->nick;
                if (!$tpago->save()) {
                    $result = array('error' => 'T', 'msj' => 'Error al generar el Cobro en el Anticipo.');
                    $anticipo->delete();
                } else {
                    $result = array('error' => 'F', 'msj' => 'Anticipo de Proveedor Generado correctamente.', 'url' => $this->url() . '&imprimir=' . $anticipo->idanticipoprov);
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
                $result = array('error' => 'T', 'msj' => 'Error al generar el Anticipo del proveedor: ' . $proveedor->razonsocial . '.' . $msj);
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
            $mensaje = 'Imprima el comprobante presionando <a href="index.php?page=impresion_compras&imprimir_anticipo=' . $_GET['imprimir'] . '" target="_blank">aquí</a>';
        }

        if ($mensaje != '') {
            $this->new_message($mensaje);
        }
    }

    private function historial()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Historial no encontrado.');

        $anticipo = $this->anticiposprov->get($_POST['historial']);
        if ($anticipo) {
            $historial = $this->trans_pagos->all_by_anticipoprov($this->idempresa, $anticipo->idanticipoprov);
            if ($historial) {
                $result = array('error' => 'F', 'msj' => '', 'historial' => $historial, 'total' => $anticipo->valor);
            }
        } else {
            $result = array('error' => 'T', 'msj' => 'Anticipo de Proveedor no encontrado.');
        }

        echo json_encode($result);
        exit;
    }
}
