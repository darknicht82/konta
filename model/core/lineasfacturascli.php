<?php

namespace GSC_Systems\model;

class lineasfacturascli extends \model
{
    public $url_art;
    public $cant_ant;
    public function __construct($data = false)
    {
        parent::__construct('lineasfacturascli');
        if ($data) {

            $this->idlineafaccli = $data['idlineafaccli'];
            $this->idfacturacli  = $data['idfacturacli'];
            $this->idarticulo    = $data['idarticulo'];
            $this->idimpuesto    = $data['idimpuesto'];
            $this->codprincipal  = $data['codprincipal'];
            $this->descripcion   = $data['descripcion'];
            $this->cantidad      = floatval($data['cantidad']);
            $this->pvpunitario   = floatval($data['pvpunitario']);
            $this->dto           = floatval($data['dto']);
            $this->pvptotal      = floatval($data['pvptotal']);
            $this->pvpsindto     = floatval($data['pvpsindto']);
            $this->valorice      = floatval($data['valorice']);
            $this->valoriva      = floatval($data['valoriva']);
            $this->idunidad      = $data['idunidad'];
            $this->factor        = floatval($data['factor']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

            $this->url_art  = $this->get_url_articulo();
            $this->cant_ant = floatval($data['cantidad']);
            $this->medida   = $this->get_um();

        } else {
            $this->idlineafaccli = null;
            $this->idfacturacli  = null;
            $this->idarticulo    = null;
            $this->idimpuesto    = null;
            $this->codprincipal  = null;
            $this->descripcion   = null;
            $this->cantidad      = 0;
            $this->pvpunitario   = 0;
            $this->dto           = 0;
            $this->pvptotal      = 0;
            $this->pvpsindto     = 0;
            $this->valorice      = 0;
            $this->valoriva      = 0;
            $this->idunidad      = null;
            $this->factor        = 1;
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
        new \facturascli();
        new \impuestos();
        new \articulos();

        return "";
    }

    public function url()
    {
        return '';
    }

    public function get($idlineafaccli)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineafaccli = " . $this->var2str($idlineafaccli) . ";");
        if ($data) {
            return new \lineasfacturascli($data[0]);
        }

        return false;
    }

    public function get_factura()
    {
        $fac0 = new \facturascli();
        $fac  = $fac0->get($this->idfacturacli);
        if ($fac) {
            return $fac;
        }
        return false;
    }

    public function exists()
    {
        if (is_null($this->idlineafaccli)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineafaccli = " . $this->var2str($this->idlineafaccli) . ";");
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
                $sql = "UPDATE " . $this->table_name . " SET idfacturacli = " . $this->var2str($this->idfacturacli)
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
                . ", idunidad = " . $this->var2str($this->idunidad)
                . ", factor = " . $this->var2str($this->factor)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idlineafaccli = " . $this->var2str($this->idlineafaccli) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idfacturacli, idarticulo, idimpuesto, codprincipal, descripcion, cantidad, pvpunitario, dto, pvptotal, pvpsindto, valorice, valoriva, idunidad, factor, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idfacturacli)
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
                . "," . $this->var2str($this->idunidad)
                . "," . $this->var2str($this->factor)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->beforeSave($control)) {
                if ($this->db->exec($sql)) {
                    if ($insert) {
                        $this->idlineafaccli = $this->db->lastval();
                    }
                    $this->afterSave();
                    return true;
                }
            }
        }

        return false;
    }

    public function delete()
    {
        if ($this->beforeDelete()) {
            $sql  = "DELETE FROM " . $this->table_name . " WHERE idlineafaccli = " . $this->var2str($this->idlineafaccli) . ";";
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
                $list[] = new \lineasfacturascli($p);
            }
        }

