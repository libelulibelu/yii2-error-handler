<?php

namespace Libelula\ErrorHandler\utils;

use Exception;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

/**
 * Thin wrapper around Symfony Mailer that builds and sends an email with
 * optional CC recipients and file attachments.
 */
class SendMail
{

    /** @var Email The message being assembled. */
    private Email $mail;

    /** @var string[] CC recipient addresses. */
    private array $cc = [];

    /** @var string[] Attachment paths/URLs keyed by their display name. */
    private array $attachments = [];

    /**
     * Builds and sends the email.
     *
     * @param array $config Email settings: `remitente`, `to`, `cc`, `user`,
     *                      `cont`, `host` and `port`.
     * @param string $message HTML body of the message.
     * @param string $subject Subject line of the message.
     * @param string[] $attachments Attachment paths/URLs keyed by display name.
     * @return bool Whether the message was sent successfully.
     */
    public function sendMail(
        array $config,
        string $message,
        string $subject,
        array  $attachments = [],
    ): bool {

        $mailer = $this->connect($config);

        $this->mail = (new Email())
            ->from($config['remitente'])
            ->to($config['to'])
            ->subject($subject)
            ->html($message);

        $this->cc = array_merge($this->cc, $config['cc'] ?? []);

        foreach ($this->cc as $cc) {
            $this->mail->addCc($cc);
        }

        $this->attachments = array_merge($this->attachments, $attachments);

        foreach ($this->attachments as $name => $attachment) {
            $this->attachment($name, $attachment);
        }

        try {
            $mailer->send($this->mail);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Adds a CC recipient.
     *
     * @param string $mail Email address to add as CC.
     * @return void
     */
    public function addCc(string $mail): void
    {
        $this->cc[] = $mail;
    }

    /**
     * Registers an attachment by path/URL.
     *
     * @param string $url Path or URL of the file to attach.
     * @return void
     */
    public function addAttachment(string $url): void
    {
        $this->attachments[] = $url;
    }

    /**
     * Reads the file at the given path/URL and attaches it to the message.
     *
     * @param string $name Display name of the attachment.
     * @param string $url Path or URL of the file to attach.
     * @return void
     */
    private function attachment(string $name, string $url): void
    {
        $body = file_get_contents($url);
        $this->mail->attach($body, $name);
    }

    /**
     * Builds the SMTP mailer from the given config.
     *
     * @param array $config Email settings: `user`, `cont`, `host` and `port`.
     * @return Mailer
     */
    private function connect(array $config): Mailer
    {
        $user = urlencode($config['user']);
        $password = urldecode($config['cont']);

        $transport = Transport::fromDsn(
            "smtp://{$user}:{$password}@{$config['host']}:{$config['port']}"
        );
        return new Mailer($transport);
    }
}
