<?php
/**
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_usuarios extends controller
{
    public $historial;
    public $rol;
    public $empresa_model;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Usuarios', 'Configuración', true, true, false, 'bi bi-file-person');
    }

    protected function private_core()
    {
        $this->rol           = new rol();
        $this->empresa_model = new empresa();

        if (isset($_POST['nick'])) {
            $this->add_user();
        } else if (isset($_GET['delete'])) {
            $this->delete_user();
        } else if (isset($_POST['nrol'])) {
            $this->add_rol();
        } else if (isset($_GET['delete_rol'])) {
            $this->delete_rol();
        } else if (isset($_REQUEST['tipoid_b'])) {
            $this->consulta_servidor();
        } else if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        }
        /// cargamos el historial
        $fslog           = new log();
        $this->historial = $fslog->all_by('login');
    }

    private function consulta_servidor()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Error en la consulta');

        $emp = $this->empresa_model->get_by_identificacion($_REQUEST['identificacion_b']);

        if ($emp) {
            $result = array('error' => 'R', 'msj' => 'La Empresa ya se encuentra registrada.');
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

    private function add_user()
    {
        $paso = false;
        if ($this->user->admin || $this->user->supervisor) {
            $paso = true;
        }
        $usuario = $this->user->get($_POST['nick']);
        if ($usuario) {
            if (complemento_exists('facturador')) {
                $this->new_error_msg('El usuario ya se encuentra Utilizado, por favor utilice otro nick.');
            } else {
                $this->new_error_msg('El usuario <a href="' . $usuario->url() . '">ya existe</a>.');
            }
        } else if (!$paso) {
            $this->new_error_msg('No tiene permisos para crear usuarios.', 'login', true, true);
        } else {
            $paso_creacion = true;
            $idempresa     = $this->empresa->idempresa;
            if (complemento_exists('facturador') && $this->user->admin) {
                if (isset($_POST['nueva_empresa'])) {
                    $empresa                  = new empresa();
                    $empresa->razonsocial     = $_POST['razonsocial'];
                    $empresa->nombrecomercial = $_POST['nombrecomercial'];
                    $empresa->ruc             = $_POST['ruc'];
                    $empresa->telefono        = $_POST['telefono'];
                    $empresa->direccion       = $_POST['direccion'];
                    //Datos del Facturador
                    $empresa->fec_inicio_plan    = $_POST['fec_inicio_plan'];
                    $empresa->fec_caducidad_plan = $_POST['fec_caducidad_plan'];
                    $empresa->numusers           = $_POST['numusers'];
                    $empresa->numdocs            = $_POST['numdocs'];
                    $empresa->produccion         = true;
                    $empresa->activafacelec      = true;
                    $empresa->plan_basico        = isset($_POST['plan_basico']);
                    //Auditoria
                    $empresa->fec_creacion  = date('Y-m-d');
                    $empresa->nick_creacion = $this->user->nick;
                    if ($empresa->save()) {
                        $idempresa = $empresa->idempresa;
                        // si se guarda genero el historial
                        $historial                     = new historial_planes();
                        $historial->idempresa          = $empresa->idempresa;
                        $historial->fec_inicio_plan    = $empresa->fec_inicio_plan;
                        $historial->fec_caducidad_plan = $empresa->fec_caducidad_plan;
                        $historial->numusers           = $empresa->numusers;
                        $historial->numdocs            = $empresa->numdocs;
                        $historial->plan_basico        = $empresa->plan_basico;
                        //Auditoria
                        $historial->nick_creacion = $this->user->nick;
                        $historial->fec_creacion  = date('Y-m-d');
                        if (!$historial->save()) {
                            $this->new_advice("Empresa guardada correctamente, pero se tuvo un error al guardar el historial del plan.");
                        }
                    } else {
                        $this->new_error_msg("Error al crear la empresa.");
                        return;
                    }
                } else {
                    $idempresa = $_POST['idempresa'];
                }
            }
            if ($paso_creacion) {
                $nu            = new users();
                $nu->nick      = $_POST['nick'];
                $nu->nombres   = $_POST['nombres'];
                $nu->apellidos = $_POST['apellidos'];
                if ($this->user->admin) {
                    $nu->admin      = isset($_POST['nadmin']);
                    $nu->supervisor = isset($_POST['nsupervisor']);
                }
                $nu->fec_creacion  = date('Y-m-d');
                $nu->nick_creacion = $this->user->nick;
                $nu->idempresa     = $idempresa;
                if (isset($_POST['idcliente'])) {
                    $nu->idcliente = $_POST['idcliente'];
                }

                if ($nu->set_password($_POST['password'])) {
                    if ($nu->save()) {
                        // si se Guarda genero el mensaje
                        $this->new_message('Usuario: ' . $nu->nick . ' creado correctamente.', true, 'login', true);

                        /// algún rol marcado
                        if (!($nu->admin && $nu->supervisor) && isset($_POST['roles'])) {
                            foreach ($_POST['roles'] as $codrol) {
                                $rol = $this->rol->get($codrol);
                                if ($rol) {
                                    $fru         = new rol_user();
                                    $fru->codrol = $codrol;
                                    $fru->user   = $nu->nick;

                                    if ($fru->save()) {
                                        foreach ($rol->get_accesses() as $p) {
                                            $a               = new access();
                                            $a->page         = $p->page;
                                            $a->user         = $nu->nick;
                                            $a->allow_delete = $p->allow_delete;
                                            $a->save();
                                        }
                                    }
                                }
                            }
                        }
                        header('location: ' . $nu->url());
                    } else {
                        $this->new_error_msg("Error al guardar el usuario!");
                    }
                }
            }
        }
    }

    private function delete_user()
    {
        $nu = $this->user->get(filter_input(INPUT_GET, 'delete'));
        if ($nu) {
            if (!$this->user->admin) {
                $this->new_error_msg("Solamente un administrador puede eliminar usuarios.", 'login', true);
            } else if ($nu->delete()) {
                $this->new_message("Usuario " . $nu->nick . " eliminado correctamente.", true, 'login', true);
            } else {
                $this->new_error_msg("¡Imposible eliminar al usuario!");
            }
        } else {
            $this->new_error_msg("¡Usuario no encontrado!");
        }
    }

    private function add_rol()
    {
        $this->rol->codrol      = filter_input(INPUT_POST, 'nrol');
        $this->rol->descripcion = filter_input(INPUT_POST, 'descripcion');

        if ($this->rol->save()) {
            $this->new_message('Datos guardados correctamente.');
            header('Location: ' . $this->rol->url());
        } else {
            $this->new_error_msg('Error al crear el rol.');
        }
    }

    private function delete_rol()
    {
        $rol = $this->rol->get(filter_input(INPUT_GET, 'delete_rol'));
        if ($rol) {
            if ($rol->delete()) {
                $this->new_message('Rol eliminado correctamente.');
            } else {
                $this->new_error_msg('Error al eliminar el rol #' . $rol->codrol);
            }
        } else {
            $this->new_error_msg('Rol no encontrado.');
        }
    }

    public function all_pages()
    {
        $returnlist = [];

        /// Obtenemos la lista de páginas. Todas
        foreach ($this->menu as $m) {
            $m->enabled      = false;
            $m->allow_delete = false;
            $m->users        = [];
            $returnlist[]    = $m;
        }

        $users = $this->user->all();
        /// colocamos a los administradores primero
        usort($users, function ($a, $b) {
            if ($a->admin) {
                return -1;
            } else if ($b->admin) {
                return 1;
            }

            return 0;
        });

        /// completamos con los permisos de los usuarios
        foreach ($users as $user) {
            if ($user->admin) {
                foreach ($returnlist as $i => $value) {
                    $returnlist[$i]->users[$user->nick] = array(
                        'modify' => true,
                        'delete' => true,
                    );
                }
            } else {
                foreach ($returnlist as $i => $value) {
                    $returnlist[$i]->users[$user->nick] = array(
                        'modify' => false,
                        'delete' => false,
                    );
                }

                foreach ($user->get_accesses() as $a) {
                    foreach ($returnlist as $i => $value) {
                        if ($a->page == $value->name) {
                            $returnlist[$i]->users[$user->nick]['modify'] = true;
                            $returnlist[$i]->users[$user->nick]['delete'] = $a->allow_delete;
                            break;
                        }
                    }
                }
            }
        }

        /// ordenamos por nombre
        usort($returnlist, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        return $returnlist;
    }

    public function usuarios()
    {
        if ($this->user->admin) {
            return $this->user->all();
        }

        return $this->user->all_by_empresa($this->empresa->idempresa);
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
