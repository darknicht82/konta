<?php
/**
 * Controlador de Inventario -> Articulos.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_sustentossri extends controller
{
    //Filtros
    public $query;
    //modelos
    public $sustentos;
    public $documentos;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Sustentos SRI', 'Configuración', true, true, false, 'bi bi-bar-chart-steps');
    }

    protected function private_core()
    {
        $this->sustentos = new sustentos();
        $this->documentos = new documentos();
        if (isset($_POST['idsustento'])) {
            $this->modificar_sustento();
        } else if (isset($_POST['codigo'])) {
            $this->crear_sustento();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_sustento();
        }

        $this->buscar();
    }

    private function crear_sustento()
    {
        if (!$this->sustentos->get_by_codigo($_POST['codigo'])) {
            $sustento = new sustentos();
            $sustento->codigo = $_POST['codigo'];
            $sustento->nombre = $_POST['nombre'];
            $sustento->documentos = implode(",", $_POST['documentos']);
            $sustento->fec_creacion = date('Y-m-d');
            $sustento->nick_creacion = $this->user->nick;

            if ($sustento->save()) {
                $this->new_message("Sustento SRI creado correctamente.");
            } else {
                $this->new_error_msg("No se pudo crear el sustento, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El sustento con codigo ".$_POST['codigo']." ya se encuentra registrado.");
        }
    }

    private function modificar_sustento()
    {
        $sustento = $this->sustentos->get($_POST['idsustento']);
        if ($sustento) {
            $sustento->codigo = $_POST['codigo'];
            $sustento->nombre = $_POST['nombre'];
            $sustento->documentos = implode(",", $_POST['documentos']);
            $sustento->fec_modificacion = date('Y-m-d');
            $sustento->nick_modificacion = $this->user->nick;

            if ($sustento->save()) {
                $this->new_message("Sustento SRI modificado correctamente.");
            } else {
                $this->new_error_msg("No se pudo modificar el sustento, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El sustento no se encuentra registrado.");
        }
    }

    private function eliminar_sustento()
    {
        $sustento = $this->sustentos->get($_GET['delete']);
        if ($sustento) {
            if ($sustento->delete()) {
                $this->new_message("Sustento SRI eliminado correctamente.");
            } else {
                $this->new_error_msg("No se puede eliminar el sustento, debe estar utilizado en una transacción de compra.");
            }
        } else {
            $this->new_advice("Error al eliminar, el sustento no se encuentra registrado o ya fue eliminado.");
        }
    }

    private function buscar()
    {
        $this->resultados = $this->sustentos->all();
    }
    
}