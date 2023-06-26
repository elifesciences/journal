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
                $statusDate = $model->getStatusDate();
                $publishedDate = $model->getPublishedDate();
                $isUpdated = $statusDate != $publishedDate;
                if (isset($context['updatedText'])) {
                    $isUpdated = $context['updatedText'];
                }
                return $statusDate ? ViewModel\Date::simple($statusDate, $isUpdated) : null;
            } elseif ($model instanceof Collection) {
                return ViewModel\Date::simple($model->getUpdatedDate() ?? $model->getPublishedDate(), !empty($model->getUpdatedDate()));
            }
        }

        return $model->getPublishedDate() ? ViewModel\Date::simple($model->getPublishedDate()) : null;
    }
}
