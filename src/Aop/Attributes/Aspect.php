<?php

namespace yzh52521\aop\Aop\Attributes;

use LinFly\Annotation\AbstractAnnotationAttribute;
use yzh52521\aop\Aop\Attributes\Parser\AspectParser;

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