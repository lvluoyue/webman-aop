<?php

namespace luoyue\aop\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class AfterThrowing
{
    /**
     * 抛出异常通知
     * @param array|string $pointcut 切入点表达式
     */
    public function __construct(array|string $pointcut)
    {
    }
}