<?php

namespace Luoyue\aop;

use Luoyue\aop\enum\AdviceTypeEnum;
use ReflectionAttribute;

class AopUtils  {

    /**
     * 过滤切面类注解
     * @param \ReflectionMethod $method
     * @param array $classes
     * @return ReflectionAttribute[]
     */
    public static function filterAttributes(\ReflectionMethod $method,array|string $classes): array
    {
        return array_filter($method->getAttributes(), function (ReflectionAttribute $attribute) use ($classes) {
            return in_array($attribute->getName(), (array)$classes);
        });
    }

    /**
     * 将切入点表达式解析为正则表达式
     * @param string $class
     * @return array
     */
    public static function getMatchesClasses(string $class): array
    {
        $explode = explode('::', $class, 2);
        $explode[1] ??= '*';
        return [
            'class' => str_replace(['\\', '**', '*', '#'], ['\\\\', '.#', '[^\\\\]#', '*'], $explode[0]),
            'method' => str_replace(['*', '\\'], ['.*', '\\\\'], $explode[1])
        ];
    }

}