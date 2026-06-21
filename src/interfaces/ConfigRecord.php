<?php

namespace Libelula\ErrorHandler\interfaces;

/**
 * Implemented by the application's configuration source so the error handler can
 * fetch settings (e.g. the email notification config) by code.
 */
interface ConfigRecord
{

    /**
     * Returns the configuration values for the given code.
     *
     * @param string $code Configuration code to look up (e.g. the email config key).
     * @return array The configuration values, or an empty array when not found.
     */
    public function getConfig(string $code): array;
}
