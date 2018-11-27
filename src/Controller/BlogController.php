<?php

namespace App\Controller;

use App\Blog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends AbstractController
{
    public function article(string $slug, Blog $blog)
    {
        try {
            $article = $blog->getArticle($slug);
        } catch (\RuntimeException $e) {
            throw $this->createNotFoundException('Page not found!');
        }

        return $this->render('blog/article.html.twig', [
            'article' => $article,
        ]);
    }

    public function wrongArticleUrl($slug)
    {
        return new RedirectResponse('https://mnapoli.fr/' . $slug . '/', Response::HTTP_MOVED_PERMANENTLY);
    }
}
