<?php

namespace GSC_Systems\model;

class facturasprov extends \model
{

    public function __construct($data = false)
    {
        parent::__construct('facturasprov');
        if ($data) {

            $this->idfacturaprov        = $data['idfacturaprov'];
            $this->idempresa            = $data['idempresa'];
            $this->idestablecimiento    = $data['idestablecimiento'];
            $this->idproveedor          = $data['idproveedor'];
            $this->coddocumento         = $data['coddocumento'];
            $this->iddocumento          = $data['iddocumento'];
            $this->idsustento           = $data['idsustento'];
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
            $this->numero_retencion     = $data['numero_retencion'];
            $this->nro_autorizacion     = $data['nro_autorizacion'];
            $this->estado_sri           = $data['estado_sri'];
            $this->fec_autorizacion     = $data['fec_autorizacion'] ? Date('d-m-Y', strtotime($data['fec_autorizacion'])) : null;
            $this->hor_autorizacion     = $data['hor_autorizacion'] ? Date('H:i:s', strtotime($data['hor_autorizacion'])) : null;
            $this->diascredito          = $data['diascredito'];
            $this->fec_emision          = $data['fec_emision'] ? Date('d-m-Y', strtotime($data['fec_emision'])) : null;
            $this->fec_emision2         = $data['fec_emision'] ? Date('Y-m-d', strtotime($data['fec_emision'])) : null;
            $this->hora_emision         = $data['hora_emision'] ? Date('H:i:s', strtotime($data['hora_emision'])) : null;
            $this->fec_caducidad        = $data['fec_caducidad'] ? Date('d-m-Y', strtotime($data['fec_caducidad'])) : null;
            $this->fec_registro         = $data['fec_registro'] ? Date('d-m-Y', strtotime($data['fec_registro'])) : null;
            $this->fec_registro2        = $data['fec_registro'] ? Date('Y-m-d', strtotime($data['fec_registro'])) : null;
            $this->base_noi             = floatval($data['base_noi']);
            $this->base_0               = floatval($data['base_0']);
            $this->base_gra             = floatval($data['base_gra']);
            $this->base_exc             = floatval($data['base_exc']);
            $this->totaldescuento       = floatval($data['totaldescuento']);
            $this->totalice             = floatval($data['totalice']);
            $this->totaliva             = floatval($data['totaliva']);
            $this->totalirbp            = floatval($data['totalirbp']);
            $this->total                = floatval($data['total']);
            $this->observaciones        = $data['observaciones'];
            $this->idfactura_mod        = $data['idfactura_mod'];
            $this->iddocumento_mod      = $data['iddocumento_mod'];
            $this->coddocumento_mod     = $data['coddocumento_mod'];
            $this->numero_documento_mod = $data['numero_documento_mod'];
            $this->nro_autorizacion_mod = $data['nro_autorizacion_mod'];
            $this->fecdoc_modificado    = $data['fecdoc_modificado'] ? Date('d-m-Y', strtotime($data['fecdoc_modificado'])) : null;
            //Datos Retencion
            $this->coddocumento_ret     = $data['coddocumento_ret'];
            $this->nro_autorizacion_ret = $data['nro_autorizacion_ret'];
            $this->estado_sri_ret       = $data['estado_sri_ret'];
            $this->fec_autorizacion_ret = $data['fec_autorizacion_ret'] ? Date('d-m-Y', strtotime($data['fec_autorizacion_ret'])) : null;
            $this->hor_autorizacion_ret = $data['hor_autorizacion_ret'] ? Date('H:i:s', strtotime($data['hor_autorizacion_ret'])) : null;
            //campos de control
            $this->anulado      = $this->str2bool($data['anulado']);
            $this->saldoinicial = $this->str2bool($data['saldoinicial']);
            //Nuevos Campos
            $this->idejercicio = $data['idejercicio'];
            //Auditoria del sistema
            $this->fec_creacion      = $data['fec_creacion'] ? Date('d-m-Y', strtotime($data['fec_creacion'])) : null;
            $this->nick_creacion     = $data['nick_creacion'];
            $this->fec_modificacion  = $data['fec_modificacion'] ? Date('d-m-Y', strtotime($data['fec_modificacion'])) : null;
            $this->nick_modificacion = $data['nick_modificacion'];

        } else {
            $this->idfacturaprov        = null;
            $this->idempresa            = null;
            $this->idestablecimiento    = null;
            $this->idproveedor          = null;
            $this->coddocumento         = null;
            $this->iddocumento          = null;
            $this->idsustento           = null;
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
            $this->numero_retencion     = null;
            $this->nro_autorizacion     = null;
            $this->estado_sri           = 'PENDIENTE';
            $this->fec_autorizacion     = null;
            $this->hor_autorizacion     = null;
            $this->diascredito          = null;
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
            $this->observaciones        = null;
            $this->idfactura_mod        = null;
            $this->iddocumento_mod      = null;
            $this->coddocumento_mod     = '01';
            $this->numero_documento_mod = null;
            $this->nro_autorizacion_mod = null;
            $this->fecdoc_modificado    = null;
            //Datos Retencion
            $this->coddocumento_ret     = '07';
            $this->nro_autorizacion_ret = null;
            $this->estado_sri_ret       = 'PENDIENTE';
            $this->fec_autorizacion_ret = null;
            $this->hor_autorizacion_ret = null;
            //campos de control
            $this->anulado      = false;
            $this->saldoinicial = false;
            //Nuevos campos
            $this->idejercicio = null;
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
        new \proveedores();
        new \documentos();
        new \establecimiento();
        new \sustentos();

        return "";
    }

    public function url()
    {
        if (is_null($this->idfacturaprov)) {
            return 'index.php?page=lista_facturas_proveedor';
        }

        return 'index.php?page=ver_factura_proveedor&id=' . $this->idfacturaprov;
    }

    public function get($idfacturaprov)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idfacturaprov = " . $this->var2str($idfacturaprov) . ";");
        if ($data) {
            return new \facturasprov($data[0]);
        }

        return false;
    }

