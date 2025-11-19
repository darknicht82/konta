<?php
/**
 * Controlador de admin -> información del sistema.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class admin_info extends controller
{

    public $allow_delete;
    public $b_alerta;
    public $b_controlador;
    public $b_desde;
    public $b_detalle;
    public $b_hasta;
    public $b_ip;
    public $b_tipo;
    public $b_usuario;
    public $db_tables;
    private $fsvar;
    public $modulos_eneboo;
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Información del sistema', 'Administrador', true, true, false, 'bi bi-info-square');
    }

    protected function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->admin;

        /**
         * Cargamos las variables del cron
         */
        $this->fsvar           = new jg_var();
        $this->configuraciones = new configuraciones();

        $cron_vars = $this->fsvar->array_get(
            [
                'cron_exists' => false,
                'cron_lock'   => false,
                'cron_error'  => false,
            ]
        );

        if (isset($_POST['mail_user'])) {
            $this->guardar_configuraciones();
        } else if (isset($_GET['fix'])) {
            $cron_vars['cron_error'] = false;
            $cron_vars['cron_lock']  = false;
            $this->fsvar->array_save($cron_vars);
        } else if (isset($_GET['clean_cache'])) {
            file_manager::clear_raintpl_cache();
            if ($this->cache->clean()) {
                $this->new_message("Cache limpiada correctamente.");
            }
        } else if (!$cron_vars['cron_exists']) {
            $this->new_advice('No has ejecutado el cron, puedes activarlo dentro de tu servidor.');
        } else if ($cron_vars['cron_error']) {
            $this->new_error_msg('Parece que ha habido un error con el cron. Haz clic <a href="' . $this->url()
                . '&fix=TRUE">aquí</a> para corregirlo.');
        } else if ($cron_vars['cron_lock']) {
            $this->new_advice('Se está ejecutando el cron.');
        }

        $this->config = false;
        $docs = $this->configuraciones->getConfig();
        if ($docs) {
            $this->config = $docs;
        }
    }

    public function cache_version()
    {
        return $this->cache->version();
    }

    public function db_name()
    {
        return JG_DB_NAME;
    }

    public function db_version()
    {
        return $this->db->version();
    }

    public function get_locks()
    {
        return $this->db->get_locks();
    }

    public function php_version()
    {
        return phpversion();
    }

    public function getIpServer()
    {
        return $_SERVER['SERVER_ADDR'];
    }

    private function guardar_configuraciones()
    {
        $configuracion = $this->configuraciones->getConfig();
        if (!$configuracion) {
            $configuracion                = new configuraciones();
            $configuracion->fec_creacion  = date('Y-m-d');
            $configuracion->nick_creacion = $this->user->nick;
        }

        $configuracion->mail_user     = $_POST['mail_user'];
        $configuracion->mail_host     = $_POST['mail_host'];
        $configuracion->mail_enc      = $_POST['mail_enc'];
        $configuracion->mail_port     = $_POST['mail_port'];
        $configuracion->mail_password = $_POST['mail_password'];

        $configuracion->fec_modificacion  = date('Y-m-d');
        $configuracion->nick_modificacion = $this->user->nick;

        if ($configuracion->save()) {
            $this->new_message("Configuraciones guardadas correctamente.");
        } else {
            $this->new_error_msg("Error al guardar las configuraciones");
        }
    }
}
