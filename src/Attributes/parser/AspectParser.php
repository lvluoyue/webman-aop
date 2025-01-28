<?php

namespace luoyue\aop\Attributes\parser;

use LinFly\Annotation\Contracts\IAnnotationParser;
use luoyue\aop\Collects\AspectCollects;
use luoyue\aop\Collects\node\AspectNode;
use luoyue\aop\enum\AdviceTypeEnum;
use ReflectionAttribute;
use ReflectionClass;
use support\Container;

/**
 * 切面类注解处理
 */
class AspectParser implements IAnnotationParser
{

    public static function process(array $item): void
    {
        $reflectionClass = new ReflectionClass($item['class']);
        foreach ($reflectionClass->getMethods() as $method) {
            foreach (AdviceTypeEnum::cases() as $aspectType) {
                /** @var ?ReflectionAttribute $annotation */
                $annotation = current($method->getAttributes($aspectType->value));
                if ($annotation) {
                    $parameters = (array)$annotation->getArguments()[0];
                    $aspectType = AdviceTypeEnum::tryFrom($annotation->getName());
                    $matches = array_map(fn ($class) => self::getMatchesClasses($class), $parameters);
                    $aspectCollects = Container::get(AspectCollects::class);
                    $aspectCollects->setAspects(new AspectNode($item['class'], $method->getName(), $aspectType, $matches));
                }
            }
        }
    }

    /**
     * 将切入点表达式解析为正则表达式
     * @param string $class
     * @return array
     */
    private static function getMatchesClasses(string $class): array
    {
        $explode = explode('::', $class, 2);
        $explode[1] ??= '*';
        return [
            'class' => str_replace(['\\', '**', '*', '#'], ['\\\\', '.#', '[^\\\\]#', '*'], $explode[0]),
            'method' => str_replace(['*', '\\'], ['.*', '\\\\'], $explode[1])
        ];
    }

}