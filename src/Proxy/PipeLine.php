<?php

namespace luoyue\aop\Proxy;

use Closure;
use luoyue\aop\interfaces\ProceedingJoinPointInterface;
use support\Container;

/**
 * Class PipeLine.
 */
class PipeLine
{
    private array $pipes;

    private string $method = 'process';

    public function __construct(array $pipes)
    {
        $this->pipes = array_reverse($pipes);
    }

    /**
     * @param ProceedingJoinPointInterface $entry
     * @param Closure $cFun
     * @return mixed
     */
    public function run(ProceedingJoinPointInterface $entry, Closure $cFun): mixed
    {
        $pipe = array_reduce($this->pipes, $this->callback(), $this->default($cFun));
        return $pipe($entry);
    }

    /**
     * @param Closure $cFun
     * @return Closure
     */
    public function default(Closure $cFun): Closure
    {
        return function (ProceedingJoinPointInterface $entry) use ($cFun) {
            return $cFun($entry);
        };
    }

    public function callback(): Closure
    {
        return function ($res, $pipe) {
            return function (ProceedingJoinPointInterface $entryClass) use ($res, $pipe) {
                $tempPipe = $pipe;
                if (is_string($pipe) && class_exists($pipe)) {
                    $tempPipe = Container::get($pipe);
                }
                $entryClass->pipe = $res;
                return method_exists($tempPipe, $this->method) ? $tempPipe->{$this->method}($entryClass) : $tempPipe($entryClass);
            };
        };
    }
}
