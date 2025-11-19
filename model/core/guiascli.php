<?php

namespace GSC_Systems\model;

class guiascli extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('guiascli');
        if ($data) {
            $this->idguiacli           = $data['idguiacli'];
            $this->idempresa           = $data['idempresa'];
            $this->idestablecimiento   = $data['idestablecimiento'];
            $this->idcliente           = $data['idcliente'];
            $this->coddocumento        = $data['coddocumento'];
            $this->tipoemision         = $data['tipoemision'];
            $this->tipoid              = $data['tipoid'];
            $this->identificacion      = $data['identificacion'];
            $this->razonsocial         = $data['razonsocial'];
            $this->email               = $data['email'];
            $this->direccion           = $data['direccion'];
            $this->regimen_empresa     = $data['regimen_empresa'];
            $this->obligado_empresa    = $this->str2bool($data['obligado_empresa']);
            $this->agretencion_empresa = $this->str2bool($data['agretencion_empresa']);
            $this->numero_documento    = $data['numero_documento'];
            $this->nro_autorizacion    = $data['nro_autorizacion'];
            $this->estado_sri          = $data['estado_sri'];
            $this->fec_autorizacion    = $data['fec_autorizacion'] ? Date('d-m-Y', strtotime($data['fec_autorizacion'])) : null;
            $this->hor_autorizacion    = $data['hor_autorizacion'] ? Date('H:i:s', strtotime($data['hor_autorizacion'])) : null;
            $this->fec_emision         = $data['fec_emision'] ? Date('d-m-Y', strtotime($data['fec_emision'])) : null;
            $this->hora_emision        = $data['hora_emision'] ? Date('H:i:s', strtotime($data['hora_emision'])) : null;
            $this->base_noi            = floatval($data['base_noi']);
            $this->base_0              = floatval($data['base_0']);
            $this->base_gra            = floatval($data['base_gra']);
            $this->base_exc            = floatval($data['base_exc']);
            $this->totaldescuento      = floatval($data['totaldescuento']);
            $this->totalice            = floatval($data['totalice']);
            $this->totaliva            = floatval($data['totaliva']);
            $this->total               = floatval($data['total']);
            $this->fec_registro        = $data['fec_registro'] ? Date('d-m-Y', strtotime($data['fec_registro'])) : null;
            $this->observaciones       = $data['observaciones'];
            //Datos de Transporte
            $this->dirpartida           = $data['dirpartida'];
            $this->razonsocial_trans    = $data['razonsocial_trans'];
            $this->tipoid_trans         = $data['tipoid_trans'];
            $this->identificacion_trans = $data['identificacion_trans'];
            $this->fec_finalizacion     = $data['fec_finalizacion'] ? Date('d-m-Y', strtotime($data['fec_finalizacion'])) : null;
            $this->placa                = $data['placa'];
            $this->motivo               = $data['motivo'];
            $this->codestablecimiento   = $data['codestablecimiento'];
            $this->ruta                 = $data['ruta'];
            $this->tipo_guia            = $data['tipo_guia'];
            //Datos de Factura Modificada
            $this->idfactura_mod        = $data['idfactura_mod'];
            $this->iddocumento_mod      = $data['iddocumento_mod'];
            $this->coddocumento_mod     = $data['coddocumento_mod'];
            $this->numero_documento_mod = $data['numero_documento_mod'];
            $this->nro_autorizacion_mod = $data['nro_autorizacion_mod'];
            $this->fec_emision_mod      = $data['fec_emision_mod'] ? Date('d-m-Y', strtotime($data['fec_emision_mod'])) : null;
            $this->anulado              = $this->str2bool($data['anulado']);
            $this->idejercicio          = $data['idejercicio'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idguiacli           = null;
            $this->idempresa           = null;
            $this->idestablecimiento   = null;
            $this->idcliente           = null;
            $this->coddocumento        = '06';
            $this->tipoemision         = 'F';
            $this->tipoid              = null;
            $this->identificacion      = null;
            $this->razonsocial         = null;
            $this->email               = null;
            $this->direccion           = null;
            $this->regimen_empresa     = null;
            $this->obligado_empresa    = 0;
            $this->agretencion_empresa = 0;
            $this->numero_documento    = null;
            $this->nro_autorizacion    = null;
            $this->estado_sri          = 'PENDIENTE';
            $this->fec_autorizacion    = null;
            $this->hor_autorizacion    = null;
            $this->fec_emision         = null;
            $this->hora_emision        = null;
            $this->fec_registro        = null;
            $this->base_noi            = 0;
            $this->base_0              = 0;
            $this->base_gra            = 0;
            $this->base_exc            = 0;
            $this->totaldescuento      = 0;
            $this->totalice            = 0;
            $this->totaliva            = 0;
            $this->total               = 0;
            $this->observaciones       = null;
            //Datos de Transporte
            $this->dirpartida           = null;
            $this->razonsocial_trans    = null;
            $this->tipoid_trans         = null;
            $this->identificacion_trans = null;
            $this->fec_finalizacion     = null;
            $this->placa                = null;
            $this->motivo               = null;
            $this->codestablecimiento   = null;
            $this->ruta                 = null;
            $this->tipo_guia            = 1;
            //Datos de Factura Modificada
            $this->idfactura_mod        = null;
            $this->iddocumento_mod      = null;
            $this->coddocumento_mod     = '01';
            $this->numero_documento_mod = null;
            $this->nro_autorizacion_mod = null;
            $this->fec_emision_mod      = null;
            $this->anulado              = false;
            $this->idejercicio          = null;
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
        new \establecimiento();
        return "";
    }

    public function url()
    {
        if (is_null($this->idguiacli)) {
            return 'index.php?page=lista_guias_remision';
        }

        return 'index.php?page=ver_guia_remision&id=' . $this->idguiacli;
    }

    public function get($idguiacli)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idguiacli = " . $this->var2str($idguiacli) . ";");
        if ($data) {
            return new \guiascli($data[0]);
        }

        return false;
    }

    public function get_regimen_empresa()
    {
        $regimen = '';
        foreach (regimen() as $key => $value) {
            if ($this->regimen_empresa == $key && $key != 'CE') {
                $regimen = $value;
            }
        }
        return $regimen;
    }

    public function getlineas()
    {
        $lineas = new \lineasguiascli();
        return $lineas->all_by_idguiacli($this->idguiacli);
    }

    public function get_establecimiento()
    {
        if ($this->idestablecimiento) {
            $est0 = new \establecimiento();
            $est  = $est0->get($this->idestablecimiento);
            if ($est) {
                return $est;
            }
        }
        return false;
    }

    public function get_empresa()
    {
        if ($this->idempresa) {
            $emp0 = new \empresa();
            $emp  = $emp0->get($this->idempresa);
            if ($emp) {
                return $emp;
            }
        }
        return false;
    }

    public function exists()
    {
        if (is_null($this->idguiacli)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idguiacli = " . $this->var2str($this->idguiacli) . ";");
    }

    public function test()
    {
        $status = true;
        //Valido si tiene acceso a crear nuevos documentos
        if (complemento_exists('facturador')) {
            if (!$this->idguiacli) {
                $empresa = $this->get_empresa();
                if ($empresa) {
                    if (!$empresa->plan_activo(true)) {
                        return false;
                    }
                }
            }
        }

        $this->total = $this->base_noi + $this->base_0 + $this->base_gra + $this->base_exc + $this->totalice + $this->totaliva;
        if (!$this->numero_documento) {
            if (!$this->generar_numero_documento()) {
                $status = false;
            }
        }

        if (complemento_exists('contabilidad')) {
            //si existe el plugin de contabilidad se debe buscar el ejercicio para generarlo
            if (!$this->idejercicio) {
                $ejer0     = new \ejercicios();
                $ejercicio = $ejer0->get_by_fecha($this->idempresa, $this->fec_emision);
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

    private function generar_numero_documento()
    {
        if ($est = $this->get_establecimiento()) {
            $establecimiento  = str_pad($est->codigo, 3, "0", STR_PAD_LEFT);
            $ptoemision       = str_pad($est->ptoemision, 3, "0", STR_PAD_LEFT);
            $numguia          = str_pad($est->numguia, 9, "0", STR_PAD_LEFT);
            $numero_documento = $establecimiento . "-" . $ptoemision . "-" . $numguia;
            //pongo el numero de factura
            $est->numguia++;
            $est->save();
            $nro_autorizacion = $this->generarNumeroAut($numero_documento);
            if ($this->validar_numero($numero_documento)) {
                $this->generar_numero_documento();
            } else {
                $this->numero_documento = $numero_documento;
                $this->nro_autorizacion = $nro_autorizacion;
                return true;
            }
        }
        return false;
    }

    private function generarNumeroAut($numero_documento)
    {
        if ($emp = $this->get_empresa()) {
            //Fecha
            $fechadoc = str_replace("-", "", date('d-m-Y', strtotime($this->fec_emision)));
            //Tipo de Comprobante
            $tipoComp = $this->coddocumento;
            //Ruc Empresa
            $rucEmp = $emp->ruc;
            //Ambiente Pruebas o Produccion
            if ($emp->produccion) {
                $ambiente = '2';
            } else {
                $ambiente = '1';
            }
            //Numero de Documento
            $numDoc = str_replace("-", "", $numero_documento);
            return generarClaveAcceso($fechadoc, $tipoComp, $rucEmp, $ambiente, $numDoc);
        }
        return '';
    }

    private function validar_numero($numero_documento)
    {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE numero_documento = " . $this->var2str($numero_documento) . " AND idempresa = " . $this->var2str($this->idempresa);
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
                . ", idestablecimiento = " . $this->var2str($this->idestablecimiento)
                . ", idcliente = " . $this->var2str($this->idcliente)
                . ", coddocumento = " . $this->var2str($this->coddocumento)
                . ", tipoemision = " . $this->var2str($this->tipoemision)
                . ", tipoid = " . $this->var2str($this->tipoid)
                . ", identificacion = " . $this->var2str($this->identificacion)
                . ", razonsocial = " . $this->var2str($this->razonsocial)
                . ", email = " . $this->var2str($this->email)
                . ", direccion = " . $this->var2str($this->direccion)
                . ", regimen_empresa = " . $this->var2str($this->regimen_empresa)
                . ", obligado_empresa = " . $this->var2str($this->obligado_empresa)
                . ", agretencion_empresa = " . $this->var2str($this->agretencion_empresa)
                . ", numero_documento = " . $this->var2str($this->numero_documento)
                . ", nro_autorizacion = " . $this->var2str($this->nro_autorizacion)
                . ", estado_sri = " . $this->var2str($this->estado_sri)
                . ", fec_autorizacion = " . $this->var2str($this->fec_autorizacion)
                . ", hor_autorizacion = " . $this->var2str($this->hor_autorizacion)
                . ", fec_emision = " . $this->var2str($this->fec_emision)
                . ", hora_emision = " . $this->var2str($this->hora_emision)
                . ", fec_registro = " . $this->var2str($this->fec_registro)
                . ", base_noi = " . $this->var2str($this->base_noi)
                . ", base_0 = " . $this->var2str($this->base_0)
                . ", base_gra = " . $this->var2str($this->base_gra)
                . ", base_exc = " . $this->var2str($this->base_exc)
                . ", totaldescuento = " . $this->var2str($this->totaldescuento)
                . ", totalice = " . $this->var2str($this->totalice)
                . ", totaliva = " . $this->var2str($this->totaliva)
                . ", total = " . $this->var2str($this->total)
                . ", observaciones = " . $this->var2str($this->observaciones)
                . ", dirpartida = " . $this->var2str($this->dirpartida)
                . ", razonsocial_trans = " . $this->var2str($this->razonsocial_trans)
                . ", tipoid_trans = " . $this->var2str($this->tipoid_trans)
                . ", identificacion_trans = " . $this->var2str($this->identificacion_trans)
                . ", fec_finalizacion = " . $this->var2str($this->fec_finalizacion)
                . ", placa = " . $this->var2str($this->placa)
                . ", motivo = " . $this->var2str($this->motivo)
                . ", codestablecimiento = " . $this->var2str($this->codestablecimiento)
                . ", ruta = " . $this->var2str($this->ruta)
                . ", tipo_guia = " . $this->var2str($this->tipo_guia)
                . ", idfactura_mod = " . $this->var2str($this->idfactura_mod)
                . ", iddocumento_mod = " . $this->var2str($this->iddocumento_mod)
                . ", coddocumento_mod = " . $this->var2str($this->coddocumento_mod)
                . ", numero_documento_mod = " . $this->var2str($this->numero_documento_mod)
                . ", nro_autorizacion_mod = " . $this->var2str($this->nro_autorizacion_mod)
                . ", fec_emision_mod = " . $this->var2str($this->fec_emision_mod)
                . ", anulado = " . $this->var2str($this->anulado)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idguiacli = " . $this->var2str($this->idguiacli) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idestablecimiento, idcliente, coddocumento, tipoemision, tipoid, identificacion, razonsocial, email, direccion, regimen_empresa, obligado_empresa, agretencion_empresa, numero_documento, nro_autorizacion, estado_sri, fec_autorizacion, hor_autorizacion, fec_emision, hora_emision, fec_registro, base_noi, base_0, base_gra, base_exc, totaldescuento, totalice, totaliva, total, observaciones, dirpartida, razonsocial_trans, tipoid_trans, identificacion_trans, fec_finalizacion, placa, motivo, codestablecimiento, ruta, tipo_guia, idfactura_mod, iddocumento_mod, coddocumento_mod, numero_documento_mod, nro_autorizacion_mod, fec_emision_mod, anulado, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idestablecimiento)
                . "," . $this->var2str($this->idcliente)
                . "," . $this->var2str($this->coddocumento)
                . "," . $this->var2str($this->tipoemision)
                . "," . $this->var2str($this->tipoid)
                . "," . $this->var2str($this->identificacion)
                . "," . $this->var2str($this->razonsocial)
                . "," . $this->var2str($this->email)
                . "," . $this->var2str($this->direccion)
                . "," . $this->var2str($this->regimen_empresa)
                . "," . $this->var2str($this->obligado_empresa)
                . "," . $this->var2str($this->agretencion_empresa)
                . "," . $this->var2str($this->numero_documento)
                . "," . $this->var2str($this->nro_autorizacion)
                . "," . $this->var2str($this->estado_sri)
                . "," . $this->var2str($this->fec_autorizacion)
                . "," . $this->var2str($this->hor_autorizacion)
                . "," . $this->var2str($this->fec_emision)
                . "," . $this->var2str($this->hora_emision)
                . "," . $this->var2str($this->fec_registro)
                . "," . $this->var2str($this->base_noi)
                . "," . $this->var2str($this->base_0)
                . "," . $this->var2str($this->base_gra)
                . "," . $this->var2str($this->base_exc)
                . "," . $this->var2str($this->totaldescuento)
                . "," . $this->var2str($this->totalice)
                . "," . $this->var2str($this->totaliva)
                . "," . $this->var2str($this->total)
                . "," . $this->var2str($this->observaciones)
                . "," . $this->var2str($this->dirpartida)
                . "," . $this->var2str($this->razonsocial_trans)
                . "," . $this->var2str($this->tipoid_trans)
                . "," . $this->var2str($this->identificacion_trans)
                . "," . $this->var2str($this->fec_finalizacion)
                . "," . $this->var2str($this->placa)
                . "," . $this->var2str($this->motivo)
                . "," . $this->var2str($this->codestablecimiento)
                . "," . $this->var2str($this->ruta)
                . "," . $this->var2str($this->tipo_guia)
                . "," . $this->var2str($this->idfactura_mod)
                . "," . $this->var2str($this->iddocumento_mod)
                . "," . $this->var2str($this->coddocumento_mod)
                . "," . $this->var2str($this->numero_documento_mod)
                . "," . $this->var2str($this->nro_autorizacion_mod)
                . "," . $this->var2str($this->fec_emision_mod)
                . "," . $this->var2str($this->anulado)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->beforeSave()) {
                if ($this->db->exec($sql)) {
                    if ($insert) {
                        $this->idguiacli = $this->db->lastval();
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
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idguiacli = " . $this->var2str($this->idguiacli) . ";");
        }
        return false;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY razonsocial DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \guiascli($p);
            }
        }

        return $list;
    }

    public function all_by_idempresa($idempresa)
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " ORDER BY razonsocial DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \guiascli($p);
            }
        }

        return $list;
    }

    public function search_guiascli($idempresa = '', $query = '', $idcliente = '', $fechadesde = '', $fechahasta = '', $anuladas = false, $autorizadas = false, $sinautorizar = false, $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        if ($idempresa != '') {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }
        if ($idcliente != '') {
            $sql .= " AND idcliente = " . $this->var2str($idcliente);
        }
        if ($fechadesde != '') {
            $sql .= " AND fec_emision >= " . $this->var2str($fechadesde);
        }
        if ($fechahasta != '') {
            $sql .= " AND fec_emision <= " . $this->var2str($fechahasta);
        }
        if ($anuladas) {
            $sql .= " AND anulado = " . $this->var2str(true);
        }
        if ($autorizadas) {
            $sql .= " AND estado_sri = " . $this->var2str('AUTORIZADO') . " AND anulado != " . $this->var2str(true);
        }
        if ($sinautorizar) {
            $sql .= " AND estado_sri != " . $this->var2str('AUTORIZADO') . " AND anulado != " . $this->var2str(true);
        }
        if ($query != '') {
            $query = strtolower($query);
            $sql .= " AND (lower(numero_documento) LIKE '%" . $query . "%' OR lower(nro_autorizacion) LIKE '%" . $query . "%' OR lower(observaciones) LIKE '%" . $query . "%')";
        }
        if ($offset >= 0) {
            $sql .= " ORDER BY fec_emision DESC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }

        if ($data) {
            if ($offset < 0) {
                return count($data);
            } else {
                foreach ($data as $p) {
                    $list[] = new \guiascli($p);
                }
            }
        }

        return $list;
    }

    public function get_by_idcliente($idempresa, $idcliente)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idcliente = " . $this->var2str($idcliente) . ";");
        if ($data) {
            return new \guiascli($data[0]);
        }

        return false;
    }

    public function get_cliente()
    {
        $cli0 = new clientes();
        return $cli0->get($this->idcliente);
    }

    public function get_tipodoc()
    {
        return 'Guia de Remision';
    }

    public function get_nombreestablecimiento()
    {
        $establecimiento = new \establecimiento();
        $doc             = $establecimiento->get($this->idestablecimiento);
        if ($doc) {
            return $doc->nombre;
        }

        return '-';
    }

    private function beforeDelete()
    {
        //Valido que la retencion no este autorizada
        if ($this->anulado) {
            $this->new_error_msg("El documento se encuentra anulado no es posible eliminar.");
            return false;
        } else if ($this->estado_sri == 'AUTORIZADO') {
            $this->new_error_msg("El documento se encuentra autorizado no es posible eliminar.");
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

    public function listado_guias($idempresa, $fec_desde = false, $fec_hasta = false, $idestablecimiento = false, $idcliente = false, $tipo_guia = false)
    {
        $lista = array();

        $sql = "SELECT CASE g.tipo_guia WHEN 1 THEN 'Facturada' ELSE 'Al Cobro' END AS tipo_guia, g.observaciones, g.numero_documento, g.fec_emision, g.razonsocial, e.nombre AS establecimiento, g.placa, g.total FROM " . $this->table_name . " g INNER JOIN establecimiento e ON e.idestablecimiento = g.idestablecimiento INNER JOIN clientes c ON c.idcliente = g.idcliente WHERE 1 = 1";
        if ($fec_desde) {
            $sql .= " AND g.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND g.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idestablecimiento) {
            $sql .= " AND g.idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($idcliente) {
            $sql .= " AND g.idcliente = " . $this->var2str($idcliente);
        }
        if ($tipo_guia) {
            $sql .= " AND g.tipo_guia = " . $this->var2str($tipo_guia);
        }
        if ($idempresa) {
            $sql .= " AND g.idempresa = " . $this->var2str($idempresa);
        }

        $sql .= " ORDER BY g.tipo_guia ASC, g.numero_documento ASC";
        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $lista;
    }

    public function buscar_guia_cliente($idempresa, $query, $idcliente)
    {
        $lista = array();

        $sql  = "SELECT * FROM " . $this->table_name . " WHERE coddocumento = " . $this->var2str('06') . " AND idempresa = " . $this->var2str($idempresa) . " AND numero_documento LIKE '%" . $query . "%' AND idcliente = " . $this->var2str($idcliente) . " ORDER BY fec_emision ASC;";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $lista[] = new \guiascli($p);
            }
        }
        return $lista;
    }

    public function getTransportistas($identificacion_trans)
    {
        $list = array();
        $sql  = "SELECT identificacion_trans, tipoid_trans, razonsocial_trans, placa FROM guiascli WHERE identificacion_trans LIKE '%" . $identificacion_trans . "%' OR lower(razonsocial_trans) LIKE '%" . strtolower($identificacion_trans) . "%' OR lower(placa) LIKE '%" . strtolower($identificacion_trans) . "%' GROUP BY identificacion_trans, tipoid_trans, razonsocial_trans, placa";
        $data = $this->db->select($sql);
        if ($data) {
            $list = $data;
        }

        return $list;
    }

    public function get_transp($identificacion_trans)
    {
        $list = array();
        $sql  = "SELECT identificacion_trans, tipoid_trans, razonsocial_trans, placa FROM guiascli WHERE identificacion_trans = " . $this->var2str($identificacion_trans) . " ORDER BY idguiacli DESC";
        $data = $this->db->select($sql);
        if ($data) {
            $list = $data[0];
        }

        return $list;
    }

    public function get_tipo_guia()
    {
        if ($this->tipo_guia == 1) {
            return 'Facturada';
        } else {
            return 'Al Cobro';
        }
    }

    public function anular()
    {
        //valido que sea electronica
        if (strlen($this->nro_autorizacion) == 49) {
            $autorizacion = consultardocsri($this->nro_autorizacion);
            if ($autorizacion['encontrado']) {
                $this->new_error_msg('El documento se encuentra autorizado, anule en el SRI y vuelva a intentarlo.');
                return false;
            }
        }

        $this->anulado = true;

        if (!$this->save()) {
            $this->new_advice('No se puede actualizar la anulación, en la cabecera del documento.');
            return false;
        }

        return true;
    }
}
