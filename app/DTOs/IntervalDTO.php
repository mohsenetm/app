<?php

namespace App\DTOs;

class IntervalDTO
{
    public function __construct(
        public int          $easyInterval = 0,
        public int          $goodInterval = 0,
        public int          $hardInterval = 0,
        public int          $againInterval = 0,
    ) {}
}
