<?php
require_once 'extras/fpdf/PDF.php';
/**
 * Controlador para impresiones del modulo de Ventas
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class impresion_ventas extends controller
{
    public $facturascli;
    public $trans_cobros;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Impresiones Ventas', 'Ventas', false, false);
    }

    protected function private_core()
    {
        $documento          = false;
        $this->facturascli  = new \facturascli();
        $this->guiascli     = new \guiascli();
        $this->trans_cobros = new \trans_cobros();
        if (class_exists('cab_cobros')) {
            $this->cab_cobros = new \cab_cobros();
        }

        if (class_exists('anticiposcli')) {
            $this->anticiposcli = new \anticiposcli();
        }

        if (class_exists('cab_devolucion_cliente')) {
            $this->cab_devolucion_cliente = new \cab_devolucion_cliente();
        }

        if (isset($_GET['id']) && isset($_GET['tipo'])) {
            if (isset($_GET['guia'])) {
                $documento = $this->guiascli->get($_GET['id']);
            } else {
                $documento = $this->facturascli->get($_GET['id']);
            }
            if ($documento) {
                if ($documento->idempresa == $this->empresa->idempresa) {
                    if ($_GET['tipo'] == 'xml') {
                        $this->exportar_xml($documento, $this->empresa);
                    } else if ($_GET['tipo'] == 'fact' || $_GET['tipo'] == 'facta5') {
                        if ($_GET['tipo'] == 'facta5') {
                            $this->generar_pdf_factura($documento, $this->empresa, 'a5');
                        } else {
                            $this->generar_pdf_factura($documento, $this->empresa);
                        }
                    } else if ($_GET['tipo'] == 'ticket') {
                        $this->impresion_ticket($documento, $this->empresa);
                    } else {
                        $this->generar_pdf_electronico($documento, $this->empresa, $_GET['tipo']);
                    }
                } else {
                    $this->new_advice("El Documento no esta disponible para su empresa.");
                    $documento = false;
                    return;
                }
            } else {
                $this->new_advice('Documento No Encontrada.');
            }
        } else if (isset($_GET['cobro'])) {
            $cobro = $this->trans_cobros->get($_GET['id']);
            if ($cobro) {
                if ($cobro->idempresa == $this->empresa->idempresa) {
                    if ($cobro->idcobro) {
                        $cobro2 = $this->cab_cobros->get($cobro->idcobro);
                        if ($cobro2) {
                            $this->pdf_cobro_comprobante($cobro2, $this->empresa);
                        } else {
                            $this->new_advice("Cobro no Encontrado.");
                            return;
                        }
                    } else {
                        $this->pdf_cobro($cobro, $this->empresa);
                    }
                } else {
                    $this->new_advice("El Cobro no esta disponible para su empresa.");
                    $cobro = false;
                    return;
                }
            } else {
                $this->new_advice("Cobro no Encontrado.");
            }
        } else if (isset($_GET['imprimir_cobro'])) {
            $cobro = $this->cab_cobros->get($_GET['imprimir_cobro']);
            if ($cobro) {
                if ($cobro->idempresa == $this->empresa->idempresa) {
                    $this->pdf_cobro_comprobante($cobro, $this->empresa);
                } else {
                    $this->new_advice("El Cobro no esta disponible para su empresa.");
                    $cobro = false;
                    return;
                }
            } else {
                $this->new_advice("Cobro no Encontrado.");
                return;
            }
        } else if (isset($_GET['imprimir_anticipo'])) {
            $anticipo = $this->anticiposcli->get($_GET['imprimir_anticipo']);
            if ($anticipo) {
                if ($anticipo->idempresa == $this->empresa->idempresa) {
                    $this->pdf_anticipo_comprobante($anticipo, $this->empresa);
                } else {
                    $this->new_advice("El Anticipo no esta disponible para su empresa.");
                    $anticipo = false;
                    return;
                }
            } else {
                $this->new_advice("Anticipo no Encontrado.");
                return;
            }
        } else if (isset($_GET['imprimir_devol'])) {
            $devol = $this->cab_devolucion_cliente->get($_GET['imprimir_devol']);
            if ($devol) {
                if ($devol->idempresa == $this->empresa->idempresa) {
                    $this->pdf_comprobante_devolucion($devol, $this->empresa);
                } else {
                    $this->new_advice("El Pago no esta disponible para su empresa.");
                    $devol = false;
                    return;
                }
            } else {
                $this->new_advice("Pago no Encontrado.");
                return;
            }
        }
    }

    public function impresion_ticket($documento, $empresa)
    {
        if ($documento->anulado) {
            $this->new_advice('El documento se encuentra anulado.');
            return true;
        }
        if ($documento->coddocumento == '01' or $documento->coddocumento == '02') {
            echo "<script>window.open('index.php?page=impresion_tickets&facturacli=" . $documento->idfacturacli . "', '', 'width=300, height=200');</script>";
            header('Refresh: 0; URL=' . $documento->url());
        } else {
            $this->new_advice("El documento no corresponde a una Factura de Venta.");
        }
    }

    public function generar_pdf_electronico($documento, $empresa, $size = 'a4', $archivo = false)
    {
        if ($size == 'a5') {
            $pdf = new PDF('A5');
        } else {
            $pdf = new PDF();
        }
        $pdf->AliasNbPages();
        $pdf->AddPage();
        if ($documento->anulado) {
            $pdf->Header(false, true, $size);
        }
        $pdf->SetTextColor(0);
        $pdf->cabecera_docs_electronicos($documento, $empresa);
        if ($documento->coddocumento == '06') {
            $ypos = $pdf->GetY();
            $pdf->transporte_docs_electronicos($documento, $ypos);
            $ypos = $pdf->GetY();
            $pdf->clientetransporte_docs_electronicos($documento, $ypos);
        } else {
            $ypos = $pdf->GetY();
            $pdf->cliente_docs_electronicos($documento, $ypos);
        }
        $ypos = $pdf->GetY();
        $pdf->detfactura_docs_electronicos($documento, $ypos);
        $ypos = $pdf->GetY();
        if ($documento->coddocumento == '06') {
            if ($documento->tipo_guia == 2) {
                $pdf->subtotales_docs_electronicos($documento, $ypos);
            }
        } else {
            $pdf->subtotales_docs_electronicos($documento, $ypos);
        }
        $pdf->infoad_docs_electronicos($documento, $ypos);

        if ($archivo) {
            $pdf->save($archivo);
        } else {
            $pdf->show($documento->get_tipodoc() . "-" . $documento->numero_documento . '.pdf');
        }
    }

    public function generar_pdf_factura($documento, $empresa, $size = 'a4', $archivo = false)
    {
        if ($size == 'a5') {
            $pdf = new PDF('A5');
        } else {
            $pdf = new PDF();
        }
        $pdf->AliasNbPages();
        $pdf->AddPage();
        if ($documento->anulado) {
            $pdf->Header(false, true, $size);
        }
        $pdf->SetTextColor(0);
        if ($documento->coddocumento == '02' && JG_ECAUTE == 1) {
            $pdf->cabecera_ecaute($empresa, $documento->numero_documento);
        } else {
            $pdf->cabecera_empresa($empresa);
        }
        if ($documento->coddocumento == '02' && JG_ECAUTE == 1) {
            $ypos = $pdf->GetY();
            $pdf->cliente_docs_electronicos($documento, $ypos);
        } else {
            $pdf->datos_factura($documento);
        }
        $ypos = $pdf->GetY();
        $pdf->detfactura_docs_electronicos($documento, $ypos);
        $ypos = $pdf->GetY();
        $pdf->subtotales_docs_electronicos($documento, $ypos);
        if ($documento->coddocumento == '02' && JG_ECAUTE == 1) {
            $pdf->infoad_docs_electronicos($documento, $ypos);
        } else {
            $pdf->observaciones_factura($documento, $ypos);
        }

        if ($archivo) {
            $pdf->save($archivo);
        } else {
            $pdf->show($documento->get_tipodoc() . "-" . $documento->numero_documento . '.pdf');
        }
    }

    private function pdf_cobro($cobro, $empresa)
    {
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->cabecera_empresa($empresa);
        $pdf->datos_cobro($cobro);

        $pdf->show("Cobro -" . $cobro->idtranscobro . '.pdf');
    }

    private function pdf_cobro_comprobante($cobro, $empresa)
    {
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->cabecera_empresa($empresa);
        $pdf->datos_comprobante_cobro($cobro);

        $pdf->show("Cobro -" . $cobro->numero . '.pdf');
    }

    private function pdf_anticipo_comprobante($anticipo, $empresa)
    {
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->cabecera_empresa($empresa);
        $pdf->datos_comprobante_anticipocli($anticipo);

        $pdf->show("AnticipoCli -" . $anticipo->numero . '.pdf');
    }

    private function exportar_xml($documento, $empresa)
    {
        if ($documento->anulado) {
            $this->new_error_msg('El documento se encuentra anulado, no puede exportar el XML.');
            return;
        }

        $archivoFirmado = false;
        if ($documento->coddocumento == '01') {
            $rutaXmlFirmado = JG_MYDOCS . 'datosEmpresas/' . $empresa->idempresa . "/documentosElectronicos/facturas/autorizados/";
            $archivoFirmado = $rutaXmlFirmado . $documento->numero_documento . ".xml";
            $nombrearchivo  = $documento->coddocumento . "-" . $documento->numero_documento . '.xml';
        } else if ($documento->coddocumento == '06') {
            $rutaXmlFirmado = JG_MYDOCS . 'datosEmpresas/' . $empresa->idempresa . "/documentosElectronicos/guiasremision/autorizados/";
            $archivoFirmado = $rutaXmlFirmado . $documento->numero_documento . ".xml";
            $nombrearchivo  = $documento->coddocumento . "-" . $documento->numero_documento . '.xml';
        }
        if ($archivoFirmado) {
            if (file_exists($archivoFirmado)) {
                header("Content-disposition: attachment; filename=" . $nombrearchivo);
                header("Content-type: MIME");
                header("Content-Length: " . filesize($archivoFirmado));
                readfile($archivoFirmado);
            } else {
                $this->new_advice("Documento Firmado no Encontrado.");
            }
        } else {
            $this->new_advice("Documento Firmado no Encontrado.");
        }
    }

    private function pdf_comprobante_devolucion($devol, $empresa)
    {
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->cabecera_empresa($empresa);
        $pdf->datos_comprobante_devol_cli($devol);

        $pdf->show("Devolucion -" . $devol->numero . '.pdf');
    }
}
