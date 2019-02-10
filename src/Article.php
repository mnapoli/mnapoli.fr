<?php
declare(strict_types=1);

namespace App;

class Article
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var \DateTimeImmutable
     */
    public $date;

    /**
     * @var string
     */
    public $slug;

    /**
     * @var string
     */
    public $htmlContent;

    /**
     * @var string
     */
    public $extract;

    /**
     * @var bool
     */
    public $isPopular;

    /**
     * @var string|null
     */
    public $image;

    public function __construct(
        string $title,
        \DateTimeImmutable $date,
        string $slug,
        string $htmlContent,
        string $extract,
        bool $isPopular,
        ?string $image
    ) {
        $this->title = $title;
        $this->date = $date;
        $this->slug = $slug;
        $this->htmlContent = $htmlContent;
        $this->extract = $extract;
        $this->isPopular = $isPopular;
        $this->image = $image;
    }
}
