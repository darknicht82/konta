<?php

namespace GSC_Systems\model;

class cab_devolucion_cliente extends \model
{
    public $nombre_fp;
    public function __construct($data = false)
    {
        parent::__construct('cab_devolucion_cliente');
        if ($data) {
            $this->iddevolucioncli = $data['iddevolucioncli'];
            $this->idempresa       = $data['idempresa'];
            $this->idcliente       = $data['idcliente'];
            $this->idformapago     = $data['idformapago'];
            $this->numero          = $data['numero'];
            $this->fecha_trans     = Date('d-m-Y', strtotime($data['fecha_trans']));
            $this->num_doc         = $data['num_doc'];
            $this->valor           = floatval($data['valor']);
            $this->idcaja          = $data['idcaja'];
            $this->observaciones   = $data['observaciones'];
            $this->anulado         = $this->str2bool($data['anulado']);
            $this->idejercicio     = $data['idejercicio'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];
        } else {
            $this->iddevolucioncli = null;
            $this->idempresa       = null;
            $this->idcliente       = null;
            $this->idformapago     = null;
            $this->numero          = null;
            $this->fecha_trans     = null;
            $this->num_doc         = null;
            $this->valor           = 0;
            $this->idcaja          = null;
            $this->observaciones   = null;
            $this->anulado         = false;
            $this->idejercicio     = null;
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
        new \clientes();
        new \formaspago();

        $sql = "";
        return $sql;
    }

    public function url()
    {
        return '';
    }

    public function get($iddevolucioncli)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE iddevolucioncli = " . $this->var2str($iddevolucioncli) . ";");
        if ($data) {
            return new \cab_devolucion_cliente($data[0]);
        }

        return false;
    }

    public function getDetalle()
    {
        $lineas = new \trans_cobros();
        return $lineas->all_by_iddevolucioncli($this->iddevolucioncli);
    }

    public function get_fp()
    {
        $fp = '-';
        if ($this->idformapago) {
            $fp0 = new \formaspago();
            $fpa = $fp0->get($this->idformapago);
            if ($fpa) {
                $fp = $fpa->nombre;
            }
        }

        if ($this->num_doc) {
            $fp .= " - Nro: " . $this->num_doc;
        }

        return $fp;
    }

    public function exists()
    {
        if (is_null($this->iddevolucioncli)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE iddevolucioncli = " . $this->var2str($this->iddevolucioncli) . ";");
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
                $ejercicio = $ejer0->get_by_fecha($this->idempresa, $this->fecha_trans);
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
                . ", idcliente = " . $this->var2str($this->idcliente)
                . ", idformapago = " . $this->var2str($this->idformapago)
                . ", numero = " . $this->var2str($this->numero)
                . ", fecha_trans = " . $this->var2str($this->fecha_trans)
                . ", num_doc = " . $this->var2str($this->num_doc)
                . ", valor = " . $this->var2str($this->valor)
                . ", idcaja = " . $this->var2str($this->idcaja)
                . ", observaciones = " . $this->var2str($this->observaciones)
                . ", anulado = " . $this->var2str($this->anulado)
                . ", idejercicio = " . $this->var2str($this->idejercicio)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE iddevolucioncli = " . $this->var2str($this->iddevolucioncli) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idcliente, idformapago, numero, fecha_trans, num_doc, valor, idcaja, observaciones, anulado, idejercicio, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idcliente)
                . "," . $this->var2str($this->idformapago)
                . "," . $this->var2str($this->numero)
                . "," . $this->var2str($this->fecha_trans)
                . "," . $this->var2str($this->num_doc)
                . "," . $this->var2str($this->valor)
                . "," . $this->var2str($this->idcaja)
                . "," . $this->var2str($this->observaciones)
                . "," . $this->var2str($this->anulado)
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
                        $this->iddevolucioncli = $this->db->lastval();
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
            if ($this->db->exec("DELETE FROM " . $this->table_name . " WHERE iddevolucioncli = " . $this->var2str($this->iddevolucioncli) . ";")) {
                //si se elimina borro los cobros asociados.
                $trans_cobros = new \trans_cobros();
                if (!$trans_cobros->deletebyIdDevolucion($this->iddevolucioncli)) {
                    $this->new_error_msg('Error al eliminar cobros de la Devolucion.');
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    private function beforeDelete()
    {
        if ($this->anulado) {
            $this->new_error_msg('La Devolucion se encuentra anulada, no es posible eliminar.');
            return false;
        }

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
        
        return true;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idcliente ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \cab_devolucion_cliente($p);
            }
        }

        return $list;
    }

    public function getCliente()
    {
        if ($this->idcliente) {
            $cli0 = new \clientes();
            return $cli0->get($this->idcliente);
        }

        return false;
    }

    public function buscarDevolucionCli($idempresa = '', $query = '', $idcliente = '', $idformapago = '', $fechadesde = '', $fechahasta = '', $sproveedor = false, $anuladas = false, $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";

        if ($idempresa != '') {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }
        if ($idcliente != '') {
            $sql .= " AND idcliente = " . $this->var2str($idcliente);
        }
        if ($idformapago != '') {
            $sql .= " AND idformapago = " . $this->var2str($idformapago);
        }
        if ($fechadesde != '') {
            $sql .= " AND fecha_trans >= " . $this->var2str($fechadesde);
        }
        if ($fechahasta != '') {
            $sql .= " AND fecha_trans <= " . $this->var2str($fechahasta);
        }
        if ($sproveedor) {
            $sql .= " AND idcliente IS NULL";
        }
        if ($anuladas) {
            $sql .= " AND anulado = " . $this->var2str(true);
        }
        if ($query != '') {
            $query = strtolower($query);
            $sql .= " AND (numero = " . $this->var2str($query) . " OR lower(observaciones) LIKE '%" . $query . "%' OR lower(num_doc) LIKE '%" . $query . "%')";
        }

        if ($offset >= 0) {
            $sql .= " ORDER BY numero DESC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }
        if ($data) {
            if ($offset < 0) {
                return count($data);
            } else {
                foreach ($data as $p) {
                    $list[] = new \cab_devolucion_cliente($p);
                }
            }
        }

        return $list;
    }

    public function getNumDoc($idempresa, $idformapago, $num_doc)
    {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idformapago = " . $this->var2str($idformapago) . " AND num_doc = " . $this->var2str($num_doc) . ";";
        $data = $this->db->select($sql);
        if ($data) {
            return true;
        }

        return false;
    }
}
