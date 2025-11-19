<?php

namespace GSC_Systems\model;

class cajas extends \model
{
    public $idcaja;
    public $idempresa;
    public $nombre;
    public $idestablecimiento;
    public $usuarios;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('cajas');

        if ($data) {

            $this->idcaja            = $data['idcaja'];
            $this->idempresa         = $data['idempresa'];
            $this->nombre            = $data['nombre'];
            $this->idestablecimiento = $data['idestablecimiento'];
            $this->inicial = floatval($data['inicial']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idcaja            = null;
            $this->idempresa         = null;
            $this->nombre            = null;
            $this->idestablecimiento = null;
            $this->inicial = 0;
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
        new \establecimiento();
        
        return "";
    }

    public function url()
    {
        return 'index.php?page=lista_cajas';
    }

    public function get($idcaja)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcaja = " . $this->var2str($idcaja) . ";");
        if ($data) {
            return new \cajas($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idcaja)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcaja = " . $this->var2str($this->idcaja) . ";");
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
                . ", nombre = " . $this->var2str($this->nombre)
                . ", idestablecimiento = " . $this->var2str($this->idestablecimiento)
                . ", inicial = " . $this->var2str($this->inicial)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idcaja = " . $this->var2str($this->idcaja) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, nombre, idestablecimiento, inicial, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->nombre)
                . "," . $this->var2str($this->idestablecimiento)
                . "," . $this->var2str($this->inicial)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idcaja = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idcaja = " . $this->var2str($this->idcaja) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY nombre DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \cajas($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY nombre ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \cajas($p);
            }
        }

        return $list;
    }

    public function get_by_idempresa_nombre($idempresa, $nombre)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE nombre = " . $this->var2str($nombre) . " AND idempresa = " . $this->var2str($idempresa) . ";");
        if ($data) {
            return new \cajas($data[0]);
        }

        return false;
    }

    public function cajasPendientes($idempresa)
    {
        $list = array();

        $sql = "SELECT * FROM ".$this->table_name." WHERE idempresa = ".$this->var2str($idempresa)." AND idcaja NOT IN (SELECT idcaja FROM cierres WHERE idempresa = ".$this->var2str($idempresa)." AND cierre IS NULL) ORDER BY nombre ASC;";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \cajas($p);
            }
        }
        return $list;
    }
}
