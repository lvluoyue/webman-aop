<?php

namespace luoyue\aop\Collects;

use luoyue\aop\AopBootstrap;
use luoyue\aop\Collects\node\PointcutNode;
use luoyue\aop\Proxy\Rewrite;
use support\Container;

/**
 * 代理类收集器
 */
class ProxyCollects
{
    private array $targetClassMap = [];

    public function scan()
    {
        $rewrite = new Rewrite();
        /**
         * @var string $className
         * @var PointcutNode $targetClass
         */
        foreach ($this->targetClassMap as $className => $targetClass) {
            $proxyClass = $targetClass->getProxyClassName(true);
            $proxyFile = $rewrite->rewrite($className, $targetClass);
            $container = Container::instance();
            AopBootstrap::getComposerClassLoader()->addClassMap([$proxyClass => $proxyFile]);
            if ($container instanceof \Webman\Container) {
                Container::make($className, Container::get($proxyClass));
            } else if ($container instanceof \DI\Container) {
                $container->set($className, \DI\autowire($proxyClass));
            }
        }
    }

    public function getTargetData(string $className, string $filePath): PointcutNode
    {
        return $this->targetClassMap[$className] ??= new PointcutNode($className, $filePath);
    }

    public function getAspectsClosure(string $className, string $method): array
    {
        if (!isset($this->targetClassMap[$className])) {
            return [];
        }
        /** @var PointcutNode $targetData */
        $targetData = $this->targetClassMap[$className];
        return $targetData->getAdviceClosure($method);
    }

    private function getProxyPath()
    {
        return base_path() . config('plugin.luoyue.aop.app.proxy_path', '/runtime/aopCache/proxyClasses') . DIRECTORY_SEPARATOR;
    }

}
