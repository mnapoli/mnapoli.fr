<?php declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

class Article
{
    public string $title;
    public DateTimeImmutable $date;
    public string $slug;
    public string $htmlContent;
    public string $extract;
    public bool $isPopular;
    public ?string $image;
    public array $tags;

    public function __construct(
        string $title,
        DateTimeImmutable $date,
        string $slug,
        string $htmlContent,
        string $extract,
        bool $isPopular,
        ?string $image,
        array $tags
    ) {
        $this->title = $title;
        $this->date = $date;
        $this->slug = $slug;
        $this->htmlContent = $htmlContent;
        $this->extract = $extract;
        $this->isPopular = $isPopular;
        $this->image = $image;
        $this->tags = $tags;
    }
}
