<?php

class access extends model
{

    /**
     * Nick del usuario.
     * @var string 
     */
    public $nick;

    /**
     * Nombre de la página (nombre del controlador).
     * @var string
     */
    public $page;

    /**
     * Otorga permisos al usuario a eliminar elementos en la página.
     * @var boolean 
     */
    public $allow_delete;
    public $allow_modify;
    public $allow_download;

    public function __construct($data = FALSE)
    {
        parent::__construct('access');
        if ($data) {
            $this->nick = $data['nick'];
            $this->page = $data['page'];
            $this->allow_delete = $this->str2bool($data['allow_delete']);
            $this->allow_modify = $this->str2bool($data['allow_modify']);
            $this->allow_download = $this->str2bool($data['allow_download']);
        } else {
            $this->nick = NULL;
            $this->page = NULL;
            $this->allow_delete = FALSE;
            $this->allow_modify = FALSE;
            $this->allow_download = FALSE;
        }
    }

    protected function install()
    {
        new \users();
        new \page();
        
        return '';
    }

    public function exists()
    {
        if (is_null($this->page)) {
            return FALSE;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name
                . " WHERE nick = " . $this->var2str($this->nick)
                . " AND page = " . $this->var2str($this->page) . ";");
    }

    public function save()
    {
        if ($this->exists()) {
            $sql = "UPDATE " . $this->table_name . " SET allow_delete = " . $this->var2str($this->allow_delete)
                . " , allow_modify = " . $this->var2str($this->allow_modify)
                . " , allow_download = " . $this->var2str($this->allow_download)
                . " WHERE nick = " . $this->var2str($this->nick)
                . " AND page = " . $this->var2str($this->page) . ";";
        } else {
            $sql = "INSERT INTO " . $this->table_name . " (nick,page,allow_delete,allow_modify,allow_download) VALUES "
                . "(" . $this->var2str($this->nick)
                . "," . $this->var2str($this->page)
                . "," . $this->var2str($this->allow_delete)
                . "," . $this->var2str($this->allow_modify)
                . "," . $this->var2str($this->allow_download)
                . ");";
        }

        return $this->db->exec($sql);
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name
                . " WHERE nick = " . $this->var2str($this->nick)
                . " AND page = " . $this->var2str($this->page) . ";");
    }

    /**
     * Devuelve todos los permisos de acceso del usuario.
     * @param string $nick
     * @return \fs_access
     */
    public function all_from_nick($nick)
    {
        $accesslist = [];

        $access = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE nick = " . $this->var2str($nick) . ";");
        if ($access) {
            foreach ($access as $a) {
                $accesslist[] = new access($a);
            }
        }

        return $accesslist;
    }
}
