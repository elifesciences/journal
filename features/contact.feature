@contact
Feature: Contact form
  Rules:
  - The completed form is sent to staff@elifesciences.org

  Scenario: User is thanked for completing the form
    Given I am on the contact page
    When I complete the form
    Then I should see a 'thank you' message
    Then the completed form should be sent to editorial@elifesciences.org

  Scenario Outline: Completed form is sent to eLife
    Given I am on the contact page
    Then I set the subject to <Subject>
    And I complete the form
    Then the completed form should be sent to <Recipient>

    Examples:
      | Subject       | Recipient                       |
      | Author query  | editorial@elifesciences.org     |
      | Press query   | press@elifesciences.org         |
      | Site feedback | site-feedback@elifesciences.org |
