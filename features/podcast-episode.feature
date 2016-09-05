@podcast
Feature: Podcast episode
  In order to...
  As a...
  I want...

  Rules:
  - Initial player title is "Episode [Episode number]"
  - When chapter data is available and applicable, player title is "Episode [Episode number]: [Chapter number]. Chapter title", where chapter info is determined by playback position.
  - Clicking a chapter title will start the audio player playing that chapter
  - Current chapter is indicated in the listing
  - Related list shows the first article covered by each of the chapters

  @wip
  Scenario: Current chapter's number and title is part of player's title
    Given there is a podcast episode with two chapters
    When I go the podcast episode page
    And I click on the second chapter's title
    Then the second chapter's number and title appear as part of the player title

  @wip
  Scenario: Current chapter indicated in chapter list
    Given there is a podcast episode with two chapters
    When I go the podcast episode page
    And I click on the second chapter's title
    Then there is an indication near the second chapter's title that this is the current chapter

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
