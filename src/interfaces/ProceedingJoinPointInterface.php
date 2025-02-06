<?php

namespace Luoyue\aop\interfaces;

use ReflectionMethod;

/**
 * Interface EntryClassInterface.
 */
interface ProceedingJoinPointInterface
{
    /**
     * 执行切面逻辑.
     */
    public function process(): mixed;

    /**
     * 执行原始方法.
     */
    public function processOriginalMethod(): mixed;

    /**
     * 获取原始方法名.
     */
    public function getMethodName(): string;

    /**
     * 获取原始类名.
     */
    public function getClassName(): string;

    /**
     * 获取原始方法参数.
     */
    public function getArguments(): array;

    /**
     * 获取原始方法反射对象
     */
    public function getReflectMethod(): ReflectionMethod;

    /**
     * 获取原始方法实例.
     */
    public function getInstance(): ?object;
}
