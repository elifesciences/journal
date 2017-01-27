@search
Feature: Search page

  Rules:
  - All content types are searchable
  - Search results are loaded in batches of 6
  - Searches are ordered by most relevant first by default
  - If the ordering can't separate two or more search results, fallback ordering is publication date (most recent first) then title (A first).

  Background:
    Given there are 20 research articles about 'Cells'
    And I am on the search page

  Scenario: List shows 4 most relevant results
    When I search for 'Cells'
    Then I should see the 6 most relevant results for 'Cells'

  @javascript
  Scenario: Loading more adds previous 4 to the list
    When I search for 'Cells'
    And I load more results
    Then I should see the 12 most relevant results for 'Cells'
