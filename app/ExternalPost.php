<?php declare(strict_types=1);

namespace App;

use Carbon\CarbonImmutable;

class ExternalPost
{
    public function __construct(
        public string $title,
        public CarbonImmutable $date,
        public string $url,
        public string $extract = '',
    ) {
    }
}
