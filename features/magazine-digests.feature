@magazine
Feature: Magazine 'eLife digests' list

  Rules:
  - Latest 3 articles that appear on https://medium.com/@elife

  Background:
    Given 10 Magazine articles have been published

  Scenario: List shows latest digests
    Given there are 5 digests on https://medium.com/@elife
    When I go to the Magazine page
    Then I should see the latest 3 digests in the 'eLife digests' list
