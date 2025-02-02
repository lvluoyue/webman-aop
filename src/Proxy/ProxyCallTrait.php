<?php

namespace Luoyue\aop\Proxy;

use Luoyue\aop\Aspect;
use Luoyue\aop\Collects\ProxyCollects;
use support\Container;

/**
 * Trait ProxyCallTrait.
 */
trait ProxyCallTrait
{
    protected static function _proxyCall(string $className, string $classMethod, array $arguments, \Closure $closure): mixed
    {
        $entryClass = new ProceedingJoinPoint(...func_get_args());
        $pipeLine = new PipeLine(self::_getClosure($className, $classMethod));
        return $pipeLine->run($entryClass);
    }

    protected static function _getClosure(string $className, string $classMethod): array
    {
        $proxyCollects = Aspect::getInstance()->getProxyCollects();
        return $proxyCollects->getAspectsClosure($className, $classMethod);
    }

}
