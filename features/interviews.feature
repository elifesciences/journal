@interview
Feature: Interviews page

  Rules:
  - Interviews are loaded in batches of 10
  - Interviews are shown most recent first

  Background:
    Given there are 30 interviews

  Scenario: List shows latest 10 interviews
    When I go to the interviews page
    Then I should see the latest 10 interviews in the 'Latest' list

  @javascript
  Scenario: Loading more interviews adds previous 10 to the list
    When I go to the interviews page
    And I load more interviews
    Then I should see the latest 20 interviews in the 'Latest' list
