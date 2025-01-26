<?php

namespace luoyue\aop\enum;

use luoyue\aop\Attributes\After;
use luoyue\aop\Attributes\AfterReturning;
use luoyue\aop\Attributes\AfterThrowing;
use luoyue\aop\Attributes\Around;
use luoyue\aop\Attributes\Before;
use luoyue\aop\exception\ParseException;
use luoyue\aop\interfaces\ProceedingJoinPointInterface;

enum AspectTypeEnum
{
    case Around;
    case Before;
    case After;
    case AfterReturning;
    case AfterThrowing;

    public static function getAspectType(string $class): AspectTypeEnum
    {
        return match ($class) {
            Before::class => AspectTypeEnum::Before,
            After::class => AspectTypeEnum::After,
            Around::class => AspectTypeEnum::Around,
            AfterReturning::class => AspectTypeEnum::AfterReturning,
            AfterThrowing::class => AspectTypeEnum::AfterThrowing,
            default => throw new ParseException('unknown aspect type')
        };
    }

    public function getAspectClosure(object $class, string $method): \Closure
    {
        return match ($this) {
            AspectTypeEnum::Around => function (ProceedingJoinPointInterface $entryClass) use ($class, $method) {
                return $class->{$method}($entryClass);
            },
            AspectTypeEnum::Before => function (ProceedingJoinPointInterface $entryClass) use ($class, $method) {
                $class->{$method}();
                return $entryClass->process();
            },
            AspectTypeEnum::After => function (ProceedingJoinPointInterface $entryClass) use ($class, $method) {
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