<?php

namespace GSC_Systems\model;

class cierres extends \model
{
    public $idcierre;
    public $idempresa;
    public $idcaja;
    public $nick;
    public $inicial;
    public $apertura;
    public $cierre;
    public $m001;
    public $m005;
    public $m010;
    public $m025;
    public $m050;
    public $m1;
    public $b1;
    public $b5;
    public $b10;
    public $b20;
    public $b50;
    public $b100;
    public $totalemp;
    public $totalmov;
    public $diferencia;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('cierres');

        if ($data) {
            $this->idcierre          = $data['idcierre'];
            $this->idempresa         = $data['idempresa'];
            $this->idcaja            = $data['idcaja'];
            $this->idestablecimiento = $data['idestablecimiento'];
            $this->nick              = $data['nick'];
            $this->inicial           = floatval($data['inicial']);
            $this->apertura          = $data['apertura'] ? Date('d-m-Y H:i:s', strtotime($data['apertura'])) : null;
            $this->cierre            = $data['cierre'] ? Date('d-m-Y H:i:s', strtotime($data['cierre'])) : null;
            $this->m001              = intval($data['m001']);
            $this->m005              = intval($data['m005']);
            $this->m010              = intval($data['m010']);
            $this->m025              = intval($data['m025']);
            $this->m050              = intval($data['m050']);
            $this->m1                = intval($data['m1']);
            $this->b1                = intval($data['b1']);
            $this->b5                = intval($data['b5']);
            $this->b10               = intval($data['b10']);
            $this->b20               = intval($data['b20']);
            $this->b50               = intval($data['b50']);
            $this->b100              = intval($data['b100']);
            $this->totalemp          = floatval($data['totalemp']);
            $this->totalmov          = floatval($data['totalmov']);
            $this->diferencia        = floatval($data['diferencia']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idcierre          = null;
            $this->idempresa         = null;
            $this->idcaja            = null;
            $this->idestablecimiento = null;
            $this->nick              = null;
            $this->inicial           = 0;
            $this->apertura          = null;
            $this->cierre            = null;
            $this->m001              = 0;
            $this->m005              = 0;
            $this->m010              = 0;
            $this->m025              = 0;
            $this->m050              = 0;
            $this->m1                = 0;
            $this->b1                = 0;
            $this->b5                = 0;
            $this->b10               = 0;
            $this->b20               = 0;
            $this->b50               = 0;
            $this->b100              = 0;
            $this->totalemp          = 0;
            $this->totalmov          = 0;
            $this->diferencia        = 0;
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
        new \cajas();
        new \establecimiento();
        new \users();

        return "";
    }

    public function url()
    {
        if ($this->idcierre) {
            return 'index.php?page=ver_cierre_caja&idcierre=' . $this->idcierre;
        }
        return 'index.php?page=lista_cierres_caja';
    }

    public function get($idcierre)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcierre = " . $this->var2str($idcierre) . ";");
        if ($data) {
            return new \cierres($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idcierre)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcierre = " . $this->var2str($this->idcierre) . ";");
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
                . ", idcaja = " . $this->var2str($this->idcaja)
                . ", idestablecimiento = " . $this->var2str($this->idestablecimiento)
                . ", nick = " . $this->var2str($this->nick)
                . ", inicial = " . $this->var2str($this->inicial)
                . ", apertura = " . $this->var2str($this->apertura)
                . ", cierre = " . $this->var2str($this->cierre)
                . ", m001 = " . $this->var2str($this->m001)
                . ", m005 = " . $this->var2str($this->m005)
                . ", m010 = " . $this->var2str($this->m010)
                . ", m025 = " . $this->var2str($this->m025)
                . ", m050 = " . $this->var2str($this->m050)
                . ", m1 = " . $this->var2str($this->m1)
                . ", b1 = " . $this->var2str($this->b1)
                . ", b5 = " . $this->var2str($this->b5)
                . ", b10 = " . $this->var2str($this->b10)
                . ", b20 = " . $this->var2str($this->b20)
                . ", b50 = " . $this->var2str($this->b50)
                . ", b100 = " . $this->var2str($this->b100)
                . ", totalemp = " . $this->var2str($this->totalemp)
                . ", totalmov = " . $this->var2str($this->totalmov)
                . ", diferencia = " . $this->var2str($this->diferencia)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idcierre = " . $this->var2str($this->idcierre) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idcaja, idestablecimiento, nick, inicial, apertura, cierre, m001, m005, m010, m025, m050, m1, b1, b5, b10, b20, b50, b100, totalemp, totalmov, diferencia, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idcaja)
                . "," . $this->var2str($this->idestablecimiento)
                . "," . $this->var2str($this->nick)
                . "," . $this->var2str($this->inicial)
                . "," . $this->var2str($this->apertura)
                . "," . $this->var2str($this->cierre)
                . "," . $this->var2str($this->m001)
                . "," . $this->var2str($this->m005)
                . "," . $this->var2str($this->m010)
                . "," . $this->var2str($this->m025)
                . "," . $this->var2str($this->m050)
                . "," . $this->var2str($this->m1)
                . "," . $this->var2str($this->b1)
                . "," . $this->var2str($this->b5)
                . "," . $this->var2str($this->b10)
                . "," . $this->var2str($this->b20)
                . "," . $this->var2str($this->b50)
                . "," . $this->var2str($this->b100)
                . "," . $this->var2str($this->totalemp)
                . "," . $this->var2str($this->totalmov)
                . "," . $this->var2str($this->diferencia)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idcierre = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idcierre = " . $this->var2str($this->idcierre) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY apertura DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \cierres($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY apertura DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \cierres($p);
            }
        }

