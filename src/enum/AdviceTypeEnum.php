<?php

namespace luoyue\aop\enum;

use luoyue\aop\Attributes\After;
use luoyue\aop\Attributes\AfterReturning;
use luoyue\aop\Attributes\AfterThrowing;
use luoyue\aop\Attributes\Around;
use luoyue\aop\Attributes\Before;
use luoyue\aop\interfaces\ProceedingJoinPointInterface;

/**
 * 通知类型枚举
 */
enum AdviceTypeEnum: string
{
    /** 环绕通知（Around） */
    case Around = Around::class;

    /** 前置通知（Before） */
    case Before = Before::class;

    /** 后置通知（After） */
    case After = After::class;

    /** 返回通知（After Returning） */
    case AfterReturning = AfterReturning::class;

    /** 异常通知（After Throwing） */
    case AfterThrowing = AfterThrowing::class;

    /**
     * 获取通知逻辑闭包
     * @param object $class
     * @param string $method
     * @return \Closure
     */
    public function getAdviceClosure(object $class, string $method): \Closure
    {
        return match ($this) {
            AdviceTypeEnum::Around => function (ProceedingJoinPointInterface $entryClass) use ($class, $method) {
                return $class->{$method}($entryClass);
            },
            AdviceTypeEnum::Before => function (ProceedingJoinPointInterface $entryClass) use ($class, $method) {
                $class->{$method}();
                return $entryClass->process();
            },
            AdviceTypeEnum::After => function (ProceedingJoinPointInterface $entryClass) use ($class, $method) {
                try {
                    $result = $entryClass->process();
                } finally {
                    $class->{$method}();
                }
                return $result;
            },
            self::AfterReturning => function (ProceedingJoinPointInterface $entryClass) use ($class, $method) {
                $result = $entryClass->process();
                $class->{$method}($result);
                return $result;
            },
            self::AfterThrowing => function (ProceedingJoinPointInterface $entryClass) use ($class, $method) {
                try {
                    $result = $entryClass->process();
                } catch (\Throwable $e) {
                    $class->{$method}($e);
                    throw $e;
                }
                return $result;
            },
        };
    }

}