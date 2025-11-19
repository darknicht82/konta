<?php
require_once 'extras/fpdf/PDF.php';
/**
 * Controlador para impresiones del modulo de Punto de Venta
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class impresion_puntoventa extends controller
{
    public function __construct()
    {
        parent::__construct(__CLASS__, 'Impresiones Punto de Venta', 'Punto de Venta', false, false);
    }

    protected function private_core()
    {
        $documento     = false;
        $this->cierres = new cierres();
        if (complemento_exists('cuadre_producto')) {
            $this->articulos_cuadre = new articulos_cuadre();
        }

        if (isset($_GET['cuadreprod'])) {
            $documento = $this->cierres->get($_GET['cuadreprod']);
            if ($documento) {
                if ($documento->idempresa == $this->empresa->idempresa) {
                    if ($documento->tieneCuadreProducto()) {
                        $this->generar_pdf_cuadre_producto($documento, $this->empresa);
                    } else {
                        $this->new_advice('El cierre no dispone de un cuadre de Producto');
                        $documento = false;
                        return;
                    }
                } else {
                    $this->new_advice("El Cierre no esta disponible para su empresa.");
                    $documento = false;
                    return;
                }
            } else {
                $this->new_advice('Cierre de Caja No Encontrado.');
            }
        }
    }

    private function generar_pdf_cuadre_producto($documento, $empresa)
    {
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->Header($this->user->nick);
        $pdf->cabecera_empresa($this->empresa);
        $ancho   = $pdf->GetPageWidth() - 20;
        $spacing = 5;
        $x       = 10;
        $y1      = $pdf->GetY() + ($spacing * 2);
        $pdf->SetXY($x, $y1);
        $pdf->SetFont('Times', 'B', 12);
        $pdf->MultiCell($ancho, $spacing, 'CIERRE DE CAJA N. ' . $documento->idcierre, '', 'C');
        $y1 = $pdf->GetY() + ($spacing);
        $pdf->SetXY($x, $y1);
        $pdf->SetFont('Times', '', 11);
        $pdf->SetWidths(array(40, 55, 40, 55));
        $pdf->SetAligns(array('R', 'L', 'R', 'L'));
        $pdf->FilaSB(array('CAJA: ', $documento->get_caja(), 'USUARIO: ', $documento->nick), $spacing);
        $pdf->FilaSB(array('APERTURA: ', $documento->apertura, 'CIERRE: ', $documento->cierre), $spacing);

        $artcuadre = $this->articulos_cuadre->all_by_idcierre($documento->idcierre);
        $y1 = $pdf->GetY() + ($spacing);
        $pdf->SetXY($x, $y1);
        $pdf->SetFont('Times', 'B', 10);
        $pdf->SetWidths(array(30, 80, 25, 25, 30));
        $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C'));
        $pdf->Titulos(array('Codigo', 'Nombre', 'Stock Empleado', 'Stock Sistema', 'Diferencia'), $spacing);
        $pdf->SetFont('Times', '', 10);
        $pdf->SetAligns(array('L', 'L', 'R', 'R', 'R'));
        foreach ($artcuadre as $key => $ac) {
            $art = $ac->get_articulo();
            $pdf->Fila(
                    array(
                        $art->codprincipal,
                        $art->nombre,
                        show_numero(floatval($ac->stockfinal), JG_NF0_ART),
                        show_numero(floatval($ac->getStockFecha()), JG_NF0_ART),
                        show_numero(floatval($ac->stockfinal - $ac->getStockFecha()), JG_NF0_ART),
                    ),
                    $spacing);
        }

        $pdf->show("cuadreproducto-" . $documento->idcierre . '.pdf');
    }
}
