<?php

namespace eLife\Journal\Controller;

use OutOfBoundsException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DoiController extends Controller
{
    public function doiAction(string $doi) : Response
    {
        preg_match('~^10.7554/eLife\.([a-z0-9-]+)$~', $doi, $matches);

        $article = $this->get('elife.api_sdk.articles')
            ->get($matches[1])
            ->otherwise($this->mightNotExist())
            ->wait();

        return $this->redirectTo($this->get('router')->generate('article', [$article]));
    }

    public function subDoiAction(string $doi) : Response
    {
        $key = str_replace('/', '.', $doi);

        $cachedRedirect = $this->get('cache.doi')->getItem($key);

        if (!$cachedRedirect->isHit()) {
            foreach (['article', 'article-figures', 'article-peer-reviews'] as $route) {
                try {
                    $redirect = $this->findDoi($doi, $route);
                } catch (OutOfBoundsException $e) {
                    continue;
                }

                $cachedRedirect->set($redirect);
                $cachedRedirect->expiresAfter(60 * 60);

                $this->get('cache.doi')->save($cachedRedirect);

                break;
            }
        }

        if (!$cachedRedirect->get()) {
            throw new NotFoundHttpException("Cannot find an ID for the DOI $doi");
        }

        return $this->redirectTo($cachedRedirect->get());
    }

    private function findDoi(string $doi, string $routeName = 'article') : string
    {
        preg_match('~10.7554/eLife\.([a-z0-9-]+)(\..+)?~', $doi, $matches);

        $subRequest = Request::create($this->get('router')->generate($routeName, ['id' => $matches[1]], UrlGeneratorInterface::ABSOLUTE_URL));

        /** @var Response $text */
        $text = $this->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $crawler = new Crawler($text->getContent(), $subRequest->getUri());

        $crawler = $crawler->filter(".doi__link[href='https://doi.org/$doi']");
        while (count($crawler)) {
            $crawler = $crawler->parents();
            if (false !== strpos($crawler->attr('class'), 'additional-asset__access')) {
                // ID is in the preceding element rather than the parent.
                $crawler = $crawler->previousAll();
            }
            if ($crawler->attr('id')) {
                return $this->get('router')->generate($routeName, ['id' => $matches[1], '_fragment' => $crawler->attr('id')]);
            }
        }

        throw new OutOfBoundsException("Cannot find an ID for the DOI $doi");
    }

    private function redirectTo(string $uri) : Response
    {
        return new RedirectResponse($uri, Response::HTTP_SEE_OTHER);
    }
}
