<?php
require_once 'base/list_filter.php';

/**
 * Description of list_filter_date
 *
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class list_filter_date extends list_filter
{
    /**
     *
     * @var string
     */
    protected $operation;

    /**
     * 
     * @param string $col_name
     * @param string $label
     * @param string $operation
     */
    public function __construct($col_name, $label, $operation)
    {
        parent::__construct($col_name, $label);
        $this->operation = $operation;
    }

    /**
     * 
     * @return string
     */
    public function get_where()
    {
        /// necesitamos un modelo, el que sea, para llamar a su función var2str()
        $log = new log();
        return $this->value ? ' AND ' . $this->col_name . ' ' . $this->operation . ' ' . $log->var2str($this->value) : '';
    }

    /**
     * 
     * @return string
     */
    public function name()
    {
        switch ($this->operation) {
            case '>':
            case '>=':
                return parent::name() . '_gt';

            case '<':
            case '<=':
                return parent::name() . '_lt';

            default:
                return parent::name();
        }
    }

    /**
     * 
     * @return string
     */
    public function show()
    {
        return '<div class="form-group"><input type="text" name="' . $this->name()
            . '" value="' . $this->value . '" class="form-control datepicker" placeholder="' . $this->label . '" autocomplete="off" onchange="this.form.submit()">'
            . '</div>';
    }
}
