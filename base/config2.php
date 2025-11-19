<?php

foreach (['JG_TMP_NAME', 'JG_PATH', 'JG_MYDOCS'] as $name) {
    if (!defined($name)) {
        define($name, '');
    }
}

if (JG_TMP_NAME != '' && !file_exists(JG_FOLDER . '/tmp/' . JG_TMP_NAME)) {
    if (!file_exists(JG_FOLDER . '/tmp') && mkdir(JG_FOLDER . '/tmp')) {
        file_put_contents(JG_FOLDER . '/tmp/index.php', "<?php\necho 'ACCESO DENEGADO';");
    }

    mkdir(JG_FOLDER . '/tmp/' . JG_TMP_NAME);
}

$GLOBALS['config2'] = array(
    'zona_horaria' => 'America/Guayaquil',
    'nf0' => 2,
    'nf0_art' => 5,
    'nf1' => ',',
    'nf2' => ' ',
    'pos_divisa' => 'right',
    'factura' => 'factura',
    'facturas' => 'facturas',
    'ndcredito' => 'nota de credito',
    'presupuesto' => 'presupuesto',
    'presupuestos' => 'presupuestos',
);

foreach ($GLOBALS['config2'] as $i => $value) {
    define('JG_' . strtoupper($i), $value);
}

if (!file_exists('plugins')) {
    mkdir('plugins');
    chmod('plugins', octdec(777));
}

/// Cargamos la lista de plugins activos
$GLOBALS['plugins'] = [];
if (file_exists(JG_FOLDER . '/tmp/' . JG_TMP_NAME . 'enabled_plugins.list')) {
    $list = explode(',', file_get_contents(JG_FOLDER . '/tmp/' . JG_TMP_NAME . 'enabled_plugins.list'));
    if (!empty($list)) {
        foreach ($list as $f) {
            if (file_exists('plugins/' . $f)) {
                $GLOBALS['plugins'][] = $f;
            }
        }
    }
}

/// cargamos las funciones de los plugins
foreach ($GLOBALS['plugins'] as $plug) {
    if (file_exists(JG_FOLDER . '/plugins/' . $plug . '/functions.php')) {
        require_once 'plugins/' . $plug . '/functions.php';
    }
}