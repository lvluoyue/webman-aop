<?php

namespace luoyue\aop;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use luoyue\aop\Collects\AspectCollects;
use luoyue\aop\Collects\ProxyCollects;
use luoyue\aop\Proxy\Rewrite;
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

    private static $workerName;

    public static function start($worker)
    {
        self::$workerName = $worker?->name ?? '';
        $config = config('plugin.luoyue.aop.app');
        if (self::$workerName == 'monitor' || !$config['enable'] || self::$isInit) {
            return;
        }

        self::$config = new Config($config);
        $isFirstWorker = $worker?->id === 0;
        if ($isFirstWorker) {
            echo '[Process:' . self::$workerName . '] Start load aop class...' . PHP_EOL;
            $time = microtime(true);
        }
        foreach (ComposerClassLoader::getRegisteredLoaders() as $loader) {
            if ($loader instanceof ComposerClassLoader) {
                self::$composerClassLoader = $loader;
                $proxyCollects = new ProxyCollects();
                $aspectCollects = new AspectCollects(self::$config);
                $aspectCollects->collectProxy($proxyCollects);
                (new Rewrite(self::$config, $proxyCollects))->rewrite();
                self::$proxyClasses = $proxyCollects->getProxyClasses();
                self::$aspectClasses = $aspectCollects->getAspectsClass();
                self::$classMap = $proxyCollects->getClassMap();
                break;
            }
        }
        self::init();

        if ($isFirstWorker) {
            $time = round(microtime(true) - $time, 3);
            echo '[Process:' . self::$workerName . '] Load aop class completed, time: ' . $time . 's' . PHP_EOL;
        }
    }

    public static function init(): void
    {
        foreach (self::$proxyClasses as $proxyClass => $class) {
            self::$composerClassLoader->addClassMap([$proxyClass => $class[0]]);
            if (Container::instance() instanceof \Webman\Container) {
                Container::set($class[1], Container::get($proxyClass));
            } else if (Container::instance() instanceof \DI\Container) {
                Container::set($class[1], \DI\autowire($proxyClass));
            }
        }
        self::$isInit = true;
    }

    public static function getComposerClassLoader(): ?ComposerClassLoader
    {
        return static::$composerClassLoader ?? null;
    }

}