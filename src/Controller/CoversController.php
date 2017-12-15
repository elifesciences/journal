<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\Button;
use eLife\Journal\ViewModel\Paragraph;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\Picture;
use GuzzleHttp\Client;


final class CoversController extends Controller
{
    public function coversAction(Request $request, string $id) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'eLife Covers Landing Page';

        $client = new Client(['base_uri' => 'http://prod--personalised-covers.elife.internal/personalised-covers/']);
        $response = $client->request('GET', $id);
        $urls = json_decode($response->getBody()->getContents());
        $arguments['body'] = [
            $this->toStyleGuideImage('Full-Color Vertical', 'cover-illo.png'),
            new Paragraph('<br> You can download a poster featuring your latest paper below. It\'s
highlighted on an imitation of an eLife journal cover.'),
            new Paragraph('Thank you for publishing your work with us.'),
            Button::link('DOWNLOAD A4', $urls->formats->a4, Button::SIZE_MEDIUM),
            Button::link('DOWNLOAD LETTER', $urls->formats->letter, Button::SIZE_MEDIUM),

        ];

        return new Response($this->get('templating')->render('::covers.html.twig', $arguments));
    }

    private function toStyleGuideImage(string $name, string $filename) : Picture
    {
        $sourceUri = "../assets/images/logos/".$filename;

        return new Picture([], new Image($sourceUri, [], $name));
    }
}
