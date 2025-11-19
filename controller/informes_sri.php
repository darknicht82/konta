<?php
require_once 'extras/phpexcel/PHPExcel.php';
require_once 'extras/phpexcel/PHPExcel/IOFactory.php';

/**
 * Controlador de Informes -> SRI.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class informes_sri extends controller
{
    public function __construct()
    {
        parent::__construct(__CLASS__, 'Informes', 'SRI', true, true, false, 'bi bi-robot');
    }

    protected function private_core()
    {
        $this->init_models();
        $this->init_filter();

        if (isset($_POST['reporte'])) {
            if ($_POST['reporte'] == '103') {
                $this->generar_formulario_renta();
            } else if ($_POST['reporte'] == '104') {
                $this->generar_formulario_iva();
            } else if ($_POST['reporte'] == 'ats') {
                $this->generar_ats();
            }
        }
    }

    private function init_models()
    {
        $this->facturas_proveedor   = new facturasprov();
        $this->lineasfacturasprov   = new lineasfacturasprov();
        $this->facturas_cliente     = new facturascli();
        $this->lineasfacturascli    = new lineasfacturascli();
        $this->lineasretencionescli = new lineasretencionescli();
        $this->establecimiento      = new establecimiento();
        $this->retencionescli       = new retencionescli();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;

        $this->fec_desde = false;
        $this->fec_hasta = false;
        $this->periodo   = '';
        $this->anio      = '';
        $this->mes       = '';
        $this->semestral = false;
        if (isset($_POST['tipo'])) {
            if ($_POST['tipo'] == 'semestral') {
                $this->semestral = true;
                if ($_POST['periodo'] == 'IP') {
                    $per1            = $_POST['anio'] . "-01";
                    $per2            = $_POST['anio'] . "-06";
                    $this->fec_desde = date('Y-01-01', strtotime($per1));
                    $this->fec_hasta = date('Y-06-t', strtotime($per2));
                    $this->periodo   = $_POST['anio'] . "_Ene-Jun";
                    $this->anio      = $_POST['anio'];
                    $this->mes       = '06';
                } else if ($_POST['periodo'] == 'IIP') {
                    $per1            = $_POST['anio'] . "-07";
                    $per2            = $_POST['anio'] . "-12";
                    $this->fec_desde = date('Y-07-01', strtotime($per1));
                    $this->fec_hasta = date('Y-12-t', strtotime($per2));
                    $this->periodo   = $_POST['anio'] . "_Jul-Dic";
                    $this->anio      = $_POST['anio'];
                    $this->mes       = '12';
                }
            } else if ($_POST['tipo'] == 'mensual') {
                $this->fec_desde = date('Y-m-01', strtotime($_POST['periodo']));
                $this->fec_hasta = date('Y-m-t', strtotime($_POST['periodo']));
                $this->periodo   = $_POST['periodo'];
                $this->anio      = date('Y', strtotime($_POST['periodo']));
                $this->mes       = date('m', strtotime($_POST['periodo']));
            }
        }
    }

    private function generar_formulario_iva()
    {
        $this->template = false;

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Konta")->setTitle("FORMULARIO 104");
        $inputFileName = 'formatos/sri/FORM104.xlsx';
        $objReader     = PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel   = $objReader->load($inputFileName);
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Fecha de Impresion: ' . date('d-m-Y H:i:s'));
        $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Usuario Impresión: ' . $this->user->nick);
        $objPHPExcel->getActiveSheet()->setCellValue('L2', $this->periodo);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A3', $this->empresa->ruc, PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue('E3', $this->empresa->razonsocial);

        //busco las ventas segun el filtro
        $datos_ventas = $this->lineasfacturascli->listado_ventas_formulario($this->idempresa, $this->fec_desde, $this->fec_hasta);
        //Ventas locales (excluye activos fijos) gravadas tarifa diferente de cero
        $b401 = 0;
        $n411 = 0;
        $i421 = 0;
        //Ventas de activos fijos gravadas tarifa diferente de cero
        $b402 = 0;
        $n412 = 0;
        $i422 = 0;
        //Ventas locales (excluye activos fijos) gravadas tarifa diferente de cero (TARIFA VARIABLE)
        $b410 = 0;
        $n420 = 0;
        $i430 = 0;
        //Ventas locales (excluye activos fijos) gravadas tarifa 0% que no dan derecho a crédito tributario
        $b403 = 0;
        $n413 = 0;
        //Ventas de activos fijos gravadas tarifa 0% que no dan derecho a crédito tributario
        $b404 = 0;
        $n414 = 0;
        //Ventas locales (excluye activos fijos) gravadas tarifa 0% que dan derecho a crédito tributario
        $b405 = 0;
        $n415 = 0;
        //Ventas de activos fijos gravadas tarifa 0% que dan derecho a crédito tributario
        $b406 = 0;
        $n416 = 0;
        //Exportaciones de bienes
        $b407 = 0;
        $n417 = 0;
        //Exportaciones de servicios y/o derechos
        $b408 = 0;
        $n418 = 0;
        //Transferencias no objeto o exentas de IVA
        $b431 = 0;
        $n441 = 0;

        foreach ($datos_ventas as $key => $v) {
            if ($v['codigo'] == 'IVANO' || $v['codigo'] == 'IVAEX') {
                if ($v['coddocumento'] == '04') {
                    $n441 -= floatval($v['base']);
                } else {
                    $b431 += floatval($v['base']);
                }
            } else if ($v['tipo'] == 1 || $v['tipo'] == 2) {
                //Articulo y Servicios
                if ($v['codigo'] == 'IVA12' || $v['codigo'] == 'IVA14') {
                    if ($v['coddocumento'] == '04') {
                        $n411 -= floatval($v['base']);
                        $i421 -= (floatval($v['base']) * (floatval($v['porcentaje']) / 100));
                    } else {
                        $b401 += floatval($v['base']);
                        $i421 += (floatval($v['base']) * (floatval($v['porcentaje']) / 100));
                    }
                } else if ($v['codigo'] == 'IVA0') {
                    if ($v['coddocumento'] == '04') {
                        $n413 -= floatval($v['base']);
                    } else {
                        $b403 += floatval($v['base']);
                    }
                } else {
                    if ($v['coddocumento'] == '04') {
                        $n420 -= floatval($v['base']);
                        $i430 -= (floatval($v['base']) * (floatval($v['porcentaje']) / 100));
                    } else {
                        $b410 += floatval($v['base']);
                        $i430 += (floatval($v['base']) * (floatval($v['porcentaje']) / 100));
                    }
                }
            } else {
                //Aqui van los activos fijos
                if ($v['codigo'] == 'IVA12' || $v['codigo'] == 'IVA14') {
                    if ($v['coddocumento'] == '04') {
                        $n412 -= floatval($v['base']);
                        $i422 -= (floatval($v['base']) * (floatval($v['porcentaje']) / 100));
                    } else {
                        $b402 += floatval($v['base']);
                        $i422 += (floatval($v['base']) * (floatval($v['porcentaje']) / 100));
                    }
                } else if ($v['codigo'] == 'IVA0') {
                    if ($v['coddocumento'] == '04') {
                        $n414 -= floatval($v['base']);
                    } else {
                        $b404 += floatval($v['base']);
                    }
                }
            }
        }

        //muestro en el reporte
        $objPHPExcel->getActiveSheet()->setCellValue('J8', round($b401, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L8', round($b401 + $n411, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N8', round($i421, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J9', round($b402, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L9', round($b402 + $n412, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N9', round($i422, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J10', round($b410, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L10', round($b410 + $n420, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N10', round($i430, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J13', round($b403, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L13', round($b403 + $n413, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J14', round($b404, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L14', round($b404 + $n414, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J15', round($b405, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L15', round($b405 + $n415, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J16', round($b406, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L16', round($b406 + $n416, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J17', round($b407, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L17', round($b407 + $n417, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J18', round($b408, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L18', round($b408 + $n418, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J20', round($b431, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L20', round($b431 + $n441, 2));

        //Busco los contadores de documentos
        $numcomp = 0;
        $numanul = 0;

        $comprobantes = $this->facturas_cliente->contador_documentos_104($this->idempresa, $this->fec_desde, $this->fec_hasta);

        if ($comprobantes) {
            foreach ($comprobantes as $key => $c) {
                if ($this->facturas_cliente->str2bool($c['anulado'])) {
                    $numanul += floatval($c['contador']);
                } else {
                    $numcomp += floatval($c['contador']);
                }
            }
        }

        $objPHPExcel->getActiveSheet()->setCellValue('F34', $numcomp);
        $objPHPExcel->getActiveSheet()->setCellValue('N34', $numanul);

        //busco las compras segun el filtro
        $datos_compras = $this->lineasfacturasprov->listado_compras_formulario($this->idempresa, $this->fec_desde, $this->fec_hasta);
        //Adquisiciones y pagos (excluye activos fijos) gravados tarifa diferente de cero (con derecho a crédito tributario)
        $b500 = 0;
        $n510 = 0;
        $i520 = 0;
        //Adquisiciones locales de activos fijos gravados tarifa diferente de cero (con derecho a crédito tributario)
        $b501 = 0;
        $n511 = 0;
        $i521 = 0;
        //Adquisiciones y pagos (excluye activos fijos) gravados tarifa diferente de cero (con derecho a crédito tributario tarifa variable)
        $b530 = 0;
        $n533 = 0;
        $i534 = 0;
        //Otras adquisiciones y pagos gravados tarifa diferente de cero (sin derecho a crédito tributario)
        $b502 = 0;
        $n512 = 0;
        $i522 = 0;
        //Importaciones de servicios y/o derechos gravados tarifa diferente de cero
        $b503 = 0;
        $n513 = 0;
        $i523 = 0;
        //Importaciones de bienes (excluye activos fijos) gravados tarifa diferente de cero
        $b504 = 0;
        $n514 = 0;
        $i524 = 0;
        //Importaciones de activos fijos gravados tarifa diferente de cero
        $b505 = 0;
        $n515 = 0;
        $i525 = 0;
        //Importaciones de bienes (incluye activos fijos) gravados tarifa 0%
        $b506 = 0;
        $n516 = 0;
        //Adquisiciones y pagos (incluye activos fijos) gravados tarifa 0%
        $b507 = 0;
        $n517 = 0;
        //Adquisiciones realizadas a contribuyentes RISE (hasta diciembre 2021), NEGOCIOS POPULARES  (desde enero 2022)
        $b508 = 0;
        $n518 = 0;
        //Adquisiciones no objeto de IVA
        $b531 = 0;
        $n541 = 0;
        //Adquisiciones exentas del pago de IVA
        $b532 = 0;
        $n542 = 0;

        foreach ($datos_compras as $key => $c) {
            if ($c['coddocumento'] == '02') {
                $b508 += floatval($c['base']);
            } else if ($c['codigo'] == 'IVANO') {
                if ($c['coddocumento'] == '04') {
                    $n541 -= floatval($c['base']);
                } else {
                    $b531 += floatval($c['base']);
                }
            } else if ($c['codigo'] == 'IVAEX') {
                if ($c['coddocumento'] == '04') {
                    $n542 -= floatval($c['base']);
                } else {
                    $b532 += floatval($c['base']);
                }
            } else if ($c['tipo'] == 1 || $c['tipo'] == 2) {
                //Articulo y Servicios
                if ($c['codigo'] == 'IVA12' || $c['codigo'] == 'IVA14') {
                    if ($c['sustento'] == '01' || $c['sustento'] == '03' || $c['sustento'] == '06') {
                        //Aplican credito tributario
                        if ($c['coddocumento'] == '04') {
                            $n510 -= floatval($c['base']);
                            $i520 -= (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        } else {
                            $b500 += floatval($c['base']);
                            $i520 += (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        }
                    } else {
                        //No Aplican Credito tributario
                        if ($c['coddocumento'] == '04') {
                            $n512 -= floatval($c['base']);
                            $i522 -= (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        } else {
                            $b502 += floatval($c['base']);
                            $i522 += (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        }
                    }
                } else if ($c['codigo'] == 'IVA0') {
                    if ($c['coddocumento'] == '04') {
                        $n517 -= floatval($c['base']);
                    } else {
                        $b507 += floatval($c['base']);
                    }
                } else {
                    if ($c['sustento'] == '01' || $c['sustento'] == '03' || $c['sustento'] == '06') {
                        //Aplican credito tributario
                        if ($c['coddocumento'] == '04') {
                            $n533 -= floatval($c['base']);
                            $i534 -= (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        } else {
                            $b530 += floatval($c['base']);
                            $i534 += (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        }
                    } else {
                        //No Aplican Credito tributario
                        if ($c['coddocumento'] == '04') {
                            $n512 -= floatval($c['base']);
                            $i522 -= (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        } else {
                            $b502 += floatval($c['base']);
                            $i522 += (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        }
                    }
                }
            } else {
                //Aqui van los activos fijos
                if ($c['codigo'] == 'IVA12' || $c['codigo'] == 'IVA14') {
                    if ($c['sustento'] == '01' || $c['sustento'] == '03' || $c['sustento'] == '06') {
                        //Aplican credito tributario
                        if ($c['coddocumento'] == '04') {
                            $n511 -= floatval($c['base']);
                            $i521 -= (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        } else {
                            $b501 += floatval($c['base']);
                            $i521 += (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        }
                    } else {
                        //No Aplican Credito tributario
                        if ($c['coddocumento'] == '04') {
                            $n512 -= floatval($c['base']);
                            $i522 -= (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        } else {
                            $b502 += floatval($c['base']);
                            $i522 += (floatval($c['base']) * (floatval($c['porcentaje']) / 100));
                        }
                    }
                } else if ($c['codigo'] == 'IVA0') {
                    if ($c['coddocumento'] == '04') {
                        $n517 -= floatval($c['base']);
                    } else {
                        $b507 += floatval($c['base']);
                    }
                }
            }
        }

        //muestro en el reporte
        $objPHPExcel->getActiveSheet()->setCellValue('J38', round($b500, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L38', round($b500 + $n510, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N38', round($i520, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J39', round($b501, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L39', round($b501 + $n511, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N39', round($i521, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J40', round($b530, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L40', round($b530 + $n533, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N40', round($i534, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J41', round($b502, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L41', round($b502 + $n512, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N41', round($i522, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J42', round($b503, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L42', round($b503 + $n513, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N42', round($i523, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J43', round($b504, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L43', round($b504 + $n514, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N43', round($i524, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J44', round($b505, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L44', round($b505 + $n515, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N44', round($i525, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J47', round($b506, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L47', round($b506 + $n516, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J48', round($b507, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L48', round($b507 + $n517, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J49', round($b508, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L49', round($b508 + $n518, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J51', round($b531, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L51', round($b531 + $n541, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J52', round($b407, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L52', round($b407 + $n417, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J51', round($b408, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L51', round($b408 + $n418, 2));

        $objPHPExcel->getActiveSheet()->setCellValue('J52', round($b532, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('L52', round($b532 + $n542, 2));

        //Busco los contadores de documentos
        $numcomp = 0;
        $numnvta = 0;
        $numnliq = 0;

        $comprobantes = $this->facturas_proveedor->contador_documentos_104($this->idempresa, $this->fec_desde, $this->fec_hasta);

        if ($comprobantes) {
            foreach ($comprobantes as $key => $c) {
                if ($c['coddocumento'] == '02') {
                    $numnvta += floatval($c['contador']);
                } else if ($c['coddocumento'] == '03') {
                    $numnliq += floatval($c['contador']);
                } else {
                    $numcomp += floatval($c['contador']);
                }
            }
        }

        $objPHPExcel->getActiveSheet()->setCellValue('F60', $numcomp);
        $objPHPExcel->getActiveSheet()->setCellValue('N60', $numnvta);
        $objPHPExcel->getActiveSheet()->setCellValue('N61', $numnliq);

        $retiva    = 0;
        $retcliiva = $this->lineasretencionescli->listado_retencion($this->idempresa, $this->fec_desde, $this->fec_hasta);
        if ($retcliiva) {
            foreach ($retcliiva as $key => $rt) {
                $retiva += floatval($rt['valret']);
            }
        }
        $objPHPExcel->getActiveSheet()->setCellValue('N73', $retiva);

        //busco las retenciones
        $retencionesiva = $this->lineasfacturasprov->listado_ret_iva($this->idempresa, $this->fec_desde, $this->fec_hasta);
        $r721           = 0;
        $r723           = 0;
        $r725           = 0;
        $r727           = 0;
        $r729           = 0;
        $r731           = 0;

        if ($retencionesiva) {
            foreach ($retencionesiva as $key => $ret) {
                switch ($ret['codigobase']) {
                    case '721':
                        $r721 += floatval($ret['valret']);
                        break;
                    case '723':
                        $r723 += floatval($ret['valret']);
                        break;
                    case '725':
                        $r725 += floatval($ret['valret']);
                        break;
                    case '727':
                        $r727 += floatval($ret['valret']);
                        break;
                    case '729':
                        $r729 += floatval($ret['valret']);
                        break;
                    case '731':
                        $r731 += floatval($ret['valret']);
                        break;
                    default:
                        break;
                }
            }
        }

        $objPHPExcel->getActiveSheet()->setCellValue('N96', round($r721, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N97', round($r723, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N98', round($r725, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N99', round($r727, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N100', round($r729, 2));
        $objPHPExcel->getActiveSheet()->setCellValue('N101', round($r731, 2));

        $nombrearchivo = 'FORM104-' . $this->periodo . "_" . $this->user->nick . '.xlsx';
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

    private function generar_formulario_renta()
    {
        $retcompras = $this->lineasfacturasprov->listado_ret_renta_formulario($this->idempresa, $this->fec_desde, $this->fec_hasta);

        $this->template = false;

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Konta")->setTitle("FORMULARIO 103");
        $inputFileName = 'formatos/sri/FORM103.xlsx';
        $objReader     = PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel   = $objReader->load($inputFileName);
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Fecha de Impresion: ' . date('d-m-Y H:i:s'));
        $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Usuario Impresión: ' . $this->user->nick);
        $objPHPExcel->getActiveSheet()->setCellValue('L2', $this->periodo);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A3', $this->empresa->ruc, PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue('E3', $this->empresa->razonsocial);

        //recorro las retenciones para llenar los datos en el reporte

        foreach ($retcompras as $key => $ret) {
            switch ($ret['codigobase']) {
                case '302':
                    $objPHPExcel->getActiveSheet()->setCellValue('L7', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N7', round($ret['valret'], 2));
                    break;
                case '303':
                    $objPHPExcel->getActiveSheet()->setCellValue('L9', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N9', round($ret['valret'], 2));
                    break;
                case '304':
                    $objPHPExcel->getActiveSheet()->setCellValue('L10', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N10', round($ret['valret'], 2));
                    break;
                case '307':
                    $objPHPExcel->getActiveSheet()->setCellValue('L11', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N11', round($ret['valret'], 2));
                    break;
                case '308':
                    $objPHPExcel->getActiveSheet()->setCellValue('L12', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N12', round($ret['valret'], 2));
                    break;
                case '309':
                    $objPHPExcel->getActiveSheet()->setCellValue('L13', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N13', round($ret['valret'], 2));
                    break;
                case '310':
                    $objPHPExcel->getActiveSheet()->setCellValue('L14', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N14', round($ret['valret'], 2));
                    break;
                case '311':
                    $objPHPExcel->getActiveSheet()->setCellValue('L15', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N15', round($ret['valret'], 2));
                    break;
                case '312':
                    $objPHPExcel->getActiveSheet()->setCellValue('L16', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N16', round($ret['valret'], 2));
                    break;
                case '3120':
                    $objPHPExcel->getActiveSheet()->setCellValue('L17', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N17', round($ret['valret'], 2));
                    break;
                case '314':
                    $objPHPExcel->getActiveSheet()->setCellValue('L18', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N18', round($ret['valret'], 2));
                    break;
                case '319':
                    $objPHPExcel->getActiveSheet()->setCellValue('L20', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N20', round($ret['valret'], 2));
                    break;
                case '320':
                    $objPHPExcel->getActiveSheet()->setCellValue('L21', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N21', round($ret['valret'], 2));
                    break;
                case '322':
                    $objPHPExcel->getActiveSheet()->setCellValue('L22', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N22', round($ret['valret'], 2));
                    break;
                case '323':
                    $objPHPExcel->getActiveSheet()->setCellValue('L23', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N23', round($ret['valret'], 2));
                    break;
                case '324':
                    $objPHPExcel->getActiveSheet()->setCellValue('L24', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N24', round($ret['valret'], 2));
                    break;
                case '325':
                    $objPHPExcel->getActiveSheet()->setCellValue('L25', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N25', round($ret['valret'], 2));
                    break;
                case '326':
                    $objPHPExcel->getActiveSheet()->setCellValue('L26', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N26', round($ret['valret'], 2));
                    break;
                case '327':
                    $objPHPExcel->getActiveSheet()->setCellValue('L27', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N27', round($ret['valret'], 2));
                    break;
                case '328':
                    $objPHPExcel->getActiveSheet()->setCellValue('L28', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N28', round($ret['valret'], 2));
                    break;
                case '329':
                    $objPHPExcel->getActiveSheet()->setCellValue('L29', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N29', round($ret['valret'], 2));
                    break;
                case '331':
                    $objPHPExcel->getActiveSheet()->setCellValue('L30', round($ret['baseimp'], 2));
                    //$objPHPExcel->getActiveSheet()->setCellValue('N17', round($ret['valret'], 2));
                    break;
                case '332':
                    $objPHPExcel->getActiveSheet()->setCellValue('L31', round($ret['baseimp'], 2));
                    //$objPHPExcel->getActiveSheet()->setCellValue('N31', round($ret['valret'], 2));
                    break;
                case '333':
                    $objPHPExcel->getActiveSheet()->setCellValue('L32', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N32', round($ret['valret'], 2));
                    break;
                case '334':
                    $objPHPExcel->getActiveSheet()->setCellValue('L33', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N33', round($ret['valret'], 2));
                    break;
                case '335':
                    $objPHPExcel->getActiveSheet()->setCellValue('L34', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N34', round($ret['valret'], 2));
                    break;
                case '336':
                    $objPHPExcel->getActiveSheet()->setCellValue('L36', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N36', round($ret['valret'], 2));
                    break;
                case '337':
                    $objPHPExcel->getActiveSheet()->setCellValue('L37', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N37', round($ret['valret'], 2));
                    break;
                case '3380':
                    $objPHPExcel->getActiveSheet()->setCellValue('L38', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N38', round($ret['valret'], 2));
                    break;
                case '3400':
                    $objPHPExcel->getActiveSheet()->setCellValue('L39', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N39', round($ret['valret'], 2));
                    break;
                case '343':
                    $objPHPExcel->getActiveSheet()->setCellValue('L41', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N41', round($ret['valret'], 2));
                    break;
                case '344':
                    $objPHPExcel->getActiveSheet()->setCellValue('L42', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N42', round($ret['valret'], 2));
                    break;
                case '3440':
                    $objPHPExcel->getActiveSheet()->setCellValue('L43', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N43', round($ret['valret'], 2));
                    break;
                case '345':
                    $objPHPExcel->getActiveSheet()->setCellValue('L44', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N44', round($ret['valret'], 2));
                    break;
                case '346':
                    $objPHPExcel->getActiveSheet()->setCellValue('L45', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N45', round($ret['valret'], 2));
                    break;
                case '348':
                    $objPHPExcel->getActiveSheet()->setCellValue('L46', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N46', round($ret['valret'], 2));
                    break;
                case '350':
                    $objPHPExcel->getActiveSheet()->setCellValue('L47', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N47', round($ret['valret'], 2));
                    break;
                case '402':
                    $objPHPExcel->getActiveSheet()->setCellValue('L53', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N53', round($ret['valret'], 2));
                    break;
                case '403':
                    $objPHPExcel->getActiveSheet()->setCellValue('L54', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N54', round($ret['valret'], 2));
                    break;
                case '404':
                    $objPHPExcel->getActiveSheet()->setCellValue('L55', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N55', round($ret['valret'], 2));
                    break;
                case '4050':
                    $objPHPExcel->getActiveSheet()->setCellValue('L56', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N56', round($ret['valret'], 2));
                    break;
                case '4060':
                    $objPHPExcel->getActiveSheet()->setCellValue('L57', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N57', round($ret['valret'], 2));
                    break;
                case '4070':
                    $objPHPExcel->getActiveSheet()->setCellValue('L58', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N58', round($ret['valret'], 2));
                    break;
                case '408':
                    $objPHPExcel->getActiveSheet()->setCellValue('L59', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N59', round($ret['valret'], 2));
                    break;
                case '409':
                    $objPHPExcel->getActiveSheet()->setCellValue('L60', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N60', round($ret['valret'], 2));
                    break;
                case '410':
                    $objPHPExcel->getActiveSheet()->setCellValue('L61', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N61', round($ret['valret'], 2));
                    break;
                case '411':
                    $objPHPExcel->getActiveSheet()->setCellValue('L62', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N62', round($ret['valret'], 2));
                    break;
                case '412':
                    $objPHPExcel->getActiveSheet()->setCellValue('L63', round($ret['baseimp'], 2));
                    //$objPHPExcel->getActiveSheet()->setCellValue('N63', round($ret['valret'], 2));
                    break;
                case '413':
                    $objPHPExcel->getActiveSheet()->setCellValue('L65', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N65', round($ret['valret'], 2));
                    break;
                case '414':
                    $objPHPExcel->getActiveSheet()->setCellValue('L66', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N66', round($ret['valret'], 2));
                    break;
                case '415':
                    $objPHPExcel->getActiveSheet()->setCellValue('L67', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N67', round($ret['valret'], 2));
                    break;
                case '4160':
                    $objPHPExcel->getActiveSheet()->setCellValue('L68', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N68', round($ret['valret'], 2));
                    break;
                case '4170':
                    $objPHPExcel->getActiveSheet()->setCellValue('L69', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N69', round($ret['valret'], 2));
                    break;
                case '4180':
                    $objPHPExcel->getActiveSheet()->setCellValue('L70', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N70', round($ret['valret'], 2));
                    break;
                case '419':
                    $objPHPExcel->getActiveSheet()->setCellValue('L71', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N71', round($ret['valret'], 2));
                    break;
                case '420':
                    $objPHPExcel->getActiveSheet()->setCellValue('L72', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N72', round($ret['valret'], 2));
                    break;
                case '421':
                    $objPHPExcel->getActiveSheet()->setCellValue('L73', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N73', round($ret['valret'], 2));
                    break;
                case '422':
                    $objPHPExcel->getActiveSheet()->setCellValue('L74', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N74', round($ret['valret'], 2));
                    break;
                case '423':
                    $objPHPExcel->getActiveSheet()->setCellValue('L75', round($ret['baseimp'], 2));
                    //$objPHPExcel->getActiveSheet()->setCellValue('N75', round($ret['valret'], 2));
                    break;
                case '424':
                    $objPHPExcel->getActiveSheet()->setCellValue('L77', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N77', round($ret['valret'], 2));
                    break;
                case '425':
                    $objPHPExcel->getActiveSheet()->setCellValue('L78', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N78', round($ret['valret'], 2));
                    break;
                case '4260':
                    $objPHPExcel->getActiveSheet()->setCellValue('L79', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N79', round($ret['valret'], 2));
                    break;
                case '4270':
                    $objPHPExcel->getActiveSheet()->setCellValue('L80', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N80', round($ret['valret'], 2));
                    break;
                case '4280':
                    $objPHPExcel->getActiveSheet()->setCellValue('L81', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N81', round($ret['valret'], 2));
                    break;
                case '429':
                    $objPHPExcel->getActiveSheet()->setCellValue('L82', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N82', round($ret['valret'], 2));
                    break;
                case '430':
                    $objPHPExcel->getActiveSheet()->setCellValue('L83', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N83', round($ret['valret'], 2));
                    break;
                case '431':
                    $objPHPExcel->getActiveSheet()->setCellValue('L84', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N84', round($ret['valret'], 2));
                    break;
                case '432':
                    $objPHPExcel->getActiveSheet()->setCellValue('L85', round($ret['baseimp'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('N85', round($ret['valret'], 2));
                    break;
                case '433':
                    $objPHPExcel->getActiveSheet()->setCellValue('L86', round($ret['baseimp'], 2));
                    //$objPHPExcel->getActiveSheet()->setCellValue('N86', round($ret['valret'], 2));
                    break;
                default:
                    // code...
                    break;
            }
        }

        $nombrearchivo = 'FORM103-' . $this->periodo . "_" . $this->user->nick . '.xlsx';
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

    private function generar_ats()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'; //Cabecera del ATS
        $xml .= '<iva>'; //Apertura del Tag de IVA
        //Genero la cabecera del ATS
        $xml .= $this->cabecera_ats();
        //Genero el detalle de compras
        $xml .= $this->compras_ats();
        //Genero del detalle de ventas
        $xml .= $this->ventas_ats();
        //Genero el detalle de establecimientos
        $xml .= $this->ventas_establecimientos_ats();
        //Genero el detalle de anulados
        $xml .= $this->anulados_ats();
        $xml .= '</iva>'; //Cierre del Tag de IVA
        //limpio los caracteres
        $xml = limpiarCaracteres($xml);
        
        $dom                     = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput       = true;
        $dom->loadXML($xml);
        $xml_pretty = $dom->saveXML();

        $rutaAts = JG_MYDOCS . 'datosEmpresas/' . $this->idempresa . "/xmlAts/";
        if (!file_exists($rutaAts)) {
            @mkdir($rutaAts, 0777, true);
        }

        $ats = $rutaAts . "AT-" . $this->mes . $this->anio . ".xml";
        if (file_exists($ats)) {
            unlink($ats);
        }

        $nombrearchivo = "AT-" . $this->mes . $this->anio . ".xml";

        //Guardamos el Archivo XML
        $file = fopen($ats, "w+");
        //Escribo el archivo
        fwrite($file, $xml_pretty);
        //Cierro el Archivo
        fclose($file);

        header("Content-Disposition: attachment; filename=" . $nombrearchivo);
        header("Content-Type: application/octet-stream");
        header("Content-Length: " . filesize($ats));
        readfile($ats);

        if (file_exists($ats)) {
            unlink($ats);
        }
    }

    private function cabecera_ats()
    {
        $cabecera = '<TipoIDInformante>R</TipoIDInformante>';
        $cabecera .= '<IdInformante>' . $this->empresa->ruc . '</IdInformante>';
        $cabecera .= '<razonSocial>' . limpiarTexto($this->empresa->razonsocial) . '</razonSocial>';
        $cabecera .= '<Anio>' . $this->anio . '</Anio>';
        $cabecera .= '<Mes>' . $this->mes . '</Mes>';
        if ($this->semestral) {
            $cabecera .= '<regimenMicroempresa>SI</regimenMicroempresa>';
        }
        //Busco la cantidad de establecimientos
        $numEst = count($this->establecimiento->getEstablecimientosEmpresa($this->idempresa));
        $cabecera .= '<numEstabRuc>' . str_pad($numEst, 3, "0", STR_PAD_LEFT) . '</numEstabRuc>';
        //Busco el total de ventas
        $totVentas = 0;
        $VentasEst = $this->facturas_cliente->totalVentas($this->idempresa, $this->fec_desde, $this->fec_hasta);
        foreach ($VentasEst as $key => $d) {
            if ($d['coddocumento'] == '04') {
                $totVentas -= floatval($d['total']);
            } else {
                $totVentas += floatval($d['total']);
            }
        }
        $cabecera .= '<totalVentas>' . show_numero2($totVentas) . '</totalVentas>';
        $cabecera .= '<codigoOperativo>IVA</codigoOperativo>';

        return $cabecera;
    }

    private function compras_ats()
    {
        $compras      = '';
        $docs_compras = $this->facturas_proveedor->listado_facturas_ats($this->idempresa, $this->fec_desde, $this->fec_hasta);
        if ($docs_compras) {
            $compras .= '<compras>'; //Aperturo Tag Compras
            foreach ($docs_compras as $key => $compra) {
                $compras .= '<detalleCompras>'; //Apertura Tag detalleCompras
                $compras .= '<codSustento>' . $compra->get_codsustento() . '</codSustento>';
                switch ($compra->tipoid) {
                    case 'R':
                        $tipid = '01';
                        break;
                    case 'C':
                        $tipid = '02';
                        break;
                    case 'P':
                        $tipid = '03';
                        break;
                    default:
                        $tipid = '';
                        break;
                }
                $compras .= '<tpIdProv>' . $tipid . '</tpIdProv>';
                $compras .= '<idProv>' . $compra->identificacion . '</idProv>';
                $compras .= '<tipoComprobante>' . $compra->coddocumento . '</tipoComprobante>';
                $compras .= '<parteRel>NO</parteRel>';
                if ($tipid == '03') {
                    $compras .= '<tipoProv>01</tipoProv>';
                    $compras .= '<denopr>' . limpiarTexto($compra->razonsocial) . '</denopr>';
                }
                $compras .= '<fechaRegistro>' . date('d/m/Y', strtotime($compra->fec_registro)) . '</fechaRegistro>';
                $numcomp = explode("-", $compra->numero_documento);
                $compras .= '<establecimiento>' . $numcomp[0] . '</establecimiento>';
                $compras .= '<puntoEmision>' . $numcomp[1] . '</puntoEmision>';
                $compras .= '<secuencial>' . $numcomp[2] . '</secuencial>';
                $compras .= '<fechaEmision>' . date('d/m/Y', strtotime($compra->fec_emision)) . '</fechaEmision>';
                $compras .= '<autorizacion>' . $compra->nro_autorizacion . '</autorizacion>';
                $compras .= '<baseNoGraIva>' . show_numero2($compra->base_noi) . '</baseNoGraIva>';
                $compras .= '<baseImponible>' . show_numero2($compra->base_0) . '</baseImponible>';
                $compras .= '<baseImpGrav>' . show_numero2($compra->base_gra) . '</baseImpGrav>';
                $compras .= '<baseImpExe>' . show_numero2($compra->base_exc) . '</baseImpExe>';
                $compras .= '<montoIce>' . show_numero2($compra->totalice) . '</montoIce>';
                $compras .= '<montoIva>' . show_numero2($compra->totaliva) . '</montoIva>';
                //valor de retenciones
                $valRetBien10      = 0.00;
                $valRetServ20      = 0.00;
                $valorRetBienes    = 0.00;
                $valRetServ50      = 0.00;
                $valorRetServicios = 0.00;
                $valRetServ100     = 0.00;
                $air               = '<air>';
                foreach ($compra->get_retencion() as $key => $rt) {
                    if ($rt['especie'] == 'iva') {
                        switch ($rt['porcentaje']) {
                            case 10:
                                $valRetBien10 += floatval($rt['valor']);
                                break;
                            case 20:
                                $valRetServ20 += floatval($rt['valor']);
                                break;
                            case 30:
                                $valorRetBienes += floatval($rt['valor']);
                                break;
                            case 50:
                                $valRetServ50 += floatval($rt['valor']);
                                break;
                            case 70:
                                $valorRetServicios += floatval($rt['valor']);
                                break;
                            case 100:
                                $valRetServ100 += floatval($rt['valor']);
                                break;
                            default:
                                break;
                        }
                    } else if ($rt['especie'] == 'renta') {
                        $air .= '<detalleAir>';
                        $air .= '<codRetAir>' . $rt['codigo'] . '</codRetAir>';
                        $air .= '<baseImpAir>' . show_numero2($rt['baseimp']) . '</baseImpAir>';
                        $air .= '<porcentajeAir>' . show_numero2($rt['porcentaje']) . '</porcentajeAir>';
                        $air .= '<valRetAir>' . show_numero2($rt['valor']) . '</valRetAir>';
                        $air .= '</detalleAir>';
                    }
                }
                $air .= '</air>';
                $compras .= '<valRetBien10>' . show_numero2($valRetBien10) . '</valRetBien10>';
                $compras .= '<valRetServ20>' . show_numero2($valRetServ20) . '</valRetServ20>';
                $compras .= '<valorRetBienes>' . show_numero2($valorRetBienes) . '</valorRetBienes>';
                $compras .= '<valRetServ50>' . show_numero2($valRetServ50) . '</valRetServ50>';
                $compras .= '<valorRetServicios>' . show_numero2($valorRetServicios) . '</valorRetServicios>';
                $compras .= '<valRetServ100>' . show_numero2($valRetServ100) . '</valRetServ100>';
                $compras .= '<valorRetencionNc>' . show_numero2(0) . '</valorRetencionNc>';
                $compras .= '<totbasesImpReemb>' . show_numero2(0) . '</totbasesImpReemb>';
                //Pago Exterior
                $compras .= '<pagoExterior>';
                $compras .= '<pagoLocExt>01</pagoLocExt>';
                $compras .= '<paisEfecPago>NA</paisEfecPago>';
                $compras .= '<aplicConvDobTrib>NA</aplicConvDobTrib>';
                $compras .= '<pagExtSujRetNorLeg>NA</pagExtSujRetNorLeg>';
                $compras .= '</pagoExterior>';
                if ($compra->coddocumento != '04') {
                    $compras .= '<formasDePago>';
                    $compras .= '<formaPago>20</formaPago>';
                    $compras .= '</formasDePago>';
                }
                if ($compra->numero_retencion) {
                    $compras .= $air;
                    $numret = explode("-", $compra->numero_retencion);
                    $compras .= '<estabRetencion1>' . $numret[0] . '</estabRetencion1>';
                    $compras .= '<ptoEmiRetencion1>' . $numret[1] . '</ptoEmiRetencion1>';
                    $compras .= '<secRetencion1>' . $numret[2] . '</secRetencion1>';
                    $compras .= '<autRetencion1>' . $compra->nro_autorizacion_ret . '</autRetencion1>';
                    $compras .= '<fechaEmiRet1>' . date('d/m/Y', strtotime($compra->fec_registro)) . '</fechaEmiRet1>';
                }

                if ($compra->coddocumento == '04' || $compra->coddocumento == '05') {
                    $compras .= '<docModificado>' . $compra->coddocumento_mod . '</docModificado>';
                    $nummod = explode("-", $compra->numero_documento_mod);
                    $compras .= '<estabModificado>' . $nummod[0] . '</estabModificado>';
                    $compras .= '<ptoEmiModificado>' . $nummod[1] . '</ptoEmiModificado>';
                    $compras .= '<secModificado>' . $nummod[2] . '</secModificado>';
                    $compras .= '<autModificado>' . $compra->nro_autorizacion_mod . '</autModificado>';
                }

                $compras .= '</detalleCompras>'; // Cierre del Tag detalleCompras
            }
            $compras .= '</compras>'; // Cierre del Tag Compras
        }

        return $compras;
    }

    private function ventas_ats()
    {
        $ventas = '';

        $datosvtas = $this->facturas_cliente->ventasAts($this->idempresa, $this->fec_desde, $this->fec_hasta);
        $datosrt   = $this->retencionescli->retencionesAts($this->idempresa, $this->fec_desde, $this->fec_hasta);

        if ($datosvtas || $datosrt) {
            $ventas .= '<ventas>';
            foreach ($datosvtas as $key => $venta) {
                $ventas .= '<detalleVentas>';
                switch ($venta['tipoid']) {
                    case 'R':
                        $tipo = '04';
                        break;
                    case 'C':
                        $tipo = '05';
                        break;
                    case 'P':
                        $tipo = '06';
                        break;
                    case 'F':
                        $tipo = '07';
                        break;
                    default:
                        break;
                }
                $ventas .= '<tpIdCliente>' . $tipo . '</tpIdCliente>';
                $ventas .= '<idCliente>' . $venta['identificacion'] . '</idCliente>';
                if ($tipo != '07') {
                    $ventas .= '<parteRelVtas>NO</parteRelVtas>';
                }
                $coddoc = $venta['coddocumento'];
                if ($venta['coddocumento'] == '01') {
                    $coddoc = '18';
                }
                $ventas .= '<tipoComprobante>' . $coddoc . '</tipoComprobante>';
                $ventas .= '<tipoEmision>' . $venta['tipoemision'] . '</tipoEmision>';
                $ventas .= '<numeroComprobantes>' . $venta['totdoc'] . '</numeroComprobantes>';
                $ventas .= '<baseNoGraIva>' . show_numero2($venta['baseno']) . '</baseNoGraIva>';
                $ventas .= '<baseImponible>' . show_numero2($venta['base0']) . '</baseImponible>';
                $ventas .= '<baseImpGrav>' . show_numero2($venta['basegra']) . '</baseImpGrav>';
                $ventas .= '<montoIva>' . show_numero2($venta['iva']) . '</montoIva>';
                $ventas .= '<montoIce>' . show_numero2($venta['ice']) . '</montoIce>';
                //Busco las retenciones de cada cliente
                $iva = 0;
                $rta = 0;
                foreach ($datosrt as $key => $ret) {
                    if ($ret['identificacion'] == $venta['identificacion']) {
                        if ($ret['especie'] == 'iva') {
                            $iva = $ret['total'];
                            unset($datosrt[$key]);
                        } else if ($ret['especie'] == 'renta') {
                            $rta = $ret['total'];
                            unset($datosrt[$key]);
                        }
                    }
                }
                $ventas .= '<valorRetIva>' . show_numero2($iva) . '</valorRetIva>';
                $ventas .= '<valorRetRenta>' . show_numero2($rta) . '</valorRetRenta>';
                if ($coddoc != '04') {
                    $ventas .= '<formasDePago>';
                    $ventas .= '<formaPago>20</formaPago>';
                    $ventas .= '</formasDePago>';
                }
                $ventas .= '</detalleVentas>';
            }

            if ($datosrt) {
                $retfal = array();
                foreach ($datosrt as $key => $rt) {
                    if (isset($retfal[$rt['identificacion']])) {
                        if ($rt['especie'] == 'iva') {
                            $retfal[$rt['identificacion']]['tiva'] += floatval($rt['total']);
                        } else if ($rt['especie'] == 'renta') {
                            $retfal[$rt['identificacion']]['trta'] += floatval($rt['total']);
                        }
                    } else {
                        $retfal[$rt['identificacion']]['tipoid'] = $rt['tipoid'];
                        if ($rt['especie'] == 'iva') {
                            $retfal[$rt['identificacion']]['tiva'] = floatval($rt['total']);
                            $retfal[$rt['identificacion']]['trta'] = floatval(0);
                        } else if ($rt['especie'] == 'renta') {
                            $retfal[$rt['identificacion']]['trta'] = floatval($rt['total']);
                            $retfal[$rt['identificacion']]['tiva'] = floatval(0);
                        }
                    }
                }
                //recorro los faltantes
                foreach ($retfal as $key => $rf) {
                    $ventas .= '<detalleVentas>';
                    switch ($rf['tipoid']) {
                        case 'R':
                            $tipo = '04';
                            break;
                        case 'C':
                            $tipo = '05';
                            break;
                        case 'P':
                            $tipo = '06';
                            break;
                        case 'F':
                            $tipo = '07';
                            break;
                        default:
                            break;
                    }
                    $ventas .= '<tpIdCliente>' . $tipo . '</tpIdCliente>';
                    $ventas .= '<idCliente>' . $key . '</idCliente>';
                    $ventas .= '<parteRelVtas>NO</parteRelVtas>';
                    $ventas .= '<tipoComprobante>18</tipoComprobante>';
                    $ventas .= '<tipoEmision>F</tipoEmision>';
                    $ventas .= '<numeroComprobantes>0</numeroComprobantes>';
                    $ventas .= '<baseNoGraIva>0.00</baseNoGraIva>';
                    $ventas .= '<baseImponible>0.00</baseImponible>';
                    $ventas .= '<baseImpGrav>0.00</baseImpGrav>';
                    $ventas .= '<montoIva>0.00</montoIva>';
                    $ventas .= '<montoIce>0.00</montoIce>';
                    $ventas .= '<valorRetIva>' . show_numero2($rf['tiva']) . '</valorRetIva>';
                    $ventas .= '<valorRetRenta>' . show_numero2($rf['trta']) . '</valorRetRenta>';
                    $ventas .= '<formasDePago>';
                    $ventas .= '<formaPago>20</formaPago>';
                    $ventas .= '</formasDePago>';
                    $ventas .= '</detalleVentas>';
                }
            }
            $ventas .= '</ventas>';
        }

        return $ventas;
    }

    private function ventas_establecimientos_ats()
    {
        $lista_est = array();

        $establecimiento = '';
        $datosEst        = $this->facturas_cliente->totalVentas($this->idempresa, $this->fec_desde, $this->fec_hasta);
        if ($datosEst) {
            foreach ($datosEst as $key => $de) {
                if (isset($lista_est[$de['codigo']])) {
                    if ($de['coddocumento'] == '04') {
                        $lista_est[$de['codigo']]['base'] -= $de['total'];
                        $lista_est[$de['codigo']]['iva'] -= $de['iva'];
                    } else {
                        $lista_est[$de['codigo']]['base'] += $de['total'];
                        $lista_est[$de['codigo']]['iva'] += $de['iva'];
                    }
                } else {
                    if ($de['coddocumento'] == '04') {
                        $lista_est[$de['codigo']]['base'] = (0 - $de['total']);
                        $lista_est[$de['codigo']]['iva']  = (0 - $de['iva']);
                    } else {
                        $lista_est[$de['codigo']]['base'] = $de['total'];
                        $lista_est[$de['codigo']]['iva']  = $de['iva'];
                    }
                }
            }

            $establecimiento .= '<ventasEstablecimiento>';
            foreach ($lista_est as $key => $le) {
                $establecimiento .= '<ventaEst>';
                $establecimiento .= '<codEstab>' . $key . '</codEstab>';
                $establecimiento .= '<ventasEstab>' . show_numero2($le['base']) . '</ventasEstab>';
                $establecimiento .= '<ivaComp>' . show_numero2($le['iva']) . '</ivaComp>';
                $establecimiento .= '</ventaEst>';
            }
            $establecimiento .= '</ventasEstablecimiento>';
        } else {
            $establecimiento .= '<ventasEstablecimiento>';
            foreach ($this->establecimiento->getEstablecimientosEmpresa($this->idempresa) as $key => $es) {
                $establecimiento .= '<ventaEst>';
                $establecimiento .= '<codEstab>' . $es['codigo'] . '</codEstab>';
                $establecimiento .= '<ventasEstab>' . show_numero2(0) . '</ventasEstab>';
                $establecimiento .= '<ivaComp>' . show_numero2(0) . '</ivaComp>';
                $establecimiento .= '</ventaEst>';
            }
            $establecimiento .= '</ventasEstablecimiento>';
        }

        return $establecimiento;
    }

    private function anulados_ats()
    {
        $anulados = '';
        $datosanu = $this->facturas_cliente->getAnulados($this->idempresa, $this->fec_desde, $this->fec_hasta);
        if ($datosanu) {
            $anulados .= '<anulados>';
            foreach ($datosanu as $key => $da) {
                $anulados .= '<detalleAnulados>';
                $anulados .= '<tipoComprobante>' . $da->coddocumento . '</tipoComprobante>';
                $doc = explode("-", $da->numero_documento);
                $anulados .= '<establecimiento>' . $doc[0] . '</establecimiento>';
                $anulados .= '<puntoEmision>' . $doc[1] . '</puntoEmision>';
                $anulados .= '<secuencialInicio>' . $doc[2] . '</secuencialInicio>';
                $anulados .= '<secuencialFin>' . $doc[2] . '</secuencialFin>';
                $anulados .= '<autorizacion>' . $da->nro_autorizacion . '</autorizacion>';
                $anulados .= '</detalleAnulados>';
            }
            $anulados .= '</anulados>';
        }
        return $anulados;
    }
}
