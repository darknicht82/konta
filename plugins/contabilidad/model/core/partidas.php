<?php

namespace GSC_Systems\model;

class partidas extends \model
{
    public $idpartida;
    public $idempresa;
    public $idasiento;
    public $idsubcuenta;
    public $debe;
    public $haber;
    public $idarticulo;
    public $idimpuesto;
    public $idfacturacli;
    public $idtranspago;
    public $idtranscobro;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('partidas');
        if ($data) {

            $this->idpartida    = $data['idpartida'];
            $this->idempresa    = $data['idempresa'];
            $this->idasiento    = $data['idasiento'];
            $this->idsubcuenta  = $data['idsubcuenta'];
            $this->debe         = $data['debe'];
            $this->haber        = $data['haber'];
            $this->idarticulo   = $data['idarticulo'];
            $this->idimpuesto   = $data['idimpuesto'];
            $this->idfacturacli = $data['idfacturacli'];
            $this->idtranspago  = $data['idtranspago'];
            $this->idtranscobro = $data['idtranscobro'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idpartida    = null;
            $this->idempresa    = null;
            $this->idasiento    = null;
            $this->idsubcuenta  = null;
            $this->debe         = null;
            $this->haber        = null;
            $this->idarticulo   = null;
            $this->idimpuesto   = null;
            $this->idfacturacli = null;
            $this->idtranspago  = null;
            $this->idtranscobro = null;
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
        new \asientos();
        new \articulos();
        new \facturascli();
        new \trans_pagos();
        new \trans_cobros();

        return "";
    }

    public function url()
    {
        if ($this->idpartida) {
            return 'index.php?page=ver_ejercicio&id=' . $this->idpartida;
        }
        return 'index.php?page=lista_partidas';
    }

    public function get($idpartida)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idpartida = " . $this->var2str($idpartida) . ";");
        if ($data) {
            return new \partidas($data[0]);
        }

        return false;
    }

    public function get_asiento()
    {
        $asi0 = new \asientos();
        $asi  = $asi0->get($this->idasiento);
        if ($asi) {
            return $asi;
        }
        return false;
    }

    public function exists()
    {
        if (is_null($this->idpartida)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idpartida = " . $this->var2str($this->idpartida) . ";");
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
                . ", idasiento = " . $this->var2str($this->idasiento)
                . ", idsubcuenta = " . $this->var2str($this->idsubcuenta)
                . ", debe = " . $this->var2str($this->debe)
                . ", haber = " . $this->var2str($this->haber)
                . ", idarticulo = " . $this->var2str($this->idarticulo)
                . ", idimpuesto = " . $this->var2str($this->idimpuesto)
                . ", idfacturacli = " . $this->var2str($this->idfacturacli)
                . ", idtranspago = " . $this->var2str($this->idtranspago)
                . ", idtranscobro = " . $this->var2str($this->idtranscobro)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idpartida = " . $this->var2str($this->idpartida) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idasiento, idsubcuenta, debe, haber, idarticulo, idimpuesto, idfacturacli, idtranspago, idtranscobro, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idasiento)
                . "," . $this->var2str($this->idsubcuenta)
                . "," . $this->var2str($this->debe)
                . "," . $this->var2str($this->haber)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->idimpuesto)
                . "," . $this->var2str($this->idfacturacli)
                . "," . $this->var2str($this->idtranspago)
                . "," . $this->var2str($this->idtranscobro)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idpartida = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        if (!$this->beforeDelete()) {
            return false;
        }
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idpartida = " . $this->var2str($this->idpartida) . ";");
    }

    private function beforeDelete()
    {
        return true;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idsubcuenta DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \partidas($p);
            }
        }

        return $list;
    }

    public function all_by_idasiento($idasiento)
    {
        $list = array();
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idasiento = " . $this->var2str($idasiento) . " ORDER BY debe DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \partidas($p);
            }
        }

        return $list;
    }
}
