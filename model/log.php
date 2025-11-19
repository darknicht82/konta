<?php

class log extends model
{

    /**
     * TRUE -> resaltar en el listado.
     * @var boolean
     */
    public $alerta;

    /**
     *
     * @var string
     */
    public $controlador;

    /**
     * Texto del log. Sin longitud máxima.
     * @var string 
     */
    public $detalle;

    /**
     *
     * @var string
     */
    public $fecha;

    /**
     * Clave primaria.
     * @var integer 
     */
    public $id;

    /**
     *
     * @var string
     */
    public $ip;

    /**
     *
     * @var string
     */
    public $tipo;

    /**
     * Nick del usuario.
     * @var string
     */
    public $usuario;

    public function __construct($data = FALSE)
    {
        parent::__construct('logs');
        if ($data) {
            $this->alerta = $this->str2bool($data['alerta']);
            $this->controlador = $data['controlador'];
            $this->detalle = $data['detalle'];
            $this->fecha = date('d-m-Y H:i:s', strtotime($data['fecha']));
            $this->id = intval($data['id']);
            $this->ip = $data['ip'];
            $this->tipo = $data['tipo'];
            $this->usuario = $data['usuario'];
        } else {
            $this->alerta = FALSE;
            $this->controlador = NULL;
            $this->detalle = NULL;
            $this->fecha = date('d-m-Y H:i:s');
            $this->id = NULL;
            $this->ip = NULL;
            $this->tipo = NULL;
            $this->usuario = NULL;
        }
    }

    /**
     * 
     * @param string $id
     * @return \fs_log|boolean
     */
    public function get($id)
    {
        $data = $this->db->select("SELECT * FROM logs WHERE id = " . $this->var2str($id) . ";");
        if ($data) {
            return new log($data[0]);
        }

        return FALSE;
    }

    /**
     * 
     * @return boolean
     */
    public function exists()
    {
        if (is_null($this->id)) {
            return FALSE;
        }

        return (bool) $this->db->select("SELECT * FROM logs WHERE id = " . $this->var2str($this->id) . ";");
    }

    /**
     * 
     * @return boolean
     */
    public function test()
    {
        $this->controlador = $this->no_html($this->controlador);
        $this->detalle = $this->no_html($this->detalle);
        return true;
    }

    /**
     * 
     * @return boolean
     */
    public function save()
    {
        if (!$this->test()) {
            return false;
        }

        if ($this->exists()) {
            $sql = "UPDATE logs SET fecha = " . $this->var2str($this->fecha)
                . ", tipo = " . $this->var2str($this->tipo)
                . ", detalle = " . $this->var2str($this->detalle)
                . ", usuario = " . $this->var2str($this->usuario)
                . ", ip = " . $this->var2str($this->ip)
                . ", alerta = " . $this->var2str($this->alerta)
                . ", controlador = " . $this->var2str($this->controlador)
                . "  WHERE id=" . $this->var2str($this->id) . ";";

            return $this->db->exec($sql);
        }

        $sql = "INSERT INTO logs (fecha,tipo,detalle,usuario,ip,alerta,controlador) "
            . "VALUES (" . $this->var2str($this->fecha) . ","
            . $this->var2str($this->tipo) . ","
            . $this->var2str($this->detalle) . ","
            . $this->var2str($this->usuario) . ","
            . $this->var2str($this->ip) . ","
            . $this->var2str($this->alerta) . ","
            . $this->var2str($this->controlador) . ");";

        if ($this->db->exec($sql)) {
            $this->id = $this->db->lastval();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * 
     * @return bool
     */
    public function delete()
    {
        return $this->db->exec("DELETE FROM logs WHERE id = " . $this->var2str($this->id) . ";");
    }

    /**
     * 
     * @param int $offset
     * @param int $limit
     * @return \fs_log[]
     */
    public function all($offset = 0, $limit = JG_ITEM_LIMIT)
    {
        return $this->all_by_sql("SELECT * FROM logs ORDER BY fecha DESC", $offset, $limit);
    }

    /**
     * 
     * @param string $usuario
     * @return \fs_log[]
     */
    public function all_from($usuario)
    {
        return $this->all_by_sql("SELECT * FROM logs WHERE usuario = " . $this->var2str($usuario) . " ORDER BY fecha DESC");
    }

    /**
     * 
     * @param string $tipo
     * @return \fs_log[]
     */
    public function all_by($tipo)
    {
        return $this->all_by_sql("SELECT * FROM logs WHERE tipo = " . $this->var2str($tipo) . " ORDER BY fecha DESC");
    }

    /**
     * 
     * @param string $sql
     * @param int $offset
     * @param int $limit
     * @return \fs_log[]
     */
    private function all_by_sql($sql, $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $lista = [];
        $data = $this->db->select_limit($sql, $limit, $offset);
        if ($data) {
            foreach ($data as $d) {
                $lista[] = new log($d);
            }
        }

        return $lista;
    }
}
