<?php

namespace GSC_Systems\model;

class ejercicios extends \model
{
    public $idejercicio;
    public $idempresa;
    public $nombre;
    public $fec_inicio;
    public $fec_fin;
    public $idasientoi;
    public $idasientoc;
    public $abierto;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('ejercicios');
        if ($data) {

            $this->idejercicio = $data['idejercicio'];
            $this->idempresa   = $data['idempresa'];
            $this->nombre      = $data['nombre'];
            $this->fec_inicio  = $data['fec_inicio'] ? Date('d-m-Y', strtotime($data['fec_inicio'])) : null;
            $this->fec_fin     = $data['fec_fin'] ? Date('d-m-Y', strtotime($data['fec_fin'])) : null;
            $this->idasientoi  = $data['idasientoi'];
            $this->idasientoc  = $data['idasientoc'];
            $this->abierto     = $this->str2bool($data['abierto']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idejercicio = null;
            $this->idempresa   = null;
            $this->nombre      = null;
            $this->fec_inicio  = null;
            $this->fec_fin     = null;
            $this->idasientoi  = null;
            $this->idasientoc  = null;
            $this->abierto     = true;
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
        if ($this->idejercicio) {
            return 'index.php?page=ver_ejercicio&id=' . $this->idejercicio;
        }
        return 'index.php?page=lista_ejercicios';
    }

    public function get($idejercicio)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idejercicio = " . $this->var2str($idejercicio) . ";");
        if ($data) {
            return new \ejercicios($data[0]);
        }

        return false;
    }

    public function getPlanCuentas()
    {
        if (!$this->idejercicio) {
            return array();
        }

        $plan = new \plancuentas();
        return $plan->mostrarPlanCuentas($this->idempresa, $this->idejercicio);
    }

    public function get_by_codigo($idempresa, $nombre)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND nombre = " . $this->var2str($nombre) . ";");
        if ($data) {
            return new \ejercicios($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idejercicio)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idejercicio = " . $this->var2str($this->idejercicio) . ";");
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
                . ", fec_inicio = " . $this->var2str($this->fec_inicio)
                . ", fec_fin = " . $this->var2str($this->fec_fin)
                . ", idasientoi = " . $this->var2str($this->idasientoi)
                . ", idasientoc = " . $this->var2str($this->idasientoc)
                . ", abierto = " . $this->var2str($this->abierto)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idejercicio = " . $this->var2str($this->idejercicio) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, nombre, fec_inicio, fec_fin, idasientoi, idasientoc, abierto, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->nombre)
                . "," . $this->var2str($this->fec_inicio)
                . "," . $this->var2str($this->fec_fin)
                . "," . $this->var2str($this->idasientoi)
                . "," . $this->var2str($this->idasientoc)
                . "," . $this->var2str($this->abierto)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idejercicio = $this->db->lastval();
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
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idejercicio = " . $this->var2str($this->idejercicio) . ";");
    }

    private function beforeDelete()
    {
        return true;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY fec_inicio DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \ejercicios($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();        
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY fec_inicio DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \ejercicios($p);
            }
        }

        return $list;
    }

    public function get_by_fecha($idempresa, $fecha)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND fec_inicio <= " . $this->var2str($fecha) . " AND fec_fin >= " . $this->var2str($fecha) . ";");
        if ($data) {
            return new \ejercicios($data[0]);
        }
        return false;
    }

    public function existsPlanCuentas()
    {
        $sql = "SELECT * FROM plancuentas WHERE idempresa = " . $this->var2str($this->idempresa) . " AND idejercicio = " . $this->var2str($this->idejercicio);

        $data = $this->db->select_limit($sql, 1, 0);

        if ($data) {
            return true;
        }

        return false;
    }
}
