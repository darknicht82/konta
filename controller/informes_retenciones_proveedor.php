<?php
require_once 'extras/fpdf/PDF.php';

require_once 'extras/phpexcel/PHPExcel.php';
require_once 'extras/phpexcel/PHPExcel/IOFactory.php';

/**
 * Controlador de Proveedores -> Retenciones.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class informes_retenciones_proveedor extends controller
{
    public $idempresa;
    public $fec_desde;
    public $fec_hasta;
    public $idestablecimiento;
    public $idproveedor;
    public $especie;
    public $formato;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Retenciones', 'Informes', 'Proveedores', true, false, 'bi bi-clipboard2-minus');
    }

    protected function private_core()
    {
        $this->init_models();
        $this->init_filter();

        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_GET['buscar_proveedor'])) {
            $this->buscar_proveedor();
        } else if (isset($_POST['inf_retenciones'])) {
            switch ($_POST['inf_retenciones']) {
                case 'compras':
                    if ($_POST['formato'] == 'pdf') {
                        $this->pdf_retenciones_compras();
                    } else if ($_POST['formato'] == 'xls') {
                        $this->xls_retenciones_compras();
                    }
                    break;
                case 'ventas':
                    if ($_POST['formato'] == 'pdf') {
                        $this->pdf_retenciones_ventas();
                    } else if ($_POST['formato'] == 'xls') {
                        $this->xls_retenciones_ventas();
                    }
                    break;
                default:
                    break;
            }
        }
    }

    private function init_models()
    {
        $this->lineasretencionescli = new lineasretencionescli();
        $this->lineasfacturasprov   = new lineasfacturasprov();
        $this->establecimiento      = new establecimiento();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;

        $this->fec_desde = false;
        if (isset($_POST['fec_desde'])) {
            $this->fec_desde = $_POST['fec_desde'];
        }

        $this->fec_hasta = false;
        if (isset($_POST['fec_hasta'])) {
            $this->fec_hasta = $_POST['fec_hasta'];
        }

        $this->idestablecimiento = false;
        if (isset($_POST['idestablecimiento'])) {
            $this->idestablecimiento = $_POST['idestablecimiento'];
        }

        $this->idproveedor = false;
        if (isset($_POST['idproveedor'])) {
            $this->idproveedor = $_POST['idproveedor'];
        }

        $this->idcliente = false;
        if (isset($_POST['idcliente'])) {
            $this->idcliente = $_POST['idcliente'];
        }

        $this->especie = false;
        if (isset($_POST['especie'])) {
            $this->especie = $_POST['especie'];
        }

        $this->formato = false;
        if (isset($_POST['formato'])) {
            $this->formato = $_POST['formato'];
        }
    }

    private function buscar_proveedor()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_proveedores($this->idempresa, $_GET['buscar_proveedor']);
        echo json_encode($result);
        exit;
    }

    private function buscar_cliente()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_clientes($this->idempresa, $_GET['buscar_cliente']);
        echo json_encode($result);
        exit;
    }

    private function pdf_retenciones_compras()
    {
        $pdf = new PDF('A4', 'L');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->Header($this->user->nick);
        $pdf->cabecera_empresa($this->empresa);
        $ancho   = $pdf->GetPageWidth() - 20;
        $spacing = 5;
        $y1      = $pdf->GetY() + $spacing;
        $x       = 10;
        $pdf->SetXY($x, $y1);
        $pdf->SetFont('Times', 'B', 12);
        $pdf->MultiCell($ancho, $spacing, 'INFORME RETENCIONES DE COMPRAS', '', 'C');
        $filtro  = '';
        $filtro2 = '';
        if ($this->fec_desde) {
            $filtro .= 'Desde: ' . date('d-m-Y', strtotime($this->fec_desde));
        }
        if ($this->fec_hasta) {
            if ($filtro != '') {
                $filtro .= ' - ';
            }
            $filtro .= 'Hasta: ' . date('d-m-Y', strtotime($this->fec_hasta));
        }

        if ($this->especie) {
            $filtro2 .= 'Especie: ' . strtoupper($this->especie);
        }

        if ($filtro != '') {
            $y1 += $spacing;
            $pdf->SetXY($x, $y1);
            $pdf->SetFont('Times', '', 11);
            $pdf->MultiCell($ancho, $spacing, $filtro, '', 'C');
        }

        if ($filtro2 != '') {
            $y1 += $spacing;
            $pdf->SetXY($x, $y1);
            $pdf->SetFont('Times', '', 11);
            $pdf->MultiCell($ancho, $spacing, $filtro2, '', 'C');
        }

        $retiva = array();
        $retren = array();

        if ($this->especie == 'renta') {
            $retren = $this->lineasfacturasprov->listado_ret_renta($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idproveedor);
        } else if ($this->especie == 'iva') {
            $retiva = $this->lineasfacturasprov->listado_ret_iva($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idproveedor);
        } else {
            $retren = $this->lineasfacturasprov->listado_ret_renta($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idproveedor);
            $retiva = $this->lineasfacturasprov->listado_ret_iva($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idproveedor);
        }
        $sdatos       = true;
        $t_base_renta = 0;
        $t_ret_renta  = 0;
        $t_base_iva   = 0;
        $t_ret_iva    = 0;
        if ($retren) {
            $sdatos = false;
            $y1 += $spacing;
            $y1 += $spacing;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(275));
            $pdf->SetAligns(array('L'));
            $pdf->FilaSB(array(''), $spacing);
            $pdf->FilaSB(array('RETENCIONES DE RENTA'), $spacing);
            $y1 += $spacing;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(30, 80, 30, 25, 25, 20, 35, 30));
            $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
            $pdf->Titulos(array('N. Retención', 'Proveedor', 'N. Documento', 'F. Emisión', 'F. Aut. Retención.', 'Porcentaje', 'Base Imponible', 'Valor Retención'), $spacing);
            $base       = 0;
            $ret        = 0;
            $tiporetren = '';
            $y1 += $spacing;
            foreach ($retren as $key => $d) {
                if ($tiporetren != $d['idretencion_renta']) {
                    if ($tiporetren != '') {
                        $t_base_renta += $base;
                        $t_ret_renta += $ret;
                        $pdf->SetFont('Times', 'B', 10);
                        $pdf->SetWidths(array(210, 35, 30));
                        $pdf->SetAligns(array('R', 'R', 'R'));
                        $pdf->Fila(
                            array(
                                'TOTAL:',
                                show_numero(floatval($base)),
                                show_numero(floatval($ret)),
                            ),
                            $spacing);
                    }
                    $base       = 0;
                    $ret        = 0;
                    $tiporetren = $d['idretencion_renta'];
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetWidths(array(275));
                    $pdf->SetAligns(array('L'));
                    $pdf->FilaSB(array($d['nombre'] . ' - ' . $d['codigo']), $spacing);
                }
                $base += $d['baseimp'];
                $ret += $d['valret'];

                $pdf->SetFont('Times', '', 9);
                $pdf->SetWidths(array(30, 80, 30, 25, 25, 20, 35, 30));
                $pdf->SetAligns(array('R', 'L', 'R', 'R', 'R', 'R', 'R', 'R'));
                $pdf->Fila(
                    array(
                        $d['numero_retencion'],
                        $d['razonsocial'],
                        $d['numero_documento'],
                        $d['fec_emision'],
                        $d['fec_autorizacion_ret'],
                        show_numero(floatval($d['porcentaje'])),
                        show_numero(floatval($d['baseimp'])),
                        show_numero(floatval($d['valret'])),
                    ), $spacing
                );
            }
            $t_base_renta += $base;
            $t_ret_renta += $ret;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(210, 35, 30));
            $pdf->SetAligns(array('R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL:',
                    show_numero(floatval($base)),
                    show_numero(floatval($ret)),
                ),
                $spacing);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(210, 35, 30));
            $pdf->SetAligns(array('R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL RETENCIONES RENTA:',
                    show_numero(floatval($t_base_renta)),
                    show_numero(floatval($t_ret_renta)),
                ),
                $spacing);
        }

        if ($retiva) {
            $sdatos = false;
            $y1 += $spacing;
            $y1 += $spacing;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(275));
            $pdf->SetAligns(array('L'));
            $pdf->FilaSB(array(''), $spacing);
            $pdf->FilaSB(array('RETENCIONES DE IVA'), $spacing);
            $y1 += $spacing;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(30, 80, 30, 25, 25, 20, 35, 30));
            $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
            $pdf->Titulos(array('N. Retención', 'Proveedor', 'N. Documento', 'F. Emisión', 'F. Aut. Retención.', 'Porcentaje', 'Base Imponible', 'Valor Retención'), $spacing);
            $base       = 0;
            $ret        = 0;
            $tiporetiva = '';
            $y1 += $spacing;
            foreach ($retiva as $key => $d) {
                if ($tiporetiva != $d['idretencion_iva']) {
                    if ($tiporetiva != '') {
                        $t_base_iva += $base;
                        $t_ret_iva += $ret;
                        $pdf->SetFont('Times', 'B', 10);
                        $pdf->SetWidths(array(210, 35, 30));
                        $pdf->SetAligns(array('R', 'R', 'R'));
                        $pdf->Fila(
                            array(
                                'TOTAL:',
                                show_numero(floatval($base)),
                                show_numero(floatval($ret)),
                            ),
                            $spacing);
                    }
                    $base       = 0;
                    $ret        = 0;
                    $tiporetiva = $d['idretencion_iva'];
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetWidths(array(275));
                    $pdf->SetAligns(array('L'));
                    $pdf->FilaSB(array($d['nombre'] . ' - ' . $d['codigo']), $spacing);
                }
                $base += $d['baseimp'];
                $ret += $d['valret'];
                $pdf->SetFont('Times', '', 9);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetWidths(array(30, 80, 30, 25, 25, 20, 35, 30));
                $pdf->SetAligns(array('R', 'L', 'R', 'R', 'R', 'R', 'R', 'R'));
                $pdf->Fila(
                    array(
                        $d['numero_retencion'],
                        $d['razonsocial'],
                        $d['numero_documento'],
                        $d['fec_emision'],
                        $d['fec_autorizacion_ret'],
                        show_numero(floatval($d['porcentaje'])),
                        show_numero(floatval($d['baseimp'])),
                        show_numero(floatval($d['valret'])),
                    ), $spacing
                );
            }
            $t_base_iva += $base;
            $t_ret_iva += $ret;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(210, 35, 30));
            $pdf->SetAligns(array('R', 'R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL:',
                    show_numero(floatval($base)),
                    show_numero(floatval($ret)),
                ),
                $spacing);

            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(210, 35, 30));
            $pdf->SetAligns(array('R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL RETENCIONES IVA:',
                    show_numero(floatval($t_base_iva)),
                    show_numero(floatval($t_ret_iva)),
                ),
                $spacing);
        }

        if ($sdatos) {
            $y1 += $spacing;
            $y1 += $spacing;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(275));
            $pdf->SetAligns(array('L'));
            $pdf->FilaSB(array(''), $spacing);
            $pdf->FilaSB(array('SIN DATOS PARA GENERAR EL REPORTE'), $spacing);
        } else {
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(275));
            $pdf->SetAligns(array('R'));
            $pdf->FilaSB(array(''), $spacing);
            $pdf->SetWidths(array(210, 35, 30));
            $pdf->SetAligns(array('R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL RETENCIONES:',
                    show_numero(floatval($t_base_iva + $t_base_renta)),
                    show_numero(floatval($t_ret_iva + $t_ret_renta)),
                ),
                $spacing);
        }

        $pdf->show('listado_retcompras_' . $this->user->nick.'.pdf');
    }

    private function xls_retenciones_compras()
    {
        $retiva = array();
        $retren = array();

        if ($this->especie == 'renta') {
            $retren = $this->lineasfacturasprov->listado_ret_renta($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idproveedor);
        } else if ($this->especie == 'iva') {
            $retiva = $this->lineasfacturasprov->listado_ret_iva($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idproveedor);
        } else {
            $retren = $this->lineasfacturasprov->listado_ret_renta($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idproveedor);
            $retiva = $this->lineasfacturasprov->listado_ret_iva($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idproveedor);
        }
        if ($retren || $retiva) {
            $this->template = false;
            $objPHPExcel    = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Konta")->setTitle("INFORME RETENCIONES DE COMPRAS");
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle("INFORME RETENCIONES DE COMPRAS");
            $titulo  = $this->empresa->razonsocial;
            $titulo1 = "INFORME RETENCIONES DE COMPRAS";
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:K2');
            $objPHPExcel->getActiveSheet()->setCellValue('A1', $titulo);
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $titulo1);
            $filtro  = '';
            $filtro2 = '';

            if ($this->fec_desde) {
                $filtro .= 'Desde: ' . date('d-m-Y', strtotime($this->fec_desde));
            }
            if ($this->fec_hasta) {
                if ($filtro != '') {
                    $filtro .= ' - ';
                }
                $filtro .= 'Hasta: ' . date('d-m-Y', strtotime($this->fec_hasta));
            }
            if ($this->especie) {
                $filtro2 .= 'Especie: ' . strtoupper($this->especie);
            }
            $i = 2;
            if ($filtro != '') {
                $i++;
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':K' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro);
            }

            if ($filtro2 != '') {
                $i++;
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':K' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro2);
            }

            $objPHPExcel->getActiveSheet()->getStyle('A1:K' . $i)->applyFromArray(styleCabeceraReporte());

            //agrego la cabecera de la tabla
            $i++;
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, 'N. Retención')
                ->setCellValue('B' . $i, 'Proveedor')
                ->setCellValue('C' . $i, 'N. Documento')
                ->setCellValue('D' . $i, 'F. Emisión')
                ->setCellValue('E' . $i, 'F. Aut. Retención.')
                ->setCellValue('F' . $i, 'Especie')
                ->setCellValue('G' . $i, 'Código')
                ->setCellValue('H' . $i, 'Nombre Retención')
                ->setCellValue('I' . $i, 'Porcentaje')
                ->setCellValue('J' . $i, 'Base Imponible')
                ->setCellValue('K' . $i, 'Valor Retención');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':K' . $i)->applyFromArray(styleNombreColumnas());
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':K' . $i);

            //Totales de Reporte
            $t_base = 0;
            $t_ret  = 0;
            //Recorro el detalle
            foreach ($retren as $key => $rt) {
                $i++;
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $i, $rt['numero_retencion'])
                    ->setCellValue('B' . $i, $rt['razonsocial'])
                    ->setCellValue('C' . $i, $rt['numero_documento'])
                    ->setCellValue('D' . $i, $rt['fec_emision'])
                    ->setCellValue('E' . $i, $rt['fec_autorizacion_ret'])
                    ->setCellValue('F' . $i, 'RENTA')
                    ->setCellValue('G' . $i, $rt['codigo'])
                    ->setCellValue('H' . $i, $rt['nombre'])
                    ->setCellValue('I' . $i, $rt['porcentaje'])
                    ->setCellValue('J' . $i, $rt['baseimp'])
                    ->setCellValue('K' . $i, $rt['valret']);
                $t_base += floatval($rt['baseimp']);
                $t_ret += floatval($rt['valret']);
            }

            foreach ($retiva as $key => $ri) {
                $i++;
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $i, $ri['numero_retencion'])
                    ->setCellValue('B' . $i, $ri['razonsocial'])
                    ->setCellValue('C' . $i, $ri['numero_documento'])
                    ->setCellValue('D' . $i, $ri['fec_emision'])
                    ->setCellValue('E' . $i, $ri['fec_autorizacion_ret'])
                    ->setCellValue('F' . $i, 'IVA')
                    ->setCellValue('G' . $i, $ri['codigo'])
                    ->setCellValue('H' . $i, $ri['nombre'])
                    ->setCellValue('I' . $i, $ri['porcentaje'])
                    ->setCellValue('J' . $i, $ri['baseimp'])
                    ->setCellValue('K' . $i, $ri['valret']);
                $t_base += floatval($ri['baseimp']);
                $t_ret += floatval($ri['valret']);
            }

            $i++;
            $i++;
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':C' . $i);
            $objPHPExcel->getActiveSheet()->mergeCells('D' . $i . ':I' . $i);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, $this->user->nick . " - " . date('d-m-Y H:i:s'))
                ->setCellValue('D' . $i, 'TOTALES')
                ->setCellValue('J' . $i, round($t_base, 2))
                ->setCellValue('K' . $i, round($t_ret, 2));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':K' . $i)->applyFromArray(styleNegrita());
            //Auto ajustar cells
            for ($j = 'A'; $j <= 'K'; $j++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($j)->setAutoSize(true);
            }

            $nombrearchivo = 'listado_retcompras_' . $this->user->nick . '.xlsx';
            $objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save($nombrearchivo);
            $objPHPExcel->disconnectWorksheets();
            unset($objPHPExcel);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($nombrearchivo));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($nombrearchivo));
            ob_clean();
            flush();
            readfile($nombrearchivo);
            unlink($nombrearchivo);
            exit;
        } else {
            $this->new_advice("Sin Datos para generar el reporte.");
        }
    }

    private function pdf_retenciones_ventas()
    {
        $pdf = new PDF('A4', 'L');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->Header($this->user->nick);
        $pdf->cabecera_empresa($this->empresa);
        $ancho   = $pdf->GetPageWidth() - 20;
        $spacing = 5;
        $y1      = $pdf->GetY() + $spacing;
        $x       = 10;
        $pdf->SetXY($x, $y1);
        $pdf->SetFont('Times', 'B', 12);
        $pdf->MultiCell($ancho, $spacing, 'INFORME RETENCIONES DE VENTAS', '', 'C');
        $filtro  = '';
        $filtro2 = '';
        if ($this->fec_desde) {
            $filtro .= 'Desde: ' . date('d-m-Y', strtotime($this->fec_desde));
        }
        if ($this->fec_hasta) {
            if ($filtro != '') {
                $filtro .= ' - ';
            }
            $filtro .= 'Hasta: ' . date('d-m-Y', strtotime($this->fec_hasta));
        }

        if ($this->especie) {
            $filtro2 .= 'Especie: ' . strtoupper($this->especie);
        }

        if ($filtro != '') {
            $y1 += $spacing;
            $pdf->SetXY($x, $y1);
            $pdf->SetFont('Times', '', 11);
            $pdf->MultiCell($ancho, $spacing, $filtro, '', 'C');
        }

        if ($filtro2 != '') {
            $y1 += $spacing;
            $pdf->SetXY($x, $y1);
            $pdf->SetFont('Times', '', 11);
            $pdf->MultiCell($ancho, $spacing, $filtro2, '', 'C');
        }

        $retiva = array();
        $retren = array();

        if ($this->especie == 'renta') {
            $retren = $this->lineasretencionescli->listado_retencion($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idcliente, 'renta');
        } else if ($this->especie == 'iva') {
            $retiva = $this->lineasretencionescli->listado_retencion($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idcliente, 'iva');
        } else {
            $retren = $this->lineasretencionescli->listado_retencion($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idcliente, 'renta');
            $retiva = $this->lineasretencionescli->listado_retencion($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idcliente, 'iva');
        }

        $sdatos       = true;
        $t_base_renta = 0;
        $t_ret_renta  = 0;
        $t_base_iva   = 0;
        $t_ret_iva    = 0;
        if ($retren) {
            $sdatos = false;
            $y1 += $spacing;
            $y1 += $spacing;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(275));
            $pdf->SetAligns(array('L'));
            $pdf->FilaSB(array(''), $spacing);
            $pdf->FilaSB(array('RETENCIONES DE RENTA'), $spacing);
            $y1 += $spacing;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(30, 80, 30, 25, 25, 20, 35, 30));
            $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
            $pdf->Titulos(array('N. Retención', 'Cliente', 'N. Documento', 'F. Emisión', 'F. Aut. Retención.', 'Porcentaje', 'Base Imponible', 'Valor Retención'), $spacing);
            $base       = 0;
            $ret        = 0;
            $tiporetren = '';
            $y1 += $spacing;
            foreach ($retren as $key => $d) {
                if ($tiporetren != $d['idtiporetencion']) {
                    if ($tiporetren != '') {
                        $t_base_renta += $base;
                        $t_ret_renta += $ret;
                        $pdf->SetFont('Times', 'B', 10);
                        $pdf->SetWidths(array(210, 35, 30));
                        $pdf->SetAligns(array('R', 'R', 'R'));
                        $pdf->Fila(
                            array(
                                'TOTAL:',
                                show_numero(floatval($base)),
                                show_numero(floatval($ret)),
                            ),
                            $spacing);
                    }
                    $base       = 0;
                    $ret        = 0;
                    $tiporetren = $d['idtiporetencion'];
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetWidths(array(275));
                    $pdf->SetAligns(array('L'));
                    $pdf->FilaSB(array($d['nombre'] . ' - ' . $d['codigo']), $spacing);
                }
                $base += $d['baseimp'];
                $ret += $d['valret'];

                $pdf->SetFont('Times', '', 9);
                $pdf->SetWidths(array(30, 80, 30, 25, 25, 20, 35, 30));
                $pdf->SetAligns(array('R', 'L', 'R', 'R', 'R', 'R', 'R', 'R'));
                $pdf->Fila(
                    array(
                        $d['numero_retencion'],
                        $d['razonsocial'],
                        $d['numero_documento'],
                        $d['fec_emision'],
                        $d['fec_autorizacion_ret'],
                        show_numero(floatval($d['porcentaje'])),
                        show_numero(floatval($d['baseimp'])),
                        show_numero(floatval($d['valret'])),
                    ), $spacing
                );
            }
            $t_base_renta += $base;
            $t_ret_renta += $ret;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(210, 35, 30));
            $pdf->SetAligns(array('R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL:',
                    show_numero(floatval($base)),
                    show_numero(floatval($ret)),
                ),
                $spacing);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(210, 35, 30));
            $pdf->SetAligns(array('R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL RETENCIONES RENTA:',
                    show_numero(floatval($t_base_renta)),
                    show_numero(floatval($t_ret_renta)),
                ),
                $spacing);
        }

        if ($retiva) {
            $sdatos = false;
            $y1 += $spacing;
            $y1 += $spacing;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(275));
            $pdf->SetAligns(array('L'));
            $pdf->FilaSB(array(''), $spacing);
            $pdf->FilaSB(array('RETENCIONES DE IVA'), $spacing);
            $y1 += $spacing;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(30, 80, 30, 25, 25, 20, 35, 30));
            $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
            $pdf->Titulos(array('N. Retención', 'Cliente', 'N. Documento', 'F. Emisión', 'F. Aut. Retención.', 'Porcentaje', 'Base Imponible', 'Valor Retención'), $spacing);
            $base       = 0;
            $ret        = 0;
            $tiporetiva = '';
            $y1 += $spacing;
            foreach ($retiva as $key => $d) {
                if ($tiporetiva != $d['idtiporetencion']) {
                    if ($tiporetiva != '') {
                        $t_base_iva += $base;
                        $t_ret_iva += $ret;
                        $pdf->SetFont('Times', 'B', 10);
                        $pdf->SetWidths(array(210, 35, 30));
                        $pdf->SetAligns(array('R', 'R', 'R'));
                        $pdf->Fila(
                            array(
                                'TOTAL:',
                                show_numero(floatval($base)),
                                show_numero(floatval($ret)),
                            ),
                            $spacing);
                    }
                    $base       = 0;
                    $ret        = 0;
                    $tiporetiva = $d['idtiporetencion'];
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetWidths(array(275));
                    $pdf->SetAligns(array('L'));
                    $pdf->FilaSB(array($d['nombre'] . ' - ' . $d['codigo']), $spacing);
                }
                $base += $d['baseimp'];
                $ret += $d['valret'];
                $pdf->SetFont('Times', '', 9);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetWidths(array(30, 80, 30, 25, 25, 20, 35, 30));
                $pdf->SetAligns(array('R', 'L', 'R', 'R', 'R', 'R', 'R', 'R'));
                $pdf->Fila(
                    array(
                        $d['numero_retencion'],
                        $d['razonsocial'],
                        $d['numero_documento'],
                        $d['fec_emision'],
                        $d['fec_autorizacion_ret'],
                        show_numero(floatval($d['porcentaje'])),
                        show_numero(floatval($d['baseimp'])),
                        show_numero(floatval($d['valret'])),
                    ), $spacing
                );
            }
            $t_base_iva += $base;
            $t_ret_iva += $ret;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(210, 35, 30));
            $pdf->SetAligns(array('R', 'R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL:',
                    show_numero(floatval($base)),
                    show_numero(floatval($ret)),
                ),
                $spacing);

            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(210, 35, 30));
            $pdf->SetAligns(array('R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL RETENCIONES IVA:',
                    show_numero(floatval($t_base_iva)),
                    show_numero(floatval($t_ret_iva)),
                ),
                $spacing);
        }

        if ($sdatos) {
            $y1 += $spacing;
            $y1 += $spacing;
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(275));
            $pdf->SetAligns(array('L'));
            $pdf->FilaSB(array(''), $spacing);
            $pdf->FilaSB(array('SIN DATOS PARA GENERAR EL REPORTE'), $spacing);
        } else {
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(275));
            $pdf->SetAligns(array('R'));
            $pdf->FilaSB(array(''), $spacing);
            $pdf->SetWidths(array(210, 35, 30));
            $pdf->SetAligns(array('R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL RETENCIONES:',
                    show_numero(floatval($t_base_iva + $t_base_renta)),
                    show_numero(floatval($t_ret_iva + $t_ret_renta)),
                ),
                $spacing);
        }

        $pdf->show('listado_retventas_' . $this->user->nick.'.pdf');
    }

    private function xls_retenciones_ventas()
    {
        $retiva = array();
        $retren = array();

        if ($this->especie == 'renta') {
            $retren = $this->lineasretencionescli->listado_retencion($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idcliente, 'renta');
        } else if ($this->especie == 'iva') {
            $retiva = $this->lineasretencionescli->listado_retencion($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idcliente, 'iva');
        } else {
            $retren = $this->lineasretencionescli->listado_retencion($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idcliente, 'renta');
            $retiva = $this->lineasretencionescli->listado_retencion($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idcliente, 'iva');
        }

        if ($retren || $retiva) {
            $this->template = false;
            $objPHPExcel    = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Konta")->setTitle("INFORME RETENCIONES DE VENTAS");
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle("INFORME RETENCIONES DE VENTAS");
            $titulo  = $this->empresa->razonsocial;
            $titulo1 = "INFORME RETENCIONES DE VENTAS";
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:K2');
            $objPHPExcel->getActiveSheet()->setCellValue('A1', $titulo);
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $titulo1);
            $filtro  = '';
            $filtro2 = '';

            if ($this->fec_desde) {
                $filtro .= 'Desde: ' . date('d-m-Y', strtotime($this->fec_desde));
            }
            if ($this->fec_hasta) {
                if ($filtro != '') {
                    $filtro .= ' - ';
                }
                $filtro .= 'Hasta: ' . date('d-m-Y', strtotime($this->fec_hasta));
            }
            if ($this->especie) {
                $filtro2 .= 'Especie: ' . strtoupper($this->especie);
            }
            $i = 2;
            if ($filtro != '') {
                $i++;
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':K' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro);
            }

            if ($filtro2 != '') {
                $i++;
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':K' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro2);
            }

            $objPHPExcel->getActiveSheet()->getStyle('A1:K' . $i)->applyFromArray(styleCabeceraReporte());

            //agrego la cabecera de la tabla
            $i++;
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, 'N. Retención')
                ->setCellValue('B' . $i, 'Cliente')
                ->setCellValue('C' . $i, 'N. Documento')
                ->setCellValue('D' . $i, 'F. Emisión')
                ->setCellValue('E' . $i, 'F. Aut. Retención.')
                ->setCellValue('F' . $i, 'Especie')
                ->setCellValue('G' . $i, 'Código')
                ->setCellValue('H' . $i, 'Nombre Retención')
                ->setCellValue('I' . $i, 'Porcentaje')
                ->setCellValue('J' . $i, 'Base Imponible')
                ->setCellValue('K' . $i, 'Valor Retención');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':K' . $i)->applyFromArray(styleNombreColumnas());
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':K' . $i);

            //Totales de Reporte
            $t_base = 0;
            $t_ret  = 0;
            //Recorro el detalle
            foreach ($retren as $key => $rt) {
                $i++;
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $i, $rt['numero_retencion'])
                    ->setCellValue('B' . $i, $rt['razonsocial'])
                    ->setCellValue('C' . $i, $rt['numero_documento'])
                    ->setCellValue('D' . $i, $rt['fec_emision'])
                    ->setCellValue('E' . $i, $rt['fec_autorizacion_ret'])
                    ->setCellValue('F' . $i, 'RENTA')
                    ->setCellValue('G' . $i, $rt['codigo'])
                    ->setCellValue('H' . $i, $rt['nombre'])
                    ->setCellValue('I' . $i, $rt['porcentaje'])
                    ->setCellValue('J' . $i, $rt['baseimp'])
                    ->setCellValue('K' . $i, $rt['valret']);
                $t_base += floatval($rt['baseimp']);
                $t_ret += floatval($rt['valret']);
            }

            foreach ($retiva as $key => $ri) {
                $i++;
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $i, $ri['numero_retencion'])
                    ->setCellValue('B' . $i, $ri['razonsocial'])
                    ->setCellValue('C' . $i, $ri['numero_documento'])
                    ->setCellValue('D' . $i, $ri['fec_emision'])
                    ->setCellValue('E' . $i, $ri['fec_autorizacion_ret'])
                    ->setCellValue('F' . $i, 'IVA')
                    ->setCellValue('G' . $i, $ri['codigo'])
                    ->setCellValue('H' . $i, $ri['nombre'])
                    ->setCellValue('I' . $i, $ri['porcentaje'])
                    ->setCellValue('J' . $i, $ri['baseimp'])
                    ->setCellValue('K' . $i, $ri['valret']);
                $t_base += floatval($ri['baseimp']);
                $t_ret += floatval($ri['valret']);
            }

            $i++;
            $i++;
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':C' . $i);
            $objPHPExcel->getActiveSheet()->mergeCells('D' . $i . ':I' . $i);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, $this->user->nick . " - " . date('d-m-Y H:i:s'))
                ->setCellValue('D' . $i, 'TOTALES')
                ->setCellValue('J' . $i, round($t_base, 2))
                ->setCellValue('K' . $i, round($t_ret, 2));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':K' . $i)->applyFromArray(styleNegrita());
            //Auto ajustar cells
            for ($j = 'A'; $j <= 'K'; $j++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($j)->setAutoSize(true);
            }

            $nombrearchivo = 'listado_retventas_' . $this->user->nick . '.xlsx';
            $objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save($nombrearchivo);
            $objPHPExcel->disconnectWorksheets();
            unset($objPHPExcel);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($nombrearchivo));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($nombrearchivo));
            ob_clean();
            flush();
            readfile($nombrearchivo);
            unlink($nombrearchivo);
            exit;
        } else {
            $this->new_advice("Sin Datos para generar el reporte.");
        }
    }
}
