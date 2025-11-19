<?php

namespace GSC_Systems\model;

class formaspago_sri extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('formaspago_sri');
        if ($data) {

            $this->idfpsri = $data['idfpsri'];
            $this->nombre  = $data['nombre'];
            $this->codigo  = $data['codigo'];
            $this->activo  = $this->str2bool($data['activo']);
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idfpsri = null;
            $this->nombre  = null;
            $this->codigo  = null;
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

        $sql = "INSERT INTO " . $this->table_name . " (nombre, codigo, activo, fec_creacion, nick_creacion) VALUES
            ('SIN UTILIZACION DEL SISTEMA FINANCIERO', '01', " . $this->var2str(true) . "," . $this->var2str(date('Y-m-d')) . ", 'admin'),
            ('COMPENSACIÓN DE DEUDAS', '15', " . $this->var2str(true) . "," . $this->var2str(date('Y-m-d')) . ", 'admin'),
            ('TARJETA DE DÉBITO', '16', " . $this->var2str(true) . "," . $this->var2str(date('Y-m-d')) . ", 'admin'),
            ('DINERO ELECTRÓNICO', '17', " . $this->var2str(true) . "," . $this->var2str(date('Y-m-d')) . ", 'admin'),
            ('TARJETA PREPAGO', '18', " . $this->var2str(true) . "," . $this->var2str(date('Y-m-d')) . ", 'admin'),
            ('TARJETA DE CRÉDITO', '19', " . $this->var2str(true) . "," . $this->var2str(date('Y-m-d')) . ", 'admin'),
            ('OTROS CON UTILIZACIÓN DEL SISTEMA FINANCIERO', '20', " . $this->var2str(true) . "," . $this->var2str(date('Y-m-d')) . ", 'admin'),
            ('ENDOSO DE TÍTULOS', '21', " . $this->var2str(true) . "," . $this->var2str(date('Y-m-d')) . ", 'admin')";
        return $sql;
    }

    public function url()
    {
        return 'index.php?page=formaspago';
    }

    public function get($idfpsri)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idfpsri = " . $this->var2str($idfpsri) . ";");
        if ($data) {
            return new \formaspago_sri($data[0]);
        }

        return false;
    }

    public function get_by_codigo($codigo)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codigo = " . $this->var2str($codigo) . ";");
        if ($data) {
            return new \formaspago_sri($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idfpsri)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idfpsri = " . $this->var2str($this->idfpsri) . ";");
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
                . ", activo = " . $this->var2str($this->activo)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idfpsri = " . $this->var2str($this->idfpsri) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (nombre, codigo, activo, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->nombre)
                . "," . $this->var2str($this->codigo)
                . "," . $this->var2str($this->activo)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idfpsri = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idfpsri = " . $this->var2str($this->idfpsri) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \formaspago_sri($p);
            }
        }

        return $list;
    }
}
