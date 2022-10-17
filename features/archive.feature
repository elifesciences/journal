@archive
Feature: Monthly archive

  Rules:
  - Each year has a monthly archive
  - Months are ordered from January to December
  - The current month is not included
  - Each month has the 4 most viewed items that have appeared in the homepage hero or highlights
  - Each month uses the image from the most viewed item that has appeared in the homepage hero or highlights
  - It doesn't matter when the cover has appeared in the homepage hero or highlights

  Background:
    Given today is 26 April 2016

  Scenario: Current month is not included
    When I go to the monthly archive for 2016
    Then I should see archives for:
      | January 2016  |
      | February 2016 |
      | March 2016    |

  Scenario: Each month shows up to 4 most viewed items that have appeared in the homepage hero or highlights
    Given there are articles with covers:
      | Article   | Cover text      | Page views | Published     |
      | Article 1 | Article 1       | 100        | 14 March 2016 |
      | Article 2 | Some Article    | 300        | 11 March 2016 |
      | Article 3 | Article 3       | 200        | 25 March 2016 |
      | Article 4 | Another Article | 1,000      | 15 March 2016 |
      | Article 5 | Article 5       | 50         | 29 March 2016 |
    When I go to the monthly archive for 2016
    Then I should see the image from the cover for "Article 4" in the archive for March 2016
    And I should see the following cover articles for March 2016:
      | Another Article |
      | Some Article    |
      | Article 3       |
      | Article 1       |
