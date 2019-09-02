<?php

final class ContactContext extends Context
{
    /**
     * @Given /^I am on the contact page$/
     */
    public function iAmOnTheContactPage()
    {
        $this->visitPath('/contact');
    }

    /**
     * @When /^I set the subject to '(.*?)'$/
     */
    public function iSetTheSubjectField($subject)
    {
        $page = $this->getSession()->getPage();

        $page->fillField('contact[subject]', $subject);
    }

    /**
     * @When /^I complete the form$/
     */
    public function iCompleteTheForm()
    {
        $this->readyToRecordEmails();

        $page = $this->getSession()->getPage();

        $page->fillField('contact[name]', 'Foo Bar');
        $page->fillField('contact[email]', 'foo@example.com');

        if (!$page->findField('contact[subject]')->getValue()) {
            $page->fillField('contact[subject]', 'Author query');
        }
        $page->fillField('contact[question]', "Lorem ipsum dolor sit amet, consectetur adipiscing elit.\n\nVivamus rhoncus turpis quam, sit amet finibus elit pharetra eget.");
        $page->pressButton('Submit');

        $this->recordEmails();
    }

    /**
     * @Then /^I should see a 'thank you' message$/
     */
    public function iShouldSeeAThankYouMessage()
    {
        $this->assertSession()
            ->elementContains('css', '.info-bar--success', 'Thanks Foo Bar, we have received your question.');
    }

    /**
     * @Given /^I should be sent a 'thank you' email$/
     */
    public function iShouldBeSentAThankYouEmail()
    {
        $this->assertEmailSent(['do_not_reply@elifesciences.org' => null], ['foo@example.com' => 'Foo Bar'],
            'Question to eLife', 'Thanks for your question. We will respond as soon as we can.

eLife Sciences Publications, Ltd is a limited liability non-profit non-stock corporation incorporated in the State of Delaware, USA, with company number 5030732, and is registered in the UK with company number FC030576 and branch number BR015634 at the address Westbrook Centre, Milton Road, Cambridge CB4 1YG.');
    }

    /**
     * @Then /^the completed form should be sent to (.*)$/
     */
    public function theCompletedFormShouldBeSentToStaffElifesciencesOrg($team)
    {
        switch ($team) {
            case 'Editorial':
            $emailAddress = 'editorial@elifesciences.org';
            $subject = 'Author query';
            break;
            case 'Communications':
            $emailAddress = 'press@elifesciences.org';
            $subject = 'Press query';
            break;
            case 'Site Feedback Google Group':
            $emailAddress = 'site-feedback@elifesciences.org';
            $subject = 'Site feedback';
            break;
            default: throw new LogicException('Unknown Team');
        }
        $this->assertEmailSent(['do_not_reply@elifesciences.org' => null], [$emailAddress => null],
            'Question submitted: '.$subject, 'A question has been submitted on '.$this->locatePath('/contact').'

Name
----
Foo Bar

Email
-----
foo@example.com

Subject
-------
'.$subject.'

Question
--------
Lorem ipsum dolor sit amet, consectetur adipiscing elit.

Vivamus rhoncus turpis quam, sit amet finibus elit pharetra eget.');
    }
}
