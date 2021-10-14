@etoc
Feature: Content alerts form
  Rules:
  - The completed form is submitted to Civi Crm

  Scenario: User is thanked for completing the form
    Given I am on the content alerts page
    When I complete the form
    Then I should see a 'thank you' message
