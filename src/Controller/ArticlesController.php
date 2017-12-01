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
use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\Doi;
use eLife\Patterns\ViewModel\InfoBar;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\ReadMoreItem;
use eLife\Patterns\ViewModel\ViewSelector;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        $recommendations = new PromiseSequence($arguments['article']
            ->then(function (ArticleVersion $article) {
                if (in_array($article->getType(), ['correction', 'retraction'])) {
                    return new EmptySequence();
                }

                return $this->get('elife.api_sdk.recommendations')->list($article->getIdentifier())->slice(0, 100)
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

        // When NUMBER of comments > 0, text must be:
        // '<span aria-hidden="true">NUMBER</span><span class="visuallyhidden">Open annotations (there are currently NUMBER annotations on this page).</span>'
        // When 0 comments, text must be:
        // "<span aria-hidden=\"true\">&#8220;</span><span class=\"visuallyhidden\">Open annotations (there are currently 0 annotations on this page).</span>
        $arguments['hypothesisOpenerAffordance'] = ViewModel\Button::speechBubble(
          '<span aria-hidden="true">12</span><span class="visuallyhidden">Open annotations (there are currently 12 annotations on this page).</span>', true, null, null, true, 'HypothesisOpenerAffordance');
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

        $arguments['paginator'] = all(['paginator' => $arguments['paginator'], 'article' => $arguments['article']])
            ->then(function (array $parts) {
                if (in_array($parts['article']->getType(), ['correction', 'retraction'])) {
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
        $arguments['relatedItem'] = all(['relatedItem' => $arguments['relatedItem'], 'article' => $arguments['article'], 'listing' => $arguments['listing'], 'relatedArticles' => $arguments['relatedArticles']])
            ->then(function (array $parts) {
                /** @var Article|null $relatedItem */
                $relatedItem = $parts['relatedItem'];
                /** @var Article $article */
                $article = $parts['article'];
                /** @var ViewModel\ListingReadMore|null $listing */
                $listing = $parts['listing'];
                /** @var Sequence|Article[] $relatedArticles */
                $relatedArticles = $parts['relatedArticles'];

                if (empty($relatedItem)) {
                    return null;
                }

                if ($relatedItem instanceof Article) {
                    $unrelated = true;
                    foreach ($relatedArticles as $relatedArticle) {
                        if ($relatedArticle->getId() === $relatedItem->getId()) {
                            $unrelated = false;
                            break;
                        }
                    }
                } else {
                    $unrelated = false;
                }

                $item = $this->convertTo($relatedItem, ViewModel\Teaser::class, ['variant' => 'relatedItem', 'from' => $article->getType(), 'unrelated' => $unrelated]);

                if ($listing) {
                    return ViewModel\ListingTeasers::withSeeMore([$item], new ViewModel\SeeMoreLink(new Link('Further reading', '#listing')));
                }

                return $item;
            });

        $arguments['downloads'] = $this->get('elife.api_sdk.metrics')
            ->totalDownloads(Identifier::article($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load downloads count'));

        $figures = $this->findFigures($arguments['article'])->then(Callback::method('notEmpty'));

        $arguments['hasFigures'] = all(['article' => $arguments['article'], 'hasFigures' => $figures])
            ->then(function (array $parts) {
                $article = $parts['article'];
                $hasFigures = $parts['hasFigures'];

                return
                    $article->getGeneratedDataSets()->notEmpty()
                    ||
                    $article->getUsedDataSets()->notEmpty()
                    ||
                    $article->getAdditionalFiles()->notEmpty()
                    ||
                    $hasFigures;
            });

        $context = all(['article' => $arguments['article'], 'history' => $arguments['history'], 'hasFigures' => $arguments['hasFigures']])
            ->then(function (array $parts) {
                $context = [];
                if ($parts['hasFigures']) {
                    $context['figuresUri'] = $this->generateFiguresPath($parts['history'], $parts['article']->getVersion());
                }

                return $context;
            });

        $arguments['body'] = all(['article' => $arguments['article'], 'history' => $arguments['history'], 'citations' => $arguments['citations'], 'downloads' => $arguments['downloads'], 'pageViews' => $arguments['pageViews'], 'context' => $context])
            ->then(function (array $parts) use ($context) {
                /** @var ArticleVersion $article */
                $article = $parts['article'];
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

                if ($article->getAbstract()) {
                    $parts[] = ArticleSection::collapsible(
                        'abstract',
                        'Abstract',
                        2,
                        $this->render(...$this->convertContent($article->getAbstract(), 2, $context)),
                        false,
                        $first,
                        $article->getAbstract()->getDoi() ? new Doi($article->getAbstract()->getDoi()) : null
                    );

                    $first = false;
                }

                if ($article instanceof ArticleVoR && $article->getDigest()) {
                    $parts[] = ArticleSection::collapsible(
                        'digest',
                        'eLife digest',
                        2,
                        $this->render(...$this->convertContent($article->getDigest(), 2, $context)),
                        false,
                        $first,
                        new Doi($article->getDigest()->getDoi())
                    );

                    $first = false;
                }

                $isInitiallyClosed = false;

                if ($article instanceof ArticleVoR) {
                    $parts = array_merge($parts, $article->getContent()->map(function (Block\Section $section) use (&$first, &$isInitiallyClosed, $context) {
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

                if ($article instanceof ArticleVoR) {
                    $parts = array_merge($parts, $article->getAppendices()->map(function (Appendix $appendix) use ($context) {
                        return ArticleSection::collapsible($appendix->getId(), $appendix->getTitle(), 2,
                            $this->render(...$this->convertContent($appendix, 2, $context)),
                            true, false, $appendix->getDoi() ? new Doi($appendix->getDoi()) : null);
                    })->toArray());
                }

                if ($article instanceof ArticleVoR && $article->getReferences()->notEmpty()) {
                    $parts[] = ArticleSection::collapsible(
                        'references',
                        'References',
                        2,
                        $this->render($this->convertTo($article, ViewModel\ReferenceList::class)),
                        true
                    );
                }

                if ($article instanceof ArticleVoR && $article->getDecisionLetter()) {
                    $parts[] = ArticleSection::collapsible(
                        'decision-letter',
                        'Decision letter',
                        2,
                        $this->render($this->convertTo($article, ViewModel\DecisionLetterHeader::class)).
                        $this->render(...$this->convertContent($article->getDecisionLetter(), 2, $context)),
                        true,
                        false,
                        new Doi($article->getDecisionLetter()->getDoi())
                    );
                }

                if ($article instanceof ArticleVoR && $article->getAuthorResponse()) {
                    $parts[] = ArticleSection::collapsible(
                        'author-response',
                        'Author response',
                        2,
                        $this->render(...$this->convertContent($article->getAuthorResponse(), 2, $context)),
                        true,
                        false,
                        new Doi($article->getAuthorResponse()->getDoi())
                    );
                }

                $infoSections = [];

                $realAuthors = $article->getAuthors()->filter(Callback::isInstanceOf(Author::class));

                if ($realAuthors->notEmpty()) {
                    $infoSections[] = new ViewModel\AuthorsDetails(
                        ...$realAuthors->map($this->willConvertTo(null, ['authors' => $realAuthors]))
                    );
                }

                if ($article->getFunding()) {
                    $funding = $article->getFunding()->getAwards()
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

                    $funding[] = new Paragraph($article->getFunding()->getStatement());

                    $infoSections[] = ArticleSection::basic('Funding', 3, $this->render(...$funding));
                }

                if ($article instanceof ArticleVoR && $article->getAcknowledgements()->notEmpty()) {
                    $infoSections[] = ArticleSection::basic(
                        'Acknowledgements',
                        3,
                        $this->render(...$article->getAcknowledgements()->map($this->willConvertTo(null, ['level' => 3])))
                    );
                }

                if ($article->getEthics()->notEmpty()) {
                    $infoSections[] = ArticleSection::basic(
                        'Ethics',
                        3,
                        $this->render(...$article->getEthics()->map($this->willConvertTo(null, ['level' => 3])))
                    );
                }

                if ($article->getReviewers()->notEmpty()) {
                    $infoSections[] = ArticleSection::basic(
                        'Reviewing Editor',
                        3,
                        $this->render(
                            Listing::ordered(
                                $article->getReviewers()
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
                    ->map(function (ArticlePoA $articleVersion, int $number) use ($history) {
                        return sprintf('Accepted Manuscript %s: <a href="%s">%s (version %s)</a>', 0 === $number ? 'published' : 'updated', $this->generateTextPath($history, $articleVersion->getVersion()), $articleVersion->getVersionDate() ? $articleVersion->getVersionDate()->format('F j, Y') : '', $articleVersion->getVersion());
                    })->toArray());

                $publicationHistory = array_merge($publicationHistory, $history->getVersions()
                    ->filter(Callback::isInstanceOf(ArticleVoR::class))
                    ->map(function (ArticleVoR $articleVersion, int $number) use ($history) {
                        return sprintf('Version of Record %s: <a href="%s">%s (version %s)</a>', 0 === $number ? 'published' : 'updated', $this->generateTextPath($history, $articleVersion->getVersion()), $articleVersion->getVersionDate() ? $articleVersion->getVersionDate()->format('F j, Y') : '', $articleVersion->getVersion());
                    })->toArray());

                $infoSections[] = ArticleSection::basic(
                    'Publication history',
                    3,
                    $this->render(
                        Listing::ordered($publicationHistory, 'bullet')
                    )
                );

                $copyright = '<p>'.$article->getCopyright()->getStatement().'</p>';

                if ($article->getCopyright()->getHolder()) {
                    $copyright = sprintf('<p>© %s, %s</p>', 2011 + $article->getVolume(), $article->getCopyright()->getHolder()).$copyright;
                }

                $infoSections[] = ArticleSection::basic('Copyright', 3, $copyright);

                $parts[] = ArticleSection::collapsible(
                    'info',
                    'Article'.($article->getAuthors()->notEmpty() ? ' and author' : '').' information',
                    2,
                    $this->render(...$infoSections),
                    true
                );

                $statistics = [];
                $statisticsExtra = [];

                if ($pageViews) {
                    $statistics[] = ViewModel\Statistic::fromNumber('Page views', $pageViews);
                    $statisticsExtra[] = new ViewModel\BarChart($article->getId(), 'article', 'page-views', rtrim($this->getParameter('api_url_public'), '/'), 'page-views', 'month');
                }

                if ($downloads) {
                    $statistics[] = ViewModel\Statistic::fromNumber('Downloads', $downloads);
                    $statisticsExtra[] = new ViewModel\BarChart($article->getId(), 'article', 'downloads', rtrim($this->getParameter('api_url_public'), '/'), 'downloads', 'month');
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

                if (!$this->isGranted('FEATURE_CAN_USE_HYPOTHESIS')) {
                    $parts[] = ArticleSection::collapsible(
                        'comments',
                        'Comments',
                        2,
                        '<div id="disqus_thread">'.$this->render(ViewModel\Button::link('View the discussion thread', 'https://'.$this->getParameter('disqus_domain').'.disqus.com/?url='.urlencode($this->get('router')->generate('article', [$article], UrlGeneratorInterface::ABSOLUTE_URL)))).'</div>',
                        true
                    );
                }

                return $parts;
            });

        $arguments['viewSelector'] = $this->createViewSelector($arguments['article'], $arguments['hasFigures'], false, $arguments['history'], $arguments['body']);

        $arguments['body'] = all(['article' => $arguments['article'], 'body' => $arguments['body'], 'downloadLinks' => $arguments['downloadLinks']])
            ->then(function (array $parts) {
                $article = $parts['article'];
                $body = $parts['body'];
                $downloadLinks = $parts['downloadLinks'];

                $body[] = ArticleSection::basic('Download links', 2, $this->render($downloadLinks));

                $body[] = $this->convertTo($article, ViewModel\ArticleMeta::class);

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

        $allFigures = $this->findFigures($arguments['article']);

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

        $generateDataSets = $arguments['article']
            ->then(function (ArticleVersion $article) {
                return $article->getGeneratedDataSets()
                    ->map(function (DataSet $dataSet, int $id) {
                        return new ViewModel\ReferenceListItem($dataSet->getId(), $id + 1, $this->convertTo($dataSet));
                    });
            });

        $usedDataSets = $arguments['article']
            ->then(function (ArticleVersion $article) {
                return $article->getUsedDataSets()
                    ->map(function (DataSet $dataSet, int $id) {
                        return new ViewModel\ReferenceListItem($dataSet->getId(), $id + 1, $this->convertTo($dataSet));
                    });
            });

        $additionalFiles = $arguments['article']
            ->then(function (ArticleVersion $article) {
                return $article->getAdditionalFiles()->map($this->willConvertTo());
            });

        $arguments['messageBar'] = all([
            'figures' => $figures,
            'videos' => $videos,
            'tables' => $tables,
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
            }));

        $usedDataSets = $usedDataSets
            ->then(Callback::emptyOr(function (Sequence $usedDataSets) {
                return [
                    new ViewModel\MessageBar('The following previously published data sets were used'),
                    new ViewModel\ReferenceList(...$usedDataSets),
                ];
            }));

        $dataSets = all(['generated' => $generateDataSets, 'used' => $usedDataSets])
            ->then(function (array $dataSets) {
                return array_filter(array_merge((array) $dataSets['generated'], (array) $dataSets['used']));
            });

        $arguments['body'] = all([
            'figures' => $figures,
            'videos' => $videos,
            'tables' => $tables,
            'dataSets' => $dataSets,
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

                if (!empty($all['dataSets'])) {
                    $parts[] = ArticleSection::collapsible('data-sets', 'Data sets', 2, $this->render(...$all['dataSets']), false, $first);
                    $first = false;
                }

                if (!empty($all['additionalFiles'])) {
                    $parts[] = ArticleSection::collapsible('files', 'Additional files', 2, $this->render($all['additionalFiles']), false, $first);
                }

                return $parts;
            });

        $arguments['viewSelector'] = $this->createViewSelector($arguments['article'], promise_for(true), true, $arguments['history'], $arguments['body']);

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

        $arguments['article'] = $arguments['article']
            ->then(Callback::methodMustNotBeEmpty('getPublishedDate', new NotFoundHttpException('Article version not published')));

        return new Response($this->get('templating')->render('::article.bib.twig', $arguments), Response::HTTP_OK, ['Content-Type' => 'application/x-bibtex']);
    }

    public function risAction(Request $request, string $id) : Response
    {
        $arguments = $this->defaultArticleArguments($request, $id);

        $arguments['article'] = $arguments['article']
            ->then(Callback::methodMustNotBeEmpty('getPublishedDate', new NotFoundHttpException('Article version not published')));

        return new Response(preg_replace('~\R~u', "\r\n", $this->get('templating')->render('::article.ris.twig', $arguments)), Response::HTTP_OK, ['Content-Type' => 'application/x-research-info-systems']);
    }

    private function defaultArticleArguments(Request $request, string $id, int $version = null) : array
    {
        $article = $this->get('elife.api_sdk.articles')
            ->get($id, $version)
            ->otherwise($this->mightNotExist());

        $arguments = $this->defaultPageArguments($request, $article);

        $arguments['title'] = $article
            ->then(Callback::method('getFullTitle'));

        $arguments['article'] = $article;

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
                return $this->generateTextPath($history, $version);
            });

        $arguments['figuresPath'] = $arguments['history']
            ->then(function (ArticleHistory $history) use ($version) {
                return $this->generateFiguresPath($history, $version);
            });

        $arguments['contentHeader'] = $arguments['article']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['infoBars'] = all(['article' => $arguments['article'], 'history' => $arguments['history'], 'relatedArticles' => $arguments['relatedArticles']])
            ->then(function (array $parts) {
                /** @var ArticleVersion $article */
                $article = $parts['article'];
                /** @var ArticleHistory $history */
                $history = $parts['history'];
                /** @var Sequence|Article[] $relatedArticles */
                $relatedArticles = $parts['relatedArticles'];

                $infoBars = [];

                if ($article->getVersion() < $history->getVersions()[count($history->getVersions()) - 1]->getVersion()) {
                    $infoBars[] = new InfoBar('Read the <a href="'.$this->generateTextPath($history).'">most recent version of this article</a>.', InfoBar::TYPE_MULTIPLE_VERSIONS);
                }

                if ($article instanceof ArticlePoA) {
                    $infoBars[] = new InfoBar('Accepted manuscript, PDF only. Full online edition to follow.');
                }

                if (count($relatedArticles) > 0) {
                    switch ($type = $article->getType()) {
                        case 'correction':
                            $infoBars[] = new InfoBar('This is a correction notice. Read the <a href="'.$this->get('router')->generate('article', [$relatedArticles[0]]).'">corrected article</a>.', InfoBar::TYPE_CORRECTION);
                            break;
                        case 'retraction':
                            $infoBars[] = new InfoBar('This is a retraction notice. Read the <a href="'.$this->get('router')->generate('article', [$relatedArticles[0]]).'">retraction notice</a>.', InfoBar::TYPE_ATTENTION);
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

        $arguments['contextualData'] = all(['article' => $arguments['article'], 'citations' => $arguments['citations'], 'pageViews' => $arguments['pageViews']])
            ->then(function (array $parts) {
                /** @var ArticleVersion $article */
                $article = $parts['article'];
                /** @var CitationsMetric|null $citations */
                $citations = $parts['citations'];
                /** @var int|null $pageViews */
                $pageViews = $parts['pageViews'];

                $metrics = [];

                if (null !== $citations) {
                    $metrics[] = new ViewModel\ContextualDataMetric('Cited', number_format($citations->getHighest()->getCitations()));
                }
                if (null !== $pageViews) {
                    $metrics[] = new ViewModel\ContextualDataMetric('Views', number_format($pageViews));
                }

                if ($this->isGranted('FEATURE_CAN_USE_HYPOTHESIS')) {
                    $metrics[] = new ViewModel\ContextualDataMetric('Annotations', 0, 'annotation-count');
                } else {
                    $metrics[] = new ViewModel\ContextualDataMetric('Comments', 0, 'disqus-comment-count');
                }

                if (!$article->getCiteAs()) {
                    return ContextualData::withMetrics($metrics);
                }

                return ContextualData::withCitation($article->getCiteAs(), new Doi($article->getDoi()), $metrics);
            });

        $arguments['downloadLinks'] = $arguments['article']
            ->then($this->willConvertTo(ViewModel\ArticleDownloadLinksList::class));

        return $arguments;
    }

    private function createViewSelector(PromiseInterface $article, PromiseInterface $hasFigures, bool $isFiguresPage, PromiseInterface $history, PromiseInterface $sections) : PromiseInterface
    {
        return all(['article' => $article, 'hasFigures' => $hasFigures, 'history' => $history, 'sections' => $sections])
            ->then(function (array $sections) use ($isFiguresPage) {
                $article = $sections['article'];
                $hasFigures = $sections['hasFigures'];
                $history = $sections['history'];
                $sections = $sections['sections'];

                if ((count($sections) < 2 || false === $sections[0] instanceof ArticleSection)) {
                    if (!$hasFigures) {
                        return null;
                    }

                    $sections = [];
                }

                return new ViewSelector(
                    $this->generateTextPath($history, $article->getVersion()),
                    array_filter(array_map(function (ViewModel $viewModel) {
                        if ($viewModel instanceof ArticleSection) {
                            return new Link($viewModel['title'], '#'.$viewModel['id']);
                        }

                        return null;
                    }, $sections)),
                    $hasFigures ? $this->generateFiguresPath($history, $article->getVersion()) : null,
                    $isFiguresPage,
                    $article instanceof ArticleVoR
                        ? rtrim($this->getParameter('side_by_side_view_url'), '/').'/'.$article->getId()
                        : null
                );
            });
    }

    private function findFigures(PromiseInterface $article) : PromiseSequence
    {
        return new PromiseSequence($article->then(function (ArticleVersion $article) {
            if (false === $article instanceof ArticleVoR) {
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

            /* @var ArticleVoR $article */
            return $article->getContent()->map($map)->flatten()
                ->filter(function ($item) {
                    return $item instanceof Block\Figure;
                });
        }));
    }

    private function generateTextPath(ArticleHistory $history, int $forVersion = null) : string
    {
        $currentVersion = $history->getVersions()[count($history->getVersions()) - 1];

        if (null === $forVersion) {
            $forVersion = $currentVersion->getVersion();
        }

        if ($forVersion === $currentVersion->getVersion()) {
            return $this->get('router')->generate('article', [$currentVersion]);
        }

        return $this->get('router')->generate('article-version', [$currentVersion, 'version' => $forVersion]);
    }

    private function generateFiguresPath(ArticleHistory $history, int $forVersion = null) : string
    {
        $currentVersion = $history->getVersions()[count($history->getVersions()) - 1];

        if (null === $forVersion) {
            $forVersion = $currentVersion->getVersion();
        }

        if ($forVersion === $currentVersion->getVersion()) {
            return $this->get('router')->generate('article-figures', [$currentVersion]);
        }

        return $this->get('router')->generate('article-version-figures', [$currentVersion, 'version' => $forVersion]);
    }
}
