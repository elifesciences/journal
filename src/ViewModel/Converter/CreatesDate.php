<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\Patterns\ViewModel;

trait CreatesDate
{
    /**
     * @return ViewModel\Date|null
     */
    final private function simpleDate(HasPublishedDate $model, array $context = [])
    {
        if ('published' !== ($context['date'] ?? 'default')) {
            if ($model instanceof ArticleVersion || $model instanceof ReviewedPreprint) {
                return $model->getStatusDate() ? ViewModel\Date::simple(
                    $model->getStatusDate(),
                    ($context['updatedText'] ?? true) ??
                    $model->getStatusDate() !== $model->getPublishedDate()) : null;
            } elseif ($model instanceof Collection) {
                return ViewModel\Date::simple($model->getUpdatedDate() ?? $model->getPublishedDate(), !empty($model->getUpdatedDate()));
            }
        }

        return $model->getPublishedDate() ? ViewModel\Date::simple($model->getPublishedDate()) : null;
    }
}
