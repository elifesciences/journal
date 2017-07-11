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
  - Items are loaded in batches of 10
  - Items are shown most recent first

  Background:
    Given there are 30 articles with the MSA 'Cell biology'

  Scenario: List shows latest 10 items
    When I go the MSA 'Cell biology' page
    Then I should see the latest 10 items with the MSA 'Cell biology' in the 'Latest articles' list

  @javascript
  Scenario: Loading more content adds previous 10 to the list
    When I go the MSA 'Cell biology' page
    And I load more articles
    Then I should see the latest 20 items with the MSA 'Cell biology' in the 'Latest articles' list
