<?php

namespace luoyue\aop\interfaces;

use ReflectionMethod;

/**
 * Interface EntryClassInterface.
 */
interface ProceedingJoinPointInterface
{
    public function process(): mixed;

    public function getMethodName(): string;

    public function getClassName(): string;

    public function getArguments(): array;

    public function getReflectMethod(): ReflectionMethod;

    public function getInstance(): ?object;
}
