@search
Feature: Search page refinements

  Rules:
  - Searches can be refined by MSA and content type
  - Refinements show the number of matches for the current search term
  - Refinements can still be selected, even if they won't refine the search any more

  Background:
    Given there are 4 articles about 'Cells' with the MSA 'Biochemistry'
    And there are 4 articles about 'Cells' with the MSA 'Immunology'
    And I am on the search page

  Scenario: Filter by MSA
    Given I searched for 'Cells'
    When I filter by the MSA 'Biochemistry'
    Then I should see the 4 most relevant results about 'Cells' with the MSA 'Biochemistry'

  Scenario: Filter by two MSAs
    Given I searched for 'Cells'
    And I filtered by the MSA 'Biochemistry'
    When I filter by the MSA 'Immunology'
    Then I should see the 6 most relevant results about 'Cells' with the MSA 'Biochemistry' or 'Immunology'
