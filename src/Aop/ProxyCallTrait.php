<?php

namespace yzh52521\aop\Aop;

use yzh52521\aop\AopBootstrap;

/**
 * Trait ProxyCallTrait.
 */
trait ProxyCallTrait
{
    /**
     * @return mixed
     */
    public static function _proxyCall(string $className, string $classMethod, array $arguments, \Closure $closure)
    {
        $entryClass = new ProceedingJoinPoint($className, $classMethod, $arguments, $closure);
        $pipeLine = new PipeLine(array_values(array_merge(AopBootstrap::$classMap[$className]['methodsMap'][$classMethod] ?? [], AopBootstrap::$classMap[$className]['methodsMap']['*'] ?? [])));
        return $pipeLine->run($entryClass, function ($entry) {
            return $entry->processOriginClosure();
        });
    }

    /**
     * @throws \ReflectionException
     */
    public static function _getArguments(string $className, string $classMethod, array $arguments): array
    {
        $res = [];
        $reflectMethod = new \ReflectionMethod($className, $classMethod);
        $reflectParameters = $reflectMethod->getParameters();
        $leftArgCount = count($arguments);
        /**
         * @var $key
         * @var \ReflectionParameter $reflectionParameter
         */
        foreach ($reflectParameters as $key => $reflectionParameter) {
            $arg = $reflectionParameter->isVariadic() ? $arguments : array_shift($arguments);
            if (! isset($arg) && $leftArgCount <= 0) {
                $arg = $reflectionParameter->getDefaultValue();
            }
            --$leftArgCount;
            $res[] = $arg;
        }
        return $res;
    }
}
