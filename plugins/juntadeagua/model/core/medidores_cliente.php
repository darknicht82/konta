<?php

namespace GSC_Systems\model;

class medidores_cliente extends \model
{
    public $idmedidor;
    public $idempresa;
    public $idcliente;
    public $numero;
    public $fec_inicio;
    public $consumo_ini;
    public $activo;
    public $sector;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('medidores_cliente');
        if ($data) {

            $this->idmedidor   = $data['idmedidor'];
            $this->idempresa   = $data['idempresa'];
            $this->idcliente   = $data['idcliente'];
            $this->numero      = $data['numero'];
            $this->fec_inicio  = $data['fec_inicio'] ? Date('Y-m-d', strtotime($data['fec_inicio'])) : null;
            $this->consumo_ini = $data['consumo_ini'];
            $this->activo      = $this->str2bool($data['activo']);
            $this->sector      = $data['sector'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idmedidor   = null;
            $this->idempresa   = null;
            $this->idcliente   = null;
            $this->numero      = null;
            $this->fec_inicio  = null;
            $this->consumo_ini = null;
            $this->activo      = true;
            $this->sector      = null;
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
        new \clientes();

        return "";
    }

    public function url()
    {
        return '';
    }

    public function get($idmedidor)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idmedidor = " . $this->var2str($idmedidor) . ";");
        if ($data) {
            return new \medidores_cliente($data[0]);
        }

        return false;
    }

    public function get_by_cliente_numero($idcliente, $numero)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcliente = " . $this->var2str($idcliente) . " AND numero = " . $this->var2str($numero) . ";");
        if ($data) {
            return new \medidores_cliente($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idmedidor)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idmedidor = " . $this->var2str($this->idmedidor) . ";");
    }

    public function test()
    {
        $status = true;

        $this->numero = trim($this->numero);

        return $status;
    }

    public function save()
    {
        if ($this->test()) {
            $insert = false;
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET idempresa = " . $this->var2str($this->idempresa)
                . ", idcliente = " . $this->var2str($this->idcliente)
                . ", numero = " . $this->var2str($this->numero)
                . ", fec_inicio = " . $this->var2str($this->fec_inicio)
                . ", consumo_ini = " . $this->var2str($this->consumo_ini)
                . ", activo = " . $this->var2str($this->activo)
                . ", sector = " . $this->var2str($this->sector)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idmedidor = " . $this->var2str($this->idmedidor) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idcliente, numero, fec_inicio, consumo_ini, activo, sector, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idcliente)
                . "," . $this->var2str($this->numero)
                . "," . $this->var2str($this->fec_inicio)
                . "," . $this->var2str($this->consumo_ini)
                . "," . $this->var2str($this->activo)
                . "," . $this->var2str($this->sector)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idmedidor = $this->db->lastval();
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
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idmedidor = " . $this->var2str($this->idmedidor) . ";");
    }

    private function beforeDelete()
    {
        $sql  = "SELECT * FROM facturascli WHERE idmedidor = " . $this->var2str($this->idmedidor);
        $data = $this->db->select($sql);
        if ($data) {
            return false;
        }
        return true;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY fec_inicio DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \medidores_cliente($p);
            }
        }

        return $list;
    }

    public function all_by_idcliente($idcliente, $activo = false)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcliente = " . $this->var2str($idcliente) . " ORDER BY numero DESC;");
        if ($data) {
            foreach ($data as $p) {
                if ($activo) {
                    if ($this->str2bool($p['activo'])) {
                        $list[] = new \medidores_cliente($p);
                    }
                } else {
                    $list[] = new \medidores_cliente($p);
                }
            }
        }

        return $list;
    }

    public function saldosClientesMedidor($idempresa, $query, $idcliente, $idmedidor, $estado, $fec_desde, $fec_hasta, $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();
        $sql  = "SELECT f.idfacturacli, f.razonsocial, f.numero_documento, f.fec_emision, to_char(f.fec_emision::date+f.diascredito, 'YYYY-MM-DD') AS vencimiento, mc.numero, ROUND( f.total, 2 ) AS total, ROUND( SUM ( tc.debito - tc.credito ), 2 ) AS saldo
                FROM facturascli f
                INNER JOIN trans_cobros tc ON tc.idfacturacli = f.idfacturacli
                INNER JOIN medidores_cliente mc ON mc.idmedidor = f.idmedidor
                WHERE
                    f.idempresa = " . $this->var2str($idempresa);
        if ($query != '') {
            $query = strtolower($query);
            $sql .= " AND (lower(f.numero_documento) LIKE '%" . $query . "%')";
        }
        if ($idcliente != '') {
            $sql .= " AND f.idcliente = " . $this->var2str($idcliente);
        }
        if ($idmedidor != '') {
            $sql .= " AND f.idmedidor = " . $this->var2str($idmedidor);
        }
        if ($fec_desde != '') {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta != '') {
            $sql .= " AND f.fec_emision <= " . $this->var2str($fec_hasta);
        }
        $sql .= " GROUP BY f.idfacturacli, f.razonsocial, f.numero_documento, f.fec_emision, mc.numero, f.total";

        if ($estado == 2) {
            // Con SALDO
            $sql .= " HAVING ROUND( f.total, 2 ) != 0";
        } else if ($estado == 3) {
            // Pagadas
            $sql .= " HAVING ROUND( f.total, 2 ) = 0";
        }

        if ($offset >= 0) {
            $sql .= " ORDER BY f.fec_emision DESC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }

        if ($data) {
            if ($offset < 0) {
                return count($data);
            } else {
                return $data;
            }
        }
        return $list;
    }
}
