<?php
require_once 'base/app.php';
require_once 'base/plugin_manager.php';

/**
 * Controlador del actualizador
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class updater extends app
{

    /**
     *
     * @var boolean
     */
    public $btn_fin;

    /**
     *
     * @var array
     */
    private $download_list2;

    /**
     *
     * @var plugin_manager
     */
    public $plugin_manager;

    /**
     *
     * @var array
     */
    private $plugin_updates;

    /**
     *
     * @var array
     */
    public $plugins;

    /**
     *
     * @var string
     */
    public $tr_options;

    /**
     *
     * @var string
     */
    public $tr_updates;

    /**
     *
     * @var array
     */
    public $updates;

    /**
     *
     * @var string
     */
    public $xid;

    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->btn_fin = FALSE;
        $this->plugin_manager = new plugin_manager();
        $this->plugins = [];
        $this->tr_options = '';
        $this->tr_updates = '';
        $this->xid();

        if (filter_input(INPUT_COOKIE, 'user') && filter_input(INPUT_COOKIE, 'logkey')) {
            $this->process();
        } else {
            $this->core_log->new_error('<a href="index.php">Debes iniciar sesi&oacute;n</a>');
        }
    }

    /**
     * Elimina la actualización de la lista de pendientes.
     * @param string $plugin
     */
    private function actualizacion_correcta($plugin = '')
    {
        if (!isset($this->updates)) {
            $this->get_updates();
        }

        if (!$this->updates) {
            return;
        }

        if (empty($plugin)) {
            /// hemos actualizado el core
            $this->updates['core'] = FALSE;
        } else {
            foreach ($this->updates['plugins'] as $i => $pl) {
                if ($pl['name'] == $plugin) {
                    unset($this->updates['plugins'][$i]);
                    break;
                }
            }
        }

        /// guardamos la lista de actualizaciones en cache
        if (count($this->updates['plugins']) > 0) {
            $this->cache->set('updater_lista', $this->updates);
        }
    }

    private function actualizar_nucleo()
    {
        return false;
    }

    private function actualizar_plugin($plugin_name)
    {
        foreach ($this->plugin_manager->installed() as $plugin) {
            if ($plugin['name'] != $plugin_name) {
                continue;
            }

            /// descargamos el zip
            if (!@file_download($plugin['update_url'], JG_FOLDER . '/update.zip')) {
                $this->core_log->new_error('Error al descargar el archivo update.zip. Intente de nuevo en unos minutos.');
                return false;
            }

            $zip = new ZipArchive();
            $zip_status = $zip->open(JG_FOLDER . '/update.zip', ZipArchive::CHECKCONS);
            if ($zip_status !== TRUE) {
                $this->core_log->new_error('Ha habido un error con el archivo update.zip. Código: ' . $zip_status
                    . '. Intente de nuevo en unos minutos.');
                return false;
            }

            /// nos guardamos la lista previa de /plugins
            $plugins_list = file_manager::scan_folder(JG_FOLDER . '/plugins');

            /// eliminamos los archivos antiguos
            file_manager::del_tree(JG_FOLDER . '/plugins/' . $plugin_name);

            /// descomprimimos
            $zip->extractTo(JG_FOLDER . '/plugins/');
            $zip->close();
            unlink(JG_FOLDER . '/update.zip');

            /// renombramos si es necesario
            foreach (file_manager::scan_folder(JG_FOLDER . '/plugins') as $f) {
                if (is_dir(JG_FOLDER . '/plugins/' . $f) && !in_array($f, $plugins_list)) {
                    rename(JG_FOLDER . '/plugins/' . $f, JG_FOLDER . '/plugins/' . $plugin_name);
                    break;
                }
            }

            $this->core_log->new_message('Plugin actualizado correctamente.');
            $this->actualizacion_correcta($plugin_name);
            return true;
        }

        return false;
    }

    private function actualizar_plugin_pago($idplugin, $name, $key)
    {
        return true;
    }

    public function check_for_plugin_updates()
    {
        if (isset($this->plugin_updates)) {
            return $this->plugin_updates;
        }

        $this->plugin_updates = [];
        foreach ($this->plugin_manager->installed() as $plugin) {
            $this->plugins[] = $plugin['name'];

            if ($plugin['version_url'] != '' && $plugin['update_url'] != '') {
                /// plugin con descarga gratuita
                $internet_ini = @parse_ini_string(@file_get_contents($plugin['version_url']));
                if ($internet_ini && $plugin['version'] < intval($internet_ini['version'])) {
                    $plugin['new_version'] = intval($internet_ini['version']);
                    $plugin['depago'] = FALSE;

                    $this->plugin_updates[] = $plugin;
                }
            } else if ($plugin['idplugin']) {
                /// plugin de pago/oculto
                foreach ($this->download_list2() as $ditem) {
                    if ($ditem->id != $plugin['idplugin']) {
                        continue;
                    }

                    if (intval($ditem->version) > $plugin['version']) {
                        $plugin['new_version'] = intval($ditem->version);
                        $plugin['depago'] = TRUE;
                        $plugin['private_key'] = '';

                        if (file_exists('tmp/' . JG_TMP_NAME . 'private_keys/' . $plugin['idplugin'])) {
                            $plugin['private_key'] = trim(@file_get_contents('tmp/' . JG_TMP_NAME . 'private_keys/' . $plugin['idplugin']));
                        } else if (!file_exists('tmp/' . JG_TMP_NAME . 'private_keys/') && mkdir('tmp/' . JG_TMP_NAME . 'private_keys/')) {
                            file_put_contents('tmp/' . JG_TMP_NAME . 'private_keys/.htaccess', 'Deny from all');
                        }

                        $this->plugin_updates[] = $plugin;
                    }
                    break;
                }
            }
        }

        return $this->plugin_updates;
    }

    private function comprobar_actualizaciones()
    {
        if (!isset($this->updates)) {
            $this->get_updates();
        }

        if ($this->updates['core']) {
            $this->tr_updates = '';
        } else {
            $this->tr_options = '';

            foreach ($this->updates['plugins'] as $plugin) {
                if (false === $plugin['depago']) {
                    $this->tr_updates .= '';
                    continue;
                }

                if (!$this->xid) {
                    /// nada
                    continue;
                }

                if (empty($plugin['private_key'])) {
                    $this->tr_updates .= '';
                    continue;
                }

                $this->tr_updates .= '';
            }

            if ($this->tr_updates == '') {
                $this->tr_updates = '<tr class="success"><td colspan="5">El sistema está actualizado.'
                    . ' <a href="index.php?page=admin_home&updated=TRUE">Volver</a></td></tr>';
                $this->btn_fin = TRUE;
            }
        }
    }

    private function download_list2()
    {
        $this->download_list2 = [];
        return $this->download_list2;
    }

    private function get_updates()
    {
        /// si no está en cache, nos toca comprobar todo
        $this->updates = ['version' => '', 'core' => FALSE, 'plugins' => []];
    }

    private function guardar_key()
    {
        $private_key = filter_input(INPUT_POST, 'key');
        if (file_put_contents('tmp/' . JG_TMP_NAME . 'private_keys/' . filter_input(INPUT_GET, 'idplugin'), $private_key)) {
            $this->core_log->new_message('Clave añadida correctamente.');
            $this->cache->clean();
        } else {
            $this->core_log->new_error('Error al guardar la clave.');
        }
    }

    private function process()
    {
        /// solamente comprobamos si no hay que hacer nada
        if (!filter_input(INPUT_GET, 'update') && !filter_input(INPUT_GET, 'reinstall') && !filter_input(INPUT_GET, 'plugin') && !filter_input(INPUT_GET, 'idplugin')) {
            /// ¿Están todos los permisos correctos?
            foreach (file_manager::not_writable_folders() as $dir) {
                $this->core_log->new_error('No se puede escribir sobre el directorio ' . $dir);
            }

            /// ¿Sigue estando disponible ziparchive?
            if (!extension_loaded('zip')) {
                $this->core_log->new_error('No se encuentra la clase ZipArchive, debes instalar php-zip.');
            }
        }

        if (count($this->core_log->get_errors()) > 0) {
            $this->core_log->new_error('Tienes que corregir estos errores antes de continuar.');
        } else if (filter_input(INPUT_GET, 'update') || filter_input(INPUT_GET, 'reinstall')) {
            $this->actualizar_nucleo();
        } else if (filter_input(INPUT_GET, 'plugin')) {
            $this->actualizar_plugin(filter_input(INPUT_GET, 'plugin'));
        } else if (filter_input(INPUT_GET, 'idplugin') && filter_input(INPUT_GET, 'name') && filter_input(INPUT_GET, 'key')) {
            $this->actualizar_plugin_pago(filter_input(INPUT_GET, 'idplugin'), filter_input(INPUT_GET, 'name'), filter_input(INPUT_GET, 'key'));
        } else if (filter_input(INPUT_GET, 'idplugin') && filter_input(INPUT_GET, 'name') && filter_input(INPUT_POST, 'key')) {
            $this->guardar_key();
        }

        if (count($this->core_log->get_errors()) == 0) {
            $this->comprobar_actualizaciones();
        } else {
            $this->tr_updates = '<tr class="warning"><td colspan="5">Aplazada la comprobación'
                . ' de plugins hasta que resuelvas los problemas.</td></tr>';
        }
    }

    private function xid()
    {
        $this->xid = '';
        $data = $this->cache->get_array('empresa');
        if (!empty($data)) {
            $this->xid = $data[0]['xid'];
            if (!filter_input(INPUT_COOKIE, 'uxid')) {
                setcookie('uxid', $this->xid, time() + JG_COOKIES_EXPIRE);
            }
        } else if (filter_input(INPUT_COOKIE, 'uxid')) {
            $this->xid = filter_input(INPUT_COOKIE, 'uxid');
        }
    }
}
