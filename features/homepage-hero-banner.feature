@homepage
Feature: Homepage Hero banner

  Rules:
  - Contains 1 hero banner
  - Any type eLife content can have a cover
  - Hero banner has a title, a short text, date, category, subjects, and image

  Background:
    Given 10 articles have been published

  @javascript
  Scenario: Cover can have a title and image different from the content they link to
    Given There is an article called 'Object vision to hand action in macaque parietal, premotor, and motor cortices'
    When I go to the homepage
    Then I should see the 'Object vision to hand action in macaque parietal, premotor, and motor cortices' cover in the hero banner
