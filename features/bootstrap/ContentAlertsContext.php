<?php

final class ContentAlertsContext extends Context
{
    private $form;

    /**
     * @Given /^I am on the content alerts page$/
     */
    public function iAmOnTheContentAlertsPage()
    {
        $this->visitPath('/content-alerts');
    }

    /**
     * @When /^I complete the form$/
     */
    public function iCompleteTheForm()
    {
        $page = $this->getSession()->getPage();

        $page->fillField('content_alerts[email]', $this->form['email'] = 'foo@example.com');
        $page->pressButton('Subscribe');
    }

    /**
     * @Then /^I should see a 'thank you' message$/
     */
    public function iShouldSeeAThankYouMessage()
    {
        $this->assertSession()
            ->elementContains('css', 'h1', 'Thank you for subscribing!');
    }
}
