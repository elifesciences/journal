@blog
Feature: Inside eLife page

  Rules:
  - Articles are loaded in batches of 6
  - Articles are shown most recent first

  Background:
    Given there are 20 blog articles

  Scenario: List shows latest 6 blog articles
    When I go the Inside eLife page
    Then I should see the latest 6 blog articles in the 'Latest' list

  @wip
  Scenario: Loading more blog articles adds previous 6 to the list
    When I go the Inside eLife page
    And I load more blog articles
    Then I should see the latest 12 blog articles in the 'Latest' list
