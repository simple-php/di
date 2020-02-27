<?php
namespace SimplePHP\DI;

trait DITrait {       
    protected static $_services = null;
    protected static $_instances = [];

    abstract static function initServices();

    static function getService($serviceName) {
        if (!isset(self::$_instances[$serviceName])) {
            self::$_instances[$serviceName] = self::createService($serviceName);
        }  
        return self::$_instances[$serviceName];
    }

    static function createService($serviceName, $args = null) {
        if (!isset(self::$_services)) {
            self::initServices();
        }
        if (!isset(self::$_services[$serviceName])) {
            return null;
        }
        $c = self::$_services[$serviceName];
        if (is_callable($c)) {            
            return call_user_func($c, $args);
        } else if (is_array($c)) {
            $cb = $c[0];
            if (is_callable($cb)) {
                $params = array_slice($c, 1);
                foreach ($params as $i => $p)
                {
                    if (is_string($p) && substr($p, 0, 1) === '%' && substr($p, -1) === '%'  ) {
                        $p = substr($p, 1, -1);
                        $service = self::getService($p);
                        if ($service) $params[$i] = $service;
                    }
                }
                call_user_func($cb, $params)
            }
        }
        
    }

    public static function __callStatic($name, $arguments) {
        return getService($name);
    }
}