<?php

namespace GSC_Systems\model;

class unidades_medida extends \model
{
    public $idunidad;
    public $idempresa;
    public $codigo;
    public $nombre;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('unidades_medida');
        if ($data) {

            $this->idunidad  = $data['idunidad'];
            $this->idempresa = $data['idempresa'];
            $this->codigo    = $data['codigo'];
            $this->nombre    = $data['nombre'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idunidad  = null;
            $this->idempresa = null;
            $this->codigo    = null;
            $this->nombre    = null;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;
        }
    }

    public function install()
    {
        new \empresa();
        
        return "";
    }

    public function url()
    {
        return 'index.php?page=lista_unidades_medida';
    }

    public function get($idunidad)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idunidad = " . $this->var2str($idunidad) . ";");
        if ($data) {
            return new \unidades_medida($data[0]);
        }

        return false;
    }

    public function get_by_codigo($codigo, $idempresa)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND codigo = " . $this->var2str($codigo) . ";");
        if ($data) {
            return new \unidades_medida($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idunidad)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idunidad = " . $this->var2str($this->idunidad) . ";");
    }

    public function test()
    {
        $status = true;

        return $status;
    }

    public function save()
    {
        if ($this->test()) {
            $insert = false;
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET idempresa = " . $this->var2str($this->idempresa)
                . ", codigo = " . $this->var2str($this->codigo)
                . ", nombre = " . $this->var2str($this->nombre)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idunidad = " . $this->var2str($this->idunidad) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, codigo, nombre, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->codigo)
                . "," . $this->var2str($this->nombre)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idunidad = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        if ($this->beforeDelete()) {
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idunidad = " . $this->var2str($this->idunidad) . ";");
        }

        return false;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY codigo ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \unidades_medida($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY codigo ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \unidades_medida($p);
            }
        }

        return $list;
    }

    private function beforeDelete()
    {
        $sql = "SELECT * FROM articulos WHERE idunidad = " . $this->idunidad;
        $data = $this->db->select_limit($sql, 1, 0);
        if ($data) {
            return false;
        }

        $sql1 = "SELECT * FROM articulos_unidades WHERE idunidad = " . $this->idunidad;
        $data1 = $this->db->select_limit($sql1, 1, 0);
        if ($data1) {
            return false;
        }

        return true;
    }
}
