@homepage
Feature: Homepage Hero banner

  Rules:
  - Contains 1 hero banner
  - Any type eLife content can have a cover
  - Hero banner has a title, a short text, date, category, subjects, and image

  Background:
    Given 10 articles have been published

  Scenario: Cover can have a title and image different from the content they link to
    Given there is a collection called 'Tropical disease'
    And there is a cover linking to the 'Tropical disease' collection
    When I go to the homepage
    Then I should see the 'Tropical disease' cover in the hero banner
