<?php

require_once 'extras/phpexcel/PHPExcel.php';
require_once 'extras/phpexcel/PHPExcel/IOFactory.php';

/**
 * Controlador de Contabilidad -> Ejercicios.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class lista_ejercicios extends controller
{
    //Filtros
    public $idempresa;
    //modelos
    public $ejercicios;
    //variables
    public $resultados;
    public $cantidad;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ejercicios', 'Contabilidad', true, true, false, 'bi bi-hdd-stack-fill');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        if (isset($_POST['new_nombre'])) {
            $this->crear_ejercicio();
        } else if (isset($_GET['delete'])) {
            $this->eliminar_ejercicio();
        } else if (isset($_GET['cerrar'])) {
            $this->cerrar_ejercicio();
        } else if (isset($_GET['formato'])) {
            $this->descargar_formato();
        } else if (isset($_FILES['archivoxlsx']) && $_FILES['archivoxlsx']['name'] != '') {
            $this->procesar_plan_cuentas();
        }

        $this->buscar();
    }

    private function init_modelos()
    {
        $this->ejercicios  = new ejercicios();
        $this->plancuentas = new plancuentas();
    }

    private function init_filter()
    {
        $this->idempresa       = $this->empresa->idempresa;
        $this->ultimo_grupo    = null;
        $this->ultima_cuenta   = null;
        $this->ultimo_subgrupo = null;
        $this->idejercicio     = false;
        if (isset($_POST['cargar_plan'])) {
            $this->idejercicio = $_POST['cargar_plan'];
            $ejer              = $this->ejercicios->get($this->idejercicio);
            if (!$ejer) {
                $this->new_error_msg('Ejercicio no encontrado, verifique los datos.');
                return;
            }
        }
    }

    private function crear_ejercicio()
    {
        if (!$this->ejercicios->get_by_codigo($this->idempresa, $_POST['new_nombre'])) {
            $ejercicio                = new ejercicios();
            $ejercicio->idempresa     = $this->idempresa;
            $ejercicio->nombre        = $_POST['new_nombre'];
            $ejercicio->fec_inicio    = $_POST['new_nombre'] . "-01-01";
            $ejercicio->fec_fin       = $_POST['new_nombre'] . "-12-31";
            $ejercicio->fec_creacion  = date('Y-m-d');
            $ejercicio->nick_creacion = $this->user->nick;

            if ($ejercicio->save()) {
                $this->new_message("Ejercicio creado correctamente.");
            } else {
                $this->new_error_msg("No se pudo crear el ejercicio, verifique los datos y vuelva a intentarlo.");
            }

        } else {
            $this->new_advice("El ejercicio " . $_POST['new_nombre'] . " ya se encuentra registrado.");
        }
    }

    private function eliminar_ejercicio()
    {
        $ejercicio = $this->ejercicios->get($_GET['delete']);
        if ($ejercicio) {
            if ($ejercicio->delete()) {
                $this->new_message("Ejercicio eliminado correctamente.");
            } else {
                $this->new_error_msg("No se puede eliminar el ejercicio.");
            }
        } else {
            $this->new_advice("Error al eliminar, el ejercicio no se encuentra registrado o ya fue eliminado.");
        }
    }

    private function cerrar_ejercicio()
    {
        $ejercicio = $this->ejercicios->get($_GET['cerrar']);
        if ($ejercicio) {
            //Proceso para cerrar el ejercicio
        } else {
            $this->new_advice("El ejercicio no se encuentra registrado, imposible cerrar el ejercicio.");
        }
    }

    private function buscar()
    {
        $this->resultados = $this->ejercicios->all_by_idempresa($this->idempresa);
        $this->cantidad   = count($this->resultados);
    }

    private function descargar_formato()
    {
        $this->template = false;
        $objPHPExcel    = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Konta")->setTitle("Plan de Cuentas");
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle("Plan de Cuentas");
        $titulo  = $this->empresa->razonsocial;
        $titulo1 = "PLAN DE CUENTAS";
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:B1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $titulo);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', $titulo1);
        $objPHPExcel->getActiveSheet()->getStyle('A1:B2')->applyFromArray(styleCabeceraReporte());
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A3', 'CODIGO')
            ->setCellValue('B3', 'NOMBRE');
        $objPHPExcel->getActiveSheet()->getStyle('A3:B3')->applyFromArray(styleNombreColumnas());
        $objPHPExcel->getActiveSheet()->setAutoFilter('A3:B3');

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(100);

        $nombrearchivo = 'formato_plan_cuentas_' . $this->user->nick . '.xlsx';
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

    private function procesar_plan_cuentas()
    {
        $longgrupos     = 1;
        $longsubgrupos  = array();
        $longcuentas    = 0;
        $longsubcuentas = 0;

        if (is_uploaded_file($_FILES['archivoxlsx']['tmp_name'])) {
            $archivo       = $_FILES['archivoxlsx']['tmp_name'];
            $inputFileType = PHPExcel_IOFactory::identify($archivo);
            $objReader     = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel   = $objReader->load($archivo);
            $sheet         = $objPHPExcel->getSheet(0);
            if ($this->validar_formato($sheet)) {
                $highestRow = $sheet->getHighestRow();
                if ($highestRow > 3) {
                    for ($i = 4; $i < $highestRow; $i++) {
                        $codigo = str_replace('.', '', trim($sheet->getCell("A" . $i)->getValue()));
                        if ($codigo != '') {
                            $longsubgrupos[strlen($codigo)] = strlen($codigo);
                            if (strlen($codigo) > $longsubcuentas) {
                                $longcuentas    = $longsubcuentas;
                                $longsubcuentas = strlen($codigo);
                            }
                        }
                    }
                    //Elimino los grupos, cuentas y subcuentas
                    unset($longsubgrupos[$longgrupos]);
                    unset($longsubgrupos[$longcuentas]);
                    unset($longsubgrupos[$longsubcuentas]);

                    $valorsub = min($longsubgrupos);

                    $num_grupos     = 0;
                    $num_subgrupos  = 0;
                    $num_cuentas    = 0;
                    $num_subcuentas = 0;
                    $continuar      = true;

                    //Procesamos el plan de Cuentas
                    for ($i = 4; $i < $highestRow; $i++) {
                        $codigo = str_replace('.', '', trim($sheet->getCell("A" . $i)->getValue()));
                        $nombre = trim($sheet->getCell("B" . $i)->getValue());
                        if ($codigo != '') {
                            /// ahora procesamos los datos en función de las longitudes de los códigos de las Cuentas
                            switch (strlen($codigo)) {
                                case $longgrupos:
                                    $continuar = $this->crear_grupo($codigo, $nombre);
                                    $num_grupos++;
                                    break;

                                case $longcuentas;
                                    $continuar = $this->crear_cuenta($codigo, $nombre);
                                    $num_cuentas++;
                                    break;

                                case $longsubcuentas:
                                    $continuar = $this->crear_subcuenta($codigo, $nombre);
                                    $num_subcuentas++;
                                    break;

                                default:
                                    $continuar = $this->crear_subgrupo($codigo, $nombre, $valorsub);
                                    $num_subgrupos++;
                                    break;
                            }
                        }
                    }

                    if ($continuar) {
                        $this->new_message('Proceso terminado: ' . $num_grupos . ' Grupos, ' . $num_subgrupos . ' Subgrupos, ' . $num_cuentas . ' Cuentas y ' . $num_subcuentas . ' Subcuentas creadas.');
                    }
                } else {
                    $this->new_advice('El archivo se encuentra sin Datos para importar.');
                }
            } else {
                $this->new_error_msg('Documento No Valido. Debe descargar el formato, llenarlo y cargarlo para poder realizar la carga.');
            }
        } else {
            $this->new_error_msg("se encontró un error al cargar el archivo.");
        }
    }

    private function validar_formato($excel)
    {
        if ($excel->getCell("A3")->getValue() != 'CODIGO') {
            return false;
        } else if ($excel->getCell("B3")->getValue() != 'NOMBRE') {
            return false;
        }

        return true;
    }

    private function crear_grupo($codigo, $nombre)
    {
        $grupo = $this->plancuentas->get_by_codigo($this->idempresa, $this->idejercicio, $codigo);
        if ($grupo) {
            /// ya existe devolvemos el que existe
            $this->ultimo_grupo = $grupo;
            return true;
        } else {
            //No existe lo creo
            $grupo                = new plancuentas();
            $grupo->idempresa     = $this->idempresa;
            $grupo->idejercicio   = $this->idejercicio;
            $grupo->tipo          = 'G';
            $grupo->codigo        = $codigo;
            $grupo->nombre        = $nombre;
            $grupo->fec_creacion  = date('Y-m-d');
            $grupo->nick_creacion = $this->user->nick;

            if ($grupo->save()) {
                $this->ultimo_grupo = $grupo;
                return true;
            } else {
                $this->new_error_msg('Error al guardar el Grupo: ' . $grupo->codigo);
                return false;
            }
        }
    }

    private function crear_subgrupo($codigo, $nombre, $valorsub)
    {
        $subgrupo = $this->plancuentas->get_by_codigo($this->idempresa, $this->idejercicio, $codigo);
        if ($subgrupo) {
            /// ya existe devolvemos el que existe
            $this->ultimo_subgrupo = $subgrupo;
            return true;
        } else {
            //No existe lo creo
            $subgrupo                = new plancuentas();
            $subgrupo->idempresa     = $this->idempresa;
            $subgrupo->idejercicio   = $this->idejercicio;
            $subgrupo->tipo          = 'SG';
            $subgrupo->codigo        = $codigo;
            $subgrupo->nombre        = $nombre;
            $subgrupo->fec_creacion  = date('Y-m-d');
            $subgrupo->nick_creacion = $this->user->nick;

            if ($valorsub == strlen($codigo)) {
                $subgrupo->idpadre = $this->ultimo_grupo->id;
            } else {
                /// usamos ultimo_subgrupo para ahorrar comprobaciones
                if ($this->ultimo_subgrupo) {
                    if (strlen($this->ultimo_subgrupo->codigo) >= strlen($subgrupo->codigo)) {
                        /// pero si el nuevo no tiene mayor longitud, mejor descartamos para buscar uno nuevo
                        $this->ultimo_subgrupo = null;
                    }
                }

                if (!$this->ultimo_subgrupo and strlen($codigo) > 1) {
                    /// buscamos un padre
                    $this->ultimo_subgrupo = $this->plancuentas->get_by_codigo($this->idempresa, $this->idejercicio, substr($subgrupo->codigo, 0, -1));
                }

                if (!$this->ultimo_subgrupo and strlen($codigo) > 2) {
                    /// buscamos un padre
                    $this->ultimo_subgrupo = $this->plancuentas->get_by_codigo($this->idempresa, $this->idejercicio, substr($subgrupo->codigo, 0, -2));
                }

                if (!$this->ultimo_subgrupo and strlen($codigo) > 3) {
                    /// buscamos un padre
                    $this->ultimo_subgrupo = $this->plancuentas->get_by_codigo($this->idempresa, $this->idejercicio, substr($subgrupo->codigo, 0, -3));
                }

                if ($this->ultimo_subgrupo) {
                    if (strlen($this->ultimo_subgrupo->codigo) < strlen($subgrupo->codigo)) {
                        /// asignamos el padre
                        $subgrupo->idpadre = $this->ultimo_subgrupo->id;
                    }
                }
            }

            if ($subgrupo->save()) {
                $this->ultimo_subgrupo = $subgrupo;
                return true;
            } else {
                $this->new_error_msg('Error al guardar el Subgrupo: ' . $subgrupo->codigo);
                return false;
            }
        }
    }

    private function crear_cuenta($codigo, $nombre)
    {
        $cuenta = $this->plancuentas->get_by_codigo($this->idempresa, $this->idejercicio, $codigo);
        if ($cuenta) {
            /// ya existe devolvemos el que existe
            $this->ultima_cuenta = $cuenta;
            return true;
        } else {
            //No existe lo creo
            $cuenta                = new plancuentas();
            $cuenta->idempresa     = $this->idempresa;
            $cuenta->idejercicio   = $this->idejercicio;
            $cuenta->tipo          = 'C';
            $cuenta->codigo        = $codigo;
            $cuenta->nombre        = $nombre;
            $cuenta->fec_creacion  = date('Y-m-d');
            $cuenta->nick_creacion = $this->user->nick;

            /// usamos ultimo_subgrupo para ahorrar comprobaciones
            if ($this->ultimo_subgrupo) {
                if (strlen($this->ultimo_subgrupo->codigo) >= strlen($cuenta->codigo)) {
                    /// pero si el nuevo no tiene mayor longitud, mejor descartamos para buscar uno nuevo
                    $this->ultimo_subgrupo = null;
                }
            }

            if (!$this->ultimo_subgrupo and strlen($codigo) > 1) {
                /// buscamos un padre
                $this->ultimo_subgrupo = $this->plancuentas->get_by_codigo($this->idempresa, $this->idejercicio, substr($cuenta->codigo, 0, -1));
            }

            if (!$this->ultimo_subgrupo and strlen($codigo) > 2) {
                /// buscamos un padre
                $this->ultimo_subgrupo = $this->plancuentas->get_by_codigo($this->idempresa, $this->idejercicio, substr($cuenta->codigo, 0, -2));
            }

            if (!$this->ultimo_subgrupo and strlen($codigo) > 3) {
                /// buscamos un padre
                $this->ultimo_subgrupo = $this->plancuentas->get_by_codigo($this->idempresa, $this->idejercicio, substr($cuenta->codigo, 0, -3));
            }

            if ($this->ultimo_subgrupo) {
                if (strlen($this->ultimo_subgrupo->codigo) < strlen($cuenta->codigo)) {
                    /// asignamos el padre
                    $cuenta->idpadre = $this->ultimo_subgrupo->id;
                }
            }

            if ($cuenta->save()) {
                $this->ultima_cuenta = $cuenta;
                return true;
            } else {
                $this->new_error_msg('Error al guardar la Cuenta: ' . $cuenta->codigo);
                return false;
            }
        }
    }

    private function crear_subcuenta($codigo, $nombre)
    {
        $subcuenta = $this->plancuentas->get_by_codigo($this->idempresa, $this->idejercicio, $codigo);
        if ($subcuenta) {
            /// ya existe devolvemos el que existe
            return true;
        } else {
            //No existe lo creo
            $subcuenta                = new plancuentas();
            $subcuenta->idempresa     = $this->idempresa;
            $subcuenta->idejercicio   = $this->idejercicio;
            $subcuenta->tipo          = 'SC';
            $subcuenta->codigo        = $codigo;
            $subcuenta->nombre        = $nombre;
            $subcuenta->fec_creacion  = date('Y-m-d');
            $subcuenta->nick_creacion = $this->user->nick;

            /// usamos ultima_cuenta para ahorrar comprobaciones
            if ($this->ultima_cuenta) {
                $subcuenta->idpadre = $this->ultima_cuenta->id;
            } else {
                $this->new_error_msg('Cuenta no encontrada para la subcuenta ' . $codigo);
                return false;
            }

            if ($subcuenta->save()) {
                return true;
            } else {
                $this->new_error_msg('Error al guardar la Subcuenta: ' . $subcuenta->codigo);
                return false;
            }
        }
    }
}
