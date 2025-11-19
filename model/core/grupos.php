<?php

namespace GSC_Systems\model;

class grupos extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('grupos');
        if ($data) {

            $this->idgrupo   = $data['idgrupo'];
            $this->idempresa = $data['idempresa'];
            $this->nombre    = $data['nombre'];
            $this->imagen    = $data['imagen'];
            $this->idpadre   = $data['idpadre'];
            $this->menu      = $this->str2bool($data['menu']);
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

            $this->espadre    = $this->isPadre();
            $this->url_imagen = $this->imagen();
        } else {
            $this->idgrupo   = null;
            $this->idempresa = null;
            $this->nombre    = null;
            $this->imagen    = null;
            $this->idpadre   = null;
            $this->menu      = false;
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
        return "";
    }

    public function url()
    {
        return 'index.php?page=configuracion_articulos';
    }

    public function get($idgrupo)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idgrupo = " . $this->var2str($idgrupo) . ";");
        if ($data) {
            return new \grupos($data[0]);
        }

        return false;
    }

    public function get_by_nombre($idempresa, $nombre, $idpadre = null)
    {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE UPPER(TRIM(nombre)) = " . $this->var2str($nombre) . " AND idempresa = " . $this->var2str($idempresa);
        if ($idpadre) {
            $sql .= " AND idpadre = " . $this->var2str($idpadre);
        }
        $data = $this->db->select($sql);
        if ($data) {
            return new \grupos($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idgrupo)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idgrupo = " . $this->var2str($this->idgrupo) . ";");
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
                . ", menu = " . $this->var2str($this->menu)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idgrupo = " . $this->var2str($this->idgrupo) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, nombre, imagen, idpadre, menu, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->nombre)
                . "," . $this->var2str($this->imagen)
                . "," . $this->var2str($this->idpadre)
                . "," . $this->var2str($this->menu)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idgrupo = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idpadre = " . $this->var2str($this->idgrupo));
        if ($data) {
            $this->new_error_msg("El grupo " . $this->nombre . " tiene subgrupos asignados, no es posible eliminar.");
            return false;
        }
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idgrupo = " . $this->var2str($this->idgrupo) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \grupos($p);
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
                $list[] = new \grupos($p);
            }
        }

        return $list;
    }

    public function mostrarGrupos($idempresa)
    {
        /// lee la lista de la caché
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY nombre ASC";
        /// si la lista no está en caché, leemos de la base de datos
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $d) {
                if (is_null($d['idpadre'])) {
                    $list[] = new \grupos($d);
                    foreach ($this->aux_all($data, $d['idgrupo'], '&nbsp;&nbsp;&nbsp;&nbsp; -&nbsp;&nbsp;') as $value) {
                        $list[] = new \grupos($value);
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
                foreach ($this->aux_all($pcuentas, $pc['idgrupo'], '&nbsp;&nbsp;&nbsp;&nbsp;' . $nivel) as $value) {
                    $sublist[] = $value;
                }
            }
        }

        return $sublist;
    }

    public function isPadre()
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idpadre = " . $this->var2str($this->idgrupo) . ";");
        if ($data) {
            return '1';
        }

        return '0';
    }

    public function getMenu($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idpadre IS NULL AND menu = " . $this->var2str(true) . " ORDER BY nombre ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \grupos($p);
            }
        }

        return $list;
    }

    public function getSubMenu($idempresa, $idgrupo)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idpadre = " . $this->var2str($idgrupo) . " ORDER BY nombre ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \grupos($p);
            }
        }

        return $list;
    }

    public function imagen()
    {
        if ($this->imagen) {
            return $this->imagen;
        }

        return 'view/img/sinimggrupo.png';
    }
}
