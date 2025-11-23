<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OptimizedImage extends Component
{
    public function __construct(
        public string $src,
        public string $alt = '',
        public ?string $class = null,
        public ?int $width = null,
        public ?int $height = null,
        public bool $lazy = true,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.optimized-image');
    }
}
