<?php

namespace Libelula\ErrorHandler\interfaces;

use DateTime;

interface LoggerHandler
{

    public function getRequestUid(): string;

    public function getRequestStartDateTime(): DateTime;
}
