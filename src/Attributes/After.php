<?php

namespace luoyue\aop\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class After
{
    /**
     * 后置通知
     * @param array|string $pointcut 切入点表达式
     */
    public function __construct(array|string $pointcut)
    {
    }
}