<?php
require_once 'base/list_filter.php';

/**
 * Description of list_filter_select
 *
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class list_filter_select extends list_filter
{
    /**
     *
     * @var array
     */
    protected $values;

    /**
     * 
     * @param string $col_name
     * @param string $label
     * @param array  $values
     */
    public function __construct($col_name, $label, $values)
    {
        parent::__construct($col_name, $label);
        $this->values = $values;
    }

    public function get_where()
    {
        /// necesitamos un modelo, el que sea, para llamar a su función var2str()
        $log = new log();
        return empty($this->value) ? '' : ' AND ' . $this->col_name . ' = ' . $log->var2str($this->value);
    }

    /**
     * 
     * @return string
     */
    public function show()
    {
        $html = '<div class="form-group">'
            . '<select class="form-control" name="' . $this->name() . '" onchange="this.form.submit()">'
            . '<option value="">Cualquier ' . $this->label . '</option>'
            . '<option value="">-----</option>';

        foreach ($this->values as $key => $value) {
            if ($key === $this->value) {
                $html .= '<option value="' . $key . '" selected="">' . $value . '</option>';
            } else {
                $html .= '<option value="' . $key . '">' . $value . '</option>';
            }
        }

        $html .= '</select></div>';
        return $html;
    }
}
