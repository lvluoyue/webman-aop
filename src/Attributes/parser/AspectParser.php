<?php

namespace luoyue\aop\Attributes\parser;

use LinFly\Annotation\Contracts\IAnnotationParser;
use luoyue\aop\Aspect;

/**
 * 切面类注解处理
 */
class AspectParser implements IAnnotationParser
{

    public static function process(array $item): void
    {
        Aspect::getInstance()->addAspect($item['class'], $item['parameters']['priority']);
    }

}