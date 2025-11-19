<?php

namespace GSC_Systems\model;

class lineasfacturasprov extends \model
{
    public $url_art;
    public $cant_ant;
    public function __construct($data = false)
    {
        parent::__construct('lineasfacturasprov');
        if ($data) {

            $this->idlineafacprov = $data['idlineafacprov'];
            $this->idfacturaprov  = $data['idfacturaprov'];
            $this->idarticulo     = $data['idarticulo'];
            $this->idimpuesto     = $data['idimpuesto'];
            $this->codprincipal   = $data['codprincipal'];
            $this->descripcion    = $data['descripcion'];
            $this->cantidad       = floatval($data['cantidad']);
            $this->pvpunitario    = floatval($data['pvpunitario']);
            $this->dto            = floatval($data['dto']);
            $this->pvptotal       = floatval($data['pvptotal']);
            $this->pvpsindto      = floatval($data['pvpsindto']);
            $this->valorice       = floatval($data['valorice']);
            $this->valoriva       = floatval($data['valoriva']);
            $this->valorirbp      = floatval($data['valorirbp']);
            //Almaceno identificador de retenciones
            $this->idretencion_renta = $data['idretencion_renta'];
            $this->idretencion_iva   = $data['idretencion_iva'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

            $this->url_art  = $this->get_url_articulo();
            $this->cant_ant = floatval($data['cantidad']);

        } else {
            $this->idlineafacprov    = null;
            $this->idfacturaprov     = null;
            $this->idarticulo        = null;
            $this->idimpuesto        = null;
            $this->codprincipal      = null;
            $this->descripcion       = null;
            $this->cantidad          = 0;
            $this->pvpunitario       = 0;
            $this->dto               = 0;
            $this->pvptotal          = 0;
            $this->pvpsindto         = 0;
            $this->valorice          = 0;
            $this->valoriva          = 0;
            $this->valorirbp         = 0;
            $this->idretencion_renta = null;
            $this->idretencion_iva   = null;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;

            $this->url_art  = '';
            $this->cant_ant = 0;
        }
    }

    public function install()
    {
        new \facturasprov();
        new \impuestos();
        new \articulos();
        new \tiposretenciones();
        
        return "";
    }

    public function url()
    {
        return '';
    }

    public function get($idlineafacprov)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineafacprov = " . $this->var2str($idlineafacprov) . ";");
        if ($data) {
            return new \lineasfacturasprov($data[0]);
        }

        return false;
    }

    public function get_factura()
    {
        $fac0 = new \facturasprov();
        $fac  = $fac0->get($this->idfacturaprov);
        if ($fac) {
            return $fac;
        }
        return false;
    }

    public function exists()
    {
        if (is_null($this->idlineafacprov)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineafacprov = " . $this->var2str($this->idlineafacprov) . ";");
    }

    public function test()
    {
        $status = true;

        return $status;
    }

    public function save($control = true)
    {
        if ($this->test()) {
            $insert = false;
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET idfacturaprov = " . $this->var2str($this->idfacturaprov)
                . ", idarticulo = " . $this->var2str($this->idarticulo)
                . ", idimpuesto = " . $this->var2str($this->idimpuesto)
                . ", codprincipal = " . $this->var2str($this->codprincipal)
                . ", descripcion = " . $this->var2str($this->descripcion)
                . ", cantidad = " . $this->var2str($this->cantidad)
                . ", pvpunitario = " . $this->var2str($this->pvpunitario)
                . ", dto = " . $this->var2str($this->dto)
                . ", pvptotal = " . $this->var2str($this->pvptotal)
                . ", pvpsindto = " . $this->var2str($this->pvpsindto)
                . ", valorice = " . $this->var2str($this->valorice)
                . ", valoriva = " . $this->var2str($this->valoriva)
                . ", valorirbp = " . $this->var2str($this->valorirbp)
                . ", idretencion_renta = " . $this->var2str($this->idretencion_renta)
                . ", idretencion_iva = " . $this->var2str($this->idretencion_iva)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idlineafacprov = " . $this->var2str($this->idlineafacprov) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idfacturaprov, idarticulo, idimpuesto, codprincipal, descripcion, cantidad, pvpunitario, dto, pvptotal, pvpsindto, valorice, valoriva, valorirbp, idretencion_renta, idretencion_iva, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idfacturaprov)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->idimpuesto)
                . "," . $this->var2str($this->codprincipal)
                . "," . $this->var2str($this->descripcion)
                . "," . $this->var2str($this->cantidad)
                . "," . $this->var2str($this->pvpunitario)
                . "," . $this->var2str($this->dto)
                . "," . $this->var2str($this->pvptotal)
                . "," . $this->var2str($this->pvpsindto)
                . "," . $this->var2str($this->valorice)
                . "," . $this->var2str($this->valoriva)
                . "," . $this->var2str($this->valorirbp)
                . "," . $this->var2str($this->idretencion_renta)
                . "," . $this->var2str($this->idretencion_iva)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }
            if ($this->beforeSave($control)) {
                if ($this->db->exec($sql)) {
                    if ($insert) {
                        $this->idlineafacprov = $this->db->lastval();
                    }
                    $this->afterSave();
                    return true;
                }
            }
        }

        return false;
    }

