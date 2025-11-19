<?php

namespace GSC_Systems\model;

class impuestos extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('impuestos');
        if ($data) {

            $this->idimpuesto = $data['idimpuesto'];
            $this->nombre     = $data['nombre'];
            $this->codigo     = $data['codigo'];
            $this->porcentaje = $data['porcentaje'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idimpuesto = null;
            $this->nombre     = null;
            $this->codigo     = null;
            $this->porcentaje = null;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;
        }
    }

    public function install()
    {
        $sql = "INSERT INTO ".$this->table_name." (nombre, codigo, porcentaje, fec_creacion, nick_creacion) VALUES 
            ('No Objeto de IVA', 'IVANO', '0', '".date('Y-m-d')."', 'admin'),
            ('IVA 0%', 'IVA0', '0', '".date('Y-m-d')."', 'admin'),
            ('IVA 12%', 'IVA12', '12', '".date('Y-m-d')."', 'admin'),
            ('IVA Excento', 'IVAEX', '0', '".date('Y-m-d')."', 'admin');";
        return $sql;
    }

    public function url()
    {
        return 'index.php?page=lista_impuestos';
    }

    public function get($idimpuesto)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idimpuesto = " . $this->var2str($idimpuesto) . ";");
        if ($data) {
            return new \impuestos($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idimpuesto)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idimpuesto = " . $this->var2str($this->idimpuesto) . ";");
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
                $sql = "UPDATE " . $this->table_name . " SET nombre = " . $this->var2str($this->nombre)
                . ", codigo = " . $this->var2str($this->codigo)
                . ", porcentaje = " . $this->var2str($this->porcentaje)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idimpuesto = " . $this->var2str($this->idimpuesto) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (nombre, codigo, porcentaje, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->nombre)
                . "," . $this->var2str($this->codigo)
                . "," . $this->var2str($this->porcentaje)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idimpuesto = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idimpuesto = " . $this->var2str($this->idimpuesto) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY porcentaje ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \impuestos($p);
            }
        }

        return $list;
    }

    public function get_by_porcentaje($porcentaje)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE porcentaje = " . $this->var2str($porcentaje) . ";");
        if ($data) {
            return new \impuestos($data[0]);
        }

        return false;
    }

    public function get_by_codigo($codigo)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codigo = " . $this->var2str($codigo) . ";");
        if ($data) {
            return new \impuestos($data[0]);
        }

        return false;
    }
}
