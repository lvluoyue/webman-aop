<?php

namespace luoyue\aop;

use SplPriorityQueue;

class queue extends SplPriorityQueue {
    public function compare($priority1, $priority2): int
    {
        if ($priority1 === $priority2) {
            return 0;
        }
        // 数字优先
        if (is_numeric($priority1) && !is_numeric($priority2)) {
            return 1;
        }
        if (!is_numeric($priority1) && is_numeric($priority2)) {
            return -1;
        }
        return $priority1 <=> $priority2;
    }
}