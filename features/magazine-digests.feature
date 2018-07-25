@magazine
Feature: Magazine 'Science Digests' list

  Rules:
  - List contains the first 3 digests from the Science Digests page

  Background:
    Given that Science Digests are enabled

  Scenario: No digests
    Given there are no digests
    When I go to the Magazine page
    Then I should not see the 'Science Digests' list

  Scenario: 3 digests
    Given there are 3 digests
    When I go to the Magazine page
    Then I should see the latest 3 digests in the 'Science Digests' list
    And I should not see a 'See more Science Digests' link

  Scenario: 4 digests
    Given there are 4 digests
    When I go to the Magazine page
    Then I should see the latest 3 digests in the 'Science Digests' list
    And I should see a 'See more Science Digests' link
