@magazine
Feature: Magazine highlights

  Rules:
  - List contains between 3 and 6 Magazine articles
  - A highlight has a title and, optionally, an image, which can both be different from the article that it links to

  Background:
    Given 10 Magazine articles have been published

  Scenario: Magazine highlights use details from the article that they link to by default
    Given there is a collection called 'Tropical disease'
    And there is a Magazine highlight linking to the 'Tropical disease' collection
    When I go to the Magazine page
    Then I should see the 'Tropical disease' Magazine highlight in the 'Highlights' list
    And I should see the title and image from the 'Tropical disease' collection used in the Magazine highlight

  Scenario: Magazine highlights can have a title and image different from the article they link to
    Given there is a collection called 'Tropical disease'
    And there is a Magazine highlight linking to the 'Tropical disease' collection with a custom title and image
    When I go to the Magazine page
    Then I should see the 'Tropical disease' Magazine highlight in the 'Highlights' list
    And I should see the custom title and image used in the 'Tropical disease' Magazine highlight
