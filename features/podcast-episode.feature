@podcast
Feature: Podcast episode
  In order to...
  As a...
  I want...

  Rules:
  - Clicking a chapter title will start the audio player playing that chapter
  - Related list shows the first article covered by each of the chapters

  @wip
  Scenario: Clicking a chapter title plays the chapter
    Given there is a podcast episode with two chapters
    When I go the podcast episode page
    And I click on the second chapter's title
    Then the audio player should start playing the second chapter

  Scenario: Related list shows first article for each chapter
    Given there is a podcast episode with two chapters
    When I go the podcast episode page
    Then I should see the two articles covered by the chapters in the 'Related' list
