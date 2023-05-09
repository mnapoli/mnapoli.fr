<?php declare(strict_types=1);

namespace App;

use Carbon\CarbonImmutable;

class Post
{
    public string $title;
    public CarbonImmutable $date;
    public string $slug;
    public string $markdown;
    public string $htmlContent;
    public string $extract;
    public ?string $image;

    public function __construct(
        string $title,
        CarbonImmutable $date,
        string $slug,
        string $markdown,
        string $htmlContent,
        string $extract,
        ?string $image
    ) {
        $this->title = $title;
        $this->date = $date;
        $this->slug = $slug;
        $this->markdown = $markdown;
        $this->htmlContent = $htmlContent;
        $this->extract = $extract;
        $this->image = $image;
    }
}
