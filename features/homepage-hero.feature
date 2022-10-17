@homepage
Feature: Homepage hero and highlights

  Rules:
  - Contains between 1 and 4 covers
  - Any type eLife content can have a cover
  - A cover has a title and image, which can be different from the content that it links to

  Background:
    Given 10 articles have been published

  Scenario: Covers use details from the content that they link to by default
    Given there is a collection called 'Tropical disease'
    And there is a cover linking to the 'Tropical disease' collection
    When I go to the homepage
    Then I should see the 'Tropical disease' cover in the hero banner
    And I should see the title and image from the 'Tropical disease' collection used in the 'Tropical disease' cover

  Scenario: Cover can have a title and image different from the content they link to
    Given there is a collection called 'Tropical disease'
    And there is a cover linking to the 'Tropical disease' collection with a custom title and image
    When I go to the homepage
    Then I should see the 'Tropical disease' cover in the hero banner
    And I should see the custom title and image used in the 'Tropical disease' cover
