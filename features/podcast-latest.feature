@podcast
Feature: Podcast page latest podcasts

  Rules:
  - Podcast episodes are loaded in batches of 6
  - Podcast episodes are shown most recent first

  Background:
    Given there are 20 podcast episodes

  Scenario: List shows latest 6 episodes
    When I go to the podcast page
    Then I should see the latest 6 podcast episodes in the 'Latest episodes' list

  @javascript
  Scenario: Loading more content adds previous 6 to the list
    When I go to the podcast page
    And I load more episodes
    Then I should see the latest 12 podcast episodes in the 'Latest episodes' list
