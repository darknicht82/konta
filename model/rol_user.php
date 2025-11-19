<?php

class rol_user extends model
{

    public $codrol;
    public $nick;

    public function __construct($data = FALSE)
    {
        parent::__construct('roles_users');
        if ($data) {
            $this->codrol = $data['codrol'];
            $this->nick = $data['nick'];
        } else {
            $this->codrol = NULL;
            $this->nick = NULL;
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
                . " AND nick = " . $this->var2str($this->nick) . ";");
    }

    public function save()
    {
        if ($this->exists()) {
            return TRUE;
        }

        $sql = "INSERT INTO " . $this->table_name . " (codrol, nick) VALUES "
            . "(" . $this->var2str($this->codrol)
            . "," . $this->var2str($this->nick) . ");";

        return $this->db->exec($sql);
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name .
                " WHERE codrol = " . $this->var2str($this->codrol) .
                " AND nick = " . $this->var2str($this->nick) . ";");
    }

    public function all_from_rol($codrol)
    {
        $accesslist = [];

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codrol = " . $this->var2str($codrol) . ";");
        if ($data) {
            foreach ($data as $a) {
                $accesslist[] = new rol_user($a);
            }
        }

        return $accesslist;
    }
}
