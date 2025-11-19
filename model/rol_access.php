<?php

class rol_access extends model
{

    public $codrol;
    public $page;
    public $allow_delete;
    public $allow_modify;
    public $allow_download;

    public function __construct($data = FALSE)
    {
        parent::__construct('roles_access');
        if ($data) {
            $this->codrol = $data['codrol'];
            $this->page = $data['page'];
            $this->allow_delete = $this->str2bool($data['allow_delete']);
            $this->allow_modify = $this->str2bool($data['allow_modify']);
            $this->allow_download = $this->str2bool($data['allow_download']);
        } else {
            $this->codrol = NULL;
            $this->page = NULL;
            $this->allow_delete = FALSE;
            $this->allow_modify = FALSE;
            $this->allow_download = FALSE;
        }
    }

    protected function install()
    {
        return '';
    }

    public function exists()
    {
        if (is_null($this->codrol)) {
            return FALSE;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name
                . " WHERE codrol = " . $this->var2str($this->codrol)
                . " AND page = " . $this->var2str($this->page) . ";");
    }

    public function save()
    {
        if ($this->exists()) {
            $sql = "UPDATE " . $this->table_name . " SET allow_delete = " . $this->var2str($this->allow_delete)
                . " , allow_modify = " . $this->var2str($this->allow_modify)
                . " , allow_download = " . $this->var2str($this->allow_download)
                . " WHERE codrol = " . $this->var2str($this->codrol)
                . " AND page = " . $this->var2str($this->page) . ";";
        } else {
            $sql = "INSERT INTO " . $this->table_name . " (codrol, page, allow_delete, allow_modify, allow_download) VALUES "
                . "(" . $this->var2str($this->codrol)
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
                . " WHERE codrol = " . $this->var2str($this->codrol)
                . " AND page = " . $this->var2str($this->page) . ";");
    }

    public function all_from_rol($codrol)
    {
        $accesslist = [];

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codrol = " . $this->var2str($codrol) . ";");
        if ($data) {
            foreach ($data as $a) {
                $accesslist[] = new rol_access($a);
            }
        }

        return $accesslist;
    }
}
