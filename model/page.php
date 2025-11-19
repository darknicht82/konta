<?php

class page extends model
{

    /**
     * Clave primaria. Varchar (30).
     * Nombre de la página (controlador).
     * @var string
     */
    public $name;
    public $title;

    /**
     * Nombre del menú donde queremos colocar el acceso.
     * @var string
     */
    public $folder;
    public $subfolder;
    public $version;

    /**
     * FALSE -> ocultar en el menú.
     * @var boolean
     */
    public $show_on_menu;
    public $exists;
    public $enabled;
    public $extra_url;

    /**
     * Cuando un usuario no tiene asignada una página por defecto, se selecciona
     * la primera página importante a la que tiene acceso.
     */
    public $important;
    public $orden;
    public $icono;

    public function __construct($data = false)
    {
        parent::__construct('pages');
        if ($data) {
            $this->name      = $data['name'];
            $this->title     = $data['title'];
            $this->folder    = $data['folder'];
            $this->subfolder = $data['subfolder'];

            $this->version = null;
            if (isset($data['version'])) {
                $this->version = $data['version'];
            }

            $this->show_on_menu = $this->str2bool($data['show_on_menu']);
            $this->important    = $this->str2bool($data['important']);

            $this->orden = 100;
            if (isset($data['orden'])) {
                $this->orden = $this->intval($data['orden']);
            }
            $this->icono = $data['icono'];
        } else {
            $this->name         = null;
            $this->title        = null;
            $this->folder       = null;
            $this->subfolder    = null;
            $this->version      = null;
            $this->show_on_menu = true;
            $this->important    = false;
            $this->orden        = 100;
            $this->icono        = 'bi bi-arrow-right-short';
        }

        $this->exists    = false;
        $this->enabled   = false;
        $this->extra_url = '';
    }

    public function __clone()
    {
        $page               = new page();
        $page->name         = $this->name;
        $page->title        = $this->title;
        $page->folder       = $this->folder;
        $page->subfolder    = $this->subfolder;
        $page->version      = $this->version;
        $page->show_on_menu = $this->show_on_menu;
        $page->important    = $this->important;
        $page->orden        = $this->orden;
        $page->icono        = $this->icono;
    }

    protected function install()
    {
        $this->clean_cache();
        return "INSERT INTO " . $this->table_name . " (name,title,folder, subfolder,version,show_on_menu, icono)
         VALUES ('admin_home','Panel de Control','Administrador', NULL,NULL,TRUE, 'bi bi-arrow-right-short');";
    }

    public function url()
    {
        if (is_null($this->name)) {
            return 'index.php?page=admin_home';
        }

        return 'index.php?page=' . $this->name . $this->extra_url;
    }

    public function is_default()
    {
        return ($this->name == $this->default_items->default_page());
    }

    public function showing()
    {
        return ($this->name == $this->default_items->showing_page());
    }

    public function exists()
    {
        if (is_null($this->name)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE name = " . $this->var2str($this->name) . ";");
    }

    public function get($name)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE name = " . $this->var2str($name) . ";");
        if ($data) {
            return new page($data[0]);
        }

        return false;
    }

    public function save()
    {
        $this->clean_cache();

        if ($this->exists()) {
            $sql = "UPDATE " . $this->table_name . " SET title = " . $this->var2str($this->title)
            . ", folder = " . $this->var2str($this->folder)
            . ", subfolder = " . $this->var2str($this->subfolder)
            . ", version = " . $this->var2str($this->version)
            . ", show_on_menu = " . $this->var2str($this->show_on_menu)
            . ", important = " . $this->var2str($this->important)
            . ", orden = " . $this->var2str($this->orden)
            . ", icono = " . $this->var2str($this->icono)
            . "  WHERE name = " . $this->var2str($this->name) . ";";
        } else {
            $sql = "INSERT INTO " . $this->table_name . " (name,title,folder, subfolder,version,show_on_menu,important,orden,icono) VALUES "
            . "(" . $this->var2str($this->name)
            . "," . $this->var2str($this->title)
            . "," . $this->var2str($this->folder)
            . "," . $this->var2str($this->subfolder)
            . "," . $this->var2str($this->version)
            . "," . $this->var2str($this->show_on_menu)
            . "," . $this->var2str($this->important)
            . "," . $this->var2str($this->orden)
            . "," . $this->var2str($this->icono)
                . ");";
        }

        return $this->db->exec($sql);
    }

    public function delete()
    {
        $this->clean_cache();
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE name = " . $this->var2str($this->name) . ";");
    }

    private function clean_cache()
    {
        $this->cache->delete('m_page_all');
    }

    /**
     * Devuelve todas las páginas o entradas del menú
     * @return \page
     */
    public function all()
    {
        $pages = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY lower(folder) ASC, lower(CONCAT(subfolder, '', title)) ASC;");
        if ($pages) {
            foreach ($pages as $p) {
                $pagelist[] = new page($p);
            }
        }

        return $pagelist;
    }

    public function all_to_supervisor($plan_basico)
    {
        if (complemento_exists('facturador') && complemento_exists('pagosycobros')) {
            //Plan Contador
            $pages = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE name NOT IN ('admin_home', 'admin_info', 'admin_rol', 'admin_orden_menu', 'lista_impuestos', 'lista_empresas', 'lista_documentos', 'lista_sustentossri', 'admin_procesos', 'cargas_masivas') ORDER BY lower(folder) ASC, lower(CONCAT(subfolder, '', title)) ASC;");
        } else if (complemento_exists('facturador')) {
            //Facturador
            if ($plan_basico) {
                $pages = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE name NOT IN ('admin_home', 'admin_info', 'admin_rol', 'admin_orden_menu', 'lista_impuestos', 'lista_empresas', 'lista_documentos', 'lista_formaspago', 'lista_sustentossri', 'admin_procesos', 'cargas_masivas', 'lista_articulos', 'ver_articulo', 'lista_movimientos_stock', 'ver_movimiento_stock', 'lista_regularizaciones_stock', 'ver_regularizacion_stock', 'crear_movimiento', 'crear_regularizacion', 'bandeja_sri', 'configuracion_articulos', 'informes_sri', 'informes_articulos') ORDER BY lower(folder) ASC, lower(CONCAT(subfolder, '', title)) ASC;");
            } else {
                $pages = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE name NOT IN ('admin_home', 'admin_info', 'admin_rol', 'admin_orden_menu', 'lista_impuestos', 'lista_empresas', 'lista_documentos', 'lista_formaspago', 'lista_sustentossri', 'admin_procesos', 'cargas_masivas') ORDER BY lower(folder) ASC, lower(CONCAT(subfolder, '', title)) ASC;");
            }
        } else {
            //Puntos de Venta
            $pages = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE name NOT IN ('admin_home', 'admin_info', 'admin_rol', 'admin_orden_menu', 'lista_impuestos', 'admin_procesos', 'cargas_masivas') ORDER BY lower(folder) ASC, lower(CONCAT(subfolder, '', title)) ASC;");
        }
        if ($pages) {
            foreach ($pages as $p) {
                $pagelist[] = new page($p);
            }
        }

        return $pagelist;
    }
}
