<?php

namespace luoyue\aop\Attributes;

use LinFly\Annotation\AbstractAnnotationAttribute;
use luoyue\aop\Attributes\parser\AspectParser;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Aspect extends AbstractAnnotationAttribute
{

    public function __construct() {
    }

    public static function getParser(): array|string
    {
        return AspectParser::class;
    }


}