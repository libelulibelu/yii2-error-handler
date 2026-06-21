<?php

namespace Libelula\ErrorHandler\Tests\Fixtures;

use DateTime;
use Libelula\ErrorHandler\interfaces\LoggerHandler;
use yii\base\Component;

/**
 * Logger fixture implementing {@see LoggerHandler} with fixed values so the
 * handler's logger-meta branch is deterministic.
 */
class StubLogger extends Component implements LoggerHandler
{
    public const UID = 'test-request-uid';

    public const START_AT = '2026-06-21 10:20:30';

    public function getRequestUid(): string
    {
        return self::UID;
    }

    public function getRequestStartDateTime(): DateTime
    {
        return new DateTime(self::START_AT);
    }
}
