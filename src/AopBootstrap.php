<?php

namespace Luoyue\aop;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Luoyue\aop\Collects\AspectCollects;
use Luoyue\aop\Collects\ProxyCollects;
use support\Container;
use Webman\Bootstrap;
use Workerman\Worker;

class AopBootstrap implements Bootstrap
{

    private static ComposerClassLoader $composerClassLoader;

    public static function start(?Worker $worker): void
    {
        $workerName = $worker?->name ?? 'master';
        $config = config('plugin.luoyue.aop.app');
        if ($workerName == 'monitor' || !$config['enable']) {
            return;
        }

        $isFirstWorker = $worker?->id === 0;
        if ($isFirstWorker) {
            echo '[Process:' . $workerName . '] Start load aop class...' . PHP_EOL;
            $time = microtime(true);
        }

        Aspect::getInstance()->scan($config);

        /** @var ProxyCollects $proxyCollects */
        $proxyCollects = Container::get(ProxyCollects::class);
        $proxyCollects->scan();

        if ($isFirstWorker) {
            $time = round(microtime(true) - $time, 3);
            echo '[Process:' . $workerName . '] Load aop class completed, time: ' . $time . 's' . PHP_EOL;
        }
    }

    public static function getComposerClassLoader(): ?ComposerClassLoader
    {
        return static::$composerClassLoader ??= current(ComposerClassLoader::getRegisteredLoaders()) ?? null;
    }

}