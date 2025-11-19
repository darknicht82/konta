<?php

require_once 'base/list_filter.php';

/**
 * Description of list_filter_checkbox
 *
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class list_filter_checkbox extends list_filter
{

    /**
     *
     * @var bool
     */
    protected $match_value;

    /**
     *
     * @var string
     */
    protected $operation;

    public function __construct($col_name, $label, $operation = '=', $match_value = true)
    {
        parent::__construct($col_name, $label);
        $this->match_value = $match_value;
        $this->operation   = $operation;
    }

    /**
     *
     * @return string
     */
    public function get_where()
    {
        /// necesitamos un modelo, el que sea, para llamar a su función var2str()
        $log = new log();
        return $this->value ? ' AND ' . $this->col_name . ' ' . $this->operation . ' ' . $log->var2str($this->match_value) : '';
    }

    /**
     *
     * @return string
     */
    public function show()
    {
        $checked = $this->value ? ' checked=""' : '';
        return '<div class="checkbox">'
        . '<label><input type="checkbox" name="' . $this->name() . '" value="TRUE" ' . $checked
        . ' onchange="this.form.submit()">' . $this->label . '</label>'
            . '</div>';
    }
}
