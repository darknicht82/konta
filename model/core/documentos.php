<?php

namespace GSC_Systems\model;

class documentos extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('documentos');
        if ($data) {

            $this->iddocumento = $data['iddocumento'];
            $this->nombre     = $data['nombre'];
            $this->codigo     = $data['codigo'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->iddocumento = null;
            $this->nombre     = null;
            $this->codigo     = null;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;
        }
    }

    public function install()
    {
        $sql = "INSERT INTO ".$this->table_name."(nombre, codigo, fec_creacion, nick_creacion) VALUES
            ('Factura', '01', '".date('Y-m-d')."', 'admin'),
            ('Nota de Venta', '02', '".date('Y-m-d')."', 'admin'),
            ('Liquidacion de compra de Bienes o Prestacion de servicios', '03', '".date('Y-m-d')."', 'admin'),
            ('Nota de credito', '04', '".date('Y-m-d')."', 'admin'),
            ('Nota de debito', '05', '".date('Y-m-d')."', 'admin'),
            ('Tiquetes o vales emitidos por maquinas registradoras', '09', '".date('Y-m-d')."', 'admin'),
            ('Pasajes expedidos por empresas de aviacion', '11', '".date('Y-m-d')."', 'admin'),
            ('Documentos emitidos por instituciones financieras', '12', '".date('Y-m-d')."', 'admin'),
            ('Comprobante de venta emitido en el Exterior', '15', '".date('Y-m-d')."', 'admin'),
            ('Comprobantes de Pago de Cuotas o Aportes', '19', '".date('Y-m-d')."', 'admin'),
            ('Documentos por Servicios Administrativos emitidos por Inst. del Estado', '20', '".date('Y-m-d')."', 'admin'),
            ('Carta de Porte Aereo', '21', '".date('Y-m-d')."', 'admin'),
            ('Comprobante de venta emitido por reembolso', '41', '".date('Y-m-d')."', 'admin'),
            ('Documento retencion presuntiva y retencion emitida por propio vendedor o por intermediario', '42', '".date('Y-m-d')."', 'admin'),
            ('Liquidacion para Explotacion y Exploracion de Hidrocarburos', '43', '".date('Y-m-d')."', 'admin'),
            ('Liquidacion por reclamos de aseguradoras', '45', '".date('Y-m-d')."', 'admin'),
            ('Nota de Credito por Reembolso Emitida por Intermediario', '47', '".date('Y-m-d')."', 'admin'),
            ('Nota de Debito por Reembolso Emitida por Intermediario', '48', '".date('Y-m-d')."', 'admin'),
            ('Liquidacion de compra de Bienes Muebles Usados', '294', '".date('Y-m-d')."', 'admin'),
            ('Liquidacion de compra de vehiculos usados', '344', '".date('Y-m-d')."', 'admin'),
            ('Acta Entrega-Recepcion PET', '364', '".date('Y-m-d')."', 'admin'),
            ('Liquidacion de compra RISE de bienes o prestacion de servicios', '375', '".date('Y-m-d')."', 'admin');";
        return $sql;
    }

    public function url()
    {
        return 'index.php?page=lista_documentos';
    }

    public function get($iddocumento)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE iddocumento = " . $this->var2str($iddocumento) . ";");
        if ($data) {
            return new \documentos($data[0]);
        }

        return false;
    }

    public function get_by_codigo($codigo)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codigo = " . $this->var2str($codigo) . ";");
        if ($data) {
            return new \documentos($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->iddocumento)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE iddocumento = " . $this->var2str($this->iddocumento) . ";");
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
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE iddocumento = " . $this->var2str($this->iddocumento) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (nombre, codigo, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->nombre)
                . "," . $this->var2str($this->codigo)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->iddocumento = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE iddocumento = " . $this->var2str($this->iddocumento) . ";");
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY codigo::int ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \documentos($p);
            }
        }

        return $list;
    }

    public function all_desde_sustento($iddocumentos)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE iddocumento IN (".$iddocumentos.") ORDER BY codigo::int ASC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \documentos($p);
            }
        }

        return $list;
    }
}