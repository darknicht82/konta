<?php

namespace GSC_Systems\model;

class clientes extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('clientes');
        if ($data) {

            $this->idcliente       = $data['idcliente'];
            $this->idempresa       = $data['idempresa'];
            $this->identificacion  = $data['identificacion'];
            $this->tipoid          = $data['tipoid'];
            $this->razonsocial     = $data['razonsocial'];
            $this->nombrecomercial = $data['nombrecomercial'];
            $this->telefono        = $data['telefono'];
            $this->celular         = $data['celular'];
            $this->email           = $data['email'];
            $this->direccion       = $data['direccion'];
            $this->regimen         = $data['regimen'];
            $this->obligado        = $this->str2bool($data['obligado']);
            $this->agretencion     = $this->str2bool($data['agretencion']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idcliente       = null;
            $this->idempresa       = null;
            $this->identificacion  = null;
            $this->tipoid          = null;
            $this->razonsocial     = null;
            $this->nombrecomercial = null;
            $this->telefono        = '';
            $this->celular         = null;
            $this->email           = null;
            $this->direccion       = null;
            $this->regimen         = 'GE';
            $this->obligado        = false;
            $this->agretencion     = false;
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
        $sql = "INSERT INTO " . $this->table_name . "(idempresa, identificacion, tipoid, razonsocial, nombrecomercial, telefono, regimen, fec_creacion, nick_creacion) VALUES
                ('1', '9999999999999', 'F', 'CONSUMIDOR FINAL', 'CONSUMIDOR FINAL', '2222222222', 'GE', '" . date('Y-m-d') . "', 'admin')";
        return $sql;
    }

    public function url()
    {
        if (is_null($this->idcliente)) {
            return 'index.php?page=lista_clientes';
        }

        return 'index.php?page=ver_cliente&id=' . $this->idcliente;
    }

    public function get($idcliente)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcliente = " . $this->var2str($idcliente) . ";");
        if ($data) {
            return new \clientes($data[0]);
        }

        return false;
    }

    public function getSaldo()
    {
        $cob0 = new \trans_cobros();
        return $cob0->getSaldoCliente($this->idempresa, $this->idcliente);
    }

    public function exists()
    {
        if (is_null($this->idcliente)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcliente = " . $this->var2str($this->idcliente) . ";");
    }

    public function test()
    {
        $status = true;

        $this->identificacion = trim($this->identificacion);

        return $status;
    }

    public function save()
    {
        if ($this->test()) {
            $insert = false;
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET idempresa = " . $this->var2str($this->idempresa)
                . ", identificacion = " . $this->var2str($this->identificacion)
                . ", tipoid = " . $this->var2str($this->tipoid)
                . ", razonsocial = " . $this->var2str($this->razonsocial)
                . ", nombrecomercial = " . $this->var2str($this->nombrecomercial)
                . ", telefono = " . $this->var2str($this->telefono)
                . ", celular = " . $this->var2str($this->celular)
                . ", email = " . $this->var2str($this->email)
                . ", direccion = " . $this->var2str($this->direccion)
                . ", regimen = " . $this->var2str($this->regimen)
                . ", obligado = " . $this->var2str($this->obligado)
                . ", agretencion = " . $this->var2str($this->agretencion)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idcliente = " . $this->var2str($this->idcliente) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, identificacion, tipoid, razonsocial, nombrecomercial, telefono, celular, email, direccion, regimen, obligado, agretencion, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->identificacion)
                . "," . $this->var2str($this->tipoid)
                . "," . $this->var2str($this->razonsocial)
                . "," . $this->var2str($this->nombrecomercial)
                . "," . $this->var2str($this->telefono)
                . "," . $this->var2str($this->celular)
                . "," . $this->var2str($this->email)
                . "," . $this->var2str($this->direccion)
                . "," . $this->var2str($this->regimen)
                . "," . $this->var2str($this->obligado)
                . "," . $this->var2str($this->agretencion)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idcliente = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idcliente = " . $this->var2str($this->idcliente) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY razonsocial DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \clientes($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY razonsocial DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \clientes($p);
            }
        }

        return $list;
    }

    public function search_clientes($idempresa = '', $query = '', $tipoid = '', $regimen = '', $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        if ($idempresa != '') {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }
        if ($tipoid != '') {
            $sql .= " AND tipoid = " . $this->var2str($tipoid);
        }
        if ($regimen != '') {
            $sql .= " AND regimen = " . $this->var2str($regimen);
        }
        if ($query != '') {
            $query = strtolower($query);
            $qry   = explode(" ", $query);
            $sql .= " AND (";
            $or = "";
            foreach ($qry as $key => $q) {
                $sql .= $or . "lower(razonsocial) LIKE '%" . $q . "%' OR lower(nombrecomercial) LIKE '%" . $q . "%'";
                $or = " OR ";
            }
            $sql .= " OR lower(identificacion) LIKE '%" . $query . "%')";
        }
        if ($offset >= 0) {
            $sql .= " ORDER BY razonsocial ASC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }
        if ($data) {
            if ($offset < 0) {
                return count($data);
            } else {
                foreach ($data as $p) {
                    $list[] = new \clientes($p);
                }
            }
        }

        return $list;
    }

    public function get_by_identificacion($idempresa, $identificacion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND identificacion = " . $this->var2str($identificacion) . ";");
        if ($data) {
            return new \clientes($data[0]);
        }

        return false;
    }

    public function get_ConsFinal($idempresa)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND tipoid = " . $this->var2str('F') . ";");
        if ($data) {
            return new \clientes($data[0]);
        }

        return false;
    }
}
