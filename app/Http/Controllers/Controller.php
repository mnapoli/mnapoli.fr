<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Blog;
use App\ExternalPost;
use App\Post;
use Carbon\CarbonImmutable;
use FeedIo\Feed;
use FeedIo\FeedIo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Safe\Exceptions\FilesystemException;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private Blog $blog;

    public function __construct()
    {
        $this->blog = new Blog();
    }

    public function home()
    {
        return view('home', [
            'home' => true,
            'posts' => $this->getAllPosts(),
        ]);
    }

    public function feed(Blog $blog)
    {
        $feed = new Feed;
        $feed->setTitle('Matthieu Napoli\'s blog');
        $feed->setPublicId('https://mnapoli.fr/');

        $lastUpdate = null;

        foreach ($this->getAllPosts() as $article) {
            $item = $feed->newItem();
            $item->setTitle($article->title);
            $articleDate = new \DateTime($article->date->format('Y-m-d H:i'));
            $lastUpdate = max($lastUpdate, $articleDate);
            $item->setLastModified($articleDate);
            if ($article instanceof Post) {
                $item->setLink('https://mnapoli.fr/' . $article->slug . '/');
                $item->setPublicId('https://mnapoli.fr/' . $article->slug . '/');
            } else {
                $item->setLink($article->url);
                $item->setPublicId($article->url);
            }
            $item->setAuthor(($item->newAuthor())->setName('Matthieu Napoli'));

            $feed->add($item);
        }

        $feed->setLastModified($lastUpdate);

        $feedId = new FeedIo;
        return new \Illuminate\Http\Response($feedId->toAtom($feed), 200, [
            'Content-Type' => 'application/atom+xml',
        ]);
    }

    public function articles()
    {
        return view('articles', [
            'posts' => $this->getAllPosts(),
        ]);
    }

    public function projects()
    {
        return view('projects');
    }

    public function speaking()
    {
        return view('speaking');
    }

    public function post(Request $request, string $slug)
    {
        if (str_ends_with($request->getPathInfo(), '/')) {
            return redirect("/$slug", Response::HTTP_MOVED_PERMANENTLY);
        }

        return view('post', [
            'post' => $this->blog->getPost($slug),
        ]);
    }

    /**
     * @return array<Post|ExternalPost>
     */
    private function getAllPosts(): array
    {
        $posts = $this->blog->getPosts();
        $externalPosts = [
            new ExternalPost(
                title: 'Bref 1.0 is released 🎉',
                date: new CarbonImmutable('2020-11-16'),
                url: 'https://bref.sh/docs/news/01-bref-1.0.html',
                extract: 'Celebrating 1 billion executions per month',
            ),
            new ExternalPost(
                title: 'Static websites on AWS — Designing Lift',
                date: new CarbonImmutable('2021-04-16'),
                url: 'https://medium.com/serverless-transformation/static-websites-on-aws-designing-lift-1db94574ba3b',
            ),
            new ExternalPost(
                title: 'Serverless queues and workers — Designing Lift',
                date: new CarbonImmutable('2021-04-30'),
                url: 'https://medium.com/serverless-transformation/serverless-queues-and-workers-designing-lift-d870afdba867',
            ),
            new ExternalPost(
                title: 'Announcing Serverless Framework v3 Beta',
                date: new CarbonImmutable('2021-11-16'),
                url: 'https://www.serverless.com/blog/serverless-framework-v3-beta',
            ),
            new ExternalPost(
                title: 'Improved SQS batch error handling with AWS Lambda',
                date: new CarbonImmutable('2021-11-30'),
                url: 'https://www.serverless.com/blog/improved-sqs-batch-error-handling-with-aws-lambda',
            ),
            new ExternalPost(
                title: 'Serverless Framework v3 is live!',
                date: new CarbonImmutable('2022-01-27'),
                url: 'https://www.serverless.com/blog/serverless-framework-v3-is-live',
            ),
            new ExternalPost(
                title: 'AWS Lambda Function URLs with Serverless Framework',
                date: new CarbonImmutable('2022-04-06'),
                url: 'https://www.serverless.com/blog/aws-lambda-function-urls-with-serverless-framework',
            ),
            new ExternalPost(
                title: 'Introducing multi-service deployments via Serverless Framework Compose',
                date: new CarbonImmutable('2022-04-20'),
                url: 'https://www.serverless.com/blog/serverless-framework-compose-multi-service-deployments',
            ),
            new ExternalPost(
                title: 'Bref 2.0 is released 🎉',
                date: new CarbonImmutable('2023-03-03'),
                url: 'https://bref.sh/docs/news/02-bref-2.0.html',
                extract: 'Celebrating 10 billion executions per month',
            ),
            new ExternalPost(
                title: 'Serverless Laravel applications with AWS Lambda and PlanetScale',
                date: new CarbonImmutable('2023-05-03'),
                url: 'https://planetscale.com/blog/serverless-laravel-app-aws-lambda-bref-planetscale',
            ),
        ];
        $allPosts = array_merge($posts, $externalPosts);
        usort($allPosts, fn($post1, $post2) => $post2->date <=> $post1->date);
        return $allPosts;
    }
}
