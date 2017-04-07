<?php

namespace eLife\Journal\Controller;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\LabsExperiment;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\Helper\Callback;
use eLife\Journal\ViewModel\EmptyListing;
use eLife\Patterns\ViewModel\ArchiveNavLink;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\BlockLink;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\FormLabel;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Select;
use eLife\Patterns\ViewModel\SelectNav;
use eLife\Patterns\ViewModel\SelectOption;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UnexpectedValueException;
use function GuzzleHttp\Promise\all;

final class ArchiveController extends Controller
{
    public function indexAction(Request $request) : Response
    {
        $year = $request->query->get('year');

        $this->validateArchiveYear($year);

        return new RedirectResponse(
            $this->get('router')->generate('archive-year', ['year' => $year]),
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    public function yearAction(Request $request, int $year) : Response
    {
        $this->validateArchiveYear($year);

        $arguments = $this->defaultPageArguments($request);

        $years = [];
        for ($yearOption = 2012; $yearOption <= $this->getMaxYear(); ++$yearOption) {
            $years[] = new SelectOption($yearOption, $yearOption, $yearOption === $year);
        }

        $months = [];
        foreach (range($this->getMinMonth($year), $this->getMaxMonth($year)) as $month) {
            $starts = DateTimeImmutable::createFromFormat('j n Y H:i:s', "1 $month $year 00:00:00", new DateTimeZone('Z'));
            $ends = $starts->setDate((int) $starts->format('Y'), (int) $starts->format('n'), (int) $starts->format('t'))->setTime(23, 59, 59);

            $months[$month] = $this->get('elife.api_sdk.covers')
                ->sortBy('page-views')
                ->startDate($starts)
                ->endDate($ends)
                ->useDate('published')
                ->slice(0, 4)
                ->otherwise($this->softFailure('Failed to load cover articles for '.$starts->format('F Y')));
        }

        $arguments['title'] = $year;

        $arguments['contentHeader'] = ContentHeaderNonArticle::archive(
            'Monthly archive',
            false,
            new SelectNav(
                $this->get('router')->generate('archive'),
                new Select('year', $years, new FormLabel('Archive year', 'year', true)),
                Button::form('Go', Button::TYPE_SUBMIT, 'go', Button::SIZE_EXTRA_SMALL)
            )
        );

        $arguments['months'] = all($months)
            ->then(function (array $months) use ($year) {
                return GridListing::forArchiveNavLinks(array_map(function (Sequence $covers = null, int $month) use ($year) {
                    $date = DateTimeImmutable::createFromFormat('j n Y', "1 $month $year", new DateTimeZone('Z'))->setTime(0, 0, 0);

                    $link = new Link($date->format('F Y'), $this->get('router')->generate('archive-month', ['year' => $year, 'month' => strtolower($date->format('F'))]));

                    if (!$covers || $covers->isEmpty()) {
                        return ArchiveNavLink::basic(new BlockLink($link));
                    }

                    return ArchiveNavLink::withLinks(
                        new BlockLink(
                            $link,
                            new BackgroundImage(
                                $covers[0]->getBanner()->getSize('2:1')->getImage(900),
                                $covers[0]->getBanner()->getSize('2:1')->getImage(1800)
                            )
                        ),
                        'Cover articles',
                        $covers->map(function (Cover $cover) {
                            $item = $cover->getItem();

                            if ($item instanceof ArticleVersion) {
                                return new Link($cover->getTitle(), $this->get('router')->generate('article', ['id' => $item->getId()]));
                            } elseif ($item instanceof BlogArticle) {
                                return new Link($cover->getTitle(), $this->get('router')->generate('inside-elife-article', ['id' => $item->getId()]));
                            } elseif ($item instanceof Collection) {
                                return new Link($cover->getTitle(), $this->get('router')->generate('collection', ['id' => $item->getId()]));
                            } elseif ($item instanceof Interview) {
                                return new Link($cover->getTitle(), $this->get('router')->generate('interview', ['id' => $item->getId()]));
                            } elseif ($item instanceof LabsExperiment) {
                                return new Link($cover->getTitle(), $this->get('router')->generate('labs-experiment', ['number' => $item->getNumber()]));
                            } elseif ($item instanceof PodcastEpisode) {
                                return new Link($cover->getTitle(), $this->get('router')->generate('podcast-episode', ['number' => $item->getNumber()]));
                            }

                            throw new UnexpectedValueException('Unexpected type '.get_class($item));
                        })->toArray()
                    );
                }, array_values($months), array_keys($months)), 'Monthly archive');
            });

        return new Response($this->get('templating')->render('::archive-year.html.twig', $arguments));
    }

    public function monthAction(Request $request, int $year, string $month) : Response
    {
        $starts = DateTimeImmutable::createFromFormat('j F Y H:i:s', "1 $month $year 00:00:00", new DateTimeZone('Z'));

        if (!$starts) {
            throw new NotFoundHttpException('Unknown month '.$month);
        }

        $this->validateArchiveYear($year, $starts->format('n'));

        $ends = $starts->setDate((int) $starts->format('Y'), (int) $starts->format('n'), (int) $starts->format('t'))->setTime(23, 59, 59);

        $covers = $this->get('elife.api_sdk.covers')
            ->sortBy('page-views')
            ->startDate($starts)
            ->endDate($ends)
            ->useDate('published')
            ->slice(0, 4)
            ->otherwise($this->softFailure('Failed to load cover articles for '.$starts->format('F Y')));

        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = $starts->format('F Y');

        $arguments['contentHeader'] = $covers
            ->then(function (Sequence $covers = null) use ($arguments) {
                if (!$covers || $covers->isEmpty()) {
                    $background = null;
                } else {
                    $background = new BackgroundImage(
                        $covers[0]->getBanner()->getSize('2:1')->getImage(900),
                        $covers[0]->getBanner()->getSize('2:1')->getImage(1800)
                    );
                }

                return ContentHeaderNonArticle::basic($arguments['title'], $background instanceof BackgroundImage, null, null, null, $background);
            });

        $arguments['listing'] = $research = $this->get('elife.api_sdk.search')
            ->forType('research-advance', 'research-article', 'research-exchange', 'short-report', 'tools-resources', 'replication-study')
            ->sortBy('date')
            ->startDate($starts)
            ->endDate($ends)
            ->useDate('published')
            ->map($this->willConvertTo(Teaser::class, ['date' => 'published']))
            ->then(function (Sequence $result) {
                if ($result->isEmpty()) {
                    return new EmptyListing('Research articles', 'No articles available.');
                }

                return ListingTeasers::basic($result->toArray(), 'Research articles');
            });

        $arguments['magazine'] = $this->get('elife.api_sdk.search')
            ->forType('editorial', 'insight', 'feature', 'collection', 'interview', 'podcast-episode')
            ->sortBy('date')
            ->startDate($starts)
            ->endDate($ends)
            ->useDate('published')
            ->sort(function (Model $a, Model $b) {
                if ($a instanceof PodcastEpisode) {
                    return -1;
                } elseif ($b instanceof PodcastEpisode) {
                    return 1;
                }

                return 0;
            })
            ->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary', 'date' => 'published']))
            ->then(Callback::emptyOr(function (Sequence $result) {
                return ListingTeasers::basic($result->toArray(), 'Magazine');
            }))
            ->otherwise($this->softFailure('Failed to load Magazine list'));

        return new Response($this->get('templating')->render('::archive-month.html.twig', $arguments));
    }

    private function validateArchiveYear(int $year, int $month = null)
    {
        if ($year < 2012) {
            throw new NotFoundHttpException('eLife did not publish in '.$year);
        } elseif ($year > $this->getMaxYear()) {
            throw new NotFoundHttpException('Year not yet in archive');
        } elseif ($month && ($month > $this->getMaxMonth($year))) {
            throw new NotFoundHttpException('Month not yet in archive');
        } elseif ($month && ($month < $this->getMinMonth($year))) {
            throw new NotFoundHttpException('eLife did not publish in '.DateTimeImmutable::createFromFormat('j n Y H:i:s', "1 $month $year 00:00:00", new DateTimeZone('Z'))->format('F Y'));
        }
    }

    private function getMaxYear() : int
    {
        $currentYear = (int) gmdate('Y', time());
        $currentMonth = (int) gmdate('n', time());

        return 1 === $currentMonth ? $currentYear - 1 : $currentYear;
    }

    private function getMinMonth(int $year) : int
    {
        return 2012 === $year ? 10 : 1;
    }

    private function getMaxMonth(int $year) : int
    {
        $currentYear = (int) gmdate('Y', time());
        $currentMonth = (int) gmdate('n', time());

        return $currentYear === $year ? $currentMonth - 1 : 12;
    }
}
