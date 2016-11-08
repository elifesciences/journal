<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\Patterns\ViewModel;

final class ViewModelConverterRegistry implements ViewModelConverter
{
    private $registry = [];

    public function add(ViewModelConverter $registry)
    {
        $this->registry[] = $registry;
    }

    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return $this->findModelConverter($object, $viewModel, $context)->convert($object, $viewModel, $context);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        try {
            $this->findModelConverter($object, $viewModel, $context);
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    private function findModelConverter($object, string $viewModel = null, array $context) : ViewModelConverter
    {
        $modelConverters = [];

        foreach ($this->registry as $modelConverter) {
            if ($modelConverter->supports($object, $viewModel, $context)) {
                $modelConverters[] = $modelConverter;
            }
        }

        if (empty($modelConverters)) {
            throw new \RuntimeException('Can\'t find model converter from '.get_class($object).' to '.$viewModel.' with '.var_export($context, true));
        } elseif (count($modelConverters) > 1) {
            throw new \RuntimeException('Can\'t find single model converter from '.get_class($object).' to '.$viewModel.' with '.var_export($context, true));
        }

        return $modelConverters[0];
    }
}
