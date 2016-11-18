<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\Block;
use eLife\Patterns\ViewModel;

/**
 * Generic manipulation of ViewModel objects, and of Model objects turning them 
 * into ViewModel ones
 */
trait ViewModelCreation
{
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
}
