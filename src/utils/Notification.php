<?php

namespace Libelula\ErrorHandler\utils;

use Yii;

/**
 * Collects request/server information into temporary files and sends them as
 * email attachments when notifying about an exception.
 */
class Notification
{

    /** @var string[] Map of logical file name to the generated temporary path. */
    private $files = [];

    /**
     * Writes the given content as pretty-printed JSON to a temporary file and
     * registers it for later attachment.
     *
     * @param string $filename Logical name (its extension is reused).
     * @param array $content Data to encode and store.
     * @return bool Whether the file was written successfully.
     */
    public function writeFile(string $filename, array $content): bool
    {
        $filePath = $this->getFileName($filename);

        if (file_put_contents(
            $filePath,
            json_encode($content, JSON_PRETTY_PRINT)
        )) {
            $this->files[$filename] = $filePath;

            return true;
        }

        return false;
    }

    /**
     * Builds the request/server detail files and emails them as attachments.
     *
     * @param string $subject Subject line of the notification email.
     * @param array $config Email settings used to deliver the message.
     * @return bool Whether the email was sent successfully.
     */
    public function send(
        string $subject,
        array $config
    ): bool {
        $this->requestFile();
        $this->serverFile();

        $mail = new SendMail();
        return $mail->sendMail(
            $config,
            "An error occurred on the platform. All the details can be found in the attached files.\n\n\n\nThis message was generated automatically.",
            $subject,
            $this->files
        );
    }


    /**
     * Builds a unique temporary file path preserving the original extension.
     *
     * @param string $filename Logical file name to derive the extension from.
     * @return string Absolute path within the system temporary directory.
     */
    private function getFileName(string $filename): string
    {
        return sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . uniqid('file_') . '.'
            . strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Resolves the client IP address from the common forwarding headers,
     * falling back to the Yii request IP.
     *
     * @return string|null
     */
    private function getUserIp(): string|null
    {
        $ip = false;
        $seq = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($seq as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return Yii::$app->request->userIP;
    }

    /**
     * Writes the current request details (IP, user agent, GET/POST/RAW) to a
     * temporary file.
     *
     * @return void
     */
    private function requestFile(): void
    {
        $request = Yii::$app->request;

        $data = [
            'ip'    => $this->getUserIp(),
            'agent' => $request->userAgent,
            'GET'   => $request->get(),
            'POST'  => $request->post(),
            'RAW'   => $request->getRawBody(),
        ];

        $this->writeFile(
            'request_info.txt',
            $data
        );
    }

    /**
     * Writes the current server details (host, scheme, name, port, software)
     * to a temporary file.
     *
     * @return void
     */
    private function serverFile(): void
    {
        $request = Yii::$app->request;

        $data = [
            'host'       => $request->hostName,
            'scheme' => $_SERVER['REQUEST_SCHEME'] ?? null,
            'name' => $_SERVER['SERVER_NAME'] ?? null,
            'port' => $_SERVER['SERVER_PORT'] ?? null,
            'soft' => $_SERVER['SERVER_SOFTWARE'] ?? null,
        ];

        $this->writeFile(
            'server_info.txt',
            $data
        );
    }
}
