<?php

namespace GSC_Systems\model;

class lineasbandejasri extends \model
{
    public function __construct($data = false)
    {
        parent::__construct('lineasbandejasri');
        if ($data) {

            $this->idlineabandejasri = $data['idlineabandejasri'];
            $this->idbandejasri      = $data['idbandejasri'];
            $this->idarticulo        = $data['idarticulo'];
            $this->idimpuesto        = $data['idimpuesto'];
            $this->codprincipal      = $data['codprincipal'];
            $this->codauxiliar       = $data['codauxiliar'];
            $this->descripcion       = $data['descripcion'];
            $this->cantidad          = floatval($data['cantidad']);
            $this->pvpunitario       = floatval($data['pvpunitario']);
            $this->dto               = floatval($data['dto']);
            $this->pvptotal          = floatval($data['pvptotal']);
            $this->pvpsindto         = floatval($data['pvpsindto']);
            $this->valorice          = floatval($data['valorice']);
            $this->valoriva          = floatval($data['valoriva']);
            $this->valorirbp         = floatval($data['valorirbp']);
            //Almaceno identificador de retenciones
            $this->idretencion_renta = $data['idretencion_renta'];
            $this->idretencion_iva   = $data['idretencion_iva'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idlineabandejasri = null;
            $this->idbandejasri      = null;
            $this->idarticulo        = null;
            $this->idimpuesto        = null;
            $this->codprincipal      = null;
            $this->codauxiliar       = null;
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
        }
    }

    public function install()
    {
        new \cab_bandejasri();
        new \impuestos();
        new \articulos();
        new \tiposretenciones();
        
        return "";
    }

    public function url()
    {
        return '';
    }

    public function get($idlineabandejasri)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineabandejasri = " . $this->var2str($idlineabandejasri) . ";");
        if ($data) {
            return new \lineasbandejasri($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idlineabandejasri)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineabandejasri = " . $this->var2str($this->idlineabandejasri) . ";");
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
                $sql = "UPDATE " . $this->table_name . " SET idbandejasri = " . $this->var2str($this->idbandejasri)
                . ", idarticulo = " . $this->var2str($this->idarticulo)
                . ", idimpuesto = " . $this->var2str($this->idimpuesto)
                . ", codprincipal = " . $this->var2str($this->codprincipal)
                . ", codauxiliar = " . $this->var2str($this->codauxiliar)
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
                . "  WHERE idlineabandejasri = " . $this->var2str($this->idlineabandejasri) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idbandejasri, idarticulo, idimpuesto, codprincipal, codauxiliar, descripcion, cantidad, pvpunitario, dto, pvptotal, pvpsindto, valorice, valoriva, valorirbp, idretencion_renta, idretencion_iva, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idbandejasri)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->idimpuesto)
                . "," . $this->var2str($this->codprincipal)
                . "," . $this->var2str($this->codauxiliar)
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
            if ($this->beforeSave()) {
                if ($this->db->exec($sql)) {
                    if ($insert) {
                        $this->idlineabandejasri = $this->db->lastval();
                        $this->afterSave();
                    }
                    return true;
                }
            }
        }

        return false;
    }

    public function beforeSave()
    {
        return true;
    }

    public function delete()
    {
        if ($this->beforeDelete()) {
            $sql  = "DELETE FROM " . $this->table_name . " WHERE idlineabandejasri = " . $this->var2str($this->idlineabandejasri) . ";";
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
                $list[] = new \lineasbandejasri($p);
            }
        }

        return $list;
    }

    public function all_by_idbandejasri($idbandejasri)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idbandejasri = " . $this->var2str($idbandejasri) . " ORDER BY idlineabandejasri ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \lineasbandejasri($p);
            }
        }

        return $list;
    }

    public function all_by_idbandejasri_sh($idbandejasri, $agret = false)
    {
        $list = array();
        $ag = '';
        if ($agret) {
            $ag = ' OR idretencion_renta IS NULL OR idretencion_iva IS NULL';
        }

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE (idarticulo IS NULL ".$ag.") AND idbandejasri = " . $this->var2str($idbandejasri) . " ORDER BY idlineabandejasri ASC;");
        if ($data) {
            return true;
        }

        return false;
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
        return true;
    }

    private function beforeDelete()
    {
        return true;
    }

    public function getHomologacionArt($idempresa, $idproveedor, $codigo)
    {
        $sql = "SELECT * FROM ".$this->table_name." WHERE idbandejasri IN (SELECT idbandejasri FROM cab_bandejasri WHERE idempresa = ".$this->var2str($idempresa)." AND idproveedor = ".$this->var2str($idproveedor).")  AND codprincipal = ".$this->var2str($codigo)." AND idarticulo IS NOT NULL ORDER BY idlineabandejasri DESC";
        $data = $this->db->select_limit($sql, 1, 0);
        if ($data) {
            return new \lineasbandejasri($data[0]);
        }
        return false;
    }

    public function homologar_items($idempresa, $identificacion, $linea)
    {
        $sql = "UPDATE ".$this->table_name." SET idarticulo = ".$this->var2str($linea->idarticulo).", idretencion_iva = ".$this->var2str($linea->idretencion_iva).", idretencion_renta = ".$this->var2str($linea->idretencion_renta)." WHERE codprincipal = ".$this->var2str($linea->codprincipal)." AND idarticulo IS NULL AND idbandejasri IN (SELECT idbandejasri FROM cab_bandejasri WHERE idempresa = ".$this->var2str($idempresa)." AND identificacion = ".$this->var2str($identificacion).")";
        return $this->db->exec($sql);
    }

    public function homologar_masivo($idempresa, $identificacion, $idbandejasri, $idarticulo, $idretencion_iva, $idretencion_renta)
    {
        $sql = "UPDATE ".$this->table_name." SET idarticulo = ".$this->var2str($idarticulo).", idretencion_iva = ".$this->var2str($idretencion_iva).", idretencion_renta = ".$this->var2str($idretencion_renta)." WHERE codprincipal IN (SELECT codprincipal FROM lineasbandejasri WHERE idbandejasri = ".$this->var2str($idbandejasri)." AND idarticulo IS NULL) AND idarticulo IS NULL AND idbandejasri IN (SELECT idbandejasri FROM cab_bandejasri WHERE idempresa = ".$this->var2str($idempresa)." AND identificacion = ".$this->var2str($identificacion).")";
        return $this->db->exec($sql);
    }
}
