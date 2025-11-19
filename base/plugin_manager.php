<?php
require_once 'base/file_manager.php';

/**
 * Description of plugin_manager
 *
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class plugin_manager
{
    /**
     *
     * @var cache
     */
    private $cache;

    /**
     *
     * @var core_log
     */
    private $core_log;

    /**
     *
     * @var bool
     */
    public $disable_mod_plugins = false;

    /**
     *
     * @var bool
     */
    public $disable_add_plugins = false;

    /**
     *
     * @var bool
     */
    public $disable_rm_plugins = false;

    /**
     *
     * @var array
     */
    private $download_list;

    /**
     *
     * @var float
     */
    public $version = 2017.901;

    public function __construct()
    {
        $this->cache    = new cache();
        $this->core_log = new core_log();

        if (defined('JG_DISABLE_MOD_PLUGINS')) {
            $this->disable_mod_plugins = JG_DISABLE_MOD_PLUGINS;
            $this->disable_add_plugins = JG_DISABLE_MOD_PLUGINS;
            $this->disable_rm_plugins  = JG_DISABLE_MOD_PLUGINS;
        }

        if (!$this->disable_mod_plugins) {
            if (defined('JG_DISABLE_ADD_PLUGINS')) {
                $this->disable_add_plugins = JG_DISABLE_ADD_PLUGINS;
            }

            if (defined('JG_DISABLE_RM_PLUGINS')) {
                $this->disable_rm_plugins = JG_DISABLE_RM_PLUGINS;
            }
        }

        if (file_exists('VERSION')) {
            $this->version = (float) trim(file_get_contents(JG_FOLDER . '/VERSION'));
        }
    }

    public function disable($plugin_name)
    {
        if (!in_array($plugin_name, $this->enabled())) {
            return true;
        }

        foreach ($GLOBALS['plugins'] as $i => $value) {
            if ($value == $plugin_name) {
                unset($GLOBALS['plugins'][$i]);
                break;
            }
        }

        if ($this->save()) {
            $this->core_log->new_message('Complemento <b>' . $plugin_name . '</b> desactivado correctamente.');
            $this->core_log->save('Complemento ' . $plugin_name . ' desactivado correctamente.', 'msg');
        } else {
            $this->core_log->new_error('Imposible desactivar el complemento <b>' . $plugin_name . '</b>.');
            return false;
        }

        /*
         * Desactivamos las páginas que ya no existen
         */
        $this->disable_unnused_pages();

        /// desactivamos los plugins que dependan de este
        foreach ($this->installed() as $plug) {
            /**
             * Si el complemento que hemos desactivado, es requerido por el complemento
             * que estamos comprobando, lo desativamos también.
             */
            if (in_array($plug['name'], $GLOBALS['plugins']) && in_array($plugin_name, $plug['require'])) {
                $this->disable($plug['name']);
            }
        }

        $this->clean_cache();
        return true;
    }

    public function disabled()
    {
        $disabled = [];
        if (defined('JG_DISABLED_PLUGINS')) {
            foreach (explode(',', JG_DISABLED_PLUGINS) as $aux) {
                $disabled[] = $aux;
            }
        }

        return $disabled;
    }

    public function download($plugin_id)
    {
        if ($this->disable_mod_plugins) {
            $this->core_log->new_error('No tienes permiso para descargar plugins.');
            return false;
        }

        foreach ($this->downloads() as $item) {
            if ($item['id'] != (int) $plugin_id) {
                continue;
            }

            $this->core_log->new_message('Descargando el complemento ' . $item['nombre']);
            if (!@file_download($item['zip_link'], JG_FOLDER . '/download.zip')) {
                $this->core_log->new_error('Error al descargar. Tendrás que descargarlo manualmente desde '
                    . '<a href="' . $item['zip_link'] . '" target="_blank">aquí</a> y añadirlo pulsando el botón <b>añadir</b>.');
                return false;
            }

            $zip = new ZipArchive();
            $res = $zip->open(JG_FOLDER . '/download.zip', ZipArchive::CHECKCONS);
            if ($res !== true) {
                $this->core_log->new_error('Error al abrir el ZIP. Código: ' . $res);
                return false;
            }

            $plugins_list = file_manager::scan_folder(JG_FOLDER . '/plugins');
            $zip->extractTo(JG_FOLDER . '/plugins/');
            $zip->close();
            unlink(JG_FOLDER . '/download.zip');

            /// renombramos si es necesario
            foreach (file_manager::scan_folder(JG_FOLDER . '/plugins') as $f) {
                if (is_dir(JG_FOLDER . '/plugins/' . $f) && !in_array($f, $plugins_list)) {
                    rename(JG_FOLDER . '/plugins/' . $f, JG_FOLDER . '/plugins/' . $item['nombre']);
                    break;
                }
            }

            $this->core_log->new_message('Complemento añadido correctamente.');
            return $this->enable($item['nombre']);
        }

        $this->core_log->new_error('Descarga no encontrada.');
        return false;
    }

    public function downloads()
    {
        $this->download_list = array();

        return $this->download_list;
    }

    public function enable($plugin_name)
    {
        if (in_array($plugin_name, $GLOBALS['plugins'])) {
            $this->core_log->new_message('Complemento <b>' . $plugin_name . '</b> ya activado.');
            return true;
        }

        $name = $this->rename_plugin($plugin_name);

        /// comprobamos las dependencias
        $install = true;
        $wizard  = false;
        foreach ($this->installed() as $pitem) {
            if ($pitem['name'] != $name) {
                continue;
            }

            $wizard = $pitem['wizard'];
            foreach ($pitem['require'] as $req) {
                if (in_array($req, $GLOBALS['plugins'])) {
                    continue;
                }

                $install = false;
                $txt     = 'Dependencias incumplidas: <b>' . $req . '</b>';
                foreach ($this->downloads() as $value) {
                    if ($value['nombre'] == $req && !$this->disable_add_plugins) {
                        $txt .= '. Puedes descargar este plugin desde la <b>pestaña descargas</b>.';
                        break;
                    }
                }

                $this->core_log->new_error($txt);
            }
            break;
        }

        if (!$install) {
            $this->core_log->new_error('Imposible activar el complemento <b>' . $name . '</b>.');
            return false;
        }

        array_unshift($GLOBALS['plugins'], $name);
        if (!$this->save()) {
            $this->core_log->new_error('Imposible activar el complemento <b>' . $name . '</b>.');
            return false;
        }

        require_all_models();

        if ($wizard) {
            $this->core_log->new_advice('Ya puedes <a href="index.php?page=' . $wizard . '">configurar el complemento</a>.');
            header('Location: index.php?page=' . $wizard);
            $this->clean_cache();
            return true;
        }

        $this->enable_plugin_controllers($name);
        $this->core_log->new_message('Complemento <b>' . $name . '</b> activado correctamente.');
        $this->core_log->save('Complemento ' . $name . ' activado correctamente.', 'msg');
        $this->clean_cache();
        return true;
    }

    public function enabled()
    {
        return $GLOBALS['plugins'];
    }

    public function install($path, $name)
    {
        if ($this->disable_add_plugins) {
            $this->core_log->new_error('La subida de plugins está desactivada. Contacta con tu proveedor de hosting.');
            return;
        }

        $zip = new ZipArchive();
        $res = $zip->open($path, ZipArchive::CHECKCONS);
        if ($res === true) {
            $zip->extractTo(JG_FOLDER . '/plugins/');
            $zip->close();

            $name = $this->rename_plugin(substr($name, 0, -4));
            $this->core_log->new_message('Complemento <b>' . $name . '</b> añadido correctamente. Ya puede activarlo.');
            $this->clean_cache();
        } else {
            $this->core_log->new_error('Error al abrir el archivo ZIP. Código: ' . $res);
        }
    }

    public function installed()
    {
        $plugins  = [];
        $disabled = $this->disabled();

        foreach (file_manager::scan_folder(JG_FOLDER . '/plugins') as $file_name) {
            if (!is_dir(JG_FOLDER . '/plugins/' . $file_name) || in_array($file_name, $disabled)) {
                continue;
            }

            $plugins[] = $this->get_plugin_data($file_name);
        }

        return $plugins;
    }

    public function remove($plugin_name)
    {
        if ($this->disable_rm_plugins) {
            $this->core_log->new_error('No tienes permiso para eliminar plugins.');
            return false;
        }

        if (!is_writable(JG_FOLDER . '/plugins/' . $plugin_name)) {
            $this->core_log->new_error('No tienes permisos de escritura sobre la carpeta plugins/' . $plugin_name);
            return false;
        }

        if (file_manager::del_tree(JG_FOLDER . '/plugins/' . $plugin_name)) {
            $this->core_log->new_message('Complemento ' . $plugin_name . ' eliminado correctamente.');
            $this->core_log->save('Complemento ' . $plugin_name . ' eliminado correctamente.');
            $this->clean_cache();
            return true;
        }

        $this->core_log->new_error('Imposible eliminar el complemento ' . $plugin_name);
        return false;
    }

    private function clean_cache()
    {
        $this->cache->clean();
        file_manager::clear_raintpl_cache();
    }

    private function disable_unnused_pages()
    {
        $eliminadas = [];
        $page_model = new page();
        foreach ($page_model->all() as $page) {
            if (file_exists(JG_FOLDER . '/controller/' . $page->name . '.php')) {
                continue;
            }

            $encontrada = false;
            foreach ($this->enabled() as $plugin) {
                if (file_exists(JG_FOLDER . '/plugins/' . $plugin . '/controller/' . $page->name . '.php')) {
                    $encontrada = true;
                    break;
                }
            }

            if (!$encontrada && $page->delete()) {
                $eliminadas[] = $page->name;
            }
        }

        if (!empty($eliminadas)) {
            $this->core_log->new_message('Se han eliminado automáticamente las siguientes páginas: ' . implode(', ', $eliminadas));
        }
    }

    private function enable_plugin_controllers($plugin_name)
    {
        /// cargamos el archivo functions.php
        if (file_exists(JG_FOLDER . '/plugins/' . $plugin_name . '/functions.php')) {
            require_once 'plugins/' . $plugin_name . '/functions.php';
        }

        /// buscamos controladores
        if (file_exists(JG_FOLDER . '/plugins/' . $plugin_name . '/controller')) {
            $page_list = [];
            foreach (file_manager::scan_files(JG_FOLDER . '/plugins/' . $plugin_name . '/controller', 'php') as $f) {
                $page_name   = substr($f, 0, -4);
                $page_list[] = $page_name;

                require_once 'plugins/' . $plugin_name . '/controller/' . $f;
                $new_fsc = new $page_name();

                if (!$new_fsc->page->save()) {
                    $this->core_log->new_error("Imposible guardar la página " . $page_name);
                }

                unset($new_fsc);
            }

            $this->core_log->new_message('Se han activado automáticamente las siguientes páginas: ' . implode(', ', $page_list) . '.');
        }
    }

    private function get_plugin_data($plugin_name)
    {
        $plugin = [
            'compatible'    => false,
            'description'   => 'Sin descripción.',
            'download2_url' => '',
            'enabled'       => false,
            'error_msg'     => 'Falta archivo gsc.ini',
            'idplugin'      => null,
            'min_version'   => $this->version,
            'name'          => $plugin_name,
            'prioridad'     => '-',
            'require'       => [],
            'update_url'    => '',
            'version'       => 1,
            'version_url'   => '',
            'wizard'        => false,
        ];

        if (!file_exists(JG_FOLDER . '/plugins/' . $plugin_name . '/gsc.ini')) {
            return $plugin;
        }

        $ini_file = parse_ini_file(JG_FOLDER . '/plugins/' . $plugin_name . '/gsc.ini');
        foreach (['description', 'idplugin', 'min_version', 'update_url', 'version', 'version_url', 'wizard'] as $field) {
            if (isset($ini_file[$field])) {
                $plugin[$field] = $ini_file[$field];
            }
        }

        $plugin['enabled']     = in_array($plugin_name, $this->enabled());
        $plugin['version']     = (int) $plugin['version'];
        $plugin['min_version'] = (float) $plugin['min_version'];

        if ($this->version >= $plugin['min_version']) {
            $plugin['compatible'] = true;
        } else {
            $plugin['error_msg'] = 'Requiere Version ' . $plugin['min_version'];
        }

        if (file_exists(JG_FOLDER . '/plugins/' . $plugin_name . '/description')) {
            $plugin['description'] = file_get_contents(JG_FOLDER . '/plugins/' . $plugin_name . '/description');
        }

        if (isset($ini_file['require']) && $ini_file['require'] != '') {
            $plugin['require'] = explode(',', $ini_file['require']);
        }

        if (!isset($ini_file['version_url']) && $this->downloads()) {
            foreach ($this->downloads() as $ditem) {
                if ($ditem['id'] != $plugin['idplugin']) {
                    continue;
                }

                if (intval($ditem['version']) > $plugin['version']) {
                    $plugin['download2_url'] = 'updater.php?idplugin=' . $plugin['idplugin'] . '&name=' . $plugin_name;
                }
                break;
            }
        }

        if ($plugin['enabled']) {
            foreach (array_reverse($this->enabled()) as $i => $value) {
                if ($value == $plugin_name) {
                    $plugin['prioridad'] = $i;
                    break;
                }
            }
        }

        return $plugin;
    }

    private function rename_plugin($name)
    {
        $new_name = $name;
        if (strpos($name, '-master') !== false) {
            /// renombramos el directorio
            $new_name = substr($name, 0, strpos($name, '-master'));
            if (!rename(JG_FOLDER . '/plugins/' . $name, JG_FOLDER . '/plugins/' . $new_name)) {
                $this->core_log->new_error('Error al renombrar el complemento.');
            }
        }

        return $new_name;
    }

    private function save()
    {
        if (empty($GLOBALS['plugins'])) {
            return unlink(JG_FOLDER . '/tmp/' . JG_TMP_NAME . 'enabled_plugins.list');
        }

        $string = implode(',', $GLOBALS['plugins']);
        if (false === file_put_contents(JG_FOLDER . '/tmp/' . JG_TMP_NAME . 'enabled_plugins.list', $string)) {
            return false;
        }

        return true;
    }
}
