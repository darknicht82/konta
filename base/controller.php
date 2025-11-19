<?php
require_once 'base/app.php';
require_once 'base/db2.php';
require_once 'base/default_items.php';
require_once 'base/extended_model.php';
require_once 'base/login.php';
//require_once 'base/divisa_tools.php';

require_all_models();

/**
 * La clase principal de la que deben heredar todos los controladores.
 *
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class controller extends app
{

    /**
     * Nombre del controlador (lo utilizamos en lugar de __CLASS__ porque __CLASS__
     * en las funciones de la clase padre es el nombre de la clase padre).
     * @var string
     */
    protected $class_name;

    /**
     * Este objeto permite acceso directo a la base de datos.
     * @var db2
     */
    protected $db;

    /**
     * Permite consultar los parámetros predeterminados para series, divisas, forma de pago, etc...
     * @var default_items
     */
    public $default_items;

    /**
     *
     * @var divisa_tools
     */
    protected $divisa_tools;

    /**
     * La empresa
     * @var empresa
     */
    public $empresa;

    /**
     * Listado de extensiones de la página
     * @var array
     */
    public $extensions;

    /**
     * Indica si está actualizado o no.
     * @var boolean
     */
    private $updated;

    /**
     * Listado con los últimos cambios en documentos.
     * @var array
     */
    private $last_changes;

    /**
     *
     * @var login
     */
    private $login_tools;

    /**
     * Contiene el menú
     * @var array
     */
    protected $menu;

    /**
     * El elemento del menú de esta página
     * @var page
     */
    public $page;

    /**
     * Esta variable contiene el texto enviado como parámetro query por cualquier formulario,
     * es decir, se corresponde con $_REQUEST['query']
     * @var string|boolean
     */
    public $query;

    /**
     * Indica que archivo HTML hay que cargar
     * @var string|false
     */
    public $template;

    /**
     * El usuario que ha hecho login
     * @var user
     */
    public $user;

    /**
     * @param string $name sustituir por __CLASS__
     * @param string $title es el título de la página, y el texto que aparecerá en el menú
     * @param string $folder es el menú dónde quieres colocar el acceso directo
     * @param boolean $subfolder creo el Submenu
     * @param boolean $shmenu debe ser TRUE si quieres añadir el acceso directo en el menú
     * @param boolean $important debe ser TRUE si quieres que aparezca en el menú de destacado
     */
    public function __construct($name = __CLASS__, $title = 'home', $folder = '', $subfolder = null, $shmenu = true, $important = false, $icono = false)
    {
        parent::__construct($name);
        $this->class_name = $name;
        $this->db         = new db2();
        $this->extensions = [];

        if ($this->db->connect()) {
            if (is_bool($subfolder)) {
                $subfolder = null;
            }
            $this->user = new users();
            $this->check_page($name, $title, $folder, $shmenu, $important, $icono, $subfolder);
            $this->empresa       = new empresa();
            $this->default_items = new default_items();
            $this->login_tools   = new login();
            $this->load_extensions();

            if (filter_input(INPUT_GET, 'logout')) {
                $this->template = 'login/default';
                $this->login_tools->log_out();
            } else if (filter_input(INPUT_POST, 'new_password') && filter_input(INPUT_POST, 'new_password2') && filter_input(INPUT_POST, 'user')) {
                $this->login_tools->change_user_passwd();
                $this->template = 'login/default';
            } else if (!$this->log_in()) {
                $this->template = 'login/default';
                $this->public_core();
            } else if ($this->user->have_access_to($this->page->name)) {
                $this->empresa = $this->empresa->get($this->user->idempresa);
                if ($name == __CLASS__) {
                    $this->template = 'index';
                } else {
                    $this->template = $name;
                    $this->set_default_items();
                    $this->pre_private_core();
                    $this->private_core();
                }
            } else if ($name == '') {
                $this->template = 'index';
            } else {
                $this->template = 'access_denied';
                $this->user->clean_cache(true);
            }
        } else {
            $this->template = 'no_db';
            $this->new_error_msg('Error al conectar con la base de datos <b>' . JG_DB_NAME . '</b>!');
        }
    }

    /**
     * Devuelve TRUE si hay actualizaciones pendientes (sólo si eres admin).
     * @return boolean
     */
    public function check_for_updates()
    {
        return false;
        if (isset($this->updated)) {
            return $this->updated;
        }

        $this->updated = false;
        if ($this->user->admin) {
            $desactivado = defined('JG_DISABLE_MOD_PLUGINS') ? JG_DISABLE_MOD_PLUGINS : false;
            if ($desactivado) {
                $this->updated = false;
            } else {
                $fsvar         = new jg_var();
                $this->updated = $fsvar->simple_get('updates');
            }
        }

        return $this->updated;
    }

    /**
     * Elimina la lista con los últimos cambios del usuario.
     */
    public function clean_last_changes()
    {
        $this->last_changes = [];
        $this->cache->delete('last_changes_' . $this->user->nick);
    }

    /**
     * Cierra la conexión con la base de datos.
     */
    public function close()
    {
        $this->db->close();
    }

    /**
     * Convierte un precio de la divisa_desde a la divisa especificada
     * @param float $precio
     * @param string $coddivisa_desde
     * @param string $coddivisa
     * @return float
     */
    public function divisa_convert($precio, $coddivisa_desde, $coddivisa)
    {
        return $this->divisa_tools->divisa_convert($precio, $coddivisa_desde, $coddivisa);
    }

    /**
     * Convierte el precio en euros a la divisa preterminada de la empresa.
     * Por defecto usa las tasas de conversión actuales, pero si se especifica
     * coddivisa y tasaconv las usará.
     * @param float $precio
     * @param string $coddivisa
     * @param float $tasaconv
     * @return float
     */
    public function euro_convert($precio, $coddivisa = null, $tasaconv = null)
    {
        return $this->divisa_tools->euro_convert($precio, $coddivisa, $tasaconv);
    }

    /**
     * Devuelve la lista de menús
     * @return array lista de menús
     */
    public function folders()
    {
        $folders = [];
        foreach ($this->menu as $m) {
            if ($m->folder != '' && $m->show_on_menu && !in_array($m->folder, $folders)) {
                $folders[] = $m->folder;
            }
        }
        return $folders;
    }

    /**
     * Devuelve la lista con los últimos cambios del usuario.
     * @return array
     */
    public function get_last_changes()
    {
        if (!isset($this->last_changes)) {
            $this->last_changes = $this->cache->get_array('last_changes_' . $this->user->nick);
        }

        return $this->last_changes;
    }

    /**
     * Muestra un consejo al usuario
     * @param string $msg el consejo a mostrar
     */
    public function new_advice($msg)
    {
        if ($this->class_name == $this->core_log->controller_name()) {
            /// solamente nos interesa mostrar los mensajes del controlador que inicia todo
            $this->core_log->new_advice($msg);
        }
    }

    /**
     * Añade un elemento a la lista de cambios del usuario.
     * @param string $txt texto descriptivo.
     * @param string $url URL del elemento (albarán, factura, artículos...).
     * @param boolean $nuevo TRUE si el elemento es nuevo, FALSE si se ha modificado.
     */
    public function new_change($txt, $url, $nuevo = false)
    {
        $this->get_last_changes();
        if (count($this->last_changes) > 0) {
            if ($this->last_changes[0]['url'] == $url) {
                $this->last_changes[0]['nuevo'] = $nuevo;
            } else {
                array_unshift($this->last_changes, array('texto' => ucfirst($txt), 'url' => $url, 'nuevo' => $nuevo, 'cambio' => date('d-m-Y H:i:s')));
            }
        } else {
            array_unshift($this->last_changes, array('texto' => ucfirst($txt), 'url' => $url, 'nuevo' => $nuevo, 'cambio' => date('d-m-Y H:i:s')));
        }

        /// sólo queremos 10 elementos
        $num = 10;
        foreach ($this->last_changes as $i => $value) {
            if ($num > 0) {
                $num--;
            } else {
                unset($this->last_changes[$i]);
            }
        }

        $this->cache->set('last_changes_' . $this->user->nick, $this->last_changes);
    }

    /**
     * Muestra al usuario un mensaje de error
     * @param string $msg el mensaje a mostrar
     */
    public function new_error_msg($msg, $tipo = 'error', $alerta = false, $guardar = true)
    {
        if ($this->class_name == $this->core_log->controller_name()) {
            /// solamente nos interesa mostrar los mensajes del controlador que inicia todo
            $this->core_log->new_error($msg);
        }

        if ($guardar) {
            $this->core_log->save($msg, $tipo, $alerta);
        }
    }

    /**
     * Muestra un mensaje al usuario
     * @param string $msg
     * @param boolean $save
     * @param string $tipo
     * @param boolean $alerta
     */
    public function new_message($msg, $save = false, $tipo = 'msg', $alerta = false)
    {
        if ($this->class_name == $this->core_log->controller_name()) {
            /// solamente nos interesa mostrar los mensajes del controlador que inicia todo
            $this->core_log->new_message($msg);
        }

        if ($save) {
            $this->core_log->save($msg, $tipo, $alerta);
        }
    }

    /**
     * Devuelve la lista de elementos de un menú seleccionado
     * @param string $folder el menú seleccionado
     * @return array lista de elementos del menú
     */
    public function pages($folder = '')
    {
        $submenus = [];
        $pages = [];
        foreach ($this->menu as $page) {
            if ($folder == $page->folder && $page->show_on_menu && !in_array($page, $pages)) {
                if ($page->subfolder) {
                    if (!isset($submenus[$page->subfolder])) {
                        $pages[] = $page;
                        $submenus[$page->subfolder] = '';
                    }
                } else {
                    $pages[] = $page;
                }
            }
        }
        return $pages;
    }

    public function subpages($subfolder = '', $folder = '')
    {
        $subpages = [];
        foreach ($this->menu as $page) {
            if ($folder == $page->folder && $subfolder == $page->subfolder && $page->show_on_menu && !in_array($page, $subpages)) {
                $subpages[] = $page;
            }
        }
        return $subpages;
    }

    /**
     * Esta es la función principal que se ejecuta cuando el usuario ha hecho login
     */
    protected function private_core()
    {

    }

    /**
     * Función que se ejecuta si el usuario no ha hecho login
     */
    protected function public_core()
    {

    }

    /**
     * Devuelve el número de consultas SQL (SELECT) que se han ejecutado
     * @return integer
     */
    public function selects()
    {
        return $this->db->get_selects();
    }

    /**
     * Redirecciona a la página predeterminada para el usuario
     */
    public function select_default_page()
    {
        if (!$this->db->connected() || !$this->user->logged_on) {
            return;
        }

        if (!is_null($this->user->ppage)) {
            header('Location: index.php?page=' . $this->user->ppage);
            return;
        }

        /*
         * Cuando un usuario no tiene asignada una página por defecto,
         * se selecciona la primera página del menú.
         */
        $page = 'admin_home';
        foreach ($this->menu as $p) {
            if (!$p->show_on_menu) {
                continue;
            }

            $page = $p->name;
            if ($p->important) {
                break;
            }
        }
        header('Location: index.php?page=' . $page);
    }

    /**
     * Devuelve un string con el número en el formato de número predeterminado.
     * @param float $num
     * @param integer $decimales
     * @param boolean $js
     * @return string
     */
    public function show_numero($num = 0, $decimales = JG_NF0, $js = false)
    {
        return $this->divisa_tools->show_numero($num, $decimales, $js);
    }

    /**
     * Devuelve un string con el precio en el formato predefinido y con la
     * divisa seleccionada (o la predeterminada).
     * @param float $precio
     * @param string $coddivisa
     * @param string $simbolo
     * @param integer $dec nº de decimales
     * @return string
     */
    public function show_precio($precio = 0, $coddivisa = false, $simbolo = true, $dec = JG_NF0)
    {
        return $this->divisa_tools->show_precio($precio, $coddivisa, $simbolo, $dec);
    }

    /**
     * Devuelve el símbolo de divisa predeterminado
     * o bien el símbolo de la divisa seleccionada.
     * @param string $coddivisa
     * @return string
     */
    public function simbolo_divisa($coddivisa = false)
    {
        return $this->divisa_tools->simbolo_divisa($coddivisa);
    }

    /**
     * Devuelve información del sistema para el informe de errores
     * @return string la información del sistema
     */
    public function system_info()
    {
        $txt = 'GSC_Systems: ' . $this->version() . "\n";

        if ($this->db->connected()) {
            if ($this->user->logged_on) {
                $txt .= 'os: ' . php_uname() . "\n";
                $txt .= 'php: ' . phpversion() . "\n";
                $txt .= 'database type: ' . JG_DB_TYPE . "\n";
                $txt .= 'database version: ' . $this->db->version() . "\n";

                if (JG_FOREIGN_KEYS == 0) {
                    $txt .= "foreign keys: NO\n";
                }

                if ($this->cache->connected()) {
                    $txt .= "memcache: YES\n";
                    $txt .= 'memcache version: ' . $this->cache->version() . "\n";
                } else {
                    $txt .= "memcache: NO\n";
                }

                if (function_exists('curl_init')) {
                    $txt .= "curl: YES\n";
                } else {
                    $txt .= "curl: NO\n";
                }

                $txt .= "max input vars: " . ini_get('max_input_vars') . "\n";

                $txt .= 'plugins: ' . join(',', $GLOBALS['plugins']) . "\n";

                if ($this->check_for_updates()) {
                    $txt .= "updated: NO\n";
                }

                if (filter_input(INPUT_SERVER, 'REQUEST_URI')) {
                    $txt .= 'url: ' . filter_input(INPUT_SERVER, 'REQUEST_URI') . "\n------";
                }
            }
        } else {
            $txt .= 'os: ' . php_uname() . "\n";
            $txt .= 'php: ' . phpversion() . "\n";
            $txt .= 'database type: ' . JG_DB_TYPE . "\n";
        }

        foreach ($this->get_errors() as $e) {
            $txt .= "\n" . $e;
        }

        return str_replace('"', "'", $txt);
    }

    /**
     * Devuleve el número de transacciones SQL que se han ejecutado
     * @return integer
     */
    public function transactions()
    {
        return $this->db->get_transactions();
    }

    /**
     * Devuelve la URL de esta página (index.php?page=LO-QUE-SEA)
     * @return string
     */
    public function url()
    {
        return $this->page->url();
    }

    /**
     * Procesa los datos de la página o entrada en el menú
     * @param string $name
     * @param string $title
     * @param string $folder
     * @param boolean $shmenu
     * @param boolean $important
     */
    private function check_page($name, $title, $folder, $shmenu, $important, $icono, $subfolder)
    {
        if (!$icono) {
            $icono = 'bi bi-arrow-right-short';
        }
        /// cargamos los datos de la página o entrada del menú actual
        $this->page = new page(
            array(
                'name'         => $name,
                'title'        => $title,
                'folder'       => $folder,
                'subfolder'    => $subfolder,
                'version'      => $this->version(),
                'show_on_menu' => $shmenu,
                'important'    => $important,
                'orden'        => 100,
                'icono'        => $icono,
            )
        );

        /// ahora debemos comprobar si guardar o no
        if ($name !== 'controller') {
            $page = $this->page->get($name);
            if ($page) {
                /// la página ya existe ¿Actualizamos?
                if ($page->title != $title || $page->folder != $folder || $page->show_on_menu != $shmenu || $page->important != $important || $page->icono != $icono || $page->subfolder != $subfolder) {
                    $page->title        = $title;
                    $page->folder       = $folder;
                    $page->subfolder    = $subfolder;
                    $page->show_on_menu = $shmenu;
                    $page->important    = $important;
                    $page->icono        = $icono;
                    $page->save();
                }

                $this->page = $page;
            } else {
                /// la página no existe, guardamos.
                $this->page->save();
            }
        }
    }

    private function load_extensions()
    {
        $fsext = new extension();
        foreach ($fsext->all() as $ext) {
            /// Cargamos las extensiones para este controlador o para todos
            if (in_array($ext->to, [null, $this->class_name])) {
                $this->extensions[] = $ext;
            }
        }
    }

    /**
     * Carga el menú
     * @param boolean $reload TRUE si quieres recargar
     */
    protected function load_menu($reload = false)
    {
        $this->menu = $this->user->get_menu($reload);
    }

    /**
     * Devuelve TRUE si el usuario realmente tiene acceso a esta página
     * @return boolean
     */
    private function log_in()
    {
        $this->login_tools->log_in($this->user);
        if ($this->user->logged_on) {
            $this->core_log->set_user_nick($this->user->nick);
            $this->load_menu();
        }

        return $this->user->logged_on;
    }

    private function pre_private_core()
    {
        $this->query = filter_input_req('query');

        /// quitamos extensiones de páginas a las que el usuario no tenga acceso
        foreach ($this->extensions as $i => $value) {
            if ($value->type != 'config' && !$this->user->have_access_to($value->from)) {
                unset($this->extensions[$i]);
            }
        }
    }

    /**
     * Establece un almacén como predeterminado para este usuario.
     * @param string $cod el código del almacén
     */
    protected function save_codalmacen($cod)
    {
        setcookie('default_almacen', $cod, time() + JG_COOKIES_EXPIRE);
        $this->default_items->set_codalmacen($cod);
    }

    /**
     * Establece un impuesto (IVA) como predeterminado para este usuario.
     * @param string $cod el código del impuesto
     */
    protected function save_codimpuesto($cod)
    {
        setcookie('default_impuesto', $cod, time() + JG_COOKIES_EXPIRE);
        $this->default_items->set_codimpuesto($cod);
    }

    /**
     * Establece una forma de pago como predeterminada para este usuario.
     * @param string $cod el código de la forma de pago
     */
    protected function save_codpago($cod)
    {
        setcookie('default_formapago', $cod, time() + JG_COOKIES_EXPIRE);
        $this->default_items->set_codpago($cod);
    }

    /**
     * Establecemos los elementos por defecto, pero no se guardan.
     * Para guardarlos hay que usar las funciones controller::save_lo_que_sea().
     * La clase default_items sólo se usa para indicar valores
     * por defecto a los modelos.
     */
    private function set_default_items()
    {
        /// gestionamos la página de inicio
        if (filter_input(INPUT_GET, 'default_page')) {
            if (filter_input(INPUT_GET, 'default_page') == 'FALSE') {
                $this->default_items->set_default_page(null);
                $this->user->ppage = null;
            } else {
                $this->default_items->set_default_page($this->page->name);
                $this->user->ppage = $this->page->name;
            }

            $this->user->save();
        } else if (is_null($this->default_items->default_page())) {
            $this->default_items->set_default_page($this->user->ppage);
        }

        if (is_null($this->default_items->showing_page())) {
            $this->default_items->set_showing_page($this->page->name);
        }

        /*$this->default_items->set_codejercicio($this->empresa->codejercicio);

    if (filter_input(INPUT_COOKIE, 'default_almacen')) {
    $this->default_items->set_codalmacen(filter_input(INPUT_COOKIE, 'default_almacen'));
    } else {
    $this->default_items->set_codalmacen($this->empresa->codalmacen);
    }

    if (filter_input(INPUT_COOKIE, 'default_formapago')) {
    $this->default_items->set_codpago(filter_input(INPUT_COOKIE, 'default_formapago'));
    } else {
    $this->default_items->set_codpago($this->empresa->codpago);
    }

    if (filter_input(INPUT_COOKIE, 'default_impuesto')) {
    $this->default_items->set_codimpuesto(filter_input(INPUT_COOKIE, 'default_impuesto'));
    }

    $this->default_items->set_codpais($this->empresa->codpais);
    $this->default_items->set_codserie($this->empresa->codserie);
    $this->default_items->set_coddivisa($this->empresa->coddivisa);*/
    }

    public function paginas($urlp, $filtros, $cantidad, $offset)
    {
        $url     = $urlp . $filtros;
        $paginas = array();
        $i       = 0;
        $num     = 0;
        $actual  = 0;

        $totalp = ceil($cantidad / JG_ITEM_LIMIT);
        if ($totalp > 1) {
            for ($i = 0; $i < $totalp; $i++) {
                $paginas[$i] = array(
                    'url'    => $url . "&offset=" . ($i * JG_ITEM_LIMIT),
                    'num'    => $i + 1,
                    'actual' => ($num == $offset),
                );
                if ($num == $offset) {
                    $actual = $i;
                }
                $num += JG_ITEM_LIMIT;
            }

            foreach ($paginas as $key => $value) {
                $medio = intval($i / 2);
                if (($key > 0 && $key < $actual - 9 && $key != $medio) or ($key > $actual + 9 && $key < ($i - 1) && $key != $medio)) {
                    unset($paginas[$key]);
                }
            }
        }

        return $paginas;
    }
}
