<?php

namespace eLife\Journal\Etoc;

final class Subscription
{
    private $id;
    private $optOut;
    private $email;
    private $firstName;
    private $lastName;
    private $preferences;
    private $preferencesUrl;

    public function __construct(
        int $id,
        bool $optOut,
        string $email,
        string $firstName,
        string $lastName,
        array $preferences,
        string $preferencesUrl = null
    )
    {
        $this->id = $id;
        $this->optOut = $optOut;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->setPreferences($preferences);
        $this->preferencesUrl = $preferencesUrl;
    }

    /**
     *
     */
    public static function getNewsletters(array $preferences) : array
    {
        $groups = [
            LatestArticles::LABEL => new LatestArticles(),
            EarlyCareer::LABEL => new EarlyCareer(),
            Technology::LABEL => new Technology(),
            ElifeNewsletter::LABEL => new ElifeNewsletter(),
        ];

        return array_map(function ($preference) use ($groups) {
            return $groups[$preference];
        }, array_intersect(array_keys($groups), $preferences));
    }

    private function setPreferences(array $preferences)
    {
        $groups = [
            LatestArticles::GROUP_ID => new LatestArticles(),
            EarlyCareer::GROUP_ID => new EarlyCareer(),
            Technology::GROUP_ID => new Technology(),
            ElifeNewsletter::GROUP_ID => new ElifeNewsletter(),
        ];

        $this->preferences = array_map(function ($preference) use ($groups) {
            return $groups[$preference];
        }, array_intersect(array_keys($groups), $preferences));
    }

    public function id() : int
    {
        return $this->id;
    }

    public function optOut() : bool
    {
        return $this->optOut;
    }

    public function email() : string
    {
        return $this->email;
    }

    public function firstName() : string
    {
        return $this->firstName;
    }

    public function lastName() : string
    {
        return $this->lastName;
    }

    /**
     * @return NewsLetter[]
     */
    public function preferences() : array
    {
        return $this->preferences;
    }

    public function preferencesUrl() : ?string
    {
        return $this->preferencesUrl;
    }

    public function data() : array
    {
        $preferences = array_map(function (Newsletter $preference) {
            return $preference->label();
        }, $this->preferences());

        return [
            'contact_id' => $this->id(),
            'email' => $this->email(),
            'preferences' => $preferences,
            'groups' => implode(',', $preferences),
            'first_name' => $this->firstName(),
            'last_name' => $this->lastName(),
        ];
    }
}
