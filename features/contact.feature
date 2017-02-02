@contact
Feature: Contact form
  Rules:
  - When the form is filled in, the user is sent a thank you email
  - The completed form is sent to staff@elifesciences.org

  Scenario: User is thanked for completing the form
    Given I am on the contact page
    When I complete the form
    Then I should see a 'thank you' message
    And I should be sent a 'thank you' email

  Scenario: Completed form is sent to eLife
    Given I am on the contact page
    When I complete the form
    Then the completed form should be sent to staff@elifesciences.org
