<?php
/**
 * Controlador de Ventas -> Nueva.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class crear_venta extends controller
{
    public $idempresa;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Nueva Venta', 'Ventas', false, false, true, 'bi bi-file-earmark-plus');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        if (isset($_REQUEST['tipoid_b'])) {
            $this->consulta_servidor();
        } else if (isset($_POST['identificacion'])) {
            $this->crear_cliente();
        } else if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_POST['idcliente'])) {
            $this->nueva_factura_venta();
        } else if (isset($_REQUEST['iddocumento_b'])) {
            $this->buscar_documento();
        } else if (isset($_REQUEST['buscar_factura'])) {
            $this->buscar_factura();
        } else if (isset($_POST['b_medidor'])) {
            $this->buscar_medidores();
        }
    }

    private function init_modelos()
    {
        $this->clientes         = new clientes();
        $this->facturas_cliente = new facturascli();
        $this->documentos       = new documentos();
        $this->establecimiento  = new establecimiento();
        $this->fp_sri           = new formaspago_sri();
        $this->medidores        = false;
        if (complemento_exists('juntadeagua')) {
            $this->medidores = new medidores_cliente();
        }
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
    }

    private function consulta_servidor()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error en la consulta');

        $cli = $this->clientes->get_by_identificacion($this->idempresa, $_REQUEST['identificacion_b']);

        if ($cli) {
            $result = array('error' => 'R', 'msj' => 'El cliente ya se encuentra registrado, utilice el buscador para registrarlo.');
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
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error en la consulta');
        if (!$this->clientes->get_by_identificacion($this->idempresa, trim($_POST['identificacion']))) {
            $cliente                 = new clientes();
            $cliente->idempresa      = $this->user->idempresa;
            $cliente->identificacion = trim($_POST['identificacion']);
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

    private function buscar_cliente()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_clientes($this->idempresa, $_GET['buscar_cliente']);

        echo json_encode($result);
        exit;
    }

    private function nueva_factura_venta()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No se encuentra el Cliente seleccionado.');
        $cli            = $this->clientes->get($_POST['idcliente']);
        if ($cli) {
            $factura                      = new facturascli();
            $factura->idempresa           = $this->idempresa;
            $factura->idcliente           = $_POST['idcliente'];
            $factura->tipoid              = $cli->tipoid;
            $factura->identificacion      = $cli->identificacion;
            $factura->razonsocial         = $cli->razonsocial;
            $factura->email               = $cli->email;
            $factura->direccion           = $cli->direccion;
            $factura->regimen_empresa     = $this->empresa->regimen;
            $factura->obligado_empresa    = $this->empresa->obligado;
            $factura->agretencion_empresa = $this->empresa->agretencion;
            $factura->iddocumento         = $_POST['iddocumento'];
            $factura->coddocumento        = $_POST['coddocumento'];
            $factura->idestablecimiento   = $_POST['idestablecimiento'];
            $factura->fec_emision         = $_POST['fec_emision'];
            $factura->hora_emision        = date('H:i:s');
            $factura->fec_registro        = $_POST['fec_registro'];
            $factura->diascredito         = $_POST['diascredito'];
            if ($this->empresa->activafacelec) {
                $factura->tipoemision = 'E';
            }
            if ($_POST['observaciones'] != '') {
                $factura->observaciones = $_POST['observaciones'];
            }
            if (isset($_POST['idmedidor'])) {
                $factura->idmedidor = $_POST['idmedidor'];
            }
            if (isset($_POST['idfpsri'])) {
                $factura->idfpsri = $_POST['idfpsri'];
            }
            $factura->fec_creacion  = date('Y-m-d');
            $factura->nick_creacion = $this->user->nick;

            if (!$this->empresa->activafacelec && $this->empresa->regimen != 'RP') {
                //otros documentos
                $numero_documento          = str_pad($_POST['establecimiento'], 3, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['ptoemision'], 3, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['nrodocumento'], 9, "0", STR_PAD_LEFT);
                $factura->numero_documento = $numero_documento;
                $factura->nro_autorizacion = $_POST['nro_autorizacion'];
            }

            //Notas de Debito y Notas de Credito
            if ($factura->coddocumento == '04' || $factura->coddocumento == '05') {
                if (isset($_POST['nofactura'])) {
                    //guardo los datos si el documento modificado no existe en el sistema
                    //Genero el numero de documento afectado
                    $numero_documento_mod = str_pad($_POST['doc_estab'], 3, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['doc_pto'], 3, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['doc_secuen'], 9, "0", STR_PAD_LEFT);
                    //Almaceno el numero de documento
                    $factura->numero_documento_mod = $numero_documento_mod;
                    $factura->nro_autorizacion_mod = $_POST['doc_nroaut'];
                    $factura->fec_emision_mod      = $_POST['doc_fecemi'];
                    $factura->coddocumento_mod     = '01';
                    $documento                     = $this->documentos->get_by_codigo($factura->coddocumento_mod);
                    if ($documento) {
                        $factura->iddocumento_mod = $documento->iddocumento;
                    }
                } else {
                    //Busco los datos de la factura utilizada para almacenar los datos
                    $factura_mod = $this->facturas_cliente->get($_POST['idfactura_mod']);
                    if ($factura_mod) {
                        $factura->idfactura_mod        = $factura_mod->idfacturacli;
                        $factura->numero_documento_mod = $factura_mod->numero_documento;
                        $factura->nro_autorizacion_mod = $factura_mod->nro_autorizacion;
                        $factura->fec_emision_mod      = $factura_mod->fec_emision;
                        $factura->coddocumento_mod     = $factura_mod->coddocumento;
                        $factura->iddocumento_mod      = $factura_mod->iddocumento;
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Documento Modificado no encontrado.');
                        echo json_encode($result);
                        exit;
                    }
                }
            }

            if ($factura->save()) {
                $result = array('error' => 'F', 'msj' => '', 'url' => $factura->url());
            } else {
                $errores = '';
                foreach ($factura->get_errors() as $key => $error) {
                    if ($errores != '') {
                        $errores .= ' , ' . $error;
                    } else {
                        $errores .= $error;
                    }
                }
                if ($errores != '') {
                    $result = array('error' => 'T', 'msj' => 'No se pudo crear la cabecera de la factura. Error: ' . $errores);
                } else {
                    $result = array('error' => 'T', 'msj' => 'No se pudo crear la cabecera de la factura, verifique los datos y vuelva a intentarlo.');
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_documento()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Documento no encontrado.');

        $doc = $this->documentos->get($_REQUEST['iddocumento_b']);

        if ($doc) {
            $result = array('error' => 'F', 'msj' => '', 'documento' => $doc->codigo);
        }

        echo json_encode($result);
        exit;
    }

    private function buscar_factura()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_factura_cli($this->idempresa, $_GET['buscar_factura'], $_GET['idcliente']);

        echo json_encode($result);
        exit;
    }

    private function buscar_medidores()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No se encuentran medidores del Cliente Seleccionado.');
        $medidores      = $this->medidores->all_by_idcliente($_POST['b_medidor'], true);
        if ($medidores) {
            $result = array('error' => 'F', 'msj' => '', 'medidores' => $medidores);
        }
        echo json_encode($result);
        exit;
    }
}
