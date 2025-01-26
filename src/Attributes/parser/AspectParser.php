<?php

namespace luoyue\aop\Attributes\parser;

use LinFly\Annotation\Contracts\IAnnotationParser;
use luoyue\aop\Collects\AspectCollects;
use luoyue\aop\Collects\AspectData;
use luoyue\aop\enum\AspectTypeEnum;
use support\Container;

class AspectParser implements IAnnotationParser
{

    public static function process(array $item): void
    {
        $aspectType = AspectTypeEnum::getAspectType($item['annotation']);
        $matches = array_map(fn ($class) => self::getMatchesClasses($class), (array) $item['parameters']['classes']);
        $aspectCollects = Container::get(AspectCollects::class);
        $aspectCollects->setAspects(new AspectData($item['class'], $item['method'], $aspectType, $matches));
    }

    private static function getMatchesClasses(string $class): array
    {
        $explode = explode('::', $class, 2);
        $explode[1] ??= '*';
        return [
            'class' => str_replace(['*', '\\'], ['.*', '\\\\'], $explode[0]),
            'method' => str_replace(['*', '\\'], ['.*', '\\\\'], $explode[1])
        ];
    }

}