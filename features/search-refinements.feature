@search
Feature: Search page refinements

  Rules:
  - Searches can be refined by MSA and Magazine/Research content
  - Refinements show the number of matches for the current search term
  - Refinements can still be selected, even if they won't refine the search any more

  Background:
    Given there are 2 research articles about 'Cells' with the MSA 'Biochemistry'
    And there are 2 insights about 'Cells' with the MSA 'Biochemistry'
    And there are 2 research articles about 'Cells' with the MSA 'Immunology'
    And there are 2 insights about 'Cells' with the MSA 'Immunology'
    And I am on the search page

  @javascript
  Scenario: Filter by MSA
    Given I searched for 'Cells'
    When I filter by the MSA 'Biochemistry'
    Then I should see the 4 most relevant results about 'Cells' with the MSA 'Biochemistry'

  @javascript
  Scenario: Filter by Magazine content
    Given I searched for 'Cells'
    When I filter by the content type 'Magazine'
    Then I should see the 4 most relevant results about 'Cells' with the content type 'Insight'

  @javascript
  Scenario: Filter by two MSAs
    Given I searched for 'Cells'
    And I filtered by the MSA 'Biochemistry'
    When I filter by the MSA 'Immunology'
    Then I should see the 8 most relevant results about 'Cells' with the MSA 'Biochemistry' or 'Immunology'

  @javascript
  Scenario: Filter by MSA and Research content
    Given I searched for 'Cells'
    And I filtered by the MSA 'Biochemistry'
    When I filter by the content type 'Research'
    Then I should see the 2 most relevant results about 'Cells' with the MSA 'Biochemistry' and the content type 'Research article'
