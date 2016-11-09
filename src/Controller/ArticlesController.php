<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block;
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
                        $article->getAbstract()->getContent()
                            ->map(function (Block $block) {
                                return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                            })
                            ->reduce(function (string $carry, ViewModel $viewModel) {
                                return $carry.$this->get('elife.patterns.pattern_renderer')->render($viewModel);
                            }, ''),
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
                        $article->getDigest()->getContent()
                            ->map(function (Block $block) {
                                return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                            })
                            ->reduce(function (string $carry, ViewModel $viewModel) {
                                return $carry.$this->get('elife.patterns.pattern_renderer')->render($viewModel);
                            }, ''),
                        false,
                        $first,
                        new Doi($article->getAbstract()->getDoi())
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
                            return ArticleSection::collapsible($appendix->getId(), $appendix->getTitle(), 2,
                                $appendix->getContent()
                                    ->map(function (Block $block) {
                                        return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                                    })
                                    ->reduce(function (string $carry, ViewModel $viewModel) {
                                        return $carry.$this->get('elife.patterns.pattern_renderer')->render($viewModel);
                                    }, ''),
                                true, false, new Doi($appendix->getDoi()));
                        })->toArray());

                        if ($article->getDecisionLetter()) {
                            $parts[] = ArticleSection::collapsible(
                                'decision-letter',
                                'Decision letter',
                                2,
                                $article->getDecisionLetter()->getContent()
                                    ->map(function (Block $block) {
                                        return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                                    })
                                    ->reduce(function (string $carry, ViewModel $viewModel) {
                                        return $carry.$this->get('elife.patterns.pattern_renderer')->render($viewModel);
                                    }, ''),
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
                                $article->getAuthorResponse()->getContent()
                                    ->map(function (Block $block) {
                                        return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 2]);
                                    })
                                    ->reduce(function (string $carry, ViewModel $viewModel) {
                                        return $carry.$this->get('elife.patterns.pattern_renderer')->render($viewModel);
                                    }, ''),
                                true,
                                false,
                                new Doi($article->getAuthorResponse()->getDoi())
                            );
                        }

                        $infoSections = [];

                        if ($article->getAcknowledgements()->notEmpty()) {
                            $infoSections[] = ArticleSection::basic(
                                'Acknowledgements',
                                3,
                                $article->getAcknowledgements()
                                    ->map(function (Block $block) {
                                        return $this->get('elife.journal.view_model.converter')->convert($block, null, ['level' => 3]);
                                    })
                                    ->reduce(function (string $carry, ViewModel $viewModel) {
                                        return $carry.$this->get('elife.patterns.pattern_renderer')->render($viewModel);
                                    }, '')
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
                    array_map(function (ArticleSection $section) {
                        return new Link($section['title'], '#'.$section['id']);
                    }, $body),
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

        $arguments['body'] = $arguments['article']
            ->then(function (ArticleVoR $article) {
                $figures = $this->findFigures($article);

                if (empty($figures)) {
                    throw new NotFoundHttpException('Article version does not contain any figures');
                }

                return $figures;
            })
            ->then(function (array $figures) {
                return array_map(function (Block $block) {
                    return $this->get('elife.journal.view_model.converter')->convert($block);
                }, $figures);
            });

        $arguments['viewSelector'] = $arguments['article']
            ->then(function (ArticleVoR $article) {
                return new ViewSelector(
                    $this->get('router')->generate('article', ['id' => $article->getId(), 'volume' => $article->getVolume()]),
                    [],
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
