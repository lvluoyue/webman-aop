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
    case Around = Around::class;
    case Before = Before::class;
    case After = After::class;
    case AfterReturning = AfterReturning::class;
    case AfterThrowing = AfterThrowing::class;

    /**
     * 获取通知逻辑闭包
     * @param object $class
     * @param string $method
     * @return \Closure
     */
    public function getAspectClosure(object $class, string $method): \Closure
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