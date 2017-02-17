@press-packs
Feature: For the press page

  Rules:
  - Press packs are loaded in batches of 6
  - Press packs are shown most recent first

  Background:
    Given there are 20 press packs

  Scenario: List shows latest 6 press packs
    When I go to the 'For the press' page
    Then I should see the latest 6 press packs in the 'Latest' list

  @javascript
  Scenario: Loading more press packs adds previous 6 to the list
    When I go to the 'For the press' page
    And I load more press packs
    Then I should see the latest 12 press packs in the 'Latest' list
