<?php
require_once 'extras/fpdf/PDF.php';

require_once 'extras/phpexcel/PHPExcel.php';
require_once 'extras/phpexcel/PHPExcel/IOFactory.php';

/**
 * Controlador de Informes -> Notas de Venta.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class informe_notasventa extends controller
{
    public $idempresa;
    public $fec_desde;
    public $fec_hasta;
    public $idestablecimiento;
    public $idcliente;
    public $iddocumento;
    public $formato;
    public $tipodet;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Notas de Venta', 'Informes', true, true, false, 'bi bi-cart4');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        if (isset($_GET['buscar_cliente'])) {
            $this->buscar_cliente();
        } else if (isset($_POST['inf_ventas'])) {
            switch ($_POST['inf_ventas']) {
                case 'totalizado':
                    if ($_POST['formato'] == 'pdf') {
                        $this->pdf_totalizado_ventas();
                    } else if ($_POST['formato'] == 'xls') {
                        $this->xls_totalizado_ventas();
                    }
                    break;
                case 'desglosado':
                    if ($_POST['formato'] == 'pdf') {
                        $this->pdf_desglosado_ventas();
                    } else if ($_POST['formato'] == 'xls') {
                        $this->xls_desglosado_ventas();
                    }
                    break;
                default:
                    break;
            }
        }
    }

    private function init_modelos()
    {
        $this->clientes          = new clientes();
        $this->facturas_cliente  = new facturascli();
        $this->lineasfacturascli = new lineasfacturascli();
        $this->documentos        = new documentos();
        $this->establecimiento   = new establecimiento();
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

        $this->iddocumento = $this->documentos->get_by_codigo('02')->iddocumento;

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

    private function pdf_totalizado_ventas()
    {
        $datos = $this->facturas_cliente->listado_facturas($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->iddocumento, true);
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
            $pdf->MultiCell($ancho, $spacing, 'INFORME TOTALIZADO DE NOTAS DE VENTA', '', 'C');
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

            $y1 += $spacing;
            $documento = '';
            $t_basegr  = 0;
            $t_base0   = 0;
            $t_otbase  = 0;
            $t_dto     = 0;
            $t_otimp   = 0;
            $t_iva     = 0;
            $t_total   = 0;
            foreach ($datos as $key => $d) {
                if ($documento != $d['iddocumento']) {
                    $y1 = $pdf->GetY();
                    $y1 += ($spacing);
                    if ($documento != '') {
                        //Sumo Totales de Totales
                        $t_basegr += $basegr;
                        $t_base0 += $base0;
                        $t_otbase += $otbase;
                        $t_dto += $dto;
                        $t_otimp += $otimp;
                        $t_iva += $iva;
                        $t_total += $total;
                        $pdf->SetX($x);
                        $pdf->SetFont('Times', 'B', 10);
                        $pdf->SetWidths(array(140, 20, 20, 20, 20, 18, 20, 20));
                        $pdf->SetAligns(array('R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
                        $pdf->Fila(
                            array(
                                'TOTALES:',
                                show_numero(floatval($basegr)),
                                show_numero(floatval($base0)),
                                show_numero($otbase),
                                show_numero(floatval($dto)),
                                show_numero($otimp),
                                show_numero(floatval($iva)),
                                show_numero(floatval($total)),
                            ),
                            $spacing);
                        $y1 += $spacing;
                    }

                    //$pdf->SetXY($x, $y1);
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetWidths(array(278));
                    $pdf->SetAligns(array('L'));
                    $pdf->FilaSB(array(''), $spacing);
                    $pdf->FilaSB(array($d['nombre']), $spacing);

                    if ($d['codigo'] == '04') {
                        $multiplicador = -1;
                    } else {
                        $multiplicador = 1;
                    }
                    //$pdf->MultiCell($ancho, $spacing, strtoupper($d['nombre']), '', 'L');
                    $documento = $d['iddocumento'];
                    //$y1 += $spacing;
                    //$pdf->SetXY($x, $y1);
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetWidths(array(30, 21, 69, 20, 20, 20, 20, 20, 18, 20, 20));
                    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                    $pdf->Titulos(array('N. Documento', 'F. Emisión', 'Cliente', 'Estab.', 'Bas. Grav.', 'Base 0%', 'Ot. Bases', 'Descuento', 'ICE', 'IVA', 'Total'), $spacing);
                    $basegr = 0;
                    $base0  = 0;
                    $otbase = 0;
                    $dto    = 0;
                    $otimp  = 0;
                    $iva    = 0;
                    $total  = 0;
                }
                $otb = floatval($d['base_noi'] + $d['base_exc']);
                $oti = floatval($d['totalice']);

                $pdf->SetX($x);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetAligns(array('R', 'R', 'L', 'L', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
                $pdf->Fila(
                    array(
                        $d['numero_documento'],
                        date('d-m-Y', strtotime($d['fec_emision'])),
                        $d['razonsocial'],
                        $d['establecimiento'],
                        show_numero(floatval($d['base_gra'] * $multiplicador)),
                        show_numero(floatval($d['base_0'] * $multiplicador)),
                        show_numero($otb * $multiplicador),
                        show_numero(floatval($d['totaldescuento'] * $multiplicador)),
                        show_numero($oti * $multiplicador),
                        show_numero(floatval($d['totaliva'] * $multiplicador)),
                        show_numero(floatval($d['total'] * $multiplicador)),
                    ),
                    $spacing);
                //sumo las bases
                $basegr += floatval($d['base_gra'] * $multiplicador);
                $base0 += floatval($d['base_0'] * $multiplicador);
                $otbase += ($otb * $multiplicador);
                $dto += floatval($d['totaldescuento'] * $multiplicador);
                $otimp += ($oti * $multiplicador);
                $iva += floatval($d['totaliva'] * $multiplicador);
                $total += floatval($d['total'] * $multiplicador);
            }
            $pdf->SetX($x);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(140, 20, 20, 20, 20, 18, 20, 20));
            $pdf->SetAligns(array('R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTALES:',
                    show_numero(floatval($basegr)),
                    show_numero(floatval($base0)),
                    show_numero($otbase),
                    show_numero(floatval($dto)),
                    show_numero($otimp),
                    show_numero(floatval($iva)),
                    show_numero(floatval($total)),
                ),
                $spacing);
            $y1 = $pdf->GetY();
            $y1 += ($spacing);
            //Sumo Totales de Totales
            $t_basegr += $basegr;
            $t_base0 += $base0;
            $t_otbase += $otbase;
            $t_dto += $dto;
            $t_otimp += $otimp;
            $t_iva += $iva;
            $t_total += $total;

            $pdf->SetXY($x, $y1);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(140, 20, 20, 20, 20, 18, 20, 20));
            $pdf->SetAligns(array('R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTALES FINALES:',
                    show_numero(floatval($t_basegr)),
                    show_numero(floatval($t_base0)),
                    show_numero($t_otbase),
                    show_numero(floatval($t_dto)),
                    show_numero($t_otimp),
                    show_numero(floatval($t_iva)),
                    show_numero(floatval($t_total)),
                ),
                $spacing);
            $y1 += $spacing;

            $pdf->show('listado_ventas_' . $this->user->nick.'.pdf');
        } else {
            $this->new_advice("Sin Datos para generar el reporte.");
        }
    }

    private function xls_totalizado_ventas()
    {
        $datos = $this->facturas_cliente->listado_facturas($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->iddocumento, true);
        if ($datos) {

            $this->template = false;
            $objPHPExcel    = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("GSC Systems")->setTitle("INFORME TOTALIZADO DE VENTAS");
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle("Informe Ventas");
            $titulo  = $this->empresa->razonsocial;
            $titulo1 = "INFORME TOTALIZADO DE VENTAS";
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:N1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:N2');
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
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':N' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A1:N' . $i)->applyFromArray(styleCabeceraReporte());

            //agrego la cabecera de la tabla
            $i++;
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, 'Documento')
                ->setCellValue('B' . $i, 'Fecha Emisión')
                ->setCellValue('C' . $i, 'Cliente')
                ->setCellValue('D' . $i, 'Observaciones')
                ->setCellValue('E' . $i, 'Establecimiento')
                ->setCellValue('F' . $i, 'Base Gravada')
                ->setCellValue('G' . $i, 'Base 0%')
                ->setCellValue('H' . $i, 'Base No Objeto de IVA')
                ->setCellValue('I' . $i, 'Base Excento de IVA')
                ->setCellValue('J' . $i, 'Subtotal Sin Impuestos')
                ->setCellValue('K' . $i, 'Descuento')
                ->setCellValue('L' . $i, 'ICE')
                ->setCellValue('M' . $i, 'IVA')
                ->setCellValue('N' . $i, 'TOTAL');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':N' . $i)->applyFromArray(styleNombreColumnas());
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':N' . $i);

            //Totales de Reporte
            $t_basegr   = 0;
            $t_base0    = 0;
            $t_bnoi     = 0;
            $t_bexc     = 0;
            $t_subtotal = 0;
            $t_desc     = 0;
            $t_ice      = 0;
            $t_iva      = 0;
            $t_total    = 0;

            //Recorro el detalle
            foreach ($datos as $key => $d) {
                if ($d['nombre'] == '04') {
                    $multiplicador = -1;
                } else {
                    $multiplicador = 1;
                }
                $i++;
                $subtotal = $d['base_gra'] + $d['base_0'] + $d['base_noi'] + $d['base_exc'];
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $i, $d['nombre'])
                    ->setCellValue('B' . $i, $d['fec_emision'])
                    ->setCellValue('C' . $i, $d['razonsocial'])
                    ->setCellValue('D' . $i, $d['observaciones'])
                    ->setCellValue('E' . $i, $d['establecimiento'])
                    ->setCellValue('F' . $i, ($d['base_gra'] * $multiplicador))
                    ->setCellValue('G' . $i, ($d['base_0'] * $multiplicador))
                    ->setCellValue('H' . $i, ($d['base_noi'] * $multiplicador))
                    ->setCellValue('I' . $i, ($d['base_exc'] * $multiplicador))
                    ->setCellValue('J' . $i, ($subtotal * $multiplicador))
                    ->setCellValue('K' . $i, ($d['totaldescuento'] * $multiplicador))
                    ->setCellValue('L' . $i, ($d['totalice'] * $multiplicador))
                    ->setCellValue('M' . $i, ($d['totaliva'] * $multiplicador))
                    ->setCellValue('N' . $i, ($d['total'] * $multiplicador));
                $t_basegr   += floatval($d['base_gra'] * $multiplicador);
                $t_base0    += floatval($d['base_0'] * $multiplicador);
                $t_bnoi     += floatval($d['base_noi'] * $multiplicador);
                $t_bexc     += floatval($d['base_exc'] * $multiplicador);
                $t_subtotal += floatval($subtotal * $multiplicador);
                $t_desc     += floatval($d['totaldescuento'] * $multiplicador);
                $t_ice      += floatval($d['totalice'] * $multiplicador);
                $t_iva      += floatval($d['totaliva'] * $multiplicador);
                $t_total    += floatval($d['total'] * $multiplicador);
            }

            //Auto ajustar cells
            for ($j = 'A'; $j <= 'N'; $j++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($j)->setAutoSize(true);
            }

            $i++;
            $i++;
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':C' . $i);
            $objPHPExcel->getActiveSheet()->mergeCells('D' . $i . ':E' . $i);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, $this->user->nick ." - ". date('d-m-Y H:i:s'))
                ->setCellValue('D' . $i, 'TOTALES')
                ->setCellValue('F' . $i, $t_basegr)
                ->setCellValue('G' . $i, $t_base0)
                ->setCellValue('H' . $i, $t_bnoi)
                ->setCellValue('I' . $i, $t_bexc)
                ->setCellValue('J' . $i, $t_subtotal)
                ->setCellValue('K' . $i, $t_desc)
                ->setCellValue('L' . $i, $t_ice)
                ->setCellValue('M' . $i, $t_iva)
                ->setCellValue('N' . $i, $t_total);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':N' . $i)->applyFromArray(styleNegrita());

            $nombrearchivo = 'listado_ventas_' . $this->user->nick . '.xlsx';
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save($nombrearchivo);
            $objPHPExcel->disconnectWorksheets();
            unset($objPHPExcel);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($nombrearchivo));
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

    private function pdf_desglosado_ventas()
    {
        $articulos = false;
        $servicios = false;

        if ($this->tipodet) {
            if ($this->tipodet == 1) {
                $articulos = $this->facturas_cliente->detalle_facturas($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->iddocumento, $this->tipodet, true);
            } else if ($this->tipodet == 2) {
                $servicios = $this->facturas_cliente->detalle_facturas($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->iddocumento, $this->tipodet, true);
            }
        } else {
            $articulos = $this->facturas_cliente->detalle_facturas($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->iddocumento, 1, true);
            $servicios = $this->facturas_cliente->detalle_facturas($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->iddocumento, 2, true);
        }

        if ($articulos || $servicios) {
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
            $pdf->MultiCell($ancho, $spacing, 'INFORME DETALLADO DE NOTAS DE VENTA', '', 'C');
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

            if ($filtro != '') {
                $y1 += $spacing;
                $pdf->SetXY($x, $y1);
                $pdf->SetFont('Times', '', 11);
                $pdf->MultiCell($ancho, $spacing, $filtro, '', 'C');
            }

            $t_cant  = 0;
            $t_desc  = 0;
            $t_neto  = 0;
            $t_otimp = 0;
            $t_iva   = 0;
            $t_total = 0;

            if ($articulos) {
                $cant  = 0;
                $desc  = 0;
                $neto  = 0;
                $otimp = 0;
                $iva   = 0;
                $total = 0;

                $y1 += $spacing;
                $y1 += $spacing;
                $pdf->SetFont('Times', 'B', 11);
                $pdf->SetWidths(array(275));
                $pdf->SetAligns(array('L'));
                $pdf->FilaSB(array(''), $spacing);
                $pdf->FilaSB(array('ARTICULOS'), $spacing);

                $pdf->SetFont('Times', 'B', 9);
                $pdf->SetWidths(array(30, 50, 20, 35, 20, 20, 20, 20, 20, 20, 20));
                $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                $pdf->Titulos(array('N. Documento', 'Cliente', 'F. Emisión', 'Descripción', 'Cantidad', 'Precio Unitario', 'Descuento', 'Subtotal', 'Otros. Imp', 'IVA', 'Total'), $spacing);

                foreach ($articulos as $key => $art) {
                    $multiplicador = 1;
                    if ($art['codigo'] == '04') {
                        $multiplicador = -1;
                    }
                    $pdf->SetFont('Times', '', 8);
                    $pdf->SetWidths(array(30, 50, 20, 35, 20, 20, 20, 20, 20, 20, 20));
                    $pdf->SetAligns(array('R', 'L', 'R', 'L', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
                    $tot = floatval($art['pvptotal'] * $multiplicador) + floatval($art['otrosimp'] * $multiplicador) + floatval($art['valoriva'] * $multiplicador);
                    $pdf->Fila(
                        array(
                            $art['numero_documento'],
                            $art['razonsocial'],
                            date('d-m-Y', strtotime($art['fec_emision'])),
                            $art['nombre'],
                            show_numero(floatval($art['cantidad'] * $multiplicador)),
                            show_numero(floatval($art['pvpunitario'] * $multiplicador)),
                            show_numero(floatval($art['dto'] * $multiplicador)),
                            show_numero(floatval($art['pvptotal'] * $multiplicador)),
                            show_numero(floatval($art['otrosimp'] * $multiplicador)),
                            show_numero(floatval($art['valoriva'] * $multiplicador)),
                            show_numero(floatval($tot)),
                        ), $spacing
                    );

                    $cant += floatval($art['cantidad'] * $multiplicador);
                    $desc += floatval($art['dto'] * $multiplicador);
                    $neto += floatval($art['pvptotal'] * $multiplicador);
                    $otimp += floatval($art['otrosimp'] * $multiplicador);
                    $iva += floatval($art['valoriva'] * $multiplicador);
                    $total += floatval($tot);
                }

                $pdf->SetFont('Times', 'B', 10);
                $pdf->SetWidths(array(135, 20, 20, 20, 20, 20, 20, 20));
                $pdf->SetAligns(array('R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
                $pdf->Fila(
                    array(
                        'TOTALES ARTICULOS:',
                        show_numero(floatval($cant)),
                        '',
                        show_numero(floatval($desc)),
                        show_numero(floatval($neto)),
                        show_numero(floatval($otimp)),
                        show_numero(floatval($iva)),
                        show_numero(floatval($total)),
                    ),
                    $spacing
                );

                $t_cant += $cant;
                $t_desc += $desc;
                $t_neto += $neto;
                $t_otimp += $otimp;
                $t_iva += $iva;
                $t_total += $total;
            }

            if ($servicios) {
                $cant  = 0;
                $desc  = 0;
                $neto  = 0;
                $otimp = 0;
                $iva   = 0;
                $total = 0;

                $y1 += $spacing;
                $y1 += $spacing;
                $pdf->SetFont('Times', 'B', 11);
                $pdf->SetWidths(array(275));
                $pdf->SetAligns(array('L'));
                $pdf->FilaSB(array(''), $spacing);
                $pdf->FilaSB(array('SERVICIOS'), $spacing);

                $pdf->SetFont('Times', 'B', 9);
                $pdf->SetWidths(array(30, 50, 20, 35, 20, 20, 20, 20, 20, 20, 20));
                $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                $pdf->Titulos(array('N. Documento', 'Cliente', 'F. Emisión', 'Descripción', 'Cantidad', 'Precio Unitario', 'Descuento', 'Subtotal', 'Otros. Imp', 'IVA', 'Total'), $spacing);

                foreach ($servicios as $key => $ser) {
                    $multiplicador = 1;
                    if ($ser['codigo'] == '04') {
                        $multiplicador = -1;
                    }
                    $pdf->SetFont('Times', '', 8);
                    $pdf->SetWidths(array(30, 50, 20, 35, 20, 20, 20, 20, 20, 20, 20));
                    $pdf->SetAligns(array('R', 'L', 'R', 'L', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
                    $tot = floatval($ser['pvptotal'] * $multiplicador) + floatval($ser['otrosimp'] * $multiplicador) + floatval($ser['valoriva'] * $multiplicador);
                    $pdf->Fila(
                        array(
                            $ser['numero_documento'],
                            $ser['razonsocial'],
                            date('d-m-Y', strtotime($ser['fec_emision'])),
                            $ser['nombre'],
                            show_numero(floatval($ser['cantidad'] * $multiplicador)),
                            show_numero(floatval($ser['pvpunitario'] * $multiplicador)),
                            show_numero(floatval($ser['dto'] * $multiplicador)),
                            show_numero(floatval($ser['pvptotal'] * $multiplicador)),
                            show_numero(floatval($ser['otrosimp'] * $multiplicador)),
                            show_numero(floatval($ser['valoriva'] * $multiplicador)),
                            show_numero(floatval($tot)),
                        ), $spacing
                    );

                    $cant += floatval($ser['cantidad'] * $multiplicador);
                    $desc += floatval($ser['dto'] * $multiplicador);
                    $neto += floatval($ser['pvptotal'] * $multiplicador);
                    $otimp += floatval($ser['otrosimp'] * $multiplicador);
                    $iva += floatval($ser['valoriva'] * $multiplicador);
                    $total += floatval($tot);
                }

                $pdf->SetFont('Times', 'B', 10);
                $pdf->SetWidths(array(135, 20, 20, 20, 20, 20, 20, 20));
                $pdf->SetAligns(array('R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
                $pdf->Fila(
                    array(
                        'TOTALES SERVICIOS:',
                        show_numero(floatval($cant)),
                        '',
                        show_numero(floatval($desc)),
                        show_numero(floatval($neto)),
                        show_numero(floatval($otimp)),
                        show_numero(floatval($iva)),
                        show_numero(floatval($total)),
                    ),
                    $spacing
                );

                $t_cant += $cant;
                $t_desc += $desc;
                $t_neto += $neto;
                $t_otimp += $otimp;
                $t_iva += $iva;
                $t_total += $total;
            }

            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetWidths(array(275));
            $pdf->SetAligns(array('R'));
            $pdf->FilaSB(array(''), $spacing);
            $pdf->SetWidths(array(135, 20, 20, 20, 20, 20, 20, 20));
            $pdf->SetAligns(array('R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
            $pdf->Fila(
                array(
                    'TOTALES FINALES:',
                    show_numero(floatval($t_cant)),
                    '',
                    show_numero(floatval($t_desc)),
                    show_numero(floatval($t_neto)),
                    show_numero(floatval($t_otimp)),
                    show_numero(floatval($t_iva)),
                    show_numero(floatval($t_total)),
                ),
                $spacing
            );

            $pdf->show('listado_detnotasdeventa_' . $this->user->nick.'.pdf');
        } else {
            $this->new_advice("Sin Datos para generar el reporte.");
        }
    }

    private function xls_desglosado_ventas()
    {
        $articulos = false;
        $servicios = false;

        if ($this->tipodet) {
            if ($this->tipodet == 1) {
                $articulos = $this->facturas_cliente->detalle_facturas($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->iddocumento, $this->tipodet, true);
            } else if ($this->tipodet == 2) {
                $servicios = $this->facturas_cliente->detalle_facturas($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->iddocumento, $this->tipodet, true);
            }
        } else {
            $articulos = $this->facturas_cliente->detalle_facturas($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->iddocumento, 1, true);
            $servicios = $this->facturas_cliente->detalle_facturas($this->idempresa, $this->fec_desde, $this->fec_hasta, $this->idestablecimiento, $this->idcliente, $this->iddocumento, 2, true);
        }

        if ($articulos || $servicios) {
            $this->template = false;
            $objPHPExcel    = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("GSC Systems")->setTitle("INFORME DETALLADO DE NOTAS DE VENTA");
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle("Informe Detallado NDV");
            $titulo  = $this->empresa->razonsocial;
            $titulo1 = "INFORME DETALLADO DE NOTAS DE VENTA";
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:N1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:N2');
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
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':O' . $i);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $filtro);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A1:O' . $i)->applyFromArray(styleCabeceraReporte());

            //agrego la cabecera de la tabla
            $i++;
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, 'Tipo')
                ->setCellValue('B' . $i, 'N. Documento')
                ->setCellValue('C' . $i, 'Tipo Documento')
                ->setCellValue('D' . $i, 'Fecha Emisión')
                ->setCellValue('E' . $i, 'identificacion')
                ->setCellValue('F' . $i, 'Cliente')
                ->setCellValue('G' . $i, 'Código')
                ->setCellValue('H' . $i, 'Descripción')
                ->setCellValue('I' . $i, 'Cantidad')
                ->setCellValue('J' . $i, 'Precio Unitario')
                ->setCellValue('K' . $i, 'Descuento')
                ->setCellValue('L' . $i, 'Subtotal')
                ->setCellValue('M' . $i, 'Otros Impuestos')
                ->setCellValue('N' . $i, 'IVA')
                ->setCellValue('O' . $i, 'TOTAL');

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':O' . $i)->applyFromArray(styleNombreColumnas());
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':O' . $i);

            $t_cant  = 0;
            $t_desc  = 0;
            $t_neto  = 0;
            $t_otimp = 0;
            $t_iva   = 0;
            $t_total = 0;

            if ($articulos) {
                foreach ($articulos as $key => $art) {
                    $i++;
                    $multiplicador = 1;
                    if ($art['codigo'] == '04') {
                        $multiplicador = -1;
                    }
                    $tot = floatval($art['pvptotal'] * $multiplicador) + floatval($art['otrosimp'] * $multiplicador) + floatval($art['valoriva'] * $multiplicador);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A' . $i, 'Artículo')
                        ->setCellValue('B' . $i, $art['numero_documento'])
                        ->setCellValue('C' . $i, $art['nombredoc'])
                        ->setCellValue('D' . $i, $art['fec_emision'])
                        ->setCellValueExplicit('E' . $i, $art['identificacion'], PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValue('F' . $i, $art['razonsocial'])
                        ->setCellValue('G' . $i, $art['codprincipal'])
                        ->setCellValue('H' . $i, $art['nombre'])
                        ->setCellValue('I' . $i, floatval($art['cantidad'] * $multiplicador))
                        ->setCellValue('J' . $i, floatval($art['pvpunitario'] * $multiplicador))
                        ->setCellValue('K' . $i, floatval($art['dto'] * $multiplicador))
                        ->setCellValue('L' . $i, floatval($art['pvptotal'] * $multiplicador))
                        ->setCellValue('M' . $i, floatval($art['otrosimp'] * $multiplicador))
                        ->setCellValue('N' . $i, floatval($art['valoriva'] * $multiplicador))
                        ->setCellValue('O' . $i, floatval($tot));
                    $t_cant += floatval($art['cantidad'] * $multiplicador);
                    $t_desc += floatval($art['dto'] * $multiplicador);
                    $t_neto += floatval($art['pvptotal'] * $multiplicador);
                    $t_otimp += floatval($art['otrosimp'] * $multiplicador);
                    $t_iva += floatval($art['valoriva'] * $multiplicador);
                    $t_total += floatval($tot);
                }
            }

            if ($servicios) {
                foreach ($servicios as $key => $ser) {
                    $i++;
                    $multiplicador = 1;
                    if ($ser['codigo'] == '04') {
                        $multiplicador = -1;
                    }
                    $tot = floatval($ser['pvptotal'] * $multiplicador) + floatval($ser['otrosimp'] * $multiplicador) + floatval($ser['valoriva'] * $multiplicador);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A' . $i, 'Servicio')
                        ->setCellValue('B' . $i, $ser['numero_documento'])
                        ->setCellValue('C' . $i, $ser['nombredoc'])
                        ->setCellValue('D' . $i, $ser['fec_emision'])
                        ->setCellValueExplicit('E' . $i, $ser['identificacion'], PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValue('F' . $i, $ser['razonsocial'])
                        ->setCellValue('G' . $i, $ser['codprincipal'])
                        ->setCellValue('H' . $i, $ser['nombre'])
                        ->setCellValue('I' . $i, floatval($ser['cantidad'] * $multiplicador))
                        ->setCellValue('J' . $i, floatval($ser['pvpunitario'] * $multiplicador))
                        ->setCellValue('K' . $i, floatval($ser['dto'] * $multiplicador))
                        ->setCellValue('L' . $i, floatval($ser['pvptotal'] * $multiplicador))
                        ->setCellValue('M' . $i, floatval($ser['otrosimp'] * $multiplicador))
                        ->setCellValue('N' . $i, floatval($ser['valoriva'] * $multiplicador))
                        ->setCellValue('O' . $i, floatval($tot));
                    $t_cant += floatval($ser['cantidad'] * $multiplicador);
                    $t_desc += floatval($ser['dto'] * $multiplicador);
                    $t_neto += floatval($ser['pvptotal'] * $multiplicador);
                    $t_otimp += floatval($ser['otrosimp'] * $multiplicador);
                    $t_iva += floatval($ser['valoriva'] * $multiplicador);
                    $t_total += floatval($tot);
                }
            }

            $i++;
            $i++;
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':C' . $i);
            $objPHPExcel->getActiveSheet()->mergeCells('D' . $i . ':H' . $i);
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $i, $this->user->nick . " - " . date('d-m-Y H:i:s'))
                ->setCellValue('D' . $i, 'TOTALES')
                ->setCellValue('I' . $i, $t_cant)
                ->setCellValue('J' . $i, '')
                ->setCellValue('K' . $i, $t_desc)
                ->setCellValue('L' . $i, $t_neto)
                ->setCellValue('M' . $i, $t_otimp)
                ->setCellValue('N' . $i, $t_iva)
                ->setCellValue('O' . $i, $t_total);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':O' . $i)->applyFromArray(styleNegrita());

            //Auto ajustar cells
            for ($j = 'A'; $j <= 'O'; $j++) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($j)->setAutoSize(true);
            }

            $nombrearchivo = 'listado_detnotasdeventa_' . $this->user->nick . '.xlsx';
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
