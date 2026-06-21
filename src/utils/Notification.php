<?php

namespace Libelula\ErrorHandler\utils;

use Yii;

class Notification
{

    /** @var string[] */
    private $files = [];

    public function writeFile(string $filename, array $content)
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

    public function send(
        string $asunto,
        array $config
    ): bool {
        $this->requestFile();
        $this->serverFile();

        $mail = new SendMail();
        return $mail->sendMail(
            $config,
            "Ocurrio un error en la plataforma todos los detalles los encontrara en los archivos adjuntos.\n\n\n\nEste mensaje fue generado de forma automática",
            $asunto,
            $this->files
        );
    }


    private function getFileName(string $filename)
    {
        return sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . uniqid('file_') . '.'
            . strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Current user ip request
     *
     * @return string|null
     */
    private function getUserIp()
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

    private function requestFile()
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

    private function serverFile()
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
