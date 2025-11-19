<?php

namespace GSC_Systems\model;

class trans_inventario extends \model
{
    public function __construct($data = false)
    {
        parent::__construct('trans_inventario');
        if ($data) {

            $this->idtransinv            = $data['idtransinv'];
            $this->idempresa             = $data['idempresa'];
            $this->idestablecimiento     = $data['idestablecimiento'];
            $this->idproveedor           = $data['idproveedor'];
            $this->idcliente             = $data['idcliente'];
            $this->idarticulo            = $data['idarticulo'];
            $this->idlineafacprov        = $data['idlineafacprov'];
            $this->idlineafaccli         = $data['idlineafaccli'];
            $this->idlineamovimiento     = $data['idlineamovimiento'];
            $this->idlinearegularizacion = $data['idlinearegularizacion'];
            $this->origen                = $data['origen'];
            $this->url                   = $data['url'];
            $this->nomestab              = $data['nomestab'];
            $this->fec_trans             = Date('d-m-Y H:i:s', strtotime($data['fec_trans']));
            $this->fecha                 = Date('d-m-Y', strtotime($data['fecha']));
            $this->hora                  = Date('H:i:s', strtotime($data['hora']));
            $this->egresos               = floatval($data['egresos']);
            $this->ingresos              = floatval($data['ingresos']);
            $this->movimiento            = floatval($data['movimiento']);
            $this->costo                 = floatval($data['costo']);
            $this->costototal            = floatval($data['costototal']);
            $this->aplica_stock          = $this->str2bool($data['aplica_stock']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idtransinv            = null;
            $this->idempresa             = null;
            $this->idestablecimiento     = null;
            $this->idproveedor           = null;
            $this->idcliente             = null;
            $this->idarticulo            = null;
            $this->idlineafacprov        = null;
            $this->idlineafaccli         = null;
            $this->idlineamovimiento     = null;
            $this->idlinearegularizacion = null;
            $this->origen                = null;
            $this->url                   = null;
            $this->nomestab              = null;
            $this->fec_trans             = null;
            $this->fecha                 = null;
            $this->hora                  = null;
            $this->egresos               = 0;
            $this->ingresos              = 0;
            $this->movimiento            = 0;
            $this->costo                 = 0;
            $this->costototal            = 0;
            $this->aplica_stock          = false;
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
        new \establecimiento();
        new \proveedores();
        new \clientes();
        new \articulos();
        new \lineasfacturasprov();
        new \lineasfacturascli();
        new \lineasmovimientos();
        new \lineasregularizaciones();

        $sql = "";
        return $sql;
    }

    public function url()
    {
        return '';
    }

    public function get($idtransinv)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idtransinv = " . $this->var2str($idtransinv) . ";");
        if ($data) {
            return new \trans_inventario($data[0]);
        }

        return false;
    }

    public function get_idlineafacprov($idlineafacprov)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineafacprov = " . $this->var2str($idlineafacprov) . ";");
        if ($data) {
            return new \trans_inventario($data[0]);
        }

        return false;
    }

    public function get_idlineafaccli($idlineafaccli)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineafaccli = " . $this->var2str($idlineafaccli) . ";");
        if ($data) {
            return new \trans_inventario($data[0]);
        }

