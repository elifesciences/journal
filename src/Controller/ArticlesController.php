<?php

namespace eLife\Journal\Controller;

use DateTime;
use DateTimeImmutable;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\Article;
use eLife\ApiSdk\Model\ArticleHistory;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticlePreprint;
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
use eLife\Journal\Exception\EarlyResponse;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\HasPages;
use eLife\Journal\Helper\Humanizer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeaderNew;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;
use function uksort;

final class ArticlesController extends Controller
{
    const DISMISSIBLE_INFO_BAR_COOKIE_DURATION = '+365 days';

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
            return $this->createFirstPage($request, $id, $arguments);
        }

        $arguments['title'] = 'Browse further reading';

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(Request $request, string $id, array $arguments) : Response
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
                } else {
                    $related = true;
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
                    $item->getAdditionalFiles()->notEmpty()
                    ||
                    $hasFigures;
            });

        $dataAvailability = (new PromiseSequence($arguments['item']
            ->then(Callback::method('getDataAvailability'))))
            ->map($this->willConvertTo());

        $generateDataSets = $arguments['item']
            ->then(function (ArticleVersion $item) {
                return $item->getGeneratedDataSets()
                    ->map(function (DataSet $dataSet) {
                        return $this->convertTo($dataSet);
                    });
            })
            ->then(Callback::emptyOr(function (Sequence $generatedDataSets) {
                return [
                    new ViewModel\MessageBar('The following data sets were generated'),
                    new ViewModel\ReferenceList(...$generatedDataSets),
                ];
            }, []));

        $usedDataSets = $arguments['item']
            ->then(function (ArticleVersion $item) {
                return $item->getUsedDataSets()
                    ->map(function (DataSet $dataSet) {
                        return $this->convertTo($dataSet);
                    });
            })
            ->then(Callback::emptyOr(function (Sequence $usedDataSets) {
                return [
                    new ViewModel\MessageBar('The following previously published data sets were used'),
                    new ViewModel\ReferenceList(...$usedDataSets),
                ];
            }, []));

        $arguments['hasData'] = all(['availability' => $dataAvailability, 'generated' => $generateDataSets, 'used' => $usedDataSets])
            ->then(function (array $data) {
                return $data['availability']->append(...$data['generated'], ...$data['used']);
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

        $bioprotocols = $this->get('elife.api_sdk.bioprotocols')
            ->list(Identifier::article($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load bioprotocols', []));

        $context = all(['item' => $arguments['item'], 'history' => $arguments['history'], 'hasFigures' => $arguments['hasFigures'], 'bioprotocols' => $bioprotocols])
            ->then(function (array $parts) {
                $context = [];
                if ($parts['hasFigures']) {
                    $context['figuresUri'] = $this->generatePath($parts['history'], $parts['item']->getVersion(), 'figures');
                }

                $context['bioprotocols'] = $parts['bioprotocols'];

                return $context;
            });

        $arguments['body'] = all(['item' => $arguments['item'], 'isMagazine' => $arguments['isMagazine'], 'history' => $arguments['history'], 'citations' => $arguments['citations'], 'downloads' => $arguments['downloads'], 'pageViews' => $arguments['pageViews'], 'data' => $arguments['hasData'], 'context' => $context])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var bool $isMagazine */
                $isMagazine = $parts['isMagazine'];
                /** @var ArticleHistory $history */
                $history = $parts['history'];
                /** @var CitationsMetric|null $citations */
                $citations = $parts['citations'];
                /** @var int|null $downloads */
                $downloads = $parts['downloads'];
                /** @var int|null $pageViews */
                $pageViews = $parts['pageViews'];
                /** @var Sequence $data */
                $data = $parts['data'];
                /** @var array $context */
                $context = $parts['context'];

                $parts = [];

                if ($isMagazine && $item->getAuthors()->notEmpty()) {
                    $parts[] = $this->convertTo($item, ViewModel\Authors::class);
                }

                $first = true;

                if (!$isMagazine && $item->getAbstract()) {
                    $parts[] = ArticleSection::collapsible(
                        'abstract',
                        'Abstract',
                        2,
                        $this->render(...$this->convertContent($item->getAbstract(), 2, $context)),
                        null,
                        null,
                        false,
                        $first,
                        $item->getAbstract()->getDoi() ? new Doi($item->getAbstract()->getDoi()) : null
                    );

                    $first = false;
                }

                if ($item instanceof ArticleVoR && $item->getEditorEvaluation()) {
                    // Editor's evaluation should feel connected to abstract and not be collapsible
                    $first = true;
                    $relatedLinks = [];

                    if ($item->getDecisionLetter()) {
                        $relatedLinks[] = new Link('Decision letter', $this->get('router')->generate('article', ['id' => $item->getId(), '_fragment' => $item->getDecisionLetter()->getId() ?? 'decision-letter']));
                    }

                    if ($item->getEditorEvaluationScietyUri()) {
                        $relatedLinks[] = new Link('Reviews on Sciety', $item->getEditorEvaluationScietyUri());
                    }

                    $relatedLinks[] = new Link('eLife\'s review process', $this->get('router')->generate('about-peer-review'));

                    $parts[] = ArticleSection::collapsible(
                        $item->getEditorEvaluation()->getId() ?? 'editor-evaluation',
                        'Editor\'s evaluation',
                        2,
                        $this->render(...$this->convertContent($item->getEditorEvaluation(), 2, $context)),
                        $relatedLinks,
                        ArticleSection::STYLE_HIGHLIGHTED,
                        false,
                        $first,
                        $item->getEditorEvaluation()->getDoi() ? new Doi($item->getEditorEvaluation()->getDoi()) : null
                    );

                    $first = false;
                }

                if ($item instanceof ArticleVoR && $item->getDigest()) {
                    $parts[] = ArticleSection::collapsible(
                        'digest',
                        'eLife digest',
                        2,
                        $this->render(...$this->convertContent($item->getDigest(), 2, $context)),
                        null,
                        null,
                        false,
                        $first,
                        $item->getDigest()->getDoi() ? new Doi($item->getDigest()->getDoi()) : null
                    );

                    $first = false;
                }

                $isInitiallyClosed = false;

                if ($item instanceof ArticleVoR) {
                    $parts = array_merge($parts, $item->getContent()->map(function (Block\Section $section) use (&$first, &$isInitiallyClosed, $isMagazine, $context) {
                        $section = ($isMagazine && $first) ?
                            ArticleSection::basic(
                                $this->render(...$this->convertContent($section, 2, $context)),
                                null,
                                null,
                                $section->getId(),
                                null,
                                null,
                                null,
                                $first
                            ) :
                            ArticleSection::collapsible(
                                $section->getId(),
                                $section->getTitle(),
                                2,
                                $this->render(...$this->convertContent($section, 2, $context)),
                                null,
                                null,
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
                            null, null, true, false, $appendix->getDoi() ? new Doi($appendix->getDoi()) : null);
                    })->toArray());
                }

                if ($data->notEmpty()) {
                    $parts[] = ArticleSection::collapsible('data', 'Data availability', 2, $this->render(...$data), null, null, false, $first);
                }

                if ($item instanceof ArticleVoR && $item->getReferences()->notEmpty()) {
                    $parts[] = ArticleSection::collapsible(
                        'references',
                        'References',
                        2,
                        $this->render($this->convertTo($item, ViewModel\ReferenceList::class)),
                        null,
                        null,
                        true
                    );
                }

                if ($item instanceof ArticleVoR && $item->getDecisionLetter()) {
                    $parts[] = ArticleSection::collapsible(
                        $item->getDecisionLetter()->getId() ?? 'decision-letter',
                        'Decision letter',
                        2,
                        $this->render($this->convertTo($item, ViewModel\DecisionLetterHeader::class)).
                        $this->render(...$this->convertContent($item->getDecisionLetter(), 2, $context)),
                        null,
                        null,
                        true,
                        false,
                        $item->getDecisionLetter()->getDoi() ? new Doi($item->getDecisionLetter()->getDoi()) : null
                    );
                }

                if ($item instanceof ArticleVoR && $item->getAuthorResponse()) {
                    $parts[] = ArticleSection::collapsible(
                        $item->getAuthorResponse()->getId() ?? 'author-response',
                        'Author response',
                        2,
                        $this->render(...$this->convertContent($item->getAuthorResponse(), 2, $context)),
                        null,
                        null,
                        true,
                        false,
                        $item->getAuthorResponse()->getDoi() ? new Doi($item->getAuthorResponse()->getDoi()) : null
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

                            return ArticleSection::basic($this->render($body), $title, 4);
                        })->toArray();

                    $funding[] = new Paragraph($item->getFunding()->getStatement());

                    $infoSections[] = ArticleSection::basic($this->render(...$funding), 'Funding', 3);
                }

                if ($item instanceof ArticleVoR && $item->getAcknowledgements()->notEmpty()) {
                    $infoSections[] = ArticleSection::basic(
                        $this->render(...$item->getAcknowledgements()->map($this->willConvertTo(null, ['level' => 3]))),
                        'Acknowledgements',
                        3
                    );
                }

                if ($item->getEthics()->notEmpty()) {
                    $infoSections[] = ArticleSection::basic(
                        $this->render(...$item->getEthics()->map($this->willConvertTo(null, ['level' => 3]))),
                        'Ethics',
                        3
                    );
                }

                if ($item->getReviewers()->notEmpty()) {
                    $roles = $item->getReviewers()
                        ->reduce(function (array $roles, Reviewer $reviewer) {
                            $entry = $reviewer->getPreferredName();

                            foreach ($reviewer->getAffiliations() as $affiliation) {
                                $entry .= ", {$affiliation->toString()}";
                            }

                            $roles[$reviewer->getRole()][] = $entry;

                            return $roles;
                        }, []);

                    uksort($roles, function (string $a, string $b) : int {
                        if (false !== stripos($a, 'Senior')) {
                            return -1;
                        }
                        if (false !== stripos($b, 'Senior')) {
                            return 1;
                        }
                        if (false !== stripos($a, 'Editor')) {
                            return -1;
                        }
                        if (false !== stripos($b, 'Editor')) {
                            return 1;
                        }

                        return 0;
                    });

                    foreach ($roles as $role => $reviewers) {
                        if (count($reviewers) > 1) {
                            $role = "${role}s";
                        }

                        $infoSections[] = ArticleSection::basic(
                            $this->render(Listing::ordered($reviewers)),
                            $role,
                            3
                        );
                    }
                }

                $received = $history->getReceived();
                $accepted = $history->getAccepted();
                $publicationHistory = [];

                /** @var ArticlePreprint[] $preprints */
                $preprints = $history->getVersions()
                    ->filter(Callback::isInstanceOf(ArticlePreprint::class))
                    ->toArray();

                if ($preprints) {
                    foreach ($preprints as $preprint) {
                        // Attempt to output $received if date is before the preprint date.
                        if ($received && 1 === $preprint->getPublishedDate()->diff(new DateTime($received->toString()))->invert) {
                            $publicationHistory[] = 'Received: '.$received->format();

                            // Set $received to null as it has now been included in the publication history.
                            $received = null;
                        }
                        // Attempt to output $accepted if date is before the preprint date.
                        if ($accepted && 1 === $preprint->getPublishedDate()->diff(new DateTime($accepted->toString()))->invert) {
                            $publicationHistory[] = 'Accepted: '.$accepted->format();

                            // Set $accepted to null as it has now been included in the publication history.
                            $accepted = null;
                        }

                        $publicationHistory[] = sprintf('Preprint posted: <a href="%s">%s (view preprint)</a>', $preprint->getUri(), $preprint->getPublishedDate()->format('F j, Y'));
                    }
                }

                // Output $received if it has not yet been output.
                if ($received) {
                    $publicationHistory[] = 'Received: '.$received->format();
                }

                // Output $accepted if it has not yet been output.
                if ($accepted) {
                    $publicationHistory[] = 'Accepted: '.$accepted->format();
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
                    $this->render(
                        Listing::ordered($publicationHistory, 'bullet')
                    ),
                    'Publication history',
                    3
                );

                $copyright = '<p>'.$item->getCopyright()->getStatement().'</p>';

                if ($item->getCopyright()->getHolder()) {
                    $copyright = sprintf('<p>© %s, %s</p>', 2011 + $item->getVolume(), $item->getCopyright()->getHolder()).$copyright;
                }

                $infoSections[] = ArticleSection::basic($copyright, 'Copyright', 3);

                $parts[] = ArticleSection::collapsible(
                    'info',
                    'Article'.($item->getAuthors()->notEmpty() ? ' and author' : '').' information',
                    2,
                    $this->render(...$infoSections),
                    null,
                    null,
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
                        null,
                        null,
                        true
                    );
                }

                return $parts;
            });

        $arguments['viewSelector'] = $this->createViewSelector($arguments['item'], $arguments['isMagazine'], $arguments['hasFigures'], false, $arguments['history'], $arguments['body'], $arguments['eraArticle']);

        $arguments['body'] = all(['item' => $arguments['item'], 'body' => $arguments['body'], 'downloadLinks' => $arguments['downloadLinks']])
            ->then(function (array $parts) {
                $item = $parts['item'];
                $body = $parts['body'];
                $downloadLinks = $parts['downloadLinks'];

                $body[] = ArticleSection::basic($this->render($downloadLinks), 'Download links', 2);

                $body[] = $this->convertTo($item, ViewModel\ArticleMeta::class);

                return $body;
            });

        $arguments['google_scholar_metadata'] = (bool) $request->headers->get('X-eLife-Google-Scholar-Metadata', false);

        return new Response($this->get('templating')->render('::article-text.html.twig', $arguments), Response::HTTP_OK, ['Vary' => 'X-eLife-Google-Scholar-Metadata']);
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

        $additionalFiles = $arguments['item']
            ->then(function (ArticleVersion $item) {
                return $item->getAdditionalFiles()->map($this->willConvertTo());
            });

        $arguments['messageBar'] = all([
            'figures' => $figures,
            'videos' => $videos,
            'tables' => $tables,
            'additionalFiles' => $additionalFiles,
        ])
            ->then(function (array $all) {
                return array_filter([
                    'figures' => $all['figures'],
                    'videos' => $all['videos'],
                    'tables' => $all['tables'],
                    'additional files' => $all['additionalFiles'],
                ], Callback::method('notEmpty'));
            })
            ->then(function (array $all) {
                if (empty($all)) {
                    return new ViewModel\MessageBar('There are no figures or additional files');
                }

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

        $arguments['body'] = all([
            'isMagazine' => $arguments['isMagazine'],
            'figures' => $figures,
            'videos' => $videos,
            'tables' => $tables,
            'additionalFiles' => $additionalFiles,
        ])
            ->then(function (array $all) use ($id) {
                $parts = [];

                $first = true;

                if ($all['figures']->notEmpty()) {
                    $parts[] = ArticleSection::collapsible('figures', 'Figures', 2, $this->render(...$all['figures']), null, null, false, $first);
                    $first = false;
                }

                if ($all['videos']->notEmpty()) {
                    $parts[] = ArticleSection::collapsible('videos', 'Videos', 2, $this->render(...$all['videos']), null, null, false, $first);
                    $first = false;
                }

                if ($all['tables']->notEmpty()) {
                    $parts[] = ArticleSection::collapsible('tables', 'Tables', 2, $this->render(...$all['tables']), null, null, false, $first);
                    $first = false;
                }

                if (!empty($all['additionalFiles'])) {
                    $parts[] = ArticleSection::collapsible('files', 'Additional files', 2, $this->render($all['additionalFiles']), null, null, false, $first);
                }

                if ($all['isMagazine'] && !empty($parts)) {
                    throw new EarlyResponse(new RedirectResponse(
                        $this->get('router')->generate('article', ['id' => $id]),
                        Response::HTTP_MOVED_PERMANENTLY
                    ));
                }

                return $parts;
            })
            ->then(Callback::mustNotBeEmpty(new NotFoundHttpException('Article version does not contain any figures or data')));

        $arguments['viewSelector'] = $this->createViewSelector($arguments['item'], $arguments['isMagazine'], promise_for(true), true, $arguments['history'], $arguments['body'], $arguments['eraArticle']);

        $arguments['body'] = all(['body' => $arguments['body'], 'downloadLinks' => $arguments['downloadLinks']])
            ->then(function (array $parts) {
                $body = $parts['body'];
                $downloadLinks = $parts['downloadLinks'];

                $body[] = ArticleSection::basic($this->render($downloadLinks), 'Download links', 2);

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

    public function pdfAction(Request $request, string $id, int $version = null) : Response
    {
        $arguments = $this->defaultArticleArguments($request, $id, $version);

        $pdf = $arguments['item']
            ->then(Callback::method('getPdf'))
            ->wait();

        if (!$pdf) {
            throw new NotFoundHttpException();
        }

        return $this->get('elife.journal.helper.http_proxy')->send($request, $pdf);
    }

    public function risAction(Request $request, string $id) : Response
    {
        $arguments = $this->defaultArticleArguments($request, $id);

        $arguments['item'] = $arguments['item']
            ->then(Callback::methodMustNotBeEmpty('getPublishedDate', new NotFoundHttpException('Article version not published')));

        return new Response(preg_replace('~\R~u', "\r\n", $this->get('templating')->render('::article.ris.twig', $arguments)), Response::HTTP_OK, ['Content-Type' => 'application/x-research-info-systems']);
    }

    public function eraAction(Request $request, string $id) : Response
    {
        $arguments = $this->defaultArticleArguments($request, $id);

        if (!isset($arguments['eraArticle']['display'])) {
            throw new NotFoundHttpException('No RDS companion associated with this article');
        }

        $arguments['footer'] = null;
        $arguments['callsToAction'] = null;
        $arguments['emailCta'] = null;

        $arguments['infoBars'][] = new InfoBar('This is an executable code view. <a href="'.$this->get('router')->generate('article', ['id' => $id]).'">See the original article</a>.', InfoBar::TYPE_WARNING);

        return new Response($this->get('templating')->render('::article-era.html.twig', $arguments));
    }

    public function eraDownloadAction(Request $request, string $id) : Response
    {
        $arguments = $this->defaultArticleArguments($request, $id);

        if (!isset($arguments['eraArticle']['download'])) {
            throw new NotFoundHttpException('No RDS companion associated with this article');
        }

        return new RedirectResponse(
            $this->get('elife.journal.helper.download_link_uri_generator')->generate(
                new DownloadLink(
                    $arguments['eraArticle']['download'],
                    $arguments['item']
                        ->then(Callback::method('getVersion'))
                        ->then(function (int $version) use ($id) {
                            return sprintf('elife-%s-v%d-era.zip', $id, $version);
                        })
                        ->wait()
                )
            ),
            Response::HTTP_MOVED_PERMANENTLY
        );
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

        $arguments['eraArticle'] = $this->getParameter('era_articles')[$id] ?? [];

        return $arguments;
    }

    private function articlePageArguments(Request $request, string $id, int $version = null) : array
    {
        $arguments = $this->defaultArticleArguments($request, $id, $version);

        $arguments['isMagazine'] = $arguments['item']
            ->then(function (ArticleVersion $item) {
                return in_array($item->getType(), ['insight', 'editorial']);
            });

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

        $arguments['pdfPath'] = $arguments['history']
            ->then(function (ArticleHistory $history) use ($version) {
                return $this->generatePath($history, $version, 'pdf');
            });

        $arguments['xmlPath'] = $arguments['history']
            ->then(function (ArticleHistory $history) use ($version) {
                return $this->generatePath($history, $version, 'xml');
            });

        $arguments['modalWindows'] = $arguments['item']
            ->then(function (ArticleVersion $item) {
                return [
                    $this->convertTo($item, ViewModel\ModalWindow::class),
                    $this->convertTo($item, ViewModel\ModalWindow::class, ['type' => 'citation', 'clipboard' => $this->get('templating')->render('::article.cite.twig', ['item' => $item])]),
                ];
            });

        $arguments['infoBars'] = all(['item' => $arguments['item'], 'history' => $arguments['history'], 'relatedArticles' => $arguments['relatedArticles'], 'eraArticle' => $arguments['eraArticle']])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var ArticleHistory $history */
                $history = $parts['history'];
                /** @var Sequence|Article[] $relatedArticles */
                $relatedArticles = $parts['relatedArticles'];
                /** @var array $eraArticle */
                $eraArticle = $parts['eraArticle'];

                $infoBars = [];

                if ($this->isGranted('FEATURE_PRC_COMMS')) {
                    $infoBarText = sprintf(
                        'eLife\'s peer-review process is changing. From early next year, we will no longer make accept/reject decisions after peer review. <a href="%s" class="">About the new process.</a>',
                        $this->get('router')->generate('inside-elife-article', ['id' => '54d63486'])
                    );
                    $infoBars[] = new InfoBar(
                        $infoBarText,
                        InfoBar::TYPE_DISMISSIBLE,
                        'article-prc-dismissible',
                        new DateTimeImmutable(self::DISMISSIBLE_INFO_BAR_COOKIE_DURATION)
                    );
                }

                $articleVersions = $history->getVersions()
                    ->filter(Callback::isInstanceOf(ArticleVersion::class))
                    ->toArray();

                $latest = $articleVersions[count($articleVersions) - 1];
                $latestVersion = $latest->getVersion();

                if ($item->getVersion() < $latestVersion) {
                    $infoBars[] = new InfoBar('Read the <a href="'.$this->generatePath($history).'">most recent version of this article</a>.', InfoBar::TYPE_MULTIPLE_VERSIONS);
                }

                if ($latest instanceof ArticlePoA) {
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

                if (isset($eraArticle['display']) && $item->getVersion() === $latestVersion) {
                    $infoBars[] = new InfoBar('See this research in an <a href="'.$this->get('router')->generate('article-era', [$item]).'">executable code view</a>.', InfoBar::TYPE_WARNING);
                }

                $dismissibleInfoBars = $this->getParameter('dismissible_info_bars');
                foreach ($dismissibleInfoBars as $infoBarConfiguration) {
                    if (in_array($item->getId(), $infoBarConfiguration['article_ids'])) {
                        if ($item instanceof ArticlePoA) {
                            continue;
                        }
                        $infoBars[] = new InfoBar($infoBarConfiguration['text'], InfoBar::TYPE_DISMISSIBLE, $infoBarConfiguration['id'], new DateTimeImmutable(self::DISMISSIBLE_INFO_BAR_COOKIE_DURATION));
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

                $metricLink = function (int $count, string $suffix) use ($history, $item) {
                    // @todo - improve pattern-library or patterns-php so class doesn't need to be set here.
                    return sprintf('<a href="%s"><span class="contextual-data__counter">%s</span> %s</a>', $this->generatePath($history, $item->getVersion(), null, 'metrics'), number_format($count), $suffix);
                };

                $metrics = [];

            if (null !== $pageViews && $pageViews > 0) {
                $metrics[] = $metricLink($pageViews, 'views');
            }
            if ($citations instanceof CitationsMetric && $citations->getHighest()->getCitations() > 0) {
                $metrics[] = $metricLink($citations->getHighest()->getCitations(), 'citations');
            }

                return $metrics;
            });

        $arguments['contentHeader'] = all(['item' => $arguments['item'], 'isMagazine' => $arguments['isMagazine'], 'metrics' => $arguments['contextualDataMetrics']])
            ->then(function (array $parts) {
                return $this->convertTo($parts['item'], ContentHeaderNew::class, ['metrics' => $parts['metrics'], 'isMagazine' => $parts['isMagazine']]);
            });

        $arguments['downloadLinks'] = all(['item' => $arguments['item'], 'history' => $arguments['history'], 'eraArticle' => $arguments['eraArticle']])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var ArticleHistory $history */
                $history = $parts['history'];
                /** @var array $eraArticle */
                $eraArticle = $parts['eraArticle'];

                $articleVersions = $history->getVersions()
                    ->filter(Callback::isInstanceOf(ArticleVersion::class))
                    ->toArray();

                $latestVersion = $articleVersions[count($articleVersions) - 1]->getVersion();

                if (isset($eraArticle['download']) && $item->getVersion() === $latestVersion) {
                    $eraDownload = $eraArticle['download'];
                }

                return $this->convertTo($item, ViewModel\ArticleDownloadLinksList::class, ['era-download' => $eraDownload ?? null]);
            });

        return $arguments;
    }

    private function createViewSelector(PromiseInterface $item, PromiseInterface $isMagazine, PromiseInterface $hasFigures, bool $isFiguresPage, PromiseInterface $history, PromiseInterface $sections, array $eraArticle) : PromiseInterface
    {
        return all(['item' => $item, 'isMagazine' => $isMagazine, 'hasFigures' => $hasFigures, 'history' => $history, 'sections' => $sections])
            ->then(function (array $sections) use ($isFiguresPage, $eraArticle) {
                if ($sections['isMagazine']) {
                    return null;
                }

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

                $articleVersions = $history->getVersions()
                    ->filter(Callback::isInstanceOf(ArticleVersion::class))
                    ->toArray();

                $latestVersion = $articleVersions[count($articleVersions) - 1]->getVersion();

                $otherLinks = [];
                if (isset($eraArticle['display']) && $item->getVersion() === $latestVersion) {
                    $otherLinks[] = new Link(
                        'Executable code',
                        $this->get('router')->generate('article-era', ['id' => $item->getId()])
                    );
                }

                return new ViewSelector(
                    $this->generatePath($history, $item->getVersion(), null, 'content'),
                    array_values(array_filter(array_map(function (ViewModel $viewModel) {
                        if ($viewModel instanceof ArticleSection) {
                            return new Link($viewModel['title'], '#'.$viewModel['id']);
                        }

                        return null;
                    }, $sections))),
                    $hasFigures ? $this->generatePath($history, $item->getVersion(), 'figures', 'content') : null,
                    $isFiguresPage,
                    $item instanceof ArticleVoR
                        ? rtrim($this->getParameter('side_by_side_view_url'), '/').'/'.$item->getId()
                        : null,
                    $otherLinks
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
            return $item->getContent()
                ->append(...$item->getAppendices())
                ->append($item->getAuthorResponse())
                ->map($map)
                ->flatten()
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

        $articleVersions = $history->getVersions()
            ->filter(Callback::isInstanceOf(ArticleVersion::class))
            ->toArray();

        $currentVersion = $articleVersions[count($articleVersions) - 1];

        if (null === $forVersion) {
            $forVersion = $currentVersion->getVersion();
        }

        if ($forVersion === $currentVersion->getVersion()) {
            return $this->get('router')->generate("article{$subRoute}", [$currentVersion, '_fragment' => $fragment]);
        }

        return $this->get('router')->generate("article-version{$subRoute}", [$currentVersion, 'version' => $forVersion, '_fragment' => $fragment]);
    }
}
