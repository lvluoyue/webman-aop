<?php

namespace luoyue\aop\Proxy;

use luoyue\aop\exception\ProceedingJoinPointException;
use luoyue\aop\interfaces\ProceedingJoinPointInterface;
use Closure;
use ReflectionFunction;
use ReflectionMethod;
use support\Container;

/**
 * Class EntryClass.
 */
class ProceedingJoinPoint implements ProceedingJoinPointInterface
{

    public ?Closure $pipe;

    /** @var ReflectionMethod[] */
    private static array $reflectMethods;

    /** @var object[] */
    private static array $instances;

    public function __construct(public string $className,
                                public string $methodName,
                                public array $arguments,
                                public Closure $originalMethod)
    {
    }

    public function process(): mixed
    {
        $c = $this->pipe;
        if (! $this->pipe instanceof Closure) {
            throw new ProceedingJoinPointException('entry class pipe must be closure');
        }
        return $c($this);
    }

    public function processOriginalMethod()
    {
        $this->pipe = null;
        $closure = $this->originalMethod;
        $arguments = $this->getArguments();
        return $closure(...$arguments);
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getArguments(): array
    {
        $result = [];
        foreach ($this->arguments['order'] ?? [] as $order) {
            $result[$order] = $this->arguments['keys'][$order];
        }

        // Variable arguments are always placed at the end.
        if (isset($this->arguments['variadic'], $order) && $order === $this->arguments['variadic']) {
            $variadic = array_pop($result);
            $result = array_merge($result, [$order => $variadic]);
        }
        return $result;
    }

    public function getReflectMethod(): ReflectionMethod
    {
        return self::$reflectMethods[$this->className . $this->methodName] ??=
            new ReflectionMethod(Container::get($this->className), $this->methodName);
    }

    public function getInstance(): ?object
    {
        return self::$reflectMethods[$this->className . $this->methodName] ??=
            (new ReflectionFunction($this->originalMethod))->getClosureThis();
    }
}
