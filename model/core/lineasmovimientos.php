<?php

namespace GSC_Systems\model;

class lineasmovimientos extends \model
{
    public $url_art;
    public $cant_ant;
    public function __construct($data = false)
    {
        parent::__construct('lineasmovimientos');
        if ($data) {

            $this->idlineamovimiento = $data['idlineamovimiento'];
            $this->idmovimiento      = $data['idmovimiento'];
            $this->idarticulo        = $data['idarticulo'];
            $this->codprincipal      = $data['codprincipal'];
            $this->descripcion       = $data['descripcion'];
            $this->cantidad          = floatval($data['cantidad']);
            $this->costo             = floatval($data['costo']);
            $this->costototal        = floatval($data['costototal']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

            $this->url_art  = $this->get_url_articulo();
            $this->cant_ant = floatval($data['cantidad']);

        } else {
            $this->idlineamovimiento = null;
            $this->idmovimiento      = null;
            $this->idarticulo        = null;
            $this->codprincipal      = null;
            $this->descripcion       = null;
            $this->cantidad          = 0;
            $this->costo             = 0;
            $this->costototal        = 0;
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
        new \movimientos();
        new \articulos();
        
        return "";
    }

    public function url()
    {
        return '';
    }

    public function get($idlineamovimiento)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineamovimiento = " . $this->var2str($idlineamovimiento) . ";");
        if ($data) {
            return new \lineasmovimientos($data[0]);
        }

        return false;
    }

    public function get_movimiento()
    {
        $mov0 = new \movimientos();
        $mov  = $mov0->get($this->idmovimiento);
        if ($mov) {
            return $mov;
        }
        return false;
    }

    public function exists()
    {
        if (is_null($this->idlineamovimiento)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlineamovimiento = " . $this->var2str($this->idlineamovimiento) . ";");
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
                $sql = "UPDATE " . $this->table_name . " SET idmovimiento = " . $this->var2str($this->idmovimiento)
                . ", idarticulo = " . $this->var2str($this->idarticulo)
                . ", codprincipal = " . $this->var2str($this->codprincipal)
                . ", descripcion = " . $this->var2str($this->descripcion)
                . ", cantidad = " . $this->var2str($this->cantidad)
                . ", costo = " . $this->var2str($this->costo)
                . ", costototal = " . $this->var2str($this->costototal)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idlineamovimiento = " . $this->var2str($this->idlineamovimiento) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idmovimiento, idarticulo, codprincipal, descripcion, cantidad, costo, costototal, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idmovimiento)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->codprincipal)
                . "," . $this->var2str($this->descripcion)
                . "," . $this->var2str($this->cantidad)
                . "," . $this->var2str($this->costo)
                . "," . $this->var2str($this->costototal)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->beforeSave()) {
                if ($this->db->exec($sql)) {
                    if ($insert) {
                        $this->idlineamovimiento = $this->db->lastval();
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
            $sql  = "DELETE FROM " . $this->table_name . " WHERE idlineamovimiento = " . $this->var2str($this->idlineamovimiento) . ";";
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
                $list[] = new \lineasmovimientos($p);
            }
        }

        return $list;
    }

    public function all_by_idmovimiento($idmovimiento)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idmovimiento = " . $this->var2str($idmovimiento) . " ORDER BY idlineamovimiento ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \lineasmovimientos($p);
            }
        }

        return $list;
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
        if (complemento_exists('contabilidad')) {
            if ($mov = $this->get_movimiento()) {
                if ($mov->idejercicio) {
                    $ejer0     = new \ejercicios();
                    $ejercicio = $ejer0->get($mov->idejercicio);
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
        if ($mov = $this->get_movimiento()) {
            if ($art = $this->get_articulo()) {
                $trans = new \trans_inventario();
                $tr    = $trans->get_idlineamovimiento($this->idlineamovimiento);
                if ($tr) {
                    $tr->delete();
                }

                $tran                    = new \trans_inventario();
                $tran->idempresa         = $mov->idempresa;
                $tran->idestablecimiento = $mov->idestablecimiento;
                $tran->idarticulo        = $this->idarticulo;
                $tran->idlineamovimiento = $this->idlineamovimiento;
                $tran->origen            = $mov->get_tipodoc() . " - " . $mov->numero;
                $tran->url               = $mov->url();
                $tran->nomestab          = $mov->get_nombreestablecimiento();
                $tran->fec_trans         = date('Y-m-d H:i:s', strtotime($mov->fec_emision . " " . $mov->hora_emision));
                $tran->fecha             = $mov->fec_emision;
                $tran->hora              = $mov->hora_emision;
                if ($mov->tipo == 'ingr') {
                    $tran->movimiento = $this->cantidad;
                    $tran->ingresos   = $this->cantidad;
                } else {
                    $tran->movimiento = (0 - $this->cantidad);
                    $tran->egresos    = $this->cantidad;
                }
                $tran->costo      = ($this->costototal / $this->cantidad);
                $tran->costototal = $this->costototal;
                if ($art->tipo == 1) {
                    $tran->aplica_stock = true;
                } else {
                    $tran->aplica_stock = false;
                }

                $tran->fec_creacion  = $this->fec_creacion;
                $tran->nick_creacion = $this->nick_creacion;

                $tran->save();
            }
        }

        return true;
    }

    public function beforeDelete()
    {
        if (complemento_exists('contabilidad')) {
            if ($mov = $this->get_movimiento()) {
                if ($mov->idejercicio) {
                    $ejer0     = new \ejercicios();
                    $ejercicio = $ejer0->get($mov->idejercicio);
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
        
        $trans = new \trans_inventario();
        $tr    = $trans->get_idlineamovimiento($this->idlineamovimiento);
        if ($tr) {
            return $tr->delete();
        }
        
        return true;
    }

    public function get_by_idempresa($idempresa)
    {
        $lista = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE idmovimiento IN (SELECT idmovimiento FROM movimientos WHERE idempresa = ".$this->var2str($idempresa).")";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $lista[] = new \lineasmovimientos($p);
            }
        }

        return $lista;
    }
}
