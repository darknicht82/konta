<?php
require_once 'extras/fpdf/PDF.php';

require_once 'extras/phpexcel/PHPExcel.php';
require_once 'extras/phpexcel/PHPExcel/IOFactory.php';

/**
 * Controlador de Informes -> Guias de Remision.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class informes_guias extends controller
{
    public $idempresa;
    public $fec_desde;
    public $fec_hasta;
    public $idestablecimiento;
    public $idcliente;
    public $tipo_guia;
    public $formato;
    public $tipodet;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Guias de Remisión', 'Informes', 'Clientes', true, false, 'bi bi-truck');
    }

    protected function private_core()
    {
        $this->init_models();
        $this->init_filter();

        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_POST['inf_guias'])) {
            switch ($_POST['inf_guias']) {
                case 'totalizado':
                    if ($_POST['formato'] == 'pdf') {
                        $this->pdf_totalizado_guias();
                    } else if ($_POST['formato'] == 'xls') {
                        $this->xls_totalizado_guias();
                    }
                    break;
                case 'desglosado':
                    if ($_POST['formato'] == 'pdf') {
                        $this->pdf_desglosado_guias();
                    } else if ($_POST['formato'] == 'xls') {
                        $this->xls_desglosado_guias();
                    }
                    break;
                default:
                    break;
            }
        }
    }

    private function init_models()
    {
        $this->clientes           = new clientes();
        $this->guiascli           = new guiascli();
        $this->lineasfacturasprov = new lineasfacturasprov();
        $this->documentos         = new documentos();
        $this->establecimiento    = new establecimiento();
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

        $this->idcliente = false;
        if (isset($_POST['idcliente'])) {
            $this->idcliente = $_POST['idcliente'];
        }

        $this->tipo_guia = false;
        if (isset($_POST['tipo_guia'])) {
            $this->tipo_guia = $_POST['tipo_guia'];
        }

        $this->formato = false;
        if (isset($_POST['formato'])) {
            $this->formato = $_POST['formato'];
        }

        $this->tipodet = false;
        if (isset($_POST['tipodet'])) {
            $this->tipodet = $_POST['tipodet'];

        }
    }

    private function buscar_cliente()
    {
        $this->template = false;
        $result         = array();
        $result         = buscar_clientes($this->idempresa, $_GET['buscar_cliente']);
        echo json_encode($result);
        exit;
    }

    private function pdf_totalizado_guias()
    {
        $datos = $this->guiascli->listado_guias($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->tipo_guia);
        if ($datos) {
            $pdf = new PDF('A4', 'L');
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
            $pdf->MultiCell($ancho, $spacing, 'INFORME GUIAS DE REMISIÓN', '', 'C');
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
            $filtro2 = '';
            if ($this->tipo_guia) {
                switch ($this->tipo_guia) {
                    case 1:
                        $filtro2 = 'Tipo: Facturadas';
                        break;
                    default:
                        $filtro2 = 'Tipo: Al Cobro';
                        break;
                }
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

            $y1 += $spacing;
            $documento = '';
            $t_total   = 0;
            $total     = 0;
            foreach ($datos as $key => $d) {
                if ($documento != $d['tipo_guia']) {
                    $y1 = $pdf->GetY();
                    $y1 += ($spacing);
                    if ($documento != '') {
                        //Sumo Totales de Totales
                        $t_total += $total;
                        $pdf->SetX($x);
                        $pdf->SetFont('Times', 'B', 10);
                        $pdf->SetWidths(array(245, 30));
                        $pdf->SetAligns(array('R', 'R'));
                        $pdf->Fila(
                            array(
                                'TOTALES:',
                                show_numero(floatval($total)),
                            ),
                            $spacing);
                        $y1 += $spacing;
                    }

                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetWidths(array(275));
                    $pdf->SetAligns(array('L'));
                    $pdf->FilaSB(array(''), $spacing);
                    $pdf->FilaSB(array($d['tipo_guia']), $spacing);

                    $documento = $d['tipo_guia'];

                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetWidths(array(30, 25, 70, 30, 30, 40, 20, 30));
                    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                    $pdf->Titulos(array('N. Documento', 'F. Emisión', 'Cliente', 'Establecimiento', 'Tipo de Guía', 'Observaciones', 'Placa', 'Total'), $spacing);
                    $total = 0;
                }

                $pdf->SetX($x);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetAligns(array('R', 'R', 'L', 'L', 'L', 'L', 'L', 'R'));
                $pdf->Fila(
                    array(
                        $d['numero_documento'],
                        date('d-m-Y', strtotime($d['fec_emision'])),
                        $d['razonsocial'],
                        $d['establecimiento'],
                        $d['tipo_guia'],
                        $d['observaciones'],
                        $d['placa'],
                        show_numero(floatval($d['total'])),
                    ),
                    $spacing);
                //sumo las bases
                $total += floatval($d['total']);
            }
            $pdf->SetX($x);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(245, 30));
            $pdf->SetAligns(array('R', 'R'));
            $pdf->Fila(
                array(
                    'TOTALES:',
                    show_numero(floatval($total)),
                ),
                $spacing);
            $y1 = $pdf->GetY();
            $y1 += ($spacing);
            //Sumo Totales de Totales
            $t_total += $total;

            $pdf->SetXY($x, $y1);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(245, 30));
            $pdf->SetAligns(array('R', 'R'));
            $pdf->Fila(
                array(
                    'TOTALES FINALES:',
                    show_numero(floatval($t_total)),
                ),
                $spacing);
            $y1 += $spacing;

            $pdf->show('listado_guias_' . $this->user->nick.'.pdf');
        } else {
            $this->new_advice("Sin Datos para generar el reporte.");
        }
    }

    private function xls_totalizado_guias()
    {
        $datos = $this->guiascli->listado_guias($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->tipo_guia);
        if ($datos) {

            $this->template = false;
            $objPHPExcel    = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Konta")->setTitle("INFORME GUIAS DE REMISION");
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle("Informe Guias de Remisión");
            $titulo  = $this->empresa->razonsocial;
            $titulo1 = "INFORME GUIAS DE REMISION";
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:H2');
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
            $filtro2 = '';
            if ($this->tipo_guia) {
                switch ($this->tipo_guia) {
                    case 1:
                        $filtro2 = 'Tipo: Facturadas';
                        break;
                    default:
                        $filtro2 = 'Tipo: Al Cobro';
                        break;
                }
            }
            $i = 2;
            if ($filtro != '') {
                $i++;
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':H' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro);
            }
            if ($filtro2 != '') {
                $i++;
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':H' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro2);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A1:H' . $i)->applyFromArray(styleCabeceraReporte());

            //agrego la cabecera de la tabla
            $i++;
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, 'Documento')
                ->setCellValue('B' . $i, 'Fecha Emisión')
                ->setCellValue('C' . $i, 'Cliente')
                ->setCellValue('D' . $i, 'Establecimiento')
                ->setCellValue('E' . $i, 'Tipo de Guía')
                ->setCellValue('F' . $i, 'Observaciones')
                ->setCellValue('G' . $i, 'Placa')
                ->setCellValue('H' . $i, 'Total');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray(styleNombreColumnas());
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':H' . $i);

            //Totales de Reporte
            $t_total = 0;
            $total   = 0;

            //Recorro el detalle
            foreach ($datos as $key => $d) {
                $i++;
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $i, $d['numero_documento'])
                    ->setCellValue('B' . $i, $d['fec_emision'])
                    ->setCellValue('C' . $i, $d['razonsocial'])
                    ->setCellValue('D' . $i, $d['establecimiento'])
                    ->setCellValue('E' . $i, $d['tipo_guia'])
                    ->setCellValue('F' . $i, $d['observaciones'])
                    ->setCellValue('G' . $i, $d['placa'])
                    ->setCellValue('H' . $i, $d['total']);
                $t_total += floatval($d['total']);
            }

            $i++;
            $i++;
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':C' . $i);
            $objPHPExcel->getActiveSheet()->mergeCells('D' . $i . ':G' . $i);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, $this->user->nick . " - " . date('d-m-Y H:i:s'))
                ->setCellValue('D' . $i, 'TOTALES')
                ->setCellValue('H' . $i, $t_total);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray(styleNegrita());
            //Auto ajustar cells
            for ($j = 'A'; $j <= 'H'; $j++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($j)->setAutoSize(true);
            }

            $nombrearchivo = 'listado_guias_' . $this->user->nick . '.xlsx';
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

    private function pdf_desglosado_guias()
    {
    }

    private function xls_desglosado_guias()
    {
    }
}
