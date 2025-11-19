<?php
/**
 * Controlador para impresiones de tickets del sistema
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class impresion_tickets extends controller
{
    public function __construct()
    {
        parent::__construct(__CLASS__, 'Tickets', 'Ventas', false, false);
    }

    protected function private_core()
    {
        $this->parametros    = new \parametrizacion();
        //busco la parametrizacion 
        $this->sizetit = 25;
        $stitleparam         = $this->parametros->all_by_codigo($this->empresa->idempresa, 'sizetit');
        if ($stitleparam) {
            $this->sizetit = $stitleparam->valor;
        }

        $this->sizeticket = 12;
        $sticketparam         = $this->parametros->all_by_codigo($this->empresa->idempresa, 'sizeticket');
        if ($sticketparam) {
            $this->sizeticket = $sticketparam->valor;
        }

        $this->facturascli    = new \facturascli();
        $this->titulo         = '';
        $this->datos_empresa  = '';
        $this->datos_ticket   = '';
        $this->datos_cliente  = '';
        $this->detalle_fac    = '';
        $this->datos_cierre   = '';
        $this->totales        = '';
        $this->agradecimiento = '';
        if (isset($_GET['facturacli'])) {
            $documento = $this->facturascli->get($_GET['facturacli']);
            if ($documento) {
                if ($documento->idempresa == $this->empresa->idempresa) {
                    $this->impresion_ticket_factura($documento);
                } else {
                    $this->new_advice("El Documento no esta disponible para su empresa.");
                    return;
                }
            } else {
                $this->new_advice('Documento No Encontrada.');
            }
        } else if (isset($_GET['movimiento'])) {
            $movs       = new mov_cajas();
            $movimiento = $movs->get($_GET['movimiento']);
            if ($movimiento) {
                if ($movimiento->idempresa == $this->empresa->idempresa) {
                    $this->impresion_ticket_movimiento($movimiento);
                } else {
                    $this->new_advice("El movimiento no esta disponible para su empresa.");
                    return;
                }
            } else {
                $this->new_advice('movimiento de Caja no Encontrado.');
            }
        } else if (isset($_GET['cierre'])) {
            $cierre0 = new cierres();
            $cierre  = $cierre0->get($_GET['cierre']);
            if ($cierre) {
                if ($cierre->idempresa == $this->empresa->idempresa) {
                    $this->impresion_cierre_caja($cierre);
                } else {
                    $this->new_advice("El cierre de caja no esta disponible para su empresa.");
                    return;
                }
            } else {
                $this->new_advice('Cierre de Caja No Encontrado.');
            }
        }
    }

    private function impresion_ticket_factura($factura)
    {
        //Lleno el titulo del ticket
        $this->titulo .= $this->empresa->nombrecomercial;
        //Leno los datos de la empresa
        //$this->datos_empresa .= $this->empresa->razonsocial."<br>";
        $this->datos_empresa .= "<b>RUC: </b>" . $this->empresa->ruc . "<br>";
        $this->datos_empresa .= "<b>Dirección: </b>" . $this->empresa->direccion . "<br>";
        $this->datos_empresa .= "<b>Teléfono: </b>" . $this->empresa->telefono . "<br>";
        $this->datos_empresa .= $this->empresa->email . "<br>";
        $this->datos_empresa .= strtoupper($factura->get_regimen_empresa()) . "<br>";
        if ($factura->obligado_empresa) {
            $this->datos_empresa .= "<b>Obligado a llevar Contabilidad</b><br>";
        }
        if ($factura->agretencion_empresa) {
            $this->datos_empresa .= "<b>Agente de Retención Resolución No. 1</b><br>";
        }
        if (isset($_GET['reimprimir'])) {
            $this->datos_ticket .= "<center><b>**** REIMPRESIÓN ****</b></center>";
        }
        if ($this->empresa->activafacelec) {
            $this->datos_ticket .= "<center><b>FACTURA ELECTRÓNICA</b></center>";
            $this->datos_ticket .= "<center>***DOCUMENTO SIN VALOR TRIBUTARIO***</center>";
            if ($factura->nro_autorizacion) {
                $this->datos_ticket .= "<center><b>Número Autorización</b></center>";
                $this->datos_ticket .= "<center>" . substr($factura->nro_autorizacion, 0, 39) . "</center>";
                $this->datos_ticket .= "<center>" . substr($factura->nro_autorizacion, 39) . "</center>";
            }
        }
        $this->datos_ticket .= "<center><b>Nro: " . $factura->numero_documento . "</b></center>";
        $this->datos_cliente .= "<b>Fecha Emisión: </b>" . $factura->fec_emision . "<br>";
        $this->datos_cliente .= "<b>Cliente: </b>" . $factura->razonsocial . "<br>";
        $this->datos_cliente .= "<b>Identificación: </b>" . $factura->identificacion . "<br>";
        $this->datos_cliente .= "<b>Telefono: </b>" . $factura->get_cliente()->telefono . "<br>";
        $this->datos_cliente .= "<b>Dirección: </b>" . $factura->direccion . "<br>";
        $this->datos_cliente .= "<b>Usuario: </b>" . $factura->nick_creacion . "<br>";

        $this->detalle_fac = $factura->getlineas();
        $subtotal          = $factura->base_gra + $factura->base_0 + $factura->base_noi + $factura->base_exc;
        $this->totales .= "<b>SUBTOTAL: </b>" . show_precio($subtotal) . "<br>";
        $this->totales .= "<b>DESCUENTO: </b>" . show_precio($factura->totaldescuento) . "<br>";
        $this->totales .= "<b>IVA: </b>" . show_precio($factura->totaliva) . "<br>";
        $this->totales .= "<b>TOTAL: </b>" . show_precio($factura->total) . "<br>";

        //Agradecimientos
        if ($this->empresa->activafacelec) {
            $this->agradecimiento .= "Su factura electrónica será enviada al siguiente<br>";
            $this->agradecimiento .= "correo: ".$factura->email."<br><br>";
        }
        $this->agradecimiento .= "** GRACIAS POR SU COMPRA **<br>";
        $this->agradecimiento .= "<b>Powered by<br> &reg; GSC_Systems, " . date('Y') . "</b>";
    }

    private function impresion_ticket_movimiento($movimiento)
    {
        //Lleno el titulo del ticket
        $this->titulo .= $this->empresa->nombrecomercial;

        if (isset($_GET['reimprimir'])) {
            $this->datos_ticket .= "<center><b>**** REIMPRESIÓN ****</b></center>";
        }

        $this->datos_ticket .= "<center><b>" . ucfirst($movimiento->tipo) . " de Caja</b></center><br>";
        //Busco la Caja
        $this->datos_ticket .= "<center><b>" . $movimiento->get_caja() . "</b></center><br>";
        $this->datos_ticket .= "<b>Fecha: </b>" . $movimiento->fec_creacion . "<br>";
        $this->datos_ticket .= "<b>Usuario: </b>" . $movimiento->nick_creacion . "<br>";
        $this->datos_ticket .= "<b>Detalle: </b>" . $movimiento->nombre . "<br>";
        $this->datos_ticket .= "<b>Valor: </b>" . show_precio($movimiento->valor) . "<br>";
        $this->datos_ticket .= "<br>";
        $this->datos_ticket .= "<br>";
        $this->datos_ticket .= "<center>_______________________</center><br>";
        $this->datos_ticket .= "<center><b>Firma</b></center><br>";

        $this->agradecimiento .= "<b>Powered by<br> &reg; GSC_Systems, " . date('Y') . "</b>";
    }

    private function impresion_cierre_caja($cierre)
    {
        $this->titulo .= $this->empresa->nombrecomercial;

        if (isset($_GET['reimprimir'])) {
            $this->datos_empresa .= "<center><b>**** REIMPRESIÓN ****</b></center><br>";
        }
        $this->datos_empresa .= "<center><b>CIERRE DE CAJA</b></center><br>";
        $this->datos_ticket .= "<b>Usuario: </b>" . $cierre->nick_creacion . "<br>";
        $this->datos_ticket .= "<b>Apertura: </b>" . $cierre->apertura . "<br>";
        $this->datos_ticket .= "<b>Cierre: </b>" . $cierre->cierre . "<br>";
        $this->datos_ticket .= "<b>Caja: </b>" . $cierre->get_caja() . "<br>";
        //Datos de Cierre
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'><b>Dinero Inicial: </b></td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'><b>" . show_precio($cierre->inicial) . "</b></td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr><td colspan='2'><center><b>DESGLOSE DE DINERO</b></center></td></tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Monedas 0.01: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->m001) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Monedas 0.05: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->m005) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Monedas 0.10: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->m010) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Monedas 0.25: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->m025) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Monedas 0.50: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->m050) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Monedas 1: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->m1) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Billetes 1: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->b1) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Billetes 5: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->b5) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Billetes 10: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->b10) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Billetes 20: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->b20) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Billetes 50: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->b50) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Billetes 100: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_numero($cierre->b100) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'><b>Caja Empleado: </b></td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'><b>" . show_precio($cierre->totalemp) . "</b></td>";
        $this->datos_cierre .= "</tr>";
        //Traigo el total de cobros
        $cobros0 = new trans_cobros();

        $cobros1 = $cobros0->getFormasCaja($this->empresa->idempresa, $cierre->idcierre);
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr><td colspan='2'><center><b>DETALLE DE VENTAS</b></center></td></tr>";
        $this->datos_cierre .= "<tr>";
        $credito  = 0;
        $cobros   = 0;
        $efectivo = 0;
        foreach ($cobros1 as $key => $c) {
            if ($this->str2bool($c['escredito'])) {
                $credito += floatval($c['total']);
            } else if ($this->str2bool($c['esefec'])) {
                $efectivo += floatval($c['cobros']);
                $cobros += floatval($c['cobros']);
                $this->datos_cierre .= "<tr>";
                $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>" . $c['nombre'] . ": </td>";
                $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_precio($c['cobros']) . "</td>";
                $this->datos_cierre .= "</tr>";
            } else {
                $cobros += floatval($c['cobros']);
                $this->datos_cierre .= "<tr>";
                $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>" . $c['nombre'] . ": </td>";
                $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_precio($c['cobros']) . "</td>";
                $this->datos_cierre .= "</tr>";
            }
        }

        $cred = round($credito - $cobros, 2);
        if ($cred > 0) {
            $this->datos_cierre .= "<tr>";
            $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Crédito: </td>";
            $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_precio($cred) . "</td>";
            $this->datos_cierre .= "</tr>";
        }

        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'><b>Total de Ventas: </b></td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'><b>" . show_precio($credito) . "</b></td>";
        $this->datos_cierre .= "</tr>";
        $mov_cajas = new mov_cajas();
        $movs      = $mov_cajas->get_by_idempresa_caja($this->empresa->idempresa, $cierre->idcierre);
        $totalmov  = 0;
        if ($movs) {
            $this->datos_cierre .= "</tr>";
            $this->datos_cierre .= "<tr><td colspan='2'><center><b>DETALLE DE MOVIMIENTOS</b></center></td></tr>";
            $this->datos_cierre .= "<tr>";
            foreach ($movs as $key => $m) {
                $val = $m->valor;
                if ($m->tipo == 'egreso') {
                    $val = $val * -1;
                }
                $totalmov += $val;
                $this->datos_cierre .= "<tr>";
                $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>" . $m->nombre . ": </td>";
                $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_precio($val) . "</td>";
                $this->datos_cierre .= "</tr>";
            }

            $this->datos_cierre .= "<tr>";
            $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'><b>Total Movimientos</b>: </td>";
            $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'><b>" . show_precio($totalmov) . "</b></td>";
            $this->datos_cierre .= "</tr>";
        }
        $totalsis = $cierre->inicial;
        $totalsis += $efectivo;
        $totalsis += $totalmov;
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr><td colspan='2'><center><b>RESUMEN DE CAJA</b></center></td></tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Inicial: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_precio($cierre->inicial) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Efectivo: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_precio($efectivo) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'>Movimientos: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'>" . show_precio($totalmov) . "</td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'><b>Cierre Sistema</b>: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'><b>" . show_precio($totalsis) . "</b></td>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'><b>Cierre Empleado</b>: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'><b>" . show_precio($cierre->totalemp) . "</b></td>";
        $this->datos_cierre .= "</tr>";
        $dif = round($cierre->totalemp - $totalsis, 2);
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "<td style='text-align: left; width: 150px; max-width: 150px; word-break: break-all;'><b>Diferencia (Emp - Sis)</b>: </td>";
        $this->datos_cierre .= "<td style='text-align: right; width: 100px; max-width: 100px; word-break: break-all'><b>" . show_precio($dif) . "</b></td>";
        $this->datos_cierre .= "</tr>";

        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr><td colspan='2'><br></td></tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr><td colspan='2'><br></td></tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr><td colspan='2'><br></td></tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr><td colspan='2'><center><b>__________________________________</b></center></td></tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr><td colspan='2'><center><b>Firma Responsable</b></center></td></tr>";
        $this->datos_cierre .= "<tr>";
        $this->datos_cierre .= "</tr>";
        $this->datos_cierre .= "<tr><td colspan='2'><br></td></tr>";
        $this->datos_cierre .= "<tr>";

        $this->agradecimiento .= "<b>Powered by<br> &reg; GSC_Systems, " . date('Y') . "</b>";

    }

    private function str2bool($val)
    {
        return ($val == 't' || $val == '1');
    }
}
