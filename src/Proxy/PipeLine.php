<?php

namespace Luoyue\aop\Proxy;

use Closure;
use Luoyue\aop\interfaces\ProceedingJoinPointInterface;

/**
 * Class PipeLine.
 */
class PipeLine
{
    private array $pipes;

    public function __construct(array $pipes)
    {
        $this->pipes = $pipes;
    }

    /**
     * 运行管道.
     */
    public function run(ProceedingJoinPointInterface $entry): mixed
    {
        $pipe = array_reduce($this->pipes, $this->callback(), $this->default());

        return $pipe($entry);
    }

    /**
     * 默认管道（调用原始方法）.
     */
    private function default(): Closure
    {
        return fn (ProceedingJoinPointInterface $entry) => $entry->processOriginalMethod();
    }

    /**
     * 管道回调.
     */
    private function callback(): Closure
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