        return false;
    }

    public function all_idlineafaccli($idlineafaccli)
    {
        $list = array();
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineafaccli = " . $this->var2str($idlineafaccli) . ";");
        if ($data) {
            foreach ($data as $key => $d) {
                $list[] = new \trans_inventario($d);
            }
        }

        return $list;
    }

    public function get_idlineamovimiento($idlineamovimiento)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineamovimiento = " . $this->var2str($idlineamovimiento) . ";");
        if ($data) {
            return new \trans_inventario($data[0]);
        }

        return false;
    }

    public function get_idlinearegularizacion($idlinearegularizacion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlinearegularizacion = " . $this->var2str($idlinearegularizacion) . ";");
        if ($data) {
            return new \trans_inventario($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idtransinv)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idtransinv = " . $this->var2str($this->idtransinv) . ";");
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
                . ", idestablecimiento = " . $this->var2str($this->idestablecimiento)
                . ", idproveedor = " . $this->var2str($this->idproveedor)
                . ", idcliente = " . $this->var2str($this->idcliente)
                . ", idarticulo = " . $this->var2str($this->idarticulo)
                . ", idlineafacprov = " . $this->var2str($this->idlineafacprov)
                . ", idlineafaccli = " . $this->var2str($this->idlineafaccli)
                . ", idlineamovimiento = " . $this->var2str($this->idlineamovimiento)
                . ", idlinearegularizacion = " . $this->var2str($this->idlinearegularizacion)
                . ", origen = " . $this->var2str($this->origen)
                . ", url = " . $this->var2str($this->url)
                . ", nomestab = " . $this->var2str($this->nomestab)
                . ", fec_trans = " . $this->var2str($this->fec_trans)
                . ", fecha = " . $this->var2str($this->fecha)
                . ", hora = " . $this->var2str($this->hora)
                . ", egresos = " . $this->var2str($this->egresos)
                . ", ingresos = " . $this->var2str($this->ingresos)
                . ", movimiento = " . $this->var2str($this->movimiento)
                . ", costo = " . $this->var2str($this->costo)
                . ", costototal = " . $this->var2str($this->costototal)
                . ", aplica_stock = " . $this->var2str($this->aplica_stock)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idtransinv = " . $this->var2str($this->idtransinv) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idestablecimiento, idproveedor, idcliente, idarticulo, idlineafacprov, idlineafaccli, idlineamovimiento, idlinearegularizacion, origen, url, nomestab, fec_trans, fecha, hora, egresos, ingresos, movimiento, costo, costototal, aplica_stock, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idestablecimiento)
                . "," . $this->var2str($this->idproveedor)
                . "," . $this->var2str($this->idcliente)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->idlineafacprov)
                . "," . $this->var2str($this->idlineafaccli)
                . "," . $this->var2str($this->idlineamovimiento)
                . "," . $this->var2str($this->idlinearegularizacion)
                . "," . $this->var2str($this->origen)
                . "," . $this->var2str($this->url)
                . "," . $this->var2str($this->nomestab)
                . "," . $this->var2str($this->fec_trans)
                . "," . $this->var2str($this->fecha)
                . "," . $this->var2str($this->hora)
                . "," . $this->var2str($this->egresos)
                . "," . $this->var2str($this->ingresos)
                . "," . $this->var2str($this->movimiento)
                . "," . $this->var2str($this->costo)
                . "," . $this->var2str($this->costototal)
                . "," . $this->var2str($this->aplica_stock)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idtransinv = $this->db->lastval();
                    $this->afterSave();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        $sql  = "DELETE FROM " . $this->table_name . " WHERE idtransinv = " . $this->var2str($this->idtransinv) . ";";
        $paso = $this->db->exec($sql);
        if ($paso) {
            $this->afterDelete();
            return true;
        }
        return false;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idestablecimiento ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \trans_inventario($p);
            }
        }

        return $list;
    }

    private function afterDelete()
    {
        if ($this->aplica_stock) {
            //busco el stock para regularizar
            $stock0 = new \stocks();
            $stock  = $stock0->get_by_idestab_idart($this->idestablecimiento, $this->idarticulo);
            if ($stock) {
                $stock->stock += ($this->movimiento * -1);
                $stock->save();
            }

        }
    }

    private function afterSave()
    {
        if ($this->aplica_stock) {
            //busco el stock para regularizar
            $stock0 = new \stocks();
            $stock  = $stock0->get_by_idestab_idart($this->idestablecimiento, $this->idarticulo);
            if (!$stock) {
                $stock                    = new \stocks();
                $stock->idempresa         = $this->idempresa;
                $stock->idestablecimiento = $this->idestablecimiento;
                $stock->idarticulo        = $this->idarticulo;
                $stock->fec_creacion      = $this->fec_creacion;
                $stock->nick_creacion     = $this->nick_creacion;
            }
            $stock->stock += $this->movimiento;
            $stock->save();
        }
    }

    public function get_by_articulo($idarticulo, $idestablecimiento = false)
    {
        $lista = array();
        $sql   = "SELECT fecha, hora, origen, url, ingresos, egresos, movimiento FROM " . $this->table_name . " WHERE aplica_stock = " . $this->var2str(true) . " AND idarticulo = " . $this->var2str($idarticulo);

        if ($idestablecimiento) {
            $sql .= " AND idestablecimiento = " . $this->var2str($idestablecimiento);
        }

        $sql .= " ORDER BY fec_trans ASC";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $lista;
    }

    public function getstock_by_articulo($idarticulo, $idestablecimiento = false)
    {
        $stock = 0;
        $sql   = "SELECT sum(movimiento) as stock FROM " . $this->table_name . " WHERE aplica_stock = " . $this->var2str(true) . " AND idarticulo = " . $this->var2str($idarticulo);

        if ($idestablecimiento) {
            $sql .= " AND idestablecimiento = " . $this->var2str($idestablecimiento);
        }

        $data = $this->db->select($sql);
        if ($data) {
            $stock = $data[0]['stock'];
        }

        return $stock;
    }

    public function get_recalculo_stock($idempresa, $idarticulo = false, $idestablecimiento = false, $fec_trans = false)
    {
        $list = array();

        $sql = "SELECT idarticulo, idestablecimiento, SUM(movimiento) AS stock FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND aplica_stock = " . $this->var2str(true);
        if ($idarticulo) {
            $sql .= " AND idarticulo = " . $this->var2str($idarticulo);
        }
        if ($idestablecimiento) {
            $sql .= " AND idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($fec_trans) {
            $sql .= " AND fec_trans < " . $this->var2str($fec_trans);
        }
        $sql .= " GROUP BY idarticulo, idestablecimiento;";

        $data = $this->db->select($sql);

        if ($data) {
            return $data;
        }

        return $list;
    }

    public function actualizar_cliente($idempresa, $idfacturacli, $idcliente)
    {
        $sql = "UPDATE trans_inventario SET idcliente = " . $this->var2str($idcliente) . " WHERE idlineafaccli IN (SELECT idlineafaccli FROM lineasfacturascli WHERE idempresa = " . $this->var2str($idempresa) . " AND idfacturacli = " . $this->var2str($idfacturacli) . ")";

        return $this->db->exec($sql);
    }

    public function actualizar_proveedor($idempresa, $idfacturaprov, $idproveedor)
    {
        $sql = "UPDATE trans_inventario SET idproveedor = " . $this->var2str($idproveedor) . " WHERE idlineafacprov IN (SELECT idlineafacprov FROM lineasfacturasprov WHERE idempresa = " . $this->var2str($idempresa) . " AND idfacturaprov = " . $this->var2str($idfacturaprov) . ")";

        return $this->db->exec($sql);
    }

    public function existenciasArticulos($idempresa, $idarticulo = '', $fechahasta = '', $idestablecimiento = '', $idmarca = '', $idgrupo = '', $estado = '', $fec_hasta = '')
    {
        $list = array();

        $sql = "SELECT
                    ti.idarticulo,
                    ar.codprincipal,
                    ar.nombre,
                    COALESCE ( ma.nombre, '-' ) AS marca,
                    COALESCE ( gr.nombre, '-' ) AS grupo,
                    SUM ( ti.ingresos ) AS ingresos,
                    SUM ( ti.egresos ) AS egresos,
                    SUM ( ti.movimiento ) AS stock
                FROM trans_inventario ti
                    INNER JOIN articulos ar ON ar.idarticulo = ti.idarticulo
                    LEFT JOIN marcas ma ON ma.idmarca = ar.idmarca
                    LEFT JOIN grupos gr ON gr.idgrupo = ar.idgrupo
                WHERE
                    ti.aplica_stock = " . $this->var2str(true) . "
                    AND ti.idempresa = " . $this->var2str($idempresa);
        if ($idarticulo != '') {
            $sql .= " AND ti.idarticulo = " . $this->var2str($idarticulo);
        }
        if ($fechahasta != '') {
            $sql .= " AND ti.fecha <= " . $this->var2str($fechahasta);
        }
        if ($fec_hasta != '') {
            $sql .= " AND ti.fec_trans <= " . $this->var2str(date('Y-m-d H:i:s', strtotime($fec_hasta)));
        }
        if ($idestablecimiento != '') {
            $sql .= " AND ti.idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($idmarca != '') {
            $sql .= " AND ar.idmarca = " . $this->var2str($idmarca);
        }
        if ($idgrupo != '') {
            $sql .= " AND ar.idgrupo = " . $this->var2str($idgrupo);
        }
        $sql .= " GROUP BY ti.idarticulo, ar.codprincipal, ar.nombre, ma.nombre, gr.nombre";

        if ($estado == 2) {
            $sql .= " HAVING (SUM ( ti.movimiento )) > 0";
        } else if ($estado == 3) {
            $sql .= " HAVING (SUM ( ti.movimiento )) < 0";
        } else if ($estado == 4) {
            $sql .= " HAVING (SUM ( ti.movimiento )) = 0";
        }

        $data = $this->db->select($sql);

        if ($data) {
            return $data;
        }

        return $list;
    }

    public function getCostoArticulo($idempresa, $idarticulo, $fechahasta = '', $horahasta = '', $opcion = '2', $compuesto = false)
    {
        $fec_trans = date('Y-12-31');
        if ($fechahasta != '') {
            $fec_trans = $fechahasta;
        }
        $hor_hasta = '23:59:59';
        if ($horahasta != '') {
            $hor_hasta = $horahasta;
        }

        $fecha = $fec_trans . " " . $hor_hasta;

        $costoinv = 'costopromedio';
        $param0   = new \parametrizacion();
        $param    = $param0->all_by_codigo($idempresa, 'costoinv');
        if ($param) {
            $costoinv = $param->valor;
        }

        if ($costoinv == 'costopromedio') {
            return $this->getCostoPromedio($idempresa, $idarticulo, $fecha, $opcion, $compuesto);
        } else {
            return $this->getUltimoCosto($idempresa, $idarticulo, $fecha, $compuesto);
        }

        return 0;
    }

    public function getCostoPromedio($idempresa, $idarticulo, $fecha = '', $opcion = '2', $compuesto = false)
    {
        if ($fecha == '') {
            $fecha = date('Y-12-31') . " " . '23:59:59';
        }
        $sql  = "SELECT costopromedio('" . $idempresa . "', '" . $idarticulo . "', '" . $fecha . "', '" . $opcion . "')";
        $data = $this->db->select($sql);
        if ($data) {
            return $data[0]['costopromedio'];
        }

        return 0;
    }

    public function getUltimoCosto($idempresa, $idarticulo, $fecha = '', $compuesto = false)
    {
        if ($fecha == '') {
            $fecha = date('Y-12-31') . " " . '23:59:59';
        }
        $sql  = "SELECT costo FROM trans_inventario WHERE idarticulo = " . $this->var2str($idarticulo) . " AND idempresa = " . $this->var2str($idempresa) . " AND aplica_stock = " . $this->var2str(true) . " AND movimiento > " . $this->var2str(0) . " AND origen NOT LIKE 'Nota de credito de Venta%' AND fec_trans < " . $this->var2str($fecha) . " ORDER BY fec_trans DESC";
        $data = $this->db->select_limit($sql, 1, 0);
        if ($data) {
            return $data[0]['costo'];
        }

        return 0;
    }

    public function getReportexArticulo($idempresa, $idarticulo = '', $fec_desde = '', $fec_hasta = '', $idestablecimiento = '', $idgrupo = '', $idmarca = '')
    {
        $list = array();
        $sql  = "SELECT
                    tr.idestablecimiento,
                    es.nombre AS nomestab,
                    tr.idarticulo,
                    ar.codprincipal,
                    ar.nombre AS nomart,
                    SUM(CASE WHEN tr.fecha < " . $this->var2str($fec_desde) . " THEN tr.movimiento ELSE 0 END) AS inicial,
                    SUM(CASE WHEN tr.movimiento > 0 AND tr.fecha >= " . $this->var2str($fec_desde) . " AND tr.fecha <= " . $this->var2str($fec_hasta) . " THEN tr.movimiento ELSE 0 END) AS ingresos,
                    SUM(CASE WHEN tr.movimiento < 0 AND tr.fecha >= " . $this->var2str($fec_desde) . " AND tr.fecha <= " . $this->var2str($fec_hasta) . " THEN (tr.movimiento * -1) ELSE 0 END) AS venta,
                    SUM(CASE WHEN tr.fecha <= " . $this->var2str($fec_hasta) . " THEN tr.movimiento ELSE 0 END) AS saldo
                FROM
                    trans_inventario tr INNER JOIN establecimiento es ON es.idestablecimiento = tr.idestablecimiento
                    INNER JOIN articulos ar ON ar.idarticulo = tr.idarticulo
                WHERE
                    tr.idempresa = " . $this->var2str($idempresa) . " AND tr.aplica_stock = TRUE";
        if ($idarticulo != '') {
            $sql .= " AND tr.idarticulo = ".$this->var2str($idarticulo);
        }
        if ($idestablecimiento != '') {
            $sql .= " AND tr.idestablecimiento = ".$this->var2str($idestablecimiento);
        }
        if ($idgrupo != '') {
            $sql .= " AND ar.idgrupo = ".$this->var2str($idgrupo);
        }
        if ($idmarca != '') {
            $sql .= " AND ar.idmarca = ".$this->var2str($idmarca);
        }
        $sql .= " GROUP BY tr.idestablecimiento, tr.idarticulo, es.nombre, ar.codprincipal, ar.nombre
                HAVING
                    (SUM(CASE WHEN tr.fecha < " . $this->var2str($fec_desde) . " THEN tr.movimiento ELSE 0 END) > 0 OR SUM(CASE WHEN tr.movimiento > 0 AND tr.fecha >= " . $this->var2str($fec_desde) . " AND tr.fecha <= " . $this->var2str($fec_hasta) . " THEN tr.movimiento ELSE 0 END) > 0 OR SUM(CASE WHEN tr.movimiento < 0 AND tr.fecha >= " . $this->var2str($fec_desde) . " AND tr.fecha <= " . $this->var2str($fec_hasta) . " THEN (tr.movimiento * -1) ELSE 0 END) > 0 OR SUM(CASE WHEN tr.fecha <= " . $this->var2str($fec_hasta) . " THEN tr.movimiento ELSE 0 END) != 0)
                ORDER BY
                    es.nombre ASC,
                    ar.nombre ASC";
        $data = $this->db->select($sql);
        if ($data) {
            $list = $data;
        }
        return $list;
    }
}