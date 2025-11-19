<?php
/**
 * Controlador para modificar el perfil del usuario.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_usuario extends controller
{
    public $allow_delete;
    public $allow_modify;
    public $user_log;
    public $suser;
    public $uempresa;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Usuario', 'Configuración', true, false);
    }

    public function private_core()
    {
        $this->share_extensions();
        $this->uempresa      = false;
        $this->empresa_model = new empresa();
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->admin || $this->user->supervisor;

        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->admin || $this->user->supervisor;

        $this->suser = false;
        if (isset($_GET['snick'])) {
            $this->suser = $this->user->get(filter_input(INPUT_GET, 'snick'));
        }

        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        }

        if ($this->suser) {
            if (!$this->user->admin) {
                if ($this->suser->idempresa != $this->empresa->idempresa) {
                    $this->new_advice("El Usuario no esta disponible para su empresa.");
                    $this->suser = false;
                    return;
                }
            }
            if ($this->user->admin) {
                $empresa0 = new empresa();
                //Cargo la empresa
                $this->uempresa = $empresa0->get($this->suser->idempresa);
            }

            $this->page->title = $this->suser->nick;

            /// ¿Estamos modificando nuestro usuario?
            if ($this->suser->nick == $this->user->nick) {
                $this->allow_modify = true;
                $this->allow_delete = false;
            }

            if (isset($_POST['spassword']) || isset($_POST['scodagente']) || isset($_POST['sadmin'])) {
                $this->modificar_user();
            } else if (filter_input_req('sactivo')) {
                $this->desactivar_usuario();
            }

            /// ¿Estamos modificando nuestro usuario?
            if ($this->suser->nick == $this->user->nick) {
                $this->user = $this->suser;
            }

            /// si el usuario no tiene acceso a ninguna página, entonces hay que informar del problema.
            if (!($this->suser->admin || $this->suser->supervisor)) {
                $sin_paginas = true;
                foreach ($this->all_pages() as $p) {
                    if ($p->activo) {
                        $sin_paginas = false;
                        break;
                    }
                }
                if ($sin_paginas) {
                    $this->new_advice('No has autorizado a este usuario a acceder a ninguna'
                        . ' página y por tanto no podrá hacer nada. Puedes darle acceso a alguna página'
                        . ' desde la pestaña autorizar.');
                }
            }

            $fslog          = new log();
            $this->user_log = $fslog->all_from($this->suser->nick);
        } else {
            $this->new_error_msg("Usuario no encontrado.");
        }
    }

    public function url()
    {
        if (!isset($this->suser)) {
            return parent::url();
        } else if ($this->suser) {
            return $this->suser->url();
        }

        return $this->page->url();
    }

    public function all_pages()
    {
        $returnlist = [];

        /// Obtenemos la lista de páginas. Todas
        foreach ($this->menu as $m) {
            $m->activo         = false;
            $m->allow_delete   = false;
            $m->allow_modify   = false;
            $m->allow_download = false;
            $returnlist[]      = $m;
        }

        /// Completamos con la lista de accesos del usuario
        $access = $this->suser->get_accesses();
        foreach ($returnlist as $i => $value) {
            foreach ($access as $a) {
                if ($value->name == $a->page) {
                    $returnlist[$i]->activo         = true;
                    $returnlist[$i]->allow_delete   = $a->allow_delete;
                    $returnlist[$i]->allow_modify   = $a->allow_modify;
                    $returnlist[$i]->allow_download = $a->allow_download;
                    break;
                }
            }
        }

        /// ordenamos por nombre
        usort($returnlist, function ($val1, $val2) {
            return strcmp($val1->name, $val2->name);
        });

        return $returnlist;
    }

    private function share_extensions()
    {
        foreach ($this->extensions as $ext) {
            if ($ext->type == 'css') {
                if (!file_exists($ext->text)) {
                    $ext->delete();
                }
            }
        }

        $extensions = array(
            array(
                'name'      => 'Modo Oscuro',
                'page_from' => __CLASS__,
                'page_to'   => __CLASS__,
                'type'      => 'css',
                'text'      => 'dark-theme',
                'params'    => '',
            ),
            array(
                'name'      => 'Modo Claro',
                'page_from' => __CLASS__,
                'page_to'   => __CLASS__,
                'type'      => 'css',
                'text'      => 'light-theme',
                'params'    => '',
            ),
            array(
                'name'      => 'Modo Mixto',
                'page_from' => __CLASS__,
                'page_to'   => __CLASS__,
                'type'      => 'css',
                'text'      => 'semi-dark',
                'params'    => '',
            ),
        );
        foreach ($extensions as $ext) {
            $fsext = new extension($ext);
            $fsext->save();
        }
    }

    private function modificar_user()
    {
        if (!$this->allow_modify) {
            $this->new_error_msg('No tienes permiso para modificar estos datos.');
        } else {
            $user_no_more_admin = false;
            $error              = false;
            $spassword          = filter_input(INPUT_POST, 'spassword');
            if ($spassword != '') {
                if ($spassword == filter_input(INPUT_POST, 'spassword2')) {
                    if ($this->suser->set_password($spassword)) {
                        $this->new_message('Se ha cambiado la contraseña del usuario ' . $this->suser->nick, true, 'login', true);
                    }
                } else {
                    $this->new_error_msg('Las contraseñas no coinciden.');
                    $error = true;
                }
            }

            if (isset($_POST['email'])) {
                $this->suser->email = strtolower(filter_input(INPUT_POST, 'email'));
            }

            if (isset($_POST['scodagente'])) {
                $this->suser->codagente = null;
                if ($_POST['scodagente'] != '') {
                    $this->suser->codagente = filter_input(INPUT_POST, 'scodagente');
                }
            }

            /*
             * Propiedad admin: solamente un admin puede cambiarla.
             */
            if ($this->user->admin) {
                /*
                 * El propio usuario no puede decidir dejar de ser administrador.
                 */
                if ($this->user->nick != $this->suser->nick) {
                    /*
                     * Si un usuario es administrador y deja de serlo, hay que darle acceso
                     * a algunas páginas, en caso contrario no podrá continuar
                     */
                    if ($this->suser->admin && !isset($_POST['sadmin'])) {
                        $user_no_more_admin = true;
                    }
                    $this->suser->admin      = isset($_POST['sadmin']);
                    $this->suser->supervisor = isset($_POST['ssupervisor']);
                }
            }

            $this->suser->ppage = null;
            if (isset($_POST['udpage'])) {
                $this->suser->ppage = filter_input(INPUT_POST, 'udpage');
            }

            if (isset($_POST['css'])) {
                $this->suser->thema = filter_input(INPUT_POST, 'css');
            }

            $this->suser->apellidos = filter_input(INPUT_POST, 'apellidos');
            $this->suser->nombres   = filter_input(INPUT_POST, 'nombres');

            if (complemento_exists('facturador') && $this->suser->admin) {
                $this->suser->idempresa = filter_input(INPUT_POST, 'idempresa');
            }

            if (isset($_POST['idcliente'])) {
                $this->suser->idcliente = filter_input(INPUT_POST, 'idcliente');
            }

            /*if ($this->user->admin) {
            $this->suser->fec_inicio    = filter_input(INPUT_POST, 'fec_inicio');
            $this->suser->fec_caducidad = filter_input(INPUT_POST, 'fec_caducidad');
            $this->suser->numdocs       = filter_input(INPUT_POST, 'numdocs');
            }*/

            //Almaceno la auditoria
            $this->suser->nick_modificacion = $this->user->nick;
            $this->suser->fec_modificacion  = date('Y-m-d');

            if ($error) {
                /// si se han producido errores, no hacemos nada más
            } else if ($this->suser->save()) {
                if ($this->user->admin || $this->user->supervisor) {
                    /// para cada página, comprobamos si hay que darle acceso o no
                    foreach ($this->all_pages() as $p) {
                        if ($p->name != 'admin_home' && $p->name != 'admin_info' && $p->name != 'admin_orden_menu' && $p->name != 'admin_rol') {
                            $a = new access(array('nick' => $this->suser->nick, 'page' => $p->name, 'allow_delete' => false, 'allow_modify' => false, 'allow_download' => false));
                            if (isset($_POST['allow_delete'])) {
                                $a->allow_delete = in_array($p->name, $_POST['allow_delete']);
                            }
                            if (isset($_POST['allow_modify'])) {
                                $a->allow_modify = in_array($p->name, $_POST['allow_modify']);
                            }
                            if (isset($_POST['allow_download'])) {
                                $a->allow_download = in_array($p->name, $_POST['allow_download']);
                            }

                            if ($user_no_more_admin) {
                                /*
                                 * Si un usuario es administrador y deja de serlo, hay que darle acceso
                                 * a algunas páginas, en caso contrario no podrá continuar.
                                 */
                                $a->save();
                            } else if (!isset($_POST['activo'])) {
                                /**
                                 * No se ha marcado ningún checkbox de autorizado, así que eliminamos el acceso
                                 * a todas las páginas. Una a una.
                                 */
                                $a->delete();
                            } else if (in_array($p->name, $_POST['activo'])) {
                                /// la página ha sido marcada como autorizada.
                                $a->save();

                                /// si no hay una página de inicio para el usuario, usamos esta
                                if (is_null($this->suser->ppage) && $p->show_on_menu) {
                                    $this->suser->page = $p->name;
                                    $this->suser->save();
                                }
                            } else {
                                /// la página no está marcada como autorizada.
                                $a->delete();
                            }
                        }
                    }
                }

                $this->new_message("Datos modificados correctamente.");
            } else {
                $this->new_error_msg("¡Imposible modificar los datos!");
            }
        }
    }

    private function desactivar_usuario()
    {
        if (!($this->user->admin || $this->user->supervisor)) {
            $this->new_error_msg('Solamente un administrador puede activar o desactivar a un Usuario.');
        } else if ($this->user->nick == $this->suser->nick) {
            $this->new_error_msg('No se permite Activar/Desactivar a uno mismo.');
        } else {
            // Un usuario no se puede Activar/Desactivar a él mismo.
            $this->suser->activo = (filter_input_req('sactivo') == 'TRUE');

            if ($this->suser->save()) {
                if ($this->suser->activo) {
                    $this->new_message('Usuario activado correctamente.', true, 'login', true);
                } else {
                    $this->new_message('Usuario desactivado correctamente.', true, 'login', true);
                }
            } else {
                $this->new_error_msg('Error al Activar/Desactivar el Usuario');
            }
        }
    }

    public function validar_pagina($page)
    {
        if (complemento_exists('facturador') && complemento_exists('pagosycobros')) {
            //Plan Contador
            $paginas_no_permitidas = array('admin_home', 'admin_info', 'admin_rol', 'admin_orden_menu', 'lista_impuestos', 'lista_empresas', 'lista_documentos', 'lista_sustentossri', 'admin_procesos', 'cargas_masivas', 'lista_usuarios');
        } else if (complemento_exists('facturador') && !complemento_exists('pagosycobros')) {
            //Facturador
            if ($this->empresa->plan_basico) {
                $paginas_no_permitidas = array('admin_home', 'admin_info', 'admin_rol', 'admin_orden_menu', 'lista_impuestos', 'lista_empresas', 'lista_documentos', 'lista_formaspago', 'lista_sustentossri', 'admin_procesos', 'cargas_masivas', 'lista_articulos', 'ver_articulo', 'lista_movimientos_stock', 'ver_movimiento_stock', 'lista_regularizaciones_stock', 'ver_regularizacion_stock', 'crear_movimiento', 'crear_regularizacion', 'bandeja_sri', 'configuracion_articulos', 'informes_sri', 'informes_articulos', 'lista_usuarios');
            } else {
                $paginas_no_permitidas = array('admin_home', 'admin_info', 'admin_rol', 'admin_orden_menu', 'lista_impuestos', 'lista_empresas', 'lista_documentos', 'lista_formaspago', 'lista_sustentossri', 'admin_procesos', 'cargas_masivas', 'lista_usuarios');
            }
        } else {
            $paginas_no_permitidas = array('admin_home', 'admin_info', 'admin_rol', 'admin_orden_menu', 'lista_impuestos', 'admin_procesos', 'cargas_masivas', 'lista_usuarios');
        }

        if (in_array($page, $paginas_no_permitidas)) {
            return false;
        }

        return true;
    }

    private function buscar_cliente()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_clientes($this->empresa->idempresa, $_GET['buscar_cliente']);

        echo json_encode($result);
        exit;
    }
}
