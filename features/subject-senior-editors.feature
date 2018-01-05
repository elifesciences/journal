@subject
Feature: Subject page senior editors

  Rules:
  - Show 3 current senior editors for the MSA
  - Picked at random, then sorted by surname

  Scenario: MSA has many senior editors
    Given there are 10 senior editors for the MSA 'Cell biology'
    When I go the MSA 'Cell biology' page
    Then I should see 3 seniors editors for the MSA 'Cell biology' sorted by surname in the 'Senior editors' list

  Scenario: MSA has few senior editors
    Given there are 2 senior editors for the MSA 'Cell biology'
    When I go the MSA 'Cell biology' page
    Then I should see the 2 seniors editors for the MSA 'Cell biology' sorted by surname in the 'Senior editors' list
