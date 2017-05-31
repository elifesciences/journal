@annual-report
Feature: Annual reports page

  Rules:
  - Annual reports are loaded in batches of 10
  - Annual reports are shown most recent first

  Background:
    Given there are 30 annual reports

  Scenario: List shows latest 10 annual reports
    When I go the annual reports page
    Then I should see the 10 most-recent annual reports in the 'Latest annual reports' list

  @javascript
  Scenario: Loading more annual reports adds previous 10 to the list
    When I go the annual reports page
    And I load more annual reports
    Then I should see the 20 most-recent annual reports in the 'Latest annual reports' list
