<?php

namespace Libelula\ErrorHandler\interfaces;

use DateTime;

/**
 * Implemented by a logger component so the error handler can attach request
 * tracing information (unique id and start time) to the exception metadata.
 */
interface LoggerHandler
{

    /**
     * @return string Unique identifier of the current request.
     */
    public function getRequestUid(): string;

    /**
     * @return DateTime Moment the current request started being processed.
     */
    public function getRequestStartDateTime(): DateTime;
}
