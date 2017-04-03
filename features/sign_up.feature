@homepage
Feature: Sign-up form

  Scenario: New subscription
    Given I am on the homepage
    When I fill in the sign-up form
    Then I should be prompted to check my email

  Scenario: Already a subscriber
    Given I am on the homepage
    And I am already subscribed
    When I fill in the sign-up form
    Then I should be reminded that I am already subscribed
