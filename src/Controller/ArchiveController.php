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
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Patterns\ViewModel\ArchiveNavLink;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\BlockLink;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\FormLabel;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Select;
use eLife\Patterns\ViewModel\SelectNav;
use eLife\Patterns\ViewModel\SelectOption;
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

    public function yearAction(int $year) : Response
    {
        $this->validateArchiveYear($year);

        $arguments = $this->defaultPageArguments();

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
                                return new Link($cover->getTitle(), $this->get('router')->generate('article', ['volume' => $item->getVolume(), 'id' => $item->getId()]));
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

    public function monthAction(int $year, string $month) : Response
    {
        $date = DateTimeImmutable::createFromFormat('j F Y', "1 $month $year", new DateTimeZone('Z'));

        if (!$date) {
            throw new NotFoundHttpException('Unknown month '.$month);
        }

        $this->validateArchiveYear($year, $date->format('n'));

        $date = $date->setTime(0, 0, 0);

        $arguments = $this->defaultPageArguments();

        $arguments['title'] = $date->format('F Y');

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['title']);

        return new Response($this->get('templating')->render('::archive-month.html.twig', $arguments));
    }

    private function validateArchiveYear(int $year, int $month = null)
    {
        if ($year < 2012) {
            throw new NotFoundHttpException('eLife did not publish in '.$year);
        } elseif ($year > $this->getMaxYear()) {
            throw new NotFoundHttpException('Year not yet in archive');
        } elseif ($month > $this->getMaxMonth($year)) {
            throw new NotFoundHttpException('Month not yet in archive');
        }
    }

    private function getMaxYear() : int
    {
        $currentYear = (int) date('Y', time());
        $currentMonth = (int) date('n', time());

        return 1 === $currentMonth ? $currentYear - 1 : $currentYear;
    }

    private function getMinMonth(int $year) : int
    {
        return 2012 === $year ? 10 : 1;
    }

    private function getMaxMonth(int $year) : int
    {
        $currentYear = (int) date('Y', time());
        $currentMonth = (int) date('n', time());

        return $currentYear === $year ? $currentMonth - 1 : 12;
    }
}
