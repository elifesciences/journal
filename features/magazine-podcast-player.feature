@magazine
Feature: Magazine podcast player

  Rules:
  - Latest podcast episode can be played in the header

  Scenario: Header has latest podcast episode player
    Given there are 3 podcast episodes
    When I go to the Magazine page
    Then I should be able to play the latest podcast episode
