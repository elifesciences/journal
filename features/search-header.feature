@search
Feature: Search from page header

  Rules:
  - If the current page has a subject, the first subject appears as a limiting option when searching on that page

  Background:
    Given I am reading an article:
      | Subjects | Cell biology, Immunology |

  @javascript
  Scenario: Page has subjects
    When I click search
    Then I should see the option to limit the search to 'Cell biology'
