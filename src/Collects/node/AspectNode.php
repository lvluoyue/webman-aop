<?php

namespace Luoyue\aop\Collects\node;

use Luoyue\aop\enum\AdviceTypeEnum;
use support\Container;

/**
 * 切面节点.
 */
class AspectNode
{
    /** @var string 切面类名 */
    private string $aspectClass;

    /** @var string 通知方法 */
    private string $adviceMethod;

    /** @var AdviceTypeEnum 切入点类型 */
    private AdviceTypeEnum $adviceType;

    /** @var array 切入点表达式 */
    private array $pointcut;

    /** @var PointcutNode[] 表达式匹配的切入点 */
    private array $matchesPointcut = [];

    public function __construct(string $aspectClass, string $adviceMethod, AdviceTypeEnum $adviceType, array $pointcut)
    {
        $this->aspectClass = $aspectClass;
        $this->adviceMethod = $adviceMethod;
        $this->adviceType = $adviceType;
        $this->pointcut = $pointcut;
    }

    /**
     * 获取切面类名.
     */
    public function getClassName(): string
    {
        return $this->aspectClass;
    }

    public function getMethodName(): string
    {
        return $this->adviceMethod;
    }

    /**
     * 获取切面优先级.
     */
    public function getPriority(): int
    {
        return $this->adviceType->getPriority();
    }

    /**
     * 获取通知闭包.
     */
    public function getAdviceClosure(): \Closure
    {
        return $this->adviceType->getAdviceClosure(Container::get($this->aspectClass), $this->adviceMethod);
    }

    public function getTargetData(): array
    {
        return $this->matchesPointcut;
    }

    /**
     * 获取切入点表达式.
     */
    public function getPointcut(): array
    {
        return $this->pointcut;
    }

    /**
     * 添加匹配的切入点.
     */
    public function addMatchesPointcut(PointcutNode $pointcutData): void
    {
        $this->matchesPointcut[] = $pointcutData;
    }
}
