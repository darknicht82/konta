<?php
require_once 'base/ip_filter.php';

/**
 * Description of login
 *
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class login
{

    private $cache;
    private $core_log;
    private $ip_filter;
    private $user_model;

    public function __construct()
    {
        $this->cache = new cache();
        $this->core_log = new core_log();
        $this->ip_filter = new ip_filter();
        $this->user_model = new users();
    }

    public function change_user_passwd()
    {
        $db_password = filter_input(INPUT_POST, 'db_password');
        $ip = get_ip();
        $nick = filter_input(INPUT_POST, 'user');
        $new_password = filter_input(INPUT_POST, 'new_password');
        $new_password2 = filter_input(INPUT_POST, 'new_password2');

        if ($this->ip_filter->isBanned($ip)) {
            $this->ip_filter->setAttempt($ip);
            $this->core_log->new_error('Tu IP ha sido bloqueada ' . $nick . '. Luego de 5 intentos fallidos. '
                . 'Tendrás que esperar 10 minutos antes de volver a intentar entrar.');
        } else if ($new_password != $new_password2) {
            $this->core_log->new_error('Las contraseñas no coinciden ' . $nick);
        } else if ($new_password == '') {
            $this->core_log->new_error('Tienes que escribir una contraseña nueva ' . $nick);
        } else if ($db_password != JG_DB_PASS) {
            $this->ip_filter->setAttempt($ip);
            $this->core_log->new_error('La contraseña de la base de datos es incorrecta ' . $nick);
        } else {
            $suser = $this->user_model->get($nick);
            if ($suser) {
                $suser->set_password($new_password);
                if ($suser->save()) {
                    $this->core_log->new_message('Contraseña cambiada correctamente ' . $nick);
                } else {
                    $this->core_log->new_error('Imposible cambiar la contraseña del usuario ' . $nick);
                }
            }
        }
    }

    public function log_in(&$controller_user)
    {
        $ip = get_ip();
        $nick = filter_input(INPUT_POST, 'user');
        $password = filter_input(INPUT_POST, 'password');

        if ($this->ip_filter->isBanned($ip)) {
            $this->core_log->new_error('Tu IP ha sido bloqueada. Luego de 5 intentos fallidos. Tendrás que esperar 10 minutos antes de volver a intentar entrar.');
            $this->core_log->save('Tu IP ha sido bloqueada. Luego de 5 intentos fallidos. Tendrás que esperar 10 minutos antes de volver a intentar entrar.', 'login', TRUE);
            return FALSE;
        }

        if ($nick && $password) {
            $user = $this->user_model->get($nick);
            if ($user && $user->activo) {
                /**
                 * En versiones anteriores se guardaban las contraseñas siempre en
                 * minúsculas, por eso, para dar compatibilidad comprobamos también
                 * en minúsculas.
                 */
                if ($user->password == sha1($password) || $user->password == sha1(mb_strtolower($password, 'UTF8'))) {
                    $user->new_logkey();

                    if (!$user->admin && !$this->ip_filter->inWhiteList($ip)) {
                        $this->core_log->new_error('La dirección IP de su red a sido bloqueada para acceder al sistema.');
                        $this->core_log->save('La dirección IP de su red a sido bloqueada para acceder al sistema..', 'login', TRUE);
                    } else if ($user->save()) {
                        $this->save_cookie($user);
                        $controller_user = $user;

                        /// añadimos el mensaje al log
                        $this->core_log->save('Login correcto.', 'login');
                    } else {
                        $this->core_log->new_error('Imposible guardar los datos de usuario.');
                        $this->cache->clean();
                    }
                } else {
                    $this->core_log->new_error('¡Contraseña incorrecta! (' . $nick . ')');
                    $this->core_log->save('¡Contraseña incorrecta! (' . $nick . ')', 'login', TRUE);
                    $this->ip_filter->setAttempt($ip);
                }
            } else if ($user && !$user->activo) {
                $this->core_log->new_error('El usuario ' . $user->nick . ' se encuentra inactivo, contacte con su administrador!');
                $this->core_log->save('El usuario ' . $user->nick . ' se encuentra inactivo, contacte con su administrador!', 'login', TRUE);
                $this->user_model->clean_cache(TRUE);
                $this->cache->clean();
            } else {
                $this->core_log->new_error('El usuario o contraseña no coinciden!');
                $this->user_model->clean_cache(TRUE);
                $this->cache->clean();
            }
        } else if (filter_input(INPUT_COOKIE, 'user') && filter_input(INPUT_COOKIE, 'logkey')) {
            $nick = filter_input(INPUT_COOKIE, 'user');
            $logkey = filter_input(INPUT_COOKIE, 'logkey');

            $user = $this->user_model->get($nick);
            if ($user && $user->activo) {
                if ($user->log_key == $logkey) {
                    $user->logged_on = TRUE;
                    $user->update_login();
                    $this->save_cookie($user);
                    $controller_user = $user;
                } else if (!is_null($user->log_key)) {
                    $this->core_log->new_advice('Se inició sesión desde otro Dispositivo en la IP: '
                        . $user->last_ip . ".");
                    $this->log_out();
                }
            } else {
                $this->core_log->new_error('¡El usuario ' . $nick . ' no existe o está desactivado!');
                $this->log_out(TRUE);
                $this->user_model->clean_cache(TRUE);
                $this->cache->clean();
            }
        }

        return $controller_user->logged_on;
    }
    
    /**
     * Gestiona el cierre de sesión
     * @param boolean $rmuser eliminar la cookie del usuario
     */
    public function log_out($rmuser = FALSE)
    {
        $path = '/';
        if (filter_input(INPUT_SERVER, 'REQUEST_URI')) {
            $aux = parse_url(str_replace('/index.php', '', filter_input(INPUT_SERVER, 'REQUEST_URI')));
            if (isset($aux['path'])) {
                $path = $aux['path'];
                if (substr($path, -1) != '/') {
                    $path .= '/';
                }
            }
        }

        /// borramos las cookies
        if (filter_input(INPUT_COOKIE, 'logkey')) {
            setcookie('logkey', '', time() - JG_COOKIES_EXPIRE);
            setcookie('logkey', '', time() - JG_COOKIES_EXPIRE, $path);
            if ($path != '/') {
                setcookie('logkey', '', time() - JG_COOKIES_EXPIRE, '/');
            }
        }

        /// ¿Eliminamos la cookie del usuario?
        if ($rmuser && filter_input(INPUT_COOKIE, 'user')) {
            setcookie('user', '', time() - JG_COOKIES_EXPIRE);
            setcookie('user', '', time() - JG_COOKIES_EXPIRE, $path);
        }

        /// guardamos el evento en el log
        $this->core_log->save('El usuario ha cerrado la sesión.', 'login');
    }

    private function save_cookie($user)
    {
        setcookie('user', $user->nick, time() + JG_COOKIES_EXPIRE);
        setcookie('logkey', $user->log_key, time() + JG_COOKIES_EXPIRE);
    }

    /**
     * Devuelve un string aleatorio de longitud $length
     * @param integer $length la longitud del string
     * @return string la cadena aleatoria
     */
    private function random_string($length = 30)
    {
        return mb_substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
}
