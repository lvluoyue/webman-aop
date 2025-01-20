<?php

namespace luoyue\aop\Attributes\parser;

use LinFly\Annotation\Contracts\IAnnotationParser;

class AspectParser implements IAnnotationParser
{

    private static array $aspects = [];

    public static function process(array $item): void
    {
        self::$aspects[$item['class']] = $item;
    }

    public static function getAspects(): array
    {
        return self::$aspects;
    }

}