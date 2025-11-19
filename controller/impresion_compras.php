<?php
require_once 'extras/fpdf/PDF.php';
/**
 * Controlador para impresiones del modulo de Compras
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class impresion_compras extends controller
{
    public $facturasprov;
    public $trans_pagos;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Impresiones Compras', 'Compras', false, false);
    }

    protected function private_core()
    {
        $documento          = false;
        $this->facturasprov = new \facturasprov();
        $this->trans_pagos  = new \trans_pagos();
        if (class_exists('cab_pagos')) {
            $this->cab_pagos = new \cab_pagos();
        }

        if (class_exists('anticiposprov')) {
            $this->anticiposprov = new \anticiposprov();
        }

        if (class_exists('cab_devolucion_proveedor')) {
            $this->cab_devolucion_proveedor = new \cab_devolucion_proveedor();
        }

        if (isset($_GET['id']) && isset($_GET['tipo'])) {
            $documento = $this->facturasprov->get($_GET['id']);
            if ($documento) {
                if ($documento->idempresa == $this->empresa->idempresa) {
                    if ($_GET['tipo'] == 'xml') {
                        $this->exportar_xml($documento, $this->empresa);
                    } else if ($_GET['tipo'] == 'xml_ret') {
                        $this->exportar_xml_ret($documento, $this->empresa);
                    } else if ($_GET['tipo'] == 'ret' || $_GET['tipo'] == 'reta5') {
                        if ($_GET['tipo'] == 'reta5') {
                            $this->generar_pdf_electronico_ret($documento, $this->empresa, 'a5');
                        } else {
                            $this->generar_pdf_electronico_ret($documento, $this->empresa);
                        }
                    } else if ($_GET['tipo'] == 'fact' || $_GET['tipo'] == 'facta5') {
                        if ($_GET['tipo'] == 'facta5') {
                            $this->generar_pdf_factura($documento, $this->empresa, 'a5');
                        } else {
                            $this->generar_pdf_factura($documento, $this->empresa);
                        }
                    } else {
                        $this->generar_pdf_electronico($documento, $this->empresa, $_GET['tipo']);
                    }
                } else {
                    $this->new_advice("El Documento no esta disponible para su empresa.");
                    $documento = false;
                    return;
                }
            } else {
                $this->new_advice('Factura No Encontrada.');
            }
        } else if (isset($_GET['pago'])) {
            $pago = $this->trans_pagos->get($_GET['id']);
            if ($pago) {
                if ($pago->idempresa == $this->empresa->idempresa) {
                    if ($pago->idpago) {
                        $pago2 = $this->cab_pagos->get($pago->idpago);
                        if ($pago2) {
                            $this->pdf_pago_comprobante($pago2, $this->empresa);
                        } else {
                            $this->new_advice("Pago no Encontrado.");
                            return;
                        }
                    } else {
                        $this->pdf_pago($pago, $this->empresa);
                    }
                } else {
                    $this->new_advice("El Pago no esta disponible para su empresa.");
                    $pago = false;
                    return;
                }
            } else {
                $this->new_advice("Pago no Encontrado.");
            }
        } else if (isset($_GET['imprimir_pago'])) {
            $pago = $this->cab_pagos->get($_GET['imprimir_pago']);
            if ($pago) {
                if ($pago->idempresa == $this->empresa->idempresa) {
                    $this->pdf_pago_comprobante($pago, $this->empresa);
                } else {
                    $this->new_advice("El Pago no esta disponible para su empresa.");
                    $pago = false;
                    return;
                }
            } else {
                $this->new_advice("Pago no Encontrado.");
                return;
            }
        } else if (isset($_GET['imprimir_anticipo'])) {
            $anticipo = $this->anticiposprov->get($_GET['imprimir_anticipo']);
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
            $devol = $this->cab_devolucion_proveedor->get($_GET['imprimir_devol']);
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
        $ypos = $pdf->GetY();
        $pdf->cliente_docs_electronicos($documento, $ypos);
        $ypos = $pdf->GetY();
        $pdf->detfactura_docs_electronicos($documento, $ypos);
        $ypos = $pdf->GetY();
        $pdf->subtotales_docs_electronicos($documento, $ypos);
        $pdf->infoad_docs_electronicos($documento, $ypos);

        if ($archivo) {
            $pdf->save($archivo);
        } else {
            $pdf->show($documento->get_tipodoc() . "-" . $documento->numero_documento . '.pdf');
        }
    }

    public function generar_pdf_electronico_ret($documento, $empresa, $size = 'a4', $archivo = false)
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
        $pdf->cabecera_docs_electronicos($documento, $empresa, true);
        $ypos = $pdf->GetY();
        $pdf->cliente_docs_electronicos($documento, $ypos, true);
        $ypos = $pdf->GetY();
        $pdf->detfactura_docs_electronicos($documento, $ypos, true);
        $ypos = $pdf->GetY();
        //$pdf->subtotales_docs_electronicos($documento, $ypos);
        $pdf->infoad_docs_electronicos($documento, $ypos, true);

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
        $pdf->cabecera_empresa($empresa);
        $pdf->datos_factura($documento);
        $ypos = $pdf->GetY();
        $pdf->detfactura_docs_electronicos($documento, $ypos);
        $ypos = $pdf->GetY();
        $pdf->subtotales_docs_electronicos($documento, $ypos);
        $pdf->observaciones_factura($documento, $ypos);
        if ($archivo) {
            $pdf->save($archivo);
        } else {
            $pdf->show($documento->get_tipodoc() . "-" . $documento->numero_documento . '.pdf');
        }
    }

    private function pdf_pago($pago, $empresa)
    {
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->cabecera_empresa($empresa);
        $pdf->datos_pago($pago);

        $pdf->show("Pago -" . $pago->idtranspago . '.pdf');
    }

    private function pdf_anticipo_comprobante($anticipo, $empresa)
    {
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->cabecera_empresa($empresa);
        $pdf->datos_comprobante_anticipoprov($anticipo);

        $pdf->show("AnticipoProv -" . $anticipo->numero . '.pdf');
    }

    private function exportar_xml($documento, $empresa)
    {
        $archivoFirmado = false;
        if ($documento->coddocumento == '03') {
            $rutaXmlFirmado = JG_MYDOCS . 'datosEmpresas/' . $empresa->idempresa . "/documentosElectronicos/liquidacionescompra/autorizados/";
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

    private function exportar_xml_ret($documento, $empresa)
    {
        $archivoFirmado = false;

        $rutaXmlFirmado = JG_MYDOCS . 'datosEmpresas/' . $empresa->idempresa . "/documentosElectronicos/retencionescompra/autorizados/";
        $archivoFirmado = $rutaXmlFirmado . $documento->numero_retencion . ".xml";
        $nombrearchivo  = "03-" . $documento->numero_retencion . '.xml';

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

    private function pdf_pago_comprobante($pago, $empresa)
    {
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->cabecera_empresa($empresa);
        $pdf->datos_comprobante_pago($pago);

        $pdf->show("Pago -" . $pago->numero . '.pdf');
    }

    private function pdf_comprobante_devolucion($devol, $empresa)
    {
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->cabecera_empresa($empresa);
        $pdf->datos_comprobante_devol_prov($devol);

        $pdf->show("Devolucion -" . $devol->numero . '.pdf');
    }
}