        return $list;
    }

    public function search($idempresa, $desde = '', $hasta = '', $idestablecimiento = '', $idcaja = '', $nick = '', $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa);
        if ($desde != '') {
            $desde = date('Y-m-d', strtotime($desde)) . " 00:00:01";
            $sql .= " AND apertura >= " . $this->var2str($desde);
        }
        if ($hasta != '') {
            $hasta = date('Y-m-d', strtotime($hasta)) . " 23:59:59";
            $sql .= " AND cierre <= " . $this->var2str($hasta);
        }
        if ($idestablecimiento != '') {
            $sql .= " AND idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($idcaja != '') {
            $sql .= " AND idcaja = " . $this->var2str($idcaja);
        }
        if ($nick != '') {
            $sql .= " AND nick = " . $this->var2str($nick);
        }

        if ($offset >= 0) {
            $sql .= " ORDER BY apertura DESC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }

        if ($data) {
            if ($offset < 0) {
                return count($data);
            } else {
                foreach ($data as $p) {
                    $list[] = new \cierres($p);
                }
            }
        }

        return $list;
    }

    public function getCierreUsuario($idempresa, $nick)
    {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE cierre IS NULL AND idempresa = " . $this->var2str($idempresa) . " AND nick = " . $this->var2str($nick);
        $data = $this->db->select($sql);
        if ($data) {
            return new \cierres($data[0]);
        }

        return false;
    }

    public function getCajaAbierta($idcaja)
    {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE cierre IS NULL AND idcaja = " . $this->var2str($idcaja);
        $data = $this->db->select($sql);
        if ($data) {
            return new \cierres($data[0]);
        }

        return false;
    }

    public function get_establecimiento()
    {
        if ($this->idestablecimiento) {
            $est0 = new \establecimiento();
            $est  = $est0->get($this->idestablecimiento);
            if ($est) {
                return $est->nombre;
            }
        }
        return false;
    }

    public function get_caja()
    {
        if ($this->idcaja) {
            $caja0 = new \cajas();
            $caja  = $caja0->get($this->idcaja);
            if ($caja) {
                return $caja->nombre;
            }
        }
        return false;
    }

    public function getEfectivo()
    {
        $efectivo = 0;
        $cobros0  = new \trans_cobros();
        $cobros1  = $cobros0->getFormasCaja($this->idempresa, $this->idcierre);

        foreach ($cobros1 as $key => $c) {
            if ($this->str2bool($c['esefec'])) {
                $efectivo += floatval($c['cobros']);
            }
        }

        return round($efectivo, 2);
    }

    public function getMovimiento()
    {
        $movimiento = 0;
        $mov_cajas  = new \mov_cajas();
        $movs       = $mov_cajas->get_by_idempresa_caja($this->idempresa, $this->idcierre);
        if ($movs) {
            foreach ($movs as $key => $m) {
                $val = $m->valor;
                if ($m->tipo == 'egreso') {
                    $val = $val * -1;
                }
                $movimiento += $val;
            }
        }

        return round($movimiento, 2);
    }

    public function verificarCierre()
    {
        $inicial    = $this->inicial;
        $cierre     = $this->totalemp;
        $efectivo   = $this->getEfectivo();
        $movimiento = $this->getMovimiento();

        $sistema = round($inicial + $efectivo + $movimiento, 2);

        $dif    = round($cierre - $sistema, 2);
        $estado = 'CUADRADA';
        if ($dif > 0) {
            $estado = 'SOBRANTE';
        } else if ($dif < 0) {
            $estado = 'FALTANTE';
        }

        $result = array('cierre' => $sistema, 'diferencia' => $dif, 'estado' => $estado);

        return $result;
    }

    public function tieneCuadreProducto()
    {
        if (complemento_exists('cuadre_producto')) {
            $sql  = "SELECT * FROM articulos_cuadre WHERE idcierre = " . $this->var2str($this->idcierre);
            $data = $this->db->select($sql);
            if ($data) {
                return true;
            }
        }

        return false;
    }
}
