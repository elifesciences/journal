@labs
Feature: Labs page

  Rules:
  - Labs posts are loaded in batches of 8
  - Labs posts are shown most recent number first

  Background:
    Given there are 20 Labs posts

  Scenario: List shows latest 8 posts
    When I go the Labs page
    Then I should see the latest 8 Labs posts in the 'Latest' list

  @javascript
  Scenario: Loading more posts adds previous 8 to the list
    When I go the Labs page
    And I load more posts
    Then I should see the latest 16 Labs posts in the 'Latest' list
