<?php

namespace luoyue\aop\Aop\Attributes\Parser;

use LinFly\Annotation\Contracts\IAnnotationParser;

class AspectParser implements iAnnotationParser
{

    private static array $aspects = [];

    public static function process(array $item): void
    {
        self::$aspects[] = $item['class'];
    }

    public static function getAspects(): array
    {
        return self::$aspects;
    }
}