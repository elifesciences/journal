@blog
Feature: Inside eLife page

  Rules:
  - Articles are loaded in batches of 10
  - Articles are shown most recent first

  Background:
    Given there are 30 blog articles

  Scenario: List shows latest 10 blog articles
    When I go the Inside eLife page
    Then I should see the latest 10 blog articles in the 'Latest' list

  @javascript
  Scenario: Loading more blog articles adds previous 10 to the list
    When I go the Inside eLife page
    And I load more blog articles
    Then I should see the latest 20 blog articles in the 'Latest' list
