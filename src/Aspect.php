<?php

namespace luoyue\aop;

use luoyue\aop\Collects\AspectCollects;
use luoyue\aop\Collects\node\AspectNode;
use luoyue\aop\enum\AdviceTypeEnum;
use ReflectionAttribute;
use ReflectionClass;

class Aspect
{

    private static Aspect $instance;

    private queue $queue;
    private AspectCollects $aspectCollects;

    private function __construct()
    {
        $this->queue = new queue();
        $this->aspectCollects = new AspectCollects();
    }

    public static function getInstance(): Aspect
    {
        return self::$instance ??= new self();
    }

    public function addAspect(string $aspectClass, ?int $priority = null): void
    {
        if ($priority === null) {
            $priority = $aspectClass;
        }
        $this->queue->insert($aspectClass, $priority);
    }

    public function scan(array $config): void
    {
        while ($this->queue->valid()) {
            $aspectClass = $this->queue->current();
            $reflectionClass = new ReflectionClass($aspectClass);
            foreach ($reflectionClass->getMethods() as $method) {
                foreach (AdviceTypeEnum::cases() as $aspectType) {
                    /** @var ?ReflectionAttribute $annotation */
                    $annotation = current($method->getAttributes($aspectType->value));
                    if ($annotation) {
                        $parameters = (array)$annotation->getArguments()[0];
                        $aspectType = AdviceTypeEnum::tryFrom($annotation->getName());
                        $matches = array_map(fn ($class) => $this->getMatchesClasses($class), $parameters);
                        $this->aspectCollects->addAspects(new AspectNode($aspectClass, $method->getName(), $aspectType, $matches));
                    }
                }
            }
            $this->queue->next();
        }

        foreach ($config['scans'] as $scan) {
            $this->aspectCollects->scan($scan);
        }
    }

    /**
     * 将切入点表达式解析为正则表达式
     * @param string $class
     * @return array
     */
    private function getMatchesClasses(string $class): array
    {
        $explode = explode('::', $class, 2);
        $explode[1] ??= '*';
        return [
            'class' => str_replace(['\\', '**', '*', '#'], ['\\\\', '.#', '[^\\\\]#', '*'], $explode[0]),
            'method' => str_replace(['*', '\\'], ['.*', '\\\\'], $explode[1])
        ];
    }

}