<?php

namespace luoyue\aop\Proxy;

use Closure;
use luoyue\aop\interfaces\ProceedingJoinPointInterface;

/**
 * Class PipeLine.
 */
class PipeLine
{
    private array $pipes;

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
        return function (Closure $res, Closure $pipe) {
            return function (ProceedingJoinPointInterface $entryClass) use ($res, $pipe) {
                $tempPipe = $pipe;
                $entryClass->pipe = $res;
                return $tempPipe($entryClass);
            };
        };
    }
}
