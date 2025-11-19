<?php
/**
 * Controlador de Guias -> Nueva.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class crear_guia extends controller
{
    public $idempresa;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Nueva Guia', 'Guias', false, false, true, 'bi bi-file-earmark-plus');
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
            $this->nueva_guia();
        } else if (isset($_REQUEST['iddocumento_b'])) {
            $this->buscar_documento();
        } else if (isset($_REQUEST['buscar_factura'])) {
            $this->buscar_factura();
        } else if (isset($_GET['buscar_trans'])) {
            $this->buscar_transportista();
        } else if (isset($_POST['trasnp'])) {
            $this->get_transportista();
        }
    }

    private function init_modelos()
    {
        $this->clientes        = new clientes();
        $this->guias_cliente   = new guiascli();
        $this->documentos      = new documentos();
        $this->establecimiento = new establecimiento();
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
        if (!$this->clientes->get_by_identificacion($this->idempresa, $_POST['identificacion'])) {
            $cliente                 = new clientes();
            $cliente->idempresa      = $this->user->idempresa;
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

    private function buscar_cliente()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_clientes($this->idempresa, $_GET['buscar_cliente']);

        echo json_encode($result);
        exit;
    }

    private function nueva_guia()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No se encuentra el Cliente seleccionado.');
        $cli            = $this->clientes->get($_POST['idcliente']);
        if ($cli) {
            $guia                      = new guiascli();
            $guia->idempresa           = $this->idempresa;
            $guia->idcliente           = $_POST['idcliente'];
            $guia->tipoid              = $cli->tipoid;
            $guia->identificacion      = $cli->identificacion;
            $guia->razonsocial         = $cli->razonsocial;
            $guia->email               = $cli->email;
            $guia->direccion           = $cli->direccion;
            $guia->regimen_empresa     = $this->empresa->regimen;
            $guia->obligado_empresa    = $this->empresa->obligado;
            $guia->agretencion_empresa = $this->empresa->agretencion;
            $guia->fec_emision         = $_POST['fec_emision'];
            $guia->hora_emision        = date('H:i:s');
            $guia->fec_finalizacion    = $_POST['fec_finalizacion'];
            $guia->fec_registro        = date('Y-m-d');
            $guia->idestablecimiento   = $_POST['idestablecimiento'];
            if (!$this->empresa->activafacelec) {
                //otros documentos
                $numero_documento       = str_pad($_POST['establecimiento'], 3, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['ptoemision'], 3, "0", STR_PAD_LEFT) . '-' . str_pad($_POST['nrodocumento'], 9, "0", STR_PAD_LEFT);
                $guia->numero_documento = $numero_documento;
                $guia->nro_autorizacion = $_POST['nro_autorizacion'];
            }
            //Transporte
            $guia->motivo = $_POST['motivo'];
            if ($_POST['codestablecimiento'] != '') {
                $guia->codestablecimiento = $_POST['codestablecimiento'];
            }
            if ($_POST['ruta'] != '') {
                $guia->ruta = $_POST['ruta'];
            }
            $guia->tipoid_trans         = $_POST['tipoid_trans'];
            $guia->identificacion_trans = $_POST['identificacion_trans'];
            $guia->razonsocial_trans    = $_POST['razonsocial_trans'];
            $guia->placa                = $_POST['placa'];
            $guia->dirpartida           = $_POST['dirpartida'];

            if (isset($_POST['idfactura_mod'])) {
                if ($_POST['idfactura_mod'] != "") {
                    $facturascli = new facturascli();
                    //Busco los datos de la factura utilizada para almacenar los datos
                    $factura_mod = $facturascli->get($_POST['idfactura_mod']);
                    if ($factura_mod) {
                        $guia->idfactura_mod        = $factura_mod->idfacturacli;
                        $guia->numero_documento_mod = $factura_mod->numero_documento;
                        $guia->nro_autorizacion_mod = $factura_mod->nro_autorizacion;
                        $guia->fec_emision_mod      = $factura_mod->fec_emision;
                        $guia->coddocumento_mod     = $factura_mod->coddocumento;
                        $guia->iddocumento_mod      = $factura_mod->iddocumento;
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Documento Modificado no encontrado.');
                        echo json_encode($result);
                        exit;
                    }
                }
            }

            $guia->tipo_guia     = $_POST['tipo_guia'];
            $guia->fec_creacion  = date('Y-m-d');
            $guia->nick_creacion = $this->user->nick;
            if ($_POST['observaciones'] != '') {
                $guia->observaciones = $_POST['observaciones'];
            }

            if ($guia->save()) {
                $result = array('error' => 'F', 'msj' => '', 'url' => $guia->url());
            } else {
                $errores = '';
                foreach ($guia->get_errors() as $key => $error) {
                    if ($errores != '') {
                        $errores .= ' , '.$error;
                    } else {
                        $errores .= $error;
                    }
                }
                if ($errores != '') {
                    $result = array('error' => 'T', 'msj' => 'No se pudo crear la cabecera de la guia. Error: '.$errores);
                } else {
                    $result = array('error' => 'T', 'msj' => 'No se pudo crear la cabecera de la guia, verifique los datos y vuelva a intentarlo.');
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

    private function buscar_transportista()
    {
        $this->template = false;
        $response       = array();
        $transportistas = $this->guias_cliente->getTransportistas($_GET['buscar_trans']);
        foreach ($transportistas as $key => $trans) {
            $response[] = array("value" => $trans['identificacion_trans'], "label" => $trans['razonsocial_trans'] . " - " . $trans['placa']);
        }
        //$response[] = array("value" => '1722804539', "label" => "Jonathan Guamba - PBV7814");
        echo json_encode($response);
        exit;
    }

    private function get_transportista()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Transportista No encontrado.');
        $transportista  = $this->guias_cliente->get_transp($_POST['trasnp']);
        if ($transportista) {
            $result = array('error' => 'F', 'msj' => '', 'trans' => $transportista);
        }
        echo json_encode($result);
        exit;
    }
}
