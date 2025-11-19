<?php

namespace GSC_Systems\model;

class trans_pagos extends \model
{
    public $nombre_fp;
    public function __construct($data = false)
    {
        parent::__construct('trans_pagos');
        if ($data) {

            $this->idtranspago      = $data['idtranspago'];
            $this->idempresa        = $data['idempresa'];
            $this->idproveedor      = $data['idproveedor'];
            $this->idfacturaprov    = $data['idfacturaprov'];
            $this->idfacturanc      = $data['idfacturanc'];
            $this->idformapago      = $data['idformapago'];
            $this->tipo             = $data['tipo'];
            $this->fecha_trans      = Date('d-m-Y', strtotime($data['fecha_trans']));
            $this->num_doc          = $data['num_doc'];
            $this->debito           = floatval($data['debito']);
            $this->credito          = floatval($data['credito']);
            $this->esabono          = $this->str2bool($data['esabono']);
            $this->idcaja           = $data['idcaja'];
            $this->idpago           = $data['idpago'];
            $this->idanticipoprov   = $data['idanticipoprov'];
            $this->iddevolucionprov = $data['iddevolucionprov'];
            $this->idejercicio      = $data['idejercicio'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

            $this->nombre_fp = $this->get_fp();

        } else {
            $this->idtranspago      = null;
            $this->idempresa        = null;
            $this->idproveedor      = null;
            $this->idfacturaprov    = null;
            $this->idfacturanc      = null;
            $this->idformapago      = null;
            $this->tipo             = null;
            $this->fecha_trans      = null;
            $this->num_doc          = null;
            $this->debito           = 0;
            $this->credito          = 0;
            $this->esabono          = 0;
            $this->idcaja           = null;
            $this->idpago           = null;
            $this->idanticipoprov   = null;
            $this->iddevolucionprov = null;
            $this->idejercicio      = null;
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
        new \facturasprov();
        new \formaspago();

        $sql = "";
        return $sql;
    }

    public function url()
    {
        return '';
    }

    public function get($idtranspago)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idtranspago = " . $this->var2str($idtranspago) . ";");
        if ($data) {
            return new \trans_pagos($data[0]);
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
        if (is_null($this->idtranspago)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idtranspago = " . $this->var2str($this->idtranspago) . ";");
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
                . ", idproveedor = " . $this->var2str($this->idproveedor)
                . ", idfacturaprov = " . $this->var2str($this->idfacturaprov)
                . ", idfacturanc = " . $this->var2str($this->idfacturanc)
                . ", idformapago = " . $this->var2str($this->idformapago)
                . ", tipo = " . $this->var2str($this->tipo)
                . ", fecha_trans = " . $this->var2str($this->fecha_trans)
                . ", num_doc = " . $this->var2str($this->num_doc)
                . ", debito = " . $this->var2str($this->debito)
                . ", credito = " . $this->var2str($this->credito)
                . ", esabono = " . $this->var2str($this->esabono)
                . ", idcaja = " . $this->var2str($this->idcaja)
                . ", idpago = " . $this->var2str($this->idpago)
                . ", idanticipoprov = " . $this->var2str($this->idanticipoprov)
                . ", iddevolucionprov = " . $this->var2str($this->iddevolucionprov)
                . ", idejercicio = " . $this->var2str($this->idejercicio)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idtranspago = " . $this->var2str($this->idtranspago) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idproveedor, idfacturaprov, idfacturanc, idformapago, tipo, fecha_trans, num_doc, debito, credito, esabono, idcaja, idpago, idanticipoprov, iddevolucionprov, idejercicio, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idproveedor)
                . "," . $this->var2str($this->idfacturaprov)
                . "," . $this->var2str($this->idfacturanc)
                . "," . $this->var2str($this->idformapago)
                . "," . $this->var2str($this->tipo)
                . "," . $this->var2str($this->fecha_trans)
                . "," . $this->var2str($this->num_doc)
                . "," . $this->var2str($this->debito)
                . "," . $this->var2str($this->credito)
                . "," . $this->var2str($this->esabono)
                . "," . $this->var2str($this->idcaja)
                . "," . $this->var2str($this->idpago)
                . "," . $this->var2str($this->idanticipoprov)
                . "," . $this->var2str($this->iddevolucionprov)
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
                        $this->idtranspago = $this->db->lastval();
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
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idtranspago = " . $this->var2str($this->idtranspago) . ";");
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

    public function deletebyIdPago($idpago)
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idpago = " . $this->var2str($idpago) . ";");
    }

    public function deletebyIdDevolucion($iddevolucionprov)
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE iddevolucionprov = " . $this->var2str($iddevolucionprov) . ";");
    }

    public function deletebyIdAnticipo($idanticipoprov)
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idanticipoprov = " . $this->var2str($idanticipoprov) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idproveedor ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \trans_pagos($p);
            }
        }

