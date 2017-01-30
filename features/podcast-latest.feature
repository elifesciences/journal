@podcast
Feature: Podcast page latest podcasts

  Rules:
  - Podcast episodes are loaded in batches of 8
  - Podcast episodes are shown most recent first

  Background:
    Given there are 20 podcast episodes

  Scenario: List shows latest 8 episodes
    When I go to the podcast page
    Then I should see the latest 8 podcast episodes in the 'Latest episodes' list

  @javascript
  Scenario: Loading more content adds previous 8 to the list
    When I go to the podcast page
    And I load more episodes
    Then I should see the latest 16 podcast episodes in the 'Latest episodes' list
