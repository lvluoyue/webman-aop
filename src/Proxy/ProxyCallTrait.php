<?php

namespace luoyue\aop\Proxy;

use luoyue\aop\AopBootstrap;
use luoyue\aop\interfaces\ProceedingJoinPointInterface;

/**
 * Trait ProxyCallTrait.
 */
trait ProxyCallTrait
{
    public static function _proxyCall(string $className, string $classMethod, array $arguments, \Closure $closure): mixed
    {
        $entryClass = new ProceedingJoinPoint($className, $classMethod, $arguments, $closure);
        $pipeLine = new PipeLine(array_values(array_merge(AopBootstrap::$classMap[$className]['methodsMap'][$classMethod] ?? [], AopBootstrap::$classMap[$className]['methodsMap']['*'] ?? [])));
        return $pipeLine->run($entryClass, fn (ProceedingJoinPointInterface $entry) => $entry->processOriginalMethod());
    }

}
