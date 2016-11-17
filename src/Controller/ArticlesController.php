<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Reference;
use eLife\Journal\FilterBlocksForClass;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeaderArticle;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\Doi;
use eLife\Patterns\ViewModel\InfoBar;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ViewSelector;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function GuzzleHttp\Promise\all;

final class ArticlesController extends Controller
{
    private function closedCollapsibleArticleSection($id, $title, $object) : ArticleSection
    {
        return ArticleSection::collapsible(
             $id, $title, 2,
                $this->toLevel(2, $object->getContent()),
                true, false,
                new Doi($object->getDoi()));
    }

    private function toLevel($level, $content) : string
    {
        return $content
            ->map(function (Block $block) use ($level) {
                return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => $level]);
            })
            ->reduce(function (string $carry, ViewModel $viewModel) {
                return $carry.$this->get('elife.patterns.pattern_renderer')->render($viewModel);
            }, '');
    }

    private function toComplete()
    {
        return function (array $blocks) {
            return array_map(function ($block) {
                return $this->get('elife.journal.view_model.converter')->convert($block, null, ['complete' => true]);
            }, $blocks);
        };
    }

    private function render()
    {
        return function (array $figures) {
            return array_map(function (ViewModel $viewModel) {
                return $this->get('elife.patterns.pattern_renderer')->render($ViewModel);
            }, $figures);
        };
    }

    private function linksToSections($body) : array
    {
    return                array_filter(array_map(function (ViewModel $viewModel) {
                        if ($viewModel instanceof ArticleSection) {
                            return new Link($viewModel['title'], '#'.$viewModel['id']);
                        }

                        return null;
                    }, count($body) > 1 ? $body : []));
    }

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
                        $this->toLevel(2, $article->getAbstract()->getContent()),
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
                        $this->toLevel(2, $article->getDigest()->getContent()),
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
                                array_reduce(array_map(function (Block $block) {
                                    return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                                }, $section->getContent()), function (string $carry, ViewModel $viewModel) {
                                    return $carry.$this->get('elife.patterns.pattern_renderer')->render($viewModel);
                                }, ''),
                                $isInitiallyClosed,
                                $first
                            );

                            $first = false;
                            $isInitiallyClosed = true;

                            return $section;
                        })->toArray());

                        $parts = array_merge($parts, $article->getAppendices()->map(function (Appendix $appendix) {
                            return $this->closedCollapsibleArticleSection(
                                $appendix->getId(),
                                $appendix->getTitle(),
                                $appendix
                            );
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
                                    })->toArray()
                                )),
                                true
                            );
                        }

                        if ($article->getDecisionLetter()) {
                            $parts[] = $this->closedCollapsibleArticleSection(
                                'decision-letter',
                                'Decision letter',
                                $article->getDecisionLetter()
                            );
                        }

                        if ($article->getAuthorResponse()) {
                            $parts[] = $this->closedCollapsibleArticleSection(
                                'author-response',
                                'Author response',
                                $article->getAuthorResponse()
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
                                ...$personAuthors->map(function (PersonAuthor $author) use ($article) {
                                    return $this->get('elife.journal.view_model.converter')->convert($author, null, ['article' => $article]);
                                })->toArray()
                            );
                        }

                        if ($article->getAcknowledgements()->notEmpty()) {
                            $infoSections[] = ArticleSection::basic(
                                'Acknowledgements',
                                3,
                                $this->toLevel(3, $article->getAcknowledgements())
                            );
                        }

                        if ($article->getEthics()->notEmpty()) {
                            $infoSections[] = ArticleSection::basic(
                                'Ethics',
                                3,
                                $this->toLevel(3, $article->getEthics())
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
                            implode('', array_map(function (ViewModel $viewModel) {
                                return $this->get('elife.patterns.pattern_renderer')->render($viewModel);
                            }, $infoSections)),
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
                    $this->linksToSections($body),
                    $hasFigures ? $this->get('router')->generate('article-figures', ['id' => $article->getId(), 'volume' => $article->getVolume()]) : null
                );
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
            ->then(FilterBlocksForClass::for(Block\Image::class))
            ->then($this->toComplete())
            ->then($this->render());

        $videos = $allFigures
            ->then(FilterBlocksForClass::for(Block\Video::class))
            ->then($this->toComplete())
            ->then($this->render());

        $tables = $allFigures
            ->then(FilterBlocksForClass::for(Block\Table::class))
            ->then($this->toComplete())
            ->then($this->render());

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

                return $this->get('elife.patterns.pattern_renderer')->render(new ViewModel\AdditionalAssets(null, $files));
            });

        $arguments['body'] = all([
            'figures' => $figures,
            'videos' => $videos,
            'tables' => $tables,
            'additionalFiles' => $additionalFiles,
        ])
            ->then(function (array $all) {
                $parts = [];

                $first = true;

                if (!empty($all['figures'])) {
                    $parts[] = ArticleSection::collapsible('figures', 'Figures', 2, implode($all['figures']), false, $first);
                    $first = false;
                }

                if (!empty($all['videos'])) {
                    $parts[] = ArticleSection::collapsible('videos', 'Videos', 2, implode($all['videos']), false, $first);
                    $first = false;
                }

                if (!empty($all['tables'])) {
                    $parts[] = ArticleSection::collapsible('tables', 'Tables', 2, implode($all['tables']), false, $first);
                }

                if (!empty($all['additionalFiles'])) {
                    $parts[] = ArticleSection::collapsible('files', 'Additional files', 2, $all['additionalFiles']);
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
                    $this->linksToSections($body),
                    /*
                    array_filter(array_map(function (ViewModel $viewModel) {
                        if ($viewModel instanceof ArticleSection) {
                            return new Link($viewModel['title'], '#'.$viewModel['id']);
                        }

                        return null;
                    }, count($body) > 1 ? $body : [])),
                     */
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
