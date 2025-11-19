<?php

error_reporting(E_ALL);

$errors  = [];
$errors2 = [];
$db_type = 'MYSQL';
$db_host = 'localhost';
$db_port = '3306';
$db_name = 'gsc';
$db_user = 'root';

function guarda_config(&$errors, $nombre_archivo = 'config.php')
{
    $archivo = fopen($nombre_archivo, "w");
    if ($archivo) {
        fwrite($archivo, "<?php\n");

        $fields = ['DB_TYPE', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'CACHE_HOST', 'CACHE_PORT', 'CACHE_PREFIX'];
        foreach ($fields as $name) {
            fwrite($archivo, "define('JG_" . $name . "', '" . filter_input(INPUT_POST, strtolower($name)) . "');\n");
        }

        if (filter_input(INPUT_POST, 'db_type') == 'MYSQL' && filter_input(INPUT_POST, 'mysql_socket') != '') {
            fwrite($archivo, "ini_set('mysqli.default_socket', '" . filter_input(INPUT_POST, 'mysql_socket') . "');\n");
        }

        fwrite($archivo, "define('JG_TMP_NAME', '" . random_string(20) . "/');\n");
        fwrite($archivo, "define('JG_COOKIES_EXPIRE', 604800);\n");
        fwrite($archivo, "define('JG_ITEM_LIMIT', 200);\n");

        $fieldsFalse = ['DB_HISTORY', 'DEMO', 'DISABLE_MOD_PLUGINS', 'DISABLE_ADD_PLUGINS', 'DISABLE_RM_PLUGINS'];
        foreach ($fieldsFalse as $name) {
            fwrite($archivo, "define('JG_" . $name . "', FALSE);\n");
        }

        if (filter_input(INPUT_POST, 'proxy_type')) {
            fwrite($archivo, "define('JG_PROXY_TYPE', '" . filter_input(INPUT_POST, 'proxy_type') . "');\n");
            fwrite($archivo, "define('JG_PROXY_HOST', '" . filter_input(INPUT_POST, 'proxy_host') . "');\n");
            fwrite($archivo, "define('JG_PROXY_PORT', '" . filter_input(INPUT_POST, 'proxy_port') . "');\n");
        }

        fclose($archivo);

        header("Location: index.php");
        exit();
    }

    $errors[] = "permisos";
}

function test_mysql(&$errors, &$errors2)
{
    if (!class_exists('mysqli')) {
        $errors[]  = "db_mysql";
        $errors2[] = 'No tienes instalada la extensión de PHP para MySQL.';
        return;
    }

    if (filter_input(INPUT_POST, 'mysql_socket') != '') {
        ini_set('mysqli.default_socket', filter_input(INPUT_POST, 'mysql_socket'));
    }

    // Omitimos el valor del nombre de la BD porque lo comprobaremos más tarde
    $connection = @new mysqli(
        filter_input(INPUT_POST, 'db_host'), filter_input(INPUT_POST, 'db_user'), filter_input(INPUT_POST, 'db_pass'), '', intval(filter_input(INPUT_POST, 'db_port'))
    );
    if ($connection->connect_error) {
        $errors[]  = "db_mysql";
        $errors2[] = $connection->connect_error;
        return;
    }

    // Comprobamos que la BD exista, de lo contrario la creamos
    $db_selected = mysqli_select_db($connection, filter_input(INPUT_POST, 'db_name'));
    if ($db_selected) {
        guarda_config($errors);
        return;
    }

    $sqlCrearBD = "CREATE DATABASE `" . filter_input(INPUT_POST, 'db_name') . "`;";
    if (mysqli_query($connection, $sqlCrearBD)) {
        guarda_config($errors);
        return;
    }

    $errors[]  = "db_mysql";
    $errors2[] = mysqli_error($connection);
}

function test_postgresql(&$errors, &$errors2)
{
    if (!function_exists('pg_connect')) {
        $errors[]  = "db_postgresql";
        $errors2[] = 'No tienes instalada la extensión de PHP para PostgreSQL.';
        return;
    }

    $connection = @pg_connect('host=' . filter_input(INPUT_POST, 'db_host')
        . ' port=' . filter_input(INPUT_POST, 'db_port')
        . ' user=' . filter_input(INPUT_POST, 'db_user')
        . ' password=' . filter_input(INPUT_POST, 'db_pass'));
    
    if (!$connection) {
        $connection = @pg_connect('host=' . filter_input(INPUT_POST, 'db_host')
        . ' port=' . filter_input(INPUT_POST, 'db_port')
        . ' dbname=' . filter_input(INPUT_POST, 'db_name')
        . ' user=' . filter_input(INPUT_POST, 'db_user')
        . ' password=' . filter_input(INPUT_POST, 'db_pass'));
    }

    if (!$connection) {
        $errors[]  = "db_postgresql";
        $errors2[] = 'No se puede conectar a la base de datos. Revisa los datos de usuario y contraseña.';
        return;
    }

    // Comprobamos que la BD exista, de lo contrario la creamos
    $connection2 = @pg_connect('host=' . filter_input(INPUT_POST, 'db_host') . ' port=' . filter_input(INPUT_POST, 'db_port') . ' dbname=' . filter_input(INPUT_POST, 'db_name')
        . ' user=' . filter_input(INPUT_POST, 'db_user') . ' password=' . filter_input(INPUT_POST, 'db_pass'));

    if ($connection2) {
        guarda_config($errors);
        return;
    }

    $sqlCrearBD = 'CREATE DATABASE "' . filter_input(INPUT_POST, 'db_name') . '";';
    if (pg_query($connection, $sqlCrearBD)) {
        guarda_config($errors);
        return;
    }

    $errors[]  = "db_postgresql";
    $errors2[] = 'Error al crear la base de datos.';
}

