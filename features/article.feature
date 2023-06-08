@article
Feature: Article page

  Rules:
  - Abstract, digest and first body section are open, the rest closed

  @javascript
  Scenario: Article sections can be closed
    Given there is a research article VoR
    When I go the research article page
    Then the "Abstract" section should be open
    And the "eLife digest" section should be open
    And the "Introduction" section should be open
    But the "Results" section should be closed
