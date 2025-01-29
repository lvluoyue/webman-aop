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

    /**
     * 构造一个新的方法拦截器实例
     *
     * @param string $className 类名，表示需要拦截的类
     * @param string $methodName 方法名，表示需要拦截的方法
     * @param array $arguments 方法参数，包含该方法的所有参数
     * @param Closure $originalMethod 原始方法的闭包，允许在拦截后仍然可以调用原始方法
     */
    public function __construct(public string $className,
                                public string $methodName,
                                public array $arguments,
                                public Closure $originalMethod)
    {
    }

    public function process(): mixed
    {
        $c = $this->pipe;
        if (!$this->pipe) {
            throw new ProceedingJoinPointException('The pipe is empty');
        }
        return $c($this);
    }

    public function processOriginalMethod(): mixed
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