    public function beforeSave($control)
    {
        if (!$control) {
            return true;
        }
        if ($fac = $this->get_factura()) {
            if ($fac->estado_sri == 'AUTORIZADO') {
                $this->new_error_msg("El documento se encuentra autorizado no es posible modificar.");
                return false;
            }
            if ($fac->anulado) {
                $this->new_error_msg("El documento se encuentra anulado no es posible modificar.");
                return false;
            }
            if ($fac->estado_sri_ret == 'AUTORIZADO') {
                $this->new_error_msg("La retencion del documento se encuentra autorizada no es posible modificar.");
                return false;
            }
            if (complemento_exists('contabilidad')) {
                if ($fac->idejercicio) {
                    $ejer0     = new \ejercicios();
                    $ejercicio = $ejer0->get($fac->idejercicio);
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
        //valido si no tiene pagos
        $pagos0 = new \trans_pagos();
        $pagos  = $pagos0->all_by_idfacturaprov($this->idfacturaprov, true);
        if (count($pagos) > 1) {
            $this->new_error_msg("Tiene cobros Asociados al Documento, no se puede agregar el detalle.");
            return false;
        }
        return true;
    }

    public function delete()
    {
        if ($this->beforeDelete()) {
            $sql  = "DELETE FROM " . $this->table_name . " WHERE idlineafacprov = " . $this->var2str($this->idlineafacprov) . ";";
            $paso = $this->db->exec($sql);
            if ($paso) {
                return true;
            }
        }
        return false;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY dto DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \lineasfacturasprov($p);
            }
        }

        return $list;
    }

    public function all_by_idfacturaprov($idfacturaprov)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idfacturaprov = " . $this->var2str($idfacturaprov) . " ORDER BY idlineafacprov ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \lineasfacturasprov($p);
            }
        }

        return $list;
    }

    public function get_impuesto()
    {
        $impuestos = new \impuestos();
        $impuesto  = $impuestos->get($this->idimpuesto);
        if ($impuesto) {
            return $impuesto->codigo;
        }

        return '';
    }

    public function get_porcentaje_impuesto()
    {
        $impuestos = new \impuestos();
        $impuesto  = $impuestos->get($this->idimpuesto);
        if ($impuesto) {
            return $impuesto->porcentaje;
        }

        return 0;
    }

    public function get_total()
    {
        return round($this->pvptotal + $this->valorice + $this->valoriva + $this->valorirbp, 6);
    }

    private function get_url_articulo()
    {
        if ($this->idarticulo) {
            $art0 = new articulos();
            $art  = $art0->get($this->idarticulo);
            if ($art) {
                return $art->url();
            }
        }

        return '';
    }

    private function get_articulo()
    {
        if ($this->idarticulo) {
            $art0 = new articulos();
            $art  = $art0->get($this->idarticulo);
            if ($art) {
                return $art;
            }
        }

        return false;
    }

    private function afterSave()
    {
        if ($fac = $this->get_factura()) {
            if ($art = $this->get_articulo()) {
                $trans = new \trans_inventario();
                $tr    = $trans->get_idlineafacprov($this->idlineafacprov);
                if ($tr) {
                    $tr->delete();
                }

                $tran                    = new \trans_inventario();
                $tran->idempresa         = $fac->idempresa;
                $tran->idestablecimiento = $fac->idestablecimiento;
                $tran->idproveedor       = $fac->idproveedor;
                $tran->idarticulo        = $this->idarticulo;
                $tran->idlineafacprov    = $this->idlineafacprov;
                $tran->origen            = $fac->get_tipodoc() . " de Compra - " . $fac->numero_documento;
                $tran->url               = $fac->url();
                $tran->nomestab          = $fac->get_nombreestablecimiento();
                $tran->fec_trans         = date('Y-m-d H:i:s', strtotime($fac->fec_emision . " " . $fac->hora_emision));
                $tran->fecha             = $fac->fec_emision;
                $tran->hora              = $fac->hora_emision;
                if ($fac->coddocumento == '04') {
                    $tran->movimiento = (0 - $this->cantidad);
                    $tran->egresos    = $this->cantidad;
                } else {
                    $tran->movimiento = ($this->cantidad);
                    $tran->ingresos   = $this->cantidad;
                }
                $tran->costo      = ($this->pvptotal / $this->cantidad);
                $tran->costototal = $this->pvptotal;
                if ($art->tipo == 1 && $fac->anulado != 1) {
                    $tran->aplica_stock = true;
                } else {
                    $tran->aplica_stock = false;
                }

                $tran->fec_creacion  = $this->fec_creacion;
                $tran->nick_creacion = $this->nick_creacion;

                $tran->save();
            }
        }

        return false;
    }

    private function beforeDelete()
    {
        if ($fac = $this->get_factura()) {
            if ($fac->anulado) {
                $this->new_error_msg("El documento se encuentra anulado no es posible eliminar.");
                return false;
            }
            if ($fac->estado_sri == 'AUTORIZADO') {
                $this->new_error_msg("El documento se encuentra autorizado no es posible eliminar.");
                return false;
            }
            if ($fac->estado_sri_ret == 'AUTORIZADO') {
                $this->new_error_msg("La retencion del documento se encuentra autorizada no es posible eliminar.");
                return false;
            }

            if (complemento_exists('contabilidad')) {
                if ($fac->idejercicio) {
                    $ejer0     = new \ejercicios();
                    $ejercicio = $ejer0->get($fac->idejercicio);
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
        //valido si no tiene pagos
        $pagos0 = new \trans_pagos();
        $pagos  = $pagos0->all_by_idfacturaprov($this->idfacturaprov, true);
        if (count($pagos) > 1) {
            $this->new_error_msg("Tiene pagos Asociados al Documento, no se puede eliminar.");
            return false;
        }

        $trans = new \trans_inventario();
        $tr    = $trans->get_idlineafacprov($this->idlineafacprov);
        if ($tr) {
            return $tr->delete();
        }

        return true;
    }

    public function listado_ret_iva($idempresa = false, $fec_desde = false, $fec_hasta = false, $idestablecimiento = false, $idproveedor = false)
    {
        $lista = array();

        $sql = "SELECT f.numero_retencion, f.razonsocial, f.numero_documento, f.fec_emision, f.fec_autorizacion_ret, t.codigo, t.codigobase, l.idretencion_iva, t.nombre, SUM(l.valoriva) AS baseimp, t.porcentaje, SUM(ROUND(l.valoriva * (t.porcentaje/100), 6)) AS valret FROM lineasfacturasprov l INNER JOIN facturasprov f ON f.idfacturaprov = l.idfacturaprov INNER JOIN tiposretenciones t ON t.idtiporetencion = l.idretencion_iva WHERE f.anulado != ".$this->var2str(true);
        if ($fec_desde) {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND f.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idestablecimiento) {
            $sql .= " AND f.idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($idproveedor) {
            $sql .= " AND f.idproveedor = " . $this->var2str($idproveedor);
        }
        if ($idempresa) {
            $sql .= " AND f.idempresa = " . $this->var2str($idempresa);
        }

        $sql .= " GROUP BY l.idretencion_iva, f.numero_retencion, f.razonsocial, f.numero_documento, f.fec_emision, f.fec_autorizacion_ret, t.codigo, t.codigobase, t.nombre, t.porcentaje ORDER BY l.idretencion_iva ASC, f.numero_retencion ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $lista;
    }

    public function listado_ret_renta($idempresa = false, $fec_desde = false, $fec_hasta = false, $idestablecimiento = false, $idproveedor = false)
    {
        $lista = array();
        
        $sql = "SELECT f.numero_retencion, f.razonsocial, f.numero_documento, f.fec_emision, f.fec_autorizacion_ret, t.codigo, t.codigobase, l.idretencion_renta, t.nombre, SUM(l.pvptotal) AS baseimp, t.porcentaje, SUM(ROUND(l.pvptotal * (t.porcentaje/100), 6)) AS valret FROM lineasfacturasprov l INNER JOIN facturasprov f ON f.idfacturaprov = l.idfacturaprov INNER JOIN tiposretenciones t ON t.idtiporetencion = l.idretencion_renta WHERE f.anulado != ".$this->var2str(true);
        if ($fec_desde) {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND f.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idestablecimiento) {
            $sql .= " AND f.idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($idproveedor) {
            $sql .= " AND f.idproveedor = " . $this->var2str($idproveedor);
        }
        if ($idempresa) {
            $sql .= " AND f.idempresa = " . $this->var2str($idempresa);
        }

        $sql .= " GROUP BY l.idretencion_renta, f.numero_retencion, f.razonsocial, f.numero_documento, f.fec_emision, f.fec_autorizacion_ret, t.codigo, t.codigobase, t.nombre, t.porcentaje ORDER BY l.idretencion_renta ASC, f.numero_retencion ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $lista;
    }

    public function listado_ret_renta_formulario($idempresa = false, $fec_desde = false, $fec_hasta = false)
    {
        $lista = array();
        
        $sql = "SELECT t.codigobase, l.idretencion_renta, SUM(l.pvptotal) AS baseimp, t.porcentaje, SUM(ROUND(l.pvptotal * (t.porcentaje/100), 6)) AS valret FROM lineasfacturasprov l INNER JOIN facturasprov f ON f.idfacturaprov = l.idfacturaprov INNER JOIN tiposretenciones t ON t.idtiporetencion = l.idretencion_renta WHERE f.anulado != ".$this->var2str(true);
        if ($fec_desde) {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND f.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idempresa) {
            $sql .= " AND f.idempresa = " . $this->var2str($idempresa);
        }

        $sql .= " GROUP BY l.idretencion_renta, t.codigobase, t.porcentaje";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $lista;
    }

    public function listado_compras_formulario($idempresa = false, $fec_desde = false, $fec_hasta = false)
    {
        $lista = array();

        $sql = "SELECT f.coddocumento, s.codigo AS sustento, i.codigo, ar.tipo, i.porcentaje, SUM ( l.pvptotal + l.valorice) AS base FROM lineasfacturasprov l INNER JOIN facturasprov f ON f.idfacturaprov = l.idfacturaprov INNER JOIN articulos ar ON ar.idarticulo = l.idarticulo INNER JOIN impuestos i ON i.idimpuesto = l.idimpuesto INNER JOIN sustentos s ON s.idsustento = f.idsustento WHERE f.anulado != ".$this->var2str(true);

        if ($fec_desde) {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND f.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idempresa) {
            $sql .= " AND f.idempresa = " . $this->var2str($idempresa);
        }

        $sql .= " GROUP BY f.coddocumento, s.codigo, i.codigo, ar.tipo, i.porcentaje";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $lista;
    }

    public function get_by_idempresa($idempresa)
    {
        $lista = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE idfacturaprov IN (SELECT idfacturaprov FROM facturasprov WHERE idempresa = ".$this->var2str($idempresa).")";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $lista[] = new \lineasfacturasprov($p);
            }
        }

        return $lista;
    }

    public function editar_retencion_masiva($idretencion_renta, $idretencion_iva, $idfacturaprov)
    {
        $sql = "UPDATE ".$this->table_name." SET idretencion_renta = ".$this->var2str($idretencion_renta).", idretencion_iva = ".$this->var2str($idretencion_iva)." WHERE idfacturaprov = ".$this->var2str($idfacturaprov);

        return $this->db->exec($sql);
    }
}
