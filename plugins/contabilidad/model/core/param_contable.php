<?php

namespace GSC_Systems\model;

class param_contable extends \model
{
    public $idparam;
    public $idempresa;
    public $idejercicio;
    public $idimpuesto;
    public $idarticulo;
    public $idgrupo;
    public $idformapago;
    public $idcliente;
    public $idproveedor;
    public $idsubcivaventas;
    public $idsubcivacompras;
    public $idsubcivanotasventa;
    public $idsubccompras;
    public $idsubcventas;
    public $idsubccostos;
    public $idsubcntventas;
    public $idsubcntcostos;
    public $idsubcgrcompras;
    public $idsubcgrventas;
    public $idsubcgrcostos;
    public $idsubcgrntventas;
    public $idsubcgrntcostos;
    public $idsubcformapago;
    public $idsubccliente;
    public $idsubcantcliente;
    public $idsubcntcliente;
    public $idsubcproveedor;
    public $idsubcantproveedor;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($data = false)
    {
        parent::__construct('param_contable');
        if ($data) {
            $this->idparam     = $data['idparam'];
            $this->idempresa   = $data['idempresa'];
            $this->idejercicio = $data['idejercicio'];
            $this->idimpuesto  = $data['idimpuesto'];
            $this->idarticulo  = $data['idarticulo'];
            $this->idgrupo     = $data['idgrupo'];
            $this->idformapago = $data['idformapago'];
            $this->idcliente   = $data['idcliente'];
            $this->idproveedor = $data['idproveedor'];
            //Cuentas de los impuestos
            $this->idsubcivacompras    = $data['idsubcivacompras'];
            $this->idsubcivaventas     = $data['idsubcivaventas'];
            $this->idsubcivanotasventa = $data['idsubcivanotasventa'];
            //Cuentas de los articulos y servicios
            $this->idsubccompras  = $data['idsubccompras'];
            $this->idsubcventas   = $data['idsubcventas'];
            $this->idsubccostos   = $data['idsubccostos'];
            $this->idsubcntventas = $data['idsubcntventas'];
            $this->idsubcntcostos = $data['idsubcntcostos'];
            //Cuentas de los grupis
            $this->idsubcgrcompras  = $data['idsubcgrcompras'];
            $this->idsubcgrventas   = $data['idsubcgrventas'];
            $this->idsubcgrcostos   = $data['idsubcgrcostos'];
            $this->idsubcgrntventas = $data['idsubcgrntventas'];
            $this->idsubcgrntcostos = $data['idsubcgrntcostos'];
            //Cuentas de las Formas de Pago
            $this->idsubcformapago = $data['idsubcformapago'];
            //Cuentas de Clientes
            $this->idsubccliente    = $data['idsubccliente'];
            $this->idsubcantcliente = $data['idsubcantcliente'];
            $this->idsubcntcliente  = $data['idsubcntcliente'];
            //Cuentas de Proveedores
            $this->idsubcproveedor    = $data['idsubcproveedor'];
            $this->idsubcantproveedor = $data['idsubcantproveedor'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];
        } else {
            $this->idparam     = null;
            $this->idempresa   = null;
            $this->idejercicio = null;
            $this->idimpuesto  = null;
            $this->idarticulo  = null;
            $this->idgrupo     = null;
            $this->idformapago = null;
            $this->idcliente   = null;
            $this->idproveedor = null;
            //Cuentas de los impuestos
            $this->idsubcivacompras    = null;
            $this->idsubcivaventas     = null;
            $this->idsubcivanotasventa = null;
            //Cuentas de los articulos y servicios
            $this->idsubccompras  = null;
            $this->idsubcventas   = null;
            $this->idsubccostos   = null;
            $this->idsubcntventas = null;
            $this->idsubcntcostos = null;
            //Cuentas de los grupis
            $this->idsubcgrcompras  = null;
            $this->idsubcgrventas   = null;
            $this->idsubcgrcostos   = null;
            $this->idsubcgrntventas = null;
            $this->idsubcgrntcostos = null;
            //Cuentas de las Formas de Pago
            $this->idsubcformapago = null;
            //Cuentas de Clientes
            $this->idsubccliente    = null;
            $this->idsubcantcliente = null;
            $this->idsubcntcliente  = null;
            //Cuentas de Proveedores
            $this->idsubcproveedor    = null;
            $this->idsubcantproveedor = null;
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
        new \impuestos();
        new \articulos();
        new \grupos();
        new \formaspago();
        new \clientes();
        new \proveedores();
        new \plancuentas();

        return "";
    }

    public function url()
    {
        return '';
    }

    public function get($idparam)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idparam = " . $this->var2str($idparam) . ";");
        if ($data) {
            return new \param_contable($data[0]);
        }

        return false;
    }

    public function getByImpuestos($idimpuesto, $idejercicio)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idejercicio = " . $this->var2str($idejercicio) . " AND idimpuesto = " . $this->var2str($idimpuesto) . ";");
        if ($data) {
            return new \param_contable($data[0]);
        }

