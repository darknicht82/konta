<?php

require_once 'extras/phpexcel/PHPExcel.php';
require_once 'extras/phpexcel/PHPExcel/IOFactory.php';

/**
 * Controlador de Administrador -> Cargas Masivas
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class cargas_masivas extends controller
{
    public $idempresa;
    //modelos
    public $establecimiento;
    public $articulos;
    public $marcas;
    public $grupos;
    public $impuestos;
    public $trans_inventario;
    //variables
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Cargas Masivas', 'Administrador', true, true, false, 'bi bi-cloud-arrow-down');
    }

    protected function private_core()
    {
        $this->init_filter();
        $this->init_models();

        if (isset($_GET['exportar'])) {
            $this->exportar_formato($_GET['exportar']);
        } else if (isset($_POST['importar'])) {
            $this->importar_formato($_POST['importar']);
        }
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
        $this->cantidad  = 0;
        $this->cantidad2 = 0;
    }

    private function init_models()
    {
        $this->establecimiento  = new \establecimiento();
        $this->articulos        = new \articulos();
        $this->marcas           = new \marcas();
        $this->grupos           = new \grupos();
        $this->impuestos        = new \impuestos();
        $this->trans_inventario = new \trans_inventario();
        $this->proveedores      = new \proveedores();
        $this->clientes         = new \clientes();
        $this->documentos       = new \documentos();
        if (complemento_exists('juntadeagua')) {
            $this->medidores = new medidores_cliente();
        }
        if (complemento_exists('articulos_compuestos')) {
            $this->insumos = new insumos_art();
        }
        if (complemento_exists('contabilidad')) {
            $this->ejercicios      = new ejercicios();
            $this->subcuentas      = new plancuentas();
            $this->parametrizacion = new param_contable();
        }
    }

    private function exportar_formato($tipo)
    {
        $this->template = false;
        $objPHPExcel    = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Konta")->setTitle("FORMATOS DE CARGA");
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle("FORMATO DE CARGA");
        $titulo = $this->empresa->razonsocial;
        $j      = 0;
        if ($tipo == 'art') {
            $titulo1       = "FORMATO DE CARGA DE ARTICULOS";
            $nombrearchivo = 'articulos_' . $this->user->nick . '.xlsx';
            $j             = 'I';
        } else if ($tipo == 'cxp') {
            $titulo1       = "FORMATO DE CARGA DE CUENTAS POR PAGAR";
            $nombrearchivo = 'cxp_' . $this->user->nick . '.xlsx';
            $j             = 'I';
        } else if ($tipo == 'cxc') {
            $titulo1       = "FORMATO DE CARGA DE CUENTAS POR COBRAR";
            $nombrearchivo = 'cxc_' . $this->user->nick . '.xlsx';
            $j             = 'I';
        } else if ($tipo == 'cli') {
            $titulo1       = "FORMATO DE CARGA DE CLIENTES";
            $nombrearchivo = 'cli_' . $this->user->nick . '.xlsx';
            if (complemento_exists('juntadeagua')) {
                $j = 'H';
            } else {
                $j = 'F';
            }
        } else if ($tipo == 'artcomp') {
            $titulo1       = "FORMATO DE CARGA DE ARTICULOS COMPUESTOS";
            $nombrearchivo = 'artcomp_' . $this->user->nick . '.xlsx';
            $j             = 'D';
        } else if ($tipo == 'serv') {
            $titulo1       = "FORMATO DE CARGA DE SERVICIOS";
            $nombrearchivo = 'serv_' . $this->user->nick . '.xlsx';
            if (complemento_exists('contabilidad')) {
                $j = 'G';
            } else {
                $j = 'E';
            }
        }

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $j . '1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:' . $j . '2');
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $titulo);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', $titulo1);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $j . '2')->applyFromArray(styleCabeceraReporte());

        if ($tipo == 'art') {
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A3', 'CODIGO')
                ->setCellValue('B3', 'NOMBRE')
                ->setCellValue('C3', 'GRUPO')
                ->setCellValue('D3', 'MARCA')
                ->setCellValue('E3', 'PRECIO SIN IVA')
                ->setCellValue('F3', '% IVA')
                ->setCellValue('G3', 'COSTO')
                ->setCellValue('H3', 'STOCK')
                ->setCellValue('I3', 'CODIGO DE BARRAS');
        } else if ($tipo == 'cxp') {
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A3', 'CEDULA-RUC')
                ->setCellValue('B3', 'TIPO IDENTIFICACION')
                ->setCellValue('C3', 'RAZON SOCIAL PROVEEDOR')
                ->setCellValue('D3', 'ESTABLECIMIENTO')
                ->setCellValue('E3', 'PUNTO DE EMISION')
                ->setCellValue('F3', 'SECUENCIAL')
                ->setCellValue('G3', 'FECHA FACTURA')
                ->setCellValue('H3', 'DIAS DE CREDITO')
                ->setCellValue('I3', 'TOTAL');
            $objPHPExcel->getActiveSheet()->getComment('B3')->setAuthor('Konta');
            $objCommentRichText = $objPHPExcel->getActiveSheet()->getComment('B3')->getText()->createTextRun('Konta:');
            $objCommentRichText->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getComment('B3')->getText()->createTextRun("\r\n");
            $objPHPExcel->getActiveSheet()->getComment('B3')->getText()->createTextRun("R: RUC\nC: CEDULA\nP: PASAPORTE\n");

        } else if ($tipo == 'cxc') {
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A3', 'CEDULA-RUC')
                ->setCellValue('B3', 'TIPO IDENTIFICACION')
                ->setCellValue('C3', 'RAZON SOCIAL CLIENTE')
                ->setCellValue('D3', 'ESTABLECIMIENTO')
                ->setCellValue('E3', 'PUNTO DE EMISION')
                ->setCellValue('F3', 'SECUENCIAL')
                ->setCellValue('G3', 'FECHA FACTURA')
                ->setCellValue('H3', 'DIAS DE CREDITO')
                ->setCellValue('I3', 'TOTAL');
            $objPHPExcel->getActiveSheet()->getComment('B3')->setAuthor('Konta');
            $objCommentRichText = $objPHPExcel->getActiveSheet()->getComment('B3')->getText()->createTextRun('Konta:');
            $objCommentRichText->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getComment('B3')->getText()->createTextRun("\r\n");
            $objPHPExcel->getActiveSheet()->getComment('B3')->getText()->createTextRun("R: RUC\nC: CEDULA\nP: PASAPORTE\n");
        } else if ($tipo == 'cli') {
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A3', 'CEDULA-RUC')
                ->setCellValue('B3', 'TIPO IDENTIFICACION')
                ->setCellValue('C3', 'RAZON SOCIAL CLIENTE')
                ->setCellValue('D3', 'DIRECCION')
                ->setCellValue('E3', 'TELEFONO')
                ->setCellValue('F3', 'EMAIL');
            if (complemento_exists('juntadeagua')) {
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('G3', 'NUM. MEDIDOR')
                    ->setCellValue('H3', 'CONSUMO INICIAL');
            }
            $objPHPExcel->getActiveSheet()->getComment('B3')->setAuthor('Konta');
            $objCommentRichText = $objPHPExcel->getActiveSheet()->getComment('B3')->getText()->createTextRun('Konta:');
            $objCommentRichText->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getComment('B3')->getText()->createTextRun("\r\n");
            $objPHPExcel->getActiveSheet()->getComment('B3')->getText()->createTextRun("R: RUC\nC: CEDULA\nP: PASAPORTE\n");
        } else if ($tipo == 'artcomp') {
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A3', 'CODIGO')
                ->setCellValue('B3', 'NOMBRE')
                ->setCellValue('C3', 'CANTIDAD')
                ->setCellValue('D3', 'CODIGO ART. COMPUESTO');
        } else if ($tipo == 'serv') {
            $objPHPExcel->getActiveSheet()
                ->setCellValue('A3', 'CODIGO')
                ->setCellValue('B3', 'NOMBRE')
                ->setCellValue('C3', 'GRUPO')
                ->setCellValue('D3', 'PRECIO SIN IVA')
                ->setCellValue('E3', '% IVA');
            if (complemento_exists('contabilidad')) {
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('F3', 'CTA COMPRA')
                    ->setCellValue('G3', 'CTA VENTA');
            }
        }

        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $j . '3')->applyFromArray(styleNombreColumnas());
        $objPHPExcel->getActiveSheet()->setAutoFilter('A3:' . $j . '3');

        //Auto ajustar cells
        for ($i = 'A'; $i <= $j; $i++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($i)->setAutoSize(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
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

    private function importar_formato($tipo)
    {
        if (is_uploaded_file($_FILES['fxlsx']['tmp_name'])) {
            $archivo       = $_FILES['fxlsx']['tmp_name'];
            $inputFileType = PHPExcel_IOFactory::identify($archivo);
            $objReader     = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel   = $objReader->load($archivo);
            $sheet         = $objPHPExcel->getSheet(0);
            if ($this->validar_formato($tipo, $sheet)) {
                if ($tipo == 'art') {
                    $this->importar_art($sheet);
                } else if ($tipo == 'cxp') {
                    $this->importar_cxp($sheet);
                } else if ($tipo == 'cxc') {
                    $this->importar_cxc($sheet);
                } else if ($tipo == 'cli') {
                    $this->importar_cli($sheet);
                } else if ($tipo == 'artcomp') {
                    $this->importar_art_compuestos($sheet);
                } else if ($tipo == 'serv') {
                    $this->importar_servicios($sheet);
                }
            } else {
                $this->new_error_msg('Documento No Valido. Debe descargar el formato, llenarlo y cargarlo para poder realizar la carga.');
            }
        } else {
            $this->new_advice('Error al cargar el archivo.');
        }
    }

    private function validar_formato($tipo, $excel)
    {
        if ($tipo == 'art') {
            if ($excel->getCell("A3")->getValue() != 'CODIGO') {
                return false;
            } else if ($excel->getCell("B3")->getValue() != 'NOMBRE') {
                return false;
            } else if ($excel->getCell("C3")->getValue() != 'GRUPO') {
                return false;
            } else if ($excel->getCell("D3")->getValue() != 'MARCA') {
                return false;
            } else if ($excel->getCell("E3")->getValue() != 'PRECIO SIN IVA') {
                return false;
            } else if ($excel->getCell("F3")->getValue() != '% IVA') {
                return false;
            } else if ($excel->getCell("G3")->getValue() != 'COSTO') {
                return false;
            } else if ($excel->getCell("H3")->getValue() != 'STOCK') {
                return false;
            } else if ($excel->getCell("I3")->getValue() != 'CODIGO DE BARRAS') {
                return false;
            }
        } else if ($tipo == 'cxp') {
            if ($excel->getCell("A3")->getValue() != 'CEDULA-RUC') {
                return false;
            } else if ($excel->getCell("B3")->getValue() != 'TIPO IDENTIFICACION') {
                return false;
            } else if ($excel->getCell("C3")->getValue() != 'RAZON SOCIAL PROVEEDOR') {
                return false;
            } else if ($excel->getCell("D3")->getValue() != 'ESTABLECIMIENTO') {
                return false;
            } else if ($excel->getCell("E3")->getValue() != 'PUNTO DE EMISION') {
                return false;
            } else if ($excel->getCell("F3")->getValue() != 'SECUENCIAL') {
                return false;
            } else if ($excel->getCell("G3")->getValue() != 'FECHA FACTURA') {
                return false;
            } else if ($excel->getCell("H3")->getValue() != 'DIAS DE CREDITO') {
                return false;
            } else if ($excel->getCell("I3")->getValue() != 'TOTAL') {
                return false;
            }
        } else if ($tipo == 'cxc') {
            if ($excel->getCell("A3")->getValue() != 'CEDULA-RUC') {
                return false;
            } else if ($excel->getCell("B3")->getValue() != 'TIPO IDENTIFICACION') {
                return false;
            } else if ($excel->getCell("C3")->getValue() != 'RAZON SOCIAL CLIENTE') {
                return false;
            } else if ($excel->getCell("D3")->getValue() != 'ESTABLECIMIENTO') {
                return false;
            } else if ($excel->getCell("E3")->getValue() != 'PUNTO DE EMISION') {
                return false;
            } else if ($excel->getCell("F3")->getValue() != 'SECUENCIAL') {
                return false;
            } else if ($excel->getCell("G3")->getValue() != 'FECHA FACTURA') {
                return false;
            } else if ($excel->getCell("H3")->getValue() != 'DIAS DE CREDITO') {
                return false;
            } else if ($excel->getCell("I3")->getValue() != 'TOTAL') {
                return false;
            }
        } else if ($tipo == 'cli') {
            if ($excel->getCell("A3")->getValue() != 'CEDULA-RUC') {
                return false;
            } else if ($excel->getCell("B3")->getValue() != 'TIPO IDENTIFICACION') {
                return false;
            } else if ($excel->getCell("C3")->getValue() != 'RAZON SOCIAL CLIENTE') {
                return false;
            } else if ($excel->getCell("D3")->getValue() != 'DIRECCION') {
                return false;
            } else if ($excel->getCell("E3")->getValue() != 'TELEFONO') {
                return false;
            } else if ($excel->getCell("F3")->getValue() != 'EMAIL') {
                return false;
            } else {
                if (complemento_exists('juntadeagua')) {
                    if ($excel->getCell("G3")->getValue() != 'NUM. MEDIDOR') {
                        return false;
                    } else if ($excel->getCell("H3")->getValue() != 'CONSUMO INICIAL') {
                        return false;
                    }
                }
            }
        } else if ($tipo == 'artcomp') {
            if ($excel->getCell("A3")->getValue() != 'CODIGO') {
                return false;
            } else if ($excel->getCell("B3")->getValue() != 'NOMBRE') {
                return false;
            } else if ($excel->getCell("C3")->getValue() != 'CANTIDAD') {
                return false;
            } else if ($excel->getCell("D3")->getValue() != 'CODIGO ART. COMPUESTO') {
                return false;
            }
        } else if ($tipo == 'serv') {
            if ($excel->getCell("A3")->getValue() != 'CODIGO') {
                return false;
            } else if ($excel->getCell("B3")->getValue() != 'NOMBRE') {
                return false;
            } else if ($excel->getCell("C3")->getValue() != 'GRUPO') {
                return false;
            } else if ($excel->getCell("D3")->getValue() != 'PRECIO SIN IVA') {
                return false;
            } else if ($excel->getCell("E3")->getValue() != '% IVA') {
                return false;
            } else if (complemento_exists('contabilidad')) {
                if ($excel->getCell("F3")->getValue() != 'CTA COMPRA') {
                    return false;
                } else if ($excel->getCell("G3")->getValue() != 'CTA VENTA') {
                    return false;
                }
            }
        }

        return true;
    }

    private function importar_art($sheet)
    {
        $regularizacion = array();
        //Importacion de articulos
        $highestRow = $sheet->getHighestRow();
        //Verifico si el archivo tiene datos ingresados
        if ($highestRow > 3) {
            for ($i = 4; $i <= $highestRow; $i++) {
                $codigo = trim($sheet->getCell("A" . $i)->getValue());
                if ($codigo != '') {
                    $art = $this->articulos->get_by_codprincipal($this->idempresa, $codigo);
                    if (!$art) {
                        $art                = new articulos();
                        $art->idempresa     = $this->idempresa;
                        $art->codprincipal  = $codigo;
                        $art->fec_creacion  = date('Y-m-d');
                        $art->nick_creacion = $this->user->nick;
                    } else {
                        $art->fec_modificacion  = date('Y-m-d');
                        $art->nick_modificacion = $this->user->nick;
                    }
                    $art->nombre = trim($sheet->getCell("B" . $i)->getValue());
                    $art->tipo   = 1;
                    //Busco el grupo
                    $grupo = strtoupper(trim($sheet->getCell("C" . $i)->getValue()));
                    if ($grupo != '') {
                        $idgrupo = null;
                        $idpadre = null;
                        $grupos  = explode("-", $grupo);
                        foreach ($grupos as $key => $gr) {
                            $grup = $this->grupos->get_by_nombre($this->idempresa, $gr, $idpadre);
                            if (!$grup) {
                                $grup                = new grupos();
                                $grup->idempresa     = $this->user->idempresa;
                                $grup->idpadre       = $idpadre;
                                $grup->fec_creacion  = date('Y-m-d');
                                $grup->nick_creacion = $this->user->nick;
                                $grup->nombre        = $gr;
                                if ($grup->save()) {
                                    $idgrupo = $grup->idgrupo;
                                    $idpadre = $grup->idgrupo;
                                } else {
                                    $this->new_advice("Error al crear el grupo: " . $gr);
                                    break;
                                }
                            } else {
                                $idgrupo = $grup->idgrupo;
                                $idpadre = $grup->idgrupo;
                            }
                        }

                        $art->idgrupo = $idgrupo;
                    }
                    //Busco la marca
                    $marca = strtoupper(trim($sheet->getCell("D" . $i)->getValue()));
                    if ($marca != '') {
                        $idmarca = null;
                        $idpadre = null;
                        $marcas  = explode("-", $marca);
                        foreach ($marcas as $key => $mr) {
                            $marc = $this->marcas->get_by_nombre($this->idempresa, $mr, $idpadre);
                            if (!$marc) {
                                $marc                = new marcas();
                                $marc->idempresa     = $this->user->idempresa;
                                $marc->idpadre       = $idpadre;
                                $marc->fec_creacion  = date('Y-m-d');
                                $marc->nick_creacion = $this->user->nick;
                                $marc->nombre        = $mr;
                                if ($marc->save()) {
                                    $idmarca = $marc->idmarca;
                                    $idpadre = $marc->idmarca;
                                } else {
                                    $this->new_advice("Error al crear la marca: " . $mr);
                                    break;
                                }
                            } else {
                                $idmarca = $marc->idmarca;
                                $idpadre = $marc->idmarca;
                            }
                        }

                        $art->idmarca = $idmarca;
                    }

                    //ALMACENO EL PRECIO
                    if (trim($sheet->getCell("E" . $i)->getValue()) == '') {
                        $art->sevende = false;
                        $art->precio  = floatval(0);
                    } else {
                        $art->precio = floatval(str_replace(",", ".", trim($sheet->getCell("E" . $i)->getValue())));
                    }
                    //almaceno el impuesto
                    $impuesto = floatval(str_replace(",", ".", trim($sheet->getCell("F" . $i)->getValue())));
                    if ($impuesto == '') {
                        $impuesto = 12;
                    }

                    $imp = $this->impuestos->get_by_porcentaje($impuesto);
                    if ($imp) {
                        $art->idimpuesto = $imp->idimpuesto;
                    } else {
                        $this->new_error_msg('Porcentaje de Impuesto no encontrado (' . $impuesto . '). Articulo: ' . $art->nombre . ' (' . $codigo . ')');
                        break;
                    }

                    $codbarras = strtoupper(trim($sheet->getCell("I" . $i)->getValue()));
                    if ($codbarras != '') {
                        $art->codbarras = $codbarras;
                    }

                    if ($art->save()) {
                        $this->cantidad++;
                        if (isset($_POST['stock'])) {
                            $cost = floatval(str_replace(",", ".", trim($sheet->getCell("G" . $i)->getValue())));
                            $cant = floatval(str_replace(",", ".", trim($sheet->getCell("H" . $i)->getValue())));

                            if ($cost != '' && $cant != '') {
                                $regularizacion[] = array('idarticulo' => $art->idarticulo, 'codigo' => $art->codprincipal, 'nombre' => $art->nombre, 'cant' => $cant, 'costo' => $cost);
                            }
                        }
                    } else {
                        $this->new_advice('Error al guardar los datos del Articulo: ' . $art->nombre . ' (' . $codigo . ')');
                    }
                } else {
                    break;
                }
            }
            $this->new_message($this->cantidad . ' Registro(s) Procesado(s)');

            if ($regularizacion) {
                $this->generar_regularizacion_stock($regularizacion, $_POST['idestablecimiento'], $_POST['fecha'], $_POST['hora']);
            }
        } else {
            $this->new_advice('El archivo se encuentra sin Datos para importar.');
        }
    }

    private function importar_cxp($sheet)
    {
        ///creo el servicio para poder utilizarlo
        $art = $this->articulos->get_by_codprincipal($this->idempresa, 'SALDOS');
        if (!$art) {
            $art               = new articulos();
            $art->idempresa    = $this->user->idempresa;
            $art->codprincipal = 'SALDOS';
            $art->nombre       = 'SALDOS INICIALES';

            $imp = $this->impuestos->get_by_codigo('IVA0');
            if ($imp) {
                $art->idimpuesto = $imp->idimpuesto;
            } else {
                $this->new_error_msg('Porcentaje de Impuesto no encontrado ( 0 ).');
                return;
            }
            $art->tipo   = 2;
            $art->precio = 0;

            $art->fec_creacion  = date('Y-m-d');
            $art->nick_creacion = $this->user->nick;

            if (!$art->save()) {
                $this->new_error_msg("No se pudo crear el servicio de SALDOS INICIALES, verifique los datos y vuelva a intentarlo.");
                return;
            }
        }
        //Importacion de cuentas por pagar
        $highestRow = $sheet->getHighestRow();
        //Verifico si el archivo tiene datos ingresados
        if ($highestRow > 3) {
            for ($i = 4; $i <= $highestRow; $i++) {
                $cedruc = $sheet->getCell("A" . $i)->getValue();
                if ($cedruc != '') {
                    //Busco si existe el proveedor
                    $prov = $this->proveedores->get_by_identificacion($this->idempresa, $cedruc);
                    if (!$prov) {
                        $prov                  = new proveedores();
                        $prov->idempresa       = $this->idempresa;
                        $prov->identificacion  = $cedruc;
                        $prov->telefono        = '222222222';
                        $prov->tipoid          = strtoupper(trim($sheet->getCell("B" . $i)->getValue()));
                        $prov->razonsocial     = strtoupper(trim($sheet->getCell("C" . $i)->getValue()));
                        $prov->nombrecomercial = strtoupper(trim($sheet->getCell("C" . $i)->getValue()));
                        $prov->fec_creacion    = date('Y-m-d');
                        $prov->nick_creacion   = $this->user->nick;

                        if (!$prov->save()) {
                            $this->new_advice("No se pudo crear el proveedor (" . $cedruc . "), verifique los datos y vuelva a intentarlo.");
                            continue;
                        }
                    }
                    //Si existe el proveedor creo el saldo inicial
                    if ($prov) {
                        $factura                    = new facturasprov();
                        $factura->idempresa         = $this->idempresa;
                        $factura->idestablecimiento = $_POST['idestablecimiento'];
                        $factura->idproveedor       = $prov->idproveedor;

                        //Busco el documento
                        $documento = $this->documentos->get_by_codigo('01');
                        if ($documento) {
                            $factura->iddocumento  = $documento->iddocumento;
                            $factura->coddocumento = $documento->codigo;
                        } else {
                            $this->new_advice("Documento Factura no encontrado.");
                            continue;
                        }

                        $factura->idsustento          = 1;
                        $factura->tipoemision         = 'F';
                        $factura->tipoid              = $prov->tipoid;
                        $factura->identificacion      = $prov->identificacion;
                        $factura->razonsocial         = $prov->razonsocial;
                        $factura->email               = $prov->email;
                        $factura->direccion           = $prov->direccion;
                        $factura->regimen_empresa     = $this->empresa->regimen;
                        $factura->obligado_empresa    = $this->empresa->obligado;
                        $factura->agretencion_empresa = false;
                        $estab                        = str_pad(trim($sheet->getCell("D" . $i)->getValue()), 3, "0", STR_PAD_LEFT);
                        $ptoem                        = str_pad(trim($sheet->getCell("E" . $i)->getValue()), 3, "0", STR_PAD_LEFT);
                        $secue                        = str_pad(trim($sheet->getCell("F" . $i)->getValue()), 9, "0", STR_PAD_LEFT);
                        $numero_documento             = $estab . '-' . $ptoem . '-' . $secue;
                        $factura->numero_documento    = $numero_documento;
                        $factura->nro_autorizacion    = '9999999999';
                        $factura->diascredito         = floatval(trim($sheet->getCell("H" . $i)->getValue()));
                        $factura->fec_emision         = trim($sheet->getCell("G" . $i)->getValue());
                        $factura->hora_emision        = '00:00:01';
                        $factura->fec_caducidad       = trim($sheet->getCell("G" . $i)->getValue());
                        $factura->fec_registro        = trim($sheet->getCell("G" . $i)->getValue());
                        $factura->base_0              = floatval(trim($sheet->getCell("I" . $i)->getValue()));
                        $factura->total               = floatval(trim($sheet->getCell("I" . $i)->getValue()));
                        $factura->observaciones       = 'SALDOS INICIALES CARGADOS MASIVAMENTE';
                        $factura->saldoinicial        = true;
                        $factura->fec_creacion        = date('Y-m-d');
                        $factura->nick_creacion       = $this->user->nick;

                        if ($factura->save()) {
                            $this->cantidad++;
                            //Almaceno la linea
                            $linea                = new lineasfacturasprov();
                            $linea->idfacturaprov = $factura->idfacturaprov;
                            $linea->idarticulo    = $art->idarticulo;
                            $linea->idimpuesto    = $art->idimpuesto;
                            $linea->codprincipal  = $art->codprincipal;
                            $linea->descripcion   = $art->nombre;
                            $linea->cantidad      = 1;
                            $linea->pvpunitario   = $factura->total;
                            $linea->pvptotal      = $factura->total;
                            $linea->pvpsindto     = $factura->total;
                            $linea->fec_creacion  = date('Y-m-d');
                            $linea->nick_creacion = $this->user->nick;
                            if (!$linea->save()) {
                                $this->new_error_msg("Error al guardar el detalle del la factura (" . $numero_documento . "). Item: " . $linea->descripcion);
                                $paso = false;
                                continue;
                            }
                        } else {
                            $this->new_error_msg("No se pudo crear el saldo inicial del proveedor (" . $cedruc . "), numero de docuemnto (" . $numero_documento . ").");
                            continue;
                        }
                    }
                } else {
                    break;
                }
            }
            $this->new_message($this->cantidad . ' Registro(s) Procesado(s)');
        } else {
            $this->new_advice('El archivo se encuentra sin Datos para importar.');
        }
    }

    private function importar_cxc($sheet)
    {
        ///creo el servicio para poder utilizarlo
        $art = $this->articulos->get_by_codprincipal($this->idempresa, 'SALDOS');
        if (!$art) {
            $art               = new articulos();
            $art->idempresa    = $this->user->idempresa;
            $art->codprincipal = 'SALDOS';
            $art->nombre       = 'SALDOS INICIALES';

            $imp = $this->impuestos->get_by_codigo('IVA0');
            if ($imp) {
                $art->idimpuesto = $imp->idimpuesto;
            } else {
                $this->new_error_msg('Porcentaje de Impuesto no encontrado ( 0 ).');
                return;
            }
            $art->tipo   = 2;
            $art->precio = 0;

            $art->fec_creacion  = date('Y-m-d');
            $art->nick_creacion = $this->user->nick;

            if (!$art->save()) {
                $this->new_error_msg("No se pudo crear el servicio de SALDOS INICIALES, verifique los datos y vuelva a intentarlo.");
                return;
            }
        }
        //Importacion de cuentas por cobrar
        $highestRow = $sheet->getHighestRow();
        //Verifico si el archivo tiene datos ingresados
        if ($highestRow > 3) {
            for ($i = 4; $i <= $highestRow; $i++) {
                $cedruc = $sheet->getCell("A" . $i)->getValue();
                if ($cedruc != '') {
                    //Busco si existe el cliente
                    $cli = $this->clientes->get_by_identificacion($this->idempresa, $cedruc);
                    if (!$cli) {
                        $cli                  = new clientes();
                        $cli->idempresa       = $this->idempresa;
                        $cli->identificacion  = $cedruc;
                        $cli->telefono        = '222222222';
                        $cli->tipoid          = strtoupper(trim($sheet->getCell("B" . $i)->getValue()));
                        $cli->razonsocial     = strtoupper(trim($sheet->getCell("C" . $i)->getValue()));
                        $cli->nombrecomercial = strtoupper(trim($sheet->getCell("C" . $i)->getValue()));
                        $cli->fec_creacion    = date('Y-m-d');
                        $cli->nick_creacion   = $this->user->nick;

                        if (!$cli->save()) {
                            $this->new_advice("No se pudo crear el cliente (" . $cedruc . "), verifique los datos y vuelva a intentarlo.");
                            continue;
                        }
                    }
                    //Si existe el cliente creo el saldo inicial
                    if ($cli) {
                        $factura                    = new facturascli();
                        $factura->idempresa         = $this->idempresa;
                        $factura->idestablecimiento = $_POST['idestablecimiento'];
                        $factura->idcliente         = $cli->idcliente;

                        //Busco el documento
                        $documento = $this->documentos->get_by_codigo('01');
                        if ($documento) {
                            $factura->iddocumento  = $documento->iddocumento;
                            $factura->coddocumento = $documento->codigo;
                        } else {
                            $this->new_advice("Documento Factura no encontrado.");
                            continue;
                        }

                        $factura->idsustento          = 1;
                        $factura->tipoemision         = 'F';
                        $factura->tipoid              = $cli->tipoid;
                        $factura->identificacion      = $cli->identificacion;
                        $factura->razonsocial         = $cli->razonsocial;
                        $factura->email               = $cli->email;
                        $factura->direccion           = $cli->direccion;
                        $factura->regimen_empresa     = $this->empresa->regimen;
                        $factura->obligado_empresa    = $this->empresa->obligado;
                        $factura->agretencion_empresa = false;
                        $estab                        = str_pad(trim($sheet->getCell("D" . $i)->getValue()), 3, "0", STR_PAD_LEFT);
                        $ptoem                        = str_pad(trim($sheet->getCell("E" . $i)->getValue()), 3, "0", STR_PAD_LEFT);
                        $secue                        = str_pad(trim($sheet->getCell("F" . $i)->getValue()), 9, "0", STR_PAD_LEFT);
                        $numero_documento             = $estab . '-' . $ptoem . '-' . $secue;
                        $factura->numero_documento    = $numero_documento;
                        $factura->nro_autorizacion    = '9999999999';
                        $factura->diascredito         = floatval(str_replace(",", ".", trim($sheet->getCell("H" . $i)->getValue())));
                        $factura->fec_emision         = trim($sheet->getCell("G" . $i)->getValue());
                        $factura->hora_emision        = '00:00:01';
                        $factura->fec_caducidad       = trim($sheet->getCell("G" . $i)->getValue());
                        $factura->fec_registro        = trim($sheet->getCell("G" . $i)->getValue());
                        $factura->base_0              = floatval(str_replace(",", ".", trim($sheet->getCell("I" . $i)->getValue())));
                        $factura->total               = floatval(str_replace(",", ".", trim($sheet->getCell("I" . $i)->getValue())));
                        $factura->observaciones       = 'SALDOS INICIALES CARGADOS MASIVAMENTE';
                        $factura->saldoinicial        = true;
                        $factura->fec_creacion        = date('Y-m-d');
                        $factura->nick_creacion       = $this->user->nick;

                        if ($factura->save()) {
                            $this->cantidad++;
                            //Almaceno la linea
                            $linea                = new lineasfacturascli();
                            $linea->idfacturacli  = $factura->idfacturacli;
                            $linea->idarticulo    = $art->idarticulo;
                            $linea->idimpuesto    = $art->idimpuesto;
                            $linea->codprincipal  = $art->codprincipal;
                            $linea->descripcion   = $art->nombre;
                            $linea->cantidad      = 1;
                            $linea->pvpunitario   = $factura->total;
                            $linea->pvptotal      = $factura->total;
                            $linea->pvpsindto     = $factura->total;
                            $linea->fec_creacion  = date('Y-m-d');
                            $linea->nick_creacion = $this->user->nick;
                            if (!$linea->save()) {
                                $this->new_error_msg("Error al guardar el detalle del la factura (" . $numero_documento . "). Item: " . $linea->descripcion);
                                $paso = false;
                                continue;
                            }
                        } else {
                            $this->new_error_msg("No se pudo crear el saldo inicial del cliente (" . $cedruc . "), numero de docuemnto (" . $numero_documento . ").");
                            continue;
                        }
                    }
                } else {
                    break;
                }
            }
            $this->new_message($this->cantidad . ' Registro(s) Procesado(s)');
        } else {
            $this->new_advice('El archivo se encuentra sin Datos para importar.');
        }
    }

    private function generar_regularizacion_stock($lineas, $establecimiento, $fecha, $hora)
    {
        $hora = date('H:i:59', strtotime($hora));
        //Genero cabecera de la regularizacion
        $regularizacion                    = new regularizaciones();
        $regularizacion->idempresa         = $this->idempresa;
        $regularizacion->idestablecimiento = $establecimiento;
        $regularizacion->fec_emision       = $fecha;
        $regularizacion->hora_emision      = $hora;
        $regularizacion->observaciones     = 'Carga Masiva de Inventario.';
        $regularizacion->fec_creacion      = date('Y-m-d');
        $regularizacion->nick_creacion     = $this->user->nick;
        $fectrans                          = date('Y-m-d H:i:s', strtotime($fecha . " " . $hora));
        if ($regularizacion->save()) {
            foreach ($lineas as $key => $lin) {
                $linea                   = new lineasregularizaciones();
                $linea->idregularizacion = $regularizacion->idregularizacion;
                $linea->idarticulo       = $lin['idarticulo'];
                $linea->codprincipal     = $lin['codigo'];
                $linea->descripcion      = $lin['nombre'];
                $start                   = $this->trans_inventario->get_recalculo_stock($this->idempresa, $lin['idarticulo'], $establecimiento, $fectrans);
                $cant_ant                = 0;
                if ($start) {
                    $cant_ant = $start[0]['stock'];
                }
                $linea->cantidad       = $cant_ant;
                $linea->costo          = $lin['costo'];
                $linea->nueva_cantidad = $lin['cant'];
                $linea->fec_creacion   = date('Y-m-d');
                $linea->nick_creacion  = $this->user->nick;

                if ($linea->save()) {
                    $regularizacion->total += $linea->costototal;
                } else {
                    $this->new_advice("Error al generar la linea de la regularización. Articulo: " . $linea->descripcion . " (" . $linea->nueva_cantidad . ")");
                }
            }

            if ($regularizacion->save()) {
                $this->new_message("<a href='" . $regularizacion->url() . "' target='_blank'>Regularización</a> generada correctamente.");
            } else {
                $this->new_advice("Error al actualizar los totales de la regularización de stock.");
                $regularizacion->delete();
                return;
            }
        } else {
            $this->new_error_msg("Error al generar la regularización de stock, verifique los datos y vuelva a intentarlo.");
        }
    }

    private function importar_cli($sheet)
    {
        //Importacion de clientes
        $highestRow = $sheet->getHighestRow();
        //Verifico si el archivo tiene datos ingresados
        if ($highestRow > 3) {
            for ($i = 4; $i <= $highestRow; $i++) {
                $cedruc = $sheet->getCell("A" . $i)->getValue();
                if ($cedruc != '') {
                    //Busco si existe el cliente
                    $cli = $this->clientes->get_by_identificacion($this->idempresa, $cedruc);
                    if (!$cli) {
                        $cli                  = new clientes();
                        $cli->idempresa       = $this->idempresa;
                        $cli->identificacion  = $cedruc;
                        $cli->telefono        = trim($sheet->getCell("E" . $i)->getValue());
                        $cli->direccion       = strtoupper(trim($sheet->getCell("D" . $i)->getValue()));
                        $cli->email           = trim($sheet->getCell("F" . $i)->getValue());
                        $cli->tipoid          = strtoupper(trim($sheet->getCell("B" . $i)->getValue()));
                        $cli->razonsocial     = strtoupper(trim($sheet->getCell("C" . $i)->getValue()));
                        $cli->nombrecomercial = strtoupper(trim($sheet->getCell("C" . $i)->getValue()));
                        $cli->fec_creacion    = date('Y-m-d');
                        $cli->nick_creacion   = $this->user->nick;

                        if (!$cli->save()) {
                            $this->new_advice("No se pudo crear el cliente (" . $cedruc . "), verifique los datos y vuelva a intentarlo.");
                            continue;
                        } else {
                            $this->cantidad++;
                        }
                    }
                    //Si existe el cliente creo el medidor si esta activo el plugin de junta de agua
                    if (complemento_exists('juntadeagua')) {
                        if ($cli) {
                            $numero = trim($sheet->getCell("G" . $i)->getValue());
                            if ($numero != '') {
                                $medidor = $this->medidores->get_by_cliente_numero($cli->idcliente, $numero);
                                if (!$medidor) {
                                    $medidor             = new medidores_cliente();
                                    $medidor->idempresa  = $this->idempresa;
                                    $medidor->idcliente  = $cli->idcliente;
                                    $medidor->numero     = $numero;
                                    $medidor->fec_inicio = date('Y-m-d');
                                    $consumo             = trim($sheet->getCell("H" . $i)->getValue());
                                    if ($consumo != '') {
                                        $medidor->consumo_ini = $_consumo;
                                    } else {
                                        $medidor->consumo_ini = 0;
                                    }
                                    $medidor->activo        = true;
                                    $medidor->fec_creacion  = date('Y-m-d');
                                    $medidor->nick_creacion = $this->user->nick;

                                    if (!$medidor->save()) {
                                    } else {
                                        $this->cantidad2++;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    break;
                }
            }
            $medi = '';
            if (complemento_exists('juntadeagua')) {
                $medi = ', ' . $this->cantidad2 . ' Medidor(es) Procesado(s)';
            }
            $this->new_message($this->cantidad . ' Cliente(s) Procesado(s)' . $medi);
        } else {
            $this->new_advice('El archivo se encuentra sin Datos para importar.');
        }
    }

    private function importar_art_compuestos($sheet)
    {
        //Importacion de clientes
        $highestRow = $sheet->getHighestRow();
        //Verifico si el archivo tiene datos ingresados
        if ($highestRow > 3) {
            for ($i = 4; $i <= $highestRow; $i++) {
                $codigo = trim($sheet->getCell("A" . $i)->getValue());
                if ($codigo != '') {
                    $art = $this->articulos->get_by_codprincipal($this->idempresa, $codigo);
                    if ($art) {
                        if ($art->sevende) {
                            $art->secompra = true;
                            $art->sevende  = false;
                            $art->save();
                        }
                        $codigocomp = trim($sheet->getCell("D" . $i)->getValue());
                        if ($codigocomp != '') {
                            $artc = $this->articulos->get_by_codprincipal($this->idempresa, $codigocomp);
                            if ($artc) {
                                if ($artc->secompra || !$artc->compuesto) {
                                    $artc->sevende   = true;
                                    $artc->secompra  = false;
                                    $artc->compuesto = true;
                                    $artc->save();
                                }
                                $insumo = $this->insumos->getInsumo($art->idarticulo, $artc->idarticulo);
                                if (!$insumo) {
                                    $insumo                 = new insumos_art();
                                    $insumo->idempresa      = $this->idempresa;
                                    $insumo->idarticulocomp = $artc->idarticulo;
                                    $insumo->idarticulo     = $art->idarticulo;
                                    $insumo->fec_creacion   = date('Y-m-d');
                                    $insumo->nick_creacion  = $this->user->nick;
                                } else {
                                    $insumo->fec_modificacion  = date('Y-m-d');
                                    $insumo->nick_modificacion = $this->user->nick;
                                }
                                $cant             = floatval(str_replace(",", ".", trim($sheet->getCell("C" . $i)->getValue())));
                                $insumo->cantidad = $cant;
                                if ($insumo->save()) {
                                    $this->cantidad++;
                                } else {
                                    $nombre = trim($sheet->getCell("B" . $i)->getValue());
                                    $this->new_advice('Error al guardar el insumo: ' . $nombre . ' (' . $codigo . ')');
                                }
                            } else {
                                $this->new_advice('Articulo no encontrado. (' . $codigocomp . ')');
                                continue;
                            }
                        }
                    } else {
                        $this->new_advice('Articulo no encontrado. (' . $codigo . ')');
                        continue;
                    }
                } else {
                    break;
                }
            }

            $this->new_message($this->cantidad . ' Registro(s) Procesado(s)');
        } else {
            $this->new_advice('El archivo se encuentra sin Datos para importar.');
        }
    }

    private function importar_servicios($sheet)
    {
        //Importacion de articulos
        $highestRow = $sheet->getHighestRow();
        //Verifico si el archivo tiene datos ingresados
        if ($highestRow > 3) {
            for ($i = 4; $i <= $highestRow; $i++) {
                $codigo = trim($sheet->getCell("A" . $i)->getValue());
                if ($codigo != '') {
                    $serv = $this->articulos->get_by_codprincipal($this->idempresa, $codigo);
                    if (!$serv) {
                        $serv                = new articulos();
                        $serv->idempresa     = $this->idempresa;
                        $serv->codprincipal  = $codigo;
                        $serv->fec_creacion  = date('Y-m-d');
                        $serv->nick_creacion = $this->user->nick;
                    } else {
                        $serv->fec_modificacion  = date('Y-m-d');
                        $serv->nick_modificacion = $this->user->nick;
                    }
                    $serv->nombre = trim($sheet->getCell("B" . $i)->getValue());
                    $serv->tipo   = 2;
                    //Busco el grupo
                    $grupo = strtoupper(trim($sheet->getCell("C" . $i)->getValue()));
                    if ($grupo != '') {
                        $idgrupo = null;
                        $idpadre = null;
                        $grupos  = explode("-", $grupo);
                        foreach ($grupos as $key => $gr) {
                            $grup = $this->grupos->get_by_nombre($this->idempresa, $gr, $idpadre);
                            if (!$grup) {
                                $grup                = new grupos();
                                $grup->idempresa     = $this->user->idempresa;
                                $grup->idpadre       = $idpadre;
                                $grup->fec_creacion  = date('Y-m-d');
                                $grup->nick_creacion = $this->user->nick;
                                $grup->nombre        = $gr;
                                if ($grup->save()) {
                                    $idgrupo = $grup->idgrupo;
                                    $idpadre = $grup->idgrupo;
                                } else {
                                    $this->new_advice("Error al crear el grupo: " . $gr);
                                    break;
                                }
                            } else {
                                $idgrupo = $grup->idgrupo;
                                $idpadre = $grup->idgrupo;
                            }
                        }

                        $serv->idgrupo = $idgrupo;
                    }

                    //ALMACENO EL PRECIO
                    if (trim($sheet->getCell("D" . $i)->getValue()) == '') {
                        $serv->sevende = false;
                        $serv->precio  = floatval(0);
                    } else {
                        $serv->precio = floatval(str_replace(",", ".", trim($sheet->getCell("D" . $i)->getValue())));
                    }
                    //almaceno el impuesto
                    $impuesto = floatval(str_replace(",", ".", trim($sheet->getCell("E" . $i)->getValue())));
                    if ($impuesto == '') {
                        $impuesto = 12;
                    }

                    $imp = $this->impuestos->get_by_porcentaje($impuesto);
                    if ($imp) {
                        $serv->idimpuesto = $imp->idimpuesto;
                    } else {
                        $this->new_error_msg('Porcentaje de Impuesto no encontrado (' . $impuesto . '). Articulo: ' . $serv->nombre . ' (' . $codigo . ')');
                        break;
                    }

                    if ($serv->save()) {
                        $this->cantidad++;
                        if (complemento_exists('contabilidad')) {
                            $ctacompra = str_replace('.', '', trim($sheet->getCell("F" . $i)->getValue()));
                            $ctaventa  = str_replace('.', '', trim($sheet->getCell("G" . $i)->getValue()));
                            if ($ctacompra != '' || $ctaventa != '') {
                                $ejercicios = $this->ejercicios->all_by_idempresa($this->idempresa);
                                if ($ejercicios) {
                                    foreach ($ejercicios as $key => $ejer) {
                                        if ($ejer->existsPlanCuentas()) {
                                            $idsubccompras = null;
                                            $idsubcventas  = null;
                                            //Busco la cuenta de Compras
                                            if ($ctacompra != '') {
                                                $subctac = $this->subcuentas->get_by_codigo($this->idempresa, $ejer->idejercicio, $ctacompra);
                                                if ($subctac) {
                                                    //Si existe la cuenta de compra guardamos la parametrizacion
                                                    $idsubccompras = $subctac->id;
                                                }
                                            }
                                            //Busco la cuenta de Ventas
                                            if ($ctaventa != '') {
                                                $subctav = $this->subcuentas->get_by_codigo($this->idempresa, $ejer->idejercicio, $ctaventa);
                                                if ($subctav) {
                                                    //Si existe la cuenta de compra guardamos la parametrizacion
                                                    $idsubcventas = $subctav->id;
                                                }
                                            }

                                            if ($idsubccompras || $idsubcventas) {
                                                $param = $this->parametrizacion->getByArticulo($serv->idarticulo, $ejer->idejercicio);
                                                if (!$param) {
                                                    $param                = new param_contable();
                                                    $param->idempresa     = $this->idempresa;
                                                    $param->idejercicio   = $ejer->idejercicio;
                                                    $param->idarticulo    = $serv->idarticulo;
                                                    $param->fec_creacion  = date('Y-m-d');
                                                    $param->nick_creacion = $this->user->nick;
                                                } else {
                                                    $param->fec_modificacion  = date('Y-m-d');
                                                    $param->nick_modificacion = $this->user->nick;
                                                }

                                                $param->idsubccompras = $idsubccompras;
                                                $param->idsubcventas  = $idsubcventas;

                                                if (!$param->save()) {
                                                    $this->new_error_msg('Error al guardar la configuracion contable.');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $this->new_advice('Error al guardar los datos del Servicio: ' . $serv->nombre . ' (' . $codigo . ')');
                    }
                } else {
                    break;
                }
            }
            $this->new_message($this->cantidad . ' Registro(s) Procesado(s)');
        } else {
            $this->new_advice('El archivo se encuentra sin Datos para importar.');
        }
    }
}
