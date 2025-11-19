<?php
require_once 'extras/fpdf/PDF.php';

require_once 'extras/phpexcel/PHPExcel.php';
require_once 'extras/phpexcel/PHPExcel/IOFactory.php';

/**
 * Controlador de Informes -> Articulos.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class informes_articulos extends controller
{
    //Variables
    public $idempresa;
    public $b_tipo;
    public $idarticulo;
    public $fec_hasta;
    public $idestablecimiento;
    public $idgrupo;
    public $idmarca;
    public $estado;
    public $formato;

    //Modelos
    public $trans_inventario;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Articulos', 'Informes', true, true, false, 'bi bi-signpost-2');
    }

    protected function private_core()
    {
        $this->init_models();
        $this->init_filter();

        if (isset($_GET['buscar_articulos'])) {
            $this->buscar_articulos();
        } else if (isset($_POST['inf_articulos'])) {
            switch ($_POST['inf_articulos']) {
                case 'existencias':
                    if ($_POST['formato'] == 'pdf') {
                        $this->pdf_existencias_articulos();
                    } else if ($_POST['formato'] == 'xls') {
                        $this->xls_existencias_articulos();
                    }
                    break;
                case 'valorizado':
                    if ($_POST['formato'] == 'pdf') {
                        $this->pdf_valorizado_articulos();
                    } else if ($_POST['formato'] == 'xls') {
                        $this->xls_valorizado_articulos();
                    }
                    break;
                case 'xarticulo':
                    if ($_POST['formato'] == 'pdf') {
                        $this->pdf_x_articulo();
                    } else if ($_POST['formato'] == 'xls') {
                        $this->xls_x_articulo();
                    }
                    break;
                default:
                    break;
            }
        }
    }

    private function init_models()
    {
        $this->trans_inventario = new trans_inventario();
        $this->establecimiento  = new establecimiento();
        $this->grupos           = new grupos();
        $this->marcas           = new marcas();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
        $this->b_tipo    = 1;

        $this->idarticulo = '';
        if (isset($_POST['idarticulo']) && $_POST['idarticulo'] != '') {
            $this->idarticulo = $_POST['idarticulo'];
        }

        $this->fec_desde = '';
        if (isset($_POST['fec_desde']) && $_POST['fec_desde'] != '') {
            $this->fec_desde = $_POST['fec_desde'];
        }

        $this->fec_hasta = '';
        if (isset($_POST['fec_hasta']) && $_POST['fec_hasta'] != '') {
            $this->fec_hasta = $_POST['fec_hasta'];
        }

        $this->idestablecimiento = '';
        if (isset($_POST['idestablecimiento']) && $_POST['idestablecimiento'] != '') {
            $this->idestablecimiento = $_POST['idestablecimiento'];
        }

        $this->idgrupo = '';
        if (isset($_POST['idgrupo']) && $_POST['idgrupo'] != '') {
            $this->idgrupo = $_POST['idgrupo'];
        }

        $this->idmarca = '';
        if (isset($_POST['idmarca']) && $_POST['idmarca'] != '') {
            $this->idmarca = $_POST['idmarca'];
        }

        $this->estado = '';
        if (isset($_POST['estado'])) {
            $this->estado = $_POST['estado'];
        }

        $this->formato = '';
        if (isset($_POST['formato'])) {
            $this->formato = $_POST['formato'];
        }
    }

    private function buscar_articulos()
    {
        $this->template = false;
        $articulos      = buscar_articulos($this->idempresa, $_GET['buscar_articulos'], '', '', $this->b_tipo);
        echo json_encode($articulos);
        exit;
    }

    private function pdf_existencias_articulos()
    {
        $datos = $this->trans_inventario->existenciasArticulos($this->idempresa, $this->idarticulo, $this->fec_hasta, $this->idestablecimiento, $this->idmarca, $this->idgrupo, $this->estado);
        if ($datos) {
            $pdf = new PDF();
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
            $pdf->MultiCell($ancho, $spacing, 'INFORME DE EXISTENCIAS DE ARTÍCULOS', '', 'C');
            $filtro = '';
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

            $y1 += $spacing;
            $t_ingre = 0;
            $t_egres = 0;
            $t_saldo = 0;

            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(30, 80, 25, 25, 30));
            $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C'));
            $pdf->Titulos(array('Codigo', 'Nombre', 'Grupo', 'Marca', 'Saldo'), $spacing);

            $pdf->SetX($x);
            $pdf->SetFont('Times', '', 9);
            $pdf->SetAligns(array('L', 'L', 'L', 'L', 'R', 'R', 'R'));
            foreach ($datos as $key => $d) {
                $pdf->Fila(
                    array(
                        $d['codprincipal'],
                        $d['nombre'],
                        $d['grupo'],
                        $d['marca'],
                        show_numero(floatval($d['stock'])),
                    ),
                    $spacing);
                //sumo las bases
                $t_ingre += floatval($d['ingresos']);
                $t_egres += floatval($d['egresos']);
                $t_saldo += floatval($d['stock']);
            }
            $pdf->SetX($x);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(160, 30));
            $pdf->SetAligns(array('R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL:',
                    show_numero(floatval($t_saldo)),
                ),
                $spacing);
            $pdf->show('reporte_existencias_' . $this->user->nick . '.pdf');
        } else {
            $this->new_advice("Sin Datos para generar el reporte.");
        }
    }

    public function xls_existencias_articulos()
    {
        $datos = $this->trans_inventario->existenciasArticulos($this->idempresa, $this->idarticulo, $this->fec_hasta, $this->idestablecimiento, $this->idmarca, $this->idgrupo, $this->estado);
        if ($datos) {
            $this->template = false;
            $objPHPExcel    = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Konta")->setTitle("INFORME DE EXISTENCIAS DE ARTÍCULOS");
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle("Existencias");
            $titulo  = $this->empresa->razonsocial;
            $titulo1 = "INFORME DE EXISTENCIAS DE ARTÍCULOS";
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:G2');
            $objPHPExcel->getActiveSheet()->setCellValue('A1', $titulo);
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $titulo1);
            $filtro = '';
            if ($this->fec_hasta) {
                if ($filtro != '') {
                    $filtro .= ' - ';
                }
                $filtro .= 'Hasta: ' . date('d-m-Y', strtotime($this->fec_hasta));
            }
            $i = 2;
            if ($filtro != '') {
                $i++;
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':G' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $i)->applyFromArray(styleCabeceraReporte());

            //agrego la cabecera de la tabla
            $i++;
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, 'Codigo')
                ->setCellValue('B' . $i, 'Nombre')
                ->setCellValue('C' . $i, 'Grupo')
                ->setCellValue('D' . $i, 'Marca')
                ->setCellValue('E' . $i, 'Ingresos')
                ->setCellValue('F' . $i, 'Egresos')
                ->setCellValue('G' . $i, 'Saldo');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':G' . $i)->applyFromArray(styleNombreColumnas());
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':G' . $i);

            //Totales de Reporte
            $t_ingre = 0;
            $t_egres = 0;
            $t_saldo = 0;

            //Recorro el detalle
            foreach ($datos as $key => $d) {
                $i++;
                $objPHPExcel->getActiveSheet()
                    ->setCellValueExplicit('A' . $i, $d['codprincipal'], PHPExcel_Cell_DataType::TYPE_STRING)
                    ->setCellValue('B' . $i, $d['nombre'])
                    ->setCellValue('C' . $i, $d['grupo'])
                    ->setCellValue('D' . $i, $d['marca'])
                    ->setCellValue('E' . $i, $d['ingresos'])
                    ->setCellValue('F' . $i, $d['egresos'])
                    ->setCellValue('G' . $i, $d['stock']);
                //sumo las bases
                $t_ingre += floatval($d['ingresos']);
                $t_egres += floatval($d['egresos']);
                $t_saldo += floatval($d['stock']);
            }

            $i++;
            $i++;
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':B' . $i);
            $objPHPExcel->getActiveSheet()->mergeCells('C' . $i . ':D' . $i);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, $this->user->nick . " - " . date('d-m-Y H:i:s'))
                ->setCellValue('C' . $i, 'TOTALES')
                ->setCellValue('E' . $i, $t_ingre)
                ->setCellValue('F' . $i, $t_egres)
                ->setCellValue('G' . $i, $t_saldo);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':G' . $i)->applyFromArray(styleNegrita());
            //Auto ajustar cells
            for ($j = 'A'; $j <= 'G'; $j++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($j)->setAutoSize(true);
            }

            $nombrearchivo = 'reporte_existencias_' . $this->user->nick . '.xlsx';
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

    private function pdf_valorizado_articulos()
    {
        $datos = $this->trans_inventario->existenciasArticulos($this->idempresa, $this->idarticulo, $this->fec_hasta, $this->idestablecimiento, $this->idmarca, $this->idgrupo, $this->estado);
        if ($datos) {
            $pdf = new PDF();
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
            $pdf->MultiCell($ancho, $spacing, 'INFORME VALORIZADO DE ARTÍCULOS', '', 'C');
            $filtro = '';
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

            $y1 += $spacing;
            $t_stock  = 0;
            $t_costot = 0;

            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(30, 80, 25, 25, 30));
            $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C'));
            $pdf->Titulos(array('Codigo', 'Nombre', 'Cantidad', 'Costo Unitario', 'Costo Total'), $spacing);

            $pdf->SetX($x);
            $pdf->SetFont('Times', '', 9);
            $pdf->SetAligns(array('L', 'L', 'R', 'R', 'R'));
            foreach ($datos as $key => $d) {
                $costo  = $this->trans_inventario->getCostoArticulo($this->idempresa, $d['idarticulo'], $this->fec_hasta);
                $costot = $costo * $d['stock'];
                $pdf->Fila(
                    array(
                        $d['codprincipal'],
                        $d['nombre'],
                        show_numero(floatval($d['stock']), JG_NF0_ART),
                        show_numero(floatval($costo), JG_NF0_ART),
                        show_numero(floatval($costot), JG_NF0_ART),
                    ),
                    $spacing);
                //sumo las bases
                $t_stock += floatval($d['stock']);
                $t_costot += floatval($costot);
            }
            $pdf->SetX($x);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(110, 25, 25, 30));
            $pdf->SetAligns(array('R', 'R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTAL:',
                    show_numero(floatval($t_stock)),
                    '',
                    show_numero(floatval($t_costot)),
                ),
                $spacing);
            $pdf->show('reporte_valorizado_' . $this->user->nick . '.pdf');
        } else {
            $this->new_advice("Sin Datos para generar el reporte.");
        }
    }

    private function xls_valorizado_articulos()
    {
        $datos = $this->trans_inventario->existenciasArticulos($this->idempresa, $this->idarticulo, $this->fec_hasta, $this->idestablecimiento, $this->idmarca, $this->idgrupo, $this->estado);
        if ($datos) {
            $this->template = false;
            $objPHPExcel    = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Konta")->setTitle("INFORME DE EXISTENCIAS DE ARTÍCULOS");
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle("Existencias");
            $titulo  = $this->empresa->razonsocial;
            $titulo1 = "INFORME DE EXISTENCIAS DE ARTÍCULOS";
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:G2');
            $objPHPExcel->getActiveSheet()->setCellValue('A1', $titulo);
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $titulo1);
            $filtro = '';
            if ($this->fec_hasta) {
                if ($filtro != '') {
                    $filtro .= ' - ';
                }
                $filtro .= 'Hasta: ' . date('d-m-Y', strtotime($this->fec_hasta));
            }
            $i = 2;
            if ($filtro != '') {
                $i++;
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':G' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $i)->applyFromArray(styleCabeceraReporte());

            //agrego la cabecera de la tabla
            $i++;
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, 'Codigo')
                ->setCellValue('B' . $i, 'Nombre')
                ->setCellValue('C' . $i, 'Grupo')
                ->setCellValue('D' . $i, 'Marca')
                ->setCellValue('E' . $i, 'Cantidad')
                ->setCellValue('F' . $i, 'Costo Unitario')
                ->setCellValue('G' . $i, 'Costo Total');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':G' . $i)->applyFromArray(styleNombreColumnas());
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':G' . $i);

            //Totales de Reporte
            $t_cant  = 0;
            $t_costt = 0;

            //Recorro el detalle
            foreach ($datos as $key => $d) {
                $i++;
                $costo  = $this->trans_inventario->getCostoArticulo($this->idempresa, $d['idarticulo'], $this->fec_hasta);
                $costot = $costo * $d['stock'];
                $objPHPExcel->getActiveSheet()
                    ->setCellValueExplicit('A' . $i, $d['codprincipal'], PHPExcel_Cell_DataType::TYPE_STRING)
                    ->setCellValue('B' . $i, $d['nombre'])
                    ->setCellValue('C' . $i, $d['grupo'])
                    ->setCellValue('D' . $i, $d['marca'])
                    ->setCellValue('E' . $i, $d['stock'])
                    ->setCellValue('F' . $i, $costo)
                    ->setCellValue('G' . $i, $costot);
                //sumo las bases
                $t_cant += floatval($d['stock']);
                $t_costt += floatval($costot);
            }

            $i++;
            $i++;
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':B' . $i);
            $objPHPExcel->getActiveSheet()->mergeCells('C' . $i . ':D' . $i);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, $this->user->nick . " - " . date('d-m-Y H:i:s'))
                ->setCellValue('C' . $i, 'TOTALES')
                ->setCellValue('E' . $i, $t_cant)
                ->setCellValue('F' . $i, '')
                ->setCellValue('G' . $i, $t_costt);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':G' . $i)->applyFromArray(styleNegrita());
            //Auto ajustar cells
            for ($j = 'A'; $j <= 'G'; $j++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($j)->setAutoSize(true);
            }

            $nombrearchivo = 'reporte_valorizado_' . $this->user->nick . '.xlsx';
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

    private function pdf_x_articulo()
    {
        $datos = $this->trans_inventario->getReportexArticulo($this->idempresa, $this->idarticulo, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idgrupo, $this->idmarca);
        if ($datos) {
            $pdf = new PDF();
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
            $pdf->MultiCell($ancho, $spacing, 'INFORME DE STOCK POR ARTICULO Y ESTABLECIMIENTO', '', 'C');
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
            $y1 += $spacing + 2;

            if ($filtro != '') {
                $pdf->SetXY($x, $y1);
                $pdf->SetFont('Times', '', 11);
                $pdf->MultiCell($ancho, $spacing, $filtro, '', 'C');
            }

            $y1 += $spacing;
            $y1 += $spacing;

            $idestablecimiento = '';

            foreach ($datos as $key => $d) {
                if ($idestablecimiento != $d['idestablecimiento']) {
                    if ($idestablecimiento != '') {
                        $pdf->AddPage();
                        $y1 = $pdf->GetY() + $spacing;
                    }
                    $pdf->SetXY($x, $y1);
                    $pdf->SetFont('Times', 'B', 12);
                    $pdf->MultiCell($ancho, $spacing, $d['nomestab'], '', 'C');
                    $y1 += $spacing + 2;
                    $pdf->SetXY($x, $y1);
                    $idestablecimiento = $d['idestablecimiento'];
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetWidths(array(25, 70, 19, 19, 19, 19, 19));
                    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C'));
                    $pdf->Titulos(array('Codigo', 'Nombre', 'Inicial', 'Ingresos', 'Venta', 'Final', 'Real'), $spacing);
                }

                $pdf->SetFont('Times', '', 10);
                $pdf->SetAligns(array('L', 'L', 'R', 'R', 'R', 'R', 'R'));
                $pdf->Fila(
                    array(
                        $d['codprincipal'],
                        $d['nomart'],
                        show_numero(floatval($d['inicial']), JG_NF0_ART),
                        show_numero(floatval($d['ingresos']), JG_NF0_ART),
                        show_numero(floatval($d['venta']), JG_NF0_ART),
                        show_numero(floatval($d['saldo']), JG_NF0_ART),
                        '',
                    ),
                    $spacing);
            }

            $pdf->show('reporte_xarticulo_' . $this->user->nick . '.pdf');
        } else {
            $this->new_advice("Sin Datos para generar el reporte.");
        }
    }

    private function xls_x_articulo()
    {
        $datos = $this->trans_inventario->getReportexArticulo($this->idempresa, $this->idarticulo, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idgrupo, $this->idmarca);
        if ($datos) {
            $this->template = false;
            $objPHPExcel    = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Konta")->setTitle("INFORME DE STOCK POR ARTICULO Y ESTABLECIMIENTO");
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle("Stock Por Articulo");
            $titulo  = $this->empresa->razonsocial;
            $titulo1 = "INFORME DE STOCK POR ARTICULO Y ESTABLECIMIENTO";
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
            $i = 2;
            if ($filtro != '') {
                $i++;
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':H' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A1:H' . $i)->applyFromArray(styleCabeceraReporte());

            //agrego la cabecera de la tabla
            $i++;
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, 'Establecimiento')
                ->setCellValue('B' . $i, 'Codigo')
                ->setCellValue('C' . $i, 'Nombre')
                ->setCellValue('D' . $i, 'Inicial')
                ->setCellValue('E' . $i, 'Ingresos')
                ->setCellValue('F' . $i, 'Venta')
                ->setCellValue('G' . $i, 'Final')
                ->setCellValue('H' . $i, 'Real');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray(styleNombreColumnas());
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':H' . $i);

            foreach ($datos as $key => $d) {
                $i++;
                $objPHPExcel->getActiveSheet()
                    ->setCellValueExplicit('A' . $i, $d['nomestab'], PHPExcel_Cell_DataType::TYPE_STRING)
                    ->setCellValueExplicit('B' . $i, $d['codprincipal'], PHPExcel_Cell_DataType::TYPE_STRING)
                    ->setCellValue('C' . $i, $d['nomart'])
                    ->setCellValue('D' . $i, $d['inicial'])
                    ->setCellValue('E' . $i, $d['ingresos'])
                    ->setCellValue('F' . $i, $d['venta'])
                    ->setCellValue('G' . $i, $d['saldo'])
                    ->setCellValue('H' . $i, '');
            }

            //Auto ajustar cells
            for ($j = 'A'; $j <= 'G'; $j++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($j)->setAutoSize(true);
            }

            $nombrearchivo = 'reporte_xarticulo_' . $this->user->nick . '.xlsx';
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