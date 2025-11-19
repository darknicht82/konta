<?php
echo 'Iniciando cron...';

/// establecemos el límite de ejecución de PHP en 50 minutos
@set_time_limit(3000);

/// accedemos al directorio
chdir(__DIR__);
define('JG_FOLDER', __DIR__);

/// cargamos las constantes de configuración
require_once 'config.php';
require_once 'base/config2.php';

$tiempo = explode(' ', microtime());
$uptime = $tiempo[1] + $tiempo[0];

require_once 'base/core_log.php';
require_once 'base/db2.php';
$core_log = new core_log();
$db = new db2();

require_once 'base/default_items.php';

require_once 'base/extended_model.php';
require_once 'base/log_manager.php';
require_all_models();

if ($db->connect()) {
    $fsvar = new var();
    $cron_vars = $fsvar->array_get(array('cron_exists' => FALSE, 'cron_lock' => FALSE, 'cron_error' => FALSE));

    if ($cron_vars['cron_lock']) {
        echo "\nERROR: Ya hay un cron en ejecución. Si crees que es un error,"
        . " ve a Admin > Información del sistema para solucionar el problema.";

        /// marcamos el error en el cron
        $cron_vars['cron_error'] = 'TRUE';
    } else {
        /**
         * He detectado que a veces, con el plugin kiwimaru,
         * el proceso cron tarda más de una hora, y por tanto se encadenan varios
         * procesos a la vez. Para evitar esto, uso la entrada cron_lock.
         * Además uso la entrada cron_exists para marcar que alguna vez se ha ejecutado el cron,
         * y cron_error por si hubiese algún fallo.
         */
        $cron_vars['cron_lock'] = 'TRUE';
        $cron_vars['cron_exists'] = 'TRUE';

        /// guardamos las variables
        $fsvar->array_save($cron_vars);

        /// indicamos el inicio en el log
        $core_log->save('Ejecutando el cron...', 'cron');

        /// establecemos los elementos por defecto
        $default_items = new default_items();
        $empresa = new empresa();
        $default_items->set_codalmacen($empresa->codalmacen);
        $default_items->set_coddivisa($empresa->coddivisa);
        $default_items->set_codejercicio($empresa->codejercicio);
        $default_items->set_codpago($empresa->codpago);
        $default_items->set_codpais($empresa->codpais);
        $default_items->set_codserie($empresa->codserie);

        /*
         * Ahora ejecutamos el cron de cada plugin que tenga cron y esté activado
         */
        foreach ($GLOBALS['plugins'] as $plugin) {
            if (file_exists('plugins/' . $plugin . '/cron.php')) {
                echo "\n***********************\nEjecutamos el cron.php del plugin " . $plugin . "\n";

                include 'plugins/' . $plugin . '/cron.php';

                echo "\n***********************";
            }
        }

        /// indicamos el fin en el log
        $core_log->save('Terminada la ejecución del cron.', 'cron');

        /// Eliminamos la variable cron_lock puesto que ya hemos terminado
        $cron_vars['cron_lock'] = FALSE;
    }

    /// guardamos las variables
    $fsvar->array_save($cron_vars);

    /// mostramos el errores que se hayan podido producir
    foreach ($core_log->get_errors() as $err) {
        echo "\nERROR: " . $err . "\n";
    }

    /// guardamos los errores en el log
    $log_manager = new log_manager();
    $log_manager->save();

    $db->close();
} else {
    echo "¡Imposible conectar a la base de datos!\n";
    foreach ($core_log->get_errors() as $err) {
        echo $err . "\n";
    }
}

$tiempo2 = explode(' ', microtime());
echo "\nTiempo de ejecución: " . number_format($tiempo2[1] + $tiempo2[0] - $uptime, 3) . " s\n";
