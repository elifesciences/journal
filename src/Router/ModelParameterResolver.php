<?php

namespace eLife\Journal\Router;

use Cocur\Slugify\SlugifyInterface;
use eLife\ApiSdk\Model;
use InvalidArgumentException;

final class ModelParameterResolver implements ParameterResolver
{
    private $slugify;

    public function __construct(SlugifyInterface $slugify)
    {
        $this->slugify = $slugify;
    }

    public function resolve(string $route, array $parameters) : array
    {
        if (!isset($parameters[0]) || !is_object($parameters[0])) {
            return $parameters;
        }

        $model = $parameters[0];
        unset($parameters[0]);

        if ($model instanceof Model\ArticleVersion) {
            $new = ['id' => $model->getId()];
        } elseif ($model instanceof Model\BlogArticle) {
            $new = ['id' => $model->getId(), 'slug' => $this->slugify->slugify($model->getTitle())];
        } elseif ($model instanceof Model\Collection) {
            $new = ['id' => $model->getId(), 'slug' => $this->slugify->slugify($model->getTitle())];
        } elseif ($model instanceof Model\Digest) {
            $new = ['id' => $model->getId(), 'slug' => $this->slugify->slugify($model->getTitle())];
        } elseif ($model instanceof Model\Event) {
            $new = ['id' => $model->getId(), 'slug' => $this->slugify->slugify($model->getTitle())];
        } elseif ($model instanceof Model\Interview) {
            $new = ['id' => $model->getId(), 'slug' => $this->slugify->slugify($model->getInterviewee()->getPerson()->getPreferredName())];
        } elseif ($model instanceof Model\JobAdvert) {
            $new = ['id' => $model->getId(), 'slug' => $this->slugify->slugify($model->getTitle())];
        } elseif ($model instanceof Model\LabsPost) {
            $new = ['id' => $model->getId(), 'slug' => $this->slugify->slugify($model->getTitle())];
        } elseif ($model instanceof Model\PodcastEpisode) {
            $new = ['number' => $model->getNumber()];
        } elseif ($model instanceof Model\PodcastEpisodeChapterModel) {
            $new = ['number' => $model->getEpisode()->getNumber(), '_fragment' => $model->getChapter()->getTime()];
        } elseif ($model instanceof Model\PressPackage) {
            $new = ['id' => $model->getId(), 'slug' => $this->slugify->slugify($model->getTitle())];
        } elseif ($model instanceof Model\PromotionalCollection) {
            $new = ['id' => $model->getId(), 'slug' => $this->slugify->slugify($model->getTitle())];
        } elseif ($model instanceof Model\Subject) {
            $new = ['id' => $model->getId()];
        } elseif ($model instanceof Model\ReviewedPreprint) {
            $new = ['id' => $model->getId()];
        } else {
            throw new InvalidArgumentException('Unexpected '.get_class($model));
        }

        return $new + $parameters;
    }
}
