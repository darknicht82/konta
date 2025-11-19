<?php

namespace GSC_Systems\model;

class lineasretbandejasri extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('lineasretbandejasri');
        if ($data) {

            $this->idlinearetbandejasri = $data['idlinearetbandejasri'];
            $this->idbandejasri         = $data['idbandejasri'];
            $this->idtiporetencion      = $data['idtiporetencion'];
            $this->especie              = $data['especie'];
            $this->codigo               = $data['codigo'];
            $this->baseimponible        = floatval($data['baseimponible']);
            $this->porcentaje           = floatval($data['porcentaje']);
            $this->total                = floatval($data['total']);
            $this->coddocumento_mod     = $data['coddocumento_mod'];
            $this->iddocumento_mod      = $data['iddocumento_mod'];
            $this->numero_documento_mod = $data['numero_documento_mod'];
            $this->fec_emision_mod      = $data['fec_emision_mod'] ? Date('d-m-Y', strtotime($data['fec_emision_mod'])) : null;
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idlinearetbandejasri = null;
            $this->idbandejasri         = null;
            $this->idtiporetencion      = null;
            $this->especie              = null;
            $this->codigo               = null;
            $this->baseimponible        = 0;
            $this->porcentaje           = 0;
            $this->total                = 0;
            $this->coddocumento_mod     = null;
            $this->iddocumento_mod      = null;
            $this->numero_documento_mod = null;
            $this->fec_emision_mod      = null;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;
        }
    }

    public function install()
    {
        new \cab_bandejasri();
        new \tiposretenciones();
        new \facturascli();

        $sql = "";

        return $sql;
    }

    public function url()
    {
        return '';
    }

    public function get($idlinearetbandejasri)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlinearetbandejasri = " . $this->var2str($idlinearetbandejasri) . ";");
        if ($data) {
            return new \lineasretbandejasri($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idlinearetbandejasri)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlinearetbandejasri = " . $this->var2str($this->idlinearetbandejasri) . ";");
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
                $sql = "UPDATE " . $this->table_name . " SET idbandejasri = " . $this->var2str($this->idbandejasri)
                . ", idtiporetencion = " . $this->var2str($this->idtiporetencion)
                . ", especie = " . $this->var2str($this->especie)
                . ", codigo = " . $this->var2str($this->codigo)
                . ", baseimponible = " . $this->var2str($this->baseimponible)
                . ", porcentaje = " . $this->var2str($this->porcentaje)
                . ", total = " . $this->var2str($this->total)
                . ", coddocumento_mod = " . $this->var2str($this->coddocumento_mod)
                . ", iddocumento_mod = " . $this->var2str($this->iddocumento_mod)
                . ", numero_documento_mod = " . $this->var2str($this->numero_documento_mod)
                . ", fec_emision_mod = " . $this->var2str($this->fec_emision_mod)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idlinearetbandejasri = " . $this->var2str($this->idlinearetbandejasri) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idbandejasri, idtiporetencion, especie, codigo, baseimponible, porcentaje, total, coddocumento_mod, iddocumento_mod, numero_documento_mod, fec_emision_mod, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idbandejasri)
                . "," . $this->var2str($this->idtiporetencion)
                . "," . $this->var2str($this->especie)
                . "," . $this->var2str($this->codigo)
                . "," . $this->var2str($this->baseimponible)
                . "," . $this->var2str($this->porcentaje)
                . "," . $this->var2str($this->total)
                . "," . $this->var2str($this->coddocumento_mod)
                . "," . $this->var2str($this->iddocumento_mod)
                . "," . $this->var2str($this->numero_documento_mod)
                . "," . $this->var2str($this->fec_emision_mod)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idlinearetbandejasri = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idlinearetbandejasri = " . $this->var2str($this->idlinearetbandejasri) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idtiporetencion ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \lineasretbandejasri($p);
            }
        }

        return $list;
    }

    public function all_by_idbandejasri($idbandejasri)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idbandejasri = " . $this->var2str($idbandejasri) . " ORDER BY especie ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \lineasretbandejasri($p);
            }
        }

        return $list;
    }

    public function homologar_items($idempresa, $identificacion, $linea)
    {
        $sql = "UPDATE " . $this->table_name . " SET idtiporetencion = " . $this->var2str($linea->idtiporetencion) . " WHERE codigo = " . $this->var2str($linea->codigo) . " AND idtiporetencion IS NULL AND idbandejasri IN (SELECT idbandejasri FROM cab_bandejasri WHERE idempresa = " . $this->var2str($idempresa) . " AND coddocumento = " . $this->var2str('07') . ")";
        return $this->db->exec($sql);
    }

    public function all_by_idbandejasri_sh($idbandejasri)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idtiporetencion IS NULL AND idbandejasri = " . $this->var2str($idbandejasri) . " ORDER BY idlinearetbandejasri ASC;");
        if ($data) {
            return true;
        }

        return false;
    }
}
