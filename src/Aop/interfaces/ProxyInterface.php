<?php

namespace luoyue\aop\Aop\interfaces;

/**
 * Interface ProxyInterface.
 */
interface ProxyInterface
{
    public function process(ProceedingJoinPointInterface $entryClass);
}
