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
    public static function _proxyCall(string $className, string $classMethod, array $arguments, \Closure $closure): mixed
    {
        $entryClass = new ProceedingJoinPoint($className, $classMethod, $arguments, $closure);
        /** @var ProxyCollects $proxyCollects */
        $proxyCollects = Container::get(ProxyCollects::class);
        $pipeLine = new PipeLine($proxyCollects->getAspectsClosure($className, $classMethod));
        return $pipeLine->run($entryClass, fn (ProceedingJoinPointInterface $entry) => $entry->processOriginalMethod());
    }

}
