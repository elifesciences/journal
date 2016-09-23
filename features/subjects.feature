@subject
Feature: Subjects page

  Rules:
  - All subjects are shown in alphabetical order

  Scenario: List shows all subjects
    Given there are 20 subjects
    When I go the Subjects page
    Then I should see the 20 subjects.
