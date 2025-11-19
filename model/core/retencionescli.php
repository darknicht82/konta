<?php

namespace GSC_Systems\model;

class retencionescli extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('retencionescli');
        if ($data) {

            $this->idretencion      = $data['idretencion'];
            $this->idempresa        = $data['idempresa'];
            $this->idcliente        = $data['idcliente'];
            $this->fec_emision      = $data['fec_emision'] ? Date('d-m-Y', strtotime($data['fec_emision'])) : null;
            $this->fec_registro     = $data['fec_registro'] ? Date('d-m-Y', strtotime($data['fec_registro'])) : null;
            $this->numero_documento = $data['numero_documento'];
            $this->nro_autorizacion = $data['nro_autorizacion'];
            $this->fec_autorizacion = $data['fec_autorizacion'] ? Date('d-m-Y', strtotime($data['fec_autorizacion'])) : null;
            $this->hor_autorizacion = $data['hor_autorizacion'] ? Date('H:i:s', strtotime($data['hor_autorizacion'])) : null;
            $this->total            = floatval($data['total']);
            $this->idejercicio      = $data['idejercicio'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idretencion      = null;
            $this->idempresa        = null;
            $this->idcliente        = null;
            $this->fec_emision      = date('Y-m-d');
            $this->fec_registro     = date('Y-m-d');
            $this->numero_documento = null;
            $this->nro_autorizacion = null;
            $this->fec_autorizacion = null;
            $this->hor_autorizacion = null;
            $this->total            = 0;
            $this->idejercicio            = null;
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

        $sql = "";

        return $sql;
    }

    public function url()
    {
        if ($this->idretencion) {
            return 'index.php?page=ver_retencion_cliente&id=' . $this->idretencion;
        }
        return 'index.php?page=lista_retenciones_cliente';
    }

    public function get($idretencion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idretencion = " . $this->var2str($idretencion) . ";");
        if ($data) {
            return new \retencionescli($data[0]);
        }

        return false;
    }

    public function get_by_nro_aut($idempresa, $nro_autorizacion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND nro_autorizacion = " . $this->var2str($nro_autorizacion) . ";");
        if ($data) {
            return new \retencionescli($data[0]);
        }

        return false;
    }

    public function get_tipodoc()
    {
        return 'Retención';
    }

    public function exists()
    {
        if (is_null($this->idretencion)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idretencion = " . $this->var2str($this->idretencion) . ";");
    }

    public function test()
    {
        $status = true;

        if (!$this->idretencion) {
            if ($this->validar_duplicado()) {
                $this->new_error_msg("El numero de retencion ya se encuentra registrado.");
                return false;
            }
        }

        if (complemento_exists('contabilidad')) {
            //si existe el plugin de contabilidad se debe buscar el ejercicio para generarlo
            if (!$this->idejercicio) {
                $ejer0     = new \ejercicios();
                $ejercicio = $ejer0->get_by_fecha($this->idempresa, $this->fec_emision);
                if ($ejercicio) {
                    $this->idejercicio = $ejercicio->idejercicio;
                } else {
                    $this->new_error_msg('Ejercicio Fiscal no encontrado, primero debe crear el ejercicio y podrá ingresar el documento');
                    return false;
                }
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
                . ", idcliente = " . $this->var2str($this->idcliente)
                . ", fec_emision = " . $this->var2str($this->fec_emision)
                . ", fec_registro = " . $this->var2str($this->fec_registro)
                . ", numero_documento = " . $this->var2str($this->numero_documento)
                . ", nro_autorizacion = " . $this->var2str($this->nro_autorizacion)
                . ", fec_autorizacion = " . $this->var2str($this->fec_autorizacion)
                . ", hor_autorizacion = " . $this->var2str($this->hor_autorizacion)
                . ", total = " . $this->var2str($this->total)
                . ", idejercicio = " . $this->var2str($this->idejercicio)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idretencion = " . $this->var2str($this->idretencion) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idcliente, fec_emision, fec_registro, numero_documento, nro_autorizacion, hor_autorizacion, fec_autorizacion, total, idejercicio, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idcliente)
                . "," . $this->var2str($this->fec_emision)
                . "," . $this->var2str($this->fec_registro)
                . "," . $this->var2str($this->numero_documento)
                . "," . $this->var2str($this->nro_autorizacion)
                . "," . $this->var2str($this->hor_autorizacion)
                . "," . $this->var2str($this->fec_autorizacion)
                . "," . $this->var2str($this->total)
                . "," . $this->var2str($this->idejercicio)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->beforeSave()) {
                if ($this->db->exec($sql)) {
                    if ($insert) {
                        $this->idretencion = $this->db->lastval();
                    }
                    $this->afterSave();
                    return true;
                }
            }
        }

        return false;
    }

    private function beforeSave()
    {
        if (complemento_exists('contabilidad')) {
            if ($this->idejercicio) {
                $ejer0     = new \ejercicios();
                $ejercicio = $ejer0->get($this->idejercicio);
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

        return true;
    }

    public function afterSave()
    {
        $ret    = new \lineasretencionescli();
        $lineas = $ret->get_valor_ret($this->idretencion);
        if ($lineas) {
            $fp0    = new \formaspago();
            $cobro0 = new \trans_cobros();
            $fp     = $fp0->get_retencion($this->idempresa);
            if ($fp) {
                $cobro = $cobro0->get_pago_ret($this->idretencion);
                if ($cobro) {
                    foreach ($cobro as $key => $c) {
                        $c->delete();
                    }
                }
                foreach ($lineas as $key => $lin) {
                    if (floatval($lin['valor']) > 0) {
                        $cobro                = new \trans_cobros();
                        $cobro->idempresa     = $this->idempresa;
                        $cobro->idcliente     = $this->idcliente;
                        $cobro->fec_creacion  = date('Y-m-d');
                        $cobro->nick_creacion = $this->nick_creacion;
                        if ($lin['iddocumento_mod']) {
                            $cobro->idfacturacli = $lin['iddocumento_mod'];
                        }
                        $cobro->esabono     = true;
                        $cobro->idformapago = $fp->idformapago;
                        $cobro->idretencion = $this->idretencion;
                        $cobro->tipo        = 'Retención';
                        $cobro->fecha_trans = $this->fec_emision;
                        $cobro->credito     = $lin['valor'];
                        if (!$cobro->save()) {
                            $this->new_error_msg('Error al guardar el cobro.');
                            return false;
                        }
                    }
                }
            } else {
                $this->new_error_msg('No existe la forma de Pago de Retención.');
                return false;
            }
        }
        return true;
    }

    public function delete()
    {
        if ($this->beforeDelete()) {
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idretencion = " . $this->var2str($this->idretencion) . ";");
        }
        return false;
    }

    private function beforeDelete()
    {
        if (complemento_exists('contabilidad')) {
            if ($this->idejercicio) {
                $ejer0     = new \ejercicios();
                $ejercicio = $ejer0->get($this->idejercicio);
                if ($ejercicio) {
                    if (!$ejercicio->abierto) {
                        $this->new_error_msg('El Ejercicio se encuentra cerrado, No puede realizar movimientos en la fecha de transacción ingresada.');
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

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY fec_emision ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \retencionescli($p);
            }
        }

        return $list;
    }

    public function search_retenciones_cliente($idempresa = false, $query = false, $idcliente = false, $fec_desde = false, $fec_hasta = false, $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        if ($query != "") {
            $sql .= " AND (lower(numero_documento) LIKE '%" . strtolower($query) . "%' OR lower(nro_autorizacion) LIKE '%" . strtolower($query) . "%')";
        }

        if ($idcliente) {
            $sql .= " AND idcliente = " . $this->var2str($idcliente);
        }

        if ($idempresa) {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }

        if ($fec_desde) {
            $sql .= " AND fec_emision = " . $this->var2str($fec_desde);
        }

        if ($fec_hasta) {
            $sql .= " AND fec_emision = " . $this->var2str($fec_hasta);
        }
        if ($offset >= 0) {
            $sql .= " ORDER BY fec_emision DESC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }

        if ($data) {
            if ($offset < 0) {
                return count($data);
            } else {
                foreach ($data as $p) {
                    $list[] = new \retencionescli($p);
                }
            }
        }

        return $list;
    }

    public function get_cliente()
    {
        $cliente_r = new \clientes();

        if ($this->idcliente) {
            $cli0    = new clientes();
            $cliente = $cli0->get($this->idcliente);
            if ($cliente) {
                $cliente_r = $cliente;
            }
        }

        return $cliente_r;
    }

    private function validar_duplicado()
    {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($this->idempresa) . " AND numero_documento = " . $this->var2str($this->numero_documento) . " AND idcliente = " . $this->var2str($this->idcliente);

        $data = $this->db->select($sql);
        if ($data) {
            return true;
        }

        return false;
    }

    public function retencionesAts($idempresa, $desde, $hasta)
    {
        $list = array();

        $sql = "SELECT c.identificacion, c.tipoid, l.especie, SUM(l.total) AS total FROM lineasretencionescli l INNER JOIN retencionescli r ON r.idretencion = l.idretencion INNER JOIN clientes c ON c.idcliente = r.idcliente WHERE r.idempresa = " . $this->var2str($idempresa) . " AND r.fec_emision >= " . $this->var2str($desde) . " AND r.fec_emision <= " . $this->var2str($hasta) . " GROUP BY c.identificacion, c.tipoid, l.especie";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }
        return $list;
    }
}