function random_string($length = 20)
{
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}
/**
 * Buscamos errores
 */
if (file_exists('config.php')) {
    header('Location: index.php');
} else if (floatval(substr(phpversion(), 0, 3)) < 5.4) {
    $errors[] = 'php';
} else if (floatval('3,1') >= floatval('3.1')) {
    $errors[]  = "floatval";
    $errors2[] = 'El separador de decimales de esta versión de PHP no es el punto,'
        . ' como sucede en las instalaciones estándar. Debes corregirlo.';
} else if (!function_exists('mb_substr')) {
    $errors[] = "mb_substr";
} else if (!extension_loaded('simplexml')) {
    $errors[]  = "simplexml";
    $errors2[] = 'No se encuentra la extensión simplexml en tu instalación de PHP.'
        . ' Debes instalarla o activarla.';
    $errors2[] = 'Linux: instala el paquete <b>php-xml</b> y reinicia el Apache.';
} else if (!extension_loaded('openssl')) {
    $errors[] = "openssl";
} else if (!extension_loaded('zip')) {
    $errors[] = "ziparchive";
} else if (!is_writable(__DIR__)) {
    $errors[] = "permisos";
} else if (filter_input(INPUT_POST, 'db_type')) {
    if (filter_input(INPUT_POST, 'db_type') == 'MYSQL') {
        test_mysql($errors, $errors2);
    } else if (filter_input(INPUT_POST, 'db_type') == 'POSTGRESQL') {
        test_postgresql($errors, $errors2);
    }

    $db_type = filter_input(INPUT_POST, 'db_type');
    $db_host = filter_input(INPUT_POST, 'db_host');
    $db_port = filter_input(INPUT_POST, 'db_port');
    $db_name = filter_input(INPUT_POST, 'db_name');
    $db_user = filter_input(INPUT_POST, 'db_user');
}

$system_info = 'GSC_Systems: ' . file_get_contents('VERSION') . "\n";
$system_info .= 'os: ' . php_uname() . "\n";
$system_info .= 'php: ' . phpversion() . "\n";

if (isset($_SERVER['REQUEST_URI'])) {
    $system_info .= 'url: ' . $_SERVER['REQUEST_URI'] . "\n------";
}
foreach ($errors as $e) {
    $system_info .= "\n" . $e;
}

