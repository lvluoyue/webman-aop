<?php

namespace luoyue\aop\Collects\node;

use luoyue\aop\enum\AdviceTypeEnum;
use support\Container;

/**
 * 切面节点
 */
class AspectNode
{
    /** @var string $aspectClass 切面类名 */
    private string $aspectClass;

    /** @var string $adviceMethod 通知方法 */
    private string $adviceMethod;

    /** @var AdviceTypeEnum $adviceType 切入点类型 */
    private AdviceTypeEnum $adviceType;

    /** @var array $pointcut 切入点表达式 */
    private array $pointcut;

    /** @var PointcutNode[] $matchesPointcut 表达式匹配的切入点 */
    private array $matchesPointcut = [];

    public function __construct(string $aspectClass, string $adviceMethod, AdviceTypeEnum $adviceType, array $pointcut)
    {
        $this->aspectClass = $aspectClass;
        $this->adviceMethod = $adviceMethod;
        $this->adviceType = $adviceType;
        $this->pointcut = $pointcut;
    }

    /**
     * 获取切面类名
     * @return string
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
     * 获取通知闭包
     * @return \Closure
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
     * 获取切入点表达式
     * @return array
     */
    public function getPointcut(): array
    {
        return $this->pointcut;
    }

    /**
     * 添加匹配的切入点
     * @param PointcutNode $pointcutData
     * @return void
     */
    public function addMatchesPointcut(PointcutNode $pointcutData)
    {
        $this->matchesPointcut[] = $pointcutData;
    }

}