    public function get_by_nro_aut($idempresa, $nro_autorizacion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND nro_autorizacion = " . $this->var2str($nro_autorizacion) . ";");
        if ($data) {
            return new \facturasprov($data[0]);
        }

        return false;
    }

    public function get_proveedor()
    {
        $prov0 = new proveedores();
        return $prov0->get($this->idproveedor);
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
        $lineas = new \lineasfacturasprov();
        return $lineas->all_by_idfacturaprov($this->idfacturaprov);
    }

    public function exists()
    {
        if (is_null($this->idfacturaprov)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idfacturaprov = " . $this->var2str($this->idfacturaprov) . ";");
    }

    public function test()
    {
        $status = true;
        //Busco si la factura del proveedor ya se encuentra generada en el sistema
        if (!$this->idfacturaprov) {
            //Valido si tiene acceso a crear nuevos documentos
            if (complemento_exists('facturador')) {
                if ($this->coddocumento == '03' || $this->agretencion_empresa) {
                    $empresa = $this->get_empresa();
                    if ($empresa) {
                        if (!$empresa->plan_activo(true)) {
                            return false;
                        }
                    }
                }
            }

            if ($this->coddocumento == '03' && !$this->numero_documento) {
                $this->generar_numero_documento();
            } else {
                $sql  = 'SELECT * FROM ' . $this->table_name . ' WHERE iddocumento = ' . $this->var2str($this->iddocumento) . ' AND idempresa = ' . $this->var2str($this->idempresa) . " AND idproveedor = " . $this->var2str($this->idproveedor) . " AND numero_documento = " . $this->var2str($this->numero_documento);
                $data = $this->db->select($sql);
                if ($data) {
                    $this->new_error_msg("El documento: " . $this->numero_documento . " del Proveedor: " . $this->razonsocial . " ya se encuentra registrado.");
                    return false;
                }
            }

            //Genero el numero de autorizacion de la retencion con el numero de la retencion
            if (($this->agretencion_empresa || $this->coddocumento == '03') && !$this->numero_retencion) {
                if ($this->coddocumento != '04' && $this->coddocumento != '05') {
                    $this->generar_numero_retencion();
                }
            }
        } else {
            //Genero el numero de autorizacion de la retencion con el numero de la retencion
            if (($this->agretencion_empresa || $this->coddocumento == '03') && $this->numero_retencion && !$this->nro_autorizacion_ret) {
                if ($this->coddocumento != '04' && $this->coddocumento != '05') {
                    $this->nro_autorizacion_ret = $this->generarNumeroAutRetencion($this->numero_retencion);
                }
            }

            if ($this->coddocumento == '03' && !$this->nro_autorizacion) {
                $this->nro_autorizacion = $this->generarNumeroAut($this->numero_documento);
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

        $this->total = round($this->base_noi + $this->base_0 + $this->base_gra + $this->base_exc + $this->totalice + $this->totaliva + $this->totalirbp, 2);
        return $status;
    }

    public function save()
    {
        if ($this->test()) {
            $insert = false;
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET idempresa = " . $this->var2str($this->idempresa)
                . ", idestablecimiento = " . $this->var2str($this->idestablecimiento)
                . ", idproveedor = " . $this->var2str($this->idproveedor)
                . ", iddocumento = " . $this->var2str($this->iddocumento)
                . ", idsustento = " . $this->var2str($this->idsustento)
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
                . ", numero_retencion = " . $this->var2str($this->numero_retencion)
                . ", nro_autorizacion = " . $this->var2str($this->nro_autorizacion)
                . ", estado_sri = " . $this->var2str($this->estado_sri)
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
                . ", observaciones = " . $this->var2str($this->observaciones)
                . ", idfactura_mod = " . $this->var2str($this->idfactura_mod)
                . ", iddocumento_mod = " . $this->var2str($this->iddocumento_mod)
                . ", coddocumento_mod = " . $this->var2str($this->coddocumento_mod)
                . ", numero_documento_mod = " . $this->var2str($this->numero_documento_mod)
                . ", nro_autorizacion_mod = " . $this->var2str($this->nro_autorizacion_mod)
                . ", fecdoc_modificado = " . $this->var2str($this->fecdoc_modificado)
                . ", coddocumento_ret = " . $this->var2str($this->coddocumento_ret)
                . ", nro_autorizacion_ret = " . $this->var2str($this->nro_autorizacion_ret)
                . ", estado_sri_ret = " . $this->var2str($this->estado_sri_ret)
                . ", fec_autorizacion_ret = " . $this->var2str($this->fec_autorizacion_ret)
                . ", hor_autorizacion_ret = " . $this->var2str($this->hor_autorizacion_ret)
                . ", anulado = " . $this->var2str($this->anulado)
                . ", saldoinicial = " . $this->var2str($this->saldoinicial)
                . ", idejercicio = " . $this->var2str($this->idejercicio)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idfacturaprov = " . $this->var2str($this->idfacturaprov) . ";";
            } else {
                $insert = true;
                $sql    = "INSERT INTO " . $this->table_name . " (idempresa, idestablecimiento, idproveedor, iddocumento, idsustento, coddocumento, tipoemision, tipoid, identificacion, razonsocial, email, direccion, regimen_empresa, obligado_empresa, agretencion_empresa, numero_documento, numero_retencion, nro_autorizacion, estado_sri, fec_autorizacion, hor_autorizacion, diascredito, fec_emision, hora_emision, fec_caducidad, fec_registro, base_noi, base_0, base_gra, base_exc, totaldescuento, totalice, totaliva, totalirbp, total, observaciones, idfactura_mod, iddocumento_mod, coddocumento_mod, numero_documento_mod, nro_autorizacion_mod, fecdoc_modificado, coddocumento_ret, nro_autorizacion_ret, estado_sri_ret, fec_autorizacion_ret, hor_autorizacion_ret, anulado, saldoinicial, idejercicio, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES "
                . "(" . $this->var2str($this->idempresa)
                . "," . $this->var2str($this->idestablecimiento)
                . "," . $this->var2str($this->idproveedor)
                . "," . $this->var2str($this->iddocumento)
                . "," . $this->var2str($this->idsustento)
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
                . "," . $this->var2str($this->numero_retencion)
                . "," . $this->var2str($this->nro_autorizacion)
                . "," . $this->var2str($this->estado_sri)
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
                . "," . $this->var2str($this->observaciones)
                . "," . $this->var2str($this->idfactura_mod)
                . "," . $this->var2str($this->iddocumento_mod)
                . "," . $this->var2str($this->coddocumento_mod)
                . "," . $this->var2str($this->numero_documento_mod)
                . "," . $this->var2str($this->nro_autorizacion_mod)
                . "," . $this->var2str($this->fecdoc_modificado)
                . "," . $this->var2str($this->coddocumento_ret)
                . "," . $this->var2str($this->nro_autorizacion_ret)
                . "," . $this->var2str($this->estado_sri_ret)
                . "," . $this->var2str($this->fec_autorizacion_ret)
                . "," . $this->var2str($this->hor_autorizacion_ret)
                . "," . $this->var2str($this->anulado)
                . "," . $this->var2str($this->saldoinicial)
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
                        $this->idfacturaprov = $this->db->lastval();
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
        $fp0   = new \formaspago();
        $pago0 = new \trans_pagos();
        //Si ya existe lo elimino
        if ($this->coddocumento == '04') {
            $pago = $pago0->get_pago_ncc($this->idfacturaprov);
        } else {
            $pago = $pago0->get_pago_fact($this->idfacturaprov);
        }
        if ($pago) {
            if (!$pago->delete()) {
                $this->new_error_msg('Error al eliminar el pago asociado al Documento.');
                return false;
            } else {
                $pago = $pago0->get_pago_fact($this->idfacturaprov);
                if ($pago) {
                    if (!$pago->delete()) {
                        $this->new_error_msg('Error al eliminar el pago asociado al Documento.');
                        return false;
                    }
                }
            }
        }

        if (!$this->anulado) {
            $pago              = new \trans_pagos();
            $pago->idempresa   = $this->idempresa;
            $pago->idproveedor = $this->idproveedor;
            if ($this->coddocumento == '04') {
                $fp = $fp0->get_notacredito($this->idempresa);
                if ($fp) {
                    $pago->idformapago = $fp->idformapago;
                } else {
                    $this->new_error_msg('Forma de Pago Nota de Crédito no encontrada.');
                    return false;
                }
                if ($this->idfactura_mod) {
                    $pago->idfacturaprov = $this->idfactura_mod;
                } else {
                    $pago->idfacturaprov = $this->idfacturaprov;
                }
                $pago->idfacturanc = $this->idfacturaprov;
                $pago->debito      = $this->total;
                $pago->esabono     = true;
            } else {
                $fp = $fp0->get_credito($this->idempresa);
                if ($fp) {
                    $pago->idformapago = $fp->idformapago;
                } else {
                    $this->new_error_msg('Forma de Pago Crédito no encontrada.');
                    return false;
                }
                $pago->idfacturaprov = $this->idfacturaprov;
                $pago->credito       = $this->total;
            }
            $pago->fecha_trans   = $this->fec_emision;
            $pago->tipo          = $this->get_tipodoc();
            $pago->fec_creacion  = date('Y-m-d');
            $pago->nick_creacion = $this->nick_creacion;

            if (!$pago->save()) {
                $this->new_error_msg('Error al guardar el pago.');
                return false;
            }

            //Si tiene Retencion genero el tipo de retencion
            if ($this->numero_retencion) {
                $val_ret = $this->totalret();
                //Si es mayor a 0 la retencion genero
                if ($val_ret > 0) {
                    $fp = $fp0->get_retencion($this->idempresa);
                    if ($fp) {
                        $pago                = new \trans_pagos();
                        $pago->idempresa     = $this->idempresa;
                        $pago->idproveedor   = $this->idproveedor;
                        $pago->idfacturaprov = $this->idfacturaprov;
                        $pago->idformapago   = $fp->idformapago;
                        $pago->tipo          = 'Retencion';
                        $pago->fecha_trans   = $this->fec_registro;
                        $pago->debito        = $val_ret;
                        $pago->fec_creacion  = date('Y-m-d');
                        $pago->nick_creacion = $this->nick_creacion;
                        if (!$pago->save()) {
                            $this->new_error_msg('Error al guardar el pago de la Retencion.');
                        }
                    } else {
                        $this->new_error_msg("Forma de Pago Retencion no Encontrada.");
                    }
                }
            }
        }

        return true;
    }

    public function delete()
    {
        if ($this->beforeDelete()) {
            return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idfacturaprov = " . $this->var2str($this->idfacturaprov) . ";");
        }

        return false;
    }

    public function all()
    {
        $list = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY razonsocial DESC;");
        if ($data) {
            foreach ($data as $p) {
                $list[] = new \facturasprov($p);
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
                $list[] = new \facturasprov($p);
            }
        }

        return $list;
    }

    public function search_facturasprov($idempresa = '', $query = '', $idproveedor = '', $fechadesde = '', $fechahasta = '', $iddocumento = '', $sanuladas = false, $anuladas = false, $saldosini = false, $autorizadas = false, $sinautorizar = false, $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        if ($idempresa != '') {
            $sql .= " AND idempresa = " . $this->var2str($idempresa);
        }
        if ($idproveedor != '') {
            $sql .= " AND idproveedor = " . $this->var2str($idproveedor);
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
            $sql .= " AND (estado_sri = " . $this->var2str('AUTORIZADO') . " OR (estado_sri_ret = " . $this->var2str('AUTORIZADO') . " AND numero_retencion IS NOT NULL)) AND anulado != " . $this->var2str(true) . " AND saldoinicial != " . $this->var2str(true);
        }
        if ($sinautorizar) {
            $sql .= " AND (estado_sri != " . $this->var2str('AUTORIZADO') . " OR (estado_sri_ret != " . $this->var2str('AUTORIZADO') . " AND numero_retencion IS NOT NULL)) AND anulado != " . $this->var2str(true) . " AND saldoinicial != " . $this->var2str(true);
        }
        if ($query != '') {
            $query = strtolower($query);
            $sql .= " AND (lower(numero_documento) LIKE '%" . $query . "%' OR lower(nro_autorizacion) LIKE '%" . $query . "%' OR lower(numero_retencion) LIKE '%" . $query . "%' OR lower(observaciones) LIKE '%" . $query . "%')";
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
                    $list[] = new \facturasprov($p);
                }
            }
        }

        return $list;
    }

    public function get_by_idproveedor($idempresa, $idproveedor)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND idproveedor = " . $this->var2str($idproveedor) . ";");
        if ($data) {
            return new \facturasprov($data[0]);
        }

        return false;
    }

    public function get_tipodoc()
    {
        $documento = new \documentos();
        $doc       = $documento->get($this->iddocumento);
        if ($doc) {
            return $doc->nombre;
        }

        return '-';
    }

    public function get_sustento()
    {
        $sustento = new \sustentos();
        $sust     = $sustento->get($this->idsustento);
        if ($sust) {
            return $sust->codigo . " - " . $sust->nombre;
        }

        return '-';
    }

    public function get_codsustento()
    {
        $sustento = new \sustentos();
        $sust     = $sustento->get($this->idsustento);
        if ($sust) {
            return $sust->codigo;
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
        }

        if ($this->estado_sri == 'AUTORIZADO') {
            $this->new_error_msg("El documento se encuentra autorizado no es posible eliminar.");
            return false;
        }
        if ($this->estado_sri_ret == 'AUTORIZADO') {
            $this->new_error_msg("La retencion del documento se encuentra autorizada no es posible eliminar.");
            return false;
        }
        //valido si no tiene pagos
        $pagos0 = new \trans_pagos();
        $pagos  = $pagos0->all_by_idfacturaprov($this->idfacturaprov, true);
        if (count($pagos) > 1) {
            $this->new_error_msg("Tiene Pagos Asociados al Documento, no se puede eliminar.");
            return false;
        } else {
            $pagos = $pagos0->all_by_idfacturaprov($this->idfacturaprov);
            $paso  = true;
            foreach ($pagos as $key => $p) {
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

    public function listado_facturas($idempresa, $fec_desde = false, $fec_hasta = false, $idestablecimiento = false, $idproveedor = false, $iddocumento = false, $sanuladas = false)
    {
        $lista = array();

        $sql = "SELECT d.iddocumento, d.codigo, d.nombre, f.numero_documento, f.observaciones, f.fec_emision, f.identificacion, f.nro_autorizacion, f.razonsocial, e.nombre AS establecimiento, f.base_gra, f.base_0, f.base_noi, f.base_exc, f.totaldescuento, f.totalice, f.totaliva, f.totalirbp, f.total FROM " . $this->table_name . " f INNER JOIN documentos d ON d.iddocumento = f.iddocumento INNER JOIN establecimiento e ON e.idestablecimiento = f.idestablecimiento WHERE 1 = 1";
        if ($fec_desde) {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND f.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idestablecimiento) {
            $sql .= " AND f.idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($idproveedor) {
            $sql .= " AND f.idproveedor = " . $this->var2str($idproveedor);
        }
        if ($iddocumento) {
            $sql .= " AND f.iddocumento = " . $this->var2str($iddocumento);
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

    public function listado_facturas_ats($idempresa, $fec_desde = false, $fec_hasta = false)
    {
        $lista = array();

        $sql = "SELECT * FROM " . $this->table_name . " f WHERE anulado != " . $this->var2str(true) . " AND saldoinicial != " . $this->var2str(true);
        if ($fec_desde) {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND f.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idempresa) {
            $sql .= " AND f.idempresa = " . $this->var2str($idempresa);
        }

        $sql .= " ORDER BY f.iddocumento, f.fec_emision, f.razonsocial ASC";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $lista[] = new \facturasprov($p);
            }
        }

        return $lista;
    }

    public function buscar_factura_proveedor($idempresa, $query, $idproveedor)
    {
        $lista = array();

        $sql  = "SELECT * FROM " . $this->table_name . " WHERE anulado != " . $this->var2str(true) . " AND coddocumento = " . $this->var2str('01') . " AND idempresa = " . $this->var2str($idempresa) . " AND numero_documento LIKE '%" . $query . "%' AND idproveedor = " . $this->var2str($idproveedor) . " ORDER BY fec_emision ASC;";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $p) {
                $lista[] = new \facturasprov($p);
            }
        }
        return $lista;
    }

    private function generar_numero_documento()
    {
        if ($est = $this->get_establecimiento()) {
            $establecimiento  = str_pad($est->codigo, 3, "0", STR_PAD_LEFT);
            $ptoemision       = str_pad($est->ptoemision, 3, "0", STR_PAD_LEFT);
            $numliq           = str_pad($est->numliq, 9, "0", STR_PAD_LEFT);
            $numero_documento = $establecimiento . "-" . $ptoemision . "-" . $numliq;
            //pongo el numero de liquidacion
            $est->numliq++;
            $est->save();
            //Genero la clave de acceso de la liquidacion de compra
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

    private function generar_numero_retencion()
    {
        if ($est = $this->get_establecimiento()) {
            $establecimiento  = str_pad($est->codigo, 3, "0", STR_PAD_LEFT);
            $ptoemision       = str_pad($est->ptoemision, 3, "0", STR_PAD_LEFT);
            $numret           = str_pad($est->numret, 9, "0", STR_PAD_LEFT);
            $numero_retencion = $establecimiento . "-" . $ptoemision . "-" . $numret;
            //pongo el numero de liquidacion
            $est->numret++;
            $est->save();
            //Genero la clave de acceso de la Retencion de compra
            $nro_autorizacion = $this->generarNumeroAutRetencion($numero_retencion);
            if ($this->validar_numero_retencion($numero_retencion)) {
                $this->generar_numero_retencion();
            } else {
                $this->numero_retencion     = $numero_retencion;
                $this->nro_autorizacion_ret = $nro_autorizacion;
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
            //Tipo de Comprobante 03
            $tipoComp = '03';
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

    private function generarNumeroAutRetencion($numero_retencion)
    {
        if ($emp = $this->get_empresa()) {
            //Fecha
            $fechadoc = str_replace("-", "", date('d-m-Y', strtotime($this->fec_registro)));
            //Tipo de Comprobante 07 Retencion
            $tipoComp = '07';
            //Ruc Empresa
            $rucEmp = $emp->ruc;
            //Ambiente Pruebas o Produccion
            if ($emp->produccion) {
                $ambiente = '2';
            } else {
                $ambiente = '1';
            }
            //Numero de Documento
            $numDoc = str_replace("-", "", $numero_retencion);
            return generarClaveAcceso($fechadoc, $tipoComp, $rucEmp, $ambiente, $numDoc);
        }
        return '';
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

    private function get_empresa()
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

    private function validar_numero($numero_documento)
    {
        $sql  = 'SELECT * FROM ' . $this->table_name . ' WHERE iddocumento = ' . $this->var2str($this->iddocumento) . ' AND idempresa = ' . $this->var2str($this->idempresa) . " AND numero_documento = " . $this->var2str($numero_documento);
        $data = $this->db->select($sql);
        if ($data) {
            return true;
        }

        return false;
    }

    private function validar_numero_retencion($numero_retencion)
    {
        $sql  = 'SELECT * FROM ' . $this->table_name . ' WHERE idempresa = ' . $this->var2str($this->idempresa) . " AND numero_retencion = " . $this->var2str($numero_retencion);
        $data = $this->db->select($sql);
        if ($data) {
            return true;
        }

        return false;
    }

    public function totalret()
    {
        $valor = 0;

        foreach ($this->get_retencion() as $key => $ret) {
            $valor += floatval($ret['valor']);
        }

        $valor = round($valor, 2);
        return $valor;
    }

    public function get_retencion()
    {
        $tipo_ret  = new \tiposretenciones();
        $retencion = array();
        $val_ice   = 0;
        foreach ($this->getlineas() as $key => $l) {
            $encontrado_iva = false;
            $encontrado_rta = false;
            foreach ($retencion as $key => $r) {
                if ($r['idretencion'] == $l->idretencion_iva) {
                    //Retenciones de IVA
                    $encontrado_iva = true;
                    $retencion[$key]['baseimp'] += $l->pvptotal * ($l->get_porcentaje_impuesto() / 100);
                }
                if ($r['idretencion'] == $l->idretencion_renta) {
                    //Retenciones de Renta
                    $encontrado_rta = true;
                    $retencion[$key]['baseimp'] += $l->pvptotal;
                }
            }
            //Acumulo el Valor del ICE para aumentarle al codigo 0
            $val_ice += $l->valorice * ($l->get_porcentaje_impuesto() / 100);
            //Si no encuentra se crea
            if (!$encontrado_iva) {
                $ret = $tipo_ret->get($l->idretencion_iva);
                if ($ret) {
                    $retencion[] = array(
                        'idretencion' => $l->idretencion_iva,
                        'comprobante' => $this->get_tipodoc(),
                        'numero'      => str_replace("-", "", $this->numero_documento),
                        'fecha'       => date('d/m/Y', strtotime($this->fec_registro)),
                        'ejercicio'   => date('m/Y', strtotime($this->fec_registro)),
                        'baseimp'     => $l->pvptotal * ($l->get_porcentaje_impuesto() / 100),
                        'especie'     => 'iva',
                        'codigo'      => $ret->codigo,
                        'codigobase'  => $ret->codigobase,
                        'porcentaje'  => $ret->porcentaje,
                        'valor'       => 0,
                    );
                }
            }
            if (!$encontrado_rta) {
                $ret = $tipo_ret->get($l->idretencion_renta);
                if ($ret) {
                    $retencion[] = array(
                        'idretencion' => $l->idretencion_renta,
                        'comprobante' => $this->get_tipodoc(),
                        'numero'      => str_replace("-", "", $this->numero_documento),
                        'fecha'       => date('d/m/Y', strtotime($this->fec_registro)),
                        'ejercicio'   => date('m/Y', strtotime($this->fec_registro)),
                        'baseimp'     => $l->pvptotal,
                        'especie'     => 'renta',
                        'codigo'      => $ret->codigo,
                        'codigobase'  => $ret->codigobase,
                        'porcentaje'  => $ret->porcentaje,
                        'valor'       => 0,
                    );
                }
            }
        }

        //recorro las retenciones para realizar el calculo del valor retenido
        $paso_ice = true;
        foreach ($retencion as $key => $ret) {
            if ($ret['porcentaje'] == 0 && $ret['especie'] == 'iva') {
                $paso_ice = false;
                $retencion[$key]['baseimp'] += $val_ice;
                $retencion[$key]['valor']   = round($retencion[$key]['baseimp'] * ($retencion[$key]['porcentaje'] / 100), 2);
                $retencion[$key]['baseimp'] = round($retencion[$key]['baseimp'], 2);
            } else {
                $retencion[$key]['valor']   = round($retencion[$key]['baseimp'] * ($retencion[$key]['porcentaje'] / 100), 2);
                $retencion[$key]['baseimp'] = round($retencion[$key]['baseimp'], 2);
            }
        }

        if ($paso_ice && $val_ice > 0) {
            $ret0iva = $tipo_ret->get_retiva0();
            if ($ret0iva) {
                $retencion[] = array(
                    'idretencion' => $ret0iva->idtiporetencion,
                    'comprobante' => $this->get_tipodoc(),
                    'numero'      => str_replace("-", "", $this->numero_documento),
                    'fecha'       => date('d/m/Y', strtotime($this->fec_registro)),
                    'ejercicio'   => date('m/Y', strtotime($this->fec_registro)),
                    'baseimp'     => $val_ice,
                    'especie'     => 'iva',
                    'codigo'      => $ret0iva->codigo,
                    'codigobase'  => $ret0iva->codigobase,
                    'porcentaje'  => $ret0iva->porcentaje,
                    'valor'       => 0,
                );
            }
        }

        return $retencion;
    }

    public function getDocumentoProveedor($idempresa, $identificacion, $coddocumento, $numero_documento)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . " AND identificacion = " . $this->var2str($identificacion) . " AND coddocumento = " . $this->var2str($coddocumento) . " AND numero_documento = " . $this->var2str($numero_documento) . ";");
        if ($data) {
            return new \facturasprov($data[0]);
        }

        return false;
    }

    public function detalle_facturas($idempresa, $fec_desde = false, $fec_hasta = false, $idestablecimiento = false, $idproveedor = false, $iddocumento = false, $item = 1, $sanuladas = false)
    {
        $lista = array();

        $sql = "SELECT f.numero_documento, d.codigo, d.nombre AS nombredoc, f.fec_emision, f.identificacion, f.nro_autorizacion, f.razonsocial, ar.codprincipal, ar.nombre, l.cantidad, l.pvpunitario, round((l.cantidad * l.pvpunitario) - l.pvptotal, 6) AS dto, l.pvptotal, round(l.valorice + l.valorirbp, 6) AS otrosimp, l.valoriva, i.porcentaje FROM lineasfacturasprov l INNER JOIN " . $this->table_name . " f ON f.idfacturaprov = l.idfacturaprov INNER JOIN articulos ar ON ar.idarticulo = l.idarticulo INNER JOIN documentos d ON d.iddocumento = f.iddocumento INNER JOIN impuestos i ON i.idimpuesto = l.idimpuesto WHERE 1 = 1";

        if ($fec_desde) {
            $sql .= " AND f.fec_emision >= " . $this->var2str($fec_desde);
        }
        if ($fec_hasta) {
            $sql .= " AND f.fec_emision <= " . $this->var2str($fec_hasta);
        }
        if ($idestablecimiento) {
            $sql .= " AND f.idestablecimiento = " . $this->var2str($idestablecimiento);
        }
        if ($idproveedor) {
            $sql .= " AND f.idproveedor = " . $this->var2str($idproveedor);
        }
        if ($iddocumento) {
            $sql .= " AND f.iddocumento = " . $this->var2str($iddocumento);
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

        $sql = "SELECT coddocumento, count(idfacturaprov) AS contador FROM facturasprov WHERE anulado != " . $this->var2str(true) . " AND idempresa = " . $this->var2str($idempresa) . " AND fec_emision >= " . $this->var2str($fec_desde) . " AND fec_emision <= " . $this->var2str($fec_hasta) . " AND coddocumento NOT IN ('04') GROUP BY coddocumento";

        $data = $this->db->select($sql);
        if ($data) {
            return $data;
        }

        return $list;
    }

    public function anular_documento($nick)
    {
        if ($this->numero_retencion) {
            //valido que sea electronica
            if (strlen($this->nro_autorizacion_ret) == 49) {
                $autorizacion = consultardocsri($this->nro_autorizacion_ret);
                if ($autorizacion['encontrado']) {
                    $this->new_error_msg('La Retencion del documento se encuentra autorizada, anule en el SRI y vuelva a intentarlo.');
                    return false;
                }
            }
        }

        if ($this->coddocumento == '03') {
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
        $pagos0 = new \trans_pagos();
        $pagos  = $pagos0->all_by_idfacturaprov($this->idfacturaprov, true);
        if (count($pagos) > 1) {
            $this->new_error_msg("Tiene pagos Asociados al Documento, no se puede anular.");
            return false;
        }

        $this->anulado           = true;
        $this->nick_modificacion = $nick;
        $this->fec_modificacion  = date('Y-m-d');

        if (!$this->save()) {
            $this->new_advice('No se puede actualizar la anulación, en la cabecera del documento.');
            return false;
        } else {
            //actualizo las lineas para reversar el stock
            foreach ($this->getlineas() as $key => $l) {
                $l->save(false);
            }
            //Si esta autorizada la retencion la anulo en la tabla de anulaciones
            if ($this->numero_retencion && $this->estado_sri_ret == 'AUTORIZADO') {
                $this->generar_registro_anulacion('03', $this->numero_retencion, $this->nro_autorizacion_ret, $this->fec_autorizacion_ret, $this->hor_autorizacion_ret);
            }
            //Si es liquidacion de compra autorizada como anulada
            if ($this->coddocumento == '03' && $this->estado_sri == 'AUTORIZADO') {
                $this->generar_registro_anulacion('07', $this->numero_documento, $this->nro_autorizacion, $this->fec_autorizacion, $this->hor_autorizacion);
            }
        }

        return true;
    }

    public function anular_retencion($nick)
    {
        if ($this->estado_sri_ret != 'AUTORIZADO') {
            $this->new_error_msg('La Retencion no se encuentra autorizado no es necesario anular.');
            return false;
        } else if ($this->numero_retencion) {
            //valido que sea electronica
            if (strlen($this->nro_autorizacion_ret) == 49) {
                $autorizacion = consultardocsri($this->nro_autorizacion_ret);
                if ($autorizacion['encontrado']) {
                    $this->new_error_msg('La Retencion del documento se encuentra autorizada, anule en el SRI y vuelva a intentarlo.');
                    return false;
                }
            }
        }

        $numero_retencion     = $this->numero_retencion;
        $nro_autorizacion_ret = $this->nro_autorizacion_ret;
        $fec_autorizacion_ret = $this->fec_autorizacion_ret;
        $hor_autorizacion_ret = $this->hor_autorizacion_ret;
        $estado_sri_ret       = $this->estado_sri_ret;

        $this->numero_retencion     = null;
        $this->nro_autorizacion_ret = null;
        $this->fec_autorizacion_ret = null;
        $this->hor_autorizacion_ret = null;
        $this->estado_sri_ret       = 'PENDIENTE';
        $this->nick_modificacion    = $nick;
        $this->fec_modificacion     = date('Y-m-d');

        $this->generar_numero_retencion();

        if (!$this->save()) {
            $this->new_advice('No se puede actualizar la anulación, en la cabecera del documento.');
            return false;
        } else {
            //Guardo las retenciones anuladas en la tabla generada
            $this->generar_registro_anulacion('03', $numero_retencion, $nro_autorizacion_ret, $fec_autorizacion_ret, $hor_autorizacion_ret);
        }

        return true;
    }

    private function generar_registro_anulacion($coddocumento, $numero_documento, $nro_autorizacion, $fec_autorizacion, $hor_autorizacion)
    {
        $anulados                   = new \documentos_anulados();
        $anulados->idempresa        = $this->idempresa;
        $anulados->idproveedor      = $this->idproveedor;
        $anulados->coddocumento     = $coddocumento;
        $anulados->nro_autorizacion = $nro_autorizacion;
        $anulados->numero_documento = $numero_documento;
        $anulados->fec_autorizacion = $fec_autorizacion;
        $anulados->hor_autorizacion = $hor_autorizacion;
        $anulados->idfacturaprov    = $this->idfacturaprov;
        $anulados->fec_creacion     = date('Y-m-d');
        $anulados->nick_creacion    = $this->nick_modificacion;

        if (!$anulados->save()) {
            $this->new_error_msg('Error al generar el registro de la anulación.');
        }
    }
}
