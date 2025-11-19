<?php

namespace GSC_Systems\model;

class configuraciones extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('configuraciones');
        if ($data) {

            $this->idconfiguracion = $data['idconfiguracion'];
            $this->mail_user       = $data['mail_user'];
            $this->mail_host       = $data['mail_host'];
            $this->mail_enc        = $data['mail_enc'];
            $this->mail_port       = $data['mail_port'];
            $this->mail_password   = $data['mail_password'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idconfiguracion = null;
            $this->mail_user       = null;
            $this->mail_host       = null;
            $this->mail_enc        = null;
            $this->mail_port       = null;
            $this->mail_password   = null;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;
        }
    }

    public function install()
    {
        $sql = "INSERT INTO " . $this->table_name . " (mail_user, mail_host, mail_enc, mail_port, mail_password, fec_creacion, nick_creacion) VALUES ('postmaster@docs.gscsystemsec.com', 'smtp.mailgun.org', 'ssl', '465', '', '" . date('Y-m-d') . "', 'admin')";

        return $sql;
    }

    public function url()
    {
        return 'index.php?page=configuraciones';
    }

    public function get($idconfiguracion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idconfiguracion = " . $this->var2str($idconfiguracion) . ";");
        if ($data) {
            return new \configuraciones($data[0]);
        }

        return false;
    }

    public function getConfig()
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . ";");
        if ($data) {
            return new \configuraciones($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idconfiguracion)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idconfiguracion = " . $this->var2str($this->idconfiguracion) . ";");
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
                $sql = "UPDATE " . $this->table_name . " SET mail_user = " . $this->var2str($this->mail_user)
                . ", mail_host = " . $this->var2str($this->mail_host)
                . ", mail_enc = " . $this->var2str($this->mail_enc)
                . ", mail_port = " . $this->var2str($this->mail_port)
                . ", mail_password = " . $this->var2str($this->mail_password)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idconfiguracion = " . $this->var2str($this->idconfiguracion) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (mail_user, mail_host, mail_enc, mail_port, mail_password, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->mail_user)
                . "," . $this->var2str($this->mail_host)
                . "," . $this->var2str($this->mail_enc)
                . "," . $this->var2str($this->mail_port)
                . "," . $this->var2str($this->mail_password)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idconfiguracion = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idconfiguracion = " . $this->var2str($this->idconfiguracion) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY mail_host DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \configuraciones($p);
            }
        }

        return $list;
    }
}
