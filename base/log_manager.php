<?php
/**
 * Description of log_manager
 *
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class log_manager
{
    /**
     *
     * @var core_log
     */
    private $core_log;

    public function __construct()
    {
        $this->core_log = new core_log();
    }

    public function save()
    {
        foreach ($this->core_log->get_to_save() as $data) {
            $new_log              = new log();
            $new_log->alerta      = $data['context']['alert'];
            $new_log->controlador = $this->core_log->controller_name();
            $new_log->detalle     = $data['message'];
            $new_log->fecha       = date('d-m-Y H:i:s', $data['time']);
            $new_log->ip          = get_ip();
            $new_log->tipo        = $data['context']['type'];
            $new_log->usuario     = $this->core_log->user_nick();
            $new_log->save();
        }
    }
}
