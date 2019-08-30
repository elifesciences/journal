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

  Scenario: Completed Author Query form is sent to Editorial
    Given I am on the contact page
    Then I set the subject to 'Author query'
    And I complete the form
    Then the completed form should be sent to Editorial

  Scenario: Completed Press Query form is sent to Communications
    Given I am on the contact page
    Then I set the subject to 'Press query'
    And I complete the form
    Then the completed form should be sent to Communications

  Scenario: Completed Site Feedback form is sent to Site Feedback Google Group
    Given I am on the contact page
    Then I set the subject to 'Site feedback'
    And I complete the form
    Then the completed form should be sent to Site Feedback Google Group
