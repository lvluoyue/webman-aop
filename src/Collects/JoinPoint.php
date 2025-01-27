<?php

namespace luoyue\aop\Collects;

use luoyue\aop\enum\AdviceTypeEnum;
use support\Container;

/**
 * 连接点数据类
 */
class JoinPoint
{
    /** @var string $aspectClass 切面类 */
    private string $aspectClass;

    /** @var string $aspectMethod 切面方法 */
    private string $aspectMethod;

    /** @var AdviceTypeEnum $aspectType 切面类型 */
    private AdviceTypeEnum $aspectType;

    /** @var array $aspectClasses 切面表达式 */
    private array $aspectClasses;

    /** @var Pointcut[] $targetData 表达式匹配的目标类 */
    private array $targetData = [];

    public function __construct(string $aspectClass, string $aspectMethod, AdviceTypeEnum $aspectType, array $aspectClasses)
    {
        $this->aspectClass = $aspectClass;
        $this->aspectMethod = $aspectMethod;
        $this->aspectType = $aspectType;
        $this->aspectClasses = $aspectClasses;
    }

    public function getClassName(): string
    {
        return $this->aspectClass;
    }

    public function getMethodName(): string
    {
        return $this->aspectMethod;
    }

    public function getAspectClosure(): \Closure
    {
        return $this->aspectType->getAspectClosure(Container::get($this->aspectClass), $this->aspectMethod);
    }

    public function getTargetData(): array
    {
        return $this->targetData;
    }

    public function getAspectClasses(): array
    {
        return $this->aspectClasses;
    }

    public function addTargetData(Pointcut $targetData)
    {
        $this->targetData[] = $targetData;
    }

}