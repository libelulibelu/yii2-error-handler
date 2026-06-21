<?php

namespace Libelula\ErrorHandler\Tests\Utils;

use Libelula\ErrorHandler\Tests\TestCase;
use Libelula\ErrorHandler\utils\SendMail;
use Symfony\Component\Mailer\Mailer;

class SendMailTest extends TestCase
{
    public function testConnectBuildsMailerFromConfig(): void
    {
        $mailer = $this->invokePrivate(new SendMail(), 'connect', [[
            'user' => 'user@example.com',
            'cont' => 's3cr3t',
            'host' => 'smtp.example.com',
            'port' => 587,
        ]]);

        $this->assertInstanceOf(Mailer::class, $mailer);
    }

    public function testAddCcAppendsRecipient(): void
    {
        $sendMail = new SendMail();

        $sendMail->addCc('cc@example.com');

        $this->assertContains('cc@example.com', $this->getPrivateProperty($sendMail, 'cc'));
    }

    public function testAddAttachmentAppendsPath(): void
    {
        $sendMail = new SendMail();

        $sendMail->addAttachment('/tmp/report.txt');

        $this->assertContains('/tmp/report.txt', $this->getPrivateProperty($sendMail, 'attachments'));
    }
}
