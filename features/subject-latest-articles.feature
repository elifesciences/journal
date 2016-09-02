@subject
Feature: Subject page latest articles

  Rules:
  - The following types of content with the MSA are included:
    - Research article
    - Research advance
    - Research exchange
    - Short report
    - Tools and resources
    - Replication study
    - Editorial
    - Insight
    - Feature
    - Collection
  - Items are loaded in batches of 6
  - Items are shown most recent first

  Background:
    Given there are 20 articles with the MSA 'Cell biology'

  Scenario: List shows latest 6 items
    When I go the MSA 'Cell biology' page
    Then I should see the latest 6 items with the MSA 'Cell biology' in the 'Latest articles' list

  @wip
  Scenario: Loading more content adds previous 6 to the list
    When I go the MSA 'Cell biology' page
    And I load more articles
    Then I should see the latest 12 items with the MSA 'Cell biology' in the 'Latest articles' list
