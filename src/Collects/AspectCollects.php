<?php

namespace Luoyue\aop\Collects;

use Generator;
use Luoyue\aop\Collects\node\AspectNode;

/**
 * 切面类收集器.
 */
class AspectCollects
{
    /** @var AspectNode[] 切面集合 */
    private array $aspectsClass = [];

    public function addAspects(AspectNode $aspectsClass)
    {
        $this->aspectsClass[$aspectsClass->getClassName() . '::' . $aspectsClass->getMethodName()] = $aspectsClass;
    }

    /**
     * 获取切面节点.
     */
    public function getAspectNode(string $className, string $methodName): ?AspectNode
    {
        return $this->aspectsClass[$className . '::' . $methodName] ?? null;
    }

    /**
     * 遍历所有切入点表达式.
     */
    public function getAspectsClasses(): Generator
    {
        foreach ($this->aspectsClass as $aspectsClass) {
            foreach ($aspectsClass->getPointcut() as $class) {
                yield [
                    'aspect' => $aspectsClass,
                    ...$class,
                ];
            }
        }
    }
}
