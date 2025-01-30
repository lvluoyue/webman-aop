<?php

namespace Luoyue\aop\Attributes;

use LinFly\Annotation\AbstractAnnotationAttribute;
use Luoyue\aop\Attributes\parser\AspectParser;

/**
 * 切面类注解
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Aspect extends AbstractAnnotationAttribute
{

    public function __construct(?int $priority = null)
    {
        $this->setArguments(func_get_args());
    }

    public static function getParser(): array|string
    {
        return AspectParser::class;
    }
}