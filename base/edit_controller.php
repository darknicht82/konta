<?php
require_once 'base/edit_form.php';

/**
 * Description of edit_controller
 *
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
abstract class edit_controller extends controller
{
    /**
     * TRUE si el usuario tiene permisos para eliminar en la página.
     *
     * @var boolean 
     */
    public $allow_delete;
    /**
     * TRUE si el usuario tiene permisos para modificar en la página.
     *
     * @var boolean 
     */
    public $allow_modificar;

    /**
     *
     * @var edit_form
     */
    public $form;

    /**
     *
     * @var extended_model
     */
    public $model;

    abstract public function get_model_class_name();

    abstract protected function set_edit_columns();

    /**
     * 
     * @param string $name
     * @param string $title
     * @param string $folder
     */
    public function __construct($name = __CLASS__, $title = 'home', $folder = '')
    {
        parent::__construct($name, $title, $folder, FALSE, FALSE, FALSE);
    }

    /**
     * 
     * @return boolean
     */
    protected function delete_action()
    {
        if (!$this->allow_delete) {
            $this->new_error_msg('No tienes permiso para eliminar en esta página.');
            return false;
        }

        if ($this->model->delete()) {
            $this->new_message('Datos eliminados correctamente.');
            $this->model->clear();
            return true;
        }

        $this->new_error_msg('Error al eliminar los datos.');
        return false;
    }

    /**
     * 
     * @return boolean
     */
    protected function edit_action()
    {
        if (!$this->allow_modificar) {
            $this->new_error_msg('No tienes permiso para modificar en esta página.');
            return false;
        }

        if (isset($_POST['petition_id']) && $this->duplicated_petition($_POST['petition_id'])) {
            $this->new_error_msg('Petición duplicada. Has hecho doble clic sobre el botón y se han enviado dos peticiones.');
            return false;
        }

        /// asignamos valores
        foreach ($this->form->columns as $key => $col_config) {
            $this->process_form_value($key, $col_config['type']);
        }

        if ($this->model->save()) {
            $this->new_message('Datos guardados correctamente.');
            return true;
        }

        $this->new_error_msg('Error al guardar los datos.');
        return false;
    }

    protected function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on($this->class_name);
        /// ¿El usuario tiene permiso para modificar en esta página?
        $this->allow_modificar = $this->user->allow_delete_on($this->class_name);

        $this->form = new edit_form();
        $this->template = 'master/edit_controller';

        /// cargamos el modelo
        $model_class = $this->get_model_class_name();
        $this->model = new $model_class();
        if (isset($_REQUEST['code']) && !empty($_REQUEST['code'])) {
            $this->model->load_from_code($_REQUEST['code']);
        }

        $this->set_edit_columns();

        /// acciones
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        switch ($action) {
            case 'delete':
                $this->delete_action();
                break;

            case 'edit':
                $this->edit_action();
                break;
        }
    }

    /**
     * 
     * @param string $col_name
     * @param string $type
     */
    protected function process_form_value($col_name, $type)
    {
        switch ($type) {
            case 'bool':
                $this->model->{$col_name} = isset($_POST[$col_name]);
                break;

            case 'date':
                $this->model->{$col_name} = empty($_POST[$col_name]) ? null : $_POST[$col_name];
                break;

            default:
                $this->model->{$col_name} = isset($_POST[$col_name]) ? $_POST[$col_name] : $this->model->{$col_name};
        }
    }

    /**
     * 
     * @param string $tabla
     * @param string $columna1
     * @param string $columna2
     *
     * @return array
     */
    protected function sql_distinct($tabla, $columna1, $columna2 = '')
    {
        if (!$this->db->table_exists($tabla)) {
            return [];
        }

        $columna2 = empty($columna2) ? $columna1 : $columna2;
        $final = [];
        $sql = "SELECT DISTINCT " . $columna1 . ", " . $columna2 . " FROM " . $tabla . " ORDER BY " . $columna2 . " ASC;";
        $data = $this->db->select($sql);
        if (!empty($data)) {
            foreach ($data as $d) {
                if ($d[$columna1] != '') {
                    $final[$d[$columna1]] = $d[$columna2];
                }
            }
        }

        return $final;
    }
}
