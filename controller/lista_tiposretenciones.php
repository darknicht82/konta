<?php
/**
 * Controlador de Inventario -> Articulos.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_tiposretenciones extends controller
{
    //Filtros
    public $query;
    public $idempresa;
    //modelos
    public $tiposretenciones;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Tipos de Retenciones', 'Configuración', true, true, false, 'bi bi-clipboard-data');
    }

    protected function private_core()
    {
        $this->tiposretenciones = new tiposretenciones();
        if (isset($_POST['idtiporetencion'])) {
            $this->modificar_tiporetencion();
        } else if (isset($_POST['nombre'])) {
            $this->crear_tiporetencion();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_tiporetencion();
        }

        $this->buscar();
    }

    private function crear_tiporetencion()
    {
        $tiporetencion                = new tiposretenciones();
        $tiporetencion->idempresa     = $this->empresa->idempresa;
        $tiporetencion->codigobase    = $_POST['codigobase'];
        $tiporetencion->codigo        = $_POST['codigo'];
        $tiporetencion->especie       = $_POST['especie'];
        $tiporetencion->nombre        = $_POST['nombre'];
        $tiporetencion->porcentaje    = floatval($_POST['porcentaje']);
        $tiporetencion->esventa       = isset($_POST['esventa']);
        $tiporetencion->escompra      = isset($_POST['escompra']);
        $tiporetencion->fec_creacion  = date('Y-m-d');
        $tiporetencion->nick_creacion = $this->user->nick;
        if ($tiporetencion->save()) {
            $this->new_message("Tipo de Retención creada correctamente.");
        } else {
            $this->new_error_msg("No se pudo crear el Tipo de Retención, verifique los datos y vuelva a intentarlo.");
        }
    }

    private function modificar_tiporetencion()
    {
        $tiporetencion = $this->tiposretenciones->get($_POST['idtiporetencion']);
        if ($tiporetencion) {
            $tiporetencion->codigobase        = $_POST['codigobase'];
            $tiporetencion->codigo            = $_POST['codigo'];
            $tiporetencion->especie           = $_POST['especie'];
            $tiporetencion->nombre            = $_POST['nombre'];
            $tiporetencion->porcentaje        = floatval($_POST['porcentaje']);
            $tiporetencion->esventa           = isset($_POST['esventa']);
            $tiporetencion->escompra          = isset($_POST['escompra']);
            $tiporetencion->fec_modificacion  = date('Y-m-d');
            $tiporetencion->nick_modificacion = $this->user->nick;

            if ($tiporetencion->save()) {
                $this->new_message("Tipo de Retención modificada correctamente.");
            } else {
                $this->new_error_msg("No se pudo modificar el Tipo de Retención, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El Tipo de Retención no se encuentra registrado.");
        }
    }

    private function eliminar_tiporetencion()
    {
        $tiporetencion = $this->tiposretenciones->get($_GET['delete']);
        if ($tiporetencion) {
            if ($tiporetencion->delete()) {
                $this->new_message("Tipo de Retención eliminada correctamente.");
            } else {
                $this->new_error_msg("No se puede eliminar el Tipo de Retención, debe estar utilizado en una transacción de compra o venta.");
            }
        } else {
            $this->new_advice("Error al eliminar, el Tipo de Retención no se encuentra registrada o ya fue eliminada.");
        }
    }

    private function buscar()
    {
        $this->resultados = buscar_tiposretencion('', $this->empresa->idempresa);
    }
}
