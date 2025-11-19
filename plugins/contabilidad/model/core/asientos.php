<?php

namespace GSC_Systems\model;

class asientos extends \model
{
    public $idasiento;
    public $idempresa;
    public $idejercicio;
    public $tipo;
    public $numero;
    public $fecha;
    public $concepto;
    public $nombre;
    public $valor;
    public $editable;
    public $idformapago;
    public $num_doc;
    //Movimientos de Proveedor
    public $idfacturaprov;
    public $idpago;
    public $idanticipoprov;
    public $iddevolucionprov;
    public $idtranspago;
    //Movimientos de Cliente
    public $idfacturacli;
    public $idcobro;
    public $idanticipocli;
    public $iddevolucioncli;
    public $idtranscobro;
    public $idretencion;
    //Movimientos de Articulos
    public $idmovimiento;
    public $idregularizacion;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('asientos');
        if ($data) {

            $this->idasiento   = $data['idasiento'];
            $this->idempresa   = $data['idempresa'];
            $this->idejercicio = $data['idejercicio'];
            $this->tipo        = $data['tipo'];
            $this->numero      = $data['numero'];
            $this->fecha       = $data['fecha'] ? Date('d-m-Y', strtotime($data['fecha'])) : null;
            $this->concepto    = $data['concepto'];
            $this->nombre      = $data['nombre'];
            $this->valor       = floatval($data['valor']);
            $this->editable    = $this->str2bool($data['editable']);
            $this->idformapago = $data['idformapago'];
            $this->num_doc     = $data['num_doc'];
            //Movimientos de Proveedor
            $this->idfacturaprov    = $data['idfacturaprov'];
            $this->idpago           = $data['idpago'];
            $this->idanticipoprov   = $data['idanticipoprov'];
            $this->iddevolucionprov = $data['iddevolucionprov'];
            $this->idtranspago      = $data['idtranspago'];
            //Movimientos de Cliente
            $this->idfacturacli    = $data['idfacturacli'];
            $this->idcobro         = $data['idcobro'];
            $this->idanticipocli   = $data['idanticipocli'];
            $this->iddevolucioncli = $data['iddevolucioncli'];
            $this->idtranscobro    = $data['idtranscobro'];
            $this->idretencion     = $data['idretencion'];
            //Movimientos de Articulos
            $this->idmovimiento     = $data['idmovimiento'];
            $this->idregularizacion = $data['idregularizacion'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idasiento        = null;
            $this->idempresa        = null;
            $this->idejercicio      = null;
            $this->tipo             = null;
            $this->numero           = null;
            $this->fecha            = null;
            $this->concepto         = null;
            $this->nombre           = null;
            $this->valor            = 0;
            $this->editable         = false;
            $this->idformapago      = null;
            $this->num_doc          = null;
            $this->idfacturaprov    = null;
            $this->idpago           = null;
            $this->idanticipoprov   = null;
            $this->iddevolucionprov = null;
            $this->idtranspago      = null;
            $this->idfacturacli     = null;
            $this->idcobro          = null;
            $this->idanticipocli    = null;
            $this->iddevolucioncli  = null;
            $this->idtranscobro     = null;
            $this->idretencion      = null;
            $this->idmovimiento     = null;
            $this->idregularizacion = null;
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
        new \ejercicios();
        new \formaspago();
        new \facturasprov();
        
        if(file_exists('plugins/pagosycobros/model/cab_pagos.php')) require_once 'plugins/pagosycobros/model/cab_pagos.php';
        if(class_exists('\cab_pagos')) new \cab_pagos();
        
        if(file_exists('plugins/pagosycobros/model/anticiposprov.php')) require_once 'plugins/pagosycobros/model/anticiposprov.php';
        if(class_exists('\anticiposprov')) new \anticiposprov();
        
        if(file_exists('plugins/pagosycobros/model/cab_devolucion_proveedor.php')) require_once 'plugins/pagosycobros/model/cab_devolucion_proveedor.php';
        if(class_exists('\cab_devolucion_proveedor')) new \cab_devolucion_proveedor();
        
        new \trans_pagos();
        new \facturascli();
        
        if(file_exists('plugins/pagosycobros/model/cab_cobros.php')) require_once 'plugins/pagosycobros/model/cab_cobros.php';
        if(class_exists('\cab_cobros')) new \cab_cobros();
        
        if(file_exists('plugins/pagosycobros/model/anticiposcli.php')) require_once 'plugins/pagosycobros/model/anticiposcli.php';
        if(class_exists('\anticiposcli')) new \anticiposcli();
        
        if(file_exists('plugins/pagosycobros/model/cab_devolucion_cliente.php')) require_once 'plugins/pagosycobros/model/cab_devolucion_cliente.php';
        if(class_exists('\cab_devolucion_cliente')) new \cab_devolucion_cliente();
        
        new \trans_cobros();
        new \retencionescli();
        new \movimientos();
        new \regularizaciones();

        return "";
    }

    public function url()
    {
        if ($this->idasiento) {
            return 'index.php?page=ver_asiento&id=' . $this->idasiento;
        }
        return 'index.php?page=lista_asientos';
    }

    public function get($idasiento)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idasiento = " . $this->var2str($idasiento) . ";");
        if ($data) {
            return new \asientos($data[0]);
        }

        return false;
    }

    public function getEjercicio()
    {
        if ($this->idejercicio) {
            $data = $this->db->select("SELECT * FROM ejercicios WHERE idejercicio = " . $this->var2str($this->idejercicio) . ";");
            if ($data) {
                return $data[0]['nombre'];
            }
        }

        return '-';
    }

