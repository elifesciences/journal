<?php

final class ContactContext extends Context
{
    private $form;

    /**
     * @Given /^I am on the contact page$/
     */
    public function iAmOnTheContactPage()
    {
        $this->visitPath('/contact');
    }

    /**
     * @When /^I set the subject to (.+)$/
     */
    public function iSetTheSubjectField($subject)
    {
        $page = $this->getSession()->getPage();

        $page->fillField('contact[subject]', $this->form['subject'] = $subject);
    }

    /**
     * @When /^I complete the form$/
     */
    public function iCompleteTheForm()
    {
        $this->readyToRecordEmails();

        $page = $this->getSession()->getPage();

        $page->fillField('contact[name]', $this->form['name'] = 'Foo Bar');
        $page->fillField('contact[email]', $this->form['email'] = 'foo@example.com');

        if (!$page->findField('contact[subject]')->getValue()) {
            $page->fillField('contact[subject]', $this->form['subject'] = 'Author query');
        }
        $page->fillField('contact[question]', $this->form['question'] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.\n\nVivamus rhoncus turpis quam, sit amet finibus elit pharetra eget.");
        $page->pressButton('Submit');

        $this->recordEmails();
    }

    /**
     * @Then /^I should see a 'thank you' message$/
     */
    public function iShouldSeeAThankYouMessage()
    {
        $this->assertSession()
            ->elementContains('css', '.info-bar--success', 'Thank you for your question. We will respond as soon as we can.');
    }

    /**
     * @Then /^the completed form should be sent to (.+)$/
     */
    public function theCompletedFormShouldBeSent($emailAddress)
    {
        $this->assertEmailSent(['do_not_reply@elifesciences.org' => null], [$emailAddress => null],
            'Question submitted: '.$this->form['subject'], 'A question has been submitted on '.$this->locatePath('/contact').'

Name
----
'.$this->form['name'].'

Email
-----
'.$this->form['email'].'

Subject
-------
'.$this->form['subject'].'

Question
--------
'.$this->form['question']);
    }
}
