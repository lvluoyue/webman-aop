<?php

namespace Luoyue\aop;

use Luoyue\aop\Collects\AspectCollects;
use Luoyue\aop\Collects\node\AspectNode;
use Luoyue\aop\enum\AdviceTypeEnum;
use ReflectionAttribute;
use ReflectionClass;

class Aspect
{

    private static Aspect $instance;

    private PriorityQueue $queue;

    private AspectCollects $aspectCollects;

    private function __construct()
    {
        $this->queue = new PriorityQueue();
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
        $this->scanAnnotations();
        foreach ($config['scans'] as $scan) {
            $this->aspectCollects->scan($scan);
        }
    }

    /**
     * 执行切面注解逻辑
     * @return void
     */
    private function scanAnnotations(): void
    {
        foreach ($this->queue as $aspectClass) {
            $reflectionClass = new ReflectionClass($aspectClass);
            // 遍历切面类方法
            foreach ($reflectionClass->getMethods() as $method) {
                //过滤非切面注解
                foreach ($this->filterAttributes($method) as $annotation) {
                    //获取方法名
                    $methodName = $method->getName();
                    //获取枚举对象
                    $adviceType = AdviceTypeEnum::tryFrom($annotation->getName());
                    //获取切入点正则表达式数组
                    $matches = array_map([$this, 'getMatchesClasses'], (array)$annotation->getArguments()[0]);
                    //将切面节点添加到切面收集器中
                    $this->aspectCollects->addAspects(new AspectNode($aspectClass, $methodName, $adviceType, $matches));
                }
            }
        }
    }

    /**
     * 过滤切面类注解
     * @return ReflectionAttribute[]
     */
    private function filterAttributes(\ReflectionMethod $method): array
    {
        return array_filter($method->getAttributes(), function (ReflectionAttribute $attribute) {
            return in_array($attribute->getName(), AdviceTypeEnum::getAnnotationNames());
        });
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