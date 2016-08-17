@magazine
Feature: Magazine 'Latest' list

  Rules:
  - Articles are loaded in batches of 6
  - Articles are shown most recent first
  - If an article is PoA, the date used for ordering is the first PoA date
  - If an article is VoR, the date used for ordering is the first VoR date
  - The following types of article are included:
    - Editorial
    - Insight
    - Feature
    - Podcast episode
    - Collection
    - Interview

  Scenario: List shows latest 6 articles
    Given 10 Magazine articles have been published
    When I go to the Magazine page
    Then I should see the latest 6 Magazine articles in the 'Latest' list

  @wip
  Scenario: Loading more articles adds previous 6 to the list
    Given 20 Magazine articles have been published
    When I go to the Magazine page
    And I load more articles
    Then I should see the latest 12 Magazine articles in the 'Latest' list
