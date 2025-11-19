<?php
require_once __DIR__ . '/php_file_cache.php';

class cache
{
    private static $memcache;
    private static $php_file_cache;
    private static $connected;
    private static $error;
    private static $error_msg;

    public function __construct()
    {
        if (!isset(self::$memcache)) {
            if (class_exists('Memcache')) {
                self::$memcache = new Memcache();
                if (@self::$memcache->connect(JG_CACHE_HOST, JG_CACHE_PORT)) {
                    self::$connected = true;
                    self::$error     = false;
                    self::$error_msg = '';
                } else {
                    self::$connected = false;
                    self::$error     = true;
                    self::$error_msg = 'Error al conectar al servidor Memcache.';
                }
            } else {
                self::$memcache  = null;
                self::$connected = false;
                self::$error     = true;
                self::$error_msg = 'Clase Memcache no encontrada. Debes instalar <b>Memcache</b> y activarlo en el php.ini';
            }
        }

        self::$php_file_cache = new php_file_cache();
    }

    public function error()
    {
        return self::$error;
    }

    public function error_msg()
    {
        return self::$error_msg;
    }

    public function close()
    {
        if (isset(self::$memcache) && self::$connected) {
            self::$memcache->close();
        }
    }

    public function set($key, $object, $expire = 5400)
    {
        if (self::$connected) {
            self::$memcache->set(JG_CACHE_PREFIX . $key, $object, false, $expire);
        } else {
            self::$php_file_cache->put($key, $object);
        }
    }

    public function get($key)
    {
        if (self::$connected) {
            return self::$memcache->get(JG_CACHE_PREFIX . $key);
        }

        return self::$php_file_cache->get($key);
    }

    public function get_array($key)
    {
        $aa = [];

        if (self::$connected) {
            $a = self::$memcache->get(JG_CACHE_PREFIX . $key);
            if ($a) {
                $aa = $a;
            }
        } else {
            $a = self::$php_file_cache->get($key);
            if ($a) {
                $aa = $a;
            }
        }

        return $aa;
    }
    
    public function get_array2($key, &$error)
    {
        $aa    = [];
        $error = true;

        if (self::$connected) {
            $a = self::$memcache->get(JG_CACHE_PREFIX . $key);
            if (is_array($a)) {
                $aa    = $a;
                $error = false;
            }
        } else {
            $a = self::$php_file_cache->get($key);
            if (is_array($a)) {
                $aa    = $a;
                $error = false;
            }
        }

        return $aa;
    }

    public function delete($key)
    {
        if (self::$connected) {
            return self::$memcache->delete(JG_CACHE_PREFIX . $key);
        }

        return self::$php_file_cache->delete($key);
    }

    public function delete_multi($keys)
    {
        $done = false;

        if (self::$connected) {
            foreach ($keys as $i => $value) {
                $done = self::$memcache->delete(JG_CACHE_PREFIX . $value);
            }
        } else {
            foreach ($keys as $i => $value) {
                $done = self::$php_file_cache->delete($value);
            }
        }

        return $done;
    }

    public function clean()
    {
        if (self::$connected) {
            return self::$memcache->flush();
        }

        return self::$php_file_cache->flush();
    }

    public function version()
    {
        if (self::$connected) {
            return 'Memcache ' . self::$memcache->getVersion();
        }

        return 'Files';
    }

    public function connected()
    {
        return self::$connected;
    }
}
