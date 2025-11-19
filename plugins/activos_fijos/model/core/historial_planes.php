<?php

namespace GSC_Systems\model;

class historial_planes extends \model
{
    public $idhistorial;
    public $idempresa;
    public $fec_inicio_plan;
    public $fec_caducidad_plan;
    public $numusers;
    public $numdocs;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('historial_planes');
        if ($data) {

            $this->idhistorial        = $data['idhistorial'];
            $this->idempresa          = $data['idempresa'];
            $this->fec_inicio_plan    = $data['fec_inicio_plan'];
            $this->fec_caducidad_plan = $data['fec_caducidad_plan'];
            $this->numusers           = $data['numusers'];
            $this->numdocs            = $data['numdocs'];
            $this->plan_basico        = $this->str2bool($data['plan_basico']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idhistorial        = null;
            $this->idempresa          = null;
            $this->fec_inicio_plan    = null;
            $this->fec_caducidad_plan = null;
            $this->numusers           = null;
            $this->numdocs            = null;
            $this->plan_basico        = 0;
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
        return 'index.php?page=lista_empresas';
    }

    public function get($idhistorial)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idhistorial = " . $this->var2str($idhistorial) . ";");
        if ($data) {
            return new \historial_planes($data[0], $this->establecimiento);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idhistorial)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idhistorial = " . $this->var2str($this->idhistorial) . ";");
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
                . ", fec_inicio_plan = " . $this->var2str($this->fec_inicio_plan)
                . ", fec_caducidad_plan = " . $this->var2str($this->fec_caducidad_plan)
                . ", numusers = " . $this->var2str($this->numusers)
                . ", numdocs = " . $this->var2str($this->numdocs)
                . ", plan_basico = " . $this->var2str($this->plan_basico)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idhistorial = " . $this->var2str($this->idhistorial) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, fec_inicio_plan, fec_caducidad_plan, numusers, numdocs, plan_basico, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->fec_inicio_plan)
                . "," . $this->var2str($this->fec_caducidad_plan)
                . "," . $this->var2str($this->numusers)
                . "," . $this->var2str($this->numdocs)
                . "," . $this->var2str($this->plan_basico)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idhistorial = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idhistorial = " . $this->var2str($this->idhistorial) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY fec_inicio_plan DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \historial_planes($p, $this->establecimiento);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY fec_inicio_plan DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \historial_planes($p);
            }
        }

        return $list;
    }
}
