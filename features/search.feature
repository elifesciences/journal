@search
Feature: Search page

  Rules:
  - All content types are searchable
  - Search results are loaded in batches of 10
  - Searches are ordered by most relevant first by default
  - If the ordering can't separate two or more search results, fallback ordering is publication date (most recent first) then title (A first).

  Background:
    Given there are 30 research articles about 'Cells'
    And I am on the search page

  Scenario: List shows 10 most relevant results
    When I search for 'Cells'
    Then I should see the 10 most relevant results for 'Cells'

  @javascript
  Scenario: Loading more adds previous 10 to the list
    When I search for 'Cells'
    And I load more results
    Then I should see the 20 most relevant results for 'Cells'
