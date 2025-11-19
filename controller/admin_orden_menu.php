<?php
/**
 * Description Ordenar menú
 *
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class admin_orden_menu extends controller
{

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ordenar menú', 'Administrador', FALSE, TRUE, FALSE, 'bi bi-menu-down');
    }

    protected function private_core()
    {
        if (filter_input(INPUT_POST, 'guardar')) {
            $this->guardar_orden();
        }
    }

    private function guardar_orden()
    {
        foreach ($this->folders() as $folder) {
            $orden = 0;
            foreach (filter_input_array(INPUT_POST) as $key => $value) {
                if (strlen($key) > $folder) {
                    if (substr($key, 0, strlen($folder)) == $folder) {
                        $page = $this->page->get($value);
                        $page->orden = $orden;
                        if ($page->save()) {
                            $orden++;
                        }
                    }
                }
            }
        }

        $this->new_message('Datos guardados.');
        $this->menu = $this->user->get_menu(TRUE);
    }
}
