<?php

namespace GSC_Systems\model;

class trans_cobros extends \model
{
    public $nombre_fp;
    public function __construct($data = false)
    {
        parent::__construct('trans_cobros');
        if ($data) {

            $this->idtranscobro    = $data['idtranscobro'];
            $this->idempresa       = $data['idempresa'];
            $this->idcliente       = $data['idcliente'];
            $this->idfacturacli    = $data['idfacturacli'];
            $this->idfacturanc     = $data['idfacturanc'];
            $this->idretencion     = $data['idretencion'];
            $this->idformapago     = $data['idformapago'];
            $this->tipo            = $data['tipo'];
            $this->fecha_trans     = Date('d-m-Y', strtotime($data['fecha_trans']));
            $this->num_doc         = $data['num_doc'];
            $this->debito          = floatval($data['debito']);
            $this->credito         = floatval($data['credito']);
            $this->esabono         = $this->str2bool($data['esabono']);
            $this->idcaja          = $data['idcaja'];
            $this->idcobro         = $data['idcobro'];
            $this->idanticipocli   = $data['idanticipocli'];
            $this->iddevolucioncli = $data['iddevolucioncli'];
            $this->idejercicio     = $data['idejercicio'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

            $this->nombre_fp = $this->get_fp();

        } else {
            $this->idtranscobro    = null;
            $this->idempresa       = null;
            $this->idcliente       = null;
            $this->idfacturacli    = null;
            $this->idfacturanc     = null;
            $this->idretencion     = null;
            $this->idformapago     = null;
            $this->tipo            = null;
            $this->fecha_trans     = null;
            $this->num_doc         = null;
            $this->debito          = 0;
            $this->credito         = 0;
            $this->esabono         = 0;
            $this->idcaja          = null;
            $this->idcobro         = null;
            $this->idanticipocli   = null;
            $this->iddevolucioncli = null;
            $this->idejercicio     = null;
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
        new \facturascli();
        new \formaspago();
        new \retencionescli();

        $sql = "";
        return $sql;
    }

    public function url()
    {
        return '';
    }

    public function get($idtranscobro)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idtranscobro = " . $this->var2str($idtranscobro) . ";");
        if ($data) {
            return new \trans_cobros($data[0]);
        }

        return false;
    }

    public function get_fp()
    {
        $fp = '-';
        if ($this->idformapago) {
            $fp0 = new \formaspago();
            $fpa = $fp0->get($this->idformapago);
            if ($fpa) {
                $fp = $fpa->nombre;
            }
        }

        if ($this->num_doc) {
            $fp .= " - Nro: " . $this->num_doc;
        }

        return $fp;
    }

    public function exists()
    {
        if (is_null($this->idtranscobro)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idtranscobro = " . $this->var2str($this->idtranscobro) . ";");
    }

    public function test()
    {
        $status = true;
        if (complemento_exists('contabilidad')) {
            //si existe el plugin de contabilidad se debe buscar el ejercicio para generarlo
            if (!$this->idejercicio) {
                $ejer0     = new \ejercicios();
                $ejercicio = $ejer0->get_by_fecha($this->idempresa, $this->fecha_trans);
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
                . ", idfacturacli = " . $this->var2str($this->idfacturacli)
                . ", idfacturanc = " . $this->var2str($this->idfacturanc)
                . ", idretencion = " . $this->var2str($this->idretencion)
                . ", idformapago = " . $this->var2str($this->idformapago)
                . ", tipo = " . $this->var2str($this->tipo)
                . ", fecha_trans = " . $this->var2str($this->fecha_trans)
                . ", num_doc = " . $this->var2str($this->num_doc)
                . ", debito = " . $this->var2str($this->debito)
                . ", credito = " . $this->var2str($this->credito)
                . ", esabono = " . $this->var2str($this->esabono)
                . ", idcaja = " . $this->var2str($this->idcaja)
                . ", idcobro = " . $this->var2str($this->idcobro)
                . ", idanticipocli = " . $this->var2str($this->idanticipocli)
                . ", iddevolucioncli = " . $this->var2str($this->iddevolucioncli)
                . ", idejercicio = " . $this->var2str($this->idejercicio)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idtranscobro = " . $this->var2str($this->idtranscobro) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idcliente, idfacturacli, idfacturanc, idretencion, idformapago, tipo, fecha_trans, num_doc, debito, credito, esabono, idcaja, idcobro, idanticipocli, iddevolucioncli, idejercicio, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idcliente)
                . "," . $this->var2str($this->idfacturacli)
                . "," . $this->var2str($this->idfacturanc)
                . "," . $this->var2str($this->idretencion)
                . "," . $this->var2str($this->idformapago)
                . "," . $this->var2str($this->tipo)
                . "," . $this->var2str($this->fecha_trans)
                . "," . $this->var2str($this->num_doc)
                . "," . $this->var2str($this->debito)
                . "," . $this->var2str($this->credito)
                . "," . $this->var2str($this->esabono)
                . "," . $this->var2str($this->idcaja)
                . "," . $this->var2str($this->idcobro)
                . "," . $this->var2str($this->idanticipocli)
                . "," . $this->var2str($this->iddevolucioncli)
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
                        $this->idtranscobro = $this->db->lastval();
                    }
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

    public function delete()
    {
        if ($this->beforeDelete()) {
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idtranscobro = " . $this->var2str($this->idtranscobro) . ";");
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

    public function deletebyIdCobro($idcobro)
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idcobro = " . $this->var2str($idcobro) . ";");
    }

    public function deletebyIdAnticipo($idanticipocli)
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idanticipocli = " . $this->var2str($idanticipocli) . ";");
    }

    public function deletebyIdDevolucion($iddevolucioncli)
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE iddevolucioncli = " . $this->var2str($iddevolucioncli) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idcliente ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \trans_cobros($p);
            }
        }

        return $list;
    }

    public function all_by_idfacturacli($idfacturacli)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idfacturacli = " . $this->var2str($idfacturacli) . " ORDER BY fecha_trans, idtranscobro ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \trans_cobros($p);
            }
        }

        return $list;
    }

    public function all_by_idcobro($idcobro)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idcobro = " . $this->var2str($idcobro) . " ORDER BY idtranscobro ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \trans_cobros($p);
            }
        }

        return $list;
    }

    public function all_by_iddevolucioncli($iddevolucioncli)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE iddevolucioncli = " . $this->var2str($iddevolucioncli) . " ORDER BY idtranscobro ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \trans_cobros($p);
            }
        }

        return $list;
    }

    public function get_pago_fact($idfacturacli)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE esabono = " . $this->var2str(false) . " AND idfacturacli = " . $this->var2str($idfacturacli) . ";");
        if ($data) {
            return new \trans_cobros($data[0]);
        }

        return false;
    }

    public function get_pago_ncc($idfacturanc)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idfacturanc = " . $this->var2str($idfacturanc) . ";");
        if ($data) {
            return new \trans_cobros($data[0]);
        }

        return false;
    }

    public function get_pago_ret($idretencion)
    {
        $list = array();
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idretencion = " . $this->var2str($idretencion) . ";");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \trans_cobros($p);
            }
        }
        return $list;
    }

    public function getCliente()
    {
        $cli0 = new \clientes();
        return $cli0->get($this->idcliente);
    }

    public function getFactura()
    {
        if ($this->idfacturacli) {
            $fac0 = new \facturascli();
            return $fac0->get($this->idfacturacli);
        }
        return false;
    }

    public function getAnticipo()
    {
        if ($this->idanticipocli) {
            $ant0 = new \anticiposcli();
            return $ant0->get($this->idanticipocli);
        }
        return false;
    }

    public function getRetencion()
    {
        if ($this->idretencion) {
            $ret0 = new \retencionescli();
            return $ret0->get($this->idretencion);
        }
        return false;
    }

    public function getSaldoCliente($idempresa, $idcliente)
    {
        $data = $this->db->select("SELECT ROUND(SUM(debito - credito), 2) AS saldo FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idcliente = " . $this->var2str($idcliente) . " AND idfacturacli NOT IN (SELECT idfacturacli FROM facturascli WHERE idcliente = " . $this->var2str($idcliente) . " AND anulado = TRUE);");
        if ($data) {
            return $data[0]['saldo'];
        }

        return 0;
    }

    public function getFormasCaja($idempresa, $idcierre)
    {
        $sql = "SELECT cob.idformapago, fp.nombre, fp.esefec, fp.escredito, COUNT(cob.idtranscobro) AS cont, SUM(cob.credito) AS cobros, SUM(cob.debito) AS total FROM trans_cobros cob INNER JOIN formaspago fp ON fp.idformapago = cob.idformapago WHERE cob.idfacturacli IN (SELECT idfacturacli FROM facturascli WHERE anulado != " . $this->var2str(true) . " AND idempresa = " . $this->var2str($idempresa) . " AND idcaja = " . $this->var2str($idcierre) . ") AND (cob.idcaja = " . $this->var2str($idcierre) . " OR cob.debito > 0) GROUP BY cob.idformapago, fp.nombre, fp.esefec, fp.escredito";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }
        return array();
    }

    public function getPagosCierre($idempresa, $idcierre, $coddocumento)
    {
        $list = array();

        $sql = "SELECT cob.idformapago, fp.nombre, fc.idfacturacli, fc.fec_emision, fc.numero_documento, fc.razonsocial, cob.credito, cob.num_doc, cob.fecha_trans FROM trans_cobros cob INNER JOIN formaspago fp ON fp.idformapago = cob.idformapago INNER JOIN facturascli fc ON fc.idfacturacli = cob.idfacturacli WHERE cob.idcaja = " . $this->var2str($idcierre) . " AND cob.idempresa = " . $this->var2str($idempresa) . " AND cob.credito > 0 AND fc.anulado != true AND fc.coddocumento = " . $this->var2str($coddocumento) . " ORDER BY fp.nombre, fc.numero_documento ASC";

        $data = $this->db->select($sql);

        if ($data) {
            foreach ($data as $key => $d) {
                if (isset($list[$d['idformapago']])) {
                    $list[$d['idformapago']]['total'] += floatval($d['credito']);
                    $list[$d['idformapago']]['datos'][] = array(
                        'fec_emision'      => date('d-m-Y', strtotime($d['fec_emision'])),
                        'numero_documento' => $d['numero_documento'],
                        'valor'            => show_numero(floatval($d['credito'])),
                        'num_doc'          => $d['num_doc'] ? $d['num_doc'] : '-',
                        'fecha_trans'      => date('d-m-Y', strtotime($d['fecha_trans'])),
                        'idfacturacli'     => $d['idfacturacli'],
                        'razonsocial'      => $d['razonsocial'],
                    );
                } else {
                    $list[$d['idformapago']] = array(
                        'nombre' => $d['nombre'],
                        'total'  => floatval($d['credito']),
                        'datos'  => array(),
                    );
                    $list[$d['idformapago']]['datos'][] = array(
                        'fec_emision'      => date('d-m-Y', strtotime($d['fec_emision'])),
                        'numero_documento' => $d['numero_documento'],
                        'valor'            => show_numero(floatval($d['credito'])),
                        'num_doc'          => $d['num_doc'] ? $d['num_doc'] : '-',
                        'fecha_trans'      => date('d-m-Y', strtotime($d['fecha_trans'])),
                        'idfacturacli'     => $d['idfacturacli'],
                        'razonsocial'      => $d['razonsocial'],
                    );
                }
            }
        }

        $sql1 = "SELECT cob.idformapago, fp.nombre, fc.idfacturacli, fc.fec_emision, fc.numero_documento, fc.razonsocial, (cob.debito - (SELECT COALESCE( SUM(credito), 0 ) FROM trans_cobros WHERE idcaja = " . $this->var2str($idcierre) . " AND idempresa = " . $this->var2str($idempresa) . " AND credito > 0 AND idfacturacli = fc.idfacturacli)) AS credito, cob.num_doc, cob.fecha_trans FROM trans_cobros cob INNER JOIN formaspago fp ON fp.idformapago = cob.idformapago INNER JOIN facturascli fc ON fc.idfacturacli = cob.idfacturacli WHERE fc.idcaja = " . $this->var2str($idcierre) . " AND cob.idempresa = " . $this->var2str($idempresa) . " AND fc.anulado != true AND fc.coddocumento = " . $this->var2str($coddocumento) . " AND cob.debito > 0 AND (cob.debito - (SELECT COALESCE( SUM(credito), 0 ) FROM trans_cobros WHERE idcaja = " . $this->var2str($idcierre) . " AND idempresa = " . $this->var2str($idempresa) . " AND credito > 0 AND idfacturacli = fc.idfacturacli)) > 0";

        $data1 = $this->db->select($sql1);

        if ($data1) {
            foreach ($data1 as $key => $d) {
                if (isset($list[$d['idformapago']])) {
                    $list[$d['idformapago']]['total'] += floatval($d['credito']);
                    $list[$d['idformapago']]['datos'][] = array(
                        'fec_emision'      => date('d-m-Y', strtotime($d['fec_emision'])),
                        'numero_documento' => $d['numero_documento'],
                        'valor'            => show_numero(floatval($d['credito'])),
                        'num_doc'          => $d['num_doc'] ? $d['num_doc'] : '-',
                        'fecha_trans'      => date('d-m-Y', strtotime($d['fecha_trans'])),
                        'idfacturacli'     => $d['idfacturacli'],
                        'razonsocial'      => $d['razonsocial'],
                    );
                } else {
                    $list[$d['idformapago']] = array(
                        'nombre' => $d['nombre'],
                        'total'  => floatval($d['credito']),
                        'datos'  => array(),
                    );
                    $list[$d['idformapago']]['datos'][] = array(
                        'fec_emision'      => date('d-m-Y', strtotime($d['fec_emision'])),
                        'numero_documento' => $d['numero_documento'],
                        'valor'            => show_numero(floatval($d['credito'])),
                        'num_doc'          => $d['num_doc'] ? $d['num_doc'] : '-',
                        'fecha_trans'      => date('d-m-Y', strtotime($d['fecha_trans'])),
                        'idfacturacli'     => $d['idfacturacli'],
                        'razonsocial'      => $d['razonsocial'],
                    );
                }
            }
        }

        return $list;
    }

    public function getSaldosFacturas($idempresa, $query, $idcliente, $estado, $fec_desde, $fec_hasta, $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();
        if ($estado == 2) {
            // Pagadas
            $sql = "SELECT
                    fc.idfacturacli,
                    fc.fec_emision AS fec_emision,
                    fc.numero_documento AS numero_documento,
                    d.nombre AS documento,
                    fc.razonsocial,
                    fp.nombre AS formapago,
                    tc.num_doc,
                    tc.fecha_trans,
                    to_char(fc.fec_emision::date+fc.diascredito, 'YYYY-MM-DD') AS vencimiento,
                    tc.credito AS valor
                FROM
                    facturascli fc
                    INNER JOIN trans_cobros tc ON tc.idfacturacli = fc.idfacturacli
                    INNER JOIN documentos d ON d.iddocumento = fc.iddocumento
                    INNER JOIN formaspago fp ON tc.idformapago = fp.idformapago
                WHERE
                    fc.idempresa = " . $this->var2str($idempresa) . " AND fc.anulado != " . $this->var2str(true) . " AND tc.credito > 0";
            if ($query != '') {
                $query = strtolower($query);
                $sql .= " AND (lower(fc.numero_documento) LIKE '%" . $query . "%' OR lower(tc.num_doc) LIKE '%" . $query . "%')";
            }
            if ($idcliente != '') {
                $sql .= " AND fc.idcliente = " . $this->var2str($idcliente);
            }
            if ($fec_desde != '') {
                $sql .= " AND fc.fec_emision >= " . $this->var2str($fec_desde);
            }
            if ($fec_hasta != '') {
                $sql .= " AND fc.fec_emision <= " . $this->var2str($fec_hasta);
            }

            if ($offset >= 0) {
                $sql .= " ORDER BY tc.fecha_trans DESC";
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
        } else {
            //otras formas de pago
            $sql = "SELECT
                        fc.idfacturacli,
                        fc.fec_emision AS fec_emision,
                        fc.numero_documento AS numero_documento,
                        d.nombre AS documento,
                        fc.razonsocial,
                        'Credito' AS formapago,
                        NULL AS num_doc,
                        fc.fec_emision AS fecha_trans,
                        to_char(fc.fec_emision::date+fc.diascredito, 'YYYY-MM-DD') AS vencimiento,
                        sd.saldo AS valor
                    FROM
                        ( SELECT idfacturacli AS idfac, SUM ( debito - credito ) AS saldo FROM trans_cobros GROUP BY idfacturacli ) AS sd
                        INNER JOIN facturascli fc ON sd.idfac = fc.idfacturacli
                        INNER JOIN documentos d ON d.iddocumento = fc.iddocumento
                    WHERE fc.idempresa = " . $this->var2str($idempresa) . " AND fc.anulado != " . $this->var2str(true);
            if ($estado == 1) {
                $sql .= " AND sd.saldo > 0";
            } else if ($estado == 3) {
                $hoy = date('Y-m-d');
                $sql .= " AND sd.saldo > 0 AND to_char(fc.fec_emision::date+fc.diascredito, 'YYYY-MM-DD') < " . $this->var2str($hoy);
            }

            if ($query != '') {
                $query = strtolower($query);
                $sql .= " AND (lower(fc.numero_documento) LIKE '%" . $query . "%')";
            }
            if ($idcliente != '') {
                $sql .= " AND fc.idcliente = " . $this->var2str($idcliente);
            }
            if ($fec_desde != '') {
                $sql .= " AND fc.fec_emision >= " . $this->var2str($fec_desde);
            }
            if ($fec_hasta != '') {
                $sql .= " AND fc.fec_emision <= " . $this->var2str($fec_hasta);
            }
            if ($offset >= 0) {
                $sql .= " ORDER BY to_char(fc.fec_emision::date+fc.diascredito, 'YYYY-MM-DD') ASC";
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
        }

        return $list;
    }

    public function pendientesCobro($idempresa, $idcliente)
    {
        $list = array();

        $sql = "SELECT
                    fc.idfacturacli,
                    fc.coddocumento,
                    d.nombre AS documento,
                    fc.fec_emision AS fec_emision,
                    fc.numero_documento AS numero_documento,
                    fc.razonsocial,
                    fc.fec_emision AS fecha_trans,
                    to_char(fc.fec_emision::date+fc.diascredito, 'YYYY-MM-DD') AS vencimiento,
                    sd.saldo AS valor
                FROM
                    ( SELECT idfacturacli AS idfac, SUM ( debito - credito ) AS saldo FROM trans_cobros GROUP BY idfacturacli ) AS sd
                    INNER JOIN facturascli fc ON sd.idfac = fc.idfacturacli
                    INNER JOIN documentos d ON d.iddocumento = fc.iddocumento
                WHERE fc.idempresa = " . $this->var2str($idempresa) . " AND fc.anulado != " . $this->var2str(true) . " AND sd.saldo > 0 AND fc.idcliente = " . $this->var2str($idcliente);

        $sql .= " ORDER BY to_char(fc.fec_emision::date+fc.diascredito, 'YYYY-MM-DD') ASC";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $list;
    }

    public function getNumDocCliente($idempresa, $idcliente, $idformapago, $num_doc)
    {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idcliente = " . $this->var2str($idcliente) . " AND idformapago = " . $this->var2str($idformapago) . " AND num_doc = " . $this->var2str($num_doc) . ";";
        $data = $this->db->select($sql);
        if ($data) {
            return true;
        }

        return false;
    }

    public function getEstadoCuentaCliente($idempresa, $idcliente = '', $fechadesde = '', $fechahasta = '', $idestablecimiento = '', $estado = 1)
    {
        $list = array();
        $sql  = "BEGIN;
                CREATE TEMPORARY TABLE
                IF NOT EXISTS saldosclientes (
                    ID serial PRIMARY KEY,
                    idfacturacli TEXT,
                    idcliente INTEGER,
                    razonsocial TEXT,
                    identificacion TEXT,
                    direccion TEXT,
                    telefono TEXT,
                    tipodoc TEXT,
                    numero_documento TEXT,
                    fec_emision TEXT,
                    vencimiento TEXT,
                    dias TEXT,
                    total DECIMAL ( 16, 6 ),
                    abono DECIMAL ( 16, 6 ),
                    saldo DECIMAL ( 16, 6 )
                );";
        if ($fechadesde != '') {
            $sql .= "INSERT INTO saldosclientes ( idfacturacli, idcliente, razonsocial, identificacion, direccion, telefono, tipodoc, numero_documento, fec_emision, vencimiento, dias, total, abono, saldo ) SELECT
                    '' AS idfacturacli,
                    tc.idcliente,
                    f.razonsocial,
                    cl.identificacion,
                    cl.direccion,
                    cl.telefono,
                    'SALDO INICIAL' AS tipodoc,
                    '' AS numero_documento,
                    '" . $fechadesde . "' AS fec_emision,
                    '' AS vencimiento,
                    '' AS dias,
                    ROUND( SUM(tc.debito), 2 ) AS total,
                    ROUND( SUM(tc.credito), 2 ) AS abono,
                    ROUND( SUM ( tc.debito - tc.credito ), 2 ) AS saldo
                    FROM
                        facturascli f
                        INNER JOIN trans_cobros tc ON tc.idfacturacli = f.idfacturacli
                        INNER JOIN clientes cl ON cl.idcliente = f.idcliente
                    WHERE
                        f.anulado != " . $this->var2str(true) . "
                        AND f.idempresa = " . $this->var2str($idempresa) . "
                        AND f.fec_emision < " . $this->var2str($fechadesde);
            if ($idcliente != '') {
                $sql .= " AND f.idcliente = " . $this->var2str($idcliente);
            }
            if ($idestablecimiento != '') {
                $sql .= " AND f.idestablecimiento = " . $this->var2str($idestablecimiento);
            }
            if ($fechahasta != '') {
                $sql .= " AND tc.fecha_trans <= " . $this->var2str($fechahasta);
            }
            $sql .= " GROUP BY tc.idcliente, f.razonsocial, cl.identificacion, cl.direccion, cl.telefono;";
        }
        $sql .= "INSERT INTO saldosclientes ( idfacturacli, idcliente, razonsocial, identificacion, direccion, telefono, tipodoc, numero_documento, fec_emision, vencimiento, dias, total, abono, saldo ) SELECT
            tc.idfacturacli,
            tc.idcliente,
            f.razonsocial,
            cl.identificacion,
            cl.direccion,
            cl.telefono,
            d.nombre AS tipodoc,
            f.numero_documento,
            f.fec_emision,
            to_char( f.fec_emision :: DATE + f.diascredito, 'YYYY-MM-DD' ) AS vencimiento,
            ( NOW( ) :: DATE - ( f.fec_emision :: DATE + f.diascredito ) ) AS dias,
            ROUND( SUM ( tc.debito ), 2 ) AS total,
            ROUND( SUM ( tc.credito ), 2 ) AS abono,
            ROUND( SUM ( tc.debito - tc.credito ), 2 ) AS saldo
            FROM
                facturascli f
                INNER JOIN trans_cobros tc ON tc.idfacturacli = f.idfacturacli
                INNER JOIN clientes cl ON cl.idcliente = f.idcliente
                INNER JOIN documentos d ON d.iddocumento = f.iddocumento
            WHERE
                f.anulado != " . $this->var2str(true) . "
                AND f.idempresa = " . $this->var2str($idempresa);
        if ($idcliente != '') {
            $sql .= " AND f.idcliente = " . $this->var2str($idcliente);
        }
        if ($idestablecimiento != '') {
            $sql .= " AND f.idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($fechadesde != '') {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fechadesde);
        }
        if ($fechahasta != '') {
            $sql .= "AND f.fec_emision <= " . $this->var2str($fechahasta) . " AND tc.fecha_trans <= " . $this->var2str($fechahasta);
        }
        $sql .= " GROUP BY tc.idfacturacli, tc.idcliente, f.razonsocial, cl.identificacion, cl.direccion, cl.telefono, f.numero_documento, f.fec_emision, f.diascredito, d.nombre; COMMIT;";
        //Genero la tabla temporal
        $data = $this->db->exec($sql);
        if ($data) {
            //saco los resultados
            $sql = "SELECT * FROM saldosclientes WHERE 1 = 1";
            // Todos
            if ($estado == 1) {
            } else if ($estado == 2) {
                //Pagadas
                $sql .= " AND saldo = " . $this->var2str(0);
            } else if ($estado == 3) {
                //Pendientes
                $sql .= " AND saldo != " . $this->var2str(0);
            } else if ($estado == 4) {
                //Saldos a Favor
                $sql .= " AND saldo < " . $this->var2str(0);
            } else if ($estado == 5) {
                //Vencidas
                $sql .= " AND saldo > " . $this->var2str(0) . " AND dias > " . $this->var2str(0);
            }
            $sql .= " ORDER BY razonsocial, fec_emision, numero_documento ASC;";
            $data = $this->db->select($sql);
            if ($data) {
                $list = $data;
            }
        }

        return $list;
    }

    public function all_by_anticipocli($idempresa, $idanticipocli)
    {
        $list = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idanticipocli = " . $this->var2str($idanticipocli);

        $data = $this->db->select($sql);

        if ($data) {
            foreach ($data as $key => $d) {
                $list[] = new trans_cobros($d);
            }
        }

        return $list;
    }

    public function getSaldosFavor($idempresa, $idcliente = '')
    {
        $list = array();

        $list = array();
        $sql  = "BEGIN;
                CREATE TEMPORARY TABLE
                IF NOT EXISTS saldosfavorcli (
                    ID serial PRIMARY KEY,
                    iddocumento TEXT,
                    idcliente INTEGER,
                    identificacion TEXT,
                    razonsocial TEXT,
                    numero_documento TEXT,
                    documento TEXT,
                    fecha_trans TEXT,
                    valor DECIMAL ( 16, 6 )
                );";
        // Consulta para obtener anticipos pendientes
        $sql .= "INSERT INTO saldosfavorcli ( iddocumento, idcliente, identificacion, razonsocial, numero_documento, documento, fecha_trans, valor )
                SELECT
                    'A-' || an.idanticipocli, an.idcliente, cl.identificacion, cl.razonsocial, an.numero, 'Anticipo', an.fecha_trans, ROUND( SUM ( tc.credito - tc.debito ) , 2)
                FROM
                    trans_cobros tc
                    INNER JOIN anticiposcli an ON an.idanticipocli = tc.idanticipocli
                    INNER JOIN clientes cl ON an.idcliente = cl.idcliente
                WHERE
                    an.anulado = FALSE
                    AND an.idempresa = " . $this->var2str($idempresa);
        if ($idcliente != '') {
            $sql .= " AND an.idcliente = " . $this->var2str($idcliente);
        }
        $sql .= " GROUP BY
                    an.idanticipocli, an.idcliente, cl.identificacion, cl.razonsocial, an.numero, an.fecha_trans
                HAVING
                    ROUND( SUM ( tc.credito - tc.debito ), 2) > 0;";
        // Consulta facturas con saldo a favor
        $sql .= "INSERT INTO saldosfavorcli ( iddocumento, idcliente, identificacion, razonsocial, numero_documento, documento, fecha_trans, valor )
                SELECT
                    'F-' || fc.idfacturacli, fc.idcliente, fc.identificacion, fc.razonsocial, fc.numero_documento, d.nombre,  fc.fec_emision, ROUND( SUM ( tc.credito - tc.debito ), 2)
                FROM
                    trans_cobros tc
                    INNER JOIN facturascli fc ON fc.idfacturacli = tc.idfacturacli
                    INNER JOIN documentos d ON d.iddocumento = fc.iddocumento
                WHERE
                    fc.anulado = FALSE
                    AND fc.idempresa = " . $this->var2str($idempresa);
        if ($idcliente != '') {
            $sql .= " AND fc.idcliente = " . $this->var2str($idcliente);
        }
        $sql .= " GROUP BY
                    fc.idfacturacli, fc.idcliente, fc.identificacion, fc.razonsocial, fc.numero_documento, d.nombre, fc.fec_emision
                HAVING
                    ROUND( SUM ( tc.credito - tc.debito ), 2) > 0;";
        // Consulta retenciones con saldo a favor
        $sql .= "INSERT INTO saldosfavorcli ( iddocumento, idcliente, identificacion, razonsocial, numero_documento, documento, fecha_trans, valor )
                SELECT
                    'R-' || rt.idretencion, rt.idcliente, cl.identificacion, cl.razonsocial, rt.numero_documento, 'Retención',  rt.fec_emision, ROUND( SUM ( tc.credito - tc.debito ), 2)
                FROM
                    trans_cobros tc
                    INNER JOIN retencionescli rt ON rt.idretencion = tc.idretencion
                    INNER JOIN clientes cl ON rt.idcliente = cl.idcliente
                WHERE
                    tc.idfacturacli IS NULL
                    AND rt.idempresa = " . $this->var2str($idempresa);
        if ($idcliente != '') {
            $sql .= " AND rt.idcliente = " . $this->var2str($idcliente);
        }
        $sql .= " GROUP BY
                    rt.idretencion, rt.idcliente, cl.identificacion, cl.razonsocial, rt.numero_documento, rt.fec_emision
                HAVING
                    ROUND( SUM ( tc.credito - tc.debito ), 2) > 0;";

        //Genero la tabla temporal
        $data = $this->db->exec($sql);
        if ($data) {
            //saco los resultados
            $sql  = "SELECT * FROM saldosfavorcli ORDER BY fecha_trans ASC;";
            $data = $this->db->select($sql);
            if ($data) {
                $list = $data;
            }
        }

        return $list;
    }
}
