@digest
Feature: Science Digests page

  Rules:
  - Digests are loaded in batches of 8
  - Digests are shown most recent first

  Background:
    Given there are 20 digests

  Scenario: List shows latest 8 posts
    When I go the Science Digests page
    Then I should see the latest 8 digests in the 'Latest' list

  @javascript
  Scenario: Loading more posts adds previous 8 to the list
    When I go the Science Digests page
    And I load more digests
    Then I should see the latest 16 digests in the 'Latest' list
