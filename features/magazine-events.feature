@magazine
Feature: Magazine events
  In order to...
  As a...
  I want...

  Rules:
  - List contains the first 3 events from the Events page
  - A 'Go to events' link only appears if the Events page has 4 or more events

  Background:
    Given 10 Magazine articles have been published

  Scenario: No events
    Given there are no upcoming events
    When I go to the Magazine page
    Then I should not see the 'Upcoming events' list

  Scenario: 3 Upcoming events
    Given there are 3 upcoming events
    When I go to the Magazine page
    Then I should see 3 upcoming events in the 'Events' list
    And I should not see a 'See more events' link

  Scenario: 4 Upcoming events
    Given there are 4 upcoming events
    When I go to the Magazine page
    Then I should see 3 upcoming events in the 'Events' list
    And I should see a 'See more events' link
