@article-type
Feature: Article type page

  Rules:
  - Articles are loaded in batches of 6
  - Articles are shown most recent first
  - If an article is POA, the date used for ordering is the first POA date
  - If an article is VOR, the date used for ordering is the first VOR date

  Scenario: List shows latest 6 articles
    Given 10 research articles have been published
    When I go to the research articles page
    Then I should see the latest 6 research articles in the 'Latest articles' list

  @wip
  Scenario: Loading more articles adds previous 6 to the list
    Given 20 research articles have been published
    When I go to the research articles page
    And I load more articles
    Then I should see the latest 12 research articles in the 'Latest articles' list
