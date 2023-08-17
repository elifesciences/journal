<?php

namespace eLife\Journal\Controller;

use DateTimeImmutable;
use eLife\ApiClient\Exception\BadResponse;
use eLife\ApiSdk\Model\Image;
use eLife\Journal\Exception\EarlyResponse;
use eLife\Journal\Helper\CanCheckAuthorization;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\exception_for;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use function preg_match;

abstract class Controller implements ContainerAwareInterface
{
    use CanCheckAuthorization;
    use CanConvertContent;

    /**
     * @var ContainerInterface
     */
    private $container;

    final public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    final protected function get(string $id)
    {
        return $this->container->get($id);
    }

    final protected function getParameter(string $id)
    {
        return $this->container->getParameter($id);
    }

    final protected function has(string $id) : bool
    {
        return $this->container->has($id);
    }

    final protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->get('elife.journal.view_model.converter');
    }

    final protected function getAuthorizationChecker() : AuthorizationCheckerInterface
    {
        return $this->get('security.authorization_checker');
    }

    final protected function render(ViewModel ...$viewModels) : string
    {
        return $this->get('elife.patterns.pattern_renderer')->render(...$viewModels);
    }

    final protected function willRender() : callable
    {
        return function (ViewModel $viewModel) {
            return $this->render($viewModel);
        };
    }

    final protected function checkSlug(Request $request, callable $toSlugify) : callable
    {
        return function ($object) use ($request, $toSlugify) {
            $slug = $request->attributes->get('_route_params')['slug'];
            $correctSlug = $this->get('elife.slugify')->slugify($toSlugify($object));

            if ($slug !== $correctSlug) {
                $route = $request->attributes->get('_route');
                $routeParams = $request->attributes->get('_route_params');
                $routeParams['slug'] = $correctSlug;
                $url = $this->get('router')->generate($route, $routeParams);
                if ($queryString = $request->server->get('QUERY_STRING')) {
                    $url .= "?{$queryString}";
                }

                throw new EarlyResponse(new RedirectResponse($url, Response::HTTP_MOVED_PERMANENTLY));
            }

            return $object;
        };
    }

    final protected function mightNotExist() : callable
    {
        return function ($reason) {
            if ($reason instanceof BadResponse) {
                switch ($reason->getResponse()->getStatusCode()) {
                    case Response::HTTP_GONE:
                    case Response::HTTP_NOT_FOUND:
                        throw new HttpException($reason->getResponse()->getStatusCode(), $reason->getMessage(), $reason);
                }
            }

            return rejection_for($reason);
        };
    }

    final protected function softFailure(string $message = null, $default = null) : callable
    {
        return function ($reason) use ($message, $default) {
            //return new RejectedPromise($reason);
            $e = exception_for($reason);

            if (false === $e instanceof HttpException) {
                $this->get('elife.logger')->error($message ?? $e->getMessage(), ['exception' => $e]);
            }

            return $default;
        };
    }

    final protected function createSubsequentPage(Request $request, array $arguments) : Response
    {
        $arguments['listing'] = $arguments['listing']
            ->otherwise($this->mightNotExist());

        if ($request->isXmlHttpRequest()) {
            $response = new Response($this->render($arguments['listing']->wait()));
        } else {
            $arguments['contentHeader'] = $arguments['paginator']
                ->then(function (Paginator $paginator) {
                    return new ViewModel\ContentHeaderSimple(
                        $paginator->getTitle(),
                        sprintf('Page %s of %s', number_format($paginator->getCurrentPage()), number_format(count($paginator)))
                    );
                });

            $template = $arguments['listing']->wait() instanceof ViewModel\GridListing ? '::pagination-grid.html.twig' : '::pagination.html.twig';

            $response = new Response($this->get('templating')->render($template, $arguments));
        }

        $response->headers->set('Vary', 'X-Requested-With', false);

        return $response;
    }

    final protected function ifFormSubmitted(Request $request, FormInterface $form, callable $onValid, bool $earlyResponse = true, bool $honeypotOnly = false)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $return = $onValid();

                if ($earlyResponse) {
                    throw new EarlyResponse(new RedirectResponse($request->getUri()));
                } else {
                    return $return;
                }
            }

            $this->processFormErrors($request, $form, $honeypotOnly);
        }
    }

    private function processFormErrors(Request $request, FormInterface $form, bool $honeypotOnly = false)
    {
        $statusMessages = [];
        $honeypot = false;

        if (count($form->getErrors()) > 0) {
            foreach ($form->getErrors() as $error) {
                if ($error->getMessage() === $form->getConfig()->getOption('honeypot_message')) {
                    $this->get('monolog.logger.honeypot')->info('Honeypot field filled in', ['extra' => ['request' => $request]]);
                    $honeypot = true;
                }

                if (!$honeypotOnly || $honeypot) {
                    $statusMessages[ViewModel\InfoBar::TYPE_ATTENTION][] = $error->getMessage();
                    $honeypot = false;
                }
            }
        } elseif (!$honeypotOnly) {
            $statusMessages[ViewModel\InfoBar::TYPE_ATTENTION][] = 'There were problems submitting the form.';
        }

        foreach ($statusMessages as $level => $messages) {
            foreach ($messages as $message) {
                $this->get('session')
                    ->getFlashBag()
                    ->add($level, $message);
            }
        }
    }

    private function getCallsToAction(Request $request) : array
    {
        return array_map(
            function (array $callToAction) : ViewModel\CallToAction {
                return new ViewModel\CallToAction(
                    $callToAction['id'],
                    $this->convertTo(
                        $this->get('elife.api_sdk.serializer')->denormalize($callToAction['image'], Image::class),
                        Picture::class,
                        ['width' => 80, 'height' => 80]
                    ),
                    $callToAction['text'],
                    ViewModel\Button::link($callToAction['button']['text'], $callToAction['button']['path']),
                    $callToAction['needsJs'] ?? false,
                    !empty($callToAction['dismissible']['cookieExpires']) ? new DateTimeImmutable($callToAction['dismissible']['cookieExpires']) : null
                );
            },
            array_slice(array_filter(
                // Limit of one call to action until we resolve issues of multiple calls to action.
                $this->getParameter('calls_to_action'),
                function (array $callToAction) use ($request) : bool {
                    if (isset($callToAction['from']) && time() < $callToAction['from']) {
                        return false;
                    }

                    if (
                        isset($callToAction['path'])
                        &&
                        !preg_match("~{$callToAction['path']}~", $request->getPathInfo())
                    ) {
                        return false;
                    }

                    return true;
                }
            ), 0, 1)
        );
    }

    final protected function magazinePageArguments($arguments, $type) {
        return [
            'hasSocialMedia' => true,
            'socialMediaSharersLinks' => all(['item' => $arguments['item']])
                ->then(function (array $parts) use ($type) {
                    $context['variant'] = $type;
                    return $this->convertTo($parts['item'], ViewModel\SocialMediaSharersNew::class, $context);
                }),
            'contextualDataMetrics' => isset($arguments['pageViews']) ? all(['pageViews' => $arguments['pageViews']])
                ->then(function (array $parts) {
                    /** @var int|null $pageViews */
                    $pageViews = $parts['pageViews'];

                    $metrics = [];

                    if (null !== $pageViews && $pageViews > 0) {
                        $metrics[] = sprintf('<span class="contextual-data__counter">%s</span> %s', number_format($pageViews), 'views');
                    }

                    return $metrics;
            }) : null

        ];
    }

    final protected function defaultPageArguments(Request $request, PromiseInterface $item = null) : array
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $profile = $this->get('elife.api_sdk.profiles')->get($user->getUsername())
                ->otherwise(function ($reason) use ($user) {
                    $e = exception_for($reason);
                    $this->get('elife.logger')->error("Logging user {$user->getUsername()} out due to {$e->getMessage()}", ['exception' => $e]);

                    throw new EarlyResponse(new RedirectResponse($this->get('router')->generate('log-out', [], UrlGeneratorInterface::ABSOLUTE_URL)));
                });
        }

        return [
            'header' => all(['item' => promise_for($item), 'profile' => promise_for($profile ?? null)])
                ->then(function (array $parts) {
                    return $this->get('elife.journal.view_model.factory.site_header')->createSiteHeader($parts['item']);
                }),
            'infoBars' => [],
            'callsToAction' => $this->getCallsToAction($request),
            'emailCta' => new ViewModel\EmailCta(
                'Be the first to read new articles from eLife',
                ViewModel\Button::link('Sign up for email alerts', $this->get('router')->generate('content-alerts')),
                $this->get('router')->generate('privacy'),
                'Privacy notice'
            ),
            'footer' => $this->get('elife.journal.view_model.factory.footer')->createFooter(),
            'user' => $user ?? null,
            'item' => $item,
        ];
    }

    final protected function simplePageArguments(Request $request, PromiseInterface $item = null) : array
    {
        return [
            'header' => new ViewModel\SiteHeaderTitle($this->get('router')->generate('home'), true, true, true),
            'infoBars' => [],
            'callsToAction' => $this->getCallsToAction($request),
            'emailCta' => null,
            'footer' => null,
            'user' => $user ?? null,
            'item' => $item,
        ];
    }
}
