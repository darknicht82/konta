<?php

namespace GSC_Systems\model;

class tiposretenciones extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('tiposretenciones');
        if ($data) {

            $this->idtiporetencion = $data['idtiporetencion'];
            $this->idempresa       = $data['idempresa'];
            $this->codigobase      = $data['codigobase'];
            $this->codigo          = $data['codigo'];
            $this->especie         = $data['especie'];
            $this->nombre          = $data['nombre'];
            $this->porcentaje      = floatval($data['porcentaje']);
            $this->esventa         = $this->str2bool($data['esventa']);
            $this->escompra        = $this->str2bool($data['escompra']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idtiporetencion = null;
            $this->idempresa       = null;
            $this->codigobase      = null;
            $this->codigo          = null;
            $this->especie         = null;
            $this->nombre          = null;
            $this->porcentaje      = 0;
            $this->esventa         = 0;
            $this->escompra        = 0;
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
        
        $sql = "";
        return $sql;
    }

    public function url()
    {
        return 'index.php?page=lista_tiposretenciones';
    }

    public function get($idtiporetencion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idtiporetencion = " . $this->var2str($idtiporetencion) . ";");
        if ($data) {
            return new \tiposretenciones($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idtiporetencion)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idtiporetencion = " . $this->var2str($this->idtiporetencion) . ";");
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
                . ", codigobase = " . $this->var2str($this->codigobase)
                . ", codigo = " . $this->var2str($this->codigo)
                . ", especie = " . $this->var2str($this->especie)
                . ", nombre = " . $this->var2str($this->nombre)
                . ", porcentaje = " . $this->var2str($this->porcentaje)
                . ", esventa = " . $this->var2str($this->esventa)
                . ", escompra = " . $this->var2str($this->escompra)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idtiporetencion = " . $this->var2str($this->idtiporetencion) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, codigobase, codigo, especie, nombre, porcentaje, esventa, escompra, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->codigobase)
                . "," . $this->var2str($this->codigo)
                . "," . $this->var2str($this->especie)
                . "," . $this->var2str($this->nombre)
                . "," . $this->var2str($this->porcentaje)
                . "," . $this->var2str($this->esventa)
                . "," . $this->var2str($this->escompra)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idtiporetencion = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idtiporetencion = " . $this->var2str($this->idtiporetencion) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \tiposretenciones($p);
            }
        }

        return $list;
    }

    public function search_retencion($query = '', $idempresa = '')
    {
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        if ($query != "") {
            $qry = explode(" ", strtolower($query));
            $sql .= "(";
            foreach ($qry as $key => $val) {
                $sql .= "lower(nombre) LIKE '%" . $val . "%' OR lower(especie) LIKE '%" . $val . "%' OR lower(codigo) LIKE '%" . $val . "%' OR lower(codigobase) LIKE '%" . $val . "%'";
            }
            $sql .= ")";
        }

        if ($idempresa != "") {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }

        $sql .= " ORDER BY nombre ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \tiposretenciones($p);
            }
        }

        return $list;
    }

    public function lista_retenciones($idempresa, $especie = false, $esventa = false, $escompra = false)
    {
        $list = array();
        $sql = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa);
        if ($esventa) {
            $sql .= " AND esventa = " . $this->var2str(true);
        }
        if ($escompra) {
            $sql .= " AND escompra = " . $this->var2str(true);
        }
        if ($especie) {
            $sql .= " AND especie = " . $this->var2str($especie);
        }
        $sql .= " ORDER BY codigo ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \tiposretenciones($p);
            }
        }

        return $list;
    }

    public function get_retiva0()
    {
        $sql = "SELECT * FROM ".$this->table_name." WHERE porcentaje = ".$this->var2str(0)." AND especie = ".$this->var2str('iva');
        $data = $this->db->select($sql);
        if ($data) {
            return new \tiposretenciones($data[0]);
        }

        return false;
    }

    public function get_retencion_venta($idempresa, $especie, $codigo)
    {
        $sql = "SELECT * FROM ".$this->table_name." WHERE idempresa = ".$this->var2str($idempresa)." AND especie = ".$this->var2str($especie)." AND (codigo = ".$this->var2str($codigo)." OR codigobase = ".$this->var2str($codigo).") AND esventa = ".$this->var2str(true);
        $data = $this->db->select($sql);
        if ($data) {
            return new \tiposretenciones($data[0]);
        }

        return false;
    }

    public function all_retenciones_venta($idempresa, $query = '')
    {
        $list = array();
        $sql = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa);
        if ($query != '') {
            $query = strtolower($query);
            $sql .= " AND (";
            $prim = true;
            foreach (explode(" ", $query) as $key => $q) {
                if (!$prim) {
                    $sql ." OR ";
                }
                $sql .= "lower(codigo) LIKE '%".$q."%' OR lower(codigobase) LIKE '%".$q."%' OR lower(nombre) LIKE '%".$q."%' OR lower(especie) LIKE '%".$q."%'";
            }
            $sql .= ")";
        }
        $sql .= " AND esventa = " . $this->var2str(true);
        $sql .= " ORDER BY codigo ASC;";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \tiposretenciones($p);
            }
        }

        return $list;
    }
}
