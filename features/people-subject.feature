@people
Feature: Editors and People subject page

  Rules:
  - Each MSA has its own People page
  - Shows list of current leadership and senior editors sorted by surname
  - Shows list of current reviewing editors sorted by surname

  Background:
    Given there is the MSA 'Cell biology'

  Scenario: Senior editors for the MSA
    Given there are senior editors for the MSA 'Cell biology':
      | Forename | Surname   | Leadership |
      | Vivek    | Malhotra  |            |
      | Ivan     | Dikic     |            |
      | Anna     | Akhmanova | true       |
      | Tony     | Hunter    |            |
    When I go to the People page for the MSA 'Cell biology'
    Then I should see in the 'Senior editors' list:
      | Anna Akhmanova |
      | Ivan Dikic     |
      | Tony Hunter    |
      | Vivek Malhotra |

  Scenario: Reviewing editors for the MSA
    Given there are reviewing editors for the MSA 'Cell biology':
      | Forename | Surname         |
      | Johannes | Walter          |
      | J Wade   | Harper          |
      | Mohan    | Balasubramanian |
    When I go to the People page for the MSA 'Cell biology'
    Then I should see in the 'Reviewing editors' list:
      | Mohan Balasubramanian |
      | J Wade Harper         |
      | Johannes Walter       |
