@labs
Feature: Labs page
  In order to...
  As a...
  I want...

  Rules:
  - Labs experiments are loaded in batches of 6
  - Labs experiments are shown most recent number first

  Background:
    Given there are 20 Labs experiments

  Scenario: List shows latest 6 experiments
    When I go the Labs page
    Then I should see the latest 6 Labs experiments in the 'Experiments' list

  @wip
  Scenario: Loading more experiments adds previous 6 to the list
    When I go the Labs page
    And I load more experiments
    Then I should see the latest 12 Labs experiments in the 'Experiments' list
