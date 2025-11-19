<?php
/**
 * Controlador de Compras -> Pagos
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_pagos_proveedores extends controller
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
    //variables
    public $resultados;
    public $cantidad;
    public $offset;
    public $filtros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Lista de Pagos', 'Compras', 'Pagos', true, false, 'bi bi-cash-coin');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();
        //Tiene accesos para crear
        $this->access_crear = $this->user->have_access_to('crear_pago');
        //Tiene accesos para imprimir
        $this->impresion = $this->user->have_access_to('impresion_compras');
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        if (isset($_GET['buscar_proveedor'])) {
            $this->buscar_proveedor();
        } else if (isset($_GET['imprimir_pago'])) {
            $this->imprimir_pago();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_pago();
        }

        //Busqueda
        $this->buscar();
        $this->buscar(-1);
    }

    private function init_modelos()
    {
        $this->proveedores = new proveedores();
        $this->trans_pagos = new trans_pagos();
        $this->formaspago  = new formaspago();
        $this->cab_pagos   = new cab_pagos();
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
        if (isset($_REQUEST['idproveedor']) && $_REQUEST['idproveedor'] != '') {
            $proveedor = $this->proveedores->get($_REQUEST['idproveedor']);
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
            $this->resultados = $this->cab_pagos->buscarPagos($this->idempresa, $this->query, $this->idproveedor, $this->idformapago, $this->desde, $this->hasta, false, $this->anulados, $this->offset);
        } else {
            $this->cantidad = $this->cab_pagos->buscarPagos($this->idempresa, $this->query, $this->idproveedor, $this->idformapago, $this->desde, $this->hasta, false, $this->anulados, -1);
            if (!$this->cantidad) {
                $this->cantidad = 0;
            }
        }
    }

    private function buscar_proveedor()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_proveedores($this->idempresa, $_GET['buscar_proveedor']);

        echo json_encode($result);
        exit;
    }

    private function eliminar_pago()
    {
        $pago = $this->cab_pagos->get($_GET['delete']);
        if ($pago) {
            if ($pago->idempresa != $this->idempresa) {
                $this->new_advice('El Pago no es valido para su empresa.');
                return;
            }
            if (!$this->allow_delete) {
                $this->new_advice('El usuario no tiene permiso para eliminar.');
                return;
            }

            if ($pago->delete()) {
                $this->new_message("Pago eliminado correctamente.");
            } else {
                $this->new_error_msg('Error al eliminar el pago.');
                foreach ($pago->get_errors() as $key => $e) {
                    $this->new_advice($e);
                }
            }
        } else {
            $this->new_error_msg('Pago no encontrado.');
        }
    }
}
