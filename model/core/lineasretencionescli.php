<?php

namespace GSC_Systems\model;

class lineasretencionescli extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('lineasretencionescli');
        if ($data) {

            $this->idlinearetencion     = $data['idlinearetencion'];
            $this->idempresa            = $data['idempresa'];
            $this->idretencion          = $data['idretencion'];
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
            $this->idlinearetencion     = null;
            $this->idempresa            = null;
            $this->idretencion          = null;
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
        new \empresa();
        new \retencionescli();
        new \tiposretenciones();
        new \facturascli();        

        $sql = "";

        return $sql;
    }

    public function url()
    {
        return '';
    }

    public function get($idlinearetencion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlinearetencion = " . $this->var2str($idlinearetencion) . ";");
        if ($data) {
            return new \lineasretencionescli($data[0]);
        }

        return false;
    }

    public function get_retencion()
    {
        $ret0 = new \retencionescli();
        $ret  = $ret0->get($this->idretencion);
        if ($ret) {
            return $ret;
        }
        return false;
    }

    public function exists()
    {
        if (is_null($this->idlinearetencion)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlinearetencion = " . $this->var2str($this->idlinearetencion) . ";");
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
                . ", idretencion = " . $this->var2str($this->idretencion)
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
                . "  WHERE idlinearetencion = " . $this->var2str($this->idlinearetencion) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idretencion, idtiporetencion, especie, codigo, baseimponible, porcentaje, total, coddocumento_mod, iddocumento_mod, numero_documento_mod, fec_emision_mod, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idretencion)
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
            if ($this->beforeSave()) {
                if ($this->db->exec($sql)) {
                    if ($insert) {
                        $this->idlinearetencion = $this->db->lastval();
                    }
                    return true;
                }
            }
        }

        return false;
    }

    public function beforeSave()
    {
        if (complemento_exists('contabilidad')) {
            if ($ret = $this->get_retencion()) {
                if ($ret->idejercicio) {
                    $ejer0     = new \ejercicios();
                    $ejercicio = $ejer0->get($ret->idejercicio);
                    if ($ejercicio) {
                        if (!$ejercicio->abierto) {
                            $this->new_error_msg('El Ejercicio se encuentra cerrado, No puede realizar movimientos en la fecha de transacción ingresada.');
                            return false;
                        }
                    } else {
                        $this->new_error_msg('Ejercicio no encontrado.');
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function delete()
    {
        if ($this->beforeDelete()) {
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idlinearetencion = " . $this->var2str($this->idlinearetencion) . ";");
        }
        return false;
    }

    public function beforeDelete()
    {
        if (complemento_exists('contabilidad')) {
            if ($ret = $this->get_retencion()) {
                if ($ret->idejercicio) {
                    $ejer0     = new \ejercicios();
                    $ejercicio = $ejer0->get($ret->idejercicio);
                    if ($ejercicio) {
                        if (!$ejercicio->abierto) {
                            $this->new_error_msg('El Ejercicio se encuentra cerrado, No puede realizar movimientos en la fecha de transacción ingresada.');
                            return false;
                        }
                    } else {
                        $this->new_error_msg('Ejercicio no encontrado.');
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idtiporetencion ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \lineasretencionescli($p);
            }
        }

        return $list;
    }

    public function all_by_idretencion($idretencion)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idretencion = " . $this->var2str($idretencion) . " ORDER BY especie ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \lineasretencionescli($p);
            }
        }

        return $list;
    }

    public function get_valor_ret($idretencion)
    {
        $list = array();

        $sql = "SELECT iddocumento_mod, idretencion, ROUND(SUM(total), 2) AS valor FROM lineasretencionescli WHERE idretencion = ".$this->var2str($idretencion)." GROUP BY iddocumento_mod, idretencion;";
        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $list;
    }

    public function listado_retencion($idempresa = false, $fec_desde = false, $fec_hasta = false, $idcliente = false, $especie = 'iva')
    {
        $lista = array();

        $sql = "SELECT r.numero_documento AS numero_retencion, cl.razonsocial, l.numero_documento_mod AS numero_documento, l.fec_emision_mod AS fec_emision, r.fec_autorizacion AS fec_autorizacion_ret, t.codigo, t.codigobase, l.idtiporetencion, t.nombre, l.baseimponible AS baseimp, t.porcentaje, ROUND(l.total, 6) AS valret FROM lineasretencionescli l INNER JOIN retencionescli r ON r.idretencion = l.idretencion INNER JOIN clientes cl ON cl.idcliente = r.idcliente INNER JOIN tiposretenciones t ON t.idtiporetencion = l.idtiporetencion WHERE 1 = 1";
        if ($fec_desde) {
            $sql .= " AND r.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND r.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($especie) {
            $sql .= " AND l.especie = " . $this->var2str($especie);
        }
        if ($idcliente) {
            $sql .= " AND r.idcliente = " . $this->var2str($idcliente);
        }
        if ($idempresa) {
            $sql .= " AND r.idempresa = " . $this->var2str($idempresa);
        }

        $sql .= " ORDER BY l.idtiporetencion ASC, r.numero_documento ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $lista;
    }
}
