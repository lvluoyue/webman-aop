<?php

namespace luoyue\aop\Attributes;

use LinFly\Annotation\AbstractAnnotationAttribute;
use luoyue\aop\Attributes\parser\AspectParser;

/**
 * 切面类注解
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Aspect extends AbstractAnnotationAttribute
{
    public static function getParser(): array|string
    {
        return AspectParser::class;
    }
}