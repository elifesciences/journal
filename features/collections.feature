@collection
Feature: Collections page latest collections

  Rules:
  - Collections are loaded in batches of 10
  - Collections are shown most recently updated first

  Background:
    Given there are 30 collections

  Scenario: List shows latest 10 collections
    When I go the collections page
    Then I should see the 10 most-recently-updated collections in the 'Latest collections' list

  @javascript
  Scenario: Loading more content adds previous 10 to the list
    When I go the collections page
    And I load more collections
    Then I should see the 20 most-recently-updated collections in the 'Latest collections' list
