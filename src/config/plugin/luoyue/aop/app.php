<?php

return [
    'enable' => true,
    'proxyPath' => '/runtime/cache/aop',
    // 切入点扫描路径，不建议扫描vendor目录
    'scans' => [
        'app',
    ],
    // 切入的切面类
    'aspect' => [
    ],
];
