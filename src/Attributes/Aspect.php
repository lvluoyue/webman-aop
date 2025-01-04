<?php

namespace luoyue\aop\Attributes;

use luoyue\aop\Attributes\Parser\AspectParser;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Aspect
{


    public function __construct() {
    }

    public static function getParser(): string|array
    {
        return AspectParser::class;
    }

}