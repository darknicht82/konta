<?php

class relation extends model
{

    public $id;
    public $table1;
    public $id1;
    public $table2;
    public $id2;
    public $return_url;

    public function __construct($data = FALSE)
    {
        parent::__construct('fs_relations');
        if ($data) {
            $this->id = $this->intval($data['id']);
            $this->table1 = $data['table1'];
            $this->id1 = $data['id1'];
            $this->table2 = $data['table2'];
            $this->id2 = $data['id2'];
            $this->return_url = $data['return_url'];
        } else {
            $this->id = NULL;
            $this->table1 = NULL;
            $this->id1 = NULL;
            $this->table2 = NULL;
            $this->id2 = NULL;
            $this->return_url = NULL;
        }
    }

    protected function install()
    {
        return '';
    }

    public function get($id)
    {
        $data = $this->db->select("SELECT * FROM fs_relations WHERE id = " . $this->var2str($id) . ";");
        if ($data) {
            return new relation($data[0]);
        }

        return FALSE;
    }

    public function exists()
    {
        if (is_null($this->id)) {
            return FALSE;
        }

        return $this->db->select("SELECT * FROM fs_relations WHERE id = " . $this->var2str($this->id) . ";");
    }

    public function save()
    {
        if ($this->exists()) {
            $sql = "UPDATE fs_relations SET table1 = " . $this->var2str($this->table1)
                . ", id1 = " . $this->var2str($this->id1)
                . ", table2 = " . $this->var2str($this->table2)
                . ", id2 = " . $this->var2str($this->id2)
                . ", return_url = " . $this->var2str($this->return_url)
                . "  WHERE id = " . $this->var2str($this->id) . ";";

            return $this->db->exec($sql);
        }

        $sql = "INSERT INTO fs_relations (table1,id1,table2,id2,return_url) VALUES "
            . "(" . $this->var2str($this->table1)
            . "," . $this->var2str($this->id1)
            . "," . $this->var2str($this->table2)
            . "," . $this->var2str($this->id2)
            . "," . $this->var2str($this->return_url) . ");";

        if ($this->db->exec($sql)) {
            $this->id = $this->db->lastval();
            return TRUE;
        }

        return FALSE;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM fs_relations WHERE id = " . $this->var2str($this->id) . ";");
    }

    public function all_for($table, $id)
    {
        $rlist = [];
        $sql = "SELECT * FROM fs_relations WHERE (table1 = " . $this->var2str($table)
            . " AND id1 = " . $this->var2str($id) . ") OR (table2 = " . $this->var2str($table)
            . " AND id2 = " . $this->var2str($id) . ") ORDER BY id DESC;";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $d) {
                $rlist[] = new relation($d);
            }
        }

        return $rlist;
    }
}
