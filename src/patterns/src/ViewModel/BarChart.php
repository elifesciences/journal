<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class BarChart implements ViewModel
{
    const METRIC_DOWNLOADS = 'downloads';
    const METRIC_PAGE_VIEWS = 'page-views';
    const PERIOD_DAY = 'day';
    const PERIOD_MONTH = 'month';

    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $id;
    private $type;
    private $containerId;
    private $apiEndpoint;
    private $metric;
    private $period;

    public function __construct(
        string $id,
        string $type,
        string $containerId,
        string $apiEndpoint,
        string $metric,
        string $period = self::PERIOD_MONTH
    ) {
        Assertion::notBlank($id);
        Assertion::choice($type, ['article']);
        Assertion::notBlank($containerId);
        Assertion::url($apiEndpoint);
        Assertion::choice($metric, [self::METRIC_DOWNLOADS, self::METRIC_PAGE_VIEWS]);
        Assertion::choice($period, [self::PERIOD_DAY, self::PERIOD_MONTH]);

        $this->id = $id;
        $this->type = $type;
        $this->containerId = $containerId;
        $this->apiEndpoint = rtrim($apiEndpoint, '/');
        $this->metric = $metric;
        $this->period = $period;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/bar-chart.mustache';
    }
}
