@press-packs
Feature: For the press page

  Rules:
  - Press packs are loaded in batches of 10
  - Press packs are shown most recent first

  Background:
    Given there are 30 press packs

  Scenario: List shows latest 10 press packs
    When I go to the 'For the press' page
    Then I should see the latest 10 press packs in the 'Latest' list

  @javascript
  Scenario: Loading more press packs adds previous 10 to the list
    When I go to the 'For the press' page
    And I load more press packs
    Then I should see the latest 20 press packs in the 'Latest' list
