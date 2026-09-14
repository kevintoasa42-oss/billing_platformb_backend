<?php

namespace App\Context\V1\Modules\Menu\Domain\Models;

class Menu
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $route = null,
        public ?string $icon = null,
        public ?int $parent_id = null,
        public int $order = 0,
        /** @var Menu[] */
        public array $hijos = [],
    ) {}
}
