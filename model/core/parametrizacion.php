<?php

namespace GSC_Systems\model;

class parametrizacion extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('parametrizacion');
        if ($data) {

            $this->idparametrizacion = $data['idparametrizacion'];
            $this->idempresa         = $data['idempresa'];
            $this->codigo            = $data['codigo'];
            $this->valor             = $data['valor'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idparametrizacion = null;
            $this->idempresa         = null;
            $this->codigo            = null;
            $this->valor             = null;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;
        }
    }

    public function install()
    {
        $sql = '';

        return $sql;
    }

    public function url()
    {
        return 'index.php?page=parametrizaciones';
    }

    public function get($idparametrizacion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idparametrizacion = " . $this->var2str($idparametrizacion) . ";");
        if ($data) {
            return new \parametrizacion($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idparametrizacion)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idparametrizacion = " . $this->var2str($this->idparametrizacion) . ";");
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
                . ", valor = " . $this->var2str($this->valor)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idparametrizacion = " . $this->var2str($this->idparametrizacion) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, codigo, valor, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->codigo)
                . "," . $this->var2str($this->valor)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idparametrizacion = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idparametrizacion = " . $this->var2str($this->idparametrizacion) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY codigo DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \parametrizacion($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY codigo DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \parametrizacion($p);
            }
        }
        return $list;
    }

    public function all_by_codigo($idempresa, $codigo)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND codigo = " . $this->var2str($codigo) . " ORDER BY codigo DESC;");
        if ($data) {
            return new \parametrizacion($data[0]);
        }
        return false;
    }
}
