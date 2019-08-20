<?php

namespace App\Controller;

use App\Blog;
use FeedIo\Feed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
    public function home(Request $request, Blog $blog)
    {
        $acceptLanguage = $request->headers->get('Accept-Language');
        $language = (strpos($acceptLanguage, 'fr') === 0) ? 'fr' : 'en';

        return $this->render('home.html.twig', [
            'language' => $language,
            'articles' => $blog->getArticles(),
        ]);
    }

    public function feed(Blog $blog)
    {
        $feed = new Feed;
        $feed->setTitle('Matthieu Napoli\'s blog');
        $feed->setPublicId('https://mnapoli.fr/');

        $lastUpdate = null;

        foreach ($blog->getArticles() as $article) {
            $item = $feed->newItem();
            $item->setTitle($article->title);
            $articleDate = new \DateTime($article->date->format('Y-m-d H:i'));
            $lastUpdate = max($lastUpdate, $articleDate);
            $item->setLastModified($articleDate);
            $item->setLink('https://mnapoli.fr/' . $article->slug . '/');
            $item->setPublicId('https://mnapoli.fr/' . $article->slug . '/');
            $item->setAuthor(($item->newAuthor())->setName('Matthieu Napoli'));

            $feed->add($item);
        }

        $feed->setLastModified($lastUpdate);

        $feedIo = \FeedIo\Factory::create()->getFeedIo();
        $psrResponse = $feedIo->getPsrResponse($feed, 'atom');
        return new Response($psrResponse->getBody()->getContents(), 200, $psrResponse->getHeaders());
    }

    public function talks()
    {
        return $this->render('talks.html.twig');
    }

    public function projects()
    {
        return $this->render('projects.html.twig');
    }
}
