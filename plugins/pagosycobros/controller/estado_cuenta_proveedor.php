<?php
require_once 'extras/fpdf/PDF.php';

require_once 'extras/phpexcel/PHPExcel.php';
require_once 'extras/phpexcel/PHPExcel/IOFactory.php';

/**
 * Controlador de Proveedores -> Estados de Cuenta.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class estado_cuenta_proveedor extends controller
{
    public $idempresa;
    public $fec_desde;
    public $fec_hasta;
    public $idestablecimiento;
    public $idproveedor;
    public $estado;
    public $formato;
    public $tipodet;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Estados de Cuenta', 'Informes', 'Proveedores', true, false, 'bi bi-cart-dash-fill');
    }

    protected function private_core()
    {
        $this->init_models();
        $this->init_filter();

        if (isset($_GET['buscar_proveedor'])) {
            $this->buscar_proveedor();
        } else if (isset($_POST['inf_est_cuenta'])) {
            switch ($_POST['inf_est_cuenta']) {
                case 'totalizado':
                    if ($_POST['formato'] == 'pdf') {
                        $this->pdf_totalizado_est_cuenta();
                    } else if ($_POST['formato'] == 'xls') {
                        $this->xls_totalizado_est_cuenta();
                    }
                    break;
                default:
                    break;
            }
        }
    }

    private function init_models()
    {
        $this->proveedores     = new proveedores();
        $this->trans_pagos     = new trans_pagos();
        $this->establecimiento = new establecimiento();
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

        $this->estado = false;
        if (isset($_POST['estado'])) {
            $this->estado = $_POST['estado'];
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

    private function pdf_totalizado_est_cuenta()
    {
        $datos = $this->trans_pagos->getEstadoCuentaProveedor($this->idempresa, $this->idproveedor, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->estado);
        if ($datos) {
            $pdf = new PDF();
            //$pdf = new PDF();
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
            $pdf->MultiCell($ancho, $spacing, 'ESTADO DE CUENTA TOTALIZADO DE PROVEEDORES', '', 'C');
            $filtro = '';
            if ($this->fec_desde) {
                $filtro .= 'Desde: ' . date('d-m-Y', strtotime($this->fec_desde));
            }
            if ($this->fec_hasta) {
                if ($filtro != '') {
                    $filtro .= ' - ';
                }
                $filtro .= 'Hasta: ' . date('d-m-Y', strtotime($this->fec_hasta));
            }
            $y1 += $spacing;

            if ($filtro != '') {
                $pdf->SetXY($x, $y1);
                $pdf->SetFont('Times', '', 11);
                $pdf->MultiCell($ancho, $spacing, $filtro, '', 'C');
            }

            $idproveedor = '';
            $t_total     = 0;
            $t_abono     = 0;
            $t_saldo     = 0;

            foreach ($datos as $key => $d) {
                if ($idproveedor != $d['idproveedor']) {
                    $y1 = $pdf->GetY();
                    $y1 += ($spacing);
                    if ($idproveedor != '') {
                        //Sumo Totales de Totales
                        $t_total += $total;
                        $t_abono += $abono;
                        $t_saldo += $saldo;
                        $pdf->SetX($x);
                        $pdf->SetFont('Times', 'B', 10);
                        $pdf->SetWidths(array(130, 20, 20, 20));
                        $pdf->SetAligns(array('R', 'R', 'R', 'R'));
                        $pdf->Fila(
                            array(
                                'TOTALES:',
                                show_numero(floatval($total)),
                                show_numero(floatval($abono)),
                                show_numero(floatval($saldo)),
                            ),
                            $spacing);
                        $y1 += $spacing;
                    }

                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetWidths(array(190));
                    $pdf->SetAligns(array('L'));
                    $pdf->FilaSB(array(''), $spacing);
                    $pdf->FilaSB(array($d['identificacion'] . ' - ' . $d['razonsocial']), $spacing);
                    $pdf->SetFont('Times', '', 10);
                    $pdf->FilaSB(array($d['direccion'] . ' - (' . $d['telefono'] . ')'), $spacing);
                    $idproveedor = $d['idproveedor'];
                    //Genero las cabeceras
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetWidths(array(30, 35, 25, 25, 15, 20, 20, 20));
                    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                    $pdf->Titulos(array('Tipo Documento', 'N. Documento', 'F. Emisión', 'Vencimiento', 'Dias', 'Total', 'Abono', 'Saldo'), $spacing);

                    $total = 0;
                    $abono = 0;
                    $saldo = 0;
                }

                $pdf->SetX($x);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetAligns(array('L', 'L', 'R', 'R', 'R', 'R', 'R', 'R'));
                $pdf->Fila(
                    array(
                        $d['tipodoc'],
                        $d['numero_documento'],
                        date('d-m-Y', strtotime($d['fec_emision'])),
                        $d['vencimiento'] ? date('d-m-Y', strtotime($d['vencimiento'])) : '',
                        $d['dias'],
                        show_numero(floatval($d['total'])),
                        show_numero(floatval($d['abono'])),
                        show_numero(floatval($d['saldo'])),
                    ),
                    $spacing);

                $total += floatval($d['total']);
                $abono += floatval($d['abono']);
                $saldo += floatval($d['saldo']);
            }

            //Sumo Totales de Totales
            $pdf->SetX($x);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(130, 20, 20, 20));
            $pdf->SetAligns(array('R', 'R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTALES:',
                    show_numero(floatval($total)),
                    show_numero(floatval($abono)),
                    show_numero(floatval($saldo)),
                ),
                $spacing);
            $y1 = $pdf->GetY();
            $y1 += ($spacing);
            $t_total += $total;
            $t_abono += $abono;
            $t_saldo += $saldo;

            $pdf->SetX($x);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(130, 20, 20, 20));
            $pdf->SetAligns(array('R', 'R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTALES FINALES:',
                    show_numero(floatval($t_total)),
                    show_numero(floatval($t_abono)),
                    show_numero(floatval($t_saldo)),
                ),
                $spacing);

            $pdf->show('estado_cuenta_proveedor_' . $this->user->nick . '.pdf');
        } else {
            $this->new_advice("Sin Datos para generar el reporte.");
        }
    }

    private function xls_totalizado_est_cuenta()
    {
        $datos = $this->trans_pagos->getEstadoCuentaProveedor($this->idempresa, $this->idproveedor, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->estado);
        if ($datos) {
            $this->template = false;
            $objPHPExcel    = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Konta")->setTitle("ESTADO DE CUENTA TOTALIZADO DE PROVEEDORES");
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle("Estado de Cuenta");
            $titulo  = $this->empresa->razonsocial;
            $titulo1 = "ESTADO DE CUENTA TOTALIZADO DE PROVEEDORES";
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:J2');
            $objPHPExcel->getActiveSheet()->setCellValue('A1', $titulo);
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $titulo1);
            $filtro = '';
            if ($this->fec_desde) {
                $filtro .= 'Desde: ' . date('d-m-Y', strtotime($this->fec_desde));
            }
            if ($this->fec_hasta) {
                if ($filtro != '') {
                    $filtro .= ' - ';
                }
                $filtro .= 'Hasta: ' . date('d-m-Y', strtotime($this->fec_hasta));
            }
            $i = 2;
            if ($filtro != '') {
                $i++;
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':J' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A1:J' . $i)->applyFromArray(styleCabeceraReporte());

            //agrego la cabecera de la tabla
            $i++;
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, 'Identificacion')
                ->setCellValue('B' . $i, 'Razon Social')
                ->setCellValue('C' . $i, 'Documento')
                ->setCellValue('D' . $i, 'Num. Documento')
                ->setCellValue('E' . $i, 'Fecha Emisión')
                ->setCellValue('F' . $i, 'Vencimiento')
                ->setCellValue('G' . $i, 'Dias')
                ->setCellValue('H' . $i, 'Total')
                ->setCellValue('I' . $i, 'Abono')
                ->setCellValue('J' . $i, 'Saldo');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':J' . $i)->applyFromArray(styleNombreColumnas());
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':J' . $i);

            //Totales de Reporte
            $t_total     = 0;
            $t_abono     = 0;
            $t_saldo     = 0;

            //Recorro el detalle
            foreach ($datos as $key => $d) {
                $i++;
                $objPHPExcel->getActiveSheet()
                    ->setCellValueExplicit('A' . $i, $d['identificacion'], PHPExcel_Cell_DataType::TYPE_STRING)
                    ->setCellValue('B' . $i, $d['razonsocial'])
                    ->setCellValue('C' . $i, $d['tipodoc'])
                    ->setCellValue('D' . $i, $d['numero_documento'])
                    ->setCellValue('E' . $i, $d['fec_emision'])
                    ->setCellValue('F' . $i, $d['vencimiento'])
                    ->setCellValue('G' . $i, $d['dias'])
                    ->setCellValue('H' . $i, $d['total'])
                    ->setCellValue('I' . $i, $d['abono'])
                    ->setCellValue('J' . $i, $d['saldo']);

                $t_total += floatval($d['total']);
                $t_abono += floatval($d['abono']);
                $t_saldo += floatval($d['saldo']);
            }

            $i++;
            $i++;
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':D' . $i);
            $objPHPExcel->getActiveSheet()->mergeCells('E' . $i . ':G' . $i);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, $this->user->nick . " - " . date('d-m-Y H:i:s'))
                ->setCellValue('E' . $i, 'TOTALES')
                ->setCellValue('H' . $i, $t_total)
                ->setCellValue('I' . $i, $t_abono)
                ->setCellValue('J' . $i, $t_saldo);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':J' . $i)->applyFromArray(styleNegrita());
            //Auto ajustar cells
            for ($j = 'A'; $j <= 'J'; $j++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($j)->setAutoSize(true);
            }

            $nombrearchivo = 'estado_cuenta_proveedor_' . $this->user->nick . '.xlsx';
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
