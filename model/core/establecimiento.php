<?php
namespace GSC_Systems\model;

class establecimiento extends \model
{
    public $idestablecimiento;
    public $idempresa;
    public $codigo;
    public $nombre;
    public $direccion;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($d = false)
    {
        parent::__construct('establecimiento');

        if ($d) {
            $this->idestablecimiento = $this->intval($d['idestablecimiento']);
            $this->idempresa         = $d['idempresa'];
            $this->codigo            = $d['codigo'];
            $this->nombre            = $d['nombre'];
            $this->direccion         = $d['direccion'];
            $this->ptoemision        = $d['ptoemision'];
            $this->numfac            = $this->intval($d['numfac']);
            $this->numncc            = $this->intval($d['numncc']);
            $this->numndd            = $this->intval($d['numndd']);
            $this->numliq            = $this->intval($d['numliq']);
            $this->numret            = $this->intval($d['numret']);
            $this->numguia           = $this->intval($d['numguia']);
            $this->numnvt            = $this->intval($d['numnvt']);
            //Auditoria del sistema
            $this->fec_creacion      = $d['fec_creacion'] ? Date('Y-m-d', strtotime($d['fec_creacion'])) : null;
            $this->nick_creacion     = $d['nick_creacion'];
            $this->fec_modificacion  = $d['fec_modificacion'] ? Date('Y-m-d', strtotime($d['fec_modificacion'])) : null;
            $this->nick_modificacion = $d['nick_modificacion'];
        } else {
            $this->idestablecimiento = null;
            $this->idempresa         = null;
            $this->codigo            = '001';
            $this->nombre            = null;
            $this->direccion         = null;
            $this->ptoemision        = '001';
            $this->numfac            = 1;
            $this->numncc            = 1;
            $this->numndd            = 1;
            $this->numliq            = 1;
            $this->numret            = 1;
            $this->numguia           = 1;
            $this->numnvt            = 1;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;

        }
    }

    protected function install()
    {
        new \empresa();
        
        return "INSERT INTO " . $this->table_name . " (idempresa, codigo, nombre, direccion, ptoemision, fec_creacion, nick_creacion) VALUES ('1', '001', 'Matriz', 'Quito', '001', '" . date('Y-m-d') . "', 'admin');";
    }

    /**
     * Devuelve la url donde ver/modificar los datos
     * @return string
     */
    public function url()
    {
        return 'index.php?page=ver_empresa';
    }

    /**
     * Devuelve TRUE si existe
     * @return boolean
     */
    public function exists()
    {
        if (is_null($this->idestablecimiento)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idestablecimiento = " . $this->var2str($this->idestablecimiento) . ";");
    }

    public function get($idestablecimiento)
    {

        $sql = "SELECT * FROM " . $this->table_name . " WHERE idestablecimiento = " . $this->var2str($idestablecimiento) . ";";

        $data = $this->db->select($sql);
        if ($data) {
            return new \establecimiento($data[0]);
        }

        return false;
    }

    /**
     * Comprueba los datos de la establecimiento, devuelve TRUE si está todo correcto
     * @return boolean
     */
    public function test()
    {
        $status           = false;
        $this->codigo     = str_pad(trim($this->codigo), 3, '0', STR_PAD_LEFT);
        $this->ptoemision = str_pad(trim($this->ptoemision), 3, '0', STR_PAD_LEFT);
        $this->nombre     = trim($this->nombre);
        $this->direccion  = trim($this->direccion);

        if (strlen($this->codigo) < 1 || strlen($this->codigo) > 3) {
            $this->new_error_msg("Código de establecimiento no válido.");
        } else if (strlen($this->ptoemision) < 1 || strlen($this->ptoemision) > 3) {
            $this->new_error_msg("Punto de emisión del establecimiento no válido.");
        } else if (strlen($this->nombre) < 1 || strlen($this->nombre) > 250) {
            $this->new_error_msg("Nombre del establecimiento no válido.");
        } else {
            $status = true;
        }

        return $status;
    }

    /**
     * Guarda los datos en la base de datos
     * @return boolean
     */
    public function save()
    {
        if ($this->test()) {
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET idempresa = " . $this->var2str($this->idempresa)
                . ", codigo = " . $this->var2str($this->codigo)
                . ", nombre = " . $this->var2str($this->nombre)
                . ", direccion = " . $this->var2str($this->direccion)
                . ", ptoemision = " . $this->var2str($this->ptoemision)
                . ", numfac = " . $this->var2str($this->numfac)
                . ", numncc = " . $this->var2str($this->numncc)
                . ", numndd = " . $this->var2str($this->numndd)
                . ", numliq = " . $this->var2str($this->numliq)
                . ", numret = " . $this->var2str($this->numret)
                . ", numguia = " . $this->var2str($this->numguia)
                . ", numnvt = " . $this->var2str($this->numnvt)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idestablecimiento = " . $this->var2str($this->idestablecimiento) . ";";

                return $this->db->exec($sql);
            }

            $sql = "INSERT INTO " . $this->table_name . " (idempresa, codigo, nombre, direccion, ptoemision, numfac, numncc, numndd, numliq, numret, numguia, numnvt, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES
                      (" . $this->var2str($this->idempresa)
            . "," . $this->var2str($this->codigo)
            . "," . $this->var2str($this->nombre)
            . "," . $this->var2str($this->direccion)
            . "," . $this->var2str($this->ptoemision)
            . "," . $this->var2str($this->numfac)
            . "," . $this->var2str($this->numncc)
            . "," . $this->var2str($this->numndd)
            . "," . $this->var2str($this->numliq)
            . "," . $this->var2str($this->numret)
            . "," . $this->var2str($this->numguia)
            . "," . $this->var2str($this->numnvt)
            . "," . $this->var2str($this->fec_creacion)
            . "," . $this->var2str($this->nick_creacion)
            . "," . $this->var2str($this->fec_modificacion)
            . "," . $this->var2str($this->nick_modificacion)
                . ");";

            if ($this->db->exec($sql)) {
                $this->idestablecimiento = $this->db->lastval();
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        $sql = "DELETE FROM " . $this->table_name . " WHERE idestablecimiento = " . $this->var2str($this->idestablecimiento);
        return $this->db->exec($sql);
    }

    public function get_by_idempresa($idempresa)
    {
        $lista = array();
        $data  = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY codigo ASC;");
        if ($data) {
            foreach ($data as $key => $d) {
                $lista[] = new establecimiento($d);
            }
        }

        return $lista;
    }

    public function getEstablecimientosEmpresa($idempresa)
    {
        $list = array();

        $sql = "SELECT codigo FROM " . $this->table_name . " WHERE idempresa = ".$this->var2str($idempresa)." GROUP BY codigo;";

        $data = $this->db->select($sql);

        if ($data) {
            return $data;
        }

        return $list;
    }

}
