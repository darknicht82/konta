<?php
/**
 * Controlador de Compras -> Nueva.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class crear_compra extends controller
{
    public $idempresa;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Nueva Compra', 'Compras', false, false, true, 'bi bi-file-earmark-plus');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        if (isset($_REQUEST['tipoid_b'])) {
            $this->consulta_servidor();
        } else if (isset($_POST['identificacion'])) {
            $this->crear_proveedor();
        } else if (isset($_GET['buscar_proveedor'])) {
            $this->buscar_proveedor();
        } else if (isset($_POST['idproveedor'])) {
            $this->nueva_factura_compra();
        } else if (isset($_REQUEST['iddocumento_b'])) {
            $this->buscar_documento();
        } else if (isset($_REQUEST['buscar_factura'])) {
            $this->buscar_factura();
        } else if (isset($_REQUEST['idsustento_b'])) {
            $this->buscar_documentos();
        }
    }

    private function init_modelos()
    {
        $this->proveedores        = new proveedores();
        $this->facturas_proveedor = new facturasprov();
        $this->documentos         = new documentos();
        $this->establecimiento    = new establecimiento();
        $this->sustentos          = new sustentos();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
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

    private function crear_proveedor()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error en la consulta');
        if (!$this->proveedores->get_by_identificacion($this->idempresa, trim($_POST['identificacion']))) {
            $proveedor                 = new proveedores();
            $proveedor->idempresa      = $this->user->idempresa;
            $proveedor->identificacion = trim($_POST['identificacion']);
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

    private function buscar_proveedor()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_proveedores($this->idempresa, $_GET['buscar_proveedor']);

        echo json_encode($result);
        exit;
    }

    private function nueva_factura_compra()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No se encuentra el Proveedor seleccionado.');

        $prov = $this->proveedores->get($_POST['idproveedor']);
        if ($prov) {
            $factura                      = new facturasprov();
            $factura->idempresa           = $this->idempresa;
            $factura->idproveedor         = $_POST['idproveedor'];
            $factura->tipoid              = $prov->tipoid;
            $factura->identificacion      = $prov->identificacion;
            $factura->razonsocial         = $prov->razonsocial;
            $factura->email               = $prov->email;
            $factura->direccion           = $prov->direccion;
            $factura->regimen_empresa     = $this->empresa->regimen;
            $factura->obligado_empresa    = $this->empresa->obligado;
            $factura->agretencion_empresa = $this->empresa->agretencion;
            $factura->coddocumento        = $_POST['coddocumento'];
            $factura->iddocumento         = $_POST['iddocumento'];
            $factura->idsustento          = $_POST['idsustento'];
            $factura->idestablecimiento   = $_POST['idestablecimiento'];
            $factura->fec_emision         = $_POST['fec_emision'];
            $factura->hora_emision        = date('H:i:s');
            $factura->fec_caducidad       = $_POST['fec_caducidad'];
            $factura->fec_registro        = $_POST['fec_emision'];
            $factura->diascredito         = $_POST['diascredito'];
            //Si es liquidacion no guardamos el numero de documento se debe generar en el modelo
            if ($factura->coddocumento == '03' && $this->empresa->activafacelec) {
                //Si es liquidacion de compra y esta activa la facturacion electronica no hago nada
            } else {
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
                    $factura->fecdoc_modificado    = $_POST['doc_fecemi'];
                    $factura->coddocumento_mod     = '01';
                    $documento                     = $this->documentos->get_by_codigo($factura->coddocumento_mod);
                    if ($documento) {
                        $factura->iddocumento_mod = $documento->iddocumento;
                    }
                } else {
                    //Busco los datos de la factura utilizada para almacenar los datos
                    $factura_mod = $this->facturas_proveedor->get($_POST['idfactura_mod']);
                    if ($factura_mod) {
                        $factura->idfactura_mod        = $factura_mod->idfacturaprov;
                        $factura->numero_documento_mod = $factura_mod->numero_documento;
                        $factura->nro_autorizacion_mod = $factura_mod->nro_autorizacion;
                        $factura->fecdoc_modificado    = $factura_mod->fec_emision;
                        $factura->coddocumento_mod     = $factura_mod->coddocumento;
                        $factura->iddocumento_mod      = $factura_mod->iddocumento;
                    } else {
                        $result         = array('error' => 'T', 'msj' => 'Documento Modificado no encontrado.');
                        echo json_encode($result);
                        exit;
                    }
                }
            }
            $factura->fec_creacion  = date('Y-m-d');
            $factura->nick_creacion = $this->user->nick;
            if ($_POST['observaciones'] != '') {
                $factura->observaciones = $_POST['observaciones'];
            }

            if ($factura->save()) {
                $result = array('error' => 'F', 'msj' => '', 'url' => $factura->url());
            } else {
                $errores = '';
                foreach ($factura->get_errors() as $key => $error) {
                    if ($errores != '') {
                        $errores .= ' , '.$error;
                    } else {
                        $errores .= $error;
                    }
                }
                if ($errores != '') {
                    $result = array('error' => 'T', 'msj' => 'No se pudo crear la cabecera de la factura. Error: '.$errores);
                } else {
                    $result = array('error' => 'T', 'msj' => 'No se pudo crear la cabecera de la factura, verifique los datos y vuelva a intentarlo.');
                }
            }
        }
        echo json_encode($result);
        exit;
    }

    private function buscar_factura()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_factura_prov($this->idempresa, $_GET['buscar_factura'], $_GET['idproveedor']);

        echo json_encode($result);
        exit;
    }

    private function buscar_documentos()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Documentos no encontrados.');

        $sustento = $this->sustentos->get($_REQUEST['idsustento_b']);
        if ($sustento) {
            $docs = $this->documentos->all_desde_sustento($sustento->documentos);
            if ($docs) {
                $result = array('error' => 'F', 'msj' => '', 'documentos' => $docs);
            }
        } else {
            $result = array('error' => 'T', 'msj' => 'Sustento no encontrado.');
        }

        echo json_encode($result);
        exit;
    }
}
