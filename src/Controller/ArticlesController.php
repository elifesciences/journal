<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\Article;
use eLife\ApiSdk\Model\ArticleHistory;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\CitationsMetric;
use eLife\ApiSdk\Model\CitationsMetricSource;
use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\FundingAward;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Reviewer;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\HasPages;
use eLife\Journal\Helper\Humanizer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\Doi;
use eLife\Patterns\ViewModel\InfoBar;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\ReadMoreItem;
use eLife\Patterns\ViewModel\SpeechBubble;
use eLife\Patterns\ViewModel\ViewSelector;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class ArticlesController extends Controller
{
    use HasPages;

    public function textAction(Request $request, string $id, int $version = null) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 3;

        $arguments = $this->articlePageArguments($request, $id, $version);

        /** @var Sequence $recommendations */
        $recommendations = new PromiseSequence($arguments['item']
            ->then(function (ArticleVersion $item) {
                if (in_array($item->getType(), ['correction', 'retraction'])) {
                    return new EmptySequence();
                }

                return $this->get('elife.api_sdk.recommendations')->list($item->getIdentifier())->slice(0, 100)
                    ->otherwise($this->mightNotExist())
                    ->otherwise($this->softFailure('Failed to load recommendations', new EmptySequence()));
            }));

        $arguments['furtherReading'] = $recommendations
            ->filter(function (Model $model) use ($arguments) {
                // Remove corrections and retractions for this article.
                if ($model instanceof ArticleVersion && in_array($model->getType(), ['correction', 'retraction'])) {
                    foreach ($arguments['relatedArticles'] as $relatedArticle) {
                        if ($relatedArticle->getId() === $model->getId()) {
                            return false;
                        }
                    }
                }

                return true;
            });

        $arguments['relatedItem'] = $arguments['furtherReading']->then(Callback::method('offsetGet', 0));

        $furtherReading = $this->pagerfantaPromise(
            $arguments['furtherReading'],
            $page,
            $perPage,
            function (Model $model, int $i) use ($page) {
                $context = [];

                if (0 === $i && 1 === $page) {
                    $context['isRelated'] = true;
                }

                return $this->convertTo($model, ReadMoreItem::class, $context);
            }
        );

        $arguments['paginator'] = $this->paginator(
            $furtherReading,
            $request,
            'Read more articles',
            'article'
        );

        $arguments['paginator'] = all(['paginator' => $arguments['paginator'], 'item' => $arguments['item']])
            ->then(function (array $parts) {
                if (in_array($parts['item']->getType(), ['correction', 'retraction'])) {
                    return null;
                }

                return $parts['paginator'];
            });

        $arguments['listing'] = $arguments['paginator']
            ->then(Callback::emptyOr($this->willConvertTo(ViewModel\ListingReadMore::class)));

        if (1 === $page) {
            return $this->createFirstPage($id, $arguments);
        }

        $arguments['title'] = 'Browse further reading';

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(string $id, array $arguments) : Response
    {
        $arguments['relatedItem'] = all(['relatedItem' => $arguments['relatedItem'], 'item' => $arguments['item'], 'listing' => $arguments['listing'], 'relatedArticles' => $arguments['relatedArticles']])
            ->then(function (array $parts) {
                /** @var Article|null $relatedItem */
                $relatedItem = $parts['relatedItem'];
                /** @var Article $item */
                $item = $parts['item'];
                /** @var ViewModel\ListingReadMore|null $listing */
                $listing = $parts['listing'];
                /** @var Sequence|Article[] $relatedArticles */
                $relatedArticles = $parts['relatedArticles'];

                if (empty($relatedItem)) {
                    return null;
                }

                if ($relatedItem instanceof Article) {
                    foreach ($relatedArticles as $relatedArticle) {
                        if ($relatedArticle->getId() === $relatedItem->getId()) {
                            $related = true;
                            break;
                        }
                    }
                }

                $item = $this->convertTo($relatedItem, ViewModel\Teaser::class, ['variant' => 'relatedItem', 'from' => $item->getType(), 'related' => $related ?? false]);

                if ($listing) {
                    return ViewModel\ListingTeasers::withSeeMore([$item], new ViewModel\SeeMoreLink(new Link('Further reading', '#listing')));
                }

                return $item;
            });

        $arguments['downloads'] = $this->get('elife.api_sdk.metrics')
            ->totalDownloads(Identifier::article($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load downloads count'));

        $figures = $this->findFigures($arguments['item'])->then(Callback::method('notEmpty'));

        $arguments['hasFigures'] = all(['item' => $arguments['item'], 'hasFigures' => $figures])
            ->then(function (array $parts) {
                $item = $parts['item'];
                $hasFigures = $parts['hasFigures'];

                return
                    $item->getDataAvailability()->notEmpty()
                    ||
                    $item->getGeneratedDataSets()->notEmpty()
                    ||
                    $item->getUsedDataSets()->notEmpty()
                    ||
                    $item->getAdditionalFiles()->notEmpty()
                    ||
                    $hasFigures;
            });

        $arguments['contextualData'] = all(['item' => $arguments['item'], 'metrics' => $arguments['contextualDataMetrics']])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var array $metrics */
                $metrics = $parts['metrics'];

                $speechBubble = SpeechBubble::forContextualData();

                if (!$item->getCiteAs()) {
                    if (empty($metrics)) {
                        return ContextualData::annotationsOnly($speechBubble);
                    }

                    return ContextualData::withMetrics($metrics, null, null, $speechBubble);
                }

                return ContextualData::withCitation($item->getCiteAs(), new Doi($item->getDoi()), $metrics, $speechBubble);
            });

        $context = all(['item' => $arguments['item'], 'history' => $arguments['history'], 'hasFigures' => $arguments['hasFigures']])
            ->then(function (array $parts) {
                $context = [];
                if ($parts['hasFigures']) {
                    $context['figuresUri'] = $this->generatePath($parts['history'], $parts['item']->getVersion(), 'figures');
                }

                return $context;
            });

        $arguments['body'] = all(['item' => $arguments['item'], 'history' => $arguments['history'], 'citations' => $arguments['citations'], 'downloads' => $arguments['downloads'], 'pageViews' => $arguments['pageViews'], 'context' => $context])
            ->then(function (array $parts) use ($context) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var ArticleHistory $history */
                $history = $parts['history'];
                /** @var CitationsMetric|null $citations */
                $citations = $parts['citations'];
                /** @var int|null $downloads */
                $downloads = $parts['downloads'];
                /** @var int|null $pageViews */
                $pageViews = $parts['pageViews'];
                /** @var array $context */
                $context = $parts['context'];

                $parts = [];

                $first = true;

                if ($item->getAbstract()) {
                    $parts[] = ArticleSection::collapsible(
                        'abstract',
                        'Abstract',
                        2,
                        $this->render(...$this->convertContent($item->getAbstract(), 2, $context)),
                        false,
                        $first,
                        $item->getAbstract()->getDoi() ? new Doi($item->getAbstract()->getDoi()) : null
                    );

                    $first = false;
                }

                if ($item instanceof ArticleVoR && $item->getDigest()) {
                    $parts[] = ArticleSection::collapsible(
                        'digest',
                        'eLife digest',
                        2,
                        $this->render(...$this->convertContent($item->getDigest(), 2, $context)),
                        false,
                        $first,
                        new Doi($item->getDigest()->getDoi())
                    );

                    $first = false;
                }

                $isInitiallyClosed = false;

                if ($item instanceof ArticleVoR) {
                    $parts = array_merge($parts, $item->getContent()->map(function (Block\Section $section) use (&$first, &$isInitiallyClosed, $context) {
                        $section = ArticleSection::collapsible(
                            $section->getId(),
                            $section->getTitle(),
                            2,
                            $this->render(...$this->convertContent($section, 2, $context)),
                            $isInitiallyClosed,
                            $first
                        );

                        $first = false;
                        $isInitiallyClosed = true;

                        return $section;
                    })->toArray());
                }

                $parts[] = SpeechBubble::forArticleBody();

                if ($item instanceof ArticleVoR) {
                    $parts = array_merge($parts, $item->getAppendices()->map(function (Appendix $appendix) use ($context) {
                        return ArticleSection::collapsible($appendix->getId(), $appendix->getTitle(), 2,
                            $this->render(...$this->convertContent($appendix, 2, $context)),
                            true, false, $appendix->getDoi() ? new Doi($appendix->getDoi()) : null);
                    })->toArray());
                }

                if ($item instanceof ArticleVoR && $item->getReferences()->notEmpty()) {
                    $parts[] = ArticleSection::collapsible(
                        'references',
                        'References',
                        2,
                        $this->render($this->convertTo($item, ViewModel\ReferenceList::class)),
                        true
                    );
                }

                if ($item instanceof ArticleVoR && $item->getDecisionLetter()) {
                    $parts[] = ArticleSection::collapsible(
                        'decision-letter',
                        'Decision letter',
                        2,
                        $this->render($this->convertTo($item, ViewModel\DecisionLetterHeader::class)).
                        $this->render(...$this->convertContent($item->getDecisionLetter(), 2, $context)),
                        true,
                        false,
                        new Doi($item->getDecisionLetter()->getDoi())
                    );
                }

                if ($item instanceof ArticleVoR && $item->getAuthorResponse()) {
                    $parts[] = ArticleSection::collapsible(
                        'author-response',
                        'Author response',
                        2,
                        $this->render(...$this->convertContent($item->getAuthorResponse(), 2, $context)),
                        true,
                        false,
                        new Doi($item->getAuthorResponse()->getDoi())
                    );
                }

                $infoSections = [];

                $realAuthors = $item->getAuthors()->filter(Callback::isInstanceOf(Author::class));

                if ($realAuthors->notEmpty()) {
                    $infoSections[] = new ViewModel\AuthorsDetails(
                        ...$realAuthors->map($this->willConvertTo(null, ['authors' => $realAuthors]))
                    );
                }

                if ($item->getFunding()) {
                    $funding = $item->getFunding()->getAwards()
                        ->map(function (FundingAward $award) {
                            $title = $award->getSource()->getPlace()->toString();

                            if ($award->getAwardId()) {
                                $title .= ' ('.$award->getAwardId().')';
                            }

                            $body = Listing::unordered(
                                $award->getRecipients()
                                    ->map(Callback::method('toString'))
                                    ->toArray(),
                                'bullet'
                            );

                            return ArticleSection::basic($title, 4, $this->render($body));
                        })->toArray();

                    $funding[] = new Paragraph($item->getFunding()->getStatement());

                    $infoSections[] = ArticleSection::basic('Funding', 3, $this->render(...$funding));
                }

                if ($item instanceof ArticleVoR && $item->getAcknowledgements()->notEmpty()) {
                    $infoSections[] = ArticleSection::basic(
                        'Acknowledgements',
                        3,
                        $this->render(...$item->getAcknowledgements()->map($this->willConvertTo(null, ['level' => 3])))
                    );
                }

                if ($item->getEthics()->notEmpty()) {
                    $infoSections[] = ArticleSection::basic(
                        'Ethics',
                        3,
                        $this->render(...$item->getEthics()->map($this->willConvertTo(null, ['level' => 3])))
                    );
                }

                if ($item->getReviewers()->notEmpty()) {
                    $infoSections[] = ArticleSection::basic(
                        'Reviewing Editor',
                        3,
                        $this->render(
                            Listing::ordered(
                                $item->getReviewers()
                                    ->map(function (Reviewer $reviewer) {
                                        $parts = [$reviewer->getPreferredName(), $reviewer->getRole()];

                                        foreach ($reviewer->getAffiliations() as $affiliation) {
                                            $parts[] = $affiliation->toString();
                                        }

                                        return implode(', ', $parts);
                                    })
                                    ->toArray()
                            )
                        )
                    );
                }

                $publicationHistory = [];

                if ($history->getReceived()) {
                    $publicationHistory[] = 'Received: '.$history->getReceived()->format();
                }

                if ($history->getAccepted()) {
                    $publicationHistory[] = 'Accepted: '.$history->getAccepted()->format();
                }

                $publicationHistory = array_merge($publicationHistory, $history->getVersions()
                    ->filter(Callback::isInstanceOf(ArticlePoA::class))
                    ->map(function (ArticlePoA $itemVersion, int $number) use ($history) {
                        return sprintf('Accepted Manuscript %s: <a href="%s">%s (version %s)</a>', 0 === $number ? 'published' : 'updated', $this->generatePath($history, $itemVersion->getVersion()), $itemVersion->getVersionDate() ? $itemVersion->getVersionDate()->format('F j, Y') : '', $itemVersion->getVersion());
                    })->toArray());

                $publicationHistory = array_merge($publicationHistory, $history->getVersions()
                    ->filter(Callback::isInstanceOf(ArticleVoR::class))
                    ->map(function (ArticleVoR $itemVersion, int $number) use ($history) {
                        return sprintf('Version of Record %s: <a href="%s">%s (version %s)</a>', 0 === $number ? 'published' : 'updated', $this->generatePath($history, $itemVersion->getVersion()), $itemVersion->getVersionDate() ? $itemVersion->getVersionDate()->format('F j, Y') : '', $itemVersion->getVersion());
                    })->toArray());

                $infoSections[] = ArticleSection::basic(
                    'Publication history',
                    3,
                    $this->render(
                        Listing::ordered($publicationHistory, 'bullet')
                    )
                );

                $copyright = '<p>'.$item->getCopyright()->getStatement().'</p>';

                if ($item->getCopyright()->getHolder()) {
                    $copyright = sprintf('<p>© %s, %s</p>', 2011 + $item->getVolume(), $item->getCopyright()->getHolder()).$copyright;
                }

                $infoSections[] = ArticleSection::basic('Copyright', 3, $copyright);

                $parts[] = ArticleSection::collapsible(
                    'info',
                    'Article'.($item->getAuthors()->notEmpty() ? ' and author' : '').' information',
                    2,
                    $this->render(...$infoSections),
                    true
                );

                $statistics = [];
                $statisticsExtra = [];

                if ($pageViews) {
                    $statistics[] = ViewModel\Statistic::fromNumber('Page views', $pageViews);
                    $statisticsExtra[] = new ViewModel\BarChart($item->getId(), 'article', 'page-views', rtrim($this->getParameter('api_url_public'), '/'), 'page-views', 'month');
                }

                if ($downloads) {
                    $statistics[] = ViewModel\Statistic::fromNumber('Downloads', $downloads);
                    $statisticsExtra[] = new ViewModel\BarChart($item->getId(), 'article', 'downloads', rtrim($this->getParameter('api_url_public'), '/'), 'downloads', 'month');
                }

                if ($citations) {
                    $statistics[] = ViewModel\Statistic::fromNumber('Citations', $citations->getHighest()->getCitations());
                    $statisticsExtra[] = new Paragraph('Article citation count generated by polling the highest count across the following sources: '.implode(', ', array_map(function (CitationsMetricSource $source) {
                        return sprintf('<a href="%s">%s</a>', $source->getUri(), $source->getService());
                    }, $citations->toArray())).'.');
                }

                if (!empty($statistics)) {
                    $parts[] = ArticleSection::collapsible(
                        'metrics',
                        'Metrics',
                        2,
                        $this->render(new ViewModel\StatisticCollection(...$statistics), ...$statisticsExtra),
                        true
                    );
                }

                return $parts;
            });

        $arguments['viewSelector'] = $this->createViewSelector($arguments['item'], $arguments['hasFigures'], false, $arguments['history'], $arguments['body']);

        $arguments['body'] = all(['item' => $arguments['item'], 'body' => $arguments['body'], 'downloadLinks' => $arguments['downloadLinks']])
            ->then(function (array $parts) {
                $item = $parts['item'];
                $body = $parts['body'];
                $downloadLinks = $parts['downloadLinks'];

                $body[] = ArticleSection::basic('Download links', 2, $this->render($downloadLinks));

                $body[] = $this->convertTo($item, ViewModel\ArticleMeta::class);

                return $body;
            });

        return new Response($this->get('templating')->render('::article-text.html.twig', $arguments));
    }

    public function figuresAction(Request $request, string $id, int $version = null) : Response
    {
        $arguments = $this->articlePageArguments($request, $id, $version);

        $arguments['title'] = $arguments['title']
            ->then(function (string $title) {
                return 'Figures and data in '.$title;
            });

        $arguments['contextualData'] = all(['item' => $arguments['item'], 'metrics' => $arguments['contextualDataMetrics']])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var array $metrics */
                $metrics = $parts['metrics'];

                if (!$item->getCiteAs()) {
                    if (empty($metrics)) {
                        return null;
                    }

                    return ContextualData::withMetrics($metrics);
                }

                return ContextualData::withCitation($item->getCiteAs(), new Doi($item->getDoi()), $metrics);
            });

        $allFigures = $this->findFigures($arguments['item']);

        $figures = $allFigures
            ->filter(function (Block\Figure $figure) {
                return $figure->getAssets()[0]->getAsset() instanceof Block\Image;
            })
            ->map($this->willConvertTo(null, ['complete' => true]));

        $videos = $allFigures
            ->filter(function (Block\Figure $figure) {
                return $figure->getAssets()[0]->getAsset() instanceof Block\Video;
            })
            ->map($this->willConvertTo(null, ['complete' => true]));

        $tables = $allFigures
            ->filter(function (Block\Figure $figure) {
                return $figure->getAssets()[0]->getAsset() instanceof Block\Table;
            })
            ->map($this->willConvertTo(null, ['complete' => true]));

        $dataAvailability = (new PromiseSequence($arguments['item']
            ->then(Callback::method('getDataAvailability'))))
            ->map($this->willConvertTo());

        $generateDataSets = $arguments['item']
            ->then(function (ArticleVersion $item) {
                return $item->getGeneratedDataSets()
                    ->map(function (DataSet $dataSet, int $id) {
                        return new ViewModel\ReferenceListItem($dataSet->getId(), $id + 1, $this->convertTo($dataSet));
                    });
            });

        $usedDataSets = $arguments['item']
            ->then(function (ArticleVersion $item) {
                return $item->getUsedDataSets()
                    ->map(function (DataSet $dataSet, int $id) {
                        return new ViewModel\ReferenceListItem($dataSet->getId(), $id + 1, $this->convertTo($dataSet));
                    });
            });

        $additionalFiles = $arguments['item']
            ->then(function (ArticleVersion $item) {
                return $item->getAdditionalFiles()->map($this->willConvertTo());
            });

        $arguments['messageBar'] = all([
            'figures' => $figures,
            'videos' => $videos,
            'tables' => $tables,
            'dataAvailability' => $dataAvailability,
            'generatedDataSets' => $generateDataSets,
            'usedDataSets' => $usedDataSets,
            'additionalFiles' => $additionalFiles,
        ])
            ->then(function (array $all) {
                return array_filter([
                    'figures' => $all['figures'],
                    'videos' => $all['videos'],
                    'tables' => $all['tables'],
                    'data sets' => $all['generatedDataSets']->append(...$all['usedDataSets']),
                    'additional files' => $all['additionalFiles'],
                ], Callback::method('notEmpty'));
            })
            ->then(Callback::mustNotBeEmpty(new NotFoundHttpException('Article version does not contain any figures or data')))
            ->then(function (array $all) {
                return new ViewModel\MessageBar(Humanizer::prettyList(...array_map(function (string $text, Sequence $items) {
                    if (1 === count($items)) {
                        $text = substr($text, 0, strlen($text) - 1);
                    }

                    return sprintf('<b>%s</b> %s', number_format(count($items)), $text);
                }, array_keys($all), array_values($all))));
            });

        $additionalFiles = $additionalFiles
            ->then(Callback::emptyOr(function (Sequence $files) {
                return new ViewModel\AdditionalAssets(null, $files->toArray());
            }));

        $generateDataSets = $generateDataSets
            ->then(Callback::emptyOr(function (Sequence $generatedDataSets) {
                return [
                    new ViewModel\MessageBar('The following data sets were generated'),
                    new ViewModel\ReferenceList(...$generatedDataSets),
                ];
            }, []));

        $usedDataSets = $usedDataSets
            ->then(Callback::emptyOr(function (Sequence $usedDataSets) {
                return [
                    new ViewModel\MessageBar('The following previously published data sets were used'),
                    new ViewModel\ReferenceList(...$usedDataSets),
                ];
            }, []));

        $data = all(['availability' => $dataAvailability, 'generated' => $generateDataSets, 'used' => $usedDataSets])
            ->then(function (array $data) {
                return $data['availability']->append(...$data['generated'], ...$data['used']);
            });

        $arguments['body'] = all([
            'figures' => $figures,
            'videos' => $videos,
            'tables' => $tables,
            'data' => $data,
            'additionalFiles' => $additionalFiles,
        ])
            ->then(function (array $all) {
                $parts = [];

                $first = true;

                if ($all['figures']->notEmpty()) {
                    $parts[] = ArticleSection::collapsible('figures', 'Figures', 2, $this->render(...$all['figures']), false, $first);
                    $first = false;
                }

                if ($all['videos']->notEmpty()) {
                    $parts[] = ArticleSection::collapsible('videos', 'Videos', 2, $this->render(...$all['videos']), false, $first);
                    $first = false;
                }

                if ($all['tables']->notEmpty()) {
                    $parts[] = ArticleSection::collapsible('tables', 'Tables', 2, $this->render(...$all['tables']), false, $first);
                    $first = false;
                }

                if ($all['data']->notEmpty()) {
                    $parts[] = ArticleSection::collapsible('data', 'Data availability', 2, $this->render(...$all['data']), false, $first);
                    $first = false;
                }

                if (!empty($all['additionalFiles'])) {
                    $parts[] = ArticleSection::collapsible('files', 'Additional files', 2, $this->render($all['additionalFiles']), false, $first);
                }

                return $parts;
            });

        $arguments['viewSelector'] = $this->createViewSelector($arguments['item'], promise_for(true), true, $arguments['history'], $arguments['body']);

        $arguments['body'] = all(['body' => $arguments['body'], 'downloadLinks' => $arguments['downloadLinks']])
            ->then(function (array $parts) {
                $body = $parts['body'];
                $downloadLinks = $parts['downloadLinks'];

                $body[] = ArticleSection::basic('Download links', 2, $this->render($downloadLinks));

                return $body;
            });

        return new Response($this->get('templating')->render('::article-figures.html.twig', $arguments));
    }

    public function bibTexAction(Request $request, string $id) : Response
    {
        $arguments = $this->defaultArticleArguments($request, $id);

        $arguments['item'] = $arguments['item']
            ->then(Callback::methodMustNotBeEmpty('getPublishedDate', new NotFoundHttpException('Article version not published')));

        return new Response($this->get('templating')->render('::article.bib.twig', $arguments), Response::HTTP_OK, ['Content-Type' => 'application/x-bibtex']);
    }

    public function risAction(Request $request, string $id) : Response
    {
        $arguments = $this->defaultArticleArguments($request, $id);

        $arguments['item'] = $arguments['item']
            ->then(Callback::methodMustNotBeEmpty('getPublishedDate', new NotFoundHttpException('Article version not published')));

        return new Response(preg_replace('~\R~u', "\r\n", $this->get('templating')->render('::article.ris.twig', $arguments)), Response::HTTP_OK, ['Content-Type' => 'application/x-research-info-systems']);
    }

    public function xmlAction(Request $request, string $id, int $version = null) : Response
    {
        $arguments = $this->defaultArticleArguments($request, $id, $version);

        $xml = $arguments['item']
            ->then(Callback::method('getXml'))
            ->wait();

        if (!$xml) {
            throw new NotFoundHttpException();
        }

        return $this->get('elife.journal.helper.http_proxy')->send($request, $xml);
    }

    private function defaultArticleArguments(Request $request, string $id, int $version = null) : array
    {
        $item = $this->get('elife.api_sdk.articles')
            ->get($id, $version)
            ->otherwise($this->mightNotExist());

        $arguments = $this->defaultPageArguments($request, $item);

        $arguments['title'] = $arguments['item']
            ->then(Callback::method('getFullTitle'));

        return $arguments;
    }

    private function articlePageArguments(Request $request, string $id, int $version = null) : array
    {
        $arguments = $this->defaultArticleArguments($request, $id, $version);

        $arguments['history'] = $this->get('elife.api_sdk.articles')
            ->getHistory($id)
            ->otherwise($this->mightNotExist());

        /* @var Sequence $related */
        $arguments['relatedArticles'] = new PromiseSequence($this->get('elife.api_sdk.articles')->getRelatedArticles($id)->slice(0)
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load related articles', new EmptySequence())));

        $arguments['textPath'] = $arguments['history']
            ->then(function (ArticleHistory $history) use ($version) {
                return $this->generatePath($history, $version);
            });

        $arguments['figuresPath'] = $arguments['history']
            ->then(function (ArticleHistory $history) use ($version) {
                return $this->generatePath($history, $version, 'figures');
            });

        $arguments['xmlPath'] = $arguments['history']
            ->then(function (ArticleHistory $history) use ($version) {
                return $this->generatePath($history, $version, 'xml');
            });

        $arguments['contentHeader'] = $arguments['item']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['infoBars'] = all(['item' => $arguments['item'], 'history' => $arguments['history'], 'relatedArticles' => $arguments['relatedArticles']])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var ArticleHistory $history */
                $history = $parts['history'];
                /** @var Sequence|Article[] $relatedArticles */
                $relatedArticles = $parts['relatedArticles'];

                $infoBars = [];

                if ($item->getVersion() < $history->getVersions()[count($history->getVersions()) - 1]->getVersion()) {
                    $infoBars[] = new InfoBar('Read the <a href="'.$this->generatePath($history).'">most recent version of this article</a>.', InfoBar::TYPE_MULTIPLE_VERSIONS);
                }

                if ($item instanceof ArticlePoA) {
                    $infoBars[] = new InfoBar('Accepted manuscript, PDF only. Full online edition to follow.');
                }

                if (count($relatedArticles) > 0) {
                    switch ($type = $item->getType()) {
                        case 'correction':
                            $infoBars[] = new InfoBar('This is a correction notice. Read the <a href="'.$this->get('router')->generate('article', [$relatedArticles[0]]).'">corrected article</a>.', InfoBar::TYPE_CORRECTION);
                            break;
                        case 'retraction':
                            $infoBars[] = new InfoBar('This is a retraction notice. Read the <a href="'.$this->get('router')->generate('article', [$relatedArticles[0]]).'">retracted article</a>.', InfoBar::TYPE_ATTENTION);
                            break;
                    }

                    foreach ($relatedArticles as $relatedArticle) {
                        switch ($relatedArticle->getType()) {
                            case 'correction':
                                $infoBars[] = new InfoBar('This article has been corrected. Read the <a href="'.$this->get('router')->generate('article', [$relatedArticle]).'">correction notice</a>.', InfoBar::TYPE_CORRECTION);
                                break;
                            case 'retraction':
                                $infoBars[] = new InfoBar('This article has been retracted. Read the <a href="'.$this->get('router')->generate('article', [$relatedArticle]).'">retraction notice</a>.', InfoBar::TYPE_ATTENTION);
                                break;
                        }
                    }
                }

                return $infoBars;
            });

        $arguments['citations'] = $this->get('elife.api_sdk.metrics')
            ->citations(Identifier::article($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load citations count'));

        $arguments['pageViews'] = $this->get('elife.api_sdk.metrics')
            ->totalPageViews(Identifier::article($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load page views count'));

        $arguments['contextualDataMetrics'] = all(['item' => $arguments['item'], 'history' => $arguments['history'], 'citations' => $arguments['citations'], 'pageViews' => $arguments['pageViews']])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var ArticleHistory $history */
                $history = $parts['history'];
                /** @var CitationsMetric|null $citations */
                $citations = $parts['citations'];
                /** @var int|null $pageViews */
                $pageViews = $parts['pageViews'];

                $metrics = [];

                if (null !== $citations) {
                    $metrics[] = 'Cited '.number_format($citations->getHighest()->getCitations());
                }
                if (null !== $pageViews) {
                    $metrics[] = sprintf('<a href="%s">Views %s</a>', $this->generatePath($history, $item->getVersion(), null, 'metrics'), number_format($pageViews));
                }

                return $metrics;
            });

        $arguments['downloadLinks'] = $arguments['item']
            ->then($this->willConvertTo(ViewModel\ArticleDownloadLinksList::class));

        return $arguments;
    }

    private function createViewSelector(PromiseInterface $item, PromiseInterface $hasFigures, bool $isFiguresPage, PromiseInterface $history, PromiseInterface $sections) : PromiseInterface
    {
        return all(['item' => $item, 'hasFigures' => $hasFigures, 'history' => $history, 'sections' => $sections])
            ->then(function (array $sections) use ($isFiguresPage) {
                $item = $sections['item'];
                $hasFigures = $sections['hasFigures'];
                $history = $sections['history'];
                $sections = $sections['sections'];

                $sections = array_filter($sections, Callback::isInstanceOf(ArticleSection::class));

                if (count($sections) < 2) {
                    if (!$hasFigures) {
                        return null;
                    }

                    $sections = [];
                }

                return new ViewSelector(
                    $this->generatePath($history, $item->getVersion()),
                    array_values(array_filter(array_map(function (ViewModel $viewModel) {
                        if ($viewModel instanceof ArticleSection) {
                            return new Link($viewModel['title'], '#'.$viewModel['id']);
                        }

                        return null;
                    }, $sections))),
                    $hasFigures ? $this->generatePath($history, $item->getVersion(), 'figures') : null,
                    $isFiguresPage,
                    $item instanceof ArticleVoR
                        ? rtrim($this->getParameter('side_by_side_view_url'), '/').'/'.$item->getId()
                        : null
                );
            });
    }

    private function findFigures(PromiseInterface $item) : PromiseSequence
    {
        return new PromiseSequence($item->then(function (ArticleVersion $item) {
            if (false === $item instanceof ArticleVoR) {
                return new EmptySequence();
            }

            $map = function ($item) use (&$map) {
                if ($item instanceof HasContent) {
                    return $item->getContent()->map($map)->prepend($item);
                } elseif ($item instanceof Block\Listing) {
                    return $item->getItems()->map($map)->prepend($item);
                }

                return $item;
            };

            /* @var ArticleVoR $item */
            return $item->getContent()->map($map)->flatten()
                ->filter(function ($item) {
                    return $item instanceof Block\Figure;
                });
        }));
    }

    private function generatePath(ArticleHistory $history, int $forVersion = null, string $subRoute = null, string $fragment = null) : string
    {
        if ($subRoute) {
            $subRoute = "-{$subRoute}";
        }

        $currentVersion = $history->getVersions()[count($history->getVersions()) - 1];

        if (null === $forVersion) {
            $forVersion = $currentVersion->getVersion();
        }

        if ($forVersion === $currentVersion->getVersion()) {
            return $this->get('router')->generate("article{$subRoute}", [$currentVersion, '_fragment' => $fragment]);
        }

        return $this->get('router')->generate("article-version{$subRoute}", [$currentVersion, 'version' => $forVersion, '_fragment' => $fragment]);
    }
}
