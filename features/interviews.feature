@interview
Feature: Interviews page

  Rules:
  - Interviews are loaded in batches of 6
  - Interviews are shown most recent first

  Background:
    Given there are 20 interviews

  Scenario: List shows latest 6 interviews
    When I go to the interviews page
    Then I should see the latest 6 interviews in the 'Latest' list

  @javascript
  Scenario: Loading more interviews adds previous 6 to the list
    When I go to the interviews page
    And I load more interviews
    Then I should see the latest 12 interviews in the 'Latest' list
