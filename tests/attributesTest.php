<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class attributesTest extends TestCase
{

    #[Test]
    #[DataProvider('annotationData')]
    public function annotationTest(string $class, $throws): void
    {
        $throws && $this->expectException($throws);
        new $class($class !== \Luoyue\aop\Attributes\Aspect::class ? '' : 1);
        $this->assertTrue(true);
    }

    public static function annotationData()
    {
        return [
            'After' => [
                'class' => \Luoyue\aop\Attributes\After::class,
                'throws' => \InvalidArgumentException::class,
            ],
            'AfterReturning' => [
                'class' => \Luoyue\aop\Attributes\AfterReturning::class,
                'throws' => \InvalidArgumentException::class,
            ],
            'AfterThrowing' => [
                'class' => \Luoyue\aop\Attributes\AfterThrowing::class,
                'throws' => \InvalidArgumentException::class,
            ],
            'Before' => [
                'class' => \Luoyue\aop\Attributes\Before::class,
                'throws' => \InvalidArgumentException::class,
            ],
            'Around' => [
                'class' => \Luoyue\aop\Attributes\Around::class,
                'throws' => \InvalidArgumentException::class,
            ],
            'Aspect' => [
                'class' => \Luoyue\aop\Attributes\Aspect::class,
                'throws' => '',
            ],
        ];
    }

    #[Test]
    #[DataProvider('classesData')]
    public function matchesClassesTest(string $classes, string $resultClass, string $resultMethod): void
    {
        [$class, $method] = array_values(\Luoyue\aop\AopUtils::getMatchesClasses($classes));
        $this->assertEquals($resultClass, $class, '类名不匹配');
        $this->assertEquals($resultMethod, $method, '方法名不匹配');
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