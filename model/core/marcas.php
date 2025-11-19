<?php

namespace GSC_Systems\model;

class marcas extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('marcas');
        if ($data) {

            $this->idmarca   = $data['idmarca'];
            $this->idempresa = $data['idempresa'];
            $this->nombre    = $data['nombre'];
            $this->imagen    = $data['imagen'];
            $this->idpadre   = $data['idpadre'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];
            //Niveles
            $this->nivel = '-&nbsp;&nbsp;';
            if (isset($data['nivel'])) {
                $this->nivel = $data['nivel'];
            }
        } else {
            $this->idmarca   = null;
            $this->idempresa = null;
            $this->nombre    = null;
            $this->imagen    = null;
            $this->idpadre   = null;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;
            //Niveles
            $this->nivel = '-&nbsp;&nbsp;';
        }
    }

    public function install()
    {
        new \empresa();

        return "";
    }

    public function url()
    {
        return 'index.php?page=configuracion_articulos';
    }

    public function get($idmarca)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idmarca = " . $this->var2str($idmarca) . ";");
        if ($data) {
            return new \marcas($data[0]);
        }

        return false;
    }

    public function get_by_nombre($idempresa, $nombre, $idpadre = false)
    {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE UPPER(TRIM(nombre)) = " . $this->var2str($nombre) . " AND idempresa = " . $this->var2str($idempresa);
        if ($idpadre) {
            $sql .= " AND idpadre = " . $this->var2str($idpadre);
        }
        $data = $this->db->select($sql);
        if ($data) {
            return new \marcas($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idmarca)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idmarca = " . $this->var2str($this->idmarca) . ";");
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
                . ", nombre = " . $this->var2str($this->nombre)
                . ", imagen = " . $this->var2str($this->imagen)
                . ", idpadre = " . $this->var2str($this->idpadre)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idmarca = " . $this->var2str($this->idmarca) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, nombre, imagen, idpadre, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->nombre)
                . "," . $this->var2str($this->imagen)
                . "," . $this->var2str($this->idpadre)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idmarca = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idpadre = " . $this->var2str($this->idmarca));
        if ($data) {
            $this->new_error_msg("La marca " . $this->nombre . " tiene submarcas asignadas, no es posible eliminar.");
            return false;
        }
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idmarca = " . $this->var2str($this->idmarca) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \marcas($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY nombre ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \marcas($p);
            }
        }

        return $list;
    }

    public function mostrarMarcas($idempresa)
    {
        /// lee la lista de la caché
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY nombre ASC";
        /// si la lista no está en caché, leemos de la base de datos
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $d) {
                if (is_null($d['idpadre'])) {
                    $list[] = new \marcas($d);
                    foreach ($this->aux_all($data, $d['idmarca'], '&nbsp;&nbsp;&nbsp;&nbsp; -&nbsp;&nbsp;') as $value) {
                        $list[] = new \marcas($value);
                    }
                }
            }
        }

        return $list;
    }

    private function aux_all(&$pcuentas, $idpadre, $nivel = '-&nbsp;&nbsp;')
    {
        $sublist = array();

        foreach ($pcuentas as $pc) {
            if ($pc['idpadre'] === $idpadre) {
                $pc['nivel'] = $nivel;
                $sublist[]   = $pc;
                foreach ($this->aux_all($pcuentas, $pc['idmarca'], '&nbsp;&nbsp;&nbsp;&nbsp;' . $nivel) as $value) {
                    $sublist[] = $value;
                }
            }
        }

        return $sublist;
    }
}
