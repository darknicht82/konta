<?php

namespace GSC_Systems\model;

class plancuentas extends \model
{
    public $id;
    public $idempresa;
    public $codigo;
    public $nombre;
    public $tipo;
    public $idpadre;
    public $idejercicio;
    public $esbanco;
    public $escajach;
    public $ctacliente;
    public $ctaantprov;
    public $ctaproveedor;
    public $ctaantcli;
    public $ctaresultado;
    public $ctaventa;
    public $ctadtoventa;
    public $ctadevolventa;
    public $ctacompra;
    public $ctacostos;
    public $ingresos;
    public $ingresosnop;
    public $costos;
    public $gastos;
    public $egresosnop;
    public $ctanotaventa;
    public $ctanotacostos;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('plancuentas');
        if ($data) {

            $this->id            = $data['id'];
            $this->idempresa     = $data['idempresa'];
            $this->idejercicio   = $data['idejercicio'];
            $this->tipo          = $data['tipo'];
            $this->codigo        = $data['codigo'];
            $this->nombre        = $data['nombre'];
            $this->idpadre       = $data['idpadre'];
            $this->esbanco       = $this->str2bool($data['esbanco']);
            $this->escajach      = $this->str2bool($data['escajach']);
            $this->ctacliente    = $this->str2bool($data['ctacliente']);
            $this->ctaantprov    = $this->str2bool($data['ctaantprov']);
            $this->ctaproveedor  = $this->str2bool($data['ctaproveedor']);
            $this->ctaantcli     = $this->str2bool($data['ctaantcli']);
            $this->ctaresultado  = $this->str2bool($data['ctaresultado']);
            $this->ctaventa      = $this->str2bool($data['ctaventa']);
            $this->ctadtoventa   = $this->str2bool($data['ctadtoventa']);
            $this->ctadevolventa = $this->str2bool($data['ctadevolventa']);
            $this->ctacompra     = $this->str2bool($data['ctacompra']);
            $this->ctacostos     = $this->str2bool($data['ctacostos']);
            $this->ingresos      = $this->str2bool($data['ingresos']);
            $this->ingresosnop   = $this->str2bool($data['ingresosnop']);
            $this->costos        = $this->str2bool($data['costos']);
            $this->gastos        = $this->str2bool($data['gastos']);
            $this->egresosnop    = $this->str2bool($data['egresosnop']);
            $this->ctanotaventa    = $this->str2bool($data['ctanotaventa']);
            $this->ctanotacostos    = $this->str2bool($data['ctanotacostos']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];
            //Niveles
            $this->nivel = '';
            if (isset($data['nivel'])) {
                $this->nivel = $data['nivel'];
            }

        } else {
            $this->id            = null;
            $this->idempresa     = null;
            $this->idejercicio   = null;
            $this->tipo          = null;
            $this->codigo        = null;
            $this->nombre        = null;
            $this->idpadre       = null;
            $this->esbanco       = false;
            $this->escajach      = false;
            $this->ctacliente    = false;
            $this->ctaantprov    = false;
            $this->ctaproveedor  = false;
            $this->ctaantcli     = false;
            $this->ctaresultado  = false;
            $this->ctaventa      = false;
            $this->ctadtoventa   = false;
            $this->ctadevolventa = false;
            $this->ctacompra     = false;
            $this->ctacostos     = false;
            $this->ingresos      = false;
            $this->ingresosnop   = false;
            $this->costos        = false;
            $this->gastos        = false;
            $this->egresosnop    = false;
            $this->ctanotaventa    = false;
            $this->ctanotacostos    = false;
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
        new \ejercicios();

        return "";
    }

    public function url()
    {
        return 'index.php?page=plandecuentas';
    }

    public function get($id)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE id = " . $this->var2str($id) . ";");
        if ($data) {
            return new \plancuentas($data[0]);
        }

