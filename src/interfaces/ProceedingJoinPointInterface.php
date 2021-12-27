<?php

namespace yzh52521\aop\interfaces;

/**
 * Interface EntryClassInterface.
 */
interface ProceedingJoinPointInterface
{
    public function process();

    public function getClassMethod();

    public function getClassName();

    public function getArguments();
}
