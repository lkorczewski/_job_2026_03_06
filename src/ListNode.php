<?php

declare(strict_types=1);

namespace App;

final class ListNode
{
    public function __construct(
        public int | string $value,
        public ?ListNode $next = null
    ) {
    }
}
