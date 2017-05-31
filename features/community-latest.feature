@community
Feature: Community 'Latest' list

  Rules:
  - All content types can be included
  - Items are loaded in batches of 10
  - Items are shown most recent first

  Scenario: List shows latest 10 articles
    Given 20 Community articles have been published
    When I go to the Community page
    Then I should see the latest 10 Community articles in the 'Latest' list

  @javascript
  Scenario: Loading more articles adds previous 10 to the list
    Given 30 Community articles have been published
    When I go to the Community page
    And I load more community content
    Then I should see the latest 20 Community articles in the 'Latest' list
