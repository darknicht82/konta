<?php

namespace GSC_Systems\model;

class articulos extends \model
{
    public $stock_fisico;

    public function __construct($data = false, $establecimiento = '')
    {
        parent::__construct('articulos');

        $this->establecimiento = $establecimiento;

        if ($data) {

            $this->idarticulo      = $data['idarticulo'];
            $this->idempresa       = $data['idempresa'];
            $this->codprincipal    = $data['codprincipal'];
            $this->codauxiliar     = $data['codauxiliar'];
            $this->codbarras       = $data['codbarras'];
            $this->nombre          = $data['nombre'];
            $this->detalle         = $data['detalle'];
            $this->idimpuesto      = $data['idimpuesto'];
            $this->precio          = $data['precio'];
            $this->dto             = $data['dto'];
            $this->idmarca         = $data['idmarca'];
            $this->idgrupo         = $data['idgrupo'];
            $this->ultcosto        = $data['ultcosto'];
            $this->costomedio      = $data['costomedio'];
            $this->stock           = $data['stock'];
            $this->tipo            = $data['tipo'];
            $this->controlar_stock = $data['controlar_stock'];
            $this->idunidad        = $data['idunidad'];
            $this->sevende         = $this->str2bool($data['sevende']);
            $this->secompra        = $this->str2bool($data['secompra']);
            $this->bloqueado       = $this->str2bool($data['bloqueado']);
            $this->compuesto       = $this->str2bool($data['compuesto']);
            $this->imagen          = $data['imagen'];
            $this->gencuadre          = $this->str2bool($data['gencuadre']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

            //Variable para manejo de stock
            $this->stock_fisico = $this->getstock();
            $this->medida       = $this->get_um();
            $this->url_imagen   = $this->imagen();
            $this->pvppublico   = $this->get_precio_iva();

        } else {
            $this->idarticulo      = null;
            $this->idempresa       = null;
            $this->codprincipal    = null;
            $this->codauxiliar     = null;
            $this->codbarras       = null;
            $this->nombre          = null;
            $this->detalle         = null;
            $this->idimpuesto      = null;
            $this->precio          = 0;
            $this->dto             = 0;
            $this->idmarca         = null;
            $this->idgrupo         = null;
            $this->ultcosto        = 0;
            $this->costomedio      = 0;
            $this->stock           = 0;
            $this->tipo            = 0;
            $this->controlar_stock = 1;
            $this->idunidad        = null;
            $this->sevende         = true;
            $this->secompra        = true;
            $this->bloqueado       = false;
            $this->compuesto       = false;
            $this->imagen          = false;
            $this->gencuadre          = false;
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
        return "";
    }

    public function url()
    {
        if (is_null($this->idarticulo)) {
            if ($this->tipo == 1) {
                return 'index.php?page=lista_articulos';
            } else {
                return 'index.php?page=lista_servicios';
            }
        }
        if ($this->tipo == 1) {
            return 'index.php?page=ver_articulo&id=' . $this->idarticulo;
        } else {
            return 'index.php?page=ver_servicio&id=' . $this->idarticulo;
        }
    }

    public function get($idarticulo)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idarticulo = " . $this->var2str($idarticulo) . ";");
        if ($data) {
            return new \articulos($data[0], $this->establecimiento);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idarticulo)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idarticulo = " . $this->var2str($this->idarticulo) . ";");
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
                . ", codprincipal = " . $this->var2str($this->codprincipal)
                . ", codauxiliar = " . $this->var2str($this->codauxiliar)
                . ", codbarras = " . $this->var2str($this->codbarras)
                . ", nombre = " . $this->var2str($this->nombre)
                . ", detalle = " . $this->var2str($this->detalle)
                . ", idimpuesto = " . $this->var2str($this->idimpuesto)
                . ", precio = " . $this->var2str($this->precio)
                . ", dto = " . $this->var2str($this->dto)
                . ", idmarca = " . $this->var2str($this->idmarca)
                . ", idgrupo = " . $this->var2str($this->idgrupo)
                . ", ultcosto = " . $this->var2str($this->ultcosto)
                . ", costomedio = " . $this->var2str($this->costomedio)
                . ", stock = " . $this->var2str($this->stock)
                . ", tipo = " . $this->var2str($this->tipo)
                . ", controlar_stock = " . $this->var2str($this->controlar_stock)
                . ", idunidad = " . $this->var2str($this->idunidad)
                . ", sevende = " . $this->var2str($this->sevende)
                . ", secompra = " . $this->var2str($this->secompra)
                . ", bloqueado = " . $this->var2str($this->bloqueado)
                . ", compuesto = " . $this->var2str($this->compuesto)
                . ", imagen = " . $this->var2str($this->imagen)
                . ", gencuadre = " . $this->var2str($this->gencuadre)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idarticulo = " . $this->var2str($this->idarticulo) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, codprincipal, codauxiliar, codbarras, nombre, detalle, idimpuesto, precio, dto, idmarca, idgrupo, ultcosto, costomedio, stock, tipo, controlar_stock, idunidad, sevende, secompra, bloqueado, compuesto, imagen, gencuadre, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->codprincipal)
                . "," . $this->var2str($this->codauxiliar)
                . "," . $this->var2str($this->codbarras)
                . "," . $this->var2str($this->nombre)
                . "," . $this->var2str($this->detalle)
                . "," . $this->var2str($this->idimpuesto)
                . "," . $this->var2str($this->precio)
                . "," . $this->var2str($this->dto)
                . "," . $this->var2str($this->idmarca)
                . "," . $this->var2str($this->idgrupo)
                . "," . $this->var2str($this->ultcosto)
                . "," . $this->var2str($this->costomedio)
                . "," . $this->var2str($this->stock)
                . "," . $this->var2str($this->tipo)
                . "," . $this->var2str($this->controlar_stock)
                . "," . $this->var2str($this->idunidad)
                . "," . $this->var2str($this->sevende)
                . "," . $this->var2str($this->secompra)
                . "," . $this->var2str($this->bloqueado)
                . "," . $this->var2str($this->compuesto)
                . "," . $this->var2str($this->imagen)
                . "," . $this->var2str($this->gencuadre)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idarticulo = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idarticulo = " . $this->var2str($this->idarticulo) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY codbarras DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \articulos($p, $this->establecimiento);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY codbarras DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \articulos($p, $this->establecimiento);
            }
        }

