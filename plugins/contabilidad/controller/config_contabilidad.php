<?php
/**
 * Controlador de Contabilidad -> Configuracion.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class config_contabilidad extends controller
{
    //Filtros
    public $idempresa;
    //modelos
    public $plancuentas;
    public $ejercicios;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Configuraciones', 'Contabilidad', true, true, false, 'bi bi-gear-fill');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        if (isset($_POST['idsub'])) {
            $this->guardar_banco();
        } else if (isset($_POST['idsubch'])) {
            $this->guardar_caja_chica();
        } else if (isset($_POST['idsubccli'])) {
            $this->guardar_ctacliente();
        } else if (isset($_POST['idsubcantprov'])) {
            $this->guardar_ctaantprov();
        } else if (isset($_POST['idsubcprov'])) {
            $this->guardar_ctaproveedor();
        } else if (isset($_POST['idsubcantcli'])) {
            $this->guardar_ctaantcli();
        } else if (isset($_POST['idsubcresul'])) {
            $this->guardar_ctaresultado();
        } else if (isset($_POST['idsubcventa'])) {
            $this->guardar_ctaventas();
        } else if (isset($_POST['idsubcdtoventa'])) {
            $this->guardar_ctadtoventas();
        } else if (isset($_POST['idsubcdevol'])) {
            $this->guardar_ctadevolventas();
        } else if (isset($_POST['idsubccompra'])) {
            $this->guardar_ctacompras();
        } else if (isset($_POST['idsubccostos'])) {
            $this->guardar_ctacostos();
        } else if (isset($_POST['idsubingresos'])) {
            $this->guardar_ingresos();
        } else if (isset($_POST['idsubingresosnop'])) {
            $this->guardar_ingresosnop();
        } else if (isset($_POST['idsubcostos'])) {
            $this->guardar_costos();
        } else if (isset($_POST['idsubgastos'])) {
            $this->guardar_gastos();
        } else if (isset($_POST['idsubegresosnop'])) {
            $this->guardar_egresosnop();
        } else if (isset($_POST['idsubctanotaventa'])) {
            $this->guardar_ctanotaventa();
        } else if (isset($_POST['idsubctanotacostos'])) {
            $this->guardar_ctanotacostos();
        }

        $this->buscar();
    }

    private function init_modelos()
    {
        $this->plancuentas = new plancuentas();
        $this->ejercicios  = new ejercicios();
    }

    private function init_filter()
    {
        $this->idempresa   = $this->empresa->idempresa;
        $this->idejercicio = '';
        if (isset($_REQUEST['idejercicio'])) {
            $this->idejercicio = $_REQUEST['idejercicio'];
        }
    }

    private function buscar()
    {
        if ($this->idejercicio != '') {
            $this->resultados = $this->plancuentas->allPlanCuentas($this->idempresa, $this->idejercicio);
        } else {
            $this->resultados = array();
        }
    }

    private function guardar_banco()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsub']);
        if ($subcuenta) {
            $subcuenta->esbanco           = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar el Banco.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_caja_chica()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubch']);
        if ($subcuenta) {
            $subcuenta->escajach          = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Caja Chica.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctacliente()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubccli']);
        if ($subcuenta) {
            $subcuenta->ctacliente        = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubccli'], 'ctacliente');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Clientes.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctaantprov()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubcantprov']);
        if ($subcuenta) {
            $subcuenta->ctaantprov        = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubcantprov'], 'ctaantprov');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Anticipo a Proveedores.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctaproveedor()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubcprov']);
        if ($subcuenta) {
            $subcuenta->ctaproveedor      = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubcprov'], 'ctaproveedor');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Proveedores.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctaantcli()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubcantcli']);
        if ($subcuenta) {
            $subcuenta->ctaantcli         = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubcantcli'], 'ctaantcli');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Anticipo de Clientes.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctaresultado()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubcresul']);
        if ($subcuenta) {
            $subcuenta->ctaresultado      = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubcresul'], 'ctaresultado');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Resultado.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctaventas()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubcventa']);
        if ($subcuenta) {
            $subcuenta->ctaventa          = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubcventa'], 'ctaventa');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Ventas.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctadtoventas()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubcdtoventa']);
        if ($subcuenta) {
            $subcuenta->ctadtoventa       = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubcdtoventa'], 'ctadtoventa');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Descuento en Ventas.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctadevolventas()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubcdevol']);
        if ($subcuenta) {
            $subcuenta->ctadevolventa     = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubcdevol'], 'ctadevolventa');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Devolución en Ventas.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctacompras()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubccompra']);
        if ($subcuenta) {
            $subcuenta->ctacompra         = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubccompra'], 'ctacompra');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Compra.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctacostos()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubccostos']);
        if ($subcuenta) {
            $subcuenta->ctacostos         = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubccostos'], 'ctacostos');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Costos.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ingresos()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubingresos']);
        if ($subcuenta) {
            $subcuenta->ingresos          = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubingresos'], 'ingresos');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar el Grupo de Ingresos.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ingresosnop()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubingresosnop']);
        if ($subcuenta) {
            $subcuenta->ingresosnop       = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubingresosnop'], 'ingresosnop');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar el Grupo de Ingresos No Operacionales.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_costos()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubcostos']);
        if ($subcuenta) {
            $subcuenta->costos            = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubcostos'], 'costos');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar el Grupo de Costos.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_gastos()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubgastos']);
        if ($subcuenta) {
            $subcuenta->gastos            = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubgastos'], 'gastos');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar el Grupo de Gastos.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_egresosnop()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubegresosnop']);
        if ($subcuenta) {
            $subcuenta->egresosnop        = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubegresosnop'], 'egresosnop');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar el Grupo de Egresos no Operacionales.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctanotaventa()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubctanotaventa']);
        if ($subcuenta) {
            $subcuenta->ctanotaventa      = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubctanotaventa'], 'ctanotaventa');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Ventas (Nota de Venta).');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function guardar_ctanotacostos()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Subcuenta no Encontrada.');
        $subcuenta      = $this->plancuentas->get($_POST['idsubctanotacostos']);
        if ($subcuenta) {
            $subcuenta->ctanotacostos     = $this->plancuentas->str2bool($_POST['valorp']);
            $subcuenta->fec_modificacion  = date('Y-m-d');
            $subcuenta->nick_modificacion = $this->user->nick;

            if ($subcuenta->save()) {
                $this->plancuentas->desactivarDefecto($this->idempresa, $_POST['idejerciciop'], $_POST['idsubctanotacostos'], 'ctanotacostos');
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar la Cuenta por Defecto de Costos de Ventas (Nota de Venta).');
            }
        }
        echo json_encode($result);
        exit;
    }
}
