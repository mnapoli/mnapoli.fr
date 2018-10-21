<?php

namespace App\Controller;

use App\Blog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
}
