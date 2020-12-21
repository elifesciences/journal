<?php

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class Context extends RawMinkContext implements KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var Swift_Message[]
     */
    protected $emails;

    final public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    final protected function mockApiResponse(RequestInterface $request, ResponseInterface $response)
    {
        $this->kernel->getContainer()
            ->get('elife.guzzle.middleware.mock.storage')
            ->save($request, $response);
    }

    /**
     * @BeforeScenario
     */
    final public function setTestChannelCookie()
    {
        if (getenv('JOURNAL_INSTANCE')) {
            $this->visitPath('/?JOURNAL_INSTANCE='.getenv('JOURNAL_INSTANCE'));
        }
    }

    /**
     * @BeforeScenario
     */
    final public function clearMockedApiResponses()
    {
        (new Filesystem())->remove($this->kernel->getContainer()->getParameter('api_mock'));
    }

    /**
     * @BeforeScenario
     */
    final public function clearHttpCache()
    {
        $this->kernel->getContainer()->get('cache.guzzle')->clear();
    }

    /**
     * @BeforeScenario
     */
    final public function resetEmails()
    {
        $this->emails = [];
        if ($this->getSession()->getDriver() instanceof BrowserKitDriver) {
            $this->getSession()->getDriver()->getClient()->followRedirects(true);
        }
    }

    /**
     * @AfterScenario
     */
    final public function checkEmailsHandled()
    {
        if (count($this->emails) > 1) {
            throw new RuntimeException('('.count($this->emails).') email remains in the stack');
        }
    }

    /**
     * @AfterScenario
     */
    final public function stopSession()
    {
        $this->getSession()->stop();
    }

    final protected function readyToRecordEmails()
    {
        $this->getSession()->getDriver()->getClient()->followRedirects(false);

        $this->getSession()
            ->getDriver()
            ->getClient()
            ->enableProfiler();
    }

    final protected function recordEmails()
    {
        /** @var MessageDataCollector $collector */
        $collector = $this->getSession()
            ->getDriver()
            ->getClient()
            ->getProfile()
            ->getCollector('swiftmailer');

        if (0 === $collector->getMessageCount()) {
            return;
        }

        $this->emails = array_merge($this->emails, $collector->getMessages());

        $this->getSession()->getDriver()->getClient()->followRedirect();
    }

    final protected function assertEmailSent(array $from, array $to, string $subject, string $body)
    {
        foreach ($this->emails as $key => $email) {
            if ($from === $email->getFrom() && $to === $email->getTo() && $subject === $email->getSubject() && $body === $email->getBody()) {
                unset($this->emails[$key]);

                return;
            }
        }

        throw new RuntimeException('Could not find email in stack ('.count($this->emails).' recorded)');
    }

    final protected function spin(callable $lambda, int $wait = 10)
    {
        if (!$this->isJavaScript()) {
            $lambda();

            return;
        }

        $e = null;
        for ($i = 0; $i < $wait; ++$i) {
            try {
                $lambda();

                return;
            } catch (Exception $e) {
                // Do nothing.
            }

            sleep(1);
        }

        throw new Exception('Timeout: '.$e->getMessage(), -1, $e);
    }

    final protected function isJavaScript()
    {
        try {
            return $this->getSession()->evaluateScript('true');
        } catch (UnsupportedDriverActionException $e) {
            return false;
        }
    }
}
