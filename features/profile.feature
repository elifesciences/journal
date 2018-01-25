@profile
Feature: Profile page

  Rules:
  - Annotations are loaded in batches of 10
  - Annotations are shown most recently updated first

  Background:
    Given Josiah Carberry has 30 annotations

  Scenario: List shows 10 most-recently-updated annotations
    When I go to Josiah Carberry's profile page
    Then I should see his 10 most-recently-updated annotations in the 'Annotations' list

  @javascript
  Scenario: Loading more annotations adds previous 10 to the list
    When I go to Josiah Carberry's profile page
    And I load more annotations
    Then I should see his 20 most-recently-updated annotations in the 'Annotations' list
