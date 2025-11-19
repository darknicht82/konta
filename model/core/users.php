<?php
namespace GSC_Systems\model;

class users extends \model
{
    //campos de la base
    public $nick;
    public $idempresa;
    public $password;
    public $log_key;
    public $apellidos;
    public $nombres;
    public $admin;
    public $supervisor;
    public $activo;
    public $last_login;
    public $last_login_time;
    public $last_ip;
    public $last_browser;
    public $ppage;
    public $thema;
    public $idcliente;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;
    //variable de inicio de sesion
    public $logged_on;
    //menu del usuario
    private $menu;

    public function __construct($d = false)
    {
        parent::__construct('users');
        if ($d) {
            $this->nick            = $d['nick'];
            $this->password        = $d['password'];
            $this->idempresa       = $d['idempresa'];
            $this->nick_supervisor = $d['nick_supervisor'];
            $this->log_key         = $d['log_key'];
            $this->apellidos       = $d['apellidos'];
            $this->nombres         = $d['nombres'];
            $this->admin           = $this->str2bool($d['admin']);
            $this->supervisor      = $this->str2bool($d['supervisor']);
            $this->activo          = $this->str2bool($d['activo']);
            $this->last_login      = $d['last_login'] ? Date('d-m-Y', strtotime($d['last_login'])) : null;
            $this->last_login_time = $d['last_login_time'] ? Date('H:i:s', strtotime($d['last_login_time'])) : null;
            $this->last_ip         = $d['last_ip'];
            $this->last_browser    = $d['last_browser'];
            $this->ppage           = $d['ppage'];
            $this->thema           = isset($d['thema']) ? $d['thema'] : 'light-theme';
            $this->idcliente       = $d['idcliente'];
            //Auditoria del sistema
            $this->fec_creacion      = $d['fec_creacion'] ? Date('d-m-Y', strtotime($d['fec_creacion'])) : null;
            $this->nick_creacion     = $d['nick_creacion'];
            $this->fec_modificacion  = $d['fec_modificacion'] ? Date('d-m-Y', strtotime($d['fec_modificacion'])) : null;
            $this->nick_modificacion = $d['nick_modificacion'];
        } else {
            $this->nick            = null;
            $this->password        = null;
            $this->idempresa       = null;
            $this->nick_supervisor = null;
            $this->log_key         = null;
            $this->apellidos       = null;
            $this->nombres         = null;
            $this->admin           = false;
            $this->supervisor      = false;
            $this->activo          = true;
            $this->last_login      = null;
            $this->last_login_time = null;
            $this->last_ip         = null;
            $this->last_browser    = null;
            $this->ppage           = null;
            $this->thema           = 'light-theme';
            $this->idcliente       = null;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;

        }

        $this->logged_on = false;
    }

    protected function install()
    {
        new \empresa();
        new \page();
        new \clientes();

        $this->clean_cache(true);
        $this->new_message('Se ha creado el usuario <b>admin</b>.');
        return "INSERT INTO " . $this->table_name . " (nick, idempresa, password, log_key, admin, activo, apellidos, nombres, thema, fec_creacion, nick_creacion)
            VALUES ('admin', '1','" . sha1('gsc123') . "', NULL, TRUE, TRUE, 'Sistema', 'Administrador', 'light-theme', '" . date('Y-m-d') . "', 'admin');";
    }

    public function url()
    {
        if (is_null($this->nick)) {
            return 'index.php?page=lista_usuarios';
        }
        return 'index.php?page=ver_usuario&snick=' . $this->nick;
    }

    public function getCliente()
    {
        if ($this->idcliente) {
            $cli0 = new clientes();
            return $cli0->get($this->idcliente);
        }

        return false;
    }

    public function get_menu($reload = false)
    {
        if (!isset($this->menu) || $reload) {
            $this->menu = [];
            $page       = new \page();

            if ($this->admin) {
                $this->menu = $page->all();
            } else if ($this->supervisor) {
                $this->menu = $page->all_to_supervisor($this->isPlanBasico());
            } else {
                $access      = new \access();
                $access_list = $access->all_from_nick($this->nick);
                foreach ($page->all() as $p) {
                    foreach ($access_list as $a) {
                        if ($p->name == $a->page) {
                            $this->menu[] = $p;
                            break;
                        }
                    }
                }
            }
        }
        return $this->menu;
    }

