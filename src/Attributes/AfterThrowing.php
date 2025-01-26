<?php

namespace luoyue\aop\Attributes;

use LinFly\Annotation\AbstractAnnotationAttribute;
use luoyue\aop\Attributes\parser\AspectParser;

#[\Attribute(\Attribute::TARGET_METHOD)]
class AfterThrowing extends AbstractAnnotationAttribute
{

    public function __construct(array|string $classes)
    {
        $this->setArguments(func_get_args());
    }

    public static function getParser(): array|string
    {
        return AspectParser::class;
    }

}