        return $list;
    }

    public function all_by_idfacturaprov($idfacturaprov, $sin_ret = false)
    {
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idfacturaprov = " . $this->var2str($idfacturaprov);
        if ($sin_ret) {
            $sql .= " AND tipo != 'Retencion'";
        }
        $sql .= " ORDER BY fecha_trans ASC;";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \trans_pagos($p);
            }
        }

        return $list;
    }

    public function all_by_idpago($idpago)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idpago = " . $this->var2str($idpago) . " ORDER BY idtranspago ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \trans_pagos($p);
            }
        }

        return $list;
    }

    public function all_by_iddevolucionprov($iddevolucionprov)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE iddevolucionprov = " . $this->var2str($iddevolucionprov) . " ORDER BY idtranspago ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \trans_pagos($p);
            }
        }

        return $list;
    }

    public function get_pago_fact($idfacturaprov)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE esabono = " . $this->var2str(false) . " AND idfacturaprov = " . $this->var2str($idfacturaprov) . ";");
        if ($data) {
            return new \trans_pagos($data[0]);
        }

        return false;
    }

    public function get_pago_ncc($idfacturanc)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idfacturanc = " . $this->var2str($idfacturanc) . ";");
        if ($data) {
            return new \trans_pagos($data[0]);
        }

        return false;
    }

    public function getProveedor()
    {
        $prov0 = new \proveedores();
        return $prov0->get($this->idproveedor);
    }

    public function getFactura()
    {
        $fac0 = new \facturasprov();
        return $fac0->get($this->idfacturaprov);
    }

    public function getAnticipo()
    {
        $ant0 = new \anticiposprov();
        return $ant0->get($this->idanticipoprov);
    }

    public function getSaldoProveedor($idempresa, $idproveedor)
    {
        $data = $this->db->select("SELECT ROUND(SUM(debito - credito), 2) AS saldo FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idproveedor = " . $this->var2str($idproveedor) . " AND idfacturaprov NOT IN (SELECT idfacturaprov FROM facturasprov WHERE idproveedor = " . $this->var2str($idproveedor) . " AND anulado = TRUE);");
        if ($data) {
            return $data[0]['saldo'];
        }

        return 0;
    }

    public function getSaldosFacturas($idempresa, $query, $idproveedor, $estado, $fec_desde, $fec_hasta, $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();
        if ($estado == 2) {
            // Pagadas
            $sql = "SELECT
                    fc.idfacturaprov,
                    fc.fec_emision AS fec_emision,
                    fc.numero_documento AS numero_documento,
                    d.nombre AS documento,
                    fc.razonsocial,
                    fp.nombre AS formapago,
                    tc.num_doc,
                    tc.fecha_trans,
                    to_char(fc.fec_emision::date+fc.diascredito, 'YYYY-MM-DD') AS vencimiento,
                    tc.debito AS valor
                FROM
                    facturasprov fc
                    INNER JOIN trans_pagos tc ON tc.idfacturaprov = fc.idfacturaprov
                    INNER JOIN documentos d ON d.iddocumento = fc.iddocumento
                    INNER JOIN formaspago fp ON tc.idformapago = fp.idformapago
                WHERE
                    fc.idempresa = " . $this->var2str($idempresa) . " AND tc.debito > 0";
            if ($query != '') {
                $query = strtolower($query);
                $sql .= " AND (lower(fc.numero_documento) LIKE '%" . $query . "%' OR lower(tc.num_doc) LIKE '%" . $query . "%')";
            }
            if ($idproveedor != '') {
                $sql .= " AND fc.idproveedor = " . $this->var2str($idproveedor);
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
                        fc.idfacturaprov,
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
                        ( SELECT idfacturaprov AS idfac, SUM ( credito - debito ) AS saldo FROM trans_pagos GROUP BY idfacturaprov ) AS sd
                        INNER JOIN facturasprov fc ON sd.idfac = fc.idfacturaprov
                        INNER JOIN documentos d ON d.iddocumento = fc.iddocumento
                    WHERE fc.idempresa = " . $this->var2str($idempresa);
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
            if ($idproveedor != '') {
                $sql .= " AND fc.idproveedor = " . $this->var2str($idproveedor);
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

    public function pendientesPago($idempresa, $idproveedor)
    {
        $list = array();

        $sql = "SELECT
                    fc.idfacturaprov,
                    fc.coddocumento,
                    d.nombre AS documento,
                    fc.fec_emision AS fec_emision,
                    fc.numero_documento AS numero_documento,
                    fc.razonsocial,
                    fc.fec_emision AS fecha_trans,
                    to_char(fc.fec_emision::date+fc.diascredito, 'YYYY-MM-DD') AS vencimiento,
                    sd.saldo AS valor
                FROM
                    ( SELECT idfacturaprov AS idfac, SUM ( credito - debito ) AS saldo FROM trans_pagos GROUP BY idfacturaprov ) AS sd
                    INNER JOIN facturasprov fc ON sd.idfac = fc.idfacturaprov
                    INNER JOIN documentos d ON d.iddocumento = fc.iddocumento
                WHERE fc.idempresa = " . $this->var2str($idempresa) . " AND fc.anulado != " . $this->var2str(true) . " AND sd.saldo > 0 AND fc.idproveedor = " . $this->var2str($idproveedor);

        $sql .= " ORDER BY to_char(fc.fec_emision::date+fc.diascredito, 'YYYY-MM-DD') ASC";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $list;
    }

    public function getNumDocProveedor($idempresa, $idformapago, $num_doc, $idproveedor = '')
    {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idformapago = " . $this->var2str($idformapago) . " AND num_doc = " . $this->var2str($num_doc) . ";";
        $data = $this->db->select($sql);
        if ($data) {
            return true;
        }

        return false;
    }

    public function getEstadoCuentaProveedor($idempresa, $idproveedor = '', $fechadesde = '', $fechahasta = '', $idestablecimiento = '', $estado = 1)
    {
        $list = array();
        $sql  = "BEGIN;
                CREATE TEMPORARY TABLE
                IF NOT EXISTS saldosproveedores (
                    ID serial PRIMARY KEY,
                    idfacturaprov TEXT,
                    idproveedor INTEGER,
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
            $sql .= "INSERT INTO saldosproveedores ( idfacturaprov, idproveedor, razonsocial, identificacion, direccion, telefono, tipodoc, numero_documento, fec_emision, vencimiento, dias, total, abono, saldo ) SELECT
                    '' AS idfacturaprov,
                    tc.idproveedor,
                    f.razonsocial,
                    pr.identificacion,
                    pr.direccion,
                    pr.telefono,
                    'SALDO INICIAL' AS tipodoc,
                    '' AS numero_documento,
                    '" . $fechadesde . "' AS fec_emision,
                    '' AS vencimiento,
                    '' AS dias,
                    ROUND( SUM(tc.credito), 2 ) AS total,
                    ROUND( SUM(tc.debito), 2 ) AS abono,
                    ROUND( SUM ( tc.credito - tc.debito ), 2 ) AS saldo
                    FROM
                        facturasprov f
                        INNER JOIN trans_pagos tc ON tc.idfacturaprov = f.idfacturaprov
                        INNER JOIN proveedores pr ON pr.idproveedor = f.idproveedor
                    WHERE
                        f.anulado != " . $this->var2str(true) . "
                        AND f.idempresa = " . $this->var2str($idempresa) . "
                        AND f.fec_emision < " . $this->var2str($fechadesde);
            if ($idproveedor != '') {
                $sql .= " AND f.idproveedor = " . $this->var2str($idproveedor);
            }
            if ($idestablecimiento != '') {
                $sql .= " AND f.idestablecimiento = " . $this->var2str($idestablecimiento);
            }
            if ($fechahasta != '') {
                $sql .= " AND tc.fecha_trans <= " . $this->var2str($fechahasta);
            }
            $sql .= " GROUP BY tc.idproveedor, f.razonsocial, pr.identificacion, pr.direccion, pr.telefono;";
        }
        $sql .= "INSERT INTO saldosproveedores ( idfacturaprov, idproveedor, razonsocial, identificacion, direccion, telefono, tipodoc, numero_documento, fec_emision, vencimiento, dias, total, abono, saldo ) SELECT
            tc.idfacturaprov,
            tc.idproveedor,
            f.razonsocial,
            pr.identificacion,
            pr.direccion,
            pr.telefono,
            d.nombre AS tipodoc,
            f.numero_documento,
            f.fec_emision,
            to_char( f.fec_emision :: DATE + f.diascredito, 'YYYY-MM-DD' ) AS vencimiento,
            ( NOW( ) :: DATE - ( f.fec_emision :: DATE + f.diascredito ) ) AS dias,
            ROUND( SUM ( tc.credito ), 2 ) AS total,
            ROUND( SUM ( tc.debito ), 2 ) AS abono,
            ROUND( SUM ( tc.credito - tc.debito ), 2 ) AS saldo
            FROM
                facturasprov f
                INNER JOIN trans_pagos tc ON tc.idfacturaprov = f.idfacturaprov
                INNER JOIN proveedores pr ON pr.idproveedor = f.idproveedor
                INNER JOIN documentos d ON d.iddocumento = f.iddocumento
            WHERE
                f.anulado != " . $this->var2str(true) . "
                AND f.idempresa = " . $this->var2str($idempresa);
        if ($idproveedor != '') {
            $sql .= " AND f.idproveedor = " . $this->var2str($idproveedor);
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
        $sql .= " GROUP BY tc.idfacturaprov, tc.idproveedor, f.razonsocial, pr.identificacion, pr.direccion, pr.telefono, f.numero_documento, f.fec_emision, f.diascredito, d.nombre; COMMIT;";
        //Genero la tabla temporal
        $data = $this->db->exec($sql);
        if ($data) {
            //saco los resultados
            $sql = "SELECT * FROM saldosproveedores WHERE 1 = 1";
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

    public function all_by_anticipoprov($idempresa, $idanticipoprov)
    {
        $list = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idanticipoprov = " . $this->var2str($idanticipoprov);

        $data = $this->db->select($sql);

        if ($data) {
            foreach ($data as $key => $d) {
                $list[] = new trans_pagos($d);
            }
        }

        return $list;
    }

    public function getSaldosFavor($idempresa, $idproveedor = '')
    {
        $list = array();

        $list = array();
        $sql  = "BEGIN;
                CREATE TEMPORARY TABLE
                IF NOT EXISTS saldosfavorprov (
                    ID serial PRIMARY KEY,
                    iddocumento TEXT,
                    idproveedor INTEGER,
                    identificacion TEXT,
                    razonsocial TEXT,
                    numero_documento TEXT,
                    documento TEXT,
                    fecha_trans TEXT,
                    valor DECIMAL ( 16, 6 )
                );";
        // Consulta para obtener anticipos pendientes
        $sql .= "INSERT INTO saldosfavorprov ( iddocumento, idproveedor, identificacion, razonsocial, numero_documento, documento, fecha_trans, valor )
                SELECT
                    'A-' || an.idanticipoprov, an.idproveedor, pr.identificacion, pr.razonsocial, an.numero, 'Anticipo', an.fecha_trans, ROUND( SUM ( tp.debito - tp.credito ), 2)
                FROM
                    trans_pagos tp
                    INNER JOIN anticiposprov an ON an.idanticipoprov = tp.idanticipoprov
                    INNER JOIN proveedores pr ON an.idproveedor = pr.idproveedor
                WHERE
                    an.anulado = FALSE
                    AND an.idempresa = " . $this->var2str($idempresa);
        if ($idproveedor != '') {
            $sql .= " AND an.idproveedor = " . $this->var2str($idproveedor);
        }
        $sql .= " GROUP BY
                    an.idanticipoprov, an.idproveedor, pr.identificacion, pr.razonsocial, an.numero, an.fecha_trans
                HAVING
                    ROUND( SUM ( tp.debito - tp.credito ), 2) > 0;";
        // Consulta facturas con saldo a favor
        $sql .= "INSERT INTO saldosfavorprov ( iddocumento, idproveedor, identificacion, razonsocial, numero_documento, documento, fecha_trans, valor )
                SELECT
                    'F-' || fp.idfacturaprov, fp.idproveedor, fp.identificacion, fp.razonsocial, fp.numero_documento, d.nombre, fp.fec_emision, ROUND( SUM ( tp.debito - tp.credito ), 2)
                FROM
                    trans_pagos tp
                    INNER JOIN facturasprov fp ON fp.idfacturaprov = tp.idfacturaprov
                    INNER JOIN documentos d ON d.iddocumento = fp.iddocumento
                WHERE
                    fp.anulado = FALSE
                    AND fp.idempresa = " . $this->var2str($idempresa);
        if ($idproveedor != '') {
            $sql .= " AND fp.idproveedor = " . $this->var2str($idproveedor);
        }
        $sql .= " GROUP BY
                    fp.idfacturaprov, fp.idproveedor, fp.identificacion, fp.razonsocial, fp.numero_documento, d.nombre, fp.fec_emision
                HAVING
                    ROUND( SUM ( tp.debito - tp.credito ), 2) > 0;";

        //Genero la tabla temporal
        $data = $this->db->exec($sql);
        if ($data) {
            //saco los resultados
            $sql  = "SELECT * FROM saldosfavorprov ORDER BY fecha_trans ASC;";
            $data = $this->db->select($sql);
            if ($data) {
                $list = $data;
            }
        }

        return $list;
    }
}
