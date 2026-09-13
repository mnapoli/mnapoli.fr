<?php

declare(strict_types=1);

namespace App;

use Carbon\CarbonImmutable;

class Post
{
    public function __construct(
        public string $title,
        public CarbonImmutable $date,
        public string $slug,
        public string $markdown,
        public string $htmlContent,
        public string $extract,
        public ?string $image,
    ) {}
}
