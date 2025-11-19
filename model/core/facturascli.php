<?php

namespace GSC_Systems\model;

class facturascli extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('facturascli');
        if ($data) {

            $this->idfacturacli         = $data['idfacturacli'];
            $this->idempresa            = $data['idempresa'];
            $this->idestablecimiento    = $data['idestablecimiento'];
            $this->idcliente            = $data['idcliente'];
            $this->iddocumento          = $data['iddocumento'];
            $this->coddocumento         = $data['coddocumento'];
            $this->tipoemision          = $data['tipoemision'];
            $this->tipoid               = $data['tipoid'];
            $this->identificacion       = $data['identificacion'];
            $this->razonsocial          = $data['razonsocial'];
            $this->email                = $data['email'];
            $this->direccion            = $data['direccion'];
            $this->regimen_empresa      = $data['regimen_empresa'];
            $this->obligado_empresa     = $this->str2bool($data['obligado_empresa']);
            $this->agretencion_empresa  = $this->str2bool($data['agretencion_empresa']);
            $this->numero_documento     = $data['numero_documento'];
            $this->nro_autorizacion     = $data['nro_autorizacion'];
            $this->estado_sri           = $data['estado_sri'];
            $this->fec_autorizacion     = $data['fec_autorizacion'] ? Date('d-m-Y', strtotime($data['fec_autorizacion'])) : null;
            $this->hor_autorizacion     = $data['hor_autorizacion'] ? Date('H:i:s', strtotime($data['hor_autorizacion'])) : null;
            $this->diascredito          = $data['diascredito'];
            $this->fec_emision          = $data['fec_emision'] ? Date('d-m-Y', strtotime($data['fec_emision'])) : null;
            $this->hora_emision         = $data['hora_emision'] ? Date('H:i:s', strtotime($data['hora_emision'])) : null;
            $this->fec_registro         = $data['fec_registro'] ? Date('d-m-Y', strtotime($data['fec_registro'])) : null;
            $this->base_noi             = floatval($data['base_noi']);
            $this->base_0               = floatval($data['base_0']);
            $this->base_gra             = floatval($data['base_gra']);
            $this->base_exc             = floatval($data['base_exc']);
            $this->totaldescuento       = floatval($data['totaldescuento']);
            $this->totalice             = floatval($data['totalice']);
            $this->totaliva             = floatval($data['totaliva']);
            $this->total                = floatval($data['total']);
            $this->observaciones        = $data['observaciones'];
            $this->idfactura_mod        = $data['idfactura_mod'];
            $this->iddocumento_mod      = $data['iddocumento_mod'];
            $this->coddocumento_mod     = $data['coddocumento_mod'];
            $this->numero_documento_mod = $data['numero_documento_mod'];
            $this->nro_autorizacion_mod = $data['nro_autorizacion_mod'];
            $this->fec_emision_mod      = $data['fec_emision_mod'] ? Date('d-m-Y', strtotime($data['fec_emision_mod'])) : null;
            $this->idcaja               = $data['idcaja'];
            $this->anulado              = $this->str2bool($data['anulado']);
            $this->saldoinicial         = $this->str2bool($data['saldoinicial']);
            $this->idejercicio          = $data['idejercicio'];
            $this->idmedidor            = $data['idmedidor'];
            $this->idfpsri              = $data['idfpsri'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

            $this->namedoc = $this->get_tipodoc();
            $this->urldoc  = $this->url();

        } else {
            $this->idfacturacli         = null;
            $this->idempresa            = null;
            $this->idestablecimiento    = null;
            $this->idcliente            = null;
            $this->iddocumento          = null;
            $this->coddocumento         = '01';
            $this->tipoemision          = 'F';
            $this->tipoid               = null;
            $this->identificacion       = null;
            $this->razonsocial          = null;
            $this->email                = null;
            $this->direccion            = null;
            $this->regimen_empresa      = null;
            $this->obligado_empresa     = 0;
            $this->agretencion_empresa  = 0;
            $this->numero_documento     = null;
            $this->nro_autorizacion     = null;
            $this->estado_sri           = 'PENDIENTE';
            $this->fec_autorizacion     = null;
            $this->hor_autorizacion     = null;
            $this->diascredito          = null;
            $this->fec_emision          = null;
            $this->hora_emision         = null;
            $this->fec_registro         = null;
            $this->base_noi             = 0;
            $this->base_0               = 0;
            $this->base_gra             = 0;
            $this->base_exc             = 0;
            $this->totaldescuento       = 0;
            $this->totalice             = 0;
            $this->totaliva             = 0;
            $this->total                = 0;
            $this->observaciones        = null;
            $this->idfactura_mod        = null;
            $this->iddocumento_mod      = null;
            $this->coddocumento_mod     = '01';
            $this->numero_documento_mod = null;
            $this->nro_autorizacion_mod = null;
            $this->fec_emision_mod      = null;
            $this->idcaja               = null;
            $this->anulado              = false;
            $this->saldoinicial         = false;
            $this->idejercicio          = null;
            $this->idmedidor            = null;
            $this->idfpsri              = null;
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
        new \documentos();
        new \establecimiento();
        new \formaspago_sri();

        return "";
    }

    public function url()
    {
        if (is_null($this->idfacturacli)) {
            return 'index.php?page=lista_facturas_cliente';
        }

        if ($this->coddocumento == '02') {
            return 'index.php?page=ver_nota_venta&id=' . $this->idfacturacli;
        }

        return 'index.php?page=ver_factura_cliente&id=' . $this->idfacturacli;
    }

    public function get($idfacturacli)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idfacturacli = " . $this->var2str($idfacturacli) . ";");
        if ($data) {
            return new \facturascli($data[0]);
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
        $lineas = new \lineasfacturascli();
        return $lineas->all_by_idfacturacli($this->idfacturacli);
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

    public function get_medidor()
    {
        if ($this->idmedidor) {
            $med0 = new \medidores_cliente();
            $med  = $med0->get($this->idmedidor);
            if ($med) {
                return $med;
            }
        }
        return false;
    }

    public function get_fp_sri()
    {
        if ($this->idfpsri) {
            $fpsri0 = new \formaspago_sri();
            $fpsri  = $fpsri0->get($this->idfpsri);
            if ($fpsri) {
                return $fpsri;
            }
        }
        return false;
    }

    public function exists()
    {
        if (is_null($this->idfacturacli)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idfacturacli = " . $this->var2str($this->idfacturacli) . ";");
    }

    public function test()
    {
        $status = true;
        //Valido si tiene acceso a crear nuevos documentos
        if (complemento_exists('facturador')) {
            if (!$this->idfacturacli) {
                $empresa = $this->get_empresa();
                if ($empresa) {
                    if (!$empresa->plan_activo(true)) {
                        return false;
                    }
                }
            }
        }
        //se actualiza el total
        $this->total = round($this->base_noi + $this->base_0 + $this->base_gra + $this->base_exc + $this->totalice + $this->totaliva, 2);
        if (!$this->numero_documento) {
            if (!$this->generar_numero_documento()) {
                $status = false;
            }
        } else if (!$this->nro_autorizacion) {
            $nro_autorizacion       = $this->generarNumeroAut($this->numero_documento);
            $this->nro_autorizacion = $nro_autorizacion;
        }

        if (complemento_exists('contabilidad')) {
            //si existe el plugin de contabilidad se debe buscar el ejercicio para generarlo
            if (!$this->idejercicio && !$this->saldoinicial) {
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
            if ($this->coddocumento == '01') {
                //pongo el numero de factura
                $numSec = str_pad($est->numfac, 9, "0", STR_PAD_LEFT);
                $est->numfac++;
            } else if ($this->coddocumento == '04') {
                //pongo el numero de Nota de Credito
                $numSec = str_pad($est->numncc, 9, "0", STR_PAD_LEFT);
                $est->numncc++;
            } else if ($this->coddocumento == '05') {
                //pongo el numero de Nota de Credito
                $numSec = str_pad($est->numndd, 9, "0", STR_PAD_LEFT);
                $est->numndd++;
            } else if ($this->coddocumento == '02') {
                //pongo el numero de Nota de Venta
                $numSec = str_pad($est->numnvt, 9, "0", STR_PAD_LEFT);
                $est->numnvt++;
            }
            $est->save();
            $establecimiento  = str_pad($est->codigo, 3, "0", STR_PAD_LEFT);
            $ptoemision       = str_pad($est->ptoemision, 3, "0", STR_PAD_LEFT);
            $numero_documento = $establecimiento . "-" . $ptoemision . "-" . $numSec;
            if ($this->regimen_empresa != 'RP') {
                $nro_autorizacion = $this->generarNumeroAut($numero_documento);
            }
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
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE numero_documento = " . $this->var2str($numero_documento) . " AND coddocumento = " . $this->var2str($this->coddocumento) . " AND idempresa = " . $this->var2str($this->idempresa);
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
                . ", iddocumento = " . $this->var2str($this->iddocumento)
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
                . ", estado_sri = " . $this->var2str($this->estado_sri)
                . ", fec_autorizacion = " . $this->var2str($this->fec_autorizacion)
                . ", hor_autorizacion = " . $this->var2str($this->hor_autorizacion)
                . ", nro_autorizacion = " . $this->var2str($this->nro_autorizacion)
                . ", diascredito = " . $this->var2str($this->diascredito)
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
                . ", idfactura_mod = " . $this->var2str($this->idfactura_mod)
                . ", iddocumento_mod = " . $this->var2str($this->iddocumento_mod)
                . ", coddocumento_mod = " . $this->var2str($this->coddocumento_mod)
                . ", numero_documento_mod = " . $this->var2str($this->numero_documento_mod)
                . ", nro_autorizacion_mod = " . $this->var2str($this->nro_autorizacion_mod)
                . ", fec_emision_mod = " . $this->var2str($this->fec_emision_mod)
                . ", idcaja = " . $this->var2str($this->idcaja)
                . ", anulado = " . $this->var2str($this->anulado)
                . ", saldoinicial = " . $this->var2str($this->saldoinicial)
                . ", idejercicio = " . $this->var2str($this->idejercicio)
                . ", idmedidor = " . $this->var2str($this->idmedidor)
                . ", idfpsri = " . $this->var2str($this->idfpsri)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idfacturacli = " . $this->var2str($this->idfacturacli) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idestablecimiento, idcliente, iddocumento, coddocumento, tipoemision, tipoid, identificacion, razonsocial, email, direccion, regimen_empresa, obligado_empresa, agretencion_empresa, numero_documento, nro_autorizacion, estado_sri, fec_autorizacion, hor_autorizacion, diascredito, fec_emision, hora_emision, fec_registro, base_noi, base_0, base_gra, base_exc, totaldescuento, totalice, totaliva, total, observaciones, idfactura_mod, iddocumento_mod, coddocumento_mod, numero_documento_mod, nro_autorizacion_mod, fec_emision_mod, idcaja, anulado, saldoinicial, idejercicio, idmedidor, idfpsri, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idestablecimiento)
                . "," . $this->var2str($this->idcliente)
                . "," . $this->var2str($this->iddocumento)
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
                . "," . $this->var2str($this->diascredito)
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
                . "," . $this->var2str($this->idfactura_mod)
                . "," . $this->var2str($this->iddocumento_mod)
                . "," . $this->var2str($this->coddocumento_mod)
                . "," . $this->var2str($this->numero_documento_mod)
                . "," . $this->var2str($this->nro_autorizacion_mod)
                . "," . $this->var2str($this->fec_emision_mod)
                . "," . $this->var2str($this->idcaja)
                . "," . $this->var2str($this->anulado)
                . "," . $this->var2str($this->saldoinicial)
                . "," . $this->var2str($this->idejercicio)
                . "," . $this->var2str($this->idmedidor)
                . "," . $this->var2str($this->idfpsri)
                . "," . $this->var2str($this->fec_creacion)
                . "," . $this->var2str($this->nick_creacion)
                . "," . $this->var2str($this->fec_modificacion)
                . "," . $this->var2str($this->nick_modificacion)
                    . ");";
            }

            if ($this->beforeSave()) {
                if ($this->db->exec($sql)) {
                    if ($insert) {
                        $this->idfacturacli = $this->db->lastval();
                    }
                    $this->afterSave();
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

    private function afterSave()
    {
        $fp0    = new \formaspago();
        $cobro0 = new \trans_cobros();
        //Si ya existe lo elimino
        if ($this->coddocumento == '04') {
            $cobro = $cobro0->get_pago_ncc($this->idfacturacli);
        } else {
            $cobro = $cobro0->get_pago_fact($this->idfacturacli);
        }
        if ($cobro) {
            if (!$cobro->delete()) {
                $this->new_error_msg('Error al eliminar el cobro asociado al Documento.');
                return false;
            }
        }

        if (!$this->anulado) {
            $cobro            = new \trans_cobros();
            $cobro->idempresa = $this->idempresa;
            $cobro->idcliente = $this->idcliente;
            if ($this->coddocumento == '04') {
                $fp = $fp0->get_notacredito($this->idempresa);
                if ($fp) {
                    $cobro->idformapago = $fp->idformapago;
                } else {
                    $this->new_error_msg('Forma de Pago Nota de Crédito no encontrada.');
                    return false;
                }
                if ($this->idfactura_mod) {
                    $cobro->idfacturacli = $this->idfactura_mod;
                } else {
                    $cobro->idfacturacli = $this->idfacturacli;
                }
                $cobro->idfacturanc = $this->idfacturacli;
                $cobro->credito     = $this->total;
                $cobro->esabono     = true;
            } else {
                $fp = $fp0->get_credito($this->idempresa);
                if ($fp) {
                    $cobro->idformapago = $fp->idformapago;
                } else {
                    $this->new_error_msg('Forma de Pago Crédito no encontrada.');
                    return false;
                }
                $cobro->idfacturacli = $this->idfacturacli;
                $cobro->debito       = $this->total;
            }
            $cobro->fecha_trans   = $this->fec_emision;
            $cobro->tipo          = $this->get_tipodoc();
            $cobro->fec_creacion  = date('Y-m-d');
            $cobro->nick_creacion = $this->nick_creacion;

            if (!$cobro->save()) {
                $this->new_error_msg('Error al guardar el pago.');
                return false;
            }
        }

        return true;
    }

    public function delete()
    {
        if ($this->beforeDelete()) {
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idfacturacli = " . $this->var2str($this->idfacturacli) . ";");
        }
        return false;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY razonsocial DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \facturascli($p);
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
                $list[] = new \facturascli($p);
            }
        }

        return $list;
    }

    public function search_facturascli($idempresa = '', $query = '', $idcliente = '', $fechadesde = '', $fechahasta = '', $iddocumento = '', $sanuladas = false, $anuladas = false, $saldosini = false, $autorizadas = false, $sinautorizar = false, $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        if ($idempresa != '') {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }
        if ($idcliente != '') {
            $sql .= " AND idcliente = " . $this->var2str($idcliente);
        }
        if ($iddocumento != '') {
            $sql .= " AND iddocumento = " . $this->var2str($iddocumento);
        } else {
            $sql .= " AND coddocumento != '02'";
        }
        if ($fechadesde != '') {
            $sql .= " AND fec_emision >= " . $this->var2str($fechadesde);
        }
        if ($fechahasta != '') {
            $sql .= " AND fec_emision <= " . $this->var2str($fechahasta);
        }
        if ($sanuladas) {
            $sql .= " AND anulado != " . $this->var2str(true);
        }
        if ($anuladas) {
            $sql .= " AND anulado = " . $this->var2str(true);
        }
        if ($saldosini) {
            $sql .= " AND saldoinicial = " . $this->var2str(true);
        }
        if ($autorizadas) {
            $sql .= " AND estado_sri = " . $this->var2str('AUTORIZADO') . " AND anulado != " . $this->var2str(true) . " AND saldoinicial != " . $this->var2str(true);
        }
        if ($sinautorizar) {
            $sql .= " AND estado_sri != " . $this->var2str('AUTORIZADO') . " AND anulado != " . $this->var2str(true) . " AND saldoinicial != " . $this->var2str(true);
        }
        if ($query != '') {
            $query = strtolower($query);
            $sql .= " AND (lower(numero_documento) LIKE '%" . $query . "%' OR lower(nro_autorizacion) LIKE '%" . $query . "%' OR lower(observaciones) LIKE '%" . $query . "%')";
        }
        if ($offset >= 0) {
            $sql .= " ORDER BY fec_emision DESC, numero_documento DESC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }
        if ($data) {
            if ($offset < 0) {
                return count($data);
            } else {
                foreach ($data as $p) {
                    $list[] = new \facturascli($p);
                }
            }
        }

        return $list;
    }

    public function get_by_idcliente($idempresa, $idcliente)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idcliente = " . $this->var2str($idcliente) . ";");
        if ($data) {
            return new \facturascli($data[0]);
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
        if (JG_ECAUTE == 1) {
            if ($this->coddocumento == '02') {
                return 'Prefactura';
            }
        }
        $documento = new \documentos();
        $doc       = $documento->get($this->iddocumento);
        if ($doc) {
            return $doc->nombre;
        }

        return '-';
    }

    public function vencimiento()
    {
        return date('d-m-Y', strtotime($this->fec_emision . "+ " . $this->diascredito . " days"));
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

        //valido si no tiene pagos
        $cobros0 = new \trans_cobros();
        $cobros  = $cobros0->all_by_idfacturacli($this->idfacturacli);
        if (count($cobros) > 1) {
            $this->new_error_msg("Tiene cobros Asociados al Documento, no se puede eliminar.");
            return false;
        } else {
            $paso = true;
            foreach ($cobros as $key => $p) {
                if (!$p->delete()) {
                    $paso = false;
                    $this->new_error_msg("Existió un problema al eliminar el pago de la factura.");
                }
            }
            if (!$paso) {
                return $paso;
            }
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

        //elimino las lineas para reversar el stock
        foreach ($this->getlineas() as $key => $l) {
            $l->delete();
        }

        return true;
    }

    public function listado_facturas($idempresa, $fec_desde = false, $fec_hasta = false, $idestablecimiento = false, $idcliente = false, $iddocumento = false, $sanuladas = false)
    {
        $lista = array();

        $sql = "SELECT d.iddocumento, d.codigo, c.regimen, d.nombre, f.observaciones, f.numero_documento, f.nro_autorizacion, f.fec_autorizacion, f.identificacion, f.fec_emision, f.razonsocial, e.nombre AS establecimiento, f.base_gra, f.base_0, f.base_noi, f.base_exc, f.totaldescuento, f.totalice, f.totaliva, f.total FROM " . $this->table_name . " f INNER JOIN documentos d ON d.iddocumento = f.iddocumento INNER JOIN establecimiento e ON e.idestablecimiento = f.idestablecimiento INNER JOIN clientes c ON c.idcliente = f.idcliente WHERE 1 = 1";
        if ($fec_desde) {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND f.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idestablecimiento) {
            $sql .= " AND f.idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($idcliente) {
            $sql .= " AND f.idcliente = " . $this->var2str($idcliente);
        }
        if ($iddocumento) {
            $sql .= " AND f.iddocumento = " . $this->var2str($iddocumento);
        } else {
            $sql .= " AND f.coddocumento != " . $this->var2str('02');
        }
        if ($idempresa) {
            $sql .= " AND f.idempresa = " . $this->var2str($idempresa);
        }

        if ($sanuladas) {
            $sql .= " AND f.anulado != " . $this->var2str(true);
        }

        $sql .= " ORDER BY f.iddocumento, f.fec_emision, f.razonsocial ASC";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $lista;
    }

    public function buscar_factura_cliente($idempresa, $query, $idcliente)
    {
        $lista = array();

        $sql  = "SELECT * FROM " . $this->table_name . " WHERE coddocumento = " . $this->var2str('01') . " AND idempresa = " . $this->var2str($idempresa) . " AND numero_documento LIKE '%" . $query . "%' AND idcliente = " . $this->var2str($idcliente) . " ORDER BY fec_emision ASC;";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $lista[] = new \facturascli($p);
            }
        }
        return $lista;
    }

    public function buscar_factura($idempresa, $numero_documento)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE coddocumento = " . $this->var2str('01') . " AND idempresa = " . $this->var2str($idempresa) . " AND numero_documento = " . $this->var2str($numero_documento) . ";");
        if ($data) {
            return new \facturascli($data[0]);
        }

        return false;
    }

    public function getbycierre($idempresa, $idcaja)
    {
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE anulado != " . $this->var2str(true) . " AND idempresa = " . $this->var2str($idempresa) . " AND idcaja = " . $this->var2str($idcaja) . " ORDER BY fec_emision DESC, numero_documento DESC";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \facturascli($p);
            }
        }
        return $list;
    }

    public function detalle_facturas($idempresa, $fec_desde = false, $fec_hasta = false, $idestablecimiento = false, $idcliente = false, $iddocumento = false, $item = 1, $sanuladas = false)
    {
        $lista = array();

        $sql = "SELECT f.numero_documento, d.codigo, d.nombre AS nombredoc, f.fec_emision, f.identificacion, f.nro_autorizacion, f.fec_autorizacion, f.razonsocial, ar.codprincipal, ar.nombre, l.cantidad, l.pvpunitario, round((l.cantidad * l.pvpunitario) - l.pvptotal, 6) AS dto, l.pvptotal, round(l.valorice, 6) AS otrosimp, l.valoriva FROM lineasfacturascli l INNER JOIN " . $this->table_name . " f ON f.idfacturacli = l.idfacturacli INNER JOIN articulos ar ON ar.idarticulo = l.idarticulo INNER JOIN documentos d ON d.iddocumento = f.iddocumento WHERE 1 = 1";

        if ($fec_desde) {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND f.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idestablecimiento) {
            $sql .= " AND f.idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($idcliente) {
            $sql .= " AND f.idcliente = " . $this->var2str($idcliente);
        }
        if ($iddocumento) {
            $sql .= " AND f.iddocumento = " . $this->var2str($iddocumento);
        } else {
            $sql .= " AND coddocumento != '02'";
        }
        if ($idempresa) {
            $sql .= " AND f.idempresa = " . $this->var2str($idempresa);
        }
        if ($item) {
            $sql .= " AND ar.tipo = " . $this->var2str($item);
        }

        if ($sanuladas) {
            $sql .= " AND f.anulado != " . $this->var2str(true);
        }

        $sql .= " ORDER BY f.fec_emision ASC";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $lista;
    }

    public function contador_documentos_104($idempresa, $fec_desde, $fec_hasta)
    {
        $list = array();

        $sql = "SELECT anulado, count(idfacturacli) AS contador FROM facturascli WHERE idempresa = " . $this->var2str($idempresa) . " AND fec_emision >= " . $this->var2str($fec_desde) . " AND fec_emision <= " . $this->var2str($fec_hasta) . " AND coddocumento NOT IN ('02', '04') GROUP BY anulado";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $list;
    }

    public function anular()
    {
        if ($this->coddocumento != '02') {
            //valido que sea electronica
            if (strlen($this->nro_autorizacion) == 49) {
                $autorizacion = consultardocsri($this->nro_autorizacion);
                if ($autorizacion['encontrado']) {
                    $this->new_error_msg('El documento se encuentra autorizado, anule en el SRI y vuelva a intentarlo.');
                    return false;
                }
            }
        }

        //valido si no tiene pagos
        $cobros0 = new \trans_cobros();
        $cobros  = $cobros0->all_by_idfacturacli($this->idfacturacli);
        if (count($cobros) > 1) {
            $this->new_error_msg("Tiene cobros Asociados al Documento, no se puede anular.");
            return false;
        }

        $this->anulado = true;

        if (!$this->save()) {
            $this->new_advice('No se puede actualizar la anulación, en la cabecera del documento.');
            return false;
        } else {
            //actualizo las lineas para reversar el stock
            foreach ($this->getlineas() as $key => $l) {
                $l->save(false);
            }
        }

        return true;
    }

    public function totalVentas($idempresa, $desde, $hasta)
    {
        $list = array();

        $sql = "SELECT f.coddocumento, e.codigo, SUM(base_0 + base_gra + base_noi) AS total, SUM(f.totaliva) AS iva FROM " . $this->table_name . " f INNER JOIN establecimiento e ON e.idestablecimiento = f.idestablecimiento WHERE f.idempresa = " . $this->var2str($idempresa) . " AND f.tipoemision = 'F' AND f.coddocumento NOT IN ('02') AND f.anulado != " . $this->var2str(true) . " AND f.saldoinicial != " . $this->var2str(true) . " AND f.fec_emision >= " . $this->var2str($desde) . " AND fec_emision <= " . $this->var2str($hasta) . " GROUP BY f.coddocumento, e.codigo ORDER by e.codigo ASC";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $list;
    }

    public function ventasAts($idempresa, $desde, $hasta)
    {
        $list = array();

        $sql = "SELECT f.coddocumento, f.tipoemision, f.identificacion, f.tipoid, COUNT(f.idfacturacli) AS totdoc, SUM ( base_0 ) AS base0, SUM ( base_gra ) AS basegra, SUM ( f.base_noi ) AS baseno, SUM (f.totaliva ) AS iva, SUM (f.totalice ) AS ice  FROM " . $this->table_name . " f WHERE f.idempresa = " . $this->var2str($idempresa) . " AND f.coddocumento NOT IN ('02') AND f.anulado != " . $this->var2str(true) . " AND f.saldoinicial != " . $this->var2str(true) . " AND f.fec_emision >= " . $this->var2str($desde) . " AND f.fec_emision <= " . $this->var2str($hasta) . " GROUP BY f.coddocumento, f.tipoemision, f.identificacion, f.tipoid ORDER by f.tipoemision DESC";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $list;
    }

    public function getAnulados($idempresa, $desde, $hasta)
    {
        $list = array();
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND fec_emision >= " . $this->var2str($desde) . " AND fec_emision <= " . $this->var2str($hasta) . " AND coddocumento NOT IN ('02') AND anulado = " . $this->var2str(true) . " AND saldoinicial != " . $this->var2str(true);

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \facturascli($p);
            }
        }

        return $list;
    }

    public function getNoAutorizados($idempresa = '', $desde = '', $hasta = '', $offset = 0, $limit = 25)
    {
        $list = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE coddocumento != " . $this->var2str('02') . " AND estado_sri != " . $this->var2str('AUTORIZADO') . " AND anulado != " . $this->var2str(true) . " AND saldoinicial != " . $this->var2str(true);
        if ($idempresa) {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }
        if ($desde) {
            $sql .= " AND fec_emision >= " . $this->var2str($desde);
        }
        if ($hasta) {
            $sql .= " AND fec_emision <= " . $this->var2str($hasta);
        }
        $sql .= " ORDER BY fec_emision ASC, numero_documento ASC";

        $data = $this->db->select_limit($sql, $limit, $offset);

        if ($data) {
            foreach ($data as $p) {
                $list[] = new \facturascli($p);
            }
        }

        return $list;
    }
}
