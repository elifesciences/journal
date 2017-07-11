@magazine
Feature: Magazine 'Latest' list

  Rules:
  - Articles are loaded in batches of 10
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

  Scenario: List shows latest 10 articles
    Given 20 Magazine articles have been published
    When I go to the Magazine page
    Then I should see the latest 10 Magazine articles in the 'Latest' list

  @javascript
  Scenario: Loading more articles adds previous 10 to the list
    Given 30 Magazine articles have been published
    When I go to the Magazine page
    And I load more articles
    Then I should see the latest 20 Magazine articles in the 'Latest' list
