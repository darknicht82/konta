<?php

namespace GSC_Systems\model;

class lineasregularizaciones extends \model
{
    public $url_art;
    public $cant_ant;
    public function __construct($data = false)
    {
        parent::__construct('lineasregularizaciones');
        if ($data) {

            $this->idlinearegularizacion = $data['idlinearegularizacion'];
            $this->idregularizacion      = $data['idregularizacion'];
            $this->idarticulo            = $data['idarticulo'];
            $this->codprincipal          = $data['codprincipal'];
            $this->descripcion           = $data['descripcion'];
            $this->cantidad              = floatval($data['cantidad']);
            $this->nueva_cantidad        = floatval($data['nueva_cantidad']);
            $this->costo                 = floatval($data['costo']);
            $this->costototal            = floatval($data['costototal']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

            $this->url_art  = $this->get_url_articulo();
            $this->cant_ant = floatval($data['cantidad']);

        } else {
            $this->idlinearegularizacion = null;
            $this->idregularizacion      = null;
            $this->idarticulo            = null;
            $this->codprincipal          = null;
            $this->descripcion           = null;
            $this->cantidad              = 0;
            $this->nueva_cantidad        = 0;
            $this->costo                 = 0;
            $this->costototal            = 0;
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
        new \regularizaciones();
        new \articulos();

        return "";
    }

    public function url()
    {
        return '';
    }

    public function get($idlinearegularizacion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlinearegularizacion = " . $this->var2str($idlinearegularizacion) . ";");
        if ($data) {
            return new \lineasregularizaciones($data[0]);
        }

        return false;
    }

    public function get_regularizacion()
    {
        $mov0 = new \regularizaciones();
        $mov  = $mov0->get($this->idregularizacion);
        if ($mov) {
            return $mov;
        }
        return false;
    }

    public function exists()
    {
        if (is_null($this->idlinearegularizacion)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idlinearegularizacion = " . $this->var2str($this->idlinearegularizacion) . ";");
    }

    public function test()
    {
        $status = true;

        $movstock         = $this->nueva_cantidad - $this->cantidad;
        $this->costototal = $movstock * $this->costo;

        return $status;
    }

    public function save()
    {
        if ($this->test()) {
            $insert = false;
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET idregularizacion = " . $this->var2str($this->idregularizacion)
                . ", idarticulo = " . $this->var2str($this->idarticulo)
                . ", codprincipal = " . $this->var2str($this->codprincipal)
                . ", descripcion = " . $this->var2str($this->descripcion)
                . ", cantidad = " . $this->var2str($this->cantidad)
                . ", nueva_cantidad = " . $this->var2str($this->nueva_cantidad)
                . ", costo = " . $this->var2str($this->costo)
                . ", costototal = " . $this->var2str($this->costototal)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idlinearegularizacion = " . $this->var2str($this->idlinearegularizacion) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idregularizacion, idarticulo, codprincipal, descripcion, cantidad, nueva_cantidad, costo, costototal, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idregularizacion)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->codprincipal)
                . "," . $this->var2str($this->descripcion)
                . "," . $this->var2str($this->cantidad)
                . "," . $this->var2str($this->nueva_cantidad)
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
                        $this->idlinearegularizacion = $this->db->lastval();
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
            $sql  = "DELETE FROM " . $this->table_name . " WHERE idlinearegularizacion = " . $this->var2str($this->idlinearegularizacion) . ";";
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
                $list[] = new \lineasregularizaciones($p);
            }
        }

        return $list;
    }

    public function all_by_idregularizacion($idregularizacion)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idregularizacion = " . $this->var2str($idregularizacion) . " ORDER BY idlinearegularizacion ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \lineasregularizaciones($p);
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
            if ($reg = $this->get_regularizacion()) {
                if ($reg->idejercicio) {
                    $ejer0     = new \ejercicios();
                    $ejercicio = $ejer0->get($reg->idejercicio);
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
        if ($reg = $this->get_regularizacion()) {
            if ($art = $this->get_articulo()) {
                $trans = new \trans_inventario();
                $tr    = $trans->get_idlinearegularizacion($this->idlinearegularizacion);
                if ($tr) {
                    $tr->delete();
                }

                $tran                        = new \trans_inventario();
                $tran->idempresa             = $reg->idempresa;
                $tran->idestablecimiento     = $reg->idestablecimiento;
                $tran->idarticulo            = $this->idarticulo;
                $tran->idlinearegularizacion = $this->idlinearegularizacion;
                $tran->origen                = $reg->get_tipodoc() . " - " . $reg->numero;
                $tran->url                   = $reg->url();
                $tran->nomestab              = $reg->get_nombreestablecimiento();
                $tran->fec_trans             = date('Y-m-d H:i:s', strtotime($reg->fec_emision . " " . $reg->hora_emision));
                $tran->fecha                 = $reg->fec_emision;
                $tran->hora                  = $reg->hora_emision;
                $movstock                    = $this->nueva_cantidad - $this->cantidad;
                if ($movstock >= 0) {
                    $tran->movimiento = $movstock;
                    $tran->ingresos   = $movstock;
                } else {
                    $tran->movimiento = $movstock;
                    $tran->egresos    = abs($movstock);
                }
                if ($movstock != 0) {
                    $tran->costo = ($this->costototal / $movstock);
                } else {
                    $tran->costo = 0;
                }
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
            if ($reg = $this->get_regularizacion()) {
                if ($reg->idejercicio) {
                    $ejer0     = new \ejercicios();
                    $ejercicio = $ejer0->get($reg->idejercicio);
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
        $tr    = $trans->get_idlinearegularizacion($this->idlinearegularizacion);
        if ($tr) {
            return $tr->delete();
        }

        return true;
    }

    public function get_by_idempresa($idempresa)
    {
        $lista = array();

        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idregularizacion IN (SELECT idregularizacion FROM regularizaciones WHERE idempresa = " . $this->var2str($idempresa) . ")";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $lista[] = new \lineasregularizaciones($p);
            }
        }

        return $lista;
    }
}
