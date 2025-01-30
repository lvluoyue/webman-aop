<?php

namespace Luoyue\aop\interfaces;

use ReflectionMethod;

/**
 * Interface EntryClassInterface.
 */
interface ProceedingJoinPointInterface
{
    /**
     * 执行切面逻辑
     * @return mixed
     */
    public function process(): mixed;

    /**
     * 执行原始方法
     * @return mixed
     */
    public function processOriginalMethod(): mixed;

    /**
     * 获取原始方法名
     * @return string
     */
    public function getMethodName(): string;

    /**
     * 获取原始类名
     * @return string
     */
    public function getClassName(): string;

    /**
     * 获取原始方法参数
     * @return array
     */
    public function getArguments(): array;

    /**
     * 获取原始方法反射对象
     * @return ReflectionMethod
     */
    public function getReflectMethod(): ReflectionMethod;

    /**
     * 获取原始方法实例
     * @return object|null
     */
    public function getInstance(): ?object;
}
