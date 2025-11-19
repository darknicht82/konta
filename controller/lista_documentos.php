<?php
/**
 * Controlador de Inventario -> Articulos.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_documentos extends controller
{
    //Filtros
    public $query;
    public $b_grupo;
    public $b_marca;
    public $b_tipo;
    public $idempresa;
    //modelos
    public $documentos;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Documentos', 'Configuración', true, true, false, 'bi bi-filetype-doc');
    }

    protected function private_core()
    {
        $this->documentos = new documentos();
        if (isset($_POST['iddocumento'])) {
            $this->modificar_documento();
        } else if (isset($_POST['codigo'])) {
            $this->crear_documento();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_documento();
        }

        $this->buscar();
    }

    private function crear_documento()
    {
        if (!$this->documentos->get_by_codigo($_POST['codigo'])) {
            $documento = new documentos();
            $documento->codigo = $_POST['codigo'];
            $documento->nombre = $_POST['nombre'];
            $documento->fec_creacion = date('Y-m-d');
            $documento->nick_creacion = $this->user->nick;

            if ($documento->save()) {
                $this->new_message("Documento creado correctamente.");
            } else {
                $this->new_error_msg("No se pudo crear el documento, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El documento con codigo ".$_POST['codigo']." ya se encuentra registrado.");
        }
    }

    private function modificar_documento()
    {
        $documento = $this->documentos->get($_POST['iddocumento']);
        if ($documento) {
            $documento->codigo = $_POST['codigo'];
            $documento->nombre = $_POST['nombre'];
            $documento->fec_modificacion = date('Y-m-d');
            $documento->nick_modificacion = $this->user->nick;

            if ($documento->save()) {
                $this->new_message("Documento modificado correctamente.");
            } else {
                $this->new_error_msg("No se pudo modificar el documento, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El documento no se encuentra registrado.");
        }
    }

    private function eliminar_documento()
    {
        $documento = $this->documentos->get($_GET['delete']);
        if ($documento) {
            if ($documento->delete()) {
                $this->new_message("Documento eliminado correctamente.");
            } else {
                $this->new_error_msg("No se puede eliminar el documento, debe estar utilizado en una transacción de compra.");
            }
        } else {
            $this->new_advice("Error al eliminar, el documento no se encuentra registrado o ya fue eliminado.");
        }
    }

    private function buscar()
    {
        $this->resultados = $this->documentos->all();
    }
}