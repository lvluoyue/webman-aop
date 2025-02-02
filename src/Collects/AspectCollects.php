<?php

namespace Luoyue\aop\Collects;

use Generator;
use Luoyue\aop\Collects\node\AspectNode;

/**
 * 切面类收集器
 */
class AspectCollects
{

    /** @var AspectNode[] $aspectsClass 切面集合 */
    private array $aspectsClass = [];

    public function addAspects(AspectNode $aspectsClass)
    {
        $this->aspectsClass[] = $aspectsClass;
    }

    /**
     * 遍历所有切入点表达式
     * @return Generator
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
