<?php

namespace Luoyue\aop;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Webman\Bootstrap;
use Workerman\Worker;

class AopBootstrap implements Bootstrap
{
    private static ComposerClassLoader $composerClassLoader;

    public static function start(?Worker $worker): void
    {
        $workerName = $worker?->name ?? 'master';
        $config = config('plugin.luoyue.aop.app');
        if (in_array($workerName, $config['ignore_process']) || !$config['enable']) {
            return;
        }

        $isFirstWorker = $worker?->id === 0;
        if ($isFirstWorker) {
            echo '[Process:' . $workerName . '] Start load aop class...' . \PHP_EOL;
            $time = microtime(true);
        }

        $interface = Aspect::getInstance();

        if ($config['aspect'] ?? []) {
            foreach ($config['aspect'] as $aspect) {
                $interface->addAspect($aspect);
            }
        }

        $interface->scan()->reload();

        if ($isFirstWorker) {
            $time = round(microtime(true) - $time, 3);
            echo '[Process:' . $workerName . '] Load aop class completed, time: ' . $time . 's' . \PHP_EOL;
        }
    }

    public static function getComposerClassLoader(): ?ComposerClassLoader
    {
        return static::$composerClassLoader ??= current(ComposerClassLoader::getRegisteredLoaders()) ?? null;
    }
}