        return $list;
    }

    public function all_by_idfacturacli($idfacturacli)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idfacturacli = " . $this->var2str($idfacturacli) . " ORDER BY idlineafaccli ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \lineasfacturascli($p);
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
        return round($this->pvptotal + $this->valorice + $this->valoriva, 6);
    }

    public function get_url_articulo()
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

    public function get_articulo()
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

    public function beforeSave($control)
    {
        if (!$control) {
            return true;
        }

        if ($fac = $this->get_factura()) {
            if ($fac->tipoid == 'F' && $fac->coddocumento == '01') {
                //valido si es consumidor que la factura no supere el valor Maximo
                $total = round($fac->total + $this->get_total(), 2);
                if (floatval($total) > floatval(JG_MAX_CF)) {
                    $this->new_error_msg("La Factura de Consumidor Final no puede ser superior a: " . JG_MAX_CF);
                    return false;
                }
            } else if ($fac->anulado) {
                $this->new_error_msg("El documento se encuentra anulado no es posible modificar.");
                return false;
            } else if ($fac->estado_sri == 'AUTORIZADO') {
                $this->new_error_msg("El documento se encuentra autorizado no es posible modificar.");
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
        $cobros0 = new \trans_cobros();
        $cobros  = $cobros0->all_by_idfacturacli($this->idfacturacli);
        if (count($cobros) > 1) {
            $this->new_error_msg("Tiene cobros Asociados al Documento, no se puede agregar el detalle.");
            return false;
        }
        return true;
    }

    public function afterSave()
    {
        if ($fac = $this->get_factura()) {
            if ($art = $this->get_articulo()) {
                if ($art->compuesto) {
                    $this->tratar_articulo_compuesto($art, $fac);
                } else {
                    $trans = new \trans_inventario();
                    $tr    = $trans->get_idlineafaccli($this->idlineafaccli);
                    if ($tr) {
                        $tr->delete();
                    }

                    $tran                    = new \trans_inventario();
                    $tran->idempresa         = $fac->idempresa;
                    $tran->idestablecimiento = $fac->idestablecimiento;
                    $tran->idcliente         = $fac->idcliente;
                    $tran->idarticulo        = $this->idarticulo;
                    $tran->idlineafaccli     = $this->idlineafaccli;
                    $tran->origen            = $fac->get_tipodoc() . " de Venta - " . $fac->numero_documento;
                    $tran->url               = $fac->url();
                    $tran->nomestab          = $fac->get_nombreestablecimiento();
                    $tran->fec_trans         = date('Y-m-d H:i:s', strtotime($fac->fec_emision . " " . $fac->hora_emision));
                    $tran->fecha             = $fac->fec_emision;
                    $tran->hora              = $fac->hora_emision;
                    if ($fac->coddocumento == '04') {
                        $tran->movimiento = $this->cantidad * $this->factor;
                        $tran->ingresos   = $this->cantidad * $this->factor;
                    } else {
                        $tran->movimiento = (0 - ($this->cantidad * $this->factor));
                        $tran->egresos    = $this->cantidad * $this->factor;
                    }
                    $tran->costo      = ($this->pvptotal / ($this->cantidad * $this->factor));
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
        }

        return true;
    }

    public function beforeDelete()
    {
        if ($fac = $this->get_factura()) {
            if ($fac->anulado) {
                $this->new_error_msg("El documento se encuentra anulado no es posible eliminar.");
                return false;
            } else if ($fac->estado_sri == 'AUTORIZADO') {
                $this->new_error_msg("El documento se encuentra autorizado no es posible eliminar.");
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
        $cobros0 = new \trans_cobros();
        $cobros  = $cobros0->all_by_idfacturacli($this->idfacturacli);
        if (count($cobros) > 1) {
            $this->new_error_msg("Tiene cobros Asociados al Documento, no se puede eliminar.");
            return false;
        }

        $trans = new \trans_inventario();
        if ($art = $this->get_articulo()) {
            if ($art->compuesto) {
                $paso = true;
                $tr   = $trans->all_idlineafaccli($this->idlineafaccli);
                if ($tr) {
                    foreach ($tr as $key => $t) {
                        if (!$t->delete()) {
                            return false;
                        }
                    }
                }
            } else {
                $tr = $trans->get_idlineafaccli($this->idlineafaccli);
                if ($tr) {
                    return $tr->delete();
                }
            }
        }

        return true;
    }

    public function get_um()
    {
        if ($this->idunidad) {
            $unidad = new unidades_medida();
            $un     = $unidad->get($this->idunidad);
            if ($un) {
                return $un->nombre;
            }
        }
        return '-';
    }

    public function get_by_idempresa($idempresa)
    {
        $lista = array();

        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idfacturacli IN (SELECT idfacturacli FROM facturascli WHERE idempresa = " . $this->var2str($idempresa) . ")";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $lista[] = new \lineasfacturascli($p);
            }
        }

        return $lista;
    }

    public function listado_ventas_formulario($idempresa = false, $fec_desde = false, $fec_hasta = false)
    {
        $lista = array();

        $sql = "SELECT f.coddocumento, i.codigo, ar.tipo, i.porcentaje, SUM ( l.pvptotal ) AS base FROM lineasfacturascli l INNER JOIN facturascli f ON f.idfacturacli = l.idfacturacli INNER JOIN articulos ar ON ar.idarticulo = l.idarticulo INNER JOIN impuestos i ON i.idimpuesto = l.idimpuesto WHERE f.anulado != " . $this->var2str(true) . " AND f.coddocumento != " . $this->var2str('02');

        if ($fec_desde) {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND f.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idempresa) {
            $sql .= " AND f.idempresa = " . $this->var2str($idempresa);
        }

        $sql .= " GROUP BY f.coddocumento, i.codigo, ar.tipo, i.porcentaje";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $lista;
    }

    private function tratar_articulo_compuesto($art, $fac)
    {
        $trans = new \trans_inventario();
        $tr    = $trans->all_idlineafaccli($this->idlineafaccli);
        if ($tr) {
            foreach ($tr as $key => $t) {
                $t->delete();
            }
        }

        $ins0    = new insumos_art();
        $insumos = $ins0->all_by_idarticulocomp($this->idarticulo, $art->idempresa);
        foreach ($insumos as $key => $ins) {
            $tran                    = new \trans_inventario();
            $tran->idempresa         = $fac->idempresa;
            $tran->idestablecimiento = $fac->idestablecimiento;
            $tran->idcliente         = $fac->idcliente;
            $tran->idarticulo        = $ins->idarticulo;
            $tran->idlineafaccli     = $this->idlineafaccli;
            $tran->origen            = $fac->get_tipodoc() . " de Venta - " . $fac->numero_documento;
            $tran->url               = $fac->url();
            $tran->nomestab          = $fac->get_nombreestablecimiento();
            $tran->fec_trans         = date('Y-m-d H:i:s', strtotime($fac->fec_emision . " " . $fac->hora_emision));
            $tran->fecha             = $fac->fec_emision;
            $tran->hora              = $fac->hora_emision;
            if ($fac->coddocumento == '04') {
                $tran->movimiento = $this->cantidad * $this->factor * $ins->cantidad;
                $tran->ingresos   = $this->cantidad * $this->factor * $ins->cantidad;
            } else {
                $tran->movimiento = (0 - ($this->cantidad * $this->factor * $ins->cantidad));
                $tran->egresos    = $this->cantidad * $this->factor * $ins->cantidad;
            }
            $tran->costo      = ($this->pvptotal / ($this->cantidad * $this->factor * $ins->cantidad));
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

    public function reporteAgrupadoProductos($idempresa, $idarticulo = '', $fec_desde = '', $fec_hasta = '', $idestablecimiento = '', $idgrupo = '', $idmarca = '')
    {
        $list = array();

        $sql = "SELECT es.idestablecimiento, es.nombre AS nomestab, lf.idarticulo, ar.codprincipal, ar.nombre, gs.idgrupo AS idpadre, COALESCE(gs.nombre, 'SIN GRUPO') AS nompadre, gr.idgrupo AS idhijo, COALESCE(gr.nombre, 'SIN SUBGRUPO') AS nomhijo, ROUND( SUM ( lf.cantidad ), 6 ) AS cantidad, ROUND( ( SUM ( lf.pvptotal + lf.valoriva ) / SUM ( lf.cantidad ) ), 6 ) AS pvpunitario, ROUND( ( SUM ( lf.pvptotal + lf.valoriva ) ), 2 ) AS total
                FROM lineasfacturascli lf
                    INNER JOIN facturascli fc ON fc.idfacturacli = lf.idfacturacli
                    INNER JOIN establecimiento es ON es.idestablecimiento = fc.idestablecimiento
                    INNER JOIN articulos ar ON ar.idarticulo = lf.idarticulo
                    LEFT JOIN grupos gr ON gr.idgrupo = ar.idgrupo
                    LEFT JOIN grupos gs ON gs.idgrupo = gr.idpadre WHERE fc.anulado != " . $this->var2str(true) . " AND fc.idempresa = " . $this->var2str($idempresa);
        if ($idarticulo != '') {
            $sql .= " AND ar.idarticulo = " . $this->var2str($idarticulo);
        }
        if ($idestablecimiento != '') {
            $sql .= " AND fc.idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($fec_desde != '') {
            $sql .= " AND fc.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta != '') {
            $sql .= " AND fc.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idgrupo != '') {
            $sql .= " AND ar.idgrupo = " . $this->var2str($idgrupo);
        }
        if ($idmarca != '') {
            $sql .= " AND ar.idmarca = " . $this->var2str($idmarca);
        }
        $sql .= " GROUP BY
                    es.idestablecimiento, es.nombre, lf.idarticulo, ar.codprincipal, ar.nombre, gs.idgrupo, gs.nombre, gr.idgrupo, gr.nombre
                ORDER BY
                    es.nombre, COALESCE(gs.nombre, 'SIN GRUPO'), COALESCE(gr.nombre, 'SIN SUBGRUPO'), ar.nombre ASC";

        $data = $this->db->select($sql);
        if ($data) {
            $list = $data;
        }

        return $list;
    }
}
