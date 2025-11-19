<?php
if ((float) substr(phpversion(), 0, 3) < 5.4) {
    /// comprobamos la versión de PHP
    die('Se necesita PHP 5.4 o superior, y usted tiene PHP ' . phpversion());
}

if (!file_exists('config.php')) {
    /// si no hay config.php redirigimos al instalador
    header('Location: install.php');
    die('Redireccionando al instalador...');
}

define('JG_FOLDER', __DIR__);

/// ampliamos el límite de ejecución de PHP a 5 minutos
@set_time_limit(300);

/// cargamos las constantes de configuración
require_once 'config.php';
require_once 'base/config2.php';
require_once 'base/controller.php';
require_once 'base/edit_controller.php';
require_once 'base/list_controller.php';
require_once 'base/log_manager.php';
require_once 'raintpl/rain.tpl.class.php';

/**
 * Registramos la función para capturar los fatal error.
 * Información importante a la hora de depurar errores.
 */
register_shutdown_function("fatal_handler");

/// ¿Qué controlador usar?
$pagename = '';
if (filter_input(INPUT_GET, 'page')) {
    $pagename = filter_input(INPUT_GET, 'page');
} elseif (defined('JG_HOMEPAGE')) {
    $pagename = JG_HOMEPAGE;
}

$gsc_error = FALSE;
if ($pagename == '') {
    $gsc = new controller();
} else {
    $class_path = find_controller($pagename);
    require_once $class_path;

    try {
        /// ¿No se ha encontrado el controlador?
        if ('base/controller.php' === $class_path) {
            header("HTTP/1.0 404 Not Found");
            $gsc = new controller();
        } else {
            $gsc = new $pagename();
        }
    } catch (Exception $exc) {
        echo "<h1>Error sin Controlar</h1>"
        . "<ul>"
        . "<li><b>Código:</b> " . $exc->getCode() . "</li>"
        . "<li><b>Mensage:</b> " . $exc->getMessage() . "</li>"
        . "</ul>";
        $gsc_error = TRUE;
    }
}

if (is_null(filter_input(INPUT_GET, 'page'))) {
    /// redireccionamos a la página definida por el usuario
    $gsc->select_default_page();
}

if ($gsc_error) {
    die();
}

if ($gsc->template) {
    /// configuramos rain.tpl
    raintpl::configure('base_url', NULL);
    raintpl::configure('tpl_dir', 'view/');
    raintpl::configure('path_replace', FALSE);

    /// ¿Se puede escribir sobre la carpeta temporal?
    if (is_writable('tmp')) {
        raintpl::configure('cache_dir', 'tmp/' . JG_TMP_NAME);
    } else {
        echo '<center>'
        . '<h1>No se puede escribir sobre la carpeta temporal</h1>'
        . '</center>';
        die();
    }

    $tpl = new RainTPL();
    $tpl->assign('gsc', $gsc);

    if (filter_input(INPUT_POST, 'user')) {
        $tpl->assign('nlogin', filter_input(INPUT_POST, 'user'));
    } elseif (filter_input(INPUT_COOKIE, 'user')) {
        $tpl->assign('nlogin', filter_input(INPUT_COOKIE, 'user'));
    } else {
        $tpl->assign('nlogin', '');
    }

    $tpl->draw($gsc->template);
}

/// guardamos los errores en el log
$log_manager = new log_manager();
$log_manager->save();

/// cerramos las conexiones
$gsc->close();