    public function exists()
    {
        if (is_null($this->idasiento)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idasiento = " . $this->var2str($this->idasiento) . ";");
    }

    public function test()
    {
        $status = true;
        if (!$this->numero) {
            if (!$this->generar_numero()) {
                $status = false;
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
                . ", idejercicio = " . $this->var2str($this->idejercicio)
                . ", tipo = " . $this->var2str($this->tipo)
                . ", numero = " . $this->var2str($this->numero)
                . ", fecha = " . $this->var2str($this->fecha)
                . ", concepto = " . $this->var2str($this->concepto)
                . ", nombre = " . $this->var2str($this->nombre)
                . ", valor = " . $this->var2str($this->valor)
                . ", editable = " . $this->var2str($this->editable)
                . ", idformapago = " . $this->var2str($this->idformapago)
                . ", num_doc = " . $this->var2str($this->num_doc)
                . ", idfacturaprov = " . $this->var2str($this->idfacturaprov)
                . ", idpago = " . $this->var2str($this->idpago)
                . ", idanticipoprov = " . $this->var2str($this->idanticipoprov)
                . ", iddevolucionprov = " . $this->var2str($this->iddevolucionprov)
                . ", idtranspago = " . $this->var2str($this->idtranspago)
                . ", idfacturacli = " . $this->var2str($this->idfacturacli)
                . ", idcobro = " . $this->var2str($this->idcobro)
                . ", idanticipocli = " . $this->var2str($this->idanticipocli)
                . ", iddevolucioncli = " . $this->var2str($this->iddevolucioncli)
                . ", idtranscobro = " . $this->var2str($this->idtranscobro)
                . ", idretencion = " . $this->var2str($this->idretencion)
                . ", idmovimiento = " . $this->var2str($this->idmovimiento)
                . ", idregularizacion = " . $this->var2str($this->idregularizacion)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idasiento = " . $this->var2str($this->idasiento) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idejercicio, tipo, numero, fecha, concepto, nombre, valor, editable, idformapago, num_doc, idfacturaprov, idpago, idanticipoprov, iddevolucionprov, idtranspago, idfacturacli, idcobro, idanticipocli, iddevolucioncli, idtranscobro, idretencion, idmovimiento, idregularizacion, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idejercicio)
                . "," . $this->var2str($this->tipo)
                . "," . $this->var2str($this->numero)
                . "," . $this->var2str($this->fecha)
                . "," . $this->var2str($this->concepto)
                . "," . $this->var2str($this->nombre)
                . "," . $this->var2str($this->valor)
                . "," . $this->var2str($this->editable)
                . "," . $this->var2str($this->idformapago)
                . "," . $this->var2str($this->num_doc)
                . "," . $this->var2str($this->idfacturaprov)
                . "," . $this->var2str($this->idpago)
                . "," . $this->var2str($this->idanticipoprov)
                . "," . $this->var2str($this->iddevolucionprov)
                . "," . $this->var2str($this->idtranspago)
                . "," . $this->var2str($this->idfacturacli)
                . "," . $this->var2str($this->idcobro)
                . "," . $this->var2str($this->idanticipocli)
                . "," . $this->var2str($this->iddevolucioncli)
                . "," . $this->var2str($this->idtranscobro)
                . "," . $this->var2str($this->idretencion)
                . "," . $this->var2str($this->idmovimiento)
                . "," . $this->var2str($this->idregularizacion)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idasiento = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        if (!$this->beforeDelete()) {
            return false;
        }
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idasiento = " . $this->var2str($this->idasiento) . ";");
    }

    private function beforeDelete()
    {
        return true;
    }

    public function search_asientos($idempresa = '', $query = '', $tipo = '', $fechadesde = '', $fechahasta = '', $idejercicio = '', $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        if ($idempresa != '') {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }
        if ($query != '') {
            if (is_numeric($query)) {
                $sql .= " AND (numero LIKE '%" . $query . "%' OR valor LIKE '%" . $query . "%')";
            } else {
                $query = strtolower($query);
                $sql .= " AND (lower(concepto) LIKE '%" . $query . "%' OR lower(nombre) LIKE '%" . $query . "%' OR lower(num_doc) LIKE '%" . $query . "%')";
            }
        }
        if ($tipo != '') {
            $sql .= " AND tipo = " . $this->var2str($tipo);
        }
        if ($idejercicio != '') {
            $sql .= " AND idejercicio = " . $this->var2str($idejercicio);
        }
        if ($fechadesde != '') {
            $sql .= " AND fecha >= " . $this->var2str($fechadesde);
        }
        if ($fechahasta != '') {
            $sql .= " AND fecha <= " . $this->var2str($fechahasta);
        }

        if ($offset >= 0) {
            $sql .= " ORDER BY fecha DESC, numero DESC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }
        if ($data) {
            if ($offset < 0) {
                return count($data);
            } else {
                foreach ($data as $p) {
                    $list[] = new \asientos($p);
                }
            }
        }

        return $list;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY numero DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \asientos($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY numero DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \asientos($p);
            }
        }

        return $list;
    }

    public function get_by_tipo($idempresa, $tipo)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND tipo <= " . $this->var2str($tipo) . " AND fecha >= " . $this->var2str($tipo) . ";");
        if ($data) {
            return new \asientos($data[0]);
        }
        return false;
    }

    public function getTipos($idempresa)
    {
        $list = array();
        $data = $this->db->select("SELECT DISTINCT(tipo) AS tipo FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . ";");
        if ($data) {
            return $data;
        }
        return $list;
    }
}
