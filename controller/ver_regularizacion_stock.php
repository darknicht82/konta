<?php
/**
 * Controlador de Regularizaciones -> Ver Regularizacion de Stock.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_regularizacion_stock extends controller
{
    //variables
    public $regularizacion;
    public $allow_delete;
    public $allow_modify;
    public $impresion;
    //modelos
    public $regularizaciones;
    public $lineasregularizaciones;
    public $articulos;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ver Regularizacion', 'Inventario', false, false);
    }

    protected function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modify = $this->user->allow_modify_on(__CLASS__);

        $this->impresion = $this->user->have_access_to('impresion_docs');

        $this->regularizaciones       = new regularizaciones();
        $this->lineasregularizaciones = new lineasregularizaciones();
        $this->articulos              = new articulos();

        $this->regularizacion = false;
        if (isset($_GET['id'])) {
            $this->regularizacion = $this->regularizaciones->get($_GET['id']);
            if (!$this->regularizacion) {
                $this->new_advice("No se encuentra la regularizacion seleccionado.");
                return;
            } else if ($this->regularizacion->idempresa != $this->empresa->idempresa) {
                $this->regularizacion = false;
                $this->new_advice("El documento no esta disponible para su empresa.");
                return;
            }
        }

        if (isset($_GET['actdetreg'])) {
            $this->buscar_detalle_regularizacion();
        } else if (isset($_POST['eliminar_linea'])) {
            $this->eliminar_linea();
        }
    }

    private function buscar_detalle_regularizacion()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'No existe la Regularizacion.');
        if ($this->regularizacion) {
            $lineas = $this->lineasregularizaciones->all_by_idregularizacion($this->regularizacion->idregularizacion);
            $result = array('error' => 'T', 'msj' => 'Sin Detalle.');
            if ($lineas) {
                $result = array('error' => 'F', 'msj' => '', 'regularizacion' => $this->regularizacion, 'lineas' => $lineas);
            }
        }

        echo json_encode($result);
        exit;
    }

    private function eliminar_linea()
    {
        $this->template = false;
        $result         = array('error' => 'T', 'msj' => 'Regularizacion No Encontrada.');
        if ($this->regularizacion) {
            $linea = $this->lineasregularizaciones->get($_POST['eliminar_linea']);
            if ($linea) {
                if ($linea->delete()) {
                    $this->regularizacion->total -= $linea->costototal;

                    if ($this->regularizacion->save()) {
                        $result = array('error' => 'F', 'msj' => '');
                    } else {
                        $result = array('error' => 'T', 'msj' => 'Error al actualizar los totales del Regularizacion.');
                        $linea->save();
                    }
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al Eliminar la linea del regularizacion.');
                    if ($linea->get_errors()) {
                        foreach ($linea->get_errors() as $key => $val) {
                            $result['msj'] .= "\n" . $val;
                        }
                    }
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'Linea de Regularizacion no Encontrada.');
            }
        }
        echo json_encode($result);
        exit;
    }
}
