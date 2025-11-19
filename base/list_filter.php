<?php
/**
 * Description of list_filter
 *
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
abstract class list_filter
{
    /**
     *
     * @var string
     */
    public $col_name;

    /**
     *
     * @var string
     */
    public $label;

    /**
     *
     * @var mixed
     */
    public $value;

    abstract public function get_where();

    abstract public function show();

    /**
     *
     * @param string $col_name
     * @param string $label
     */
    public function __construct($col_name, $label)
    {
        $this->col_name = $col_name;
        $this->label    = $label;
    }

    /**
     *
     * @return string
     */
    public function name()
    {
        return 'filter_' . $this->col_name;
    }
}
