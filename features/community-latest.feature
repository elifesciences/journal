@magazine
Feature: Community 'Latest' list

  Rules:
  - All content types can be included
  - Items are loaded in batches of 6
  - Items are shown most recent first

  Scenario: List shows latest 6 articles
    Given 10 Community articles have been published
    When I go to the Community page
    Then I should see the latest 6 Community articles in the 'Latest' list

  @javascript
  Scenario: Loading more articles adds previous 6 to the list
    Given 20 Community articles have been published
    When I go to the Community page
    And I load more articles
    Then I should see the latest 12 Community articles in the 'Latest' list
