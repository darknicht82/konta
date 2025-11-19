<?php
require_once 'extras/fpdf/PDF.php';
require_once 'extras/phpexcel/PHPExcel.php';
require_once 'extras/phpexcel/PHPExcel/IOFactory.php';
/**
 * Controlador para impresiones del modulo de Contabilidad
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class impresion_contabilidad extends controller
{
    //Filtros
    public $idempresa;
    //modelos
    public $ejercicios;
    public $plancuentas;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Impresiones Contabilidad', 'Contabilidad', false, false);
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        if (isset($_GET['idejercicio'])) {
            $ejercicio = $this->ejercicios->get($_GET['idejercicio']);
            if (!$ejercicio) {
                $this->new_error_msg("Ejercicio no encontrado.");
                return;
            }
            if ($ejercicio->idempresa != $this->idempresa) {
                $this->new_advice("El Ejercicio no esta disponible para su empresa.");
                return;
            }
            //Busco el plan de Cuentas del Ejercicio
            if ($ejercicio->existsPlanCuentas()) {
                $plan = $this->plancuentas->allPlanCuentas($this->idempresa, $ejercicio->idejercicio);
                if ($_GET['tipo'] == 'pdf') {
                    $this->exportar_plancuentas_pdf($plan, $ejercicio);
                } else if ($_GET['tipo'] == 'xls') {
                    $this->exportar_plancuentas_xls($plan, $ejercicio);
                }
            } else {
                $this->new_advice("El Ejercicio no tiene cargado un Plan de Cuentas.");
                return;
            }
        }
    }

    private function init_modelos()
    {
        $this->ejercicios  = new ejercicios();
        $this->plancuentas = new plancuentas();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
    }

    private function exportar_plancuentas_pdf($plan, $ejercicio)
    {
        $pdf = new PDF();
        //$pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->Header($this->user->nick);
        $pdf->cabecera_empresa($this->empresa);
        $ancho   = $pdf->GetPageWidth() - 20;
        $spacing = 6;
        $y1      = $pdf->GetY() + $spacing;
        $x       = 10;
        $pdf->SetXY($x, $y1);
        $pdf->SetFont('Times', 'B', 12);
        $pdf->MultiCell($ancho, $spacing, 'PLAN DE CUENTAS ' . $ejercicio->nombre, '', 'C');
        $filtro = 'Desde: ' . date('d-m-Y', strtotime($ejercicio->fec_inicio)) . ' - Hasta: ' . date('d-m-Y', strtotime($ejercicio->fec_fin));
        $y1 += $spacing;

        if ($filtro != '') {
            $pdf->SetXY($x, $y1);
            $pdf->SetFont('Times', '', 12);
            $pdf->MultiCell($ancho, $spacing, $filtro, '', 'C');
        }

        $y1 += $spacing;
        $y1 += $spacing;
        $pdf->SetXY($x, $y1);
        $pdf->SetLeftMargin(25);
        $pdf->SetFont('Times', 'B', 13);
        $pdf->SetWidths(array(40, 120));
        $pdf->SetAligns(array('C', 'C'));
        $pdf->FilaSB(array('CODIGO', 'NOMBRE'), $spacing);
        $y1 += $spacing;
        $y1 += $spacing;
        $pdf->SetXY($x, $y1);
        $pdf->SetFont('Times', '', 11);
        $pdf->SetAligns(array('L', 'L'));
        foreach ($plan as $key => $d) {
            $pdf->SetLeftMargin(25);
            $pdf->FilaSB(
                array(
                    $d->codigo,
                    $d->nombre,
                ),
                $spacing);
        }

        $pdf->show('planCuentas_' . $ejercicio->nombre . '_' . $this->user->nick . '.pdf');
    }

    private function exportar_plancuentas_xls($plan, $ejercicio)
    {
        $this->template = false;
        $objPHPExcel    = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("GSC Systems")->setTitle("PLAN DE CUENTAS");
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle("Plan de Cuentas");
        $titulo  = $this->empresa->razonsocial;
        $titulo1 = 'PLAN DE CUENTAS ' . $ejercicio->nombre;
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:B1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $titulo);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', $titulo1);
        $i      = 2;
        $objPHPExcel->getActiveSheet()->getStyle('A1:B' . $i)->applyFromArray(styleCabeceraReporte());

        //agrego la cabecera de la tabla
        $i++;
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A' . $i, 'CODIGO')
            ->setCellValue('B' . $i, 'NOMBRE');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':B' . $i)->applyFromArray(styleNombreColumnas());
        $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':B' . $i);

        //Recorro el detalle
        foreach ($plan as $key => $d) {
            $i++;
            $objPHPExcel->getActiveSheet()
                ->setCellValueExplicit('A' . $i, $d->codigo, PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue('B' . $i, $d->nombre);
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(100);

        $nombrearchivo = 'planCuentas_' . $ejercicio->nombre . '_' . $this->user->nick . '.xlsx';
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
    }
}
