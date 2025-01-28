<?php

use luoyue\aop\Attributes\parser\AspectParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class attributesTest extends TestCase
{

    #[Test]
    #[DataProvider('classesData')]
    public function matchesClassesTest(string $classes, $resultClass, $resultMethod)
    {
        $reflectionMethod = new ReflectionMethod(AspectParser::class, 'getMatchesClasses');
        $result = $reflectionMethod->invoke(null, $classes);
        $this->assertEquals($resultClass, $result['class']);
        $this->assertEquals($resultMethod, $result['method']);
    }

    public static function classesData(): array
    {
        return [
            'data1' => [TestCase::class . '::*', 'PHPUnit\\\\Framework\\\\TestCase', '.*'],
            'data2' => [TestCase::class . '::a*', 'PHPUnit\\\\Framework\\\\TestCase', 'a.*'],
            'data3' => [TestCase::class . '::*a', 'PHPUnit\\\\Framework\\\\TestCase', '.*a'],
            'data4' => [TestCase::class . '::a*a', 'PHPUnit\\\\Framework\\\\TestCase', 'a.*a'],
            'data5' => ['PHPUnit\\*\\test', 'PHPUnit\\\\[^\\\\]*\\\\test', '.*'],
            'data6' => ['PHPUnit\\**\\test', 'PHPUnit\\\\.*\\\\test', '.*'],
        ];
    }
}