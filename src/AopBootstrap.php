<?php

namespace luoyue\aop;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use luoyue\aop\Aop\Collects\AspectCollects;
use luoyue\aop\Aop\Collects\ProxyCollects;
use luoyue\aop\Aop\Config;
use luoyue\aop\Aop\Rewrite;
use support\Container;
use Webman\Bootstrap;

class AopBootstrap implements Bootstrap
{

    public static array $proxyClasses = [];

    public static array $aspectClasses = [];

    public static array $classMap = [];

    private static Config $config;

    private static ComposerClassLoader $composerClassLoader;

    private static bool $isInit = false;

    public static function start($worker)
    {
        if ($worker) {
            $config = config('plugin.luoyue.aop.app');
            if (!$config['enable'] || self::$isInit) {
                return;
            }
            foreach (spl_autoload_functions() as $loader) {
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
            self::$composerClassLoader->addClassMap([$proxyClass => $class[0]]);
            if(Container::instance() instanceof \Webman\Container) {
                $instance = new $proxyClass();
                Container::set($class[1], $instance);
            } else if (Container::instance() instanceof \DI\Container) {
                Container::set($class[1], \DI\autowire($proxyClass));
            }
        }
        self::$isInit = true;
    }

}