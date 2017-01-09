@event
Feature: Events

  Rules:
  - List contains upcoming and in-progress events
  - Events are loaded in batches of 6
  - Events are shown earliest starting date/time first

  Background:
    Given there are 20 upcoming events

  Scenario: List shows latest 6 events
    When I go to the events page
    Then I should see the 6 earliest upcoming events in the 'Upcoming events' list

  @javascript
  Scenario: Loading more events adds previous 6 to the list
    When I go to the events page
    And I load more events
    Then I should see the 12 earliest upcoming events in the 'Upcoming events' list
