@homepage
Feature: Homepage Hero banner

  Rules:
  - Contains 1 hero banner
  - Any type eLife content can have a cover
  - Hero banner has a title, a short text, date, category, subjects, and image

  Background:
    Given 10 articles have been published

  Scenario: Cover can have a title and image different from the content they link to
    Given there are 4 covers
    When I go to the homepage with hero query parameter
    Then I should see the 'Cover0' cover in the hero banner
    Then I should see 3 highlights