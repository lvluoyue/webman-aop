<?php

namespace luoyue\aop\interfaces;

/**
 * Interface EntryClassInterface.
 */
interface ProceedingJoinPointInterface
{
    public function process(): mixed;

    public function getClassMethod(): string;

    public function getClassName(): string;

    public function getArguments(): array;
}
