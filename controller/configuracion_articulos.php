<?php
/**
 * Controlador de Configuracion -> Articulos.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class configuracion_articulos extends controller
{
    //variables
    public $allow_delete;
    public $allow_modify;
    //modelos
    public $marcas;
    public $grupos;

    //Resultados
    public $resultados_marcas;
    public $resultados_grupos;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Artículos', 'Configuración', true, true, false, 'bi bi-collection');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        if (isset($_POST['idgrupo'])) {
            $this->tratar_grupo();
        } else if (isset($_POST['idmarca'])) {
            $this->tratar_marca();
        } else if (isset($_GET['del_grupo'])) {
            $this->eliminar_grupo();
        } else if (isset($_GET['del_marca'])) {
            $this->eliminar_marca();
        } else if (isset($_POST['idmenu'])) {
            $this->actualizar_menu();
        } else if (isset($_POST['imagengr'])) {
            $this->cargar_imagen_grupo();
        } else if (isset($_GET['delete_imagen_gr'])) {
            $this->borrar_imagen_grupo();
        } else if (isset($_POST['imagenmar'])) {
            $this->cargar_imagen_marca();
        } else if (isset($_GET['delete_imagen_mar'])) {
            $this->borrar_imagen_marca();
        }

        $this->cargar_resultados();
    }

    private function init_modelos()
    {
        $this->marcas     = new marcas();
        $this->grupos     = new grupos();
        $this->parametros = new \parametrizacion();
    }

    private function init_filter()
    {
        $this->idempresa         = $this->empresa->idempresa;
        $this->resultados_marcas = array();
        $this->resultados_grupos = array();
        $this->mosmenu           = 0;
        $menuparam               = $this->parametros->all_by_codigo($this->idempresa, 'mosmenu');
        if ($menuparam) {
            $this->mosmenu = $menuparam->valor;
        }
    }

    private function tratar_grupo()
    {
        $idpadre = null;
        if (isset($_POST['essubgrupo'])) {
            $idpadre = $_POST['idpadre'];
        }
        $grupo = $this->grupos->get($_POST['idgrupo']);
        if (!$grupo) {
            $grupo = $this->grupos->get_by_nombre($this->idempresa, $_POST['nombre'], $idpadre);
            if (!$grupo) {
                $grupo                = new grupos();
                $grupo->idempresa     = $this->user->idempresa;
                $grupo->fec_creacion  = date('Y-m-d');
                $grupo->nick_creacion = $this->user->nick;
            } else {
                $grupo->fec_modificacion  = date('Y-m-d');
                $grupo->nick_modificacion = $this->user->nick;
            }
        } else {
            $grupo->fec_modificacion  = date('Y-m-d');
            $grupo->nick_modificacion = $this->user->nick;
        }

        $grupo->idpadre = $idpadre;
        $grupo->nombre  = $_POST['nombre'];

        if ($grupo->save()) {
            $this->new_message("Datos de Grupo guardados correctamente.");
        } else {
            $this->new_error_msg("Error al guardar los datos del Grupo.");
        }
    }

    private function tratar_marca()
    {
        $idpadre = null;
        if (isset($_POST['essubmarca'])) {
            $idpadre = $_POST['idpadre'];
        }
        $marca = $this->marcas->get($_POST['idmarca']);
        if (!$marca) {
            $marca = $this->marcas->get_by_nombre($this->idempresa, $_POST['nombre'], $idpadre);
            if (!$marca) {
                $marca                = new marcas();
                $marca->idempresa     = $this->user->idempresa;
                $marca->fec_creacion  = date('Y-m-d');
                $marca->nick_creacion = $this->user->nick;
            } else {
                $marca->fec_modificacion  = date('Y-m-d');
                $marca->nick_modificacion = $this->user->nick;
            }
        } else {
            $marca->fec_modificacion  = date('Y-m-d');
            $marca->nick_modificacion = $this->user->nick;
        }

        $marca->idpadre = $idpadre;
        $marca->nombre  = $_POST['nombre'];

        if ($marca->save()) {
            $this->new_message("Datos de Marca guardados correctamente.");
        } else {
            $this->new_error_msg("Error al guardar los datos de la Marca.");
        }
    }

    private function eliminar_grupo()
    {
        $grupo = $this->grupos->get($_GET['del_grupo']);
        if ($grupo) {
            if ($grupo->delete()) {
                $this->new_message("Grupo eliminado correctamente.");
            } else {
                $this->new_error_msg("Se generó un error al eliminar el Grupo.");
            }
        } else {
            $this->new_advice("Grupo no encontrado, posiblemente ya fue eliminado.");
        }
    }

    private function eliminar_marca()
    {
        $marca = $this->marcas->get($_GET['del_marca']);
        if ($marca) {
            if ($marca->delete()) {
                $this->new_message("Marca eliminada correctamente.");
            } else {
                $this->new_error_msg("Se generó un error al eliminar la Marca.");
            }
        } else {
            $this->new_advice("Marca no encontrada, posiblemente ya fue eliminada.");
        }
    }

    private function cargar_resultados()
    {
        $this->resultados_grupos = $this->grupos->mostrarGrupos($this->idempresa);
        $this->resultados_marcas = $this->marcas->mostrarMarcas($this->idempresa);
    }

    private function actualizar_menu()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Grupo no Encontrado.');
        $grupo          = $this->grupos->get($_POST['idmenu']);
        if ($grupo) {
            $grupo->menu              = $this->grupos->str2bool($_POST['valorp']);
            $grupo->fec_modificacion  = date('Y-m-d');
            $grupo->nick_modificacion = $this->user->nick;

            if ($grupo->save()) {
                $result = array('error' => 'F', 'msj' => '');
            } else {
                $result = array('error' => 'T', 'msj' => 'Error al almacenar el menu.');
            }
        }
        echo json_encode($result);
        exit;
    }

    private function cargar_imagen_grupo()
    {
        $grupo = $this->grupos->get($_POST['imagengr']);
        if ($grupo) {
            //carga de imagen en grupos
            if (is_uploaded_file($_FILES['imagen_grupo']['tmp_name'])) {
                if (!file_exists(JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/grupos")) {
                    @mkdir(JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/grupos", 0777, true);
                }

                $rutalogo = "";
                if (substr(strtolower($_FILES['imagen_grupo']['name']), -3) == 'png') {
                    $rutalogo = JG_MYDOCS . "datosEmpresas/" . $this->empresa->idempresa . "/grupos/" . $grupo->idgrupo . ".png";
                } else if (substr(strtolower($_FILES['imagen_grupo']['name']), -3) == 'jpg') {
                    $rutalogo = JG_MYDOCS . "datosEmpresas/" . $this->empresa->idempresa . "/grupos/" . $grupo->idgrupo . ".jpg";
                }
                // Image temp source
                $imageTemp = $_FILES["imagen_grupo"]["tmp_name"];
                // Comprimos el fichero
                $compressedImage = compressImage($imageTemp, $rutalogo);

                if ($compressedImage) {
                    $grupo->imagen = $rutalogo;
                    if ($grupo->save()) {
                        $this->new_message('Imagen guardada correctamente.');
                    } else {
                        $this->new_error_msg('Error al guardar la imagen del Grupo.');
                    }
                } else {
                    $this->new_error_msg("Error al comprimir la imagen");
                }
            }
        } else {
            $this->new_error_msg("Grupo no encontrado.");
        }
    }

    private function borrar_imagen_grupo()
    {   
        $grupo = $this->grupos->get($_GET['delete_imagen_gr']);
        if ($grupo) {
            if (file_exists($grupo->imagen)) {
                unlink($grupo->imagen);
            }
            $grupo->imagen = null;
            if ($grupo->save()) {
                $this->new_message('Imagen eliminada correctamente.');
            } else {
                $this->new_error_msg('Error al eliminar la Imagen del grupo.');
            }
        } else {
            $this->new_error_msg("Grupo no encontrado.");
        }
    }

    private function cargar_imagen_marca()
    {
        $marca = $this->marcas->get($_POST['imagenmar']);
        if ($marca) {
            //carga de imagen en marcas
            if (is_uploaded_file($_FILES['imagen_marca']['tmp_name'])) {
                if (!file_exists(JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/marcas")) {
                    @mkdir(JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/marcas", 0777, true);
                }

                $rutalogo = "";
                if (substr(strtolower($_FILES['imagen_marca']['name']), -3) == 'png') {
                    $rutalogo = JG_MYDOCS . "datosEmpresas/" . $this->empresa->idempresa . "/marcas/" . $marca->idmarca . ".png";
                } else if (substr(strtolower($_FILES['imagen_marca']['name']), -3) == 'jpg') {
                    $rutalogo = JG_MYDOCS . "datosEmpresas/" . $this->empresa->idempresa . "/marcas/" . $marca->idmarca . ".jpg";
                }
                // Image temp source
                $imageTemp = $_FILES["imagen_marca"]["tmp_name"];
                // Comprimos el fichero
                $compressedImage = compressImage($imageTemp, $rutalogo);

                if ($compressedImage) {
                    $marca->imagen = $rutalogo;
                    if ($marca->save()) {
                        $this->new_message('Imagen guardada correctamente.');
                    } else {
                        $this->new_error_msg('Error al guardar la imagen de la Marca.');
                    }
                } else {
                    $this->new_error_msg("Error al comprimir la imagen");
                }
            }
        } else {
            $this->new_error_msg("Marca no encontrada.");
        }
    }

    private function borrar_imagen_marca()
    {   
        $marca = $this->marcas->get($_GET['delete_imagen_mar']);
        if ($marca) {
            if (file_exists($marca->imagen)) {
                unlink($marca->imagen);
            }
            $marca->imagen = null;
            if ($marca->save()) {
                $this->new_message('Imagen eliminada correctamente.');
            } else {
                $this->new_error_msg('Error al eliminar la Imagen de la Marca.');
            }
        } else {
            $this->new_error_msg("Marca no encontrado.");
        }
    }
}
