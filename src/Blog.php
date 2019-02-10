<?php
declare(strict_types=1);

namespace App;

use Mni\FrontYAML\Parser;

class Blog
{
    /**
     * @var Parser
     */
    private $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * @return Article[]
     */
    public function getArticles(): array
    {
        $articles = [];
        foreach (glob(__DIR__ . '/../articles/*.md') as $file) {
            $document = $this->parser->parse(file_get_contents($file), false);
            $yaml = $document->getYAML();
            $date = $this->parseDate($yaml, $file);
            $slug = basename($file, '.md');
            $isPopular = $yaml['isPopular'] ?? false;

            $articles[] = new Article($yaml['title'], $date, $slug, '', '', $isPopular, null);
        }
        usort($articles, function (Article $article1, Article $article2) {
            return $article2->date <=> $article1->date;
        });
        return $articles;
    }

    public function getArticle(string $slug): Article
    {
        $file = __DIR__ . '/../articles/' . $slug . '.md';
        if (!file_exists($file)) {
            throw new \RuntimeException('Not found');
        }

        $markdown = file_get_contents($file);
        $document = $this->parser->parse($markdown);
        $yaml = $document->getYAML();
        $date = $this->parseDate($yaml, $file);
        $isPopular = $yaml['isPopular'] ?? false;
        $image = $yaml['image'] ?? null;

        $morePosition = strpos($markdown, '<!--more-->');
        if ($morePosition !== false) {
            $markdownExtract = trim(substr($markdown, 0, $morePosition));
        } else {
            $markdownExtract = '';
        }
        $extract = $this->parser->parse($markdownExtract)->getContent();

        return new Article($yaml['title'], $date, $slug, $document->getContent(), $extract, $isPopular, $image);
    }

    private function parseDate(array $yaml, string $file): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($yaml['date']);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Unable to parse the date for article ' . $file);
        }
    }
}
