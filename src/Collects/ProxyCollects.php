<?php

namespace Luoyue\aop\Collects;

use Luoyue\aop\AopBootstrap;
use Luoyue\aop\Collects\node\PointcutNode;
use Luoyue\aop\Proxy\Rewrite;
use support\Container;

/**
 * 代理类收集器
 */
class ProxyCollects
{
    /** @var array<string, PointcutNode> $PointcutMap 切入点集合 */
    private array $PointcutMap = [];

    public function scan()
    {
        $rewrite = new Rewrite();
        foreach ($this->PointcutMap as $className => $pointcutNode) {
            $proxyClass = $pointcutNode->getProxyClassName(true);
            $rewrite->rewrite($pointcutNode);
            $container = Container::instance();
            AopBootstrap::getComposerClassLoader()->addClassMap([$proxyClass => $pointcutNode->getProxyFile(true)]);
            if ($container instanceof \Webman\Container) {
                Container::make($className, Container::get($proxyClass));
            } else if ($container instanceof \DI\Container) {
                $container->set($className, \DI\autowire($proxyClass));
            }
        }
    }

    /**
     * 获取切入点节点
     * @param string $className
     * @return PointcutNode
     */
    public function getPointcutNode(string $className): PointcutNode
    {
        return $this->PointcutMap[$className] ??= new PointcutNode($className);
    }

    /**
     * 获取切面闭包集合
     * @param string $className
     * @param string $method
     * @return array
     */
    public function getAspectsClosure(string $className, string $method): array
    {
        if (!isset($this->PointcutMap[$className])) {
            return [];
        }
        $targetData = $this->PointcutMap[$className];
        return $targetData->getAdviceClosure($method);
    }

}
