<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\FundingAward;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reviewer;
use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeaderArticle;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\Doi;
use eLife\Patterns\ViewModel\InfoBar;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\ViewSelector;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function GuzzleHttp\Promise\all;

final class ArticlesController extends Controller
{
    public function latestVersionAction(int $volume, string $id) : Response
    {
        $arguments = $this->articlePageArguments($volume, $id);

        $arguments['body'] = $arguments['article']
            ->then(function (ArticleVersion $article) {
                $parts = [];

                $first = true;

                if ($article->getAbstract()) {
                    $parts[] = ArticleSection::collapsible(
                        'abstract',
                        'Abstract',
                        2,
                        $this->get('elife.patterns.pattern_renderer')->render(
                            ...$article->getAbstract()->getContent()
                            ->map(function (Block $block) {
                                return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                            })
                        ),
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
                        $this->get('elife.patterns.pattern_renderer')->render(
                            ...$article->getDigest()->getContent()
                            ->map(function (Block $block) {
                                return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                            })
                        ),
                        false,
                        $first,
                        new Doi($article->getDigest()->getDoi())
                    );

                    $first = false;
                }

                if ($article instanceof ArticleVoR) {
                    if (empty($parts) && 1 === count($article->getContent())) {
                        $parts = array_map(function (Block $block) {
                            return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                        }, $article->getContent()[0]->getContent());
                    } else {
                        $isInitiallyClosed = false;

                        $parts = array_merge($parts, $article->getContent()->map(function (Block\Section $section) use (&$first, &$isInitiallyClosed) {
                            $section = ArticleSection::collapsible(
                                $section->getId(),
                                $section->getTitle(),
                                2,
                                $this->get('elife.patterns.pattern_renderer')->render(
                                    ...array_map(function (Block $block) {
                                        return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                                    }, $section->getContent())
                                ),
                                $isInitiallyClosed,
                                $first
                            );

                            $first = false;
                            $isInitiallyClosed = true;

                            return $section;
                        })->toArray());

                        $parts = array_merge($parts, $article->getAppendices()->map(function (Appendix $appendix) {
                            return ArticleSection::collapsible($appendix->getId(), $appendix->getTitle(), 2,
                                $this->get('elife.patterns.pattern_renderer')->render(
                                    ...$appendix->getContent()
                                    ->map(function (Block $block) {
                                        return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                                    })
                                ),
                                true, false, $appendix->getDoi() ? new Doi($appendix->getDoi()) : null);
                        })->toArray());

                        if ($article->getReferences()->notEmpty()) {
                            $parts[] = ArticleSection::collapsible(
                                'references',
                                'References',
                                2,
                                $this->get('elife.patterns.pattern_renderer')->render(new ViewModel\ReferenceList(
                                    ...$article->getReferences()
                                    ->map(function (Reference $reference, int $index) {
                                        return new ViewModel\ReferenceListItem(
                                            $reference->getId(),
                                            $index + 1,
                                            $this->get('elife.journal.view_model.converter')->convert($reference)
                                        );
                                    })
                                )),
                                true
                            );
                        }

                        if ($article->getDecisionLetter()) {
                            $header = $this->get('elife.journal.view_model.converter')->convert($article, ViewModel\DecisionLetterHeader::class);

                            $parts[] = ArticleSection::collapsible(
                                'decision-letter',
                                'Decision letter',
                                2,
                                $this->get('elife.patterns.pattern_renderer')->render($header).
                                $this->get('elife.patterns.pattern_renderer')->render(
                                    ...$article->getDecisionLetter()->getContent()
                                    ->map(function (Block $block) {
                                        return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                                    })
                                ),
                                true,
                                false,
                                new Doi($article->getDecisionLetter()->getDoi())
                            );
                        }

                        if ($article->getAuthorResponse()) {
                            $parts[] = ArticleSection::collapsible(
                                'author-response',
                                'Author response',
                                2,
                                $this->get('elife.patterns.pattern_renderer')->render(
                                    ...$article->getAuthorResponse()->getContent()
                                    ->map(function (Block $block) {
                                        return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                                    })
                                ),
                                true,
                                false,
                                new Doi($article->getAuthorResponse()->getDoi())
                            );
                        }

                        $infoSections = [];

                        $realAuthors = $article->getAuthors()->filter(function (AuthorEntry $author) {
                            return $author instanceof Author;
                        });

                        $personAuthors = $realAuthors->filter(function (Author $author) {
                            return $author instanceof PersonAuthor;
                        });

                        if ($personAuthors->notEmpty()) {
                            $infoSections[] = new ViewModel\AuthorsDetails(
                                ...$personAuthors->map(function (PersonAuthor $author) use ($realAuthors) {
                                    return $this->get('elife.journal.view_model.converter')->convert($author, null, ['authors' => $realAuthors]);
                                })
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
                                            ->map(function (Author $author) {
                                                return $author->toString();
                                            })
                                            ->toArray(),
                                        'bullet'
                                    );

                                    return ArticleSection::basic(
                                        $title,
                                        4,
                                        $this->get('elife.patterns.pattern_renderer')->render($body)
                                    );
                                })->toArray();

                            $funding[] = new Paragraph($article->getFunding()->getStatement());

                            $infoSections[] = ArticleSection::basic(
                                'Funding',
                                3,
                                $this->get('elife.patterns.pattern_renderer')->render(...$funding)
                            );
                        }

                        if ($article->getAcknowledgements()->notEmpty()) {
                            $infoSections[] = ArticleSection::basic(
                                'Acknowledgements',
                                3,
                                $this->get('elife.patterns.pattern_renderer')->render(
                                    ...$article->getAcknowledgements()
                                    ->map(function (Block $block) {
                                        return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 3]);
                                    })
                                )
                            );
                        }

                        if ($article->getEthics()->notEmpty()) {
                            $infoSections[] = ArticleSection::basic(
                                'Ethics',
                                3,
                                $this->get('elife.patterns.pattern_renderer')->render(
                                    ...$article->getEthics()
                                    ->map(function (Block $block) {
                                        return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 3]);
                                    })
                                )
                            );
                        }

                        if ($article->getReviewers()->notEmpty()) {
                            $infoSections[] = ArticleSection::basic(
                                'Reviewers',
                                3,
                                $this->get('elife.patterns.pattern_renderer')->render(
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

                        $copyright = '<p>'.$article->getCopyright()->getStatement().'</p>';

                        if ($article->getCopyright()->getHolder()) {
                            $copyright = sprintf('<p>© %s, %s.</p>', 2011 + $article->getVolume(), $article->getCopyright()->getHolder()).$copyright;
                        }

                        $infoSections[] = ArticleSection::basic('Copyright', 3, $copyright);

                        $parts[] = ArticleSection::collapsible(
                            'info',
                            'Article and author information',
                            2,
                            $this->get('elife.patterns.pattern_renderer')->render(...$infoSections),
                            true
                        );
                    }
                }

                return $parts;
            });

        $arguments['hasFigures'] = $arguments['article']
            ->then(function (ArticleVersion $article) {
                return count($this->findFigures($article)) > 0;
            });

        $arguments['viewSelector'] = all([
            'article' => $arguments['article'],
            'body' => $arguments['body'],
            'hasFigures' => $arguments['hasFigures'],
        ])
            ->then(function (array $sections) {
                /** @var ArticleVersion $article */
                $article = $sections['article'];
                $body = $sections['body'];
                $hasFigures = $sections['hasFigures'];

                if ((count($body) < 2 || false === $body[0] instanceof ArticleSection) && !$hasFigures) {
                    return null;
                }

                return new ViewSelector(
                    $this->get('router')->generate('article', ['id' => $article->getId(), 'volume' => $article->getVolume()]),
                    array_filter(array_map(function (ViewModel $viewModel) {
                        if ($viewModel instanceof ArticleSection) {
                            return new Link($viewModel['title'], '#'.$viewModel['id']);
                        }

                        return null;
                    }, $body)),
                    $hasFigures ? $this->get('router')->generate('article-figures', ['id' => $article->getId(), 'volume' => $article->getVolume()]) : null
                );
            });

        $arguments['body'] = all(['article' => $arguments['article'], 'body' => $arguments['body']])
            ->then(function (array $parts) {
                $article = $parts['article'];
                $body = $parts['body'];

                $downloadLinks = $this->get('elife.journal.view_model.converter')->convert($article, ViewModel\ArticleDownloadLinksList::class);

                $body[] = ArticleSection::basic(
                    'Download links',
                    2,
                    $this->get('elife.patterns.pattern_renderer')->render($downloadLinks)
                );

                $body[] = $this->get('elife.journal.view_model.converter')->convert($article, ViewModel\ArticleMeta::class);

                return $body;
            });

        return new Response($this->get('templating')->render('::article.html.twig', $arguments));
    }

    public function latestVersionFiguresAction(int $volume, string $id) : Response
    {
        $arguments = $this->articlePageArguments($volume, $id);

        $arguments['article'] = $arguments['article']
            ->then(function (ArticleVersion $article) {
                if (false === $article instanceof ArticleVoR) {
                    throw new NotFoundHttpException('Article is not a VoR');
                }

                return $article;
            });

        $allFigures = $arguments['article']
            ->then(function (ArticleVoR $article) {
                $figures = $this->findFigures($article);

                if (empty($figures)) {
                    throw new NotFoundHttpException('Article version does not contain any figures');
                }

                return $figures;
            });

        $figures = $allFigures
            ->then(function (array $allFigures) {
                return array_filter($allFigures, function (Block $block) {
                    return $block instanceof Block\Image;
                });
            })
            ->then(function (array $figures) {
                return array_map(function (Block\Image $image) {
                    return $this->get('elife.journal.view_model.converter')->convert($image, null, ['complete' => true]);
                }, $figures);
            });

        $videos = $allFigures
            ->then(function (array $allFigures) {
                return array_filter($allFigures, function (Block $block) {
                    return $block instanceof Block\Video;
                });
            })
            ->then(function (array $videos) {
                return array_map(function (Block\Video $video) {
                    return $this->get('elife.journal.view_model.converter')->convert($video, null, ['complete' => true]);
                }, $videos);
            });

        $tables = $allFigures
            ->then(function (array $allFigures) {
                return array_filter($allFigures, function (Block $block) {
                    return $block instanceof Block\Table;
                });
            })
            ->then(function (array $tables) {
                return array_map(function (Block\Table $table) {
                    return $this->get('elife.journal.view_model.converter')->convert($table, null, ['complete' => true]);
                }, $tables);
            });

        $generateDataSets = $arguments['article']
            ->then(function (ArticleVoR $article) {
                return $article->getGeneratedDataSets()
                    ->map(function (DataSet $dataSet, int $id) {
                        $reference = $this->get('elife.journal.view_model.converter')->convert($dataSet);

                        return new ViewModel\ReferenceListItem($dataSet->getId(), $id + 1, $reference);
                    });
            })
            ->then(function (Sequence $generatedDataSets) {
                if ($generatedDataSets->isEmpty()) {
                    return [];
                }

                return [
                    new ViewModel\MessageBar('The following data sets were generated'),
                    new ViewModel\ReferenceList(...$generatedDataSets),
                ];
            });

        $usedDataSets = $arguments['article']
            ->then(function (ArticleVoR $article) {
                return $article->getUsedDataSets()
                    ->map(function (DataSet $dataSet, int $id) {
                        $reference = $this->get('elife.journal.view_model.converter')->convert($dataSet);

                        return new ViewModel\ReferenceListItem($dataSet->getId(), $id + 1, $reference);
                    });
            })
            ->then(function (Sequence $usedDataSets) {
                if ($usedDataSets->isEmpty()) {
                    return [];
                }

                return [
                    new ViewModel\MessageBar('The following previously published data sets were used'),
                    new ViewModel\ReferenceList(...$usedDataSets),
                ];
            });

        $dataSets = all(['generated' => $generateDataSets, 'used' => $usedDataSets])
            ->then(function (array $dataSets) {
                return array_filter(array_merge($dataSets['generated'], $dataSets['used']));
            });

        $additionalFiles = $arguments['article']
            ->then(function (ArticleVoR $article) {
                return $article->getAdditionalFiles()
                    ->map(function (File $file) {
                        return $this->get('elife.journal.view_model.converter')->convert($file);
                    })
                    ->toArray();
            })
            ->then(function (array $files) {
                if (empty($files)) {
                    return null;
                }

                return new ViewModel\AdditionalAssets(null, $files);
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

                if (!empty($all['figures'])) {
                    $parts[] = ArticleSection::collapsible('figures', 'Figures', 2, $this->get('elife.patterns.pattern_renderer')->render(...$all['figures']), false, $first);
                    $first = false;
                }

                if (!empty($all['videos'])) {
                    $parts[] = ArticleSection::collapsible('videos', 'Videos', 2, $this->get('elife.patterns.pattern_renderer')->render(...$all['videos']), false, $first);
                    $first = false;
                }

                if (!empty($all['tables'])) {
                    $parts[] = ArticleSection::collapsible('tables', 'Tables', 2, $this->get('elife.patterns.pattern_renderer')->render(...$all['tables']), false, $first);
                }

                if (!empty($all['dataSets'])) {
                    $parts[] = ArticleSection::collapsible('data-sets', 'Data sets', 2, $this->get('elife.patterns.pattern_renderer')->render(...$all['dataSets']));
                }

                if (!empty($all['additionalFiles'])) {
                    $parts[] = ArticleSection::collapsible('files', 'Additional files', 2, $this->get('elife.patterns.pattern_renderer')->render($all['additionalFiles']));
                }

                return $parts;
            });

        $arguments['viewSelector'] = all(['article' => $arguments['article'], 'body' => $arguments['body']])
            ->then(function (array $sections) {
                /** @var ArticleVoR $article */
                $article = $sections['article'];
                $body = $sections['body'];

                return new ViewSelector(
                    $this->get('router')->generate('article', ['id' => $article->getId(), 'volume' => $article->getVolume()]),
                    array_filter(array_map(function (ViewModel $viewModel) {
                        if ($viewModel instanceof ArticleSection) {
                            return new Link($viewModel['title'], '#'.$viewModel['id']);
                        }

                        return null;
                    }, count($body) > 1 ? $body : [])),
                    $this->get('router')
                        ->generate('article-figures', ['id' => $article->getId(), 'volume' => $article->getVolume()])
                );
            });

        return new Response($this->get('templating')->render('::article-figures.html.twig', $arguments));
    }

    private function articlePageArguments(int $volume, string $id) : array
    {
        $arguments = $this->defaultPageArguments();

        $arguments['article'] = $this->get('elife.api_sdk.articles')
            ->get($id)
            ->then(function (ArticleVersion $article) use ($volume) {
                if ($volume !== $article->getVolume()) {
                    throw new NotFoundHttpException('Incorrect volume');
                }

                return $article;
            });

        $arguments['articleTitle'] = $arguments['article']
            ->then(function (ArticleVersion $article) {
                return $article->getFullTitle();
            });

        $arguments['contentHeader'] = $arguments['article']
            ->then(function (ArticleVersion $article) {
                return $this->get('elife.journal.view_model.converter')->convert($article, ContentHeaderArticle::class);
            });

        $arguments['infoBars'] = $arguments['article']
            ->then(function (ArticleVersion $article) {
                if ($article instanceof ArticleVoR) {
                    return [];
                }

                return [new InfoBar('Accepted manuscript, PDF only. Full online edition to follow.')];
            });

        $arguments['contextualData'] = $arguments['article']
            ->then(function (ArticleVersion $article) {
                return ContextualData::withCitation(
                    sprintf('eLife %s;%s:%s', 2011 + $article->getVolume(), $article->getVolume(), $article->getElocationId()),
                    new Doi($article->getDoi())
                );
            });

        return $arguments;
    }

    private function findFigures(ArticleVersion $article) : array
    {
        if (false === $article instanceof ArticleVoR) {
            return [];
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

        return $figures;
    }
}
