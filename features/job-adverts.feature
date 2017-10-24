@jobs
Feature: Job adverts

  Rules:
  - List contains job adverts that have not passed their closing date
  - Job adverts are loaded in batches of 10
  - Job adverts are shown in ascending closing date order

  Background:
    Given there are 30 open job adverts

  Scenario: List shows latest 10 job adverts
    When I go to the job adverts page
    Then I should see the 10 job adverts with the nearest closing dates in the 'Open job adverts' list
#
#  @javascript
#  Scenario: Loading more job adverts adds previous 10 to the list
#    When I go to the job adverts page
#    And I load more job adverts
#    Then I should see the 20 job adverts with closing dates closest to todays date in the 'Open job adverts' list
