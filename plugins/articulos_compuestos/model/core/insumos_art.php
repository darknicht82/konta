<?php

namespace GSC_Systems\model;

class insumos_art extends \model
{
    public $idinsumo;
    public $idempresa;
    public $idarticulocomp;
    public $idarticulo;
    public $cantidad;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('insumos_art');
        if ($data) {

            $this->idinsumo       = $data['idinsumo'];
            $this->idempresa      = $data['idempresa'];
            $this->idarticulocomp = $data['idarticulocomp'];
            $this->idarticulo     = $data['idarticulo'];
            $this->cantidad       = floatval($data['cantidad']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];
        } else {
            $this->idinsumo       = null;
            $this->idempresa      = null;
            $this->idarticulo     = null;
            $this->idarticulocomp = null;
            $this->cantidad       = 0;
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
        new \articulos();

        return "";
    }

    public function url()
    {
        return '';
    }

    public function get($idinsumo)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idinsumo = " . $this->var2str($idinsumo) . ";");
        if ($data) {
            return new \insumos_art($data[0]);
        }

        return false;
    }

    public function getInsumo($idarticulo, $idarticulocomp)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idarticulocomp = " . $this->var2str($idarticulocomp) . " AND idarticulo = " . $this->var2str($idarticulo) . ";");
        if ($data) {
            return new \insumos_art($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idinsumo)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idinsumo = " . $this->var2str($this->idinsumo) . ";");
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
                . ", idarticulocomp = " . $this->var2str($this->idarticulocomp)
                . ", idarticulo = " . $this->var2str($this->idarticulo)
                . ", cantidad = " . $this->var2str($this->cantidad)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idinsumo = " . $this->var2str($this->idinsumo) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idarticulocomp, idarticulo, cantidad, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idarticulocomp)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->cantidad)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }
            if ($this->beforeSave()) {
                if ($this->db->exec($sql)) {
                    if ($insert) {
                        $this->idinsumo = $this->db->lastval();
                    }
                    return true;
                }
            }
        }

        return false;
    }

    public function delete()
    {
        if ($this->beforeDelete()) {
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idinsumo = " . $this->var2str($this->idinsumo) . ";");
        }

        return false;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idinsumo ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \insumos_art($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY idinsumo ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \insumos_art($p);
            }
        }

        return $list;
    }

    public function all_by_idarticulocomp($idarticulocomp, $idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idarticulocomp = " . $this->var2str($idarticulocomp) . " ORDER BY idarticulocomp ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \insumos_art($p);
            }
        }

        return $list;
    }

    private function beforeDelete()
    {
        $sql = '';
        return true;
    }

    private function beforeSave()
    {
        return true;
    }

    public function get_articulo()
    {
        if ($this->idarticulo) {
            $articulo = new articulos();
            $art      = $articulo->get($this->idarticulo);
            if ($art) {
                return $art;
            }
        }
        return false;
    }
}
