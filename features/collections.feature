@collection
Feature: Collections page latest collections

  Rules:
  - Collections are loaded in batches of 6
  - Collections are shown most recently updated first

  Background:
    Given there are 20 collections

  Scenario: List shows latest 6 collections
    When I go the collections page
    Then I should see the 6 most-recently-updated collections in the 'Latest collections' list

  @javascript
  Scenario: Loading more content adds previous 6 to the list
    When I go the collections page
    And I load more collections
    Then I should see the 12 most-recently-updated collections in the 'Latest collections' list
