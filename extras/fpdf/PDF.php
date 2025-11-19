<?php
require 'fpdf.php';

class PDF extends FPDF
{
    public $widths;
    public $border;
    public $aligns;
    public $paso;
    public $anul  = false;
    public $siz   = false;
    public $angle = 0;

    protected function private_core()
    {
        $this->paso = false;
        $this->anul = false;
        $this->siz  = false;
    }
    // Cabecera de página
    public function Header($usuario = false, $anulado = false, $size = 'a4')
    {
        if ($usuario) {
            $this->paso = true;
            // Posición: a 1,5 cm del inicio
            $this->SetY(8);
            // Número de página
            $this->SetFont('Times', '', 9);
            $this->Cell(0, 8, 'Fecha Impresión: ' . date('d-m-Y H:i:s'), 0, 0, 'L');
            // GSC
            $this->SetFont('Times', '', 9);
            $this->Cell(0, 8, 'Usuario Impresión: ' . $usuario, 0, 0, 'R');
        }

        if ($anulado || $this->anul) {
            $this->anul = true;
            if (!$this->siz) {
                $this->siz = $size;
            }
            $this->SetFont('Arial', 'B', 50);
            $this->SetTextColor(255, 192, 203);
            if ($this->siz == 'a5') {
                $this->RotatedText(25, 150, 'Anulado Anulado', 45);
            } else {
                $this->RotatedText(35, 190, 'Anulado Anulado Anulado', 45);
            }
        }
    }