    public function have_access_to($page_name)
    {
        $status = false;
        foreach ($this->get_menu() as $m) {
            if ($m->name == $page_name) {
                $status = true;
                break;
            }
        }
        return $status;
    }

    public function allow_delete_on($page_name)
    {
        if ($this->admin || $this->supervisor) {
            return true;
        }

        $status = false;
        foreach ($this->get_accesses() as $a) {
            if ($a->page == $page_name) {
                $status = $a->allow_delete;
                break;
            }
        }
        return $status;
    }

    public function allow_modify_on($page_name)
    {
        if ($this->admin || $this->supervisor) {
            return true;
        }

        $status = false;
        foreach ($this->get_accesses() as $a) {
            if ($a->page == $page_name) {
                $status = $a->allow_modify;
                break;
            }
        }
        return $status;
    }

    public function allow_download_on($page_name)
    {
        if ($this->admin || $this->supervisor) {
            return true;
        }

        $status = false;
        foreach ($this->get_accesses() as $a) {
            if ($a->page == $page_name) {
                $status = $a->allow_download;
                break;
            }
        }
        return $status;
    }

    public function get_accesses()
    {
        $access = new \access();
        return $access->all_from_nick($this->nick);
    }

    public function show_last_login()
    {
        if (is_null($this->last_login)) {
            return '-';
        }

        return Date('d-m-Y', strtotime($this->last_login)) . ' ' . $this->last_login_time;
    }

    public function set_password($pass = '')
    {
        $pass = trim($pass);
        if (mb_strlen($pass) > 5 && mb_strlen($pass) <= 32) {
            $this->password = sha1($pass);
            return true;
        }

        $this->new_error_msg('La contraseña debe contener entre 6 y 30 caracteres.');
        return false;
    }

    public function update_login()
    {
        $ltime = strtotime($this->last_login . ' ' . $this->last_login_time);
        if (time() - $ltime >= 300) {
            $this->last_login      = Date('d-m-Y');
            $this->last_login_time = Date('H:i:s');
            $this->last_ip         = get_ip();
            $this->last_browser    = $_SERVER['HTTP_USER_AGENT'];
            $this->save();
        }
    }

    public function new_logkey()
    {
        $this->log_key = sha1(strval(rand()));

        $this->logged_on       = true;
        $this->last_login      = Date('d-m-Y');
        $this->last_login_time = Date('H:i:s');
        $this->last_ip         = get_ip();
        $this->last_browser    = $_SERVER['HTTP_USER_AGENT'];
    }

    public function get($nick = '')
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE nick = " . $this->var2str($nick) . ";");
        if ($data) {
            return new \users($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->nick)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE nick = " . $this->var2str($this->nick) . ";");
    }

    public function test()
    {
        $this->nick         = trim($this->nick);
        $this->last_browser = $this->no_html($this->last_browser);
        if (complemento_exists('facturador')) {
            if (!$this->exists()) {
                //es nuevo usuario
                $empresa = $this->get_empresa();
                if ($empresa) {
                    if ($empresa->count_users() >= $empresa->numusers) {
                        $this->new_error_msg("Ya no puede crear mas usuarios debido al plan contratado.");
                        return false;
                    }
                }
            }
        }

        if (!preg_match("/^[A-Z0-9_\+\.\-]{3,25}$/i", $this->nick)) {
            $this->new_error_msg("Nick no válido. Debe tener entre 3 y 25 caracteres, puede utilizar números o letras, pero no Ñ ni acentos.");
            return false;
        }
        return true;
    }

