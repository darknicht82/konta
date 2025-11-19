<?php

namespace GSC_Systems\model;

class mov_cajas extends \model
{
    public $idmovcaja;
    public $idempresa;
    public $idcierre;
    public $nombre;
    public $tipo;
    public $valor;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('mov_cajas');

        if ($data) {

            $this->idmovcaja = $data['idmovcaja'];
            $this->idempresa = $data['idempresa'];
            $this->idcierre  = $data['idcierre'];
            $this->nombre    = $data['nombre'];
            $this->tipo      = $data['tipo'];
            $this->valor     = floatval($data['valor']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idmovcaja = null;
            $this->idempresa = null;
            $this->idcierre  = null;
            $this->nombre    = null;
            $this->tipo      = null;
            $this->valor     = 0;
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
        new \cierres();
        
        return "";
    }

    public function url()
    {
        return 'index.php?page=lista_mov_cajas';
    }

    public function get($idmovcaja)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idmovcaja = " . $this->var2str($idmovcaja) . ";");
        if ($data) {
            return new \mov_cajas($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idmovcaja)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idmovcaja = " . $this->var2str($this->idmovcaja) . ";");
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
                . ", idcierre = " . $this->var2str($this->idcierre)
                . ", nombre = " . $this->var2str($this->nombre)
                . ", tipo = " . $this->var2str($this->tipo)
                . ", valor = " . $this->var2str($this->valor)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idmovcaja = " . $this->var2str($this->idmovcaja) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idcierre, nombre, tipo, valor, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idcierre)
                . "," . $this->var2str($this->nombre)
                . "," . $this->var2str($this->tipo)
                . "," . $this->var2str($this->valor)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idmovcaja = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idmovcaja = " . $this->var2str($this->idmovcaja) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY nombre DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \mov_cajas($p);
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
                $list[] = new \mov_cajas($p);
            }
        }

        return $list;
    }

    public function get_by_idempresa_caja($idempresa, $idcierre)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcierre = " . $this->var2str($idcierre) . " AND idempresa = " . $this->var2str($idempresa) . ";");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \mov_cajas($p);
            }
        }
        return $list;
    }

    public function get_caja()
    {
        if ($this->idcierre) {
            $cierre0 = new \cierres();
            $cierre  = $cierre0->get($this->idcierre);
            if ($cierre) {
                
                if ($caja = $cierre->get_caja()) {
                    return $caja;
                }
            }
        }
        return '';
    }
}
