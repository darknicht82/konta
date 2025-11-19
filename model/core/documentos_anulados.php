<?php

namespace GSC_Systems\model;

class documentos_anulados extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('documentos_anulados');
        if ($data) {

            $this->idanulado        = $data['idanulado'];
            $this->idempresa        = $data['idempresa'];
            $this->idproveedor      = $data['idproveedor'];
            $this->idcliente        = $data['idcliente'];
            $this->coddocumento     = $data['coddocumento'];
            $this->nro_autorizacion = $data['nro_autorizacion'];
            $this->numero_documento = $data['numero_documento'];
            $this->fec_autorizacion = $data['fec_autorizacion'] ? Date('d-m-Y', strtotime($data['fec_autorizacion'])) : null;
            $this->hor_autorizacion = $data['hor_autorizacion'] ? Date('H:i:s', strtotime($data['hor_autorizacion'])) : null;
            $this->idfacturaprov    = $data['idfacturaprov'];
            $this->idfacturacli     = $data['idfacturacli'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idanulado        = null;
            $this->idempresa        = null;
            $this->idproveedor      = null;
            $this->idcliente        = null;
            $this->coddocumento     = null;
            $this->nro_autorizacion = null;
            $this->numero_documento = null;
            $this->fec_autorizacion = null;
            $this->hor_autorizacion = null;
            $this->idfacturaprov    = null;
            $this->idfacturacli     = null;
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
        new \proveedores();
        new \clientes();
        new \facturascli();
        new \facturasprov();

        $sql = "";
        return $sql;
    }

    public function url()
    {
        return 'index.php?page=lista_documentos_anulados';
    }

    public function get($idanulado)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idanulado = " . $this->var2str($idanulado) . ";");
        if ($data) {
            return new \documentos_anulados($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idanulado)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idanulado = " . $this->var2str($this->idanulado) . ";");
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
                $sql = "UPDATE " . $this->table_name . " SET idempresa = " . $this->var2str($this->coddocumento)
                . ", idproveedor = " . $this->var2str($this->nro_autorizacion)
                . ", idcliente = " . $this->var2str($this->nro_autorizacion)
                . ", coddocumento = " . $this->var2str($this->nro_autorizacion)
                . ", nro_autorizacion = " . $this->var2str($this->nro_autorizacion)
                . ", numero_documento = " . $this->var2str($this->numero_documento)
                . ", fec_autorizacion = " . $this->var2str($this->fec_autorizacion)
                . ", hor_autorizacion = " . $this->var2str($this->hor_autorizacion)
                . ", idfacturaprov = " . $this->var2str($this->idfacturaprov)
                . ", idfacturacli = " . $this->var2str($this->idfacturacli)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idanulado = " . $this->var2str($this->idanulado) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idproveedor, idcliente, coddocumento, nro_autorizacion, numero_documento, fec_autorizacion, hor_autorizacion, idfacturaprov, idfacturacli, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idproveedor)
                . "," . $this->var2str($this->idcliente)
                . "," . $this->var2str($this->coddocumento)
                . "," . $this->var2str($this->nro_autorizacion)
                . "," . $this->var2str($this->numero_documento)
                . "," . $this->var2str($this->fec_autorizacion)
                . "," . $this->var2str($this->hor_autorizacion)
                . "," . $this->var2str($this->idfacturaprov)
                . "," . $this->var2str($this->idfacturacli)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idanulado = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idanulado = " . $this->var2str($this->idanulado) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . ";");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \documentos_anulados($p);
            }
        }

        return $list;
    }
}