    public function save()
    {
        if ($this->test()) {
            $this->clean_cache();
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET password = " . $this->var2str($this->password)
                . ", idempresa = " . $this->var2str($this->idempresa)
                . ", nick_supervisor = " . $this->var2str($this->nick_supervisor)
                . ", log_key = " . $this->var2str($this->log_key)
                . ", apellidos = " . $this->var2str($this->apellidos)
                . ", nombres = " . $this->var2str($this->nombres)
                . ", admin = " . $this->var2str($this->admin)
                . ", supervisor = " . $this->var2str($this->supervisor)
                . ", activo = " . $this->var2str($this->activo)
                . ", last_login = " . $this->var2str($this->last_login)
                . ", last_login_time = " . $this->var2str($this->last_login_time)
                . ", last_ip = " . $this->var2str($this->last_ip)
                . ", last_browser = " . $this->var2str($this->last_browser)
                . ", ppage = " . $this->var2str($this->ppage)
                . ", thema = " . $this->var2str($this->thema)
                . ", idcliente = " . $this->var2str($this->idcliente)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE nick = " . $this->var2str($this->nick) . ";";
            } else {
                $sql = "INSERT INTO " . $this->table_name . " (nick, idempresa, nick_supervisor, password, log_key, apellidos, nombres, admin, supervisor, activo, last_login, last_login_time, last_ip, last_browser, ppage, thema, idcliente, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES
                (" . $this->var2str($this->nick)
                . "," . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->nick_supervisor)
                . "," . $this->var2str($this->password)
                . "," . $this->var2str($this->log_key)
                . "," . $this->var2str($this->apellidos)
                . "," . $this->var2str($this->nombres)
                . "," . $this->var2str($this->admin)
                . "," . $this->var2str($this->supervisor)
                . "," . $this->var2str($this->activo)
                . "," . $this->var2str($this->last_login)
                . "," . $this->var2str($this->last_login_time)
                . "," . $this->var2str($this->last_ip)
                . "," . $this->var2str($this->last_browser)
                . "," . $this->var2str($this->ppage)
                . "," . $this->var2str($this->thema)
                . "," . $this->var2str($this->idcliente)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            return $this->db->exec($sql);
        }

        return false;
    }

    public function delete()
    {
        $this->clean_cache();
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE nick = " . $this->var2str($this->nick) . ";");
    }

    public function clean_cache($full = false)
    {
        $this->cache->delete('user_all');

        if ($full) {
            $this->clean_checked_tables();
        }
    }

    public function all()
    {
        /// consultamos primero en la cache
        $list = $this->cache->get_array('user_all');
        if (empty($list)) {
            /// si no está en la cache, consultamos la base de datos
            $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY lower(nick) ASC;");
            if ($data) {
                foreach ($data as $u) {
                    $list[] = new \users($u);
                }
            }

            /// guardamos en cache
            $this->cache->set('user_all', $list);
        }
        return $list;
    }

    public function all_by_empresa($idempresa)
    {
        /// consultamos primero en la cache
        $list = array();
        /// si no está en la cache, consultamos la base de datos
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE admin = false AND idempresa = " . $this->var2str($idempresa) . " ORDER BY lower(nick) ASC;");
        if ($data) {
            foreach ($data as $u) {
                $list[] = new \users($u);
            }
        }
        return $list;
    }

    public function all_by_supervisor($nick_supervisor)
    {
        /// consultamos primero en la cache
        $list = array();
        /// si no está en la cache, consultamos la base de datos
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE admin != TRUE AND (nick_supervisor = " . $this->var2str($nick_supervisor) . " OR nick = " . $this->var2str($nick_supervisor) . ") ORDER BY lower(nick) ASC;");
        if ($data) {
            foreach ($data as $u) {
                $list[] = new \users($u);
            }
        }
        return $list;
    }

    public function all_sin_admin($nick_supervisor)
    {
        /// consultamos primero en la cache
        $list = array();
        /// si no está en la cache, consultamos la base de datos
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE admin != TRUE ORDER BY lower(nick) ASC;");
        if ($data) {
            foreach ($data as $u) {
                $list[] = new \users($u);
            }
        }
        return $list;
    }

    public function all_activo()
    {
        $list = [];
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE activo = TRUE ORDER BY lower(nick) ASC;");
        if ($data) {
            foreach ($data as $u) {
                $list[] = new \users($u);
            }
        }
        return $list;
    }

    public function get_empresa()
    {
        if (complemento_exists('facturador')) {
            $emp0    = new \empresa();
            $empresa = $emp0->get($this->idempresa);
            if ($empresa) {
                return $empresa;
            }
        }

        return "-";
    }

    public function isPlanBasico()
    {
        if (complemento_exists('facturador')) {
            $emp0    = new \empresa();
            $empresa = $emp0->getBasico($this->idempresa);
            if ($empresa) {
                return true;
            }
        }
        return false;
    }
}
