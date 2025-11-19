<?php

namespace GSC_Systems\model;

class articulos_cuadre extends \model
{
    public $idcuadreart;
    public $idempresa;
    public $idcaja;
    public $idcierre;
    public $idarticulo;
    public $stockfinal;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('articulos_cuadre');
        if ($data) {

            $this->idcuadreart = $data['idcuadreart'];
            $this->idempresa   = $data['idempresa'];
            $this->idcaja      = $data['idcaja'];
            $this->idcierre    = $data['idcierre'];
            $this->idarticulo  = $data['idarticulo'];
            $this->stockfinal  = $data['stockfinal'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idcuadreart = null;
            $this->idempresa   = null;
            $this->idcaja      = null;
            $this->idcierre    = null;
            $this->idarticulo  = null;
            $this->stockfinal  = 0;
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
        new \cajas();
        new \establecimiento();
        new \articulos();

        return "";
    }

    public function url()
    {
        return '';
    }

    public function get_articulo()
    {
        if ($this->idarticulo) {
            $art0 = new \articulos();
            $art  = $art0->get($this->idarticulo);
            if ($art) {
                return $art;
            }
        }
        return false;
    }

    public function get_cierre()
    {
        if ($this->idcierre) {
            $cierre0 = new \cierres();
            $cierre  = $cierre0->get($this->idcierre);
            if ($cierre) {
                return $cierre;
            }
        }
        return false;
    }

    public function getStockFecha($fecha_hasta = '')
    {
        $stockfin = 0;
        if ($this->idarticulo) {
            if ($cierre = $this->get_cierre()) {
                $trans = new \trans_inventario();
                if ($fecha_hasta == '') {
                    $fecha_hasta = $cierre->cierre;
                }
                $stock = $trans->existenciasArticulos($this->idempresa, $this->idarticulo, '', $cierre->idestablecimiento, '', '', '', $fecha_hasta);
                if ($stock) {
                    $stockfin = $stock[0]['stock'];
                }
            }

        }
        return $stockfin;
    }

    public function get($idcuadreart)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcuadreart = " . $this->var2str($idcuadreart) . ";");
        if ($data) {
            return new \articulos_cuadre($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idcuadreart)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcuadreart = " . $this->var2str($this->idcuadreart) . ";");
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
                . ", idcaja = " . $this->var2str($this->idcaja)
                . ", idcierre = " . $this->var2str($this->idcierre)
                . ", idarticulo = " . $this->var2str($this->idarticulo)
                . ", stockfinal = " . $this->var2str($this->stockfinal)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idcuadreart = " . $this->var2str($this->idcuadreart) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idcaja, idcierre, idarticulo, stockfinal, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idcaja)
                . "," . $this->var2str($this->idcierre)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->stockfinal)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idcuadreart = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idcuadreart = " . $this->var2str($this->idcuadreart) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idcuadreart ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \articulos_cuadre($p);
            }
        }

        return $list;
    }

    public function all_by_idcierre($idcierre)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcierre = " . $this->var2str($idcierre) . " ORDER BY idcuadreart ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \articulos_cuadre($p);
            }
        }

        return $list;
    }
}
