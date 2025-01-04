<?php

namespace luoyue\aop\Proxy;

use luoyue\aop\exception\ProceedingJoinPointException;
use luoyue\aop\interfaces\ProceedingJoinPointInterface;
use Closure;

/**
 * Class EntryClass.
 */
class ProceedingJoinPoint implements ProceedingJoinPointInterface
{

    public ?Closure $pipe;

    public function __construct(public string $className,
                                public string $classMethod,
                                public array $arguments,
                                public Closure $originalMethod)
    {
    }

    public function getClassMethod(): string
    {
        return $this->classMethod;
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
}
