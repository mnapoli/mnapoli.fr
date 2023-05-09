<?php declare(strict_types=1);

namespace App;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;
use RuntimeException;
use Safe\Exceptions\FilesystemException;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use function Safe\file_get_contents;

/**
 * This is the class that reads and writes blog posts to Markdown files.
 */
class Blog
{
    private Parser $parser;

    public function __construct()
    {
        $environment = new Environment;
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new TableExtension);
        $environment->addRenderer(FencedCode::class, new FencedCodeRenderer);
        $environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer);

        $markdownConverter = new MarkdownConverter($environment);

        $this->parser = new Parser(
            markdownParser: new CommonMarkParser($markdownConverter),
        );
    }

    /**
     * List all the blog posts.
     *
     * @return Post[]
     * @throws FilesystemException
     */
    public function getPosts(): array
    {
        $posts = [];

        foreach (glob(__DIR__ . '/../posts/*.md') as $file) {
            $markdown = file_get_contents($file);
            $document = $this->parser->parse($markdown, false);
            $yaml = $document->getYAML();
            $date = $this->parseDate($yaml, $file);
            $slug = basename($file, '.md');
            $image = $yaml['image'] ?? null;

            // For the list we don't parse the full Markdown and HTML content, only the extract
            $extract = $this->readExtract($markdown);

            $posts[] = new Post($yaml['title'], $date, $slug, '', '', $extract, $image);
        }

        // Sort articles by date
        usort($posts, function (Post $post1, Post $post2) {
            // spaceship operator!!
            return $post2->date <=> $post1->date;
        });

        return $posts;
    }

    /**
     * Get a single blog post.
     *
     * @throws FilesystemException
     */
    public function getPost(string $slug): Post
    {
        $file = __DIR__ . '/../posts/' . $slug . '.md';
        if (!file_exists($file)) {
            throw new NotFoundHttpException('Not found');
        }

        $markdown = file_get_contents($file);
        $document = $this->parser->parse($markdown);
        $originalDocument = $this->parser->parse($markdown, false);
        $yaml = $document->getYAML();
        $date = $this->parseDate($yaml, $file);
        $image = $yaml['image'] ?? null;

        $extract = $this->readExtract($markdown);

        return new Post($yaml['title'], $date, $slug, $originalDocument->getContent(), $document->getContent(), $extract, $image);
    }

    public function createPost(string $slug, string $title): void
    {
        $file = __DIR__ . '/../posts/' . $slug . '.md';
        if (file_exists($file)) {
            throw new RuntimeException("The file $file already exists");
        }

        $header = [
            'title' => $title,
            'date' => (new DateTimeImmutable)->format('Y-m-d H:i'),
        ];
        $this->dumpPost($file, $header, '');
    }

    public function editPost(string $slug, string $markdown, string $title, ?string $image): void
    {
        $file = __DIR__ . '/../posts/' . $slug . '.md';
        if (!file_exists($file)) {
            throw new RuntimeException('Not found');
        }
        $fileContent = file_get_contents($file);
        $originalDocument = $this->parser->parse($fileContent, false);

        // Keep the original YAML header and update only some fields
        $header = $originalDocument->getYAML();
        $header['title'] = $title;
        if ($image) {
            $header['image'] = $image;
        } else {
            unset($header['image']);
        }

        // Re-dump the file
        $this->dumpPost($file, $header, $markdown);
    }

    private function parseDate(array $yaml, string $file): CarbonImmutable
    {
        try {
            return new CarbonImmutable($yaml['date']);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to parse the date for post ' . $file);
        }
    }

    private function readExtract(string $markdown): string
    {
        $morePosition = strpos($markdown, "<!--more-->\n");
        if ($morePosition !== false) {
            $markdownExtract = trim(substr($markdown, 0, $morePosition));
        } else {
            $markdownExtract = '';
        }
        return $this->parser->parse($markdownExtract)->getContent();
    }

    /**
     * Turns Markdown to HTML to preview a blog post.
     *
     * Why not compile Markdown -> HTML in JavaScript on the client side instead?
     * So that we have exactly the same rendering engine.
     */
    public function preview(string $markdown): string
    {
        $document = $this->parser->parse($markdown, true);
        return $document->getContent();
    }

    private function dumpPost(string $file, array $yaml, string $markdown): void
    {
        $content = "---\n"
            . Yaml::dump($yaml)
            . "---\n\n"
            . $markdown;

        // Make sure we store LF line endings
        $content = preg_replace('~\r\n?~', "\n", $content);
        // Add a trailing empty line
        $content = rtrim($content) . "\n";

        file_put_contents($file, $content);
    }
}
