<?php

namespace Luoyue\aop;

use Luoyue\aop\enum\AdviceTypeEnum;
use ReflectionAttribute;

class AopUtils  {

    /**
     * 过滤切面类注解
     * @return ReflectionAttribute[]
     */
    public static function filterAttributes(\ReflectionMethod $method): array
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