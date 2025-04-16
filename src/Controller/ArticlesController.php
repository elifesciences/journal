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
use eLife\ApiSdk\Model\PublicReview;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Reviewer;
use eLife\Journal\Exception\EarlyResponse;
use eLife\Journal\ViewModel\Converter\AssessmentBuilder;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\DownloadLink;
use eLife\Journal\Helper\HasPages;
use eLife\Journal\Helper\Humanizer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentAside;
use eLife\Patterns\ViewModel\ContentHeaderNew;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\Doi;
use eLife\Patterns\ViewModel\JumpMenu;
use eLife\Patterns\ViewModel\InfoBar;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\ProcessBlock;
use eLife\Patterns\ViewModel\ReadMoreItem;
use eLife\Patterns\ViewModel\SpeechBubble;
use eLife\Patterns\ViewModel\TabbedNavigation;
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
    private $pageRequest;

    public function textAction(Request $request, string $id, int $version = null) : Response
    {
        $this->pageRequest = $request;
        $page = (int) $request->query->get('page', 1);
        $perPage = 3;

        $arguments = $this->articlePageArguments($request, $id, $version);

        /** @var Sequence $recommendations */
        $recommendations = new PromiseSequence($arguments['item']
            ->then(function (ArticleVersion $item) {
                if (in_array($item->getType(), ['correction', 'expression-concern', 'retraction'])) {
                    return new EmptySequence();
                }
                
                return $this->get('elife.api_sdk.recommendations')->list($item->getIdentifier())->slice(0, 100)
                    ->otherwise($this->mightNotExist())
                    ->otherwise($this->softFailure('Failed to load recommendations',
                    $item->getId() === '100254' ? HardcodedRecommendationsFor100254::build() : new EmptySequence()
                    ));
            }));

        $arguments['furtherReading'] = $recommendations
            ->filter(function (Model $model) use ($arguments) {
                // Remove corrections, expressions of concern and retractions for this article.
                if ($model instanceof ArticleVersion && in_array($model->getType(), ['correction', 'expression-concern', 'retraction'])) {
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
                if (in_array($parts['item']->getType(), ['correction', 'expression-concern', 'retraction'])) {
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

                $item = $this->convertTo($relatedItem, ViewModel\Teaser::class, ['variant' => 'relatedItem', 'from' => $item->getType(), 'related' => $related ?? false, 'updatedText' => false]);

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

        $arguments['hasFigures'] = $this->checkHasFigures($arguments['item'], $figures);

        $arguments['hasPeerReview'] = $this->hasPeerReview($arguments['item'], $arguments['isMagazine'], $arguments['history']);

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

        $arguments = $this->contentAsideArguments($arguments);

        $arguments['body'] = all(['item' => $arguments['item'], 'isMagazine' => $arguments['isMagazine'], 'history' => $arguments['history'], 'citations' => $arguments['citations'],'version1Citations' => $arguments['citationsForVersion1'],'version2Citations' => $arguments['citationsForVersion2'],  'version3Citations' => $arguments['citationsForVersion3'], 'downloads' => $arguments['downloads'], 'pageViews' => $arguments['pageViews'], 'data' => $arguments['hasData'], 'context' => $context])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var bool $isMagazine */
                $isMagazine = $parts['isMagazine'];
                /** @var ArticleHistory $history */
                $history = $parts['history'];
                /** @var CitationsMetric|null $citations */
                $citations = $parts['citations'];
                /** @var CitationsMetric|null $version1Citations */
                $version1Citations = $parts['version1Citations'];
                /** @var CitationsMetric|null $version2Citations */
                $version2Citations = $parts['version2Citations'];
                /** @var CitationsMetric|null $version3Citations */
                $version3Citations = $parts['version3Citations'];
                /** @var int|null $downloads */
                $downloads = $parts['downloads'];
                /** @var int|null $pageViews */
                $pageViews = $parts['pageViews'];
                /** @var Sequence $data */
                $data = $parts['data'];
                /** @var array $context */
                $context = $parts['context'];

                $citationsForAllVersions = [ '1' => $version1Citations, '2' => $version2Citations, '3' => $version3Citations];

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

                    $relatedLinks[] = new Link('eLife\'s review process', $this->get('router')->generate('about-pubpub', ['type'=> 'peer-review']));

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

                if ($item->getType() === 'feature' && $item instanceof ArticleVoR && $item->getDecisionLetter()) {
                    $parts[] = ArticleSection::collapsible(
                        $item->getDecisionLetter()->getId() ?? 'decision-letter',
                        'Decision letter',
                        2,
                        $this->render($this->convertTo($item, ViewModel\DecisionLetterHeader::class)).
                        $this->render(...$this->convertContent($item->getDecisionLetter(), 2, $context)),
                        null,
                        null,
                        true,
                        $first,
                        $item->getDecisionLetter()->getDoi() ? new Doi($item->getDecisionLetter()->getDoi()) : null
                    );

                    $first = false;
                }

                if ($item->getType() === 'feature' && $item instanceof ArticleVoR && $item->getAuthorResponse()) {
                    $parts[] = ArticleSection::collapsible(
                        $item->getAuthorResponse()->getId() ?? 'author-response',
                        'Author response',
                        2,
                        $this->render(...$this->convertContent($item->getAuthorResponse(), 2, $context)),
                        null,
                        null,
                        true,
                        $first,
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
                            $headerLink = null;

                            if ($award->getAwardDoi()) {
                                $headerLinkDoi = "https://doi.org/{$award->getAwardDoi()}";
                                $headerLink = new ViewModel\Link(
                                    $headerLinkDoi,
                                    $headerLinkDoi
                                );
                            } else if ($award->getAwardId()) {
                                $title .= ' ('.$award->getAwardId().')';
                            }

                            $recipients = $award->getRecipients()->notEmpty() ? $award->getRecipients()
                                ->map(Callback::method('toString'))
                                ->toArray() : ['No recipients declared.'];

                            $body = Listing::unordered(
                                $recipients,
                                'bullet'
                            );

                            return ArticleSection::basic($this->render($body), $title, 4, null, null, null, null, false, $headerLink);
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

                if (!$item->getReviewers()->isEmpty() && !$this->hasPeerReview($item, $isMagazine, $history)->wait()) {
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
                        if (count($reviewers) > 2) {
                            $role = "${role}s";
                        }

                        $infoSections[] = ArticleSection::basic(
                            $this->render(Listing::ordered($reviewers)),
                            $role,
                            3
                        );
                    }
                }

                if ($item->getEthics()->notEmpty()) {
                    $infoSections[] = ArticleSection::basic(
                        $this->render(...$item->getEthics()->map($this->willConvertTo(null, ['level' => 3]))),
                        'Ethics',
                        3
                    );
                }

                if ($item instanceof ArticleVoR && (
                        $item->isReviewedPreprint() ||
                        in_array($item->getType(), ['feature', 'correction', 'expression-concern', 'retraction']) ||
                        $isMagazine)
                    ) {
                    $publicationHistory = $this->generatePublicationHistoryForNewVor($history);
                    $publicationHistoryTitle = ($isMagazine || 'feature' === $item->getType()) ? 'Publication history' : 'Version history';
                    $infoSections[] = ArticleSection::basic(
                        $this->render(
                            Listing::ordered($publicationHistory, 'bullet')
                        ),
                        $publicationHistoryTitle,
                        3
                    );
                }

                if ($item instanceof ArticleVoR && $item->isReviewedPreprint()) {
                    $infoSections[] = ArticleSection::basic(
                        sprintf('<p>You can cite all versions using the DOI  <a href="https://doi.org/%s">https://doi.org/%s</a>. This DOI represents all versions, and will always resolve to the latest one.</p>', $item->getDoi(), $item->getDoi()),
                        'Cite all versions', 3
                    );
                }

                $copyright = '<p>'.$item->getCopyright()->getStatement().'</p>';

                if ($item->getCopyright()->getHolder()) {
                    $copyright = sprintf('<p>© %s, %s</p>', 2011 + $item->getVolume(), $item->getCopyright()->getHolder()).$copyright;
                }

                $infoSections[] = ArticleSection::basic($copyright, 'Copyright', 3, 'copyright');

                $parts[] = ArticleSection::collapsible(
                    'info',
                    'Article'.($item->getAuthors()->notEmpty() ? ' and author' : '').' information',
                    2,
                    $this->render(...$infoSections),
                    null,
                    null,
                    true
                );
                if ($pageViews || $downloads || $citations) {
                    $itemId = $item->getId();
                    $apiEndPoint = rtrim($this->getParameter('api_url_public'), '/');
                    $metrics = Metrics::build($this->pageRequest, $apiEndPoint, $itemId, $pageViews, $downloads, $citations, $citationsForAllVersions);

                    $parts[] = ArticleSection::collapsible(
                        'metrics',
                        'Metrics',
                        2,
                        $this->render(...$metrics),
                        null,
                        null,
                        true
                    );
                }

                return $parts;
            });

        $arguments['viewSelector'] = $this->createViewSelector($arguments['item'], $arguments['isMagazine'], $arguments['hasFigures'], false, $arguments['history'], $arguments['body'], $arguments['eraArticle']);

        $arguments['tabbedNavigation'] = $this->createTabbedNavigation($arguments['item'], $arguments['isMagazine'], $arguments['hasFigures'], 'fullText', $arguments['history'], $arguments['body'], $arguments['eraArticle'], $arguments['hasPeerReview']);

        $arguments['assessmentBlock'] = all(['item' => $arguments['item'], 'context' => $context])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var array $context */
                $context = $parts['context'];
                if ($item instanceof ArticleVoR && $item->getElifeAssessment()) {
                    $elifeAssessment = $item->getElifeAssessment();
                    $elifeAssessmentArticlesSection = $elifeAssessment->getArticleSection();
                    $assessmentBuilder = new AssessmentBuilder();
                    return ArticleSection::basic(
                        $this->render(...$this->convertContent($elifeAssessmentArticlesSection, 2, $context)),
                        $elifeAssessment->getTitle(),
                        2,
                        'elife-assessment',
                        $elifeAssessmentArticlesSection->getDoi() ? new Doi($elifeAssessmentArticlesSection->getDoi()) : null,
                        null,
                        ArticleSection::STYLE_HIGHLIGHTED,
                        false,
                        null,
                        null,
                        null,
                        null,
                        $assessmentBuilder->build($elifeAssessmentArticlesSection)
                    );
                }
            });

        $arguments['jumpMenu'] = $this->createJumpMenu($arguments['item'], $arguments['isMagazine'], $arguments['hasFigures'], false, $arguments['history'], $arguments['body'], $arguments['eraArticle']);

        $arguments['body'] = all(['item' => $arguments['item'], 'body' => $arguments['body'], 'downloadLinks' => $arguments['downloadLinks'], 'isMagazine' => $arguments['isMagazine']])
            ->then(function (array $parts) {
                $item = $parts['item'];
                $body = $parts['body'];
                $downloadLinks = $parts['downloadLinks'];
                $isMagazine = $parts['isMagazine'];

                $body[] = ArticleSection::basic($this->render($downloadLinks), 'Download links', 2);


                if (!$isMagazine && 'feature' !== $item->getType()) {
                    $share[] = new Doi($item->getDoi());
                    $share[] = new ViewModel\SocialMediaSharersNew(
                        strip_tags($item->getFullTitle()),
                        "https://doi.org/{$item->getDoi()}",
                        true,
                        true
                    );
                    $body[] =  ArticleSection::basic($this->render(...$share), 'Share this article', 3,
                        'share', null, null, null, false, null, null, 'article-section__sharers');
                }


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

        $arguments['hasPeerReview'] = $this->hasPeerReview($arguments['item'], $arguments['isMagazine'], $arguments['history']);

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

        $arguments['tabbedNavigation'] = $this->createTabbedNavigation($arguments['item'], $arguments['isMagazine'], promise_for(true), 'figures', $arguments['history'], $arguments['body'], $arguments['eraArticle'], $arguments['hasPeerReview']);

        $arguments['jumpMenu'] = $this->createJumpMenu($arguments['item'], $arguments['isMagazine'], promise_for(true), true, $arguments['history'], $arguments['body'], $arguments['eraArticle']);

        $arguments = $this->contentAsideArguments($arguments);

        $arguments['body'] = all(['body' => $arguments['body'], 'downloadLinks' => $arguments['downloadLinks']])
            ->then(function (array $parts) {
                $body = $parts['body'];
                $downloadLinks = $parts['downloadLinks'];

                $body[] = ArticleSection::basic($this->render($downloadLinks), 'Download links', 2);

                return $body;
            });

        return new Response($this->get('templating')->render('::article-figures.html.twig', $arguments));
    }

    public function peerReviewsAction(Request $request, string $id, int $version = null) : Response
    {
        $arguments = $this->articlePageArguments($request, $id, $version);

        $arguments['title'] = $arguments['title']
            ->then(function (string $title) {
                return 'Peer review in '.$title;
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

        $figures = $this->findFigures($arguments['item'])->then(Callback::method('notEmpty'));

        $bioprotocols = $this->get('elife.api_sdk.bioprotocols')
            ->list(Identifier::article($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load bioprotocols', []));

        $context = all(['item' => $arguments['item'], 'history' => $arguments['history'], 'hasFigures' => $figures, 'bioprotocols' => $bioprotocols])
            ->then(function (array $parts) {
                $context = [];
                if ($parts['hasFigures']) {
                    $context['figuresUri'] = $this->generatePath($parts['history'], $parts['item']->getVersion(), 'figures');
                }

                $context['bioprotocols'] = $parts['bioprotocols'];

                return $context;
            });

        $arguments['hasFigures'] = $this->checkHasFigures($arguments['item'], $figures);

        $arguments['body'] = all(['item' => $arguments['item'], 'isMagazine' => $arguments['isMagazine'], 'context' => $context, 'history' => $arguments['history']])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var bool $isMagazine */
                $isMagazine = $parts['isMagazine'];
                /** @var array $context */
                $context = $parts['context'];
                /** @var ArticleHistory $history */
                $history = $parts['history'];
                /** @var array $parts */
                $parts = [];
                /** @var array $relatedLinks */
                $relatedLinks = [];

                if ($item instanceof ArticleVoR && $item->isReviewedPreprint()) {
                    $peerReviewText = new Paragraph('<strong>Version of Record: </strong>This is the final version of the article.');
                    $peerReview[] = ArticleSection::basic(
                        $this->render(
                            new ProcessBlock(
                                $this->render($peerReviewText),
                                'vor',
                                new Link(
                                    'Read more about eLife\'s peer review process.',
                                    $this->get('router')->generate('about-pubpub', ['type' => 'peer-review'])
                                )
                            )
                        )
                    );
                } else if ($item instanceof ArticleVoR && !$item->isReviewedPreprint()) {
                    $peerReview[] = new Paragraph('This article was accepted for publication as part of eLife\'s original publishing model.');
                } else if ($item instanceof ArticlePoA) {
                    $peerReview[] = new Paragraph('This article was accepted for publication via eLife\'s original publishing model. eLife publishes the authors\' accepted manuscript as a PDF only version before the full Version of Record is ready for publication. Peer reviews are published along with the Version of Record.');
                }

                if ($item instanceof ArticleVoR && !$item->isReviewedPreprint() || $item instanceof ArticlePoA) {
                    $publicationHistory = $this->generatePublicationHistoryForOldVorAndPoa($history);
                    $publicationHistoryTitle = 'History';

                    $peerReview[] = ArticleSection::basic(
                        $this->render(
                            Listing::ordered($publicationHistory, 'line')
                        ),
                        $publicationHistoryTitle,
                        3
                    );

                    $preprints = $history->getVersions()
                        ->filter(Callback::isInstanceOf(ArticlePreprint::class))
                        ->toArray();

                    if (isset($preprints[0])) {
                        $uri = $preprints[0]->getUri();
                        $relatedLinks[] = new Link('Go to the preprint', $uri);
                    }
                }

                $parts[] = ArticleSection::collapsible(
                    'peer-review-process',
                    'Peer review process',
                    2,
                    $this->render(...$peerReview),
                    $relatedLinks ? $relatedLinks : null,
                    ArticleSection::STYLE_PEER_REVIEW,
                    true,
                    true,
                    null,
                    $relatedLinks ? ArticleSection::RELATED_LINKS_SEPARATOR_CIRCLE : null
                );

                if ($item instanceof ArticleVoR && $item->isReviewedPreprint()) {
                    $roles = $item->getReviewers()
                        ->reduce(function (array $roles, Reviewer $reviewer) {
                            $entry = $reviewer->getPreferredName();

                            $roles[$reviewer->getRole()][] = $entry;

                            foreach ($reviewer->getAffiliations() as $affiliation) {
                                $roles[$reviewer->getRole()][] = $affiliation->toString();
                            }

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
                        if (count($reviewers) > 2) {
                            $role = "${role}s";
                        }

                        $infoSections[] = ArticleSection::basic(
                            $this->render(Listing::ordered($reviewers)),
                            $role, null, null, null, null, 'editor', false, null, null, null, true
                        );
                    }

                    $parts[] = ArticleSection::collapsible(
                        'editors',
                        'Editors',
                        2,
                        $this->render(...$infoSections),
                        null,
                        null,
                        true
                    );
                }


                if ($item instanceof ArticleVoR && !$item->isReviewedPreprint() && $item->getDecisionLetter()) {
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

                if ($item instanceof ArticleVoR && $item->getPublicReviews()->notEmpty()) {
                    $publicReviews = $item->getPublicReviews()->map(function (PublicReview $publicReview, $index) use ($context) {
                        $publicReviewSection = ArticleSection::collapsible(
                            $publicReview->getId(),
                            $publicReview->getTitle(),
                            2,
                            $this->render(...$this->convertContent($publicReview, 3, $context)),
                            null,
                            null,
                            false,
                            false,
                            $publicReview->getDoi() ? new Doi($publicReview->getDoi()) : null
                        );

                        return $publicReviewSection;
                    })->toArray();

                    $parts = array_merge($parts, $publicReviews);

                    if ($item->getRecommendationsForAuthors()) {
                        $parts[] = ArticleSection::collapsible(
                            $item->getRecommendationsForAuthors()->getId(),
                            $item->getRecommendationsForAuthorsTitle(),
                            2,
                            $this->render(...$this->convertContent($item->getRecommendationsForAuthors(), 3, $context)),
                            null,
                            null,
                            false,
                            false,
                            $item->getRecommendationsForAuthors()->getDoi() ? new Doi($item->getRecommendationsForAuthors()->getDoi()) : null
                        );
                    }
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

                return $parts;
            })
            ->then(Callback::mustNotBeEmpty(new NotFoundHttpException('Article version does not contain any figures or data')));

        $arguments['tabbedNavigation'] = $this->createTabbedNavigation($arguments['item'], $arguments['isMagazine'], $arguments['hasFigures'], 'peerReviews', $arguments['history'], $arguments['body'], $arguments['eraArticle'], promise_for(true));

        $arguments['jumpMenu'] = $this->createJumpMenu($arguments['item'], $arguments['isMagazine'], promise_for(true), true, $arguments['history'], $arguments['body'], $arguments['eraArticle']);

        $arguments = $this->contentAsideArguments($arguments);

        $arguments['body'] = all(['item' => $arguments['item'], 'body' => $arguments['body'], 'downloadLinks' => $arguments['downloadLinks'], 'isMagazine' => $arguments['isMagazine']])
            ->then(function (array $parts) {
                $item = $parts['item'];
                $body = $parts['body'];
                $downloadLinks = $parts['downloadLinks'];
                $isMagazine = $parts['isMagazine'];

                $body[] = ArticleSection::basic($this->render($downloadLinks), 'Download links', 2);


                if (!$isMagazine && 'feature' !== $item->getType()) {
                    $share[] = new Doi($item->getDoi());
                    $share[] = new ViewModel\SocialMediaSharersNew(
                        strip_tags($item->getFullTitle()),
                        "https://doi.org/{$item->getDoi()}",
                        true,
                        true
                    );

                    $body[] =  ArticleSection::basic($this->render(...$share), 'Share this article', 3,
                        'share', null, null, null, false, null, null, 'article-section__sharers');
                }

                return $body;
            });

        return new Response($this->get('templating')->render('::article-peer-reviews.html.twig', $arguments));
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

        $arguments['infoBars'] = all(['item' => $arguments['item'], 'history' => $arguments['history'], 'relatedArticles' => $arguments['relatedArticles'], 'eraArticle' => $arguments['eraArticle'], 'isMagazine' => $arguments['isMagazine']])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var ArticleHistory $history */
                $history = $parts['history'];
                /** @var Sequence|Article[] $relatedArticles */
                $relatedArticles = $parts['relatedArticles'];
                /** @var array $eraArticle */
                $eraArticle = $parts['eraArticle'];
                /** @var bool $isMagazine */
                $isMagazine = $parts['isMagazine'];

                $infoBars = [];

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
                    switch ($item->getType()) {
                        case 'correction':
                            $infoBars[] = new InfoBar('This is a correction notice. Read the <a href="'.$this->get('router')->generate('article', [$relatedArticles[0]]).'">corrected article</a>.', InfoBar::TYPE_CORRECTION);
                            break;
                        case 'expression-concern':
                            $infoBars[] = new InfoBar('This is an expression of concern. Read the <a href="'.$this->get('router')->generate('article', [$relatedArticles[0]]).'">related article</a>.', InfoBar::TYPE_ATTENTION);
                            break;
                        case 'retraction':
                            $infoBars[] = new InfoBar('This is a retraction notice. Read the <a href="'.$this->get('router')->generate('article', [$relatedArticles[0]]).'">retracted article</a>.', InfoBar::TYPE_ATTENTION);
                            break;
                    }

                    foreach ($relatedArticles as $relatedArticle) {
                        if (!($relatedArticle instanceof ArticleVersion)) {
                            continue;
                        }

                        switch ($relatedArticle->getType()) {
                            case 'correction':
                                $infoBars[] = new InfoBar('This article has been corrected. Read the <a href="'.$this->get('router')->generate('article', [$relatedArticle]).'">correction notice</a>.', InfoBar::TYPE_CORRECTION);
                                break;
                            case 'expression-concern':
                                $infoBars[] = new InfoBar('Concern(s) have been raised about this article. Read the <a href="'.$this->get('router')->generate('article', [$relatedArticle]).'">expression of concern</a>.', InfoBar::TYPE_ATTENTION);
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

        $arguments['citationsForVersion1'] = $this->get('elife.api_sdk.metrics')
            ->versionCitations(Identifier::article($id), 1)
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load version citations count'));

        $arguments['citationsForVersion2'] = $this->get('elife.api_sdk.metrics')
            ->versionCitations(Identifier::article($id), 2)
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load version citations count'));

        $arguments['citationsForVersion3'] = $this->get('elife.api_sdk.metrics')
            ->versionCitations(Identifier::article($id), 3)
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load version citations count'));

        $arguments['pageViews'] = $this->get('elife.api_sdk.metrics')
            ->totalPageViews(Identifier::article($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load page views count'));

        $arguments['downloads'] = $this->get('elife.api_sdk.metrics')
            ->totalDownloads(Identifier::article($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load downloads count'));

        $arguments['contextualDataMetrics'] = all(['item' => $arguments['item'], 'history' => $arguments['history'], 'citations' => $arguments['citations'], 'pageViews' => $arguments['pageViews'], 'downloads'=> $arguments['downloads']])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];
                /** @var ArticleHistory $history */
                $history = $parts['history'];
                /** @var CitationsMetric|null $citations */
                $citations = $parts['citations'];
                /** @var int|null $pageViews */
                $pageViews = $parts['pageViews'];
                /** @var int|null $downloads */
                $downloads = $parts['downloads'];

                $metricLink = function (int $count, string $suffix) use ($history, $item) {
                    // @todo - improve pattern-library or patterns-php so class doesn't need to be set here.
                    return sprintf('<a href="%s"><span class="contextual-data__counter">%s</span> %s</a>', $this->generatePath($history, $item->getVersion(), null, 'metrics'), number_format($count), $suffix);
                };

                $metrics = [];

            if (null !== $pageViews && $pageViews > 0) {
                $metrics[] = $metricLink($pageViews, 'views');
            }
            if (null !== $downloads && $downloads > 0) {
                $metrics[] = $metricLink($downloads, 'downloads');
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

    private function createTabbedNavigation(PromiseInterface $item, PromiseInterface $isMagazine, PromiseInterface $hasFigures, string $pageType, PromiseInterface $history, PromiseInterface $sections, array $eraArticle, PromiseInterface $hasPeerReview) : PromiseInterface
    {
        return all(['item' => $item, 'isMagazine' => $isMagazine, 'hasFigures' => $hasFigures, 'history' => $history, 'sections' => $sections, 'hasPeerReview' => $hasPeerReview])
            ->then(function (array $sections) use ($pageType, $eraArticle) {

                $history = $sections['history'];
                $item = $sections['item'];
                $hasFigures = $sections['hasFigures'];
                $hasPeerReview = $sections['hasPeerReview'];
                $links = [];

                if ($sections['isMagazine'] || 'feature' === $item->getType()) {
                    return false;
                }
                $links[] = ViewModel\TabbedNavigationLink::fromLink(
                                    new Link('Full text', $this->generatePath($history, $item->getVersion(), null, 'content')),
                                    $pageType === 'fullText' ? " tabbed-navigation__tab-label--active" : null
                                );

                if ($hasFigures) {
                    $links[] = ViewModel\TabbedNavigationLink::fromLink(
                                    new Link('Figures<span class="tabbed-navigation__tab-label--long"> and data</span>', $this->generatePath($history, $item->getVersion(), 'figures', 'content')),
                                    $pageType === 'figures' ? " tabbed-navigation__tab-label--active" : null
                                );
                }

                $articleVersions = $history->getVersions()
                    ->filter(Callback::isInstanceOf(ArticleVersion::class))
                    ->toArray();

                $latestVersion = $articleVersions[count($articleVersions) - 1]->getVersion();
                if (isset($eraArticle['display']) && $item->getVersion() === $latestVersion) {
                    $links[] = ViewModel\TabbedNavigationLink::fromLink(new Link(
                        'Executable code',
                        $this->get('router')->generate('article-era', ['id' => $item->getId()])
                    ));
                }

                if ($hasPeerReview) {
                    $links[] = ViewModel\TabbedNavigationLink::fromLink(
                                        new Link('Peer review', $this->generatePath($history, $item->getVersion(), 'peer-reviews', 'content')),
                                        $pageType === 'peerReviews' ? " tabbed-navigation__tab-label--active" : null
                                );
                }

                if ($item instanceof ArticleVoR) {
                    $sideBySideUrl = rtrim($this->getParameter('side_by_side_view_url'), '/').'/'.$item->getId();
                    $links[] = ViewModel\TabbedNavigationLink::fromLink(
                        new Link('Side by side', $sideBySideUrl), null, true
                    );
                }

                return new TabbedNavigation($links);
            });
    }

    private function createJumpMenu(PromiseInterface $item, PromiseInterface $isMagazine, PromiseInterface $hasFigures, bool $isFiguresPage, PromiseInterface $history, PromiseInterface $sections, array $eraArticle) : PromiseInterface
    {
        return all(['item' => $item, 'isMagazine' => $isMagazine, 'hasFigures' => $hasFigures, 'history' => $history, 'sections' => $sections])
            ->then(function (array $sections) use ($isFiguresPage, $eraArticle) {

                $hasFigures = $sections['hasFigures'];
                $item = $sections['item'];

                if ($sections['isMagazine']  || 'feature' === $item->getType()) {
                    return false;
                }

                $sections = $sections['sections'];
                $sections = array_filter($sections, Callback::isInstanceOf(ArticleSection::class));

                if (count($sections) < 1) {
                    if (!$hasFigures) {
                        return null;
                    }

                    $sections = [];
                }

                return new JumpMenu(
                    array_map(function (ViewModel $viewModel, $i) {
                        if ($viewModel instanceof ArticleSection) {
                            return new Link($viewModel['title'], '#'.$viewModel['id']);
                        }

                        return null;
                    }, $sections, array_keys($sections))
                );
            });
    }

    private function createViewSelector(PromiseInterface $item, PromiseInterface $isMagazine, PromiseInterface $hasFigures, bool $isFiguresPage, PromiseInterface $history, PromiseInterface $sections, array $eraArticle) : PromiseInterface
    {
        return all(['item' => $item, 'isMagazine' => $isMagazine, 'hasFigures' => $hasFigures, 'history' => $history, 'sections' => $sections])
            ->then(function (array $sections) use ($isFiguresPage, $eraArticle) {

                $item = $sections['item'];
                $hasFigures = $sections['hasFigures'];
                $history = $sections['history'];
                $sections = $sections['sections'];

                // show view selector only on  Feature Articles
                if ('feature' !== $item->getType()) {
                    return null;
                }

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
                    new Link('Article', $this->generatePath($history, $item->getVersion(), null, 'content')),
                    array_values(array_filter(array_map(function (ViewModel $viewModel) {
                        if ($viewModel instanceof ArticleSection) {
                            return new Link($viewModel['title'], '#'.$viewModel['id']);
                        }

                        return null;
                    }, $sections))),
                    $hasFigures ? new Link('Figures and data', $this->generatePath($history, $item->getVersion(), 'figures', 'content')) : null,
                    $isFiguresPage,
                    false,
                    $item instanceof ArticleVoR
                        ? rtrim($this->getParameter('side_by_side_view_url'), '/').'/'.$item->getId()
                        : null,
                    $otherLinks
                );
            });
    }


    private function contentAsideArguments(array $arguments) : array
    {
        $arguments['contentAside'] = all([
            'item' => $arguments['item'],
            'isMagazine' => $arguments['isMagazine'],
            'metrics' => $arguments['contextualDataMetrics'],
            'history' => $arguments['history'],
            'relatedItem' => $arguments['relatedItem'] ?? promise_for(null),
        ])
            ->then(function (array $parts) {
                /** @var ArticleVersion $item */
                $item = $parts['item'];

                if ($parts['isMagazine'] || 'feature' === $item->getType()) {
                    return false;
                }

                /** @var ArticleHistory $history */
                $history = $parts['history'];

                $publicationHistory = [];

                $articleVersions = $history->getVersions()
                    ->filter(Callback::isInstanceOf(ArticleVersion::class))
                    ->sort(function (ArticleVersion $a, ArticleVersion $b) {
                        return $a->getVersion() <=> $b->getVersion();
                    });

                $prepareDefinition = function (int $index, string $term, string $descriptor, string $color = null, bool $isActive = false) {
                    return [
                        // index added to allow us to sort.
                        'index' => $index,
                        'term' => $term,
                        'descriptors' => [
                            $descriptor,
                        ],
                        'color' => $color,
                        'isActive' => $isActive
                    ];
                };

                $prepareDefinitionArticleVersion = function (ArticleVersion $articleVersion, bool $first) use ($prepareDefinition, $history, $item) {
                    $isLastVor = $articleVersion->getVersion() === $item->getVersion() && $articleVersion instanceof ArticleVoR && $articleVersion->isReviewedPreprint();
                    $versionLabel = $articleVersion instanceof ArticleVoR ? 'Version of Record' : 'Accepted Manuscript';
                    return $prepareDefinition(
                        $articleVersion->getVersionDate() ? $articleVersion->getVersionDate()->getTimeStamp() : 0,
                        sprintf(
                            '%s',
                                $articleVersion->getVersion() === $item->getVersion() ?
                                    $versionLabel
                                    : sprintf('<a href="%s">%s</a>', $this->generatePath($history, $articleVersion->getVersion()), $versionLabel)
                        ),
                        sprintf(
                            '%s %s',
                            $articleVersion->getVersionDate() ?
                                sprintf(
                                    '<time datetime="%s">%s</time>',
                                    $articleVersion->getVersionDate()->format('Y-m-d'),
                                    $articleVersion->getVersionDate()->format('F j, Y')
                                ) : '',
                            $isLastVor ?
                                sprintf(
                                    '<a href="%s">Read the peer reviews</a>',
                                    $this->generatePath($history, $item->getVersion(), 'peer-reviews', 'content')
                                ) : ''
                            ),
                        $isLastVor ? 'vor': '',
                        $articleVersion->getVersion() === $item->getVersion()
                    );
                };

                $publicationHistory = array_merge($publicationHistory, $articleVersions
                    ->filter(Callback::isInstanceOf(ArticleVoR::class))
                    ->map(function(ArticleVoR $itemVersion, int $number) use ($prepareDefinitionArticleVersion) {
                        return $prepareDefinitionArticleVersion($itemVersion, 0 === $number);
                    })->reverse()->toArray());

                $publicationHistory = array_merge($publicationHistory, $articleVersions
                    ->filter(Callback::isInstanceOf(ArticlePoA::class))
                    ->map(function(ArticlePoA $itemVersion, int $number) use ($prepareDefinitionArticleVersion) {
                        return $prepareDefinitionArticleVersion($itemVersion, 0 === $number);
                    })->reverse()->toArray());
                $publicationHistory = array_merge($publicationHistory, $history->getVersions()
                    ->filter(Callback::isInstanceOf(ArticlePreprint::class))
                    ->filter(function($preprint) {
                        return strpos($preprint->getDescription(), 'reviewed preprint') !== false;
                    })
                    ->map(function(ArticlePreprint $preprint) use ($prepareDefinition) {
                        return $prepareDefinition(
                            $preprint->getPublishedDate()->getTimeStamp(),
                            sprintf(
                                '<a href="%s">Reviewed Preprint</a>',
                                $preprint->getUri()
                            ),
                            sprintf(
                                '<time datetime="%s">%s</time>',
                                $preprint->getPublishedDate()->format('Y-m-d'),
                                $preprint->getPublishedDate()->format('F j, Y')
                            )
                        );
                    })->reverse()->toArray());

                // Sort by index value.
                usort($publicationHistory, function($first, $second) {
                    return $first['index'] < $second['index'];
                });

                $timeline = [];

                if (!in_array($item->getType(), ['correction', 'expression-concern', 'retraction'])) {
                    $rpCount = $history->getVersions()
                        ->filter(Callback::isInstanceOf(ArticlePreprint::class))
                        ->filter(function (ArticlePreprint $preprint) {
                            return strpos($preprint->getDescription(), 'reviewed preprint') !== false;
                        })
                        ->count();

                    // If reviewed preprint count is greater than 1 we want to alter the $item['term'].
                    $rpCount = $rpCount > 1 ? $rpCount : 0;

                    $timeline = array_map(function ($item) use (&$rpCount) {
                        // Remove index from item.
                        unset($item['index']);

                        if (strpos($item['term'], 'Reviewed Preprint') !== false && $rpCount > 0) {
                            $version = sprintf('<span class="version">v%d</span>', $rpCount);
                            $item['descriptors'][0] = $version . ' ' . $item['descriptors'][0];
                            $rpCount--;
                        }

                        return $item;
                    }, $publicationHistory);
                }

                return $this->convertTo($parts['item'],
                    ContentAside::class, [
                        'metrics' => $parts['metrics'],
                        'timeline' => $timeline,
                        'relatedItem' => $parts['relatedItem']
                    ]
                );
            });

        return $arguments;
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

    private function checkHasFigures($item, $figures) {
        return all(['item' => $item, 'hasFigures' => $figures])
            ->then(function (array $parts) {
                $item = $parts['item'];
                $hasFigures = $parts['hasFigures'];

                return $item->getAdditionalFiles()->notEmpty() || $hasFigures;
            });
    }

    private function hasPeerReview($item, $isMagazine, $history) {
        return all(['item' => $item, 'isMagazine' => $isMagazine, 'history' => $history])
            ->then(function (array $parts) {
                $item = $parts['item'];
                $isMagazine = $parts['isMagazine'];
                $history = $parts['history'];
                $combinedHistory = $history->getVersions()
                    ->filter(function ($version) {
                        return $version instanceof ArticleVoR || $version instanceof ArticlePoA;
                    })
                    ->toArray();

                return ($item instanceof ArticleVoR &&
                        ($item->getPublicReviews()->notEmpty() ||
                        $item->getDecisionLetter() ||
                        $item->getAuthorResponse() ||
                        (!$item->isReviewedPreprint() &&
                            !$isMagazine &&
                            !in_array($item->getType(), ['feature', 'correction', 'expression-concern', 'retraction']) &&
                            $combinedHistory
                        )) ||
                        ($item instanceof ArticlePoA && $combinedHistory));
            });
    }

    private function generatePublicationHistoryForOldVorAndPoa($history) {
        $received = $history->getReceived();
        $accepted = $history->getAccepted();
        $sentForReview = $history->getSentForReview();

        $publicationHistory = [];

        /** @var ArticlePreprint[] $preprints */
        $preprints = $history->getVersions()
            ->filter(Callback::isInstanceOf(ArticlePreprint::class))
            ->toArray();

        if ($preprints) {
            foreach ($preprints as $preprint) {
                // Attempt to output $received if date is before the preprint date.
                if ($received && 1 === $preprint->getPublishedDate()->diff(new DateTime($received->toString()))->invert) {
                    $publicationHistory[] = sprintf('<strong>Received</strong> <time datetime="%s">%s</time>', $received->format('Y-m-d'), $received->format());

                    // Set $received to null as it has now been included in the publication history.
                    $received = null;
                }
                // Attempt to output $accepted if date is before the preprint date.
                if ($accepted && 1 === $preprint->getPublishedDate()->diff(new DateTime($accepted->toString()))->invert) {
                    $publicationHistory[] = sprintf('<strong>Accepted</strong> <time datetime="%s">%s</time>', $accepted->format('Y-m-d'), $accepted->format());

                    // Set $accepted to null as it has now been included in the publication history.
                    $accepted = null;
                }

                // Attempt to output $sentForReview if date is before the preprint date.
                if ($sentForReview && 1 === $preprint->getPublishedDate()->diff(new DateTime($sentForReview->toString()))->invert) {
                    $publicationHistory[] = sprintf('<strong>Sent for peer review</strong> <time datetime="%s">%s</time>', $sentForReview->format('Y-m-d'), $sentForReview->format());

                    // Set $sentForReview to null as it has now been included in the publication history.
                    $sentForReview = null;
                }

                $publicationHistory[] = sprintf(
                    '<strong>Preprint posted</strong> <time datetime="%s">%s</time>',
                    $preprint->getPublishedDate()->format('Y-m-d'),
                    $preprint->getPublishedDate()->format('F j, Y')
                );
            }
        }
        // Output $received if it has not yet been output.
        if ($received) {
            $publicationHistory[] = sprintf('<strong>Received</strong> <time datetime="%s">%s</time>', $received->format('Y-m-d'), $received->format());
        }

        // Output $accepted if it has not yet been output.
        if ($accepted) {
            $publicationHistory[] = sprintf('<strong>Accepted</strong> <time datetime="%s">%s</time>', $accepted->format('Y-m-d'), $accepted->format());
        }

        // Output $sentForReview if it has not yet been output.
        if ($sentForReview) {
            $publicationHistory[] = sprintf('<strong>Sent for peer review</strong> <time datetime="%s">%s</time>', $sentForReview->format('Y-m-d'), $sentForReview->format());
        }

        $publicationHistory = array_merge($publicationHistory, $history->getVersions()
            ->filter(Callback::isInstanceOf(ArticlePoA::class))
            ->map(function (ArticlePoA $itemVersion, int $number) use ($preprints, $history) {
                return sprintf('<strong>Accepted Manuscript %s</strong> <time datetime="%s">%s</time>', 0 === $number ? 'published' : 'updated', $itemVersion->getVersionDate() ? $itemVersion->getVersionDate()->format('Y-m-d') : '', $itemVersion->getVersionDate() ? $itemVersion->getVersionDate()->format('F j, Y') : '');
            })->toArray());

        $publicationHistory = array_merge($publicationHistory, $history->getVersions()
            ->filter(Callback::isInstanceOf(ArticleVoR::class))
            ->map(function (ArticleVoR $itemVersion, int $number) use ($preprints, $history) {
                return sprintf('<strong>Version of Record %s</strong> <time datetime="%s">%s</time>', 0 === $number ? 'published' : 'updated', $itemVersion->getVersionDate() ? $itemVersion->getVersionDate()->format('Y-m-d') : '', $itemVersion->getVersionDate() ? $itemVersion->getVersionDate()->format('F j, Y') : '');
            })->toArray());

        return array_reverse($publicationHistory);
    }

    private function countReviewedPreprintsInPublicationHistory(ArticleHistory $history): int
    {
        return $history->getVersions()
                ->filter(Callback::isInstanceOf(ArticlePreprint::class))
                ->filter(function (ArticlePreprint $preprint) {
                    return strpos($preprint->getDescription(), 'reviewed preprint') !== false;
                })
                ->count();
    }

    private function generatePublicationHistoryForNewVor($history) {
        $received = $history->getReceived();
        $accepted = $history->getAccepted();
        $sentForReview = $history->getSentForReview();

        $publicationHistory = [];

        /** @var ArticlePreprint[] $preprints */
        $preprints = $history->getVersions()
            ->filter(Callback::isInstanceOf(ArticlePreprint::class))
            ->toArray();

        if ($preprints) {
            $rpCount = $this->countReviewedPreprintsInPublicationHistory($history);

            $counter = 0;

            foreach ($preprints as $preprint) {
                // Attempt to output $received if date is before the preprint date.
                if ($received && 1 === $preprint->getPublishedDate()->diff(new DateTime($received->toString()))->invert) {
                    $publicationHistory[] = sprintf('Received: <time datetime="%s">%s</time>', $received->format('Y-m-d'), $received->format());

                    // Set $received to null as it has now been included in the publication history.
                    $received = null;
                }
                // Attempt to output $accepted if date is before the preprint date.
                if ($accepted && 1 === $preprint->getPublishedDate()->diff(new DateTime($accepted->toString()))->invert) {
                    $publicationHistory[] = sprintf('Accepted: <time datetime="%s">%s</time>', $accepted->format('Y-m-d'), $accepted->format());

                    // Set $accepted to null as it has now been included in the publication history.
                    $accepted = null;
                }

                // Attempt to output $sentForReview if date is before the preprint date.
                if ($sentForReview && 1 === $preprint->getPublishedDate()->diff(new DateTime($sentForReview->toString()))->invert) {
                    $publicationHistory[] = sprintf('Sent for peer review: <time datetime="%s">%s</time>', $sentForReview->format('Y-m-d'), $sentForReview->format());

                    // Set $sentForReview to null as it has now been included in the publication history.
                    $sentForReview = null;
                }

                if (strpos($preprint->getDescription(), 'reviewed preprint') !== false && $counter > 0 && $counter <= $rpCount) {
                    $publicationHistory[] = sprintf(
                        '<a href="%s">Reviewed Preprint version %s</a>: <time datetime="%s">%s</time>',
                        $preprint->getUri(),
                        $counter,
                        $preprint->getPublishedDate()->format('Y-m-d'),
                        $preprint->getPublishedDate()->format('F j, Y')
                    );
                } else {
                    $publicationHistory[] = sprintf(
                        '<a href="%s">Preprint posted</a>: <time datetime="%s">%s</time>',
                        $preprint->getUri(),
                        $preprint->getPublishedDate()->format('Y-m-d'),
                        $preprint->getPublishedDate()->format('F j, Y')
                    );
                }

                $counter++;
            }
        }

        // Output $received if it has not yet been output.
        if ($received) {
            $publicationHistory[] = sprintf('Received: <time datetime="%s">%s</time>', $received->format('Y-m-d'), $received->format());
        }

        // Output $accepted if it has not yet been output.
        if ($accepted) {
            $publicationHistory[] = sprintf('Accepted: <time datetime="%s">%s</time>', $accepted->format('Y-m-d'), $accepted->format());
        }

        // Output $sentForReview if it has not yet been output.
        if ($sentForReview) {
            $publicationHistory[] = sprintf('Sent for peer review: <time datetime="%s">%s</time>', $sentForReview->format('Y-m-d'), $sentForReview->format());
        }

        $publicationHistory = array_merge($publicationHistory, $history->getVersions()
            ->filter(Callback::isInstanceOf(ArticlePoA::class))
            ->map(function (ArticlePoA $itemVersion, int $number) use ($preprints, $history) {
                return sprintf('<a href="%s">Accepted Manuscript %s</a>: <time datetime="%s">%s</time>', $this->generatePath($history, $itemVersion->getVersion()), 0 === $number ? 'published' : 'updated', $itemVersion->getVersionDate() ? $itemVersion->getVersionDate()->format('Y-m-d') : '', $itemVersion->getVersionDate() ? $itemVersion->getVersionDate()->format('F j, Y') : '');
            })->toArray());

        $publicationHistory = array_merge($publicationHistory, $history->getVersions()
            ->filter(Callback::isInstanceOf(ArticleVoR::class))
            ->map(function (ArticleVoR $itemVersion, int $number) use ($preprints, $history) {
                return sprintf('<a href="%s">Version of Record %s</a>: <time datetime="%s">%s</time>', $this->generatePath($history, $itemVersion->getVersion()), 0 === $number ? 'published' : 'updated', $itemVersion->getVersionDate() ? $itemVersion->getVersionDate()->format('Y-m-d') : '', $itemVersion->getVersionDate() ? $itemVersion->getVersionDate()->format('F j, Y') : '');
            })->toArray());

        return $publicationHistory;
    }
}
