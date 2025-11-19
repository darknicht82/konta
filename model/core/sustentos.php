<?php

namespace GSC_Systems\model;

class sustentos extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('sustentos');
        if ($data) {

            $this->idsustento = $data['idsustento'];
            $this->codigo     = $data['codigo'];
            $this->nombre     = $data['nombre'];
            $this->documentos = $data['documentos'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idsustento = null;
            $this->codigo     = null;
            $this->nombre     = null;
            $this->documentos = null;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;
        }
    }

    public function install()
    {
        $sql = "INSERT INTO ".$this->table_name." (codigo, nombre, documentos, fec_creacion, nick_creacion) VALUES";
        $sql .= "('01', 'Crédito Tributario para declaración de IVA (servicios y bienes distintos de inventarios y activos fijos)', '1,3,4,5,7,8,12,19,20,13,15,17,18', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('02', 'Costo o Gasto para declaración de IR (servicios y bienes distintos de inventarios y activos fijos)', '1,2,3,4,5,6,7,8,9,10,11,12,19,20,21,13,15,17,18', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('03', 'Activo Fijo - Crédito Tributario para declaración de IVA', '1,3,4,5,19,20,13,17,18', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('04', 'Activo Fijo - Costo o Gasto para declaración de IR', '1,2,3,4,5,9,19,20,13,17,18', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('05', 'Liquidación Gastos de Viaje, hospedaje y alimentación Gastos IR (a nombre de empleados y no de la empresa)', '1,2,3,4,5,7,9,19,20,13', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('06', 'Inventario - Crédito Tributario para declaración de IVA', '1,3,4,5,19,20,13,15,17,18', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('07', 'Inventario - Costo o Gasto para declaración de IR', '1,2,3,4,5,9,19,20,21,13,15,17,18', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('08', 'Valor pagado para solicitar Reembolso de Gasto (intermediario)', '1,2,3,4,5,12,19,20', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('09', 'Reembolso por Siniestros', '1,4,5,16', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('10', 'Distribución de Dividendos, Beneficios o Utilidades', '10', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('11', 'Convenios de débito o recaudación para IFI´s', '8', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('12', 'Impuestos y retenciones presuntivos', '14', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('13', 'Valores reconocidos por entidades del sector público a favor de sujetos pasivos', '10', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('14', 'Valores facturados por socios a operadoras de transporte (que no constituyen gasto de dicha operadora)', '1,2,3,4,5', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('15', 'Pagos efectuados por consumos propios y de terceros de servicios digitales', '1,2,3,4,5,8,9', '".date('Y-m-d')."', 'admin'),"; 
        $sql .= "('00', 'Casos especiales cuyo sustento no aplica en las opciones anteriores', '1,2,4,5,10,14', '".date('Y-m-d')."', 'admin');"; 
        return $sql;
    }

    public function url()
    {
        return 'index.php?page=lista_sustentos';
    }

    public function get($idsustento)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idsustento = " . $this->var2str($idsustento) . ";");
        if ($data) {
            return new \sustentos($data[0]);
        }

        return false;
    }

    public function get_by_codigo($codigo)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codigo = " . $this->var2str($codigo) . ";");
        if ($data) {
            return new \sustentos($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idsustento)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idsustento = " . $this->var2str($this->idsustento) . ";");
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
                $sql = "UPDATE " . $this->table_name . " SET nombre = " . $this->var2str($this->nombre)
                . ", codigo = " . $this->var2str($this->codigo)
                . ", documentos = " . $this->var2str($this->documentos)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idsustento = " . $this->var2str($this->idsustento) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (nombre, codigo, documentos, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->nombre)
                . "," . $this->var2str($this->codigo)
                . "," . $this->var2str($this->documentos)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idsustento = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idsustento = " . $this->var2str($this->idsustento) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY codigo ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \sustentos($p);
            }
        }

        return $list;
    }

    public function marcar_documento($iddocumento)
    {
        $documentos = explode(",", $this->documentos);
        foreach ($documentos as $key => $d) {
            if ($d == $iddocumento) {
                return true;
            }
        }
        return false;
    }

    public function all_by_iddocumento($iddocumento)
    {
        $list = array();
        $sustentos = $this->all();
        foreach ($sustentos as $key => $sus) {
            $documentos = explode(",", $sus->documentos);
            foreach ($documentos as $key => $d) {
                if ($d == $iddocumento) {
                    $list[] = $sus;
                }
            }
        }

        return $list;
    }
}
