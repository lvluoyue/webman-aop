# webman-aop

![Packagist Version](https://img.shields.io/packagist/v/luoyue/webman-aop)
![Packagist License](https://img.shields.io/packagist/l/luoyue/webman-aop)
![Packagist Dependency Version](https://img.shields.io/packagist/dependency-v/luoyue/webman-aop/php)
![Packagist Downloads](https://img.shields.io/packagist/dt/luoyue/webman-aop)
![Packagist Stars](https://img.shields.io/packagist/stars/luoyue/webman-aop)

## 安装
```
composer require luoyue/webman-aop
```

AOP 相关配置
config/plugin/luoyue/aop/app.php 配置
```
<?php
return [
    'enable' => true,
    // 切入对象的扫描路径
    'scans' => [
        'app',
    ],
    // 切入的切面类
    'aspect' => [
    ],
];
```
首先让我们编写待切入类 
```
<?php
namespace app\service;

class UserService
{
    public function info()
    {
        echo 'UserService info' . PHP_EOL;
    }
}
```
其次新增对应的 UserAspect（Aspect注解必须加上）

```

namespace app\aspect;

use app\service\UserService;
use luoyue\aop\AbstractAspect;
use luoyue\aop\Attributes\Aspect;
use luoyue\aop\interfaces\ProceedingJoinPointInterface;

/**
 * Class UserAspect
 * @package app\aspect
 */
 #[Aspect]
class UserAspect extends AbstractAspect
{
    public array $classes = [
        UserService::class . '::info',
    ];

    /**
     * @param ProceedingJoinPointInterface $entryClass
     * @return mixed
     */
    public function process(ProceedingJoinPointInterface $entryClass)
    {
        var_dump('UserAspect before');
        $res = $entryClass->process();
        var_dump('UserAspect after');
        return $res;
    }
}
```

测试,在app\controller\Index 修改代码 eg：

```

    public function index()
    {
        /** @var UserService $userService */
        $userService = load(UserService::class);
        $userService->info();
    }

```
php start.php start， 命令行输出结果：
```
UserAspect before 
UserService info
UserAspect after 
```

## 切入顺序
如果有多个切面类对同一个类方法进行切入， 会按照配置文件中顺序执行

容器 load 函数在 helpers.php 定义如下 eg:
```
/**
 *  加载容器的对象
 */
if (! function_exists('load')) {
    function load(string $class)
    {
        return \support\Container::get($class);
    }
}
```

使用php-di依赖可以使用注解在容器中注入对象达到切入的目的，例如：


```
<?php

namespace app\controller;

use app\service\UserService;
use DI\Attribute\Inject;

class Index
{
    #[Inject]
    public UserService $userService;

    public function index()
    {
        $this->userService->info();
    }
}
```

## clsses表达式
目前支持的表达式有：
- ***匹配一个方法***：类名::方法名
- ***匹配所有方法***：类名::*

其他写法暂不支持，随时欢迎您提交pr