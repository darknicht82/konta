<?php

namespace GSC_Systems\model;

class articulos_unidades extends \model
{
    public $idartunidad;
    public $idempresa;
    public $idunidad;
    public $idarticulo;
    public $cantidad;
    public $precio;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('articulos_unidades');
        if ($data) {

            $this->idartunidad = $data['idartunidad'];
            $this->idempresa   = $data['idempresa'];
            $this->idunidad    = $data['idunidad'];
            $this->idarticulo  = $data['idarticulo'];
            $this->cantidad    = floatval($data['cantidad']);
            $this->precio      = floatval($data['precio']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];
            $this->medida            = $this->get_um();
        } else {
            $this->idartunidad = null;
            $this->idempresa   = null;
            $this->idunidad    = null;
            $this->idartunidad = null;
            $this->cantidad    = 0;
            $this->precio      = 0;
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
        new \unidades_medida();
        new \articulos();
        
        return "";
    }

    public function url()
    {
        return '';
    }

    public function get($idartunidad)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idartunidad = " . $this->var2str($idartunidad) . ";");
        if ($data) {
            return new \articulos_unidades($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idartunidad)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idartunidad = " . $this->var2str($this->idartunidad) . ";");
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
                . ", idunidad = " . $this->var2str($this->idunidad)
                . ", idarticulo = " . $this->var2str($this->idarticulo)
                . ", cantidad = " . $this->var2str($this->cantidad)
                . ", precio = " . $this->var2str($this->precio)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idartunidad = " . $this->var2str($this->idartunidad) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idunidad, idarticulo, cantidad, precio, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idunidad)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->cantidad)
                . "," . $this->var2str($this->precio)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }
            if ($this->beforeSave()) {
                if ($this->db->exec($sql)) {
                    if ($insert) {
                        $this->idartunidad = $this->db->lastval();
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
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idartunidad = " . $this->var2str($this->idartunidad) . ";");
        }

        return false;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idartunidad ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \articulos_unidades($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY idartunidad ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \articulos_unidades($p);
            }
        }

        return $list;
    }

    public function all_by_idarticulo($idarticulo, $idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idarticulo = " . $this->var2str($idarticulo) . " ORDER BY idunidad ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \articulos_unidades($p);
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
        if (!$this->idartunidad) {
            $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($this->idempresa) . " AND idunidad = " . $this->var2str($this->idunidad) . " AND idarticulo = " . $this->var2str($this->idarticulo);
            $data = $this->db->select_limit($sql, 1, 0);
            if ($data) {
                $this->new_advice('La Unidad de Medida ya se encuentra registrada.');
                return false;
            }
            $sql1  = "SELECT * FROM articulos WHERE idunidad = " . $this->var2str($this->idunidad) . " AND idarticulo = " . $this->var2str($this->idarticulo);
            $data1 = $this->db->select_limit($sql1, 1, 0);
            if ($data1) {
                $this->new_advice('La Unidad de Medida se encuentra registrada como principal del Artículo.');
                return false;
            }
        }
        return true;
    }

    public function get_um()
    {
        if ($this->idunidad) {
            $unidad = new unidades_medida();
            $un     = $unidad->get($this->idunidad);
            if ($un) {
                return $un->nombre;
            }
        }
        return '-';
    }
}
