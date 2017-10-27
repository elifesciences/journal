@jobs
Feature: Job adverts

  Rules:
  - When on a page for a closed job advert, the page displays "This position is now closed to applications." instead of the content.

  Background:
    Given there is a closed job advert

  Scenario: Show placeholder text instead of content
    When I go to the closed job advert
    Then I should see text "This position is now closed to applications"
    And I should not see text "Closing date for application"