        return false;
    }

    public function get_by_codigo($idempresa, $idejercicio, $codigo)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idejercicio = " . $this->var2str($idejercicio) . " AND codigo = " . $this->var2str($codigo) . ";");
        if ($data) {
            return new \plancuentas($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->id)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE id = " . $this->var2str($this->id) . ";");
    }

    public function test()
    {
        $status = true;

        if (!$this->id) {
            if ($this->existe_codigo()) {
                $this->new_error_msg("El código ya se encuentra registrado dentro del Ejercicio Fiscal.");
                return false;
            }
        }

        return $status;
    }

    public function save()
    {
        if ($this->test()) {
            $insert = false;
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET idempresa = " . $this->var2str($this->idempresa)
                . ", idejercicio = " . $this->var2str($this->idejercicio)
                . ", tipo = " . $this->var2str($this->tipo)
                . ", codigo = " . $this->var2str($this->codigo)
                . ", nombre = " . $this->var2str($this->nombre)
                . ", idpadre = " . $this->var2str($this->idpadre)
                . ", esbanco = " . $this->var2str($this->esbanco)
                . ", escajach = " . $this->var2str($this->escajach)
                . ", ctacliente = " . $this->var2str($this->ctacliente)
                . ", ctaantprov = " . $this->var2str($this->ctaantprov)
                . ", ctaproveedor = " . $this->var2str($this->ctaproveedor)
                . ", ctaantcli = " . $this->var2str($this->ctaantcli)
                . ", ctaresultado = " . $this->var2str($this->ctaresultado)
                . ", ctaventa = " . $this->var2str($this->ctaventa)
                . ", ctadtoventa = " . $this->var2str($this->ctadtoventa)
                . ", ctadevolventa = " . $this->var2str($this->ctadevolventa)
                . ", ctacompra = " . $this->var2str($this->ctacompra)
                . ", ctacostos = " . $this->var2str($this->ctacostos)
                . ", ingresos = " . $this->var2str($this->ingresos)
                . ", ingresosnop = " . $this->var2str($this->ingresosnop)
                . ", costos = " . $this->var2str($this->costos)
                . ", gastos = " . $this->var2str($this->gastos)
                . ", egresosnop = " . $this->var2str($this->egresosnop)
                . ", ctanotaventa = " . $this->var2str($this->ctanotaventa)
                . ", ctanotacostos = " . $this->var2str($this->ctanotacostos)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE id = " . $this->var2str($this->id) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idejercicio, tipo, codigo, nombre, idpadre, esbanco, escajach, ctacliente, ctaantprov, ctaproveedor, ctaantcli, ctaresultado, ctaventa, ctadtoventa, ctadevolventa, ctacompra, ctacostos, ingresos, ingresosnop, costos, gastos, egresosnop, ctanotaventa, ctanotacostos, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idejercicio)
                . "," . $this->var2str($this->tipo)
                . "," . $this->var2str($this->codigo)
                . "," . $this->var2str($this->nombre)
                . "," . $this->var2str($this->idpadre)
                . "," . $this->var2str($this->esbanco)
                . "," . $this->var2str($this->escajach)
                . "," . $this->var2str($this->ctacliente)
                . "," . $this->var2str($this->ctaantprov)
                . "," . $this->var2str($this->ctaproveedor)
                . "," . $this->var2str($this->ctaantcli)
                . "," . $this->var2str($this->ctaresultado)
                . "," . $this->var2str($this->ctaventa)
                . "," . $this->var2str($this->ctadtoventa)
                . "," . $this->var2str($this->ctadevolventa)
                . "," . $this->var2str($this->ctacompra)
                . "," . $this->var2str($this->ctacostos)
                . "," . $this->var2str($this->ingresos)
                . "," . $this->var2str($this->ingresosnop)
                . "," . $this->var2str($this->costos)
                . "," . $this->var2str($this->gastos)
                . "," . $this->var2str($this->egresosnop)
                . "," . $this->var2str($this->ctanotaventa)
                . "," . $this->var2str($this->ctanotacostos)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->id = $this->db->lastval();
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
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE id = " . $this->var2str($this->id) . ";");
    }

    private function beforeDelete()
    {
        return true;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY nombre DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \plancuentas($p);
            }
        }

        return $list;
    }

    public function allPlanCuentas($idempresa, $idejercicio)
    {
        $lista = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idejercicio = " . $this->var2str($idejercicio) . " ORDER BY codigo ASC";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $key => $d) {
                $lista[] = new \plancuentas($d);
            }
        }

        return $lista;
    }

    private function existe_codigo()
    {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($this->idempresa) . " AND idejercicio = " . $this->var2str($this->idejercicio) . " AND codigo = " . $this->var2str($this->codigo);

        return $this->db->select($sql);
    }

    public function desactivarDefecto($idempresa, $idejercicio, $id, $campo)
    {
        $sql = "UPDATE " . $this->table_name . " SET " . $campo . " = " . $this->var2str(false) . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idejercicio = " . $this->var2str($idejercicio) . " AND id != " . $this->var2str($id);

        return $this->db->exec($sql);
    }

    public function mostrarPlanCuentas($idempresa, $idejercicio)
    {
        /// lee la lista de la caché
        $list = array();
        $sql = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idejercicio = " . $this->var2str($idejercicio) . " ORDER BY codigo ASC";
        /// si la lista no está en caché, leemos de la base de datos
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $d) {
                if (is_null($d['idpadre'])) {
                    $list[] = new \plancuentas($d);
                    foreach ($this->aux_all($data, $d['id'], '&nbsp;') as $value) {
                        $list[] = new \plancuentas($value);
                    }
                }
            }
        }

        return $list;
    }

    private function aux_all(&$pcuentas, $idpadre, $nivel)
    {
        $sublist = array();

        foreach ($pcuentas as $pc) {
            if ($pc['idpadre'] === $idpadre) {
                $pc['nivel']  = $nivel;
                $sublist[] = $pc;
                foreach ($this->aux_all($pcuentas, $pc['id'], '&nbsp;' . $nivel) as $value) {
                    $sublist[] = $value;
                }
            }
        }

        return $sublist;
    }

    public function buscar_subcuenta($idempresa, $idejercicio, $query = '', $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();

        $sql = "SELECT * FROM ".$this->table_name." WHERE tipo = 'SC' AND idempresa = ".$this->var2str($idempresa)." AND idejercicio = ".$this->var2str($idejercicio);
        if ($query != '') {
            $query = strtolower($query);
            $qry   = explode(" ", $query);
            $sql .= " AND (";
            $or = "";
            foreach ($qry as $key => $q) {
                $sql .= $or . "lower(codigo) LIKE '%" . $q . "%' OR lower(nombre) LIKE '%" . $q . "%'";
                $or = " OR ";
            }
            $sql .= ")";
        }

        if ($offset >= 0) {
            $sql .= " ORDER BY codigo ASC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }

        if ($data) {
            if ($offset < 0) {
                return count($data);
            } else {
                foreach ($data as $p) {
                    $list[] = new \plancuentas($p);
                }
            }
        }

        return $list;
    }
}
