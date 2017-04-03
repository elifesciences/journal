<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PressPackage;
use test\eLife\Journal\ViewModel\Converter\ModelConverterTestCase;
use Traversable;

abstract class BlockConverterTestCase extends ModelConverterTestCase
{
    protected $models = ['article-vor', 'blog-article', 'event', 'interview', 'labs-experiment', 'person', 'press-package'];

    final protected function modelHook(Model $model) : Traversable
    {
        yield from array_filter(iterator_to_array($this->findBlocks($model)), [$this, 'includeBlock']);
    }

    protected function includeBlock(Block $block) : bool
    {
        return true;
    }

    private function findBlocks(Model $model) : Traversable
    {
        if ($model instanceof HasContent) {
            yield from $this->hasContentHook($model);
        }

        if ($model instanceof ArticleVersion) {
            if ($model->getAbstract()) {
                yield from $this->hasContentHook($model->getAbstract());
            }
        }

        if ($model instanceof ArticleVoR) {
            if ($model->getDigest()) {
                yield from $this->hasContentHook($model->getDigest());
            }
            foreach ($model->getAppendices() as $appendix) {
                yield from $this->hasContentHook($appendix);
            }
            yield from $this->sequenceHook($model->getAcknowledgements());
            yield from $this->sequenceHook($model->getEthics());
            if ($model->getDecisionLetter()) {
                yield from $this->hasContentHook($model->getDecisionLetter());
            }
            if ($model->getAuthorResponse()) {
                yield from $this->hasContentHook($model->getAuthorResponse());
            }
        }

        if ($model instanceof Person) {
            yield from $this->sequenceHook($model->getProfile());
        }

        if ($model instanceof PressPackage) {
            yield from $this->sequenceHook($model->getAbout());
        }
    }

    private function hasContentHook(HasContent $hasContent) : Traversable
    {
        return $this->sequenceHook($hasContent->getContent());
    }

    private function sequenceHook(Sequence $blocks) : Traversable
    {
        foreach ($blocks as $block) {
            if ($block instanceof $this->blockClass) {
                yield $block;
                continue;
            }

            if ($block instanceof HasContent) {
                yield from $this->hasContentHook($block);
            }

            if ($block instanceof Asset) {
                yield from $this->sequenceHook($block->getCaption());
            }
        }
    }
}
