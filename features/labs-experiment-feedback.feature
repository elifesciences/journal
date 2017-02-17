@labs
Feature: Labs experiment feedback form

  Rules:
  - When the form is filled in, the user is sent a thank you email
  - The completed form is sent to labs@elifesciences.org

  Scenario: User is thanked for completing the form
    Given I am on a Labs experiment page
    When I complete the feedback form
    Then I should see a 'thank you' message
    And I should be sent a 'thank you' email

  Scenario: Completed form is sent to eLife
    Given I am on a Labs experiment page
    When I complete the feedback form
    Then the completed form should be sent to labs@elifesciences.org
