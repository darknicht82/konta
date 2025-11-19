<?php

namespace GSC_Systems\model;

class regularizaciones extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('regularizaciones');
        if ($data) {

            $this->idregularizacion  = $data['idregularizacion'];
            $this->idempresa         = $data['idempresa'];
            $this->idestablecimiento = $data['idestablecimiento'];
            $this->numero            = $data['numero'];
            $this->fec_emision       = $data['fec_emision'] ? Date('d-m-Y', strtotime($data['fec_emision'])) : null;
            $this->hora_emision      = $data['hora_emision'] ? Date('H:i:s', strtotime($data['hora_emision'])) : null;
            $this->total             = floatval($data['total']);
            $this->observaciones     = $data['observaciones'];
            $this->idejercicio       = $data['idejercicio'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idregularizacion  = null;
            $this->idempresa         = null;
            $this->idestablecimiento = null;
            $this->numero            = null;
            $this->fec_emision       = null;
            $this->hora_emision      = null;
            $this->total             = 0;
            $this->observaciones     = null;
            $this->idejercicio       = null;
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
        if (is_null($this->idregularizacion)) {
            return 'index.php?page=lista_regularizaciones_stock';
        }

        return 'index.php?page=ver_regularizacion_stock&id=' . $this->idregularizacion;
    }

    public function get($idregularizacion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idregularizacion = " . $this->var2str($idregularizacion) . ";");
        if ($data) {
            return new \regularizaciones($data[0]);
        }

        return false;
    }

    public function getlineas()
    {
        $lineas = new \lineasregularizaciones();
        return $lineas->all_by_idregularizacion($this->idregularizacion);
    }

    public function get_establecimiento()
    {
        if ($this->idestablecimiento) {
            $est0 = new \establecimiento();
            $est  = $est0->get($this->idestablecimiento);
            if ($est) {
                return $est;
            }
        }
        return false;
    }

    public function exists()
    {
        if (is_null($this->idregularizacion)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idregularizacion = " . $this->var2str($this->idregularizacion) . ";");
    }

    public function test()
    {
        $status = true;
        if (!$this->numero) {
            $this->generar_numero();
        }

        if (complemento_exists('contabilidad')) {
            //si existe el plugin de contabilidad se debe buscar el ejercicio para generarlo
            if (!$this->idejercicio) {
                $ejer0     = new \ejercicios();
                $ejercicio = $ejer0->get_by_fecha($this->idempresa, $this->fec_emision);
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

    private function generar_numero()
    {
        $numero = $this->buscar_numero();
        if ($this->validar_numero($numero)) {
            $this->generar_numero();
        } else {
            $this->numero = $numero;
            return true;
        }
        return false;
    }

    private function buscar_numero()
    {
        $numero = 1;
        $sql    = "SELECT MAX(numero) AS num FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($this->idempresa);
        $data   = $this->db->select($sql);
        if ($data) {
            $numero = intval($data[0]['num']) + 1;
        }

        return $numero;
    }

    private function validar_numero($numero)
    {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE numero = " . $this->var2str($numero) . " AND idempresa = " . $this->var2str($this->idempresa);
        $data = $this->db->select($sql);
        if ($data) {
            return true;
        }

        return false;
    }

    public function save()
    {
        if ($this->test()) {
            $insert = false;
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET idempresa = " . $this->var2str($this->idempresa)
                . ", idestablecimiento = " . $this->var2str($this->idestablecimiento)
                . ", numero = " . $this->var2str($this->numero)
                . ", fec_emision = " . $this->var2str($this->fec_emision)
                . ", hora_emision = " . $this->var2str($this->hora_emision)
                . ", total = " . $this->var2str($this->total)
                . ", observaciones = " . $this->var2str($this->observaciones)
                . ", idejercicio = " . $this->var2str($this->idejercicio)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idregularizacion = " . $this->var2str($this->idregularizacion) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idestablecimiento, numero, fec_emision, hora_emision, total, observaciones, idejercicio, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idestablecimiento)
                . "," . $this->var2str($this->numero)
                . "," . $this->var2str($this->fec_emision)
                . "," . $this->var2str($this->hora_emision)
                . "," . $this->var2str($this->total)
                . "," . $this->var2str($this->observaciones)
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
                        $this->idregularizacion = $this->db->lastval();
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
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idregularizacion = " . $this->var2str($this->idregularizacion) . ";");
        }
        return false;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY fec_emision DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \regularizaciones($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY fec_emision DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \regularizaciones($p);
            }
        }

        return $list;
    }

    public function search_regularizaciones($idempresa = '', $query = '', $idestablecimiento = '', $fechadesde = '', $fechahasta = '', $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        if ($idempresa != '') {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }

        if ($fechadesde != '') {
            $sql .= " AND fec_emision >= " . $this->var2str($fechadesde);
        }
        if ($fechahasta != '') {
            $sql .= " AND fec_emision <= " . $this->var2str($fechahasta);
        }
        if ($idestablecimiento != '') {
            $sql .= " AND idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($query != '') {
            $query = strtolower($query);
            $sql .= " AND (lower(numero::text) LIKE '%" . $query . "%' OR lower(observaciones) LIKE '%" . $query . "%')";
        }

        if ($offset >= 0) {
            $sql .= " ORDER BY fec_emision DESC, numero DESC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }

        $data = $this->db->select($sql);
        if ($data) {
            if ($offset < 0) {
                return count($data);
            } else {
                foreach ($data as $p) {
                    $list[] = new \regularizaciones($p);
                }
            }
        }

        return $list;
    }

    public function get_tipodoc()
    {
        return 'Regularización de Stock';
    }

    public function get_nombreestablecimiento()
    {
        $establecimiento = new \establecimiento();
        $doc             = $establecimiento->get($this->idestablecimiento);
        if ($doc) {
            return $doc->nombre;
        }

        return '-';
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

        //elimino las lineas para reversar el stock
        foreach ($this->getlineas() as $key => $l) {
            $l->delete();
        }

        return true;
    }
}
