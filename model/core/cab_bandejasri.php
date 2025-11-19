<?php

namespace GSC_Systems\model;

class cab_bandejasri extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('cab_bandejasri');
        if ($data) {
            $this->idbandejasri         = $data['idbandejasri'];
            $this->idempresa            = $data['idempresa'];
            $this->estado               = $data['estado'];
            $this->idproveedor          = $data['idproveedor'];
            $this->idcliente            = $data['idcliente'];
            $this->coddocumento         = $data['coddocumento'];
            $this->iddocumento          = $data['iddocumento'];
            $this->tipoid               = $data['tipoid'];
            $this->identificacion       = $data['identificacion'];
            $this->razonsocial          = $data['razonsocial'];
            $this->nombrecomercial      = $data['nombrecomercial'];
            $this->email                = $data['email'];
            $this->direccion            = $data['direccion'];
            $this->numero_documento     = $data['numero_documento'];
            $this->nro_autorizacion     = $data['nro_autorizacion'];
            $this->fec_autorizacion     = $data['fec_autorizacion'] ? Date('d-m-Y', strtotime($data['fec_autorizacion'])) : null;
            $this->hor_autorizacion     = $data['hor_autorizacion'] ? Date('H:i:s', strtotime($data['hor_autorizacion'])) : null;
            $this->diascredito          = $data['diascredito'];
            $this->fec_emision          = $data['fec_emision'] ? Date('d-m-Y', strtotime($data['fec_emision'])) : null;
            $this->hora_emision         = $data['hora_emision'] ? Date('H:i:s', strtotime($data['hora_emision'])) : null;
            $this->fec_caducidad        = $data['fec_caducidad'] ? Date('d-m-Y', strtotime($data['fec_caducidad'])) : null;
            $this->fec_registro         = $data['fec_registro'] ? Date('d-m-Y', strtotime($data['fec_registro'])) : null;
            $this->base_noi             = floatval($data['base_noi']);
            $this->base_0               = floatval($data['base_0']);
            $this->base_gra             = floatval($data['base_gra']);
            $this->base_exc             = floatval($data['base_exc']);
            $this->totaldescuento       = floatval($data['totaldescuento']);
            $this->totalice             = floatval($data['totalice']);
            $this->totaliva             = floatval($data['totaliva']);
            $this->totalirbp            = floatval($data['totalirbp']);
            $this->total                = floatval($data['total']);
            $this->idfactura            = $data['idfactura'];
            $this->idretencion          = $data['idretencion'];
            $this->idfactura_mod        = $data['idfactura_mod'];
            $this->iddocumento_mod      = $data['iddocumento_mod'];
            $this->coddocumento_mod     = $data['coddocumento_mod'];
            $this->numero_documento_mod = $data['numero_documento_mod'];
            $this->nro_autorizacion_mod = $data['nro_autorizacion_mod'];
            $this->fecdoc_modificado    = $data['fecdoc_modificado'] ? Date('d-m-Y', strtotime($data['fecdoc_modificado'])) : null;
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idbandejasri = null;
            $this->idempresa    = null;
            //0: Pendiente
            //1: No Procesado
            //2: Procesado
            $this->estado               = 0;
            $this->idproveedor          = null;
            $this->idcliente            = null;
            $this->coddocumento         = null;
            $this->iddocumento          = null;
            $this->tipoid               = 'R';
            $this->identificacion       = null;
            $this->razonsocial          = null;
            $this->nombrecomercial      = null;
            $this->email                = null;
            $this->direccion            = null;
            $this->numero_documento     = null;
            $this->nro_autorizacion     = null;
            $this->fec_autorizacion     = null;
            $this->hor_autorizacion     = null;
            $this->diascredito          = 0;
            $this->fec_emision          = null;
            $this->hora_emision         = null;
            $this->fec_caducidad        = null;
            $this->fec_registro         = null;
            $this->base_noi             = 0;
            $this->base_0               = 0;
            $this->base_gra             = 0;
            $this->base_exc             = 0;
            $this->totaldescuento       = 0;
            $this->totalice             = 0;
            $this->totaliva             = 0;
            $this->totalirbp            = 0;
            $this->total                = 0;
            $this->idfactura            = null;
            $this->idretencion          = null;
            $this->idfactura_mod        = null;
            $this->iddocumento_mod      = null;
            $this->coddocumento_mod     = '01';
            $this->numero_documento_mod = null;
            $this->nro_autorizacion_mod = null;
            $this->fecdoc_modificado    = null;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;
        }
    }

    public function install()
    {
        new \documentos();
        new \empresa();
        new \proveedores();
        new \clientes();

        return "";
    }

    public function url()
    {
        if (is_null($this->idbandejasri)) {
            return 'index.php?page=bandeja_sri';
        }

        return 'index.php?page=procesar_bandejasri&id=' . $this->idbandejasri;
    }

    public function get($idbandejasri)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idbandejasri = " . $this->var2str($idbandejasri) . ";");
        if ($data) {
            return new \cab_bandejasri($data[0]);
        }

        return false;
    }

    public function get_tipodoc()
    {
        if ($this->coddocumento == '07') {
            return 'Retención';
        }

        $documento = new \documentos();
        $doc       = $documento->get($this->iddocumento);
        if ($doc) {
            return $doc->nombre;
        }

        return '-';
    }

    public function getlineas()
    {
        $lineas = new \lineasbandejasri();
        return $lineas->all_by_idbandejasri($this->idbandejasri);
    }

    public function getlineasret()
    {
        $lineas = new \lineasretbandejasri();
        return $lineas->all_by_idbandejasri($this->idbandejasri);
    }

    public function exists()
    {
        if (is_null($this->idbandejasri)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idbandejasri = " . $this->var2str($this->idbandejasri) . ";");
    }

    public function test()
    {
        $status = true;

        if (!$this->idbandejasri) {
            //Si es nuevo valido que el numero de autorizacion no exista
            $sql  = 'SELECT * FROM ' . $this->table_name . ' WHERE nro_autorizacion = ' . $this->var2str($this->nro_autorizacion) . ' AND idempresa = ' . $this->var2str($this->idempresa);
            $data = $this->db->select($sql);
            if ($data) {
                $this->new_error_msg("El documento: " . $this->numero_documento . " de: " . $this->razonsocial . " ya se encuentra registrado.");
                return false;
            }
        }
        if ($this->coddocumento != '07') {
            $this->total = round($this->base_noi + $this->base_0 + $this->base_gra + $this->base_exc + $this->totalice + $this->totaliva + $this->totalirbp, 2);
        }

        return $status;
    }

    public function save()
    {
        if ($this->test()) {
            $insert = false;
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET idempresa = " . $this->var2str($this->idempresa)
                . ", estado = " . $this->var2str($this->estado)
                . ", idproveedor = " . $this->var2str($this->idproveedor)
                . ", idcliente = " . $this->var2str($this->idcliente)
                . ", iddocumento = " . $this->var2str($this->iddocumento)
                . ", coddocumento = " . $this->var2str($this->coddocumento)
                . ", tipoid = " . $this->var2str($this->tipoid)
                . ", identificacion = " . $this->var2str($this->identificacion)
                . ", razonsocial = " . $this->var2str($this->razonsocial)
                . ", nombrecomercial = " . $this->var2str($this->nombrecomercial)
                . ", email = " . $this->var2str($this->email)
                . ", direccion = " . $this->var2str($this->direccion)
                . ", numero_documento = " . $this->var2str($this->numero_documento)
                . ", nro_autorizacion = " . $this->var2str($this->nro_autorizacion)
                . ", fec_autorizacion = " . $this->var2str($this->fec_autorizacion)
                . ", hor_autorizacion = " . $this->var2str($this->hor_autorizacion)
                . ", diascredito = " . $this->var2str($this->diascredito)
                . ", fec_emision = " . $this->var2str($this->fec_emision)
                . ", hora_emision = " . $this->var2str($this->hora_emision)
                . ", fec_caducidad = " . $this->var2str($this->fec_caducidad)
                . ", fec_registro = " . $this->var2str($this->fec_registro)
                . ", base_noi = " . $this->var2str($this->base_noi)
                . ", base_0 = " . $this->var2str($this->base_0)
                . ", base_gra = " . $this->var2str($this->base_gra)
                . ", base_exc = " . $this->var2str($this->base_exc)
                . ", totaldescuento = " . $this->var2str($this->totaldescuento)
                . ", totalice = " . $this->var2str($this->totalice)
                . ", totaliva = " . $this->var2str($this->totaliva)
                . ", totalirbp = " . $this->var2str($this->totalirbp)
                . ", total = " . $this->var2str($this->total)
                . ", idfactura = " . $this->var2str($this->idfactura)
                . ", idretencion = " . $this->var2str($this->idretencion)
                . ", idfactura_mod = " . $this->var2str($this->idfactura_mod)
                . ", iddocumento_mod = " . $this->var2str($this->iddocumento_mod)
                . ", coddocumento_mod = " . $this->var2str($this->coddocumento_mod)
                . ", numero_documento_mod = " . $this->var2str($this->numero_documento_mod)
                . ", nro_autorizacion_mod = " . $this->var2str($this->nro_autorizacion_mod)
                . ", fecdoc_modificado = " . $this->var2str($this->fecdoc_modificado)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idbandejasri = " . $this->var2str($this->idbandejasri) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, estado, idproveedor, idcliente, iddocumento, coddocumento, tipoid, identificacion, razonsocial, nombrecomercial, email, direccion, numero_documento, nro_autorizacion, fec_autorizacion, hor_autorizacion, diascredito, fec_emision, hora_emision, fec_caducidad, fec_registro, base_noi, base_0, base_gra, base_exc, totaldescuento, totalice, totaliva, totalirbp, total, idfactura, idretencion, idfactura_mod, iddocumento_mod, coddocumento_mod, numero_documento_mod, nro_autorizacion_mod, fecdoc_modificado, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->estado)
                . "," . $this->var2str($this->idproveedor)
                . "," . $this->var2str($this->idcliente)
                . "," . $this->var2str($this->iddocumento)
                . "," . $this->var2str($this->coddocumento)
                . "," . $this->var2str($this->tipoid)
                . "," . $this->var2str($this->identificacion)
                . "," . $this->var2str($this->razonsocial)
                . "," . $this->var2str($this->nombrecomercial)
                . "," . $this->var2str($this->email)
                . "," . $this->var2str($this->direccion)
                . "," . $this->var2str($this->numero_documento)
                . "," . $this->var2str($this->nro_autorizacion)
                . "," . $this->var2str($this->fec_autorizacion)
                . "," . $this->var2str($this->hor_autorizacion)
                . "," . $this->var2str($this->diascredito)
                . "," . $this->var2str($this->fec_emision)
                . "," . $this->var2str($this->hora_emision)
                . "," . $this->var2str($this->fec_caducidad)
                . "," . $this->var2str($this->fec_registro)
                . "," . $this->var2str($this->base_noi)
                . "," . $this->var2str($this->base_0)
                . "," . $this->var2str($this->base_gra)
                . "," . $this->var2str($this->base_exc)
                . "," . $this->var2str($this->totaldescuento)
                . "," . $this->var2str($this->totalice)
                . "," . $this->var2str($this->totaliva)
                . "," . $this->var2str($this->totalirbp)
                . "," . $this->var2str($this->total)
                . "," . $this->var2str($this->idfactura)
                . "," . $this->var2str($this->idretencion)
                . "," . $this->var2str($this->idfactura_mod)
                . "," . $this->var2str($this->iddocumento_mod)
                . "," . $this->var2str($this->coddocumento_mod)
                . "," . $this->var2str($this->numero_documento_mod)
                . "," . $this->var2str($this->nro_autorizacion_mod)
                . "," . $this->var2str($this->fecdoc_modificado)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->db->exec($sql)) {
                if ($insert) {
                    $this->idbandejasri = $this->db->lastval();
                }
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        if ($this->afterDelete()) {
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idbandejasri = " . $this->var2str($this->idbandejasri) . ";");
        }

        return false;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY razonsocial DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \cab_bandejasri($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY fec_emision DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \cab_bandejasri($p);
            }
        }

        return $list;
    }

    public function search_cab_bandejasri($idempresa = '', $query = '', $idproveedor = '', $idcliente = '', $fechadesde = '', $fechahasta = '', $iddocumento = '')
    {
        $list = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        if ($idempresa != '') {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }
        if ($idproveedor != '') {
            $sql .= " AND idproveedor = " . $this->var2str($idproveedor);
        }
        if ($idcliente != '') {
            $sql .= " AND idcliente = " . $this->var2str($idcliente);
        }
        if ($iddocumento != '') {
            $sql .= " AND iddocumento = " . $this->var2str($iddocumento);
        }
        if ($fechadesde != '') {
            $sql .= " AND fec_emision >= " . $this->var2str($fechadesde);
        }
        if ($fechahasta != '') {
            $sql .= " AND fec_emision <= " . $this->var2str($fechahasta);
        }
        if ($query != '') {
            $query = strtolower($query);
            $sql .= " AND (lower(razonsocial) LIKE '%" . $query . "%' OR lower(numero_documento) LIKE '%" . $query . "%' OR lower(nro_autorizacion) LIKE '%" . $query . "%')";
        }
        $sql .= " ORDER BY fec_emision DESC";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \cab_bandejasri($p);
            }
        }

        return $list;
    }

    public function get_by_idproveedor($idempresa, $idproveedor)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idproveedor = " . $this->var2str($idproveedor) . ";");
        if ($data) {
            return new \cab_bandejasri($data[0]);
        }

        return false;
    }

    private function afterDelete()
    {
        return true;
    }

    public function getNumDocs($idempresa, $query = '', $coddocumento = false, $fechadesde = '', $fechahasta = '', $estado = 0)
    {
        $sql = "SELECT count(idbandejasri) AS contador FROM " . $this->table_name . " WHERE estado = " . $this->var2str($estado) . " AND idempresa = " . $this->var2str($idempresa);
        if ($query != '') {
            $query = strtolower($query);
            $sql .= " AND (lower(identificacion) LIKE '%" . $query . "%' OR lower(razonsocial) LIKE '%" . $query . "%' OR lower(numero_documento) LIKE '%" . $query . "%' OR lower(nro_autorizacion) LIKE '%" . $query . "%')";
        }
        if ($coddocumento) {
            $sql .= " AND coddocumento = " . $this->var2str($coddocumento);
        }
        if ($fechadesde) {
            $sql .= " AND fec_emision >= " . $this->var2str($fechadesde);
        }
        if ($fechahasta) {
            $sql .= " AND fec_emision <= " . $this->var2str($fechahasta);
        }

        $data = $this->db->select($sql);
        if ($data) {
            return $data[0]['contador'];
        }

        return 0;
    }

    public function allDocs($idempresa, $query = '', $coddocumento = false, $fechadesde = '', $fechahasta = '', $estado = 0)
    {
        $list = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE estado = " . $this->var2str($estado) . " AND idempresa = " . $this->var2str($idempresa);
        if ($query != '') {
            $query = strtolower($query);
            $sql .= " AND (lower(identificacion) LIKE '%" . $query . "%' OR lower(razonsocial) LIKE '%" . $query . "%' OR lower(numero_documento) LIKE '%" . $query . "%' OR lower(nro_autorizacion) LIKE '%" . $query . "%')";
        }
        if ($coddocumento) {
            $sql .= " AND coddocumento = " . $this->var2str($coddocumento);
        }
        if ($fechadesde) {
            $sql .= " AND fec_emision >= " . $this->var2str($fechadesde);
        }
        if ($fechahasta) {
            $sql .= " AND fec_emision <= " . $this->var2str($fechahasta);
        }

        $sql .= " ORDER BY fec_emision DESC";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \cab_bandejasri($p);
            }
        }

        return $list;
    }

    public function getNroDocProvPendiente($idempresa, $identificacion, $numero_documento)
    {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE estado = " . $this->var2str(0) . " AND coddocumento = " . $this->var2str('01') . " AND idempresa = " . $this->var2str($idempresa) . " AND identificacion = " . $this->var2str($identificacion) . " AND numero_documento = " . $this->var2str($numero_documento);

        $data = $this->db->select($sql);
        if ($data) {
            return new \cab_bandejasri($data[0]);
        }
        return false;
    }

    public function actualizarProveedor($idempresa, $identificacion, $idproveedor)
    {
        $sql = "UPDATE ".$this->table_name." SET idproveedor = ".$this->var2str($idproveedor)." WHERE idproveedor IS NULL AND coddocumento != ".$this->var2str('07')." AND identificacion = ".$this->var2str($identificacion)." AND idempresa = ".$this->var2str($idempresa);

        return $this->db->exec($sql);
    }

    public function actualizarCliente($idempresa, $identificacion, $idcliente)
    {
        $sql = "UPDATE ".$this->table_name." SET idcliente = ".$this->var2str($idcliente)." WHERE idcliente IS NULL AND coddocumento = ".$this->var2str('07')." AND identificacion = ".$this->var2str($identificacion)." AND idempresa = ".$this->var2str($idempresa);

        return $this->db->exec($sql);
    }
}
