<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\ArraySequence;
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
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Reviewer;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\HasPages;
use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeaderArticle;
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
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class ArticlesController extends Controller
{
    use HasPages;

    public function textAction(Request $request, string $id, int $version = null) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 3;

        $arguments = $this->articlePageArguments($id, $version);

        /** @var Sequence $recommendations */
        $recommendations = new PromiseSequence($arguments['article']
            ->then(function (ArticleVersion $article) {
                if (in_array($article->getType(), ['correction', 'retraction'])) {
                    return new EmptySequence();
                }

                return $this->get('elife.api_sdk.recommendations')->list('article', $article->getId())->slice(0)
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
            })
            ->then(function (Sequence $furtherReading) use ($arguments) {
                if (count($furtherReading) > 0) {
                    foreach ($arguments['relatedArticles'] as $relatedArticle) {
                        if ($relatedArticle instanceof Article) {
                            if ($furtherReading[0]->getId() === $relatedArticle->getId()) {
                                $relatedItem = $furtherReading[0];
                                $furtherReading = $furtherReading->slice(1);
                                break;
                            }
                        }
                    }
                }

                return [
                    'relatedItem' => $relatedItem ?? null,
                    'furtherReading' => $furtherReading,
                ];
            });

        $arguments['relatedItem'] = $arguments['furtherReading']->then(Callback::pick('relatedItem'));
        $furtherReading = new PromiseSequence($arguments['furtherReading']->then(Callback::pick('furtherReading')));

        $furtherReading = $this->pagerfantaPromise(
            $furtherReading,
            $page,
            $perPage,
            ReadMoreItem::class
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

        unset($arguments['firstFurtherReading']);

        $arguments['title'] = 'Browse further reading';

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(string $id, array $arguments) : Response
    {
        $arguments['relatedItem'] = all(['relatedItem' => $arguments['relatedItem'], 'article' => $arguments['article']])
            ->then(function (array $parts) {
                /** @var Article|null $relatedItem */
                $relatedItem = $parts['relatedItem'];
                /** @var Article $article */
                $article = $parts['article'];

                if (empty($relatedItem)) {
                    return null;
                }

                return $this->convertTo($relatedItem, ViewModel\Teaser::class, ['variant' => 'relatedItem', 'from' => $article->getType()]);
            });

        $arguments['downloads'] = $this->get('elife.api_sdk.metrics')
            ->totalDownloads('article', $id)
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load downloads count'));

        $arguments['body'] = all(['article' => $arguments['article'], 'history' => $arguments['history'], 'citations' => $arguments['citations'], 'downloads' => $arguments['downloads'], 'pageViews' => $arguments['pageViews']])
            ->then(function (array $parts) {
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

                $parts = [];

                $first = true;

                if ($article->getAbstract()) {
                    $parts[] = ArticleSection::collapsible(
                        'abstract',
                        'Abstract',
                        2,
                        $this->render(...$this->convertContent($article->getAbstract())),
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
                        $this->render(...$this->convertContent($article->getDigest())),
                        false,
                        $first,
                        new Doi($article->getDigest()->getDoi())
                    );

                    $first = false;
                }

                $isInitiallyClosed = false;

                if ($article instanceof ArticleVoR) {
                    $parts = array_merge($parts, $article->getContent()->map(function (Block\Section $section) use (&$first, &$isInitiallyClosed) {
                        $section = ArticleSection::collapsible(
                            $section->getId(),
                            $section->getTitle(),
                            2,
                            $this->render(...$this->convertContent($section)),
                            $isInitiallyClosed,
                            $first
                        );

                        $first = false;
                        $isInitiallyClosed = true;

                        return $section;
                    })->toArray());
                }

                if ($article instanceof ArticleVoR) {
                    $parts = array_merge($parts, $article->getAppendices()->map(function (Appendix $appendix) {
                        return ArticleSection::collapsible($appendix->getId(), $appendix->getTitle(), 2,
                            $this->render(...$this->convertContent($appendix)),
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
                        $this->render(...$this->convertContent($article->getDecisionLetter())),
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
                        $this->render(...$this->convertContent($article->getAuthorResponse())),
                        true,
                        false,
                        new Doi($article->getAuthorResponse()->getDoi())
                    );
                }

                $infoSections = [];

                $realAuthors = $article->getAuthors()->filter(Callback::isInstanceOf(Author::class));

                $personAuthors = $realAuthors->filter(Callback::isInstanceOf(PersonAuthor::class));

                if ($personAuthors->notEmpty()) {
                    $infoSections[] = new ViewModel\AuthorsDetails(
                        ...$personAuthors->map($this->willConvertTo(null, ['authors' => $realAuthors]))
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

                if ($article instanceof ArticleVoR && $article->getEthics()->notEmpty()) {
                    $infoSections[] = ArticleSection::basic(
                        'Ethics',
                        3,
                        $this->render(...$article->getEthics()->map($this->willConvertTo(null, ['level' => 3])))
                    );
                }

                if ($article instanceof ArticleVoR && $article->getReviewers()->notEmpty()) {
                    $infoSections[] = ArticleSection::basic(
                        'Reviewers',
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
                    $copyright = sprintf('<p>© %s, %s.</p>', 2011 + $article->getVolume(), $article->getCopyright()->getHolder()).$copyright;
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
                }

                if ($downloads) {
                    $statistics[] = ViewModel\Statistic::fromNumber('Downloads', $downloads);
                }

                if ($citations) {
                    $statistics[] = ViewModel\Statistic::fromNumber('Citations', $citations->getHighest()->getCitations());
                    $statisticsExtra[] = new Paragraph('Article citation count generated by polling the highest count across the following the
sources: '.implode(', ', array_map(function (CitationsMetricSource $source) {
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

        $arguments['viewSelector'] = $this->createViewSelector($arguments['article'], $arguments['hasFigures'], $arguments['history'], $arguments['body']);

        $arguments['body'] = all(['article' => $arguments['article'], 'body' => $arguments['body']])
            ->then(function (array $parts) {
                $article = $parts['article'];
                $body = $parts['body'];

                $downloadLinks = $this->convertTo($article, ViewModel\ArticleDownloadLinksList::class);

                $body[] = ArticleSection::basic('Download links', 2, $this->render($downloadLinks));

                $body[] = $this->convertTo($article, ViewModel\ArticleMeta::class);

                return $body;
            });

        return new Response($this->get('templating')->render('::article.html.twig', $arguments));
    }

    public function figuresAction(string $id, int $version = null) : Response
    {
        $arguments = $this->articlePageArguments($id, $version);

        $arguments['title'] = $arguments['title']
            ->then(function (string $title) {
                return 'Figures in '.$title;
            });

        $allFigures = $this->findFigures($arguments['article']);

        $figures = $allFigures
            ->filter(Callback::isInstanceOf(Block\Image::class))
            ->map($this->willConvertTo(null, ['complete' => true]));

        $videos = $allFigures
            ->filter(Callback::isInstanceOf(Block\Video::class))
            ->map($this->willConvertTo(null, ['complete' => true]));

        $tables = $allFigures
            ->filter(Callback::isInstanceOf(Block\Table::class))
            ->map($this->willConvertTo(null, ['complete' => true]));

        $generateDataSets = $arguments['article']
            ->then(function (ArticleVersion $article) {
                return $article->getGeneratedDataSets()
                    ->map(function (DataSet $dataSet, int $id) {
                        return new ViewModel\ReferenceListItem($dataSet->getId(), $id + 1, $this->convertTo($dataSet));
                    });
            })
            ->then(Callback::emptyOr(function (Sequence $generatedDataSets) {
                return [
                    new ViewModel\MessageBar('The following data sets were generated'),
                    new ViewModel\ReferenceList(...$generatedDataSets),
                ];
            }));

        $usedDataSets = $arguments['article']
            ->then(function (ArticleVersion $article) {
                return $article->getUsedDataSets()
                    ->map(function (DataSet $dataSet, int $id) {
                        return new ViewModel\ReferenceListItem($dataSet->getId(), $id + 1, $this->convertTo($dataSet));
                    });
            })
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

        $additionalFiles = $arguments['article']
            ->then(function (ArticleVersion $article) {
                return $article->getAdditionalFiles()->map($this->willConvertTo());
            })
            ->then(Callback::emptyOr(function (Sequence $files) {
                return new ViewModel\AdditionalAssets(null, $files->toArray());
            }));

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
            })
            ->then(Callback::mustNotBeEmpty(new NotFoundHttpException('Article version does not contain any figures')));

        $arguments['viewSelector'] = $this->createViewSelector($arguments['article'], promise_for(true), $arguments['history'], $arguments['body']);

        return new Response($this->get('templating')->render('::article-figures.html.twig', $arguments));
    }

    public function bibTexAction(string $id) : Response
    {
        $arguments = $this->defaultArticleArguments($id);

        $arguments['article'] = $arguments['article']
            ->then(Callback::methodMustNotBeEmpty('getPublishedDate', new NotFoundHttpException('Article version not published')));

        return new Response($this->get('templating')->render('::article.bib.twig', $arguments), Response::HTTP_OK, ['Content-Type' => 'application/x-bibtex']);
    }

    public function risAction(string $id) : Response
    {
        $arguments = $this->defaultArticleArguments($id);

        $arguments['article'] = $arguments['article']
            ->then(Callback::methodMustNotBeEmpty('getPublishedDate', new NotFoundHttpException('Article version not published')));

        return new Response(preg_replace('~\R~u', "\r\n", $this->get('templating')->render('::article.ris.twig', $arguments)), Response::HTTP_OK, ['Content-Type' => 'application/x-research-info-systems']);
    }

    private function defaultArticleArguments(string $id, int $version = null) : array
    {
        $article = $this->get('elife.api_sdk.articles')
            ->get($id, $version)
            ->otherwise($this->mightNotExist());

        $arguments = $this->defaultPageArguments($article);

        $arguments['title'] = $article
            ->then(Callback::method('getFullTitle'));

        $arguments['article'] = $article;

        return $arguments;
    }

    private function articlePageArguments(string $id, int $version = null) : array
    {
        $arguments = $this->defaultArticleArguments($id, $version);

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
            ->then($this->willConvertTo(ContentHeaderArticle::class));

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

                switch ($type = $article->getType()) {
                    case 'correction':
                        $infoBars[] = new InfoBar('This is a correction notice. Read the <a href="'.$this->get('router')->generate('article', ['id' => $relatedArticles[0]->getId()]).'">corrected article</a>.', InfoBar::TYPE_CORRECTION);
                        break;
                    case 'retraction':
                        $infoBars[] = new InfoBar('This is a retraction notice. Read the <a href="'.$this->get('router')->generate('article', ['id' => $relatedArticles[0]->getId()]).'">retraction notice</a>.', InfoBar::TYPE_ATTENTION);
                        break;
                }

                foreach ($relatedArticles as $relatedArticle) {
                    switch ($relatedArticle->getType()) {
                        case 'correction':
                            $infoBars[] = new InfoBar('This article has been corrected. Read the <a href="'.$this->get('router')->generate('article', ['id' => $relatedArticle->getId()]).'">correction notice</a>.', InfoBar::TYPE_CORRECTION);
                            break;
                        case 'retraction':
                            $infoBars[] = new InfoBar('This article has been retracted. Read the <a href="'.$this->get('router')->generate('article', ['id' => $relatedArticle->getId()]).'">retraction notice</a>.', InfoBar::TYPE_ATTENTION);
                            break;
                    }
                }

                return $infoBars;
            });

        $arguments['citations'] = $this->get('elife.api_sdk.metrics')
            ->citations('article', $id)
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load citations count'));

        $arguments['pageViews'] = $this->get('elife.api_sdk.metrics')
            ->totalPageViews('article', $id)
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

                if (!$article->getCiteAs()) {
                    if (!empty($metrics)) {
                        return ContextualData::withMetrics($metrics);
                    }

                    return null;
                }

                return ContextualData::withCitation($article->getCiteAs(), new Doi($article->getDoi()), $metrics);
            });

        return $arguments;
    }

    private function createViewSelector(PromiseInterface $article, PromiseInterface $hasFigures, PromiseInterface $history, PromiseInterface $sections) : PromiseInterface
    {
        return all(['article' => $article, 'hasFigures' => $hasFigures, 'history' => $history, 'sections' => $sections])
            ->then(function (array $sections) {
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
                    $hasFigures ? $this->generateFiguresPath($history, $article->getVersion()) : null
                );
            });
    }

    private function findFigures(PromiseInterface $article) : PromiseSequence
    {
        return new PromiseSequence($article->then(function (ArticleVersion $article) {
            if (false === $article instanceof ArticleVoR) {
                return new EmptySequence();
            }

            /* @var ArticleVoR $article */
            $blocks = $article->getContent()->reverse()->toArray();
            $figures = [];
            while (!empty($blocks)) {
                $block = array_shift($blocks);
                switch (get_class($block)) {
                    case Block\Image::class:
                        if ($block->getImage()->getLabel()) {
                            array_unshift($figures, $block);
                        }
                        break;
                    case Block\Table::class:
                    case Block\Video::class:
                        if ($block->getLabel()) {
                            array_unshift($figures, $block);
                        }
                        break;
                    case Block\Box::class:
                    case Block\Section::class:
                        foreach ($block->getContent() as $element) {
                            array_unshift($blocks, $element);
                        }
                        break;
                    case Block\Listing::class:
                        foreach ($block->getItems() as $listItem) {
                            if (is_array($listItem)) {
                                foreach ($listItem as $listItemElement) {
                                    array_unshift($blocks, $listItemElement);
                                }
                            }
                        }
                        break;
                }
            }

            return new ArraySequence($figures);
        }));
    }

    private function generateTextPath(ArticleHistory $history, int $forVersion = null) : string
    {
        $currentVersion = $history->getVersions()[count($history->getVersions()) - 1];

        if (null === $forVersion) {
            $forVersion = $currentVersion->getVersion();
        }

        if ($forVersion === $currentVersion->getVersion()) {
            return $this->get('router')->generate('article', ['id' => $currentVersion->getId()]);
        }

        return $this->get('router')->generate('article-version', ['id' => $currentVersion->getId(), 'version' => $forVersion]);
    }

    private function generateFiguresPath(ArticleHistory $history, int $forVersion = null) : string
    {
        $currentVersion = $history->getVersions()[count($history->getVersions()) - 1];

        if (null === $forVersion) {
            $forVersion = $currentVersion->getVersion();
        }

        if ($forVersion === $currentVersion->getVersion()) {
            return $this->get('router')->generate('article-figures', ['id' => $currentVersion->getId()]);
        }

        return $this->get('router')->generate('article-version-figures', ['id' => $currentVersion->getId(), 'version' => $forVersion]);
    }
}
