<?php

namespace Luoyue\aop\Proxy;

use Luoyue\aop\Aspect;

/**
 * Trait ProxyCallTrait.
 */
trait ProxyCallTrait
{
    protected static function _proxyCall(string $className, string $classMethod, array $arguments, \Closure $closure): mixed
    {
        $entryClass = new ProceedingJoinPoint(...\func_get_args());
        $pipeLine = new PipeLine(Aspect::getInstance()->getProxyCollects()->getAspectsClosure($className, $classMethod));

        return $pipeLine->run($entryClass);
    }
}
