<?php
/**
 * Controlador de Administrador -> Empresas
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_cajas extends controller
{
    //Filtros
    public $idempresa;
    //modelos
    public $establecimiento;
    public $usuarios;
    public $cajas;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Cajas', 'Punto de Venta', true, true, false, 'bi bi-bag-check-fill');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        if (isset($_POST['nueva_caja'])) {
            $this->crear_caja();
        } else if (isset($_POST['idcaja'])) {
            $this->editar_caja();
        }
        //Busqueda
        $this->buscar();
    }

    private function init_modelos()
    {
        $this->establecimiento = new establecimiento();
        $this->usuarios        = new users();
        $this->cajas           = new cajas();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
    }

    private function buscar()
    {
        $this->resultados = $this->cajas->all_by_idempresa($this->idempresa);
    }

    private function crear_caja()
    {
        if (!$this->cajas->get_by_idempresa_nombre($this->idempresa, $_POST['nombre'])) {
            $caja                    = new cajas();
            $caja->idempresa         = $this->idempresa;
            $caja->nombre            = $_POST['nombre'];
            $caja->idestablecimiento = $_POST['idestablecimiento'];
            $caja->inicial           = $_POST['inicial'];
            $caja->fec_creacion      = date('Y-m-d');
            $caja->nick_creacion     = $this->user->nick;
            if ($caja->save()) {
                $this->new_message("Caja Creada correctamente.");
            } else {
                $this->new_error_msg("Error al crear la caja.");
            }
        } else {
            $this->new_advice("El nombre: " . $_POST['nombre'] . ", ya se encuentra registrado.");
        }
    }

    private function editar_caja()
    {
        $caja = $this->cajas->get($_POST['idcaja']);
        if ($caja) {
            if ($caja->idempresa == $this->idempresa) {
                $caja->nombre            = $_POST['nombre'];
                $caja->idestablecimiento = $_POST['idestablecimiento'];
                $caja->inicial           = $_POST['inicial'];
                $caja->fec_modificacion  = date('Y-m-d');
                $caja->nick_modificacion = $this->user->nick;
                if ($caja->save()) {
                    $this->new_message("Caja Editada correctamente.");
                } else {
                    $this->new_error_msg("Error al editar la caja.");
                }
            } else {
                $this->new_advice("La caja seleccionada no corresponde a una caja de su empresa. No puede realizar modificaciones.");
            }
        } else {
            $this->new_advice("Caja no encontrada.");
        }
    }
}