        return $list;
    }

    public function search_articulos($idempresa = '', $query = '', $idgrupo = '', $idmarca = '', $tipo = '', $idimpuesto = '', $sevende = '', $secompra = '', $bloqueado = '', $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        if ($idempresa != '') {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }
        if ($tipo != '') {
            $sql .= " AND tipo = " . $this->var2str($tipo);
        }
        if ($idgrupo != '') {
            $sql .= " AND idgrupo = " . $this->var2str($idgrupo);
        }
        if ($idmarca != '') {
            $sql .= " AND idmarca = " . $this->var2str($idmarca);
        }
        if ($idimpuesto != '') {
            $sql .= " AND idimpuesto = " . $this->var2str($idimpuesto);
        }
        if ($query != '') {
            $query = strtolower($query);
            $qry   = explode(" ", $query);
            $sql .= " AND (";
            $or = "";
            foreach ($qry as $key => $q) {
                $sql .= $or . "lower(codprincipal) LIKE '%" . $q . "%' OR lower(codauxiliar) LIKE '%" . $q . "%' OR lower(nombre) LIKE '%" . $q . "%' OR lower(codbarras) LIKE '%" . $q . "%'";
                $or = " OR ";
            }
            $sql .= ")";
        }
        if ($sevende != '') {
            $sql .= " AND sevende = " . $this->var2str(true);
        }
        if ($secompra != '') {
            $sql .= " AND secompra = " . $this->var2str(true);
        }
        if ($bloqueado != '') {
            $sql .= " AND bloqueado = " . $this->var2str(false);
        }
        if ($offset >= 0) {
            $sql .= " ORDER BY nombre ASC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }
        if ($data) {
            if ($offset < 0) {
                return count($data);
            } else {
                foreach ($data as $p) {
                    $list[] = new \articulos($p, $this->establecimiento);
                }
            }
        }

        return $list;
    }

    public function get_by_codprincipal($idempresa, $codprincipal)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND codprincipal = " . $this->var2str($codprincipal) . ";");
        if ($data) {
            return new \articulos($data[0], $this->establecimiento);
        }

        return false;
    }

    public function get_by_codbarras($idempresa, $codbarras)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND codbarras = " . $this->var2str($codbarras) . ";");
        if ($data) {
            return new \articulos($data[0], $this->establecimiento);
        }

        return false;
    }

    public function get_nombre_marca()
    {
        $marcas = new \marcas();

        $marca = $marcas->get($this->idmarca);
        if ($marca) {
            return $marca->nombre;
        }
        return '-';
    }

    public function get_nombre_grupo()
    {
        $grupos = new \grupos();

        $grupo = $grupos->get($this->idgrupo);
        if ($grupo) {
            return $grupo->nombre;
        }
        return '-';
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

    public function get_precio_iva()
    {
        return show_numero($this->precio * (1 + ($this->get_porcentaje_impuesto() / 100)));
    }

    public function get_precio()
    {
        return show_numero($this->precio);
    }

    public function getstock()
    {
        $stock  = 0;
        $stock0 = new \stocks();
        if ($this->compuesto) {
            $ins0    = new insumos_art();
            $insumos = $ins0->all_by_idarticulocomp($this->idarticulo, $this->idempresa);
            $spos    = '';
            foreach ($insumos as $key => $insumo) {
                $cant   = 0;
                $stocks = $stock0->all_by_idarticulo($insumo->idarticulo);
                if ($stocks) {
                    foreach ($stocks as $key2 => $s) {
                        if ($this->establecimiento != '') {
                            if ($this->establecimiento == $s->idestablecimiento) {
                                $cant += ($s->stock / $insumo->cantidad);
                            }
                        } else {
                            $cant += ($s->stock / $insumo->cantidad);
                        }
                    }
                    if ($spos == '') {
                        $spos = $cant;
                    } else {
                        if ($cant < $spos) {
                            $spos = $cant;
                        }
                    }
                }
            }
            if ($spos != '') {
                $stock = round($spos, 6);
            }
        } else {
            $stocks = $stock0->all_by_idarticulo($this->idarticulo);
            if ($stocks) {
                foreach ($stocks as $key => $s) {
                    if ($this->establecimiento != '') {
                        if ($this->establecimiento == $s->idestablecimiento) {
                            return $s->stock;
                        }
                    } else {
                        $stock += $s->stock;
                    }
                }
            }
        }
        return $stock;
    }

    public function get_um()
    {
        if ($this->idunidad && complemento_exists('unidadesmedida')) {
            $unidad = new unidades_medida();
            $un     = $unidad->get($this->idunidad);
            if ($un) {
                return $un->nombre;
            }
        }
        return '-';
    }

    public function getCostoPromedio()
    {
        $trans0 = new \trans_inventario();
        return show_numero($trans0->getCostoPromedio($this->idempresa, $this->idarticulo, '', '2', $this->compuesto));
    }

    public function getUltimoCosto()
    {
        $trans0 = new \trans_inventario();
        return show_numero($trans0->getUltimoCosto($this->idempresa, $this->idarticulo, '', $this->compuesto));
    }

    public function getCostoArt()
    {
        $trans0 = new \trans_inventario();
        return show_numero($trans0->getCostoArticulo($this->idempresa, $this->idarticulo, '', '', '2', $this->compuesto));
    }

    public function getItemsVentaPorGrupo($idempresa, $idgrupo, $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idgrupo = " . $this->var2str($idgrupo) . " AND sevende = " . $this->var2str(true) . " AND bloqueado != " . $this->var2str(true) . " ORDER BY nombre ASC";
        $data = $this->db->select_limit($sql, $limit, $offset);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \articulos($p, $this->establecimiento);
            }
        }

        return $list;
    }

    private function imagen()
    {
        if ($this->imagen) {
            return $this->imagen;
        }

        return 'view/img/sinimgart.png';
    }

    public function getArticulosCuadre($idempresa)
    {
        $list = array();
        $sql = "SELECT idarticulo, nombre, codprincipal FROM ".$this->table_name." WHERE idempresa = ".$this->var2str($idempresa)." AND gencuadre = ".$this->var2str(true)." ORDER BY nombre ASC";

        $data = $this->db->select($sql);
        if ($data) {
            $list = $data;
        }

        return $list;
    }
}
