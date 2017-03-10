@annual-report
Feature: Annual reports page

  Rules:
  - Annual reports are loaded in batches of 6
  - Annual reports are shown most recent first

  Background:
    Given there are 20 annual reports

  Scenario: List shows latest 6 annual reports
    When I go the annual reports page
    Then I should see the 6 most-recent annual reports in the 'Latest annual reports' list

  @javascript
  Scenario: Loading more annual reports adds previous 6 to the list
    When I go the annual reports page
    And I load more annual reports
    Then I should see the 12 most-recent annual reports in the 'Latest annual reports' list
