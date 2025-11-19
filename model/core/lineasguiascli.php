<?php

namespace GSC_Systems\model;

class lineasguiascli extends \model
{
    public $url_art;

    public function __construct($data = false)
    {
        parent::__construct('lineasguiascli');
        if ($data) {

            $this->idlineaguiacli = $data['idlineaguiacli'];
            $this->idguiacli      = $data['idguiacli'];
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
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

            $this->url_art = $this->get_url_articulo();

        } else {
            $this->idlineaguiacli = null;
            $this->idguiacli      = null;
            $this->idarticulo     = null;
            $this->idimpuesto     = null;
            $this->codprincipal   = null;
            $this->descripcion    = null;
            $this->cantidad       = 0;
            $this->pvpunitario    = 0;
            $this->dto            = 0;
            $this->pvptotal       = 0;
            $this->pvpsindto      = 0;
            $this->valorice       = 0;
            $this->valoriva       = 0;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;

            $this->url_art = '';
        }
    }

    public function install()
    {
        new \guiascli();
        new \impuestos();
        new \articulos();
        
        return "";
    }

    public function url()
    {
        return '';
    }

    public function get($idlineaguiacli)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineaguiacli = " . $this->var2str($idlineaguiacli) . ";");
        if ($data) {
            return new \lineasguiascli($data[0]);
        }

        return false;
    }

    public function get_guia()
    {
        $guia0 = new \guiascli();
        $fac   = $guia0->get($this->idguiacli);
        if ($fac) {
            return $fac;
        }
        return false;
    }

    public function exists()
    {
        if (is_null($this->idlineaguiacli)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineaguiacli = " . $this->var2str($this->idlineaguiacli) . ";");
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
                $sql = "UPDATE " . $this->table_name . " SET idguiacli = " . $this->var2str($this->idguiacli)
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
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idlineaguiacli = " . $this->var2str($this->idlineaguiacli) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idguiacli, idarticulo, idimpuesto, codprincipal, descripcion, cantidad, pvpunitario, dto, pvptotal, pvpsindto, valorice, valoriva, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idguiacli)
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
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->beforeSave()) {
                if ($this->db->exec($sql)) {
                    if ($insert) {
                        $this->idlineaguiacli = $this->db->lastval();
                        $this->afterSave();
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function delete()
    {
        if ($this->beforeDelete()) {
            $sql  = "DELETE FROM " . $this->table_name . " WHERE idlineaguiacli = " . $this->var2str($this->idlineaguiacli) . ";";
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
                $list[] = new \lineasguiascli($p);
            }
        }

        return $list;
    }

    public function all_by_idguiacli($idguiacli)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idguiacli = " . $this->var2str($idguiacli) . " ORDER BY idlineaguiacli ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \lineasguiascli($p);
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

    public function beforeSave()
    {
        if ($guia = $this->get_guia()) {
            if ($guia->anulado) {
                $this->new_error_msg("El documento se encuentra anulado no es posible modificar.");
                return false;
            } else if ($guia->estado_sri == 'AUTORIZADO') {
                $this->new_error_msg("El documento se encuentra autorizado no es posible eliminar.");
                return false;
            }

            if (complemento_exists('contabilidad')) {
                if ($guia->idejercicio) {
                    $ejer0     = new \ejercicios();
                    $ejercicio = $ejer0->get($guia->idejercicio);
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

    public function afterSave()
    {

        return true;
    }

    public function beforeDelete()
    {
        if ($guia = $this->get_guia()) {
            if ($guia->anulado) {
                $this->new_error_msg("El documento se encuentra anulado no es posible eliminar.");
                return false;
            } else if ($guia->estado_sri == 'AUTORIZADO') {
                $this->new_error_msg("El documento se encuentra autorizado no es posible eliminar.");
                return false;
            }

            if (complemento_exists('contabilidad')) {
                if ($guia->idejercicio) {
                    $ejer0     = new \ejercicios();
                    $ejercicio = $ejer0->get($guia->idejercicio);
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
}
