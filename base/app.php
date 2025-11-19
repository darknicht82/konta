<?php

require_once 'base/cache.php';
require_once 'base/core_log.php';
require_once 'base/file_manager.php';
require_once 'base/functions.php';

/**
    * @author Jonathan Guamba <jonathang_28@hotmail.es>
*/
class app
{
    protected $cache;
    protected $core_log;
    private $uptime;

    public function __construct($controller_name = '')
    {
        $tiempo       = explode(' ', microtime());
        $this->uptime = $tiempo[1] + $tiempo[0];

        $this->cache    = new cache();
        $this->core_log = new core_log($controller_name);
    }

    protected function duplicated_petition($pid)
    {
        $ids = $this->cache->get_array('petition_ids');
        if (in_array($pid, $ids)) {
            return true;
        }

        $ids[] = $pid;
        $this->cache->set('petition_ids', $ids, 300);
        return false;
    }

    public function duration()
    {
        $tiempo = explode(" ", microtime());
        return (number_format($tiempo[1] + $tiempo[0] - $this->uptime, 3) . ' s');
    }

    public function anio()
    {
        return date('Y');
    }

    public function get_advices()
    {
        return $this->core_log->get_advices();
    }

    public function get_db_history()
    {
        return $this->core_log->get_sql_history();
    }

    public function get_errors()
    {
        return $this->core_log->get_errors();
    }

    public function get_js_location($filename)
    {
        /// necesitamos un id que se cambie al limpiar la caché
        $idcache = $this->cache->get('idcache');
        if (!$idcache) {
            $idcache = $this->random_string(10);
            $this->cache->set('idcache', $idcache, 86400);
        }

        foreach ($GLOBALS['plugins'] as $plugin) {
            if (file_exists('plugins/' . $plugin . '/view/js/' . $filename)) {
                return PATH . 'plugins/' . $plugin . '/view/js/' . $filename . '?idcache=' . $idcache;
            }
        }
        /// si no está en los plugins estará en el núcleo
        return JG_PATH . 'view/js/' . $filename . '?idcache=' . $idcache;
    }

    public function get_max_file_upload()
    {
        return get_max_file_upload();
    }

    public function get_messages()
    {
        return $this->core_log->get_messages();
    }

    public function hour()
    {
        return date('H:i:s');
    }

    public function random_string($length = 30)
    {
        return mb_substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    public function today()
    {
        return date('Y-m-d');
    }

    public function version()
    {
        return file_exists('VERSION') ? trim(file_get_contents('VERSION')) : '0';
    }
}