$system_info = str_replace('"', "'", $system_info);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="es" xml:lang="es" >
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>GSC_Systems</title>
        <meta name="description" content="GSC_Systems"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="shortcut icon" href="view/img/favicon.ico" />
        <!--Cargo CSS-->
        <link rel="stylesheet" href="view/css/lib/simplebar.css"/>
        <link rel="stylesheet" href="view/css/lib/perfect-scrollbar.css"/>
        <link rel="stylesheet" href="view/css/lib/metisMenu.min.css"/>
        <link rel="stylesheet" href="view/css/lib/bootstrap.min.css"/>
        <link rel="stylesheet" href="view/css/lib/bootstrap-extended.css"/>
        <link rel="stylesheet" href="view/css/lib/style.css"/>
        <link rel="stylesheet" href="view/css/lib/icons.css"/>
        <link rel="stylesheet" href="view/css/lib/pace.min.css"/>
        <!--Temas-->
        <link rel="stylesheet" href="view/css/lib/dark-theme.css"/>
        <link rel="stylesheet" href="view/css/lib/light-theme.css"/>
        <link rel="stylesheet" href="view/css/lib/semi-dark.css"/>
        <link rel="stylesheet" href="view/css/lib/header-colors.css"/>
    
        <!--Cargas de Internet-->
        <!--link href="https://fonts.googleapis.com/css/lib2?family=Roboto:wght@400;500&display=swap" rel="stylesheet"-->
        <link rel="stylesheet" href="{#JG_PATH#}view/css/lib/bootstrap-icons.css"/>
    
        <!--Cargo JS-->
        <script src="view/js/lib/bootstrap.bundle.min.js"></script>
        <script src="view/js/lib/jquery.min.js"></script>
        <script src="view/js/lib/pace.min.js"></script>
        <script src="view/js/lib/simplebar.min.js"></script>
        <script src="view/js/lib/metisMenu.min.js"></script>
        <script src="view/js/lib/perfect-scrollbar.js"></script>
    </head>
    <body class="pace-done">
        <div class="pace pace-inactive">
            <div class="pace-progress" data-progress-text="100%" data-progress="99" style="transform: translate3d(100%, 0px, 0px);">
                <div class="pace-progress-inner"></div>
            </div>
            <div class="pace-activity"></div>
        </div>
        <script type="text/javascript">
            function change_db_type() {
                if (document.f_configuracion_inicial.db_type.value == 'POSTGRESQL') {
                    document.f_configuracion_inicial.db_port.value = '5432';
                    document.f_configuracion_inicial.db_user.value = 'postgres';
                } else {
                    document.f_configuracion_inicial.db_port.value = '3306';
                    document.f_configuracion_inicial.db_user.value = 'root';
                }
            }
            $(document).ready(function () {
                $("#f_configuracion_inicial").validate({
                    rules: {
                        db_type: {required: false},
                        db_host: {required: true, minlength: 2},
                        db_port: {required: true, minlength: 2},
                        db_name: {required: true, minlength: 2},
                        db_user: {required: true, minlength: 2},
                        db_pass: {required: false}
                    },
                    messages: {
                        db_host: {
                            required: "El campo es obligatorio.",
                            minlength: $.validator.format("Requiere mínimo {0} carácteres!")
                        },
                        db_port: {
                            required: "El campo es obligatorio.",
                            minlength: $.validator.format("Requiere mínimo {0} carácteres!")
                        },
                        db_name: {
                            required: "El campo es obligatorio.",
                            minlength: $.validator.format("Requiere mínimo {0} carácteres!")
                        },
                        db_user: {
                            required: "El campo es obligatorio.",
                            minlength: $.validator.format("Requiere mínimo {0} carácteres!")
                        }
                    }
                });
            });
        </script>
        <div class="wrapper">
            <main class="page-content">
                <div class="card">
                    <div class="card-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="page-header">
                                        <h1>
                                            <i class="fa fa-cogs" aria-hidden="true"></i>
                                            Instalador de <b>GSC_Systems</b>
                                            <small><?php echo file_get_contents('VERSION'); ?></small>
                                        </h1>
                                    </div>
                                </div>
                                <hr>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <?php
                                        foreach ($errors as $err) {
                                            if ($err == 'permisos') {
                                    ?>
                                        <div class="panel panel-danger">
                                            <div class="panel-heading">
                                                Permisos de escritura:
                                            </div>
                                            <div class="panel-body">
                                                <p>
                                                    La carpeta no dispone permisos de escritura.
                                                </p>
                                                <h3>
                                                    <i class="fa fa-linux" aria-hidden="true"></i> Linux
                                                </h3>
                                                <pre>sudo chmod -R o+w <?php echo dirname(__FILE__); ?></pre>
                                            </div>
                                    <?php
                                        } else if ($err == 'php') {
                                    ?>
                                        <div class="panel panel-danger">
                                            <div class="panel-heading">
                                                Versión de PHP obsoleta:
                                            </div>
                                            <div class="panel-body">
                                                <p>
                                                    Se necesita PHP <b>5.4</b> o superior.
                                                    Tú estás usando la versión <b><?php echo phpversion() ?></b>.
                                                </p>
                                            </div>
                                        </div>
                                    <?php
                                        } else if ($err == 'mb_substr') {

                                    ?>
                                        <div class="panel panel-danger">
                                            <div class="panel-heading">
                                                No se encuentra la función mb_substr():
                                            </div>
                                            <div class="panel-body">
                                                <p>
                                                    Se necesita la extensión mbstring para poder trabajar con caracteres
                                                    no europeos (chinos, coreanos, japonenes y rusos).
                                                </p>
                                                <h3>
                                                    <i class="fa fa-linux" aria-hidden="true"></i> Linux
                                                </h3>
                                                <p class="help-block">
                                                    Instala el paquete <b>php-mbstring</b> y reinicia el Apache.
                                                </p>
                                            </div>
                                        </div>
                                    <?php
                                        } else if ($err == 'openssl') {
                                    ?>
                                        <div class="panel panel-danger">
                                            <div class="panel-heading">
                                                No se encuentra la extensión OpenSSL:
                                            </div>
                                            <div class="panel-body">
                                                <p>
                                                    Se necesita la extensión OpenSSL para poder enviar emails.
                                                </p>                                    
                                            </div>
                                        </div>
                                    <?php
                                        } else if ($err == 'ziparchive') {
                                    ?>
                                        <div class="panel panel-danger">
                                            <div class="panel-heading">
                                                No se encuentra la extensión ZipArchive:
                                            </div>
                                            <div class="panel-body">
                                                <p>
                                                    Se necesita la extensión ZipArchive para poder
                                                    descomprimir archivos.
                                                </p>
                                            </div>
                                        </div>
                                    <?php
                                        } else if ($err == 'db_mysql') {
                                    ?>
                                        <div class="panel panel-danger">
                                            <div class="panel-heading">
                                                Acceso a base de datos MySQL:
                                            </div>
                                            <div class="panel-body">
                                                <ul>
                                                    <?php
                                                        foreach ($errors2 as $err2) {
                                                            echo "<li>" . $err2 . "</li>";
                                                        }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php
                                        } else if ($err == 'db_postgresql') {
                                    ?>
                                        <div class="panel panel-danger">
                                            <div class="panel-heading">
                                                Acceso a base de datos PostgreSQL:
                                            </div>
                                            <div class="panel-body">
                                                <ul>
                                                    <?php
                                                        foreach ($errors2 as $err2) {
                                                            echo "<li>" . $err2 . "</li>";
                                                        }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php
                                        } else {
                                    ?>
                                        <div class="panel panel-danger">
                                            <div class="panel-heading">
                                                Error:
                                            </div>
                                            <div class="panel-body">
                                                <ul>
                                                    <?php
                                                        if (!empty($errors2)) {
                                                            foreach ($errors2 as $err2) {
                                                                echo "<li>" . $err2 . "</li>";
                                                            }
                                                        } else {
                                                            echo "<li>Error desconocido.</li>";
                                                        }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php
                                            }
                                        }
                                    ?>
                                </div>
                            </div>
                            <div class="row">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#db" role="tab" aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="tab-icon"><i class="bi bi-table"></i>
                                                </div>
                                                <div class="tab-title">&nbsp;Base de datos</div>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content py-3">
                                    <div class="tab-pane fade active show" id="db" role="tabpanel">
                                        <form class="row g-3" name="f_configuracion_inicial" id="f_configuracion_inicial" action="install.php" role="form" method="post">
                                            <div class="col-md-4">
                                                <label for="db_type" class="col-form-label">Servidor SQL:</label>
                                                <select name="db_type" id="db_type" class="form-select" onchange="change_db_type()">
                                                    <option value="MYSQL"<?php
                                                        if ($db_type == 'MYSQL') {
                                                            echo ' selected=""';
                                                        }
                                                        ?>>MySQL
                                                    </option>
                                                    <option value="POSTGRESQL"<?php
                                                        if ($db_type == 'POSTGRESQL') {
                                                            echo ' selected=""';
                                                        }
                                                        ?>>PostgreSQL
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="db_host" class="col-form-label">Servidor:</label>
                                                <input class="form-control" type="text" name="db_host" id="db_host" value="<?php echo $db_host; ?>" autocomplete="off" required="">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="db_port" class="col-form-label">Puerto:</label>
                                                <input  class="form-control" type="number" id="db_port" name="db_port" value="<?php echo $db_port; ?>" autocomplete="off" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="db_name" class="col-form-label">Nombre base de datos:</label>
                                                <div class="input-group">
                                                    <span class="input-group-text" id="db_name">
                                                        <i class="bi bi-table"></i>
                                                    </span>
                                                    <input class="form-control" type="text" id="db_name" name="db_name" value="<?php echo $db_name; ?>" autocomplete="off"/>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="db_user" class="col-form-label">Usuario:</label>
                                                <div class="input-group">
                                                    <span class="input-group-text" id="db_user">
                                                        <i class="bi bi-file-earmark-person"></i>
                                                    </span>
                                                    <input class="form-control" type="text" id="db_user" name="db_user" value="<?php echo $db_user; ?>" autocomplete="off"/>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="db_pass" class="col-form-label">Contraseña:</label>
                                                <div class="input-group">
                                                    <span class="input-group-text" id="db_pass">
                                                        <i class="bi bi-lock-fill"></i>
                                                    </span>
                                                    <input class="form-control" type="password" name="db_pass" id="db_pass" value="" autocomplete="off"/>
                                                </div>
                                            </div>
                                            <div class="col-12"></div>
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button id="submit_button" class="btn btn-primary px-5 radius-30" type="submit">
                                                        <i class="bi bi-check-circle"></i>&nbsp; Aceptar
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="row" style="margin-bottom: 20px;">
                                    <div class="col-sm-12 text-center">
                                        <hr/>
                                        &COPY; <?php echo date('Y'); ?>  <b>GSC_Systems</b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>