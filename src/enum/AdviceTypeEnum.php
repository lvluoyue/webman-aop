<?php

namespace Luoyue\aop\enum;

use Luoyue\aop\Attributes\After;
use Luoyue\aop\Attributes\AfterReturning;
use Luoyue\aop\Attributes\AfterThrowing;
use Luoyue\aop\Attributes\Around;
use Luoyue\aop\Attributes\Before;
use Luoyue\aop\interfaces\ProceedingJoinPointInterface;

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

    public static function getAnnotationNames(): array
    {
        return array_map(fn (AdviceTypeEnum $class) => $class->value, self::cases());
    }

    /**
     * 获取通知优先级
     * @return int
     */
    public function getPriority(): int
    {
        return match ($this) {
            AdviceTypeEnum::Around => 1,
            AdviceTypeEnum::Before => 2,
            AdviceTypeEnum::After => 2,
            AdviceTypeEnum::AfterReturning => 3,
            AdviceTypeEnum::AfterThrowing => 3,
        };
    }

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
                    $advice = $class->{$method}($result, $entryClass);
                }
                return $advice === null ? $result : $advice;
            },
            self::AfterReturning => function (ProceedingJoinPointInterface $entryClass) use ($class, $method) {
                $result = $entryClass->process();
                $advice = $class->{$method}($result, $entryClass);
                print_r($advice);
                return $advice === null ? $result : $advice;
            },
            self::AfterThrowing => function (ProceedingJoinPointInterface $entryClass) use ($class, $method) {
                try {
                    $result = $entryClass->process();
                } catch (\Throwable $e) {
                    $class->{$method}($e, $entryClass);
                    throw $e;
                }
                return $result;
            },
        };
    }

}