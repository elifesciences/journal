@archive
Feature: Archive month

  Rules:
  - The heading image is the same as the image used for the month on the monthly archive page
  - 'Research articles' list contains items from the homepage 'Latest research' list published during the month
  - 'Magazine' list contains Magazine items published during the month
  - Podcast episodes always appear at the top of the 'Magazine' list

  Background:
    Given today is 26 April 2016

  Scenario: Header is the same as the monthly archive
    Given there are articles with covers:
      | Article   | Cover text      | Page views | Published     |
      | Article 1 | Article 1       | 100        | 14 March 2016 |
      | Article 2 | Some Article    | 300        | 11 March 2016 |
      | Article 3 | Article 3       | 200        | 25 March 2016 |
      | Article 4 | Another Article | 1,000      | 15 March 2016 |
      | Article 5 | Article 5       | 50         | 29 March 2016 |
    When I go to the archive for March 2016
    Then I should see the image from the cover for "Article 4" in the header

  Scenario: 'Research articles' list shows research articles published during the month
    Given 4 research articles were published during March 2016
    When I go to the archive for March 2016
    Then I should see the 4 research articles published during March 2016 in the 'Research articles' list

  Scenario: 'Magazine' list shows Magazine items published during the month
    Given 4 Magazine articles were published during March 2016
    When I go to the archive for March 2016
    Then I should see the 4 Magazine items published during March 2016 in the 'Magazine' list

  Scenario: Podcast episodes appear at the top of the 'Magazine' list
    Given there are Magazine articles:
      | Article           | Type            | Published     |
      | Insight 1         | Insight         | 31 March 2016 |
      | Podcast episode 1 | Podcast episode | 30 March 2016 |
    When I go to the archive for March 2016
    Then I should see the "Podcast episode 1" at the top of the 'Magazine' list