    // Pie de página
    public function Footer()
    {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // GSC
        $this->SetFont('Times', 'I', 9);
        $this->Cell(0, 10, '® GSC_Systems <www.gscsystemsec.com>', 0, 0, 'L');
        // Número de página
        $this->SetFont('Times', 'BI', 12);
        $this->Cell(0, 10, 'Pag ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    public function show($filename = 'documento.pdf')
    {
        $this->Output('', $filename, true);
    }

    public function save($filename)
    {
        if ($filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
            $this->Output($filename, 'F');
            return true;
        }
        return false;
    }

    public function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1) {
            $x = $this->x;
        }

        if ($y == -1) {
            $y = $this->y;
        }

        if ($this->angle != 0) {
            $this->_out('Q');
        }

        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c  = cos($angle);
            $s  = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    public function RotatedText($x, $y, $txt, $angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    public function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    private function cargar_logo($empresa)
    {
        $logo = false;
        if ($empresa->logo) {
            if (file_exists($empresa->logo)) {
                $logo = $empresa->logo;
            }
        }

        if (!$logo) {
            $logo = JG_PATH . 'view/img/sinlogo.png';
        }

        return $logo;
    }

    private function cargar_codbarras($nroaut, $empresa)
    {
        $codbarras = false;
        $carpeta   = JG_MYDOCS . 'datosEmpresas/' . $empresa->idempresa . "/codbarras/";
        if (!file_exists($carpeta)) {
            @mkdir($carpeta, 0777, true);
        }

        $codbarras = $carpeta . $nroaut . ".png";
        barcode($codbarras, $nroaut);
        if (!$codbarras) {
            $codbarras = JG_PATH . 'view/img/codbarras.png';
        }

        return $codbarras;
    }

    public function cabecera_empresa($empresa)
    {
        $letra   = 10;
        $spacing = 6;
        $div     = 7;
        $rest    = 45;
        if ($this->sizePaper == 'A5') {
            $letra   = 7;
            $spacing = 4;
            $div     = 8;
            $rest    = 30;
        }
        if ($this->DefOrientation == 'L') {
            $rest    = 60;
            $letra   = 9;
            $spacing = 5;
        }
        $ancho    = $this->GetPageWidth();
        $anchocol = ($ancho / $div);
        $y1       = 10;
        if ($this->paso) {
            $y1 = 20;
        }
        $xlogo = $ancho - $rest;
        $ylogo = $y1;
        $wlogo = $anchocol;
        $hlogo = $spacing * 4;
        //Mostrar logo
        $this->Image($this->cargar_logo($empresa), $xlogo, $ylogo, $wlogo, $hlogo);
        $this->SetFont('Times', 'B', $letra);
        // - Razon Social
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * ($div - 3), $spacing, ' ' . $empresa->razonsocial, '', 'L');
        $y1 += $spacing;
        // - Fin Razon Social
        // - RUC
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * ($div - 3), $spacing, ' RUC: ' . $empresa->ruc, '', 'L');
        $y1 += $spacing;
        // - Fin RUC
        $this->SetFont('Times', 'I', $letra);
        // - Razon Social
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * ($div - 3), $spacing, ' Dirección: ' . $empresa->direccion, '', 'L');
        $y1 += $spacing;
        // - Fin Razon Social
        // - Telefono e Email
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * ($div - 3), $spacing, ' Telefono: ' . $empresa->telefono . ' - Email: ' . $empresa->email, '', 'L');
        $y1 += $spacing;
        // - Fin Telefono e Email
    }

    public function cabecera_ecaute($empresa, $numero_documento)
    {
        $letra   = 10;
        $spacing = 6;
        $div     = 7;
        $rest    = 45;
        if ($this->sizePaper == 'A5') {
            $letra   = 7;
            $spacing = 4;
            $div     = 8;
            $rest    = 30;
        }
        if ($this->DefOrientation == 'L') {
            $rest    = 60;
            $letra   = 9;
            $spacing = 5;
        }
        $ancho    = $this->GetPageWidth();
        $anchocol = ($ancho / $div);
        $y1       = 10;
        if ($this->paso) {
            $y1 = 20;
        }
        $xlogo = 10;
        $ylogo = $y1;
        $wlogo = $anchocol + 20;
        $hlogo = $spacing * 5;
        //Mostrar logo
        $this->Image($this->cargar_logo($empresa), $xlogo, $ylogo, $wlogo, $hlogo);
        $this->SetFont('Times', 'B', $letra);
        // - Razon Social
        $this->SetXY(80, $y1);
        $this->MultiCell($anchocol * ($div - 3), $spacing, ' ECAUTE', '', 'R');
        $y1 += $spacing;
        // - Fin Razon Social
        // - RUC
        $this->SetXY(80, $y1);
        $this->MultiCell($anchocol * ($div - 3), $spacing, ' RUC: 0992463651001', '', 'R');
        $y1 += $spacing;
        // - Fin RUC
        $this->SetFont('Times', 'I', $letra);
        // - Razon Social
        $this->SetXY(80, $y1);
        $this->MultiCell($anchocol * ($div - 3), $spacing, ' Dirección: VICTOR HUGO SICOURET SOLAR 18 Y ALBERTO NUQUES', '', 'R');
        $y1 += $spacing;
        // - Fin Razon Social
        // - Telefono e Email
        $this->SetXY(80, $y1);
        $this->MultiCell($anchocol * ($div - 3), $spacing, ' Telefono: 0995168133' . ' - Email: ', '', 'R');
        $y1 += $spacing;
        // - Fin Telefono e Email
        // - NUMERO DE PREFACTURA
        $this->SetFont('Times', 'B', $letra);
        $this->SetXY(80, $y1);
        $this->MultiCell($anchocol * ($div - 3), $spacing, ' PREFACTURA - ' . $numero_documento, '', 'R');
        $y1 += $spacing;
        // - Fin NUMERO DE PREFACTURA
        $this->SetFont('Times', 'I', $letra);
    }

    public function datos_factura($documento)
    {
        $clase   = get_class_name($documento);
        $letra   = 10;
        $spacing = 8;
        $div     = 7;
        if ($this->sizePaper == 'A5') {
            $letra   = 7;
            $spacing = 6;
            $div     = 8;
        }
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = ($ancho / $div);
        //Primer cuadro
        $y2 = $this->GetY() + 10;
        // - Factura
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra);
        $this->MultiCell($ancho, $spacing, $documento->get_tipodoc() . " - " . $documento->numero_documento, '', 'C');
        $y2 += ($spacing * 2);
        // - Fin Factura
        //Segundo cuadro
        $y3 = $y2;
        $this->SetFont('Times', '', $letra);
        // - RUC
        $this->SetXY(10, $y2);
        if ($clase == 'facturasprov') {
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Proveedor: ' . $documento->razonsocial, '', 'L');
        } else {
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Cliente: ' . $documento->razonsocial, '', 'L');
        }
        $y2 += $spacing;
        // - Fin RUC
        // - Nro. Autorizacion
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 3) * 2, $spacing, ' Nro. Autorización: ' . $documento->nro_autorizacion, '', 'L');
        $y2 += $spacing;
        // - Fin Nro. Autorizacion
        // - Direccion
        $this->SetXY(10, $y2);
        $this->MultiCell($ancho, $spacing, ' Dirección: ' . $documento->direccion, '', 'L');
        $y2 += $spacing;
        // - Fin Direccion

        //Segundo Cuadro
        // - Identificacion
        $this->SetXY((($ancho / 3) * 2) + 10, $y3);
        $this->MultiCell(($ancho / 3), $spacing, ' Identificación: ' . $documento->identificacion, '', 'L');
        $y3 += $spacing;
        // - Fin Identificacion
        // - Identificacion
        $this->SetXY((($ancho / 3) * 2) + 10, $y3);
        $this->MultiCell(($ancho / 3), $spacing, ' Fecha Emisión: ' . $documento->fec_emision, '', 'L');
        $y3 += $spacing;
        // - Fin Identificacion
        $this->SetY($y2);
    }

    public function datos_cobro($documento)
    {
        $letra   = 10;
        $spacing = 8;
        $div     = 7;
        if ($this->sizePaper == 'A5') {
            $letra   = 7;
            $spacing = 6;
            $div     = 8;
        }
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = ($ancho / $div);
        //Primer cuadro
        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Cobro N. " . $documento->idtranscobro, '', 'C');
        $y2 += $spacing;
        // - Fin Titulo

        $cliente = $documento->getCliente();
        if ($cliente) {
            $y3 = $y2;
            $this->SetFont('Times', '', $letra);
            // - RUC
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Cliente: ' . $cliente->razonsocial, '', 'L');
            $y2 += $spacing;
            // - Fin RUC
            // - Nro. Autorizacion
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Email: ' . $cliente->email, '', 'L');
            $y2 += $spacing;
            // - Fin Nro. Autorizacion
            // - Direccion
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Dirección: ' . $cliente->direccion, '', 'L');
            $y2 += $spacing;
            // - Fin Direccion

            //Segundo Cuadro
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Identificación: ' . $cliente->identificacion, '', 'L');
            $y3 += $spacing;
            // - Fin Identificacion
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Telefono: ' . $cliente->telefono, '', 'L');
            $y3 += $spacing;
        }

        $factura = $documento->getFactura();
        if ($factura) {
            $y2 = $this->GetY() + 10;
            // - Titulo
            $this->SetXY(10, $y2);
            $this->SetFont('Times', 'B', $letra + 4);
            $this->MultiCell($ancho, $spacing, "Detalle de Cobro", '', 'C');
            $y2 += $spacing;
            $y3 = $y2;
            $this->SetFont('Times', '', $letra);
            // - Fecha Cobro
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 2), $spacing, ' Fecha de Cobro: ' . $documento->fecha_trans, '', 'L');
            // - Fin Fecha Cobro
            // - Total Cobro
            $this->SetXY(($ancho / 2) + 25, $y2);
            $this->MultiCell(($ancho / 2) + 25, $spacing, ' Total Cobro: ' . $documento->credito, '', 'L');
            $y2 += $spacing;
            // - Fin Total Cobro
            // - Factura Afectada
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Factura Afectada: ' . $factura->numero_documento, '', 'L');
            $y2 += $spacing;
            // - Fin Factura Afectada
            $this->SetFont('Times', 'B', $letra + 4);
            // - Forma de Pago
            $this->SetFont('Times', 'B', $letra + 4);
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Forma de Pago: ' . $documento->get_fp(), '', 'L');
            // - Fin Forma de Pago
            // Firma Cliente
            $this->SetFont('Times', '', $letra);
            $ypos = $this->GetY();
            $this->SetXY(10, $ypos + ($spacing * 3));
            $this->MultiCell($ancho, $spacing, ' ' . str_pad('', strlen($factura->razonsocial) + 10, "_"), '', 'C');
            $this->MultiCell($ancho, $spacing, ' ' . $factura->razonsocial, '', 'C');
            // Fin Firma Cliente
        }

        $this->SetY($y2);
    }

    public function datos_comprobante_cobro($documento)
    {
        $letra    = 9;
        $spacing  = 7;
        $div      = 3;
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = ($ancho / $div);
        //Primer cuadro
        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Comprobante de Cobro N. " . $documento->numero, '', 'C');
        $y2 += $spacing;
        // - Fin Titulo

        $cliente = $documento->getCliente();
        if ($cliente) {
            $y3 = $y2;
            $this->SetFont('Times', '', $letra);
            // - RUC
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Cliente: ' . $cliente->razonsocial, '', 'L');
            $y2 += $spacing;
            // - Fin RUC
            // - Nro. Autorizacion
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Email: ' . $cliente->email, '', 'L');
            $y2 += $spacing;
            // - Fin Nro. Autorizacion
            // - Direccion
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Dirección: ' . $cliente->direccion, '', 'L');
            $y2 += $spacing;
            // - Fin Direccion

            //Segundo Cuadro
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Identificación: ' . $cliente->identificacion, '', 'L');
            $y3 += $spacing;
            // - Fin Identificacion
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Telefono: ' . $cliente->telefono, '', 'L');
            $y3 += $spacing;
        }

        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Detalle de Cobro", '', 'C');
        $y2 += $spacing;
        $y3 = $y2;
        $this->SetFont('Times', '', $letra);
        // - Fecha Cobro
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 3) * 2, $spacing, ' Fecha de Cobro: ' . $documento->fecha_trans, '', 'L');
        // - Fin Fecha Cobro
        // - Total Cobro
        $this->SetXY((($ancho / 3) * 2) + 10, $y3);
        $this->MultiCell(($ancho / 3), $spacing, ' Total Cobro: ' . show_numero($documento->valor) . ' $', '', 'L');
        $y2 += $spacing;
        // - Fin Total Cobro
        // - Forma de Pago
        $this->SetFont('Times', 'B', $letra + 4);
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 3) * 2, $spacing, ' Forma de Pago: ' . $documento->get_fp(), '', 'L');
        // - Fin Forma de Pago
        $y2 += $spacing;
        $this->SetY($y2 + 3);
        //Letra de la cabecera
        $this->SetFont('Times', 'B', $letra);
        //Anchos de la tabla
        $this->SetWidths(array('tipodoc' => $anchocol, 'numerodoc' => $anchocol, 'valor' => $anchocol));
        //Alineacion Headers
        $this->SetAligns(array('tipodoc' => 'C', 'numerodoc' => 'C', 'valor' => 'C'));
        // - Titulos Tabla
        $this->Titulos(array('tipodoc' => 'Tipo de Documento', 'numerodoc' => 'Comprobante', 'valor' => 'Valor' . ' '), $spacing);
        // - Fin Titulos Tabla
        $total = 0;
        // - Detalle Tabla
        $this->SetFont('Times', '', $letra);
        foreach ($documento->getDetalle() as $key => $l) {
            $total += $l->credito;
            //Ubico los anchos de las celdas
            $this->SetAligns(array('tipodoc' => 'L', 'numerodoc' => 'L', 'valor' => 'R'));
            $this->Fila(array('tipodoc' => $l->getFactura()->get_tipodoc(), 'numerodoc' => ' ' . $l->getFactura()->numero_documento, 'valor' => show_numero($l->credito) . ' $ '), $spacing);
        }
        $this->SetFont('Times', 'B', $letra + 4);
        $this->SetWidths(array('total' => $anchocol * 2, 'valor' => $anchocol));
        $this->SetAligns(array('total' => 'R', 'valor' => 'R'));
        $this->Fila(array('total' => 'Total Cobrado: ', 'valor' => show_numero($total) . ' $ '), $spacing);
        $this->SetWidths(array('total' => $anchocol * 2, 'valor' => $anchocol));
        $this->SetAligns(array('total' => 'R', 'valor' => 'R'));
        $this->Fila(array('total' => 'Saldo: ', 'valor' => show_numero($cliente->getSaldo()) . ' $ '), $spacing);
        // - Fin Detalle Tabla

        // Firma Cliente
        $this->SetFont('Times', '', $letra);
        $ypos = $this->GetY();
        $this->SetXY(10, $ypos + ($spacing));
        $this->MultiCell($ancho, $spacing, ' ' . str_pad('', strlen($cliente->razonsocial) + 10, "_"), '', 'C');
        $this->MultiCell($ancho, $spacing, ' ' . $cliente->razonsocial, '', 'C');
        // Fin Firma Cliente
    }

    public function datos_comprobante_anticipocli($documento)
    {
        $letra    = 9;
        $spacing  = 7;
        $div      = 3;
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = ($ancho / $div);
        //Primer cuadro
        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Anticipo de Cliente N. " . $documento->numero, '', 'C');
        $y2 += $spacing;
        // - Fin Titulo

        $cliente = $documento->getCliente();
        if ($cliente) {
            $y3 = $y2;
            $this->SetFont('Times', '', $letra);
            // - RUC
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Cliente: ' . $cliente->razonsocial, '', 'L');
            $y2 += $spacing;
            // - Fin RUC
            // - Nro. Autorizacion
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Email: ' . $cliente->email, '', 'L');
            $y2 += $spacing;
            // - Fin Nro. Autorizacion
            // - Direccion
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Dirección: ' . $cliente->direccion, '', 'L');
            $y2 += $spacing;
            // - Fin Direccion

            //Segundo Cuadro
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Identificación: ' . $cliente->identificacion, '', 'L');
            $y3 += $spacing;
            // - Fin Identificacion
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Telefono: ' . $cliente->telefono, '', 'L');
            $y3 += $spacing;
        }

        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Detalle de Anticipo", '', 'C');
        $y2 += $spacing;
        $y3 = $y2;
        $this->SetFont('Times', '', $letra);
        // - Fecha Anticipo
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 3) * 2, $spacing, ' Fecha de Anticipo: ' . $documento->fecha_trans, '', 'L');
        // - Fin Fecha Anticipo
        // - Total Anticipo
        $this->SetXY((($ancho / 3) * 2) + 10, $y3);
        $this->MultiCell(($ancho / 3), $spacing, ' Total Anticipo: ' . show_numero($documento->valor) . ' $', '', 'L');
        $y2 += $spacing;
        // - Fin Total Anticipo
        // - Observaciones
        $this->SetFont('Times', '', $letra);
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho), $spacing, ' Observaciones: ' . $documento->observaciones, '', 'L');
        $y2 += $spacing;
        // - Fin Observaciones
        // - Forma de Pago
        $this->SetFont('Times', 'B', $letra + 4);
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 3) * 2, $spacing, ' Forma de Pago: ' . $documento->get_fp(), '', 'L');
        $y2 += $spacing;
        // - Fin Forma de Pago

        // Firma Cliente
        $this->SetFont('Times', '', $letra);
        $ypos = $this->GetY();
        $this->SetXY(10, $ypos + ($spacing * 2));
        $this->MultiCell($ancho, $spacing, ' ' . str_pad('', strlen($cliente->razonsocial) + 10, "_"), '', 'C');
        $this->MultiCell($ancho, $spacing, ' ' . $cliente->razonsocial, '', 'C');
        // Fin Firma Cliente
    }

    public function datos_comprobante_pago($documento)
    {
        $letra    = 9;
        $spacing  = 7;
        $div      = 3;
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = ($ancho / $div);
        //Primer cuadro
        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Comprobante de Pago N. " . $documento->numero, '', 'C');
        $y2 += $spacing;
        // - Fin Titulo

        $proveedor = $documento->getProveedor();
        if ($proveedor) {
            $y3 = $y2;
            $this->SetFont('Times', '', $letra);
            // - RUC
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Cliente: ' . $proveedor->razonsocial, '', 'L');
            $y2 += $spacing;
            // - Fin RUC
            // - Nro. Autorizacion
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Email: ' . $proveedor->email, '', 'L');
            $y2 += $spacing;
            // - Fin Nro. Autorizacion
            // - Direccion
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Dirección: ' . $proveedor->direccion, '', 'L');
            $y2 += $spacing;
            // - Fin Direccion

            //Segundo Cuadro
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Identificación: ' . $proveedor->identificacion, '', 'L');
            $y3 += $spacing;
            // - Fin Identificacion
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Telefono: ' . $proveedor->telefono, '', 'L');
            $y3 += $spacing;
        }

        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Detalle de Pago", '', 'C');
        $y2 += $spacing;
        $y3 = $y2;
        $this->SetFont('Times', '', $letra);
        // - Fecha Pago
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 3) * 2, $spacing, ' Fecha de Pago: ' . $documento->fecha_trans, '', 'L');
        // - Fin Fecha Pago
        // - Total Pago
        $this->SetXY((($ancho / 3) * 2) + 10, $y3);
        $this->MultiCell(($ancho / 3), $spacing, ' Total Pago: ' . show_numero($documento->valor) . ' $', '', 'L');
        $y2 += $spacing;
        // - Fin Total Pago
        // - Forma de Pago
        $this->SetFont('Times', 'B', $letra + 4);
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 3) * 2, $spacing, ' Forma de Pago: ' . $documento->get_fp(), '', 'L');
        // - Fin Forma de Pago
        $y2 += $spacing;
        $this->SetY($y2 + 3);
        //Letra de la cabecera
        $this->SetFont('Times', 'B', $letra);
        //Anchos de la tabla
        $this->SetWidths(array('tipodoc' => $anchocol, 'numerodoc' => $anchocol, 'valor' => $anchocol));
        //Alineacion Headers
        $this->SetAligns(array('tipodoc' => 'C', 'numerodoc' => 'C', 'valor' => 'C'));
        // - Titulos Tabla
        $this->Titulos(array('tipodoc' => 'Tipo de Documento', 'numerodoc' => 'Comprobante', 'valor' => 'Valor' . ' '), $spacing);
        // - Fin Titulos Tabla
        $total = 0;
        // - Detalle Tabla
        $this->SetFont('Times', '', $letra);
        foreach ($documento->getDetalle() as $key => $l) {
            $total += $l->debito;
            //Ubico los anchos de las celdas
            $this->SetAligns(array('tipodoc' => 'L', 'numerodoc' => 'L', 'valor' => 'R'));
            $this->Fila(array('tipodoc' => $l->getFactura()->get_tipodoc(), 'numerodoc' => ' ' . $l->getFactura()->numero_documento, 'valor' => show_numero($l->debito) . ' $ '), $spacing);
        }
        $this->SetFont('Times', 'B', $letra + 4);
        $this->SetWidths(array('total' => $anchocol * 2, 'valor' => $anchocol));
        $this->SetAligns(array('total' => 'R', 'valor' => 'R'));
        $this->Fila(array('total' => 'Total Cobrado: ', 'valor' => show_numero($total) . ' $ '), $spacing);
        $this->SetWidths(array('total' => $anchocol * 2, 'valor' => $anchocol));
        $this->SetAligns(array('total' => 'R', 'valor' => 'R'));
        $this->Fila(array('total' => 'Saldo: ', 'valor' => show_numero($proveedor->getSaldo()) . ' $ '), $spacing);
        // - Fin Detalle Tabla

        // Firma Cliente
        $this->SetFont('Times', '', $letra);
        $ypos = $this->GetY();
        $this->SetXY(10, $ypos + ($spacing));
        $this->MultiCell($ancho, $spacing, ' ' . str_pad('', strlen($proveedor->razonsocial) + 10, "_"), '', 'C');
        $this->MultiCell($ancho, $spacing, ' ' . $proveedor->razonsocial, '', 'C');
        // Fin Firma Cliente
    }

    public function datos_comprobante_devol_prov($documento)
    {
        $letra    = 9;
        $spacing  = 7;
        $div      = 3;
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = ($ancho / $div);
        //Primer cuadro
        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Comprobante de Devolución N. " . $documento->numero, '', 'C');
        $y2 += $spacing;
        // - Fin Titulo

        $proveedor = $documento->getProveedor();
        if ($proveedor) {
            $y3 = $y2;
            $this->SetFont('Times', '', $letra);
            // - RUC
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Proveedor: ' . $proveedor->razonsocial, '', 'L');
            $y2 += $spacing;
            // - Fin RUC
            // - Nro. Autorizacion
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Email: ' . $proveedor->email, '', 'L');
            $y2 += $spacing;
            // - Fin Nro. Autorizacion
            // - Direccion
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Dirección: ' . $proveedor->direccion, '', 'L');
            $y2 += $spacing;
            // - Fin Direccion

            //Segundo Cuadro
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Identificación: ' . $proveedor->identificacion, '', 'L');
            $y3 += $spacing;
            // - Fin Identificacion
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Telefono: ' . $proveedor->telefono, '', 'L');
            $y3 += $spacing;
        }

        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Detalle de la Devolución", '', 'C');
        $y2 += $spacing;
        $y3 = $y2;
        $this->SetFont('Times', '', $letra);
        // - Fecha Pago
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 3) * 2, $spacing, ' Fecha de Pago: ' . $documento->fecha_trans, '', 'L');
        // - Fin Fecha Pago
        // - Total Pago
        $this->SetXY((($ancho / 3) * 2) + 10, $y3);
        $this->MultiCell(($ancho / 3), $spacing, ' Total Pago: ' . show_numero($documento->valor) . ' $', '', 'L');
        $y2 += $spacing;
        // - Fin Total Pago
        // - Forma de Pago
        $this->SetFont('Times', 'B', $letra + 4);
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 3) * 2, $spacing, ' Forma de Pago: ' . $documento->get_fp(), '', 'L');
        // - Fin Forma de Pago
        $y2 += $spacing;
        $this->SetY($y2 + 3);
        //Letra de la cabecera
        $this->SetFont('Times', 'B', $letra);
        //Anchos de la tabla
        $this->SetWidths(array('tipodoc' => $anchocol, 'numerodoc' => $anchocol, 'valor' => $anchocol));
        //Alineacion Headers
        $this->SetAligns(array('tipodoc' => 'C', 'numerodoc' => 'C', 'valor' => 'C'));
        // - Titulos Tabla
        $this->Titulos(array('tipodoc' => 'Tipo de Documento', 'numerodoc' => 'Comprobante', 'valor' => 'Valor' . ' '), $spacing);
        // - Fin Titulos Tabla
        $total = 0;
        // - Detalle Tabla
        $this->SetFont('Times', '', $letra);
        foreach ($documento->getDetalle() as $key => $l) {
            $total += $l->credito;
            //Ubico los anchos de las celdas
            $this->SetAligns(array('tipodoc' => 'L', 'numerodoc' => 'L', 'valor' => 'R'));
            if ($l->idfacturaprov) {
                $this->Fila(array('tipodoc' => $l->getFactura()->get_tipodoc(), 'numerodoc' => ' ' . $l->getFactura()->numero_documento, 'valor' => show_numero($l->credito) . ' $ '), $spacing);
            } elseif ($l->idanticipoprov) {
                $this->Fila(array('tipodoc' => 'Anticipo', 'numerodoc' => ' ' . $l->getAnticipo()->numero, 'valor' => show_numero($l->credito) . ' $ '), $spacing);
            }
        }
        $this->SetFont('Times', 'B', $letra + 4);
        $this->SetWidths(array('total' => $anchocol * 2, 'valor' => $anchocol));
        $this->SetAligns(array('total' => 'R', 'valor' => 'R'));
        $this->Fila(array('total' => 'Total Devolución: ', 'valor' => show_numero($total) . ' $ '), $spacing);
        $this->SetWidths(array('total' => $anchocol * 2, 'valor' => $anchocol));
        // - Fin Detalle Tabla

        if ($proveedor) {
            // Firma Cliente
            $this->SetFont('Times', '', $letra);
            $ypos = $this->GetY();
            $this->SetXY(10, $ypos + ($spacing));
            $this->MultiCell($ancho, $spacing, ' ' . str_pad('', strlen($proveedor->razonsocial) + 10, "_"), '', 'C');
            $this->MultiCell($ancho, $spacing, ' ' . $proveedor->razonsocial, '', 'C');
            // Fin Firma Cliente
        }
    }

    public function datos_comprobante_devol_cli($documento)
    {
        $letra    = 9;
        $spacing  = 7;
        $div      = 3;
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = ($ancho / $div);
        //Primer cuadro
        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Comprobante de Devolución N. " . $documento->numero, '', 'C');
        $y2 += $spacing;
        // - Fin Titulo

        $cliente = $documento->getCliente();
        if ($cliente) {
            $y3 = $y2;
            $this->SetFont('Times', '', $letra);
            // - RUC
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Cliente: ' . $cliente->razonsocial, '', 'L');
            $y2 += $spacing;
            // - Fin RUC
            // - Nro. Autorizacion
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Email: ' . $cliente->email, '', 'L');
            $y2 += $spacing;
            // - Fin Nro. Autorizacion
            // - Direccion
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Dirección: ' . $cliente->direccion, '', 'L');
            $y2 += $spacing;
            // - Fin Direccion

            //Segundo Cuadro
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Identificación: ' . $cliente->identificacion, '', 'L');
            $y3 += $spacing;
            // - Fin Identificacion
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Telefono: ' . $cliente->telefono, '', 'L');
            $y3 += $spacing;
        }

        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Detalle de la Devolución", '', 'C');
        $y2 += $spacing;
        $y3 = $y2;
        $this->SetFont('Times', '', $letra);
        // - Fecha Pago
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 3) * 2, $spacing, ' Fecha de Pago: ' . $documento->fecha_trans, '', 'L');
        // - Fin Fecha Pago
        // - Total Pago
        $this->SetXY((($ancho / 3) * 2) + 10, $y3);
        $this->MultiCell(($ancho / 3), $spacing, ' Total Pago: ' . show_numero($documento->valor) . ' $', '', 'L');
        $y2 += $spacing;
        // - Fin Total Pago
        // - Forma de Pago
        $this->SetFont('Times', 'B', $letra + 4);
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 3) * 2, $spacing, ' Forma de Pago: ' . $documento->get_fp(), '', 'L');
        // - Fin Forma de Pago
        $y2 += $spacing;
        $this->SetY($y2 + 3);
        //Letra de la cabecera
        $this->SetFont('Times', 'B', $letra);
        //Anchos de la tabla
        $this->SetWidths(array('tipodoc' => $anchocol, 'numerodoc' => $anchocol, 'valor' => $anchocol));
        //Alineacion Headers
        $this->SetAligns(array('tipodoc' => 'C', 'numerodoc' => 'C', 'valor' => 'C'));
        // - Titulos Tabla
        $this->Titulos(array('tipodoc' => 'Tipo de Documento', 'numerodoc' => 'Comprobante', 'valor' => 'Valor' . ' '), $spacing);
        // - Fin Titulos Tabla
        $total = 0;
        // - Detalle Tabla
        $this->SetFont('Times', '', $letra);
        foreach ($documento->getDetalle() as $key => $l) {
            $total += $l->debito;
            //Ubico los anchos de las celdas
            $this->SetAligns(array('tipodoc' => 'L', 'numerodoc' => 'L', 'valor' => 'R'));
            if ($l->idfacturacli) {
                $this->Fila(array('tipodoc' => $l->getFactura()->get_tipodoc(), 'numerodoc' => ' ' . $l->getFactura()->numero_documento, 'valor' => show_numero($l->debito) . ' $ '), $spacing);
            } else if ($l->idanticipocli) {
                $this->Fila(array('tipodoc' => 'Anticipo', 'numerodoc' => ' ' . $l->getAnticipo()->numero, 'valor' => show_numero($l->debito) . ' $ '), $spacing);
            } else if ($l->idretencion) {
                $this->Fila(array('tipodoc' => 'Retención', 'numerodoc' => ' ' . $l->getRetencion()->numero_documento, 'valor' => show_numero($l->debito) . ' $ '), $spacing);
            }
        }
        $this->SetFont('Times', 'B', $letra + 4);
        $this->SetWidths(array('total' => $anchocol * 2, 'valor' => $anchocol));
        $this->SetAligns(array('total' => 'R', 'valor' => 'R'));
        $this->Fila(array('total' => 'Total Devolución: ', 'valor' => show_numero($total) . ' $ '), $spacing);
        $this->SetWidths(array('total' => $anchocol * 2, 'valor' => $anchocol));
        // - Fin Detalle Tabla

        if ($cliente) {
            // Firma Cliente
            $this->SetFont('Times', '', $letra);
            $ypos = $this->GetY();
            $this->SetXY(10, $ypos + ($spacing));
            $this->MultiCell($ancho, $spacing, ' ' . str_pad('', strlen($cliente->razonsocial) + 10, "_"), '', 'C');
            $this->MultiCell($ancho, $spacing, ' ' . $cliente->razonsocial, '', 'C');
            // Fin Firma Cliente
        }
    }

    public function datos_pago($documento)
    {
        $letra   = 10;
        $spacing = 8;
        $div     = 7;
        if ($this->sizePaper == 'A5') {
            $letra   = 7;
            $spacing = 6;
            $div     = 8;
        }
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = ($ancho / $div);
        //Primer cuadro
        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Pago N. " . $documento->idtranspago, '', 'C');
        $y2 += $spacing;
        // - Fin Titulo

        $proveedor = $documento->getProveedor();
        if ($proveedor) {
            $y3 = $y2;
            $this->SetFont('Times', '', $letra);
            // - RUC
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Proveedor: ' . $proveedor->razonsocial, '', 'L');
            $y2 += $spacing;
            // - Fin RUC
            // - Nro. Autorizacion
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Email: ' . $proveedor->email, '', 'L');
            $y2 += $spacing;
            // - Fin Nro. Autorizacion
            // - Direccion
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Dirección: ' . $proveedor->direccion, '', 'L');
            $y2 += $spacing;
            // - Fin Direccion

            //Segundo Cuadro
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Identificación: ' . $proveedor->identificacion, '', 'L');
            $y3 += $spacing;
            // - Fin Identificacion
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Telefono: ' . $proveedor->telefono, '', 'L');
            $y3 += $spacing;
        }

        $factura = $documento->getFactura();
        if ($factura) {
            $y2 = $this->GetY() + 10;
            // - Titulo
            $this->SetXY(10, $y2);
            $this->SetFont('Times', 'B', $letra + 4);
            $this->MultiCell($ancho, $spacing, "Detalle de Pago", '', 'C');
            $y2 += $spacing;
            $y3 = $y2;
            $this->SetFont('Times', '', $letra);
            // - Fecha Pago
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 2), $spacing, ' Fecha de Pago: ' . $documento->fecha_trans, '', 'L');
            // - Fin Fecha Pago
            // - Total Pago
            $this->SetXY(($ancho / 2) + 25, $y2);
            $this->MultiCell(($ancho / 2) + 25, $spacing, ' Total Pago: ' . $documento->debito, '', 'L');
            $y2 += $spacing;
            // - Fin Total Pago
            // - Factura Afectada
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Factura Afectada: ' . $factura->numero_documento, '', 'L');
            $y2 += $spacing;
            // - Fin Factura Afectada
            $this->SetFont('Times', 'B', $letra + 4);
            // - Forma de Pago
            $this->SetFont('Times', 'B', $letra + 4);
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Forma de Pago: ' . $documento->get_fp(), '', 'L');
            // - Fin Forma de Pago
            // Firma Cliente
            $this->SetFont('Times', '', $letra);
            $ypos = $this->GetY();
            $this->SetXY(10, $ypos + ($spacing * 3));
            $this->MultiCell($ancho, $spacing, ' ' . str_pad('', strlen($factura->razonsocial) + 10, "_"), '', 'C');
            $this->MultiCell($ancho, $spacing, ' ' . $factura->razonsocial, '', 'C');
            // Fin Firma Cliente
        }

        $this->SetY($y2);
    }

    public function datos_comprobante_anticipoprov($documento)
    {
        $letra   = 10;
        $spacing = 8;
        $div     = 7;
        if ($this->sizePaper == 'A5') {
            $letra   = 7;
            $spacing = 6;
            $div     = 8;
        }
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = ($ancho / $div);
        //Primer cuadro
        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Anticipo de Proveedor N. " . $documento->numero, '', 'C');
        $y2 += $spacing;
        // - Fin Titulo

        $proveedor = $documento->getProveedor();
        if ($proveedor) {
            $y3 = $y2;
            $this->SetFont('Times', '', $letra);
            // - RUC
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Proveedor: ' . $proveedor->razonsocial, '', 'L');
            $y2 += $spacing;
            // - Fin RUC
            // - Nro. Autorizacion
            $this->SetXY(10, $y2);
            $this->MultiCell(($ancho / 3) * 2, $spacing, ' Email: ' . $proveedor->email, '', 'L');
            $y2 += $spacing;
            // - Fin Nro. Autorizacion
            // - Direccion
            $this->SetXY(10, $y2);
            $this->MultiCell($ancho, $spacing, ' Dirección: ' . $proveedor->direccion, '', 'L');
            $y2 += $spacing;
            // - Fin Direccion

            //Segundo Cuadro
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Identificación: ' . $proveedor->identificacion, '', 'L');
            $y3 += $spacing;
            // - Fin Identificacion
            // - Identificacion
            $this->SetXY((($ancho / 3) * 2) + 10, $y3);
            $this->MultiCell(($ancho / 3), $spacing, ' Telefono: ' . $proveedor->telefono, '', 'L');
            $y3 += $spacing;
        }

        $y2 = $this->GetY() + 10;
        // - Titulo
        $this->SetXY(10, $y2);
        $this->SetFont('Times', 'B', $letra + 4);
        $this->MultiCell($ancho, $spacing, "Detalle de Anticipo", '', 'C');
        $y2 += $spacing;
        $y3 = $y2;
        $this->SetFont('Times', '', $letra);
        // - Fecha Anticipo
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho / 2), $spacing, ' Fecha de Anticipo: ' . $documento->fecha_trans, '', 'L');
        // - Fin Fecha Anticipo
        // - Total Anticipo
        $this->SetXY(($ancho / 2) + 25, $y2);
        $this->MultiCell(($ancho / 2) + 25, $spacing, ' Total Anticipo: ' . $documento->valor, '', 'L');
        $y2 += $spacing;
        // - Fin Total Anticipo
        // - Observaciones
        $this->SetFont('Times', '', $letra);
        $this->SetXY(10, $y2);
        $this->MultiCell(($ancho), $spacing, ' Observaciones: ' . $documento->observaciones, '', 'L');
        $y2 += $spacing;
        // - Fin Observaciones
        $this->SetFont('Times', 'B', $letra + 4);
        // - Forma de Pago
        $this->SetFont('Times', 'B', $letra + 4);
        $this->SetXY(10, $y2);
        $this->MultiCell($ancho, $spacing, ' Forma de Pago: ' . $documento->get_fp(), '', 'L');
        // - Fin Forma de Pago
        // Firma Cliente
        $this->SetFont('Times', '', $letra);
        $ypos = $this->GetY();
        $this->SetXY(10, $ypos + ($spacing * 3));
        $this->MultiCell($ancho, $spacing, ' ' . str_pad('', strlen($proveedor->razonsocial) + 10, "_"), '', 'C');
        $this->MultiCell($ancho, $spacing, ' ' . $proveedor->razonsocial, '', 'C');
        // Fin Firma Cliente

        $this->SetY($y2);
    }

    public function cabecera_docs_electronicos($documento, $empresa, $isretencion = false)
    {
        $letra   = 8;
        $spacing = 6;
        $div     = 6;
        $mul     = 4;
        $sum     = 9;
        $divisor = 1;
        if ($this->sizePaper == 'A5') {
            $letra   = 6;
            $spacing = 5;
            $div     = 8;
            $mul     = 4;
            $sum     = 7;
            $divisor = 1.5;
        }
        $ancho = $this->GetPageWidth();
        $xlogo = 20;
        $ylogo = 10;
        $wlogo = ($ancho / $div);
        $hlogo = ($spacing * $mul);

        $param0 = new \parametrizacion();
        $xparam = $param0->all_by_codigo($empresa->idempresa, 'posxlogo');
        if ($xparam) {
            $xlogo = $xparam->valor;
        }
        $yparam = $param0->all_by_codigo($empresa->idempresa, 'posylogo');
        if ($yparam) {
            $ylogo = $yparam->valor;
        }
        $wparam = $param0->all_by_codigo($empresa->idempresa, 'anchologo');
        if ($wparam) {
            $wlogo = $wparam->valor / $divisor;
        }
        $hparam = $param0->all_by_codigo($empresa->idempresa, 'altologo');
        if ($hparam) {
            $hlogo = $hparam->valor / $divisor;
        }
        //Mostrar Datos de la empresa
        //Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
        $this->Image($this->cargar_logo($empresa), $xlogo, $ylogo, $wlogo, $hlogo);
        //MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
        $this->SetFont('Times', 'B', $letra);
        //Primer cuadro
        $y1 = ($spacing * ($mul + 1)) + $sum;
        // - Razon Social
        $this->SetXY(10, $y1);
        $this->MultiCell(($ancho / 2) - 12, 8, ' ' . $empresa->razonsocial, 'RLT', 'L');
        $y1 += $spacing;
        // - Fin Razon Social
        $this->SetFont('Times', '', $letra);
        // - Nombre Comercial
        $this->SetXY(10, $y1);
        $this->MultiCell(($ancho / 2) - 12, 8, ' ' . $empresa->nombrecomercial, 'RL', 'L');
        $y1 += $spacing;
        // - Fin Nombre Comercial
        // - Dir. Matriz
        $this->SetXY(10, $y1);
        $this->MultiCell(($ancho / 2) - 12, 8, ' Dir. Matriz: ' . ucwords(strtolower($empresa->direccion)), 'RL', 'L');
        $y1 += $spacing;
        // - Fin Dir. Matriz
        // - Dir. Sucursal
        $esta = '';
        if ($est0 = $documento->get_establecimiento()) {
            $esta = $est0->direccion;
        }
        $this->SetXY(10, $y1);
        $this->MultiCell(($ancho / 2) - 12, 8, ' Dir. Sucursal: ' . ucwords(strtolower($esta)), 'RL', 'L');
        $y1 += $spacing;
        // - Fin Dir. Sucursal
        // - Contribuyente Especial
        $this->SetXY(10, $y1);
        if ($documento->regimen_empresa == 'CE') {
            $this->MultiCell(($ancho / 2) - 12, 8, ' Contribuyente Especial Nro               No', 'RL', 'L');
        } else {
            $this->MultiCell(($ancho / 2) - 12, 8, ' ', 'RL', 'L');
        }
        $y1 += $spacing;
        // - Fin Contribuyente Especial
        // - Obligado
        $conta = 'No';
        if ($empresa->obligado) {
            $conta = 'Si';
        }
        $this->SetXY(10, $y1);
        $this->MultiCell(($ancho / 2) - 12, 8, ' Obligado a llevar Contabilidad          ' . $conta, 'RL', 'L');
        $y1 += $spacing;
        // - Fin Obligado
        // - Regimen
        $this->SetXY(10, $y1);
        $this->MultiCell(($ancho / 2) - 12, 8, ' ' . strtoupper($documento->get_regimen_empresa()), 'RL', 'L');
        $y1 += $spacing;
        // - Fin Regimen
        // - Regimen
        $ag = '';
        if ($documento->agretencion_empresa) {
            $ag = ' Agente de Retención Resolución No.     1';
        }
        $this->SetXY(10, $y1);
        $this->MultiCell(($ancho / 2) - 12, 8, $ag, 'RLB', 'L');
        $y1 += $spacing;
        // - Fin Regimen

        //segundo cuadro
        $this->SetFont('Times', 'B', $letra);
        // - RUC
        $y2 = 10;
        $this->SetXY(($ancho / 2), $y2);
        $this->MultiCell(($ancho / 2) - 10, $spacing, ' RUC:   ' . $empresa->ruc, 'RLT', 'L');
        $y2 += $spacing;
        // - Fin RUC
        // - Documento
        $this->SetXY(($ancho / 2), $y2);
        if ($isretencion) {
            $this->MultiCell(($ancho / 2) - 10, $spacing, ' ' . strtoupper("COMPROBANTE DE RETENCION"), 'RL', 'L');
        } else {
            $this->MultiCell(($ancho / 2) - 10, $spacing, ' ' . strtoupper($documento->get_tipodoc()), 'RL', 'L');
        }
        $y2 += $spacing;
        // - Fin Documento
        $this->SetFont('Times', '', $letra);
        // - Nro. Documento
        $this->SetXY(($ancho / 2), $y2);
        $this->MultiCell(($ancho / 2) - 10, $spacing, ' ', 'RL', 'L');
        $y2 += $spacing;
        // - Fin Nro. Documento
        // - Nro. Documento
        $this->SetXY(($ancho / 2), $y2);
        if ($isretencion) {
            $this->MultiCell(($ancho / 2) - 10, $spacing, ' Nro.    ' . $documento->numero_retencion, 'RL', 'L');
        } else {
            $this->MultiCell(($ancho / 2) - 10, $spacing, ' Nro.    ' . $documento->numero_documento, 'RL', 'L');
        }
        $y2 += $spacing;
        // - Fin Nro. Documento
        // - Nro. Autorizacion
        $this->SetXY(($ancho / 2), $y2);
        $this->MultiCell(($ancho / 2) - 10, $spacing, ' Número de Autorización', 'RL', 'L');
        $y2 += $spacing;
        // - Fin Nro. Autorizacion
        // - Nro. Autorizacion
        $this->SetXY(($ancho / 2), $y2);
        if ($isretencion) {
            $this->MultiCell(($ancho / 2) - 10, $spacing, ' ' . $documento->nro_autorizacion_ret, 'RL', 'L');
        } else {
            $this->MultiCell(($ancho / 2) - 10, $spacing, ' ' . $documento->nro_autorizacion, 'RL', 'L');
        }
        $y2 += $spacing;
        // - Fin Nro. Autorizacion
        // - Nro. Autorizacion
        $this->SetXY(($ancho / 2), $y2);
        if ($isretencion) {
            $fechaAut = '';
            if ($documento->fec_autorizacion_ret) {
                $fechaAut = date('d-m-Y', strtotime($documento->fec_autorizacion_ret)) . " " . date('H:i:s', strtotime($documento->hor_autorizacion_ret));
            }
            $this->MultiCell(($ancho / 2) - 10, $spacing, ' Fecha Autorización: ' . $fechaAut, 'RL', 'L');
        } else {
            $fechaAut = '';
            if ($documento->fec_autorizacion) {
                $fechaAut = date('d-m-Y', strtotime($documento->fec_autorizacion)) . " " . date('H:i:s', strtotime($documento->hor_autorizacion));
            }
            $this->MultiCell(($ancho / 2) - 10, $spacing, ' Fecha Autorización: ' . $fechaAut, 'RL', 'L');
        }
        $y2 += $spacing;
        // - Fin Nro. Autorizacion
        // - Ambiente
        $ambiente = 'PRUEBAS';
        if ($empresa->produccion) {
            $ambiente = 'PRODUCCION';
        }
        $this->SetXY(($ancho / 2), $y2);
        $this->MultiCell(($ancho / 2) - 10, $spacing, ' Ambiente:        ' . $ambiente, 'RL', 'L');
        $y2 += $spacing;
        // - Fin Ambiente
        // - Emision
        $this->SetXY(($ancho / 2), $y2);
        $this->MultiCell(($ancho / 2) - 10, $spacing, ' Emisión:          NORMAL', 'RL', 'L');
        $y2 += $spacing;
        // - Fin Emision
        // - Clave Acceso
        $this->SetXY(($ancho / 2), $y2);
        $this->MultiCell(($ancho / 2) - 10, $spacing, ' Clave de Acceso', 'RL', 'L');
        $y2 += $spacing;
        // - Fin Clave Acceso
        // - Img Clave Acceso
        $this->SetXY(($ancho / 2), $y2);
        if ($isretencion) {
            $ruta = $this->cargar_codbarras($documento->nro_autorizacion_ret, $empresa);
            $this->MultiCell(($ancho / 2) - 10, $spacing * 2, $this->Image($ruta, ($ancho / 2) + 2, $y2, ($ancho / 2) - 15, $spacing * 2), 'RL', 'L');
        } else {
            $ruta = $this->cargar_codbarras($documento->nro_autorizacion, $empresa);
            $this->MultiCell(($ancho / 2) - 10, $spacing * 2, $this->Image($ruta, ($ancho / 2) + 2, $y2, ($ancho / 2) - 15, $spacing * 2), 'RL', 'L');
        }
        unlink($ruta);
        $y2 += $spacing * 2;
        // - Fin Img Clave Acceso
        // - Clave Acceso
        $this->SetXY(($ancho / 2), $y2);
        if ($isretencion) {
            $this->MultiCell(($ancho / 2) - 10, $spacing, $documento->nro_autorizacion_ret, 'RLB', 'C');
        } else {
            $this->MultiCell(($ancho / 2) - 10, $spacing, $documento->nro_autorizacion, 'RLB', 'C');
        }
        $y2 += $spacing;
        // - Fin Clave Acceso
    }

    public function cliente_docs_electronicos($documento, $ypos, $isretencion = false)
    {
        $clase   = get_class_name($documento);
        $letra   = 8;
        $spacing = 6;
        $div     = 6;
        $mul     = 4;
        $sum     = 9;
        if ($this->sizePaper == 'A5') {
            $letra   = 7;
            $spacing = 5;
            $div     = 8;
            $mul     = 4;
            $sum     = 7;
        }
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = $ancho / 4;
        if ($documento->coddocumento == '02' && JG_ECAUTE == 1) {
            $this->SetFont('Times', '', $letra);
        }
        $y1 = $ypos + 3;
        // - Razon Social
        $this->SetXY(10, $y1);
        if ($clase == 'facturasprov') {
            $this->MultiCell($anchocol * 3, $spacing, ' Proveedor: ' . $documento->razonsocial, 'LT', 'L');
        } else {
            $this->MultiCell($anchocol * 3, $spacing, ' Cliente: ' . $documento->razonsocial, 'LT', 'L');
        }
        // - Fin Razon Social
        // - Identificacion
        $this->SetXY($anchocol * 3, $y1);
        $this->MultiCell($anchocol + 10, $spacing, ' Identificación: ' . $documento->identificacion, 'TR', 'L');
        // - Fin Identificacion
        $y1 += $spacing;
        // - Fecha
        $this->SetXY(10, $y1);
        if ($isretencion) {
            $this->MultiCell($anchocol * 4, $spacing, ' Fecha Emisión: ' . $documento->fec_registro, 'LRB', 'L');
        } else {
            $this->MultiCell($anchocol * 3, $spacing, ' Fecha Emisión: ' . $documento->fec_emision, 'L', 'L');
            // - Fin Fecha
            if ($documento->coddocumento == '01') {
                //Solo si es Factura
                // - Guia Remision
                $this->SetXY($anchocol * 3, $y1);
                $this->MultiCell($anchocol + 10, $spacing, ' Guia: ', 'R', 'L');
                // - Fin Guia Remision
                $y1 += $spacing;
                // - Fecha
                $this->SetXY(10, $y1);
                $this->MultiCell($anchocol * 4, $spacing, ' Dirección: ' . $documento->direccion, 'LRB', 'L');
                // - Fin Fecha
            } else if ($documento->coddocumento == '05' || $documento->coddocumento == '04') {
                // SI es Nota de Debito o Nota de Credito agrego otro casillero
                // - Guia Remision
                $this->SetXY($anchocol * 3, $y1);
                $this->MultiCell($anchocol + 10, $spacing, ' ', 'R', 'L');
                // - Fin Guia Remision
                $y1 += $spacing;
                // - Comprobante que se Modifica
                $this->SetXY(10, $y1);
                $this->MultiCell($anchocol * 3, $spacing, ' Comprobante que se Modifica:   FACTURA', 'LT', 'L');
                // - Fin Comprobante que se Modifica
                // - Documento modificado
                $this->SetXY($anchocol * 3, $y1);
                $this->MultiCell($anchocol + 10, $spacing, $documento->numero_documento_mod, 'RT', 'L');
                // - Fin Documento modificado
                $y1 += $spacing;
                // - Fecha
                $this->SetXY(10, $y1);
                if ($documento->coddocumento == '05') {
                    $this->MultiCell($anchocol * 4, $spacing, ' Fecha Emisión (Comprobante a Modificar): ' . $documento->fec_emision_mod, 'LRB', 'L');
                } else {
                    $this->MultiCell($anchocol * 4, $spacing, ' Fecha Emisión (Comprobante a Modificar): ' . $documento->fec_emision_mod, 'LR', 'L');
                    $y1 += $spacing;
                    // - Fecha
                    $this->SetXY(10, $y1);
                    $this->MultiCell($anchocol * 4, $spacing, ' Razon de Modificar: ' . $documento->observaciones, 'LRB', 'L');

                }
                // - Fin Fecha
            } else if ($documento->coddocumento == '03' or $documento->coddocumento == '02') {
                // - Guia Remision
                $this->SetXY($anchocol * 3, $y1);
                $this->MultiCell($anchocol + 10, $spacing, '', 'R', 'L');
                // - Fin Guia Remision
                $y1 += $spacing;
                // - Fecha
                $this->SetXY(10, $y1);
                $this->MultiCell($anchocol * 4, $spacing, ' Dirección: ' . $documento->direccion, 'LRB', 'L');
                // - Fin Fecha
            }
        }

        // Fin Nota de debito o Credito

    }

    public function transporte_docs_electronicos($documento, $ypos, $isretencion = false)
    {
        $clase   = get_class_name($documento);
        $letra   = 8;
        $spacing = 6;
        $div     = 6;
        $mul     = 4;
        $sum     = 9;
        if ($this->sizePaper == 'A5') {
            $letra   = 7;
            $spacing = 5;
            $div     = 8;
            $mul     = 4;
            $sum     = 7;
        }
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = $ancho / 4;

        $y1 = $ypos + 3;
        // - Identificacion Trans
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * 4, $spacing, ' Identificación (Transportista): ' . $documento->identificacion_trans, 'LRT', 'L');
        // - Fin Identificacion Trans
        $y1 += $spacing;
        // - Razon Social Trans
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * 4, $spacing, ' Razòn Social / Nombres y Apellidos: ' . $documento->razonsocial_trans, 'LR', 'L');
        // - Fin Razon Social Trans
        $y1 += $spacing;
        // - Placa Trans
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * 4, $spacing, ' Placa: ' . $documento->placa, 'LR', 'L');
        // - Fin Placa Trans
        $y1 += $spacing;
        // - Dir Partida Trans
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * 4, $spacing, ' Punto de Partida: ' . $documento->dirpartida, 'LR', 'L');
        // - Fin Dir Partida Trans
        $y1 += $spacing;
        // - Dir Partida Trans
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * 2, $spacing, ' Fecha Inicio Transporte: ' . $documento->fec_emision, 'LB', 'L');
        // - Fin Dir Partida Trans
        $this->SetXY(($anchocol * 2) + 10, $y1);
        $this->MultiCell($anchocol * 2, $spacing, ' Fecha Fin Transporte: ' . $documento->fec_finalizacion, 'RB', 'L');
    }

    public function clientetransporte_docs_electronicos($documento, $ypos, $isretencion = false)
    {
        $letra   = 8;
        $spacing = 6;
        $div     = 6;
        $mul     = 4;
        $sum     = 9;
        if ($this->sizePaper == 'A5') {
            $letra   = 7;
            $spacing = 5;
            $div     = 8;
            $mul     = 4;
            $sum     = 7;
        }
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = $ancho / 4;

        $y1   = $ypos + 3;
        $paso = true;
        if ($documento->idfactura_mod) {
            $paso = false;
            // - Comprobantes
            $this->SetXY(10, $y1);
            $this->MultiCell($anchocol, $spacing, ' Comprobante de Venta: ', 'LT', 'L');
            // - Fin Comprobantes
            // - Factura
            $this->SetXY($anchocol + 10, $y1);
            $this->MultiCell($anchocol * 2, $spacing, ' FACTURA ' . $documento->numero_documento_mod, 'T', 'L');
            // - Fin Factura
            // - Fecha Emision
            $this->SetXY(($anchocol * 3) + 10, $y1);
            $this->MultiCell($anchocol, $spacing, ' Fecha Emision: ' . $documento->fec_emision_mod, 'RT', 'L');
            // - Fin Fecha Emision
            $y1 += $spacing;
            // - Identificacion Trans
            $this->SetXY(10, $y1);
            $this->MultiCell($anchocol * 4, $spacing, ' Numero de Autorización: ' . $documento->nro_autorizacion_mod, 'LR', 'L');
            // - Fin Identificacion Trans
        }
        // - Identificacion Trans
        if ($paso) {
            $this->SetXY(10, $y1);
            $this->MultiCell($anchocol * 4, $spacing, ' Motivo Traslado: ' . $documento->motivo, 'LRT', 'L');
        } else {
            $y1 += $spacing;
            $this->SetXY(10, $y1);
            $this->MultiCell($anchocol * 4, $spacing, ' Motivo Traslado: ' . $documento->motivo, 'LR', 'L');
        }
        // - Fin Identificacion Trans
        $y1 += $spacing;
        // - Razon Social Trans
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * 4, $spacing, ' Destino (Punto de llegada): ' . $documento->direccion, 'LR', 'L');
        // - Fin Razon Social Trans
        $y1 += $spacing;
        // - Placa Trans
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * 4, $spacing, ' Identificación (Destinatario): ' . $documento->identificacion, 'LR', 'L');
        // - Fin Placa Trans
        $y1 += $spacing;
        // - Dir Partida Trans
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * 4, $spacing, ' Razòn Social / Nombres Apellidos: ' . $documento->razonsocial, 'LR', 'L');
        // - Fin Dir Partida Trans
        $y1 += $spacing;
        // - Dir Partida Trans
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * 4, $spacing, ' Codigo Establecimiento Destino: ' . $documento->codestablecimiento, 'LR', 'L');
        // - Fin Dir Partida Trans
        $y1 += $spacing;
        // - Dir Partida Trans
        $this->SetXY(10, $y1);
        $this->MultiCell($anchocol * 4, $spacing, ' Ruta: ' . $documento->ruta, 'LRB', 'L');
        // - Fin Dir Partida Trans
    }

    public function subtotales_docs_electronicos($documento, $ypos)
    {
        $clase   = get_class_name($documento);
        $letra   = 8;
        $spacing = 6;
        if ($documento->coddocumento == '06') {
            $yposmax = 243;
        } else {
            $yposmax = 214;
        }
        if ($this->sizePaper == 'A5') {
            $letra   = 6;
            $spacing = 5;
            $yposmax = 141;
        }
        $ancho = $this->GetPageWidth() - 20;
        if ($documento->coddocumento == '01' and JG_IMP_CODAUXILIAR == 1) {
            $anchocol = $ancho / 10;
            $ml       = 6;
        } else {
            $anchocol = $ancho / 9;
            $ml       = 5;
        }
        if ($ypos > $yposmax) {
            $this->AddPage();
        } else {
            $this->SetY($ypos);
        }
        $this->SetX(($anchocol * $ml) + 10);
        $this->SetFont('Times', '', $letra);
        //Anchos de la tabla
        $this->SetWidths(array('titulos' => $anchocol * 3, 'valores' => $anchocol));
        $this->SetAligns(array('titulos' => 'L', 'valores' => 'R'));
        if ($documento->coddocumento != '06') {
            $this->Fila(array('titulos' => 'SUBTOTAL GRAVADO:', 'valores' => show_numero($documento->base_gra, 2)), $spacing);
            $this->SetX(($anchocol * $ml) + 10);
            $this->Fila(array('titulos' => 'SUBTOTAL 0%:', 'valores' => show_numero($documento->base_0, 2)), $spacing);
            $this->SetX(($anchocol * $ml) + 10);
            $this->Fila(array('titulos' => 'SUBTOTAL NO OBJETO DE IVA:', 'valores' => show_numero($documento->base_noi, 2)), $spacing);
            $this->SetX(($anchocol * $ml) + 10);
            $this->Fila(array('titulos' => 'SUBTOTAL EXCENTO DE IVA:', 'valores' => show_numero($documento->base_exc, 2)), $spacing);
            $this->SetX(($anchocol * $ml) + 10);
            // code...
        }
        $subtotal = $documento->base_gra + $documento->base_0 + $documento->base_noi + $documento->base_exc;
        $this->Fila(array('titulos' => 'SUBTOTAL SIN IMPUESTOS:', 'valores' => show_numero($subtotal, 2)), $spacing);
        $this->SetX(($anchocol * $ml) + 10);
        $this->Fila(array('titulos' => 'TOTAL DESCUENTO:', 'valores' => show_numero($documento->totaldescuento, 2)), $spacing);
        $this->SetX(($anchocol * $ml) + 10);
        if ($clase == 'facturasprov') {
            $this->SetX(($anchocol * $ml) + 10);
            $this->Fila(array('titulos' => 'IRBP:', 'valores' => show_numero($documento->totalirbp, 2)), $spacing);
        }
        $this->SetX(($anchocol * $ml) + 10);
        $this->Fila(array('titulos' => 'IVA:', 'valores' => show_numero($documento->totaliva, 2)), $spacing);
        $this->SetX(($anchocol * $ml) + 10);
        $this->Fila(array('titulos' => 'TOTAL:', 'valores' => show_numero($documento->total, 2)), $spacing);
    }
    public function infoad_docs_electronicos($documento, $ypos, $isretencion = false)
    {
        $letra   = 8;
        $spacing = 6;
        $yposmax = 214;
        $suma    = 10;
        if ($isretencion) {
            $yposmax = 250;
        }
        if ($documento->coddocumento == '06') {
            $spacing = 5;
            $yposmax = 243;
            $suma    = 10;
            if ($this->sizePaper == 'A5') {
                $letra   = 6;
                $spacing = 3;
                $yposmax = 160;
                $suma    = 5;
            }
        } else if ($this->sizePaper == 'A5') {
            $letra   = 6;
            $spacing = 5;
            $yposmax = 141;
            $suma    = 5;
        }
        $ancho = $this->GetPageWidth() - 20;
        if ($documento->coddocumento == '01' and JG_IMP_CODAUXILIAR == 1) {
            $anchocol = $ancho / 10;
            $ml       = 6;
        } else {
            $anchocol = $ancho / 9;
            $ml       = 5;
        }
        if ($ypos > $yposmax) {
            $this->SetY(10);
        } else {
            $this->SetY($ypos + 3);
        }
        $this->SetFont('Times', 'B', $letra);
        $this->SetWidths(array('info' => ($anchocol * $ml) - 2));
        $this->SetAligns(array('info' => 'C'));
        $this->Titulos(array('info' => 'Información Adicional'), $spacing);
        $this->SetFont('Times', '', $letra);
        $this->SetAligns(array('info' => 'L'));
        $observaciones = "Email: " . $documento->email;
        if ($documento->observaciones && $documento->coddocumento != '04') {
            $observaciones .= "\nObservaciones: " . $documento->observaciones;
        }
        $clase = get_class_name($documento);
        if ($clase == 'facturascli') {
            if ($med = $documento->get_medidor()) {
                $observaciones .= "\nNum. Medidor: " . $med->numero;
            }
        }
        $this->Fila(array('info' => $observaciones), $spacing);
        if (!$isretencion) {
            if ($documento->coddocumento != '06') {
                $ypos = $this->GetY();
                $this->SetY($ypos + 2);
                $this->SetFont('Times', 'B', $letra);
                $this->SetWidths(array('fpago' => ($anchocol * 4), 'valor' => $anchocol - 2));
                $this->SetAligns(array('fpago' => 'C', 'valor' => 'C'));
                $this->Titulos(array('fpago' => 'Forma de Pago', 'valor' => 'Valor'), $spacing);
                $this->SetFont('Times', '', $letra);
                $this->SetAligns(array('fpago' => 'L', 'valor' => 'R'));
                if ($fpsri = $documento->get_fp_sri()) {
                    $this->Titulos(array('fpago' => $fpsri->codigo . " - " . $fpsri->nombre, 'valor' => show_numero($documento->total, 2)), $spacing);
                } else {
                    $this->Titulos(array('fpago' => '20 - Otros con Utilizacion del Sistema Financiero', 'valor' => show_numero($documento->total, 2)), $spacing);
                }
            }

            // Firma Cliente
            $ypos = $this->GetY();
            $this->SetXY(10, $ypos + $suma);
            $this->MultiCell(($anchocol * $ml) - 2, $spacing, ' ' . str_pad('', strlen($documento->razonsocial) + 5, "_"), '', 'C');
            $this->MultiCell(($anchocol * $ml) - 2, $spacing, ' ' . $documento->razonsocial, '', 'C');
            // Fin Firma Cliente
        }

    }

    public function observaciones_factura($documento, $ypos)
    {
        $letra   = 9;
        $spacing = 7;
        $yposmax = 214;
        if ($this->sizePaper == 'A5') {
            $letra   = 6;
            $spacing = 5;
            $yposmax = 141;
        }
        $ancho    = $this->GetPageWidth() - 20;
        $anchocol = $ancho / 9;
        if ($ypos > $yposmax) {
            $this->AddPage();
        } else {
            $this->SetY($ypos + 3);
        }
        if ($documento->observaciones) {
            $this->SetFont('Times', 'B', $letra);
            $this->SetWidths(array('info' => ($anchocol * 5) - 2));
            $this->SetAligns(array('info' => 'C'));
            $this->Titulos(array('info' => 'Observaciones'), $spacing);
            $this->SetFont('Times', '', $letra);
            $this->SetAligns(array('info' => 'L'));
            $observaciones = "Observaciones: " . $documento->observaciones;
            $this->Fila(array('info' => $observaciones), $spacing);
        }
        // Firma Cliente
        $ypos = $this->GetY();
        $this->SetXY(10, $ypos + 20);
        $this->MultiCell(($anchocol * 5) - 2, $spacing, ' ' . str_pad('', strlen($documento->razonsocial) + 10, "_"), '', 'C');
        $this->MultiCell(($anchocol * 5) - 2, $spacing, ' ' . $documento->razonsocial, '', 'C');
        // Fin Firma Cliente
    }

    public function detfactura_docs_electronicos($documento, $ypos, $isretencion = false)
    {
        $letra   = 8;
        $spacing = 6;
        $div     = 6;
        $mul     = 4;
        $sum     = 9;
        if ($this->sizePaper == 'A5') {
            $letra   = 6;
            $spacing = 5;
            $div     = 8;
            $mul     = 4;
            $sum     = 7;
        }
        $ancho = $this->GetPageWidth() - 20;
        if ($documento->coddocumento == '05') {
            $anchocol = $ancho / 2;
        } else if ($documento->coddocumento == '06') {
            if ($documento->tipo_guia == 1) {
                $anchocol = $ancho / 5;
            } else {
                $anchocol = $ancho / 9;
            }
        } else {
            if ($documento->coddocumento == '01' and JG_IMP_CODAUXILIAR == 1 && !$isretencion) {
                $anchocol = $ancho / 10;
            } else {
                $anchocol = $ancho / 9;
            }
        }
        $this->SetY($ypos + 3);
        //Letra de la cabecera
        $this->SetFont('Times', 'B', $letra);
        if ($isretencion) {
            //Anchos de la tabla
            $this->SetWidths(array('comprobante' => $anchocol * 2, 'numdoc' => $anchocol, 'fec_emision' => $anchocol, 'ejercicio' => $anchocol, 'baseimp' => $anchocol, 'impuesto' => $anchocol, 'porcentaje' => $anchocol, 'valor' => $anchocol));
            //Alineacion Headers
            $this->SetAligns(array('comprobante' => 'C', 'numdoc' => 'C', 'fec_emision' => 'C', 'ejercicio' => 'C', 'baseimp' => 'C', 'impuesto' => 'C', 'porcentaje' => 'C', 'valor' => 'C'));
            // - Titulos Tabla
            $this->Titulos(array('comprobante' => 'Comprobante', 'numdoc' => 'Numero' . ' ', 'fec_emision' => 'Fecha Emisión', 'ejercicio' => 'Ejercicio Fiscal', 'baseimp' => 'Base Imponible', 'impuesto' => 'Impuesto', 'porcentaje' => 'Porcentaje', 'valor' => 'Valor'), $spacing);
            // - Fin Titulos Tabla
            // - Detalle Tabla
            $this->SetFont('Times', '', $letra);
            foreach ($documento->get_retencion() as $key => $l) {
                //Ubico los anchos de las celdas
                $this->SetAligns(array('comprobante' => 'L', 'numdoc' => 'R', 'fec_emision' => 'R', 'ejercicio' => 'R', 'baseimp' => 'R', 'impuesto' => 'R', 'porcentaje' => 'R', 'valor' => 'R'));
                $this->Fila(array('comprobante' => ' ' . $l['comprobante'], 'numdoc' => $l['numero'] . ' ', 'fec_emision' => $l['fecha'] . ' ', 'ejercicio' => $l['ejercicio'] . ' ', 'baseimp' => show_numero($l['baseimp']) . ' ', 'impuesto' => strtoupper($l['especie']) . ' ', 'porcentaje' => show_numero($l['porcentaje']) . ' ', 'valor' => show_numero($l['valor']) . ' '), $spacing);
            }
            // - Fin Detalle Tabla
        } else if ($documento->coddocumento == '06') {
            if ($documento->tipo_guia == 2) {
                //Anchos de la tabla
                $this->SetWidths(array('codigo' => $anchocol, 'cantidad' => $anchocol, 'descripcion' => $anchocol * 4, 'pvpunitario' => $anchocol, 'dto' => $anchocol, 'pvptotal' => $anchocol));
                //Alineacion Headers
                $this->SetAligns(array('codigo' => 'C', 'cantidad' => 'C', 'descripcion' => 'C', 'pvpunitario' => 'C', 'dto' => 'C', 'pvptotal' => 'C'));
                // - Titulos Tabla
                $this->Titulos(array('codigo' => 'Código', 'cantidad' => 'Cantidad' . ' ', 'descripcion' => 'Descripción', 'pvpunitario' => 'Precio Unitario', 'dto' => 'Descuento', 'pvptotal' => 'Total'), $spacing);
                // - Fin Titulos Tabla
                // - Detalle Tabla
                $this->SetFont('Times', '', $letra);
                foreach ($documento->getlineas() as $key => $l) {
                    //Ubico los anchos de las celdas
                    $this->SetAligns(array('codigo' => 'L', 'cantidad' => 'R', 'descripcion' => 'L', 'pvpunitario' => 'R', 'dto' => 'R', 'pvptotal' => 'R'));
                    $this->Fila(array('codigo' => ' ' . $l->codprincipal, 'cantidad' => $l->cantidad . ' ', 'descripcion' => ' ' . $l->descripcion, 'pvpunitario' => show_numero($l->pvpunitario, 6) . ' ', 'dto' => show_numero($l->pvpsindto - $l->pvptotal, 6) . ' ', 'pvptotal' => show_numero($l->pvptotal, 6) . ' '), $spacing);
                }
                // - Fin Detalle Tabla
            } else {
                //Anchos de la tabla
                $this->SetWidths(array('cantidad' => $anchocol, 'descripcion' => $anchocol * 2, 'codigoprin' => $anchocol, 'codigoaux' => $anchocol));
                //Alineacion Headers
                $this->SetAligns(array('cantidad' => 'C', 'descripcion' => 'C', 'codigoprin' => 'C', 'codigoaux' => 'C'));
                // - Titulos Tabla
                $this->Titulos(array('cantidad' => 'Cantidad', 'descripcion' => 'Descripción' . ' ', 'codigoprin' => 'Codigo Principal', 'codigoaux' => 'Codigo Auxiliar'), $spacing);
                // - Fin Titulos Tabla
                // - Detalle Tabla
                $this->SetFont('Times', '', $letra);
                foreach ($documento->getlineas() as $key => $l) {
                    //Ubico los anchos de las celdas
                    $this->SetAligns(array('cantidad' => 'R', 'descripcion' => 'L', 'codigoprin' => 'L', 'codigoaux' => 'L'));
                    $this->Fila(array('cantidad' => $l->cantidad . ' ', 'descripcion' => ' ' . $l->descripcion, 'codigoprin' => ' ' . $l->codprincipal, 'codigoaux' => ' ' . $l->codprincipal), $spacing);
                }
                // - Fin Detalle Tabla
            }
        } else {
            if ($documento->coddocumento == '01' or $documento->coddocumento == '02' || $documento->coddocumento == '04' || $documento->coddocumento == '03') {
                if ($documento->coddocumento == '01' and JG_IMP_CODAUXILIAR == 1) {
                    //Anchos de la tabla
                    $this->SetWidths(array('codigo' => $anchocol, 'codigoaux' => $anchocol, 'cantidad' => $anchocol, 'descripcion' => $anchocol * 4, 'pvpunitario' => $anchocol, 'dto' => $anchocol, 'pvptotal' => $anchocol));
                    //Alineacion Headers
                    $this->SetAligns(array('codigo' => 'C', 'codigoaux' => 'C', 'cantidad' => 'C', 'descripcion' => 'C', 'pvpunitario' => 'C', 'dto' => 'C', 'pvptotal' => 'C'));
                    // - Titulos Tabla
                    $this->Titulos(array('codigo' => 'Código', 'codigoaux' => 'Código Aux.', 'cantidad' => 'Cantidad' . ' ', 'descripcion' => 'Descripción', 'pvpunitario' => 'Precio Unitario', 'dto' => 'Descuento', 'pvptotal' => 'Total'), $spacing);
                    // - Fin Titulos Tabla
                    // - Detalle Tabla
                    $this->SetFont('Times', '', $letra);
                    foreach ($documento->getlineas() as $key => $l) {
                        //Ubico los anchos de las celdas
                        $this->SetAligns(array('codigo' => 'L', 'codigoaux' => 'L', 'cantidad' => 'R', 'descripcion' => 'L', 'pvpunitario' => 'R', 'dto' => 'R', 'pvptotal' => 'R'));
                        $this->Fila(array('codigo' => ' ' . $l->codprincipal, 'codigoaux' => ' ' . $l->get_articulo()->codauxiliar, 'cantidad' => $l->cantidad . ' ', 'descripcion' => ' ' . $l->descripcion, 'pvpunitario' => show_numero($l->pvpunitario, 6) . ' ', 'dto' => show_numero($l->pvpsindto - $l->pvptotal, 6) . ' ', 'pvptotal' => show_numero($l->pvptotal, 6) . ' '), $spacing);
                    }
                    // - Fin Detalle Tabla
                } else {
                    //Anchos de la tabla
                    $this->SetWidths(array('codigo' => $anchocol, 'cantidad' => $anchocol, 'descripcion' => $anchocol * 4, 'pvpunitario' => $anchocol, 'dto' => $anchocol, 'pvptotal' => $anchocol));
                    //Alineacion Headers
                    $this->SetAligns(array('codigo' => 'C', 'cantidad' => 'C', 'descripcion' => 'C', 'pvpunitario' => 'C', 'dto' => 'C', 'pvptotal' => 'C'));
                    // - Titulos Tabla
                    $this->Titulos(array('codigo' => 'Código', 'cantidad' => 'Cantidad' . ' ', 'descripcion' => 'Descripción', 'pvpunitario' => 'Precio Unitario', 'dto' => 'Descuento', 'pvptotal' => 'Total'), $spacing);
                    // - Fin Titulos Tabla
                    // - Detalle Tabla
                    $this->SetFont('Times', '', $letra);
                    foreach ($documento->getlineas() as $key => $l) {
                        //Ubico los anchos de las celdas
                        $this->SetAligns(array('codigo' => 'L', 'cantidad' => 'R', 'descripcion' => 'L', 'pvpunitario' => 'R', 'dto' => 'R', 'pvptotal' => 'R'));
                        $this->Fila(array('codigo' => ' ' . $l->codprincipal, 'cantidad' => $l->cantidad . ' ', 'descripcion' => ' ' . $l->descripcion, 'pvpunitario' => show_numero($l->pvpunitario, 6) . ' ', 'dto' => show_numero($l->pvpsindto - $l->pvptotal, 6) . ' ', 'pvptotal' => show_numero($l->pvptotal, 6) . ' '), $spacing);
                    }
                    // - Fin Detalle Tabla
                }
            } else if ($documento->coddocumento == '05') {
                //Anchos de la tabla
                $this->SetWidths(array('razon' => $anchocol, 'valor' => $anchocol));
                //Alineacion Headers
                $this->SetAligns(array('razon' => 'C', 'valor' => 'C'));
                // - Titulos Tabla
                $this->Titulos(array('razon' => 'RAZON DE LA MODIFICACION', 'valor' => 'VALOR DE LA MODIFICACION' . ' '), $spacing);
                // - Fin Titulos Tabla
                // - Detalle Tabla
                $this->SetFont('Times', '', $letra);
                foreach ($documento->getlineas() as $key => $l) {
                    //Ubico los anchos de las celdas
                    $this->SetAligns(array('razon' => 'L', 'valor' => 'R'));
                    $this->Fila(array('razon' => ' ' . $l->descripcion, 'valor' => show_numero($l->pvptotal, 6) . ' '), $spacing);
                }
            }
        }
    }

    public function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    public function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    public function SetBorder($a)
    {
        //Set the array of column alignments
        $this->border = $a;
    }

    public function Titulos($data, $spacing)
    {
        //Calculate the height of the row
        $nb = 0;
        foreach ($data as $key => $d) {
            $nb = max($nb, $this->NbLines($this->widths[$key], $d));
        }

        $h = $spacing * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        foreach ($data as $key => $d) {
            $w = $this->widths[$key];
            $a = isset($this->aligns[$key]) ? $this->aligns[$key] : 'L';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            $this->Rect($x, $y, $w, $h);
            //Print the text
            $this->MultiCell($w, $spacing, $d, 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    public function Fila($data, $spacing)
    {
        //Calculate the height of the row
        $nb = 0;
        foreach ($data as $key => $d) {
            $nb = max($nb, $this->NbLines($this->widths[$key], $d));
        }

        $h = $spacing * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        foreach ($data as $key => $d) {
            $w = $this->widths[$key];
            $a = isset($this->aligns[$key]) ? $this->aligns[$key] : 'L';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            $this->Rect($x, $y, $w, $h);
            //Print the text
            $this->MultiCell($w, $spacing, $d, 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    public function FilaSB($data, $spacing)
    {
        //Calculate the height of the row
        $nb = 0;
        foreach ($data as $key => $d) {
            $nb = max($nb, $this->NbLines($this->widths[$key], $d));
        }

        $h = $spacing * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        foreach ($data as $key => $d) {
            $w = $this->widths[$key];
            $a = isset($this->aligns[$key]) ? $this->aligns[$key] : 'L';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            //$this->Rect($x, $y, $w, $h);
            //Print the text
            $this->MultiCell($w, $spacing, $d, 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    public function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    public function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }

        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s    = str_replace("\r", '', $txt);
        $nb   = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") {
            $nb--;
        }

        $sep = -1;
        $i   = 0;
        $j   = 0;
        $l   = 0;
        $nl  = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }

            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }

                } else {
                    $i = $sep + 1;
                }

                $sep = -1;
                $j   = $i;
                $l   = 0;
                $nl++;
            } else {
                $i++;
            }

        }
        return $nl;
    }
}
