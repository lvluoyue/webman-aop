<?php

namespace luoyue\aop;

use luoyue\aop\interfaces\ProceedingJoinPointInterface;
use luoyue\aop\interfaces\ProxyInterface;

/**
 * Class AbstractAspect.
 */
class AbstractAspect implements ProxyInterface
{
    //类名 eg: Index:class
    //类名 . '::方法明' eg: Index:class . '::hello'
    public array $classes = [];

    public function process(ProceedingJoinPointInterface $entryClass): mixed
    {
        return $entryClass->process();
    }
}
