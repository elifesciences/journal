@homepage
Feature: Homepage 'Latest research' list

  Rules:
  - Articles are loaded in batches of 6
  - Articles are shown most recent first
  - If an article is POA, the date used for ordering is the first POA date
  - If an article is VOR, the date used for ordering is the first VOR date
  - The following types of article are included:
    - Research article
    - Research advance
    - Research exchange
    - Short report
    - Tools and resources
    - Replication study

  Scenario: List shows latest 6 articles
    Given 10 articles have been published
    When I go to the homepage
    Then I should see the latest 6 articles in the 'Latest research' list

  @javascript
  Scenario: Loading more articles adds previous 6 to the list
    Given 20 articles have been published
    When I go to the homepage
    And I load more articles
    Then I should see the latest 12 articles in the 'Latest research' list
