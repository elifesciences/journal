<?php

namespace eLife\Journal\ViewModel\Converter;

use DateTimeImmutable;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\Patterns\ViewModel;

trait CreatesDate
{
    /**
     * @return ViewModel\Date|null
     */
    final private function simpleDate(HasPublishedDate $model, array $context = [], DateTimeImmutable $statusDateOverride = null)
    {
        if ('published' !== ($context['date'] ?? 'default')) {
            if ($model instanceof ArticleVersion) {
                $statusDate = $statusDateOverride ?? $model->getStatusDate();

                return $statusDate ? ViewModel\Date::simple($statusDate, $statusDate != $model->getPublishedDate()) : null;
            } elseif ($model instanceof Collection) {
                return ViewModel\Date::simple($model->getUpdatedDate() ?? $model->getPublishedDate(), !empty($model->getUpdatedDate()));
            }
        }

        return $model->getPublishedDate() ? ViewModel\Date::simple($model->getPublishedDate()) : null;
    }
}
