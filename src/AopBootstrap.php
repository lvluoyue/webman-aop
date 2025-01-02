<?php

namespace yzh52521\aop;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Webman\Bootstrap;
use support\Container;
use yzh52521\aop\Aop\AspectCollects;
use yzh52521\aop\Aop\Config;
use yzh52521\aop\Aop\ProxyCollects;
use yzh52521\aop\Aop\Rewrite;
use function yzh52521\aop\Aop\includeFile;

class AopBootstrap implements Bootstrap
{

    public static array $proxyClasses = [];

    public static array $aspectClasses = [];

    public static array $classMap = [];

    private static Config $config;

    private static ComposerClassLoader $composerClassLoader;

    public static function start($worker)
    {
        if ($worker) {
            $config = config('plugin.yzh52521.aop.app');
            if (!$config['enable']) {
                return;
            }
            $loaders = spl_autoload_functions();
            foreach ($loaders as $loader) {
                if (isset($loader[0]) && $loader[0] instanceof ComposerClassLoader) {
                    self::$composerClassLoader = $loader[0];
                    self::$config = new Config($config);
                    self::$config->parse();
                    $proxyCollects = new ProxyCollects();
                    $aspectCollects = new AspectCollects(self::$config);
                    $aspectCollects->collectProxy($proxyCollects);
                    (new Rewrite(self::$config, $proxyCollects))->rewrite();
                    self::$proxyClasses = $proxyCollects->getProxyClasses();
                    self::$aspectClasses = $aspectCollects->getAspectsClass();
                    self::$classMap = $proxyCollects->getClassMap();
                    spl_autoload_register([new self(), 'loadClass'], true, true);
                    spl_autoload_unregister($loader);
                }
            }
            self::init();
        }
    }

    public static function getComposerClassLoader(): ?ComposerClassLoader
    {
        return static::$composerClassLoader ?? null;
    }

    public static function init(): void
    {
        foreach (self::$proxyClasses as $proxyClass => $class) {
            if(Container::instance() instanceof \Webman\Container) {
                $instance = new $proxyClass();
                Container::set($class[1], $instance);
            } else if (Container::instance() instanceof \DI\Container) {
                Container::set($class[1], \DI\autowire($proxyClass));
            }
        }
    }

    /**
     * @param $class
     */
    public function loadClass($class): bool
    {
        if (isset(self::$proxyClasses[$class]) && file_exists(self::$proxyClasses[$class][0])) {
            $file = self::$proxyClasses[$class]['0'];
        } else {
            $file = self::$composerClassLoader->findFile($class);
        }
        if ($file) {
            include_once $file;
            return true;
        }
        return false;
    }

}
