@subject
Feature: Subject page highlights

  Rules:
  - 3 latest highlighted content with the MSA

  Scenario: List shows highlights
    Given there are 5 highlighted articles with the MSA 'Cell biology'
    When I go the MSA 'Cell biology' page
    Then I should see the latest 3 highlighted articles with the MSA 'Cell biology' in the 'Highlights' list
