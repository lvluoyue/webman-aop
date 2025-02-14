<?php

namespace Luoyue\aop\Collects;

use Generator;
use Luoyue\aop\Collects\node\PointcutNode;

/**
 * 代理类收集器.
 */
class ProxyCollects
{
    /** @var array<string, PointcutNode> 切入点集合 */
    private array $PointcutMap = [];

    /**
     * 遍历所有切入点表达式.
     */
    public function getPointcutMap(): ?Generator
    {
        foreach ($this->PointcutMap as $className => $pointcutNode) {
            yield [$className, $pointcutNode];
        }

        return null;
    }

    /**
     * 获取切入点节点.
     */
    public function getPointcutNode(string $className): PointcutNode
    {
        return $this->PointcutMap[$className] ??= new PointcutNode($className);
    }

    /**
     * 获取切面闭包集合.
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
