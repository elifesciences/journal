<?php

namespace test\eLife\Journal\EventListener;

use eLife\Journal\EventListener\FragmentLinkRewriteSubscriber;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use test\eLife\Journal\AppKernelTestCase;

final class FragmentLinkRewriteSubscriberTest extends KernelTestCase
{
    use AppKernelTestCase;

    /**
     * @before
     */
    public function setUpKernel()
    {
        $this::bootKernel();
    }

    /**
     * @test
     */
    public function it_rewrites_fragment_links()
    {
        $subscriber = new FragmentLinkRewriteSubscriber($this::$kernel->getContainer()->get('router'));

        $request = Request::create('/content/1/e00001');
        $request->attributes->set('_route', 'article');
        $request->attributes->set('volume', '1');
        $request->attributes->set('id', '00001');
        $response = new Response('<html><body id="this-id">Foo <a href="#this-id">bar</a> <a href="#other-id">baz</a> qux</body>');

        $subscriber->onKernelResponse($this->createFilterResponseEvent($request, $response));

        $this->assertSame('<html><body id="this-id">Foo <a href="#this-id">bar</a> <a href="/content/1/e00001/figures#other-id">baz</a> qux</body>', $response->getContent());
    }

    private function createFilterResponseEvent(Request $request, Response $response, int $requestType = HttpKernelInterface::MASTER_REQUEST) : FilterResponseEvent
    {
        return new FilterResponseEvent($this::$kernel, $request, $requestType, $response);
    }
}
