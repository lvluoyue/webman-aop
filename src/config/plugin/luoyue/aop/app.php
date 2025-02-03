<?php

return [
    'enable' => true,
    'proxyPath' => '/runtime/cache/aop',
    //扫描后是否立即重载（开启后其他插件无法正常使用）
    'reload' => true,
    // 切入点扫描路径，不建议扫描vendor目录
    'scans' => [
        'app',
    ],
    // 切入的切面类
    'aspect' => [
    ],
];
