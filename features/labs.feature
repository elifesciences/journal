@labs
Feature: Labs page

  Rules:
  - Labs experiments are loaded in batches of 8
  - Labs experiments are shown most recent number first

  Background:
    Given there are 20 Labs experiments

  Scenario: List shows latest 8 experiments
    When I go the Labs page
    Then I should see the latest 8 Labs experiments in the 'Latest' list

  @javascript
  Scenario: Loading more experiments adds previous 8 to the list
    When I go the Labs page
    And I load more experiments
    Then I should see the latest 16 Labs experiments in the 'Latest' list
