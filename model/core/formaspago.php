<?php

namespace GSC_Systems\model;

class formaspago extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('formaspago');
        if ($data) {

            $this->idformapago = $data['idformapago'];
            $this->idempresa   = $data['idempresa'];
            $this->nombre      = $data['nombre'];
            $this->escredito   = $this->str2bool($data['escredito']);
            $this->esventa     = $this->str2bool($data['esventa']);
            $this->escompra    = $this->str2bool($data['escompra']);
            $this->num_doc     = $this->str2bool($data['num_doc']);
            $this->esreten     = $this->str2bool($data['esreten']);
            $this->esnc        = $this->str2bool($data['esnc']);
            $this->esefec      = $this->str2bool($data['esefec']);
            $this->codigosri   = $data['codigosri'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idformapago = null;
            $this->idempresa   = null;
            $this->nombre      = null;
            $this->escredito   = false;
            $this->esventa     = true;
            $this->escompra    = true;
            $this->num_doc     = false;
            $this->esreten     = false;
            $this->esnc        = false;
            $this->esefec      = false;
            $this->codigosri   = null;
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
        
        $sql = "INSERT INTO " . $this->table_name . " (idempresa, nombre, escredito, esventa, escompra, esnc, codigosri, num_doc, esreten, fec_creacion, nick_creacion) VALUES
            ('1', 'Crédito', " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(false) . ", '20', " . $this->var2str(false) . ", " . $this->var2str(false) . ",'" . date('Y-m-d') . "', 'admin'),
            ('1', 'Transferencia', " . $this->var2str(false) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(false) . ", '20', " . $this->var2str(true) . ", " . $this->var2str(false) . ", '" . date('Y-m-d') . "', 'admin'),
            ('1', 'Nota de Credito', " . $this->var2str(false) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", 'NC', " . $this->var2str(false) . ", " . $this->var2str(false) . ", '" . date('Y-m-d') . "', 'admin'),
            ('1', 'Retencion', " . $this->var2str(false) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", 'RET', " . $this->var2str(false) . ", " . $this->var2str(true) . ", '" . date('Y-m-d') . "', 'admin'),
            ('1', 'Efectivo', " . $this->var2str(false) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(false) . ", '20', " . $this->var2str(false) . ", " . $this->var2str(false) . ",'" . date('Y-m-d') . "', 'admin')";
        return $sql;
    }

    public function url()
    {
        return 'index.php?page=lista_formaspago';
    }

    public function get($idformapago)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idformapago = " . $this->var2str($idformapago) . ";");
        if ($data) {
            return new \formaspago($data[0]);
        }

        return false;
    }

    public function get_by_codigosri($codigosri)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codigosri = " . $this->var2str($codigosri) . ";");
        if ($data) {
            return new \formaspago($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idformapago)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idformapago = " . $this->var2str($this->idformapago) . ";");
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
                . ", escredito = " . $this->var2str($this->escredito)
                . ", esventa = " . $this->var2str($this->esventa)
                . ", escompra = " . $this->var2str($this->escompra)
                . ", num_doc = " . $this->var2str($this->num_doc)
                . ", esnc = " . $this->var2str($this->esnc)
                . ", esreten = " . $this->var2str($this->esreten)
                . ", esefec = " . $this->var2str($this->esefec)
                . ", codigosri = " . $this->var2str($this->codigosri)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idformapago = " . $this->var2str($this->idformapago) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, nombre, escredito, esventa, escompra, num_doc, esnc, esefec, esreten, codigosri, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->nombre)
                . "," . $this->var2str($this->escredito)
                . "," . $this->var2str($this->esventa)
                . "," . $this->var2str($this->escompra)
                . "," . $this->var2str($this->num_doc)
                . "," . $this->var2str($this->esnc)
                . "," . $this->var2str($this->esefec)
                . "," . $this->var2str($this->esreten)
                . "," . $this->var2str($this->codigosri)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idformapago = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idformapago = " . $this->var2str($this->idformapago) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \formaspago($p);
            }
        }

        return $list;
    }

    public function search_formas_pago($query = '', $idempresa = '', $escredito = '', $esventa = '', $escompra = '')
    {
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        if ($query != "") {
            $sql .= " AND lower(nombre) LIKE '%" . strtolower($query) . "%'";
        }

        if ($idempresa != "") {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }

        if ($escredito != "") {
            $sql .= " AND escredito = " . $this->var2str($escredito);
        }

        if ($esventa != "") {
            $sql .= " AND esventa = " . $this->var2str($esventa);
        }

        if ($escompra != "") {
            $sql .= " AND escompra = " . $this->var2str($escompra);
        }

        $sql .= " ORDER BY nombre ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \formaspago($p);
            }
        }

        return $list;
    }

    public function get_credito($idempresa)
    {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND escredito = " . $this->var2str(true);
        $data = $this->db->select($sql);
        if ($data) {
            return new \formaspago($data[0]);
        }

        return false;
    }

    public function get_notacredito($idempresa)
    {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND esnc = " . $this->var2str(true);
        $data = $this->db->select($sql);
        if ($data) {
            return new \formaspago($data[0]);
        }

        return false;
    }

    public function get_retencion($idempresa)
    {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND esreten = " . $this->var2str(true);
        $data = $this->db->select($sql);
        if ($data) {
            return new \formaspago($data[0]);
        }

        return false;
    }

    public function allPuntoVenta($idempresa)
    {
        $list = array();
        $sql  = "SELECT * FROM formaspago WHERE idempresa = " . $this->var2str($idempresa) . " AND esventa = " . $this->var2str(true) . " AND esnc = " . $this->var2str(false) . " AND esreten = " . $this->var2str(false) . " ORDER BY nombre ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \formaspago($p);
            }
        }

        return $list;
    }

    public function allCobros($idempresa)
    {
        $list = array();
        $sql  = "SELECT * FROM formaspago WHERE idempresa = " . $this->var2str($idempresa) . " AND esventa = " . $this->var2str(true) . " AND escredito = " . $this->var2str(false) . " AND esnc = " . $this->var2str(false) . " AND esreten = " . $this->var2str(false) . " ORDER BY nombre ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \formaspago($p);
            }
        }

        return $list;   
    }

    public function allPagos($idempresa)
    {
        $list = array();
        $sql  = "SELECT * FROM formaspago WHERE idempresa = " . $this->var2str($idempresa) . " AND escompra = " . $this->var2str(true) . " AND escredito = " . $this->var2str(false) . " AND esnc = " . $this->var2str(false) . " AND esreten = " . $this->var2str(false) . " ORDER BY nombre ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \formaspago($p);
            }
        }

        return $list;   
    }
}
