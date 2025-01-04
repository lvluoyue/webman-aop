<?php

namespace luoyue\aop\Aop\Attributes;

use LinFly\Annotation\AbstractAnnotationAttribute;
use luoyue\aop\Aop\Attributes\Parser\AspectParser;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Aspect extends AbstractAnnotationAttribute
{
    public function __construct() {
    }

    public static function getParser(): string|array
    {
        return AspectParser::class;
    }

}