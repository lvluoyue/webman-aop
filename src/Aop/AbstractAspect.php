<?php

namespace luoyue\aop\Aop;

use luoyue\aop\Aop\interfaces\ProceedingJoinPointInterface;
use luoyue\aop\Aop\interfaces\ProxyInterface;

/**
 * Class AbstractAspect.
 */
class AbstractAspect implements ProxyInterface
{
    //类名 eg: Index:class
    //类名 . '::方法明' eg: Index:class . '::hello'
    public array $classes = [];

    /**
     * @return mixed
     */
    public function process(ProceedingJoinPointInterface $entryClass)
    {
        return $entryClass->process();
    }
}