        return false;
    }

    public function getByArticulo($idarticulo, $idejercicio)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idejercicio = " . $this->var2str($idejercicio) . " AND idarticulo = " . $this->var2str($idarticulo) . ";");
        if ($data) {
            return new \param_contable($data[0]);
        }

        return false;
    }

    public function getByFamilia($idgrupo, $idejercicio)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idejercicio = " . $this->var2str($idejercicio) . " AND idgrupo = " . $this->var2str($idgrupo) . ";");
        if ($data) {
            return new \param_contable($data[0]);
        }

        return false;
    }

    public function getByFormaPago($idformapago, $idejercicio)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idejercicio = " . $this->var2str($idejercicio) . " AND idformapago = " . $this->var2str($idformapago) . ";");
        if ($data) {
            return new \param_contable($data[0]);
        }

        return false;
    }

    public function getByCliente($idcliente, $idejercicio)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idejercicio = " . $this->var2str($idejercicio) . " AND idcliente = " . $this->var2str($idcliente) . ";");
        if ($data) {
            return new \param_contable($data[0]);
        }

        return false;
    }

    public function getByProveedor($idproveedor, $idejercicio)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idejercicio = " . $this->var2str($idejercicio) . " AND idproveedor = " . $this->var2str($idproveedor) . ";");
        if ($data) {
            return new \param_contable($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idparam)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idparam = " . $this->var2str($this->idparam) . ";");
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
                . ", idejercicio = " . $this->var2str($this->idejercicio)
                . ", idimpuesto = " . $this->var2str($this->idimpuesto)
                . ", idarticulo = " . $this->var2str($this->idarticulo)
                . ", idgrupo = " . $this->var2str($this->idgrupo)
                . ", idformapago = " . $this->var2str($this->idformapago)
                . ", idcliente = " . $this->var2str($this->idcliente)
                . ", idproveedor = " . $this->var2str($this->idproveedor)
                . ", idsubcivaventas = " . $this->var2str($this->idsubcivaventas)
                . ", idsubcivacompras = " . $this->var2str($this->idsubcivacompras)
                . ", idsubcivanotasventa = " . $this->var2str($this->idsubcivanotasventa)
                . ", idsubccompras = " . $this->var2str($this->idsubccompras)
                . ", idsubcventas = " . $this->var2str($this->idsubcventas)
                . ", idsubccostos = " . $this->var2str($this->idsubccostos)
                . ", idsubcntventas = " . $this->var2str($this->idsubcntventas)
                . ", idsubcntcostos = " . $this->var2str($this->idsubcntcostos)
                . ", idsubcgrcompras = " . $this->var2str($this->idsubcgrcompras)
                . ", idsubcgrventas = " . $this->var2str($this->idsubcgrventas)
                . ", idsubcgrcostos = " . $this->var2str($this->idsubcgrcostos)
                . ", idsubcgrntventas = " . $this->var2str($this->idsubcgrntventas)
                . ", idsubcgrntcostos = " . $this->var2str($this->idsubcgrntcostos)
                . ", idsubcformapago = " . $this->var2str($this->idsubcformapago)
                . ", idsubccliente = " . $this->var2str($this->idsubccliente)
                . ", idsubcantcliente = " . $this->var2str($this->idsubcantcliente)
                . ", idsubcntcliente = " . $this->var2str($this->idsubcntcliente)
                . ", idsubcproveedor = " . $this->var2str($this->idsubcproveedor)
                . ", idsubcantproveedor = " . $this->var2str($this->idsubcantproveedor)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idparam = " . $this->var2str($this->idparam) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idejercicio, idimpuesto, idarticulo, idgrupo, idformapago, idcliente, idproveedor, idsubcivaventas, idsubcivacompras, idsubcivanotasventa, idsubccompras, idsubcventas, idsubccostos, idsubcntventas, idsubcntcostos, idsubcgrcompras, idsubcgrventas, idsubcgrcostos, idsubcgrntventas, idsubcgrntcostos, idsubcformapago, idsubccliente, idsubcantcliente, idsubcntcliente, idsubcproveedor, idsubcantproveedor, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idejercicio)
                . "," . $this->var2str($this->idimpuesto)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->idgrupo)
                . "," . $this->var2str($this->idformapago)
                . "," . $this->var2str($this->idcliente)
                . "," . $this->var2str($this->idproveedor)
                . "," . $this->var2str($this->idsubcivaventas)
                . "," . $this->var2str($this->idsubcivacompras)
                . "," . $this->var2str($this->idsubcivanotasventa)
                . "," . $this->var2str($this->idsubccompras)
                . "," . $this->var2str($this->idsubcventas)
                . "," . $this->var2str($this->idsubccostos)
                . "," . $this->var2str($this->idsubcntventas)
                . "," . $this->var2str($this->idsubcntcostos)
                . "," . $this->var2str($this->idsubcgrcompras)
                . "," . $this->var2str($this->idsubcgrventas)
                . "," . $this->var2str($this->idsubcgrcostos)
                . "," . $this->var2str($this->idsubcgrntventas)
                . "," . $this->var2str($this->idsubcgrntcostos)
                . "," . $this->var2str($this->idsubcformapago)
                . "," . $this->var2str($this->idsubccliente)
                . "," . $this->var2str($this->idsubcantcliente)
                . "," . $this->var2str($this->idsubcntcliente)
                . "," . $this->var2str($this->idsubcproveedor)
                . "," . $this->var2str($this->idsubcantproveedor)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idparam = $this->db->lastval();
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
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idparam = " . $this->var2str($this->idparam) . ";");
    }

    private function beforeDelete()
    {
        return true;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idimpuesto DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \param_contable($p);
            }
        }

        return $list;
    }
}
