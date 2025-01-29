<?php

namespace luoyue\aop\Proxy;

use luoyue\aop\Collects\ProxyCollects;
use luoyue\aop\interfaces\ProceedingJoinPointInterface;
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
        /** @var ProxyCollects $proxyCollects */
        $proxyCollects = Container::get(ProxyCollects::class);
        return $proxyCollects->getAspectsClosure($className, $classMethod);
    }

}
