<?php

namespace GSC_Systems\model;

class stocks extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('stocks');
        if ($data) {

            $this->idstock           = $data['idstock'];
            $this->idempresa         = $data['idempresa'];
            $this->idestablecimiento = $data['idestablecimiento'];
            $this->idarticulo        = $data['idarticulo'];
            $this->stock             = floatval($data['stock']);
            $this->ubicacion         = $data['ubicacion'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idstock           = null;
            $this->idempresa         = null;
            $this->idestablecimiento = null;
            $this->idarticulo        = null;
            $this->stock             = null;
            $this->ubicacion         = null;
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
        new \articulos();

        return "";
    }

    public function url()
    {
        return 'index.php?page=configuracion_articulos';
    }

    public function get($idstock)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idstock = " . $this->var2str($idstock) . ";");
        if ($data) {
            return new \stocks($data[0]);
        }

        return false;
    }

    public function get_by_idestab_idart($idestablecimiento, $idarticulo)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idarticulo = " . $this->var2str($idarticulo) . " AND idestablecimiento = " . $this->var2str($idestablecimiento) . ";");
        if ($data) {
            return new \stocks($data[0]);
        }

        return 0;
    }

    public function get_establecimiento()
    {
        if ($this->idestablecimiento) {
            $establecimiento = new \establecimiento();
            $esta = $establecimiento->get($this->idestablecimiento);
            if ($esta) {
                return $esta->nombre;
            }
        }

        return '-';
    }

    public function exists()
    {
        if (is_null($this->idstock)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idstock = " . $this->var2str($this->idstock) . ";");
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
                . ", idestablecimiento = " . $this->var2str($this->idestablecimiento)
                . ", idarticulo = " . $this->var2str($this->idarticulo)
                . ", stock = " . $this->var2str($this->stock)
                . ", ubicacion = " . $this->var2str($this->ubicacion)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idstock = " . $this->var2str($this->idstock) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idestablecimiento, idarticulo, stock, ubicacion, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idestablecimiento)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->stock)
                . "," . $this->var2str($this->ubicacion)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idstock = $this->db->lastval();
                }
                return true;
            } else {
                echo $sql;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idstock = " . $this->var2str($this->idstock) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idestablecimiento ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \stocks($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY idestablecimiento ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \stocks($p);
            }
        }

        return $list;
    }

    public function update_by_idempresa($idempresa)
    {
        return $this->db->exec("UPDATE " . $this->table_name . " SET stock = 0 WHERE idempresa = " . $this->var2str($idempresa));
    }

    public function all_by_idarticulo($idarticulo)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idarticulo = " . $this->var2str($idarticulo) . " ORDER BY idestablecimiento ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \stocks($p);
            }
        }

        return $list;
    }
}
