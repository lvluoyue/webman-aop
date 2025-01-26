<?php

namespace luoyue\aop\interfaces;

/**
 * Interface ProxyInterface.
 */
interface ProxyInterface
{
    public function process(ProceedingJoinPointInterface $entryClass): mixed;
}